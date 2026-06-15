<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

// --- LOGIKA SEARCH & PAGINATION ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 7; // Jumlah data per halaman
$totalData = PosisiController::getTotalCount($conn, $search);
$totalPages = ($totalData > 0) ? ceil($totalData / $perPage) : 1;

$posisiList = PosisiController::getPaginated($conn, $page, $perPage, $search);
$posisiCountInPage = mysqli_num_rows($posisiList);

ob_start();
?>

<style>
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-down {
        animation: fadeInDown 0.4s ease-out;
    }
</style>

<!-- Tampilkan Notifikasi Jika Ada -->
<?php if (isset($_SESSION['success'])): ?>
    <div id="alert-success" class="mb-6 flex items-center justify-between p-4 rounded-2xl border animate-fade-in-down"
        style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-full flex-shrink-0" style="width:40px;height:40px;background:#DCFCE7;border:1px solid #86EFAC;">
                <span style="font-size:20px;">✅</span>
            </div>
            <div>
                <h4 class="font-bold text-sm">Berhasil!</h4>
                <p class="text-xs"><?= $_SESSION['success'] ?></p>
            </div>
        </div>
        <button onclick="document.getElementById('alert-success').remove()"><span class="text-xl px-2">×</span></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- HEADER -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Manajemen Posisi</h1>
        <p class="text-sm text-slate-500">Kelola data posisi dan jabatan perusahaan</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <!-- Search Form -->
        <form method="GET" action="" class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari posisi atau divisi..."
                class="pl-8 pr-10 py-2 text-xs rounded-xl outline-none w-64 border border-slate-300 focus:ring-2 focus:ring-blue-100">
            <?php if (!empty($search)): ?>
                <a href="index.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg">×</a>
            <?php endif; ?>
        </form>

        <button onclick="openCreateModal()" class="px-4 py-2 rounded-xl text-white font-semibold text-sm" style="background:#1E3A8A;">
            + Tambah Posisi
        </button>
    </div>
</div>

<!-- TABLE CARD -->
<div class="rounded-2xl overflow-hidden bg-white border border-slate-200">
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-200">
        <span class="text-sm font-semibold text-slate-800">
            <?= empty($search) ? 'Daftar Posisi' : 'Hasil Pencarian: "' . htmlspecialchars($search) . '"' ?>
        </span>
        <span class="text-xs font-semibold px-2 py-1 rounded-full" style="background:#EFF6FF;color:#1E3A8A;">
            <?= $totalData ?> Total
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Nama Posisi</th>
                    <th class="px-6 py-3 text-left text-xs font-bold text-slate-400 uppercase">Divisi</th>
                    <th class="px-6 py-3 text-right text-xs font-bold text-slate-400 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = (($page - 1) * $perPage) + 1;
                if ($posisiCountInPage > 0):
                    while ($posisi = mysqli_fetch_assoc($posisiList)):
                ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50 transition">
                            <td class="px-6 py-4 text-slate-500 font-medium"><?= $no++ ?></td>
                            <td class="px-6 py-4 font-bold text-slate-800"><?= htmlspecialchars($posisi['nama_posisi']) ?></td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-600">
                                    <?= htmlspecialchars($posisi['nama_divisi']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openEditModal(<?= $posisi['id'] ?>, '<?= htmlspecialchars(addslashes($posisi['nama_posisi'])) ?>', <?= $posisi['divisi_id'] ?>)"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">✏️ Edit</button>
                                    <button onclick="openDeleteModal(<?= $posisi['id'] ?>, '<?= htmlspecialchars(addslashes($posisi['nama_posisi'])) ?>')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700">🗑️ Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile;
                else: ?>
                    <tr>
                        <td colspan="4" class="text-center py-12 text-slate-400">Data tidak ditemukan</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER -->
    <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200">
        <span class="text-xs text-slate-500">
            Menampilkan <?= ($posisiCountInPage > 0) ? (($page - 1) * $perPage) + 1 : 0 ?> - <?= ($page - 1) * $perPage + $posisiCountInPage ?> dari <?= $totalData ?> data
        </span>

        <div class="flex gap-1">
            <?php $searchQuery = !empty($search) ? "&search=" . urlencode($search) : ""; ?>

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $searchQuery ?>" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">‹</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $searchQuery ?>"
                    class="px-3 py-1 text-xs rounded-lg font-semibold <?= $i == $page ? 'bg-blue-900 text-white' : 'border border-slate-200 hover:bg-slate-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $searchQuery ?>" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">›</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- TEMPATKAN MODAL CREATE -->
<div
    id="modalCreate"
    class="fixed inset-0 hidden items-start justify-center bg-slate-900/50 backdrop-blur-sm z-50 pt-20">

    <div
        class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">

        <div class="px-6 py-5 border-b">

            <h2 class="text-lg font-bold text-slate-800">
                Tambah Posisi
            </h2>

            <p class="text-sm text-slate-500">
                Tambahkan posisi baru
            </p>

        </div>

        <form action="create.php" method="POST">

            <div class="p-6">

                <div class="mb-4">

                    <label class="block text-sm font-medium mb-2">
                        Nama Posisi
                    </label>

                    <input
                        type="text"
                        name="nama_posisi"
                        required
                        class="w-full px-4 py-3 border rounded-xl">

                </div>

                <div>

                    <label class="block text-sm font-medium mb-2">
                        Divisi
                    </label>

                    <select
                        name="divisi_id"
                        required
                        class="w-full px-4 py-3 border rounded-xl">

                        <option value="">
                            Pilih Divisi
                        </option>

                        <?php
                        $divisiList = DivisiController::read();

                        while ($divisi = mysqli_fetch_assoc($divisiList)):
                        ?>

                            <option value="<?= $divisi['id'] ?>">
                                <?= $divisi['nama_divisi'] ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

            </div>

            <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3">

                <button
                    type="button"
                    onclick="closeCreateModal()"
                    class="px-5 py-2 rounded-xl bg-slate-200">

                    Batal

                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl text-white"
                    style="background:#1E3A8A;">

                    Simpan

                </button>

            </div>

        </form>

    </div>

</div>

<!-- TEMPATKAN MODAL EDIT -->
<div
    id="modalEdit"
    class="fixed inset-0 hidden items-start justify-center bg-slate-900/50 backdrop-blur-sm z-50 pt-20">

    <div
        class="bg-white rounded-3xl w-full max-w-lg overflow-hidden shadow-2xl">

        <div class="px-6 py-5 border-b">

            <h2 class="text-lg font-bold text-slate-800">
                Edit Posisi
            </h2>

            <p class="text-sm text-slate-500">
                Ubah data posisi
            </p>

        </div>

        <form action="edit.php" method="POST">

            <input
                type="hidden"
                name="id"
                id="edit_id">

            <div class="p-6">

                <div class="mb-4">

                    <label class="block text-sm font-medium mb-2">
                        Nama Posisi
                    </label>

                    <input
                        type="text"
                        name="nama_posisi"
                        id="edit_nama_posisi"
                        required
                        class="w-full px-4 py-3 border rounded-xl">

                </div>

                <div>

                    <label class="block text-sm font-medium mb-2">
                        Divisi
                    </label>

                    <select
                        name="divisi_id"
                        id="edit_divisi"
                        required
                        class="w-full px-4 py-3 border rounded-xl">

                        <?php
                        $divisiList = DivisiController::read();

                        while ($divisi = mysqli_fetch_assoc($divisiList)):
                        ?>

                            <option value="<?= $divisi['id'] ?>">
                                <?= $divisi['nama_divisi'] ?>
                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

            </div>

            <div class="px-6 py-4 bg-slate-50 flex justify-end gap-3">

                <button
                    type="button"
                    onclick="closeEditModal()"
                    class="px-5 py-2 rounded-xl bg-slate-200">

                    Batal

                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl text-white bg-amber-500">

                    Update

                </button>

            </div>

        </form>

    </div>

</div>

<script>
    function openEditModal(id, nama, idDivisi) {
        document.getElementById("edit_id").value = id;

        document.getElementById("edit_nama_posisi").value = nama;

        document.getElementById("edit_divisi").value = idDivisi;

        document
            .getElementById("modalEdit")
            .classList.remove("hidden");

        document
            .getElementById("modalEdit")
            .classList.add("flex");
    }
</script>

<!-- TEMPATKAN MODAL DELETE -->
<div
    id="modalDelete"
    class="fixed inset-0 hidden items-start justify-center bg-slate-900/50 backdrop-blur-sm z-50 pt-20">

    <div
        class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl">

        <div class="p-8 text-center">

            <div
                class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4 bg-red-100">

                <span class="text-3xl">
                    ⚠️
                </span>

            </div>

            <h3 class="text-xl font-bold text-slate-800">
                Hapus Posisi?
            </h3>

            <p class="text-sm text-slate-500 mt-3">

                Posisi

                <span
                    id="deleteName"
                    class="font-semibold text-slate-700">
                </span>

                akan dihapus permanen.

            </p>

            <div class="flex gap-3 mt-8">

                <button
                    onclick="closeDeleteModal()"
                    class="flex-1 py-3 rounded-xl bg-slate-200">

                    Batal

                </button>

                <a
                    id="deleteBtn"
                    href="#"
                    class="flex-1 py-3 rounded-xl bg-red-600 text-white text-center">

                    Hapus

                </a>

            </div>

        </div>

    </div>

</div>

<script>
    function openDeleteModal(id, nama) {
        document.getElementById("deleteName").innerText = nama;

        document.getElementById("deleteBtn").href =
            "delete.php?id=" + id;

        document
            .getElementById("modalDelete")
            .classList.remove("hidden");

        document
            .getElementById("modalDelete")
            .classList.add("flex");
    }
</script>

<script>
    document.getElementById("searchInput")
        .addEventListener("keyup", function() {

            let filter = this.value.toLowerCase();

            let rows =
                document.querySelectorAll("#tablePosisi tr");

            rows.forEach(row => {

                let text =
                    row.innerText.toLowerCase();

                row.style.display =
                    text.includes(filter) ?
                    "" :
                    "none";

            });

        });

    function openCreateModal() {
        document
            .getElementById("modalCreate")
            .classList.remove("hidden");

        document
            .getElementById("modalCreate")
            .classList.add("flex");
    }

    function closeCreateModal() {
        document
            .getElementById("modalCreate")
            .classList.add("hidden");

        document
            .getElementById("modalCreate")
            .classList.remove("flex");
    }

    function closeEditModal() {
        document
            .getElementById("modalEdit")
            .classList.add("hidden");

        document
            .getElementById("modalEdit")
            .classList.remove("flex");
    }

    function closeDeleteModal() {
        document
            .getElementById("modalDelete")
            .classList.add("hidden");

        document
            .getElementById("modalDelete")
            .classList.remove("flex");
    }
</script>

<?php

$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';

?>