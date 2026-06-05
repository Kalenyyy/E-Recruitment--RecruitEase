<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$posisiList = PosisiController::read();
$posisiCount = mysqli_num_rows($posisiList);

ob_start();

?>

<?php if (isset($_SESSION['success'])): ?>
    <div id="alert-success"
        class="mb-6 flex items-center justify-between p-4 rounded-2xl border animate-fade-in-down"
        style="background:#F0FDF4; border:1px solid #BBF7D0; color:#166534;">

        <div class="flex items-center gap-3">

            <div
                class="flex items-center justify-center rounded-full flex-shrink-0"
                style="width:40px;height:40px;background:#DCFCE7;border:1px solid #86EFAC;">

                ✅

            </div>

            <div>
                <h4 class="font-bold text-sm">
                    Berhasil!
                </h4>

                <p class="text-xs">
                    <?= $_SESSION['success'] ?>
                </p>
            </div>

        </div>

        <button onclick="document.getElementById('alert-success').remove()">
            ×
        </button>

    </div>

    <?php unset($_SESSION['success']); ?>

<?php endif; ?>

<!-- HEADER -->

<div class="flex items-center justify-between mb-6">

    <div>

        <h1 class="text-xl font-bold text-slate-800">
            Manajemen Posisi
        </h1>

        <p class="text-sm text-slate-500">
            Kelola data posisi perusahaan
        </p>

    </div>

    <button
        onclick="openCreateModal()"
        class="px-4 py-2 rounded-xl text-white font-semibold"
        style="background:#1E3A8A;">

        + Tambah Posisi

    </button>

</div>

<!-- TABLE -->

<div
    class="rounded-2xl overflow-hidden"
    style="background:#FFFFFF;border:1px solid #E2E8F0;">

    <div
        class="flex items-center gap-3 px-6 py-4"
        style="border-bottom:1px solid #E2E8F0;">

        <span class="text-sm font-semibold text-slate-800">
            Daftar Posisi
        </span>

        <span
            class="text-xs font-semibold px-2 py-1 rounded-full"
            style="background:#EFF6FF;color:#1E3A8A;">

            <?= $posisiCount ?> Posisi

        </span>

        <div class="ml-auto">

            <input
                type="text"
                id="searchInput"
                placeholder="Cari posisi..."
                class="px-3 py-2 text-xs rounded-lg outline-none"
                style="border:1px solid #CBD5E1;">

        </div>

    </div>

    <div class="overflow-x-auto">

        <table class="w-full text-sm">

            <thead style="background:#F8FAFC;">

                <tr>

                    <th class="px-6 py-3 text-left">
                        ID
                    </th>

                    <th class="px-6 py-3 text-left">
                        Nama Posisi
                    </th>

                    <th class="px-6 py-3 text-left">
                        Divisi
                    </th>

                    <th class="px-6 py-3 text-right">
                        Aksi
                    </th>

                </tr>

            </thead>

            <tbody id="tablePosisi">

                <?php while($posisi = mysqli_fetch_assoc($posisiList)): ?>

                    <tr
                        style="border-bottom:1px solid #F1F5F9;"
                        onmouseover="this.style.background='#F8FAFC'"
                        onmouseout="this.style.background='#FFFFFF'">

                        <td class="px-6 py-4">

                            #<?= $posisi['id'] ?>

                        </td>

                        <td class="px-6 py-4 font-semibold text-slate-800">

                            <?= htmlspecialchars($posisi['nama_posisi']) ?>

                        </td>

                        <td class="px-6 py-4 text-slate-600">

                            <?= htmlspecialchars($posisi['nama_divisi']) ?>

                        </td>

                        <td class="px-6 py-4 text-right">

                            <div class="flex justify-end gap-2">

                                <button
                                    onclick="openEditModal(
                                        <?= $posisi['id'] ?>,
                                        '<?= htmlspecialchars($posisi['nama_posisi']) ?>',
                                        <?= $posisi['divisi_id'] ?>
                                    )"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                    style="background:#FEF3C7;color:#92400E;">

                                    ✏️ Edit

                                </button>

                                <button
                                    onclick="openDeleteModal(
                                        <?= $posisi['id'] ?>,
                                        '<?= htmlspecialchars($posisi['nama_posisi']) ?>'
                                    )"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                    style="background:#FEF2F2;color:#991B1B;">

                                    🗑️ Hapus

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

                <?php if($posisiCount == 0): ?>

                    <tr>

                        <td
                            colspan="4"
                            class="text-center py-10 text-slate-400">

                            Belum ada data posisi

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

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

                        while($divisi = mysqli_fetch_assoc($divisiList)):
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

                        while($divisi = mysqli_fetch_assoc($divisiList)):
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

function openEditModal(id,nama,idDivisi)
{
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

function openDeleteModal(id,nama)
{
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
            text.includes(filter)
            ? ""
            : "none";

    });

});

function openCreateModal()
{
    document
        .getElementById("modalCreate")
        .classList.remove("hidden");

    document
        .getElementById("modalCreate")
        .classList.add("flex");
}

function closeCreateModal()
{
    document
        .getElementById("modalCreate")
        .classList.add("hidden");

    document
        .getElementById("modalCreate")
        .classList.remove("flex");
}

function closeEditModal()
{
    document
        .getElementById("modalEdit")
        .classList.add("hidden");

    document
        .getElementById("modalEdit")
        .classList.remove("flex");
}

function closeDeleteModal()
{
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