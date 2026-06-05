<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$divisiList = DivisiController::read();
$divisiCount = mysqli_num_rows($divisiList);

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

<div class="flex items-center justify-between mb-6">

    <div>

        <h1 class="text-xl font-bold text-slate-800">
            Manajemen Divisi
        </h1>

        <p class="text-sm text-slate-500">
            Kelola data divisi perusahaan
        </p>

    </div>

    <button
            onclick="openCreateModal()"
            class="px-4 py-2 rounded-xl text-white font-semibold"
            style="background:#1E3A8A;">

            + Tambah Divisi

    </button>

</div>

<div
    class="rounded-2xl overflow-hidden"
    style="background:#FFFFFF;border:1px solid #E2E8F0;">

    <div
        class="flex items-center gap-3 px-6 py-4"
        style="border-bottom:1px solid #E2E8F0;">

        <span
            class="text-sm font-semibold text-slate-800">

            Daftar Divisi

        </span>

        <span
            class="text-xs font-semibold px-2 py-1 rounded-full"
            style="background:#EFF6FF;color:#1E3A8A;">

            <?= $divisiCount ?> Divisi

        </span>

        <div class="ml-auto">

            <input
                type="text"
                id="searchInput"
                placeholder="Cari divisi..."
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
                        Nama Divisi
                    </th>

                    <th class="px-6 py-3 text-right">
                        Aksi
                    </th>

                </tr>

            </thead>

            <tbody id="tableDivisi">

                <?php while ($divisi = mysqli_fetch_assoc($divisiList)): ?>

                    <tr
                        style="border-bottom:1px solid #F1F5F9;"
                        onmouseover="this.style.background='#F8FAFC'"
                        onmouseout="this.style.background='#FFFFFF'">

                        <td class="px-6 py-4">

                            #<?= $divisi['id'] ?>

                        </td>

                        <td class="px-6 py-4 font-semibold text-slate-800">

                            <?= htmlspecialchars($divisi['nama_divisi']) ?>

                        </td>

                        <td class="px-6 py-4 text-right">

                            <div class="flex justify-end gap-2">

                                <button
                                    onclick="openEditModal(
                                        <?= $divisi['id'] ?>,
                                        '<?= htmlspecialchars($divisi['nama_divisi']) ?>'
                                )"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                style="background:#FEF3C7;color:#92400E;">

                                ✏️ Edit

                                </button>

                                <button
                                    onclick="openDeleteModal(
                                        <?= $divisi['id'] ?>,
                                        '<?= htmlspecialchars($divisi['nama_divisi']) ?>'
                                    )"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold"
                                    style="background:#FEF2F2;color:#991B1B;">

                                    🗑️ Hapus

                                </button>

                            </div>

                        </td>

                    </tr>

                <?php endwhile; ?>

                <?php if ($divisiCount == 0): ?>

                    <tr>

                        <td colspan="3"
                            class="text-center py-10 text-slate-400">

                            Belum ada data divisi

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- modal delete -->

<div
    id="modalDelete"
    class="fixed inset-0 hidden items-start justify-center bg-slate-900/50 backdrop-blur-sm z-50 pt-20">

    <div
        class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">

        <div class="p-8 text-center">

            <div
                class="w-16 h-16 mx-auto rounded-full flex items-center justify-center mb-4"
                style="background:#FEE2E2;">

                <span class="text-3xl">
                    ⚠️
                </span>

            </div>

            <h3 class="text-xl font-bold text-slate-800 mb-2">
                Hapus Divisi?
            </h3>

            <p class="text-sm text-slate-500">

                Data

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
                    class="flex-1 py-3 rounded-xl text-white text-center"
                    style="background:#DC2626;">

                    Hapus

                </a>

            </div>

        </div>

    </div>

</div>

<!-- modal create -->

<div
    id="modalCreate"
    class="fixed inset-0 hidden items-start justify-center bg-slate-900/50 backdrop-blur-sm z-50 pt-20">

    <div
        class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">

        <div class="px-6 py-5 border-b border-slate-200">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <div
                        class="w-12 h-12 rounded-2xl flex items-center justify-center"
                        style="background:#DBEAFE;">

                        <span class="text-xl">
                            🏢
                        </span>

                    </div>

                    <div>

                        <h2 class="text-lg font-bold text-slate-800">
                            Tambah Divisi
                        </h2>

                        <p class="text-xs text-slate-500">
                            Tambahkan divisi baru perusahaan
                        </p>

                    </div>

                </div>

                <button
                    onclick="closeCreateModal()"
                    class="w-8 h-8 rounded-lg hover:bg-slate-100 transition">

                    ✕

                </button>

            </div>

        </div>

        <form action="create.php" method="POST">

            <div class="p-6">

                <label
                    class="block text-sm font-semibold text-slate-700 mb-2">

                    Nama Divisi

                </label>

                <input
                    type="text"
                    name="nama_divisi"
                    required
                    placeholder="Contoh : Human Resource"
                    class="w-full px-4 py-3 rounded-xl border border-slate-300 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition">

            </div>

            <div
                class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">

                <button
                    type="button"
                    onclick="closeCreateModal()"
                    class="px-5 py-2.5 rounded-xl font-medium bg-white border border-slate-300 hover:bg-slate-100 transition">

                    Batal

                </button>

                <button
                    type="submit"
                    class="px-5 py-2.5 rounded-xl text-white font-semibold hover:opacity-90 transition"
                    style="background:#1E3A8A;">

                    Simpan Divisi

                </button>

            </div>

        </form>

    </div>

</div>

<!-- modal edit -->

<div
    id="modalEdit"
    class="fixed inset-0 hidden items-start justify-center bg-slate-900/50 backdrop-blur-sm z-50 pt-20">

    <div
        class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">

        <div class="px-6 py-5 border-b border-slate-200">

            <div class="flex items-center justify-between">

                <div class="flex items-center gap-3">

                    <div
                        class="w-12 h-12 rounded-2xl flex items-center justify-center"
                        style="background:#FEF3C7;">

                        ✏️

                    </div>

                    <div>

                        <h2 class="text-lg font-bold text-slate-800">
                            Edit Divisi
                        </h2>

                        <p class="text-xs text-slate-500">
                            Ubah data divisi
                        </p>

                    </div>

                </div>

                <button
                    onclick="closeEditModal()"
                    class="w-8 h-8 rounded-lg hover:bg-slate-100">

                    ✕

                </button>

            </div>

        </div>

        <form action="edit.php" method="POST">

            <input
                type="hidden"
                name="id_divisi"
                id="edit_id_divisi">

            <div class="p-6">

                <label
                    class="block text-sm font-semibold mb-2">

                    Nama Divisi

                </label>

                <input
                    type="text"
                    name="nama_divisi"
                    id="edit_nama_divisi"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-slate-300">

            </div>

            <div
                class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-3">

                <button
                    type="button"
                    onclick="closeEditModal()"
                    class="px-5 py-2 rounded-xl bg-slate-200">

                    Batal

                </button>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-xl text-white"
                    style="background:#F59E0B;">

                    Update

                </button>

            </div>

        </form>

    </div>

</div>

<script>

function openDeleteModal(id, nama)
{
    document.getElementById('deleteName').innerText = nama;

    document.getElementById('deleteBtn').href =
        'delete.php?id=' + id;

    document.getElementById('modalDelete')
        .classList.remove('hidden');

    document.getElementById('modalDelete')
        .classList.add('flex');
}

function closeModal()
{
    document.getElementById('modalDelete')
        .classList.add('hidden');

    document.getElementById('modalDelete')
        .classList.remove('flex');
}

document.getElementById("searchInput")
.addEventListener("keyup", function() {

    let filter = this.value.toLowerCase();

    let rows =
        document.querySelectorAll("#tableDivisi tr");

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
        .getElementById('modalCreate')
        .classList.remove('hidden');

    document
        .getElementById('modalCreate')
        .classList.add('flex');
}

function closeCreateModal()
{
    document
        .getElementById('modalCreate')
        .classList.add('hidden');

    document
        .getElementById('modalCreate')
        .classList.remove('flex');
}

function openEditModal(id, nama)
{
    document.getElementById('edit_id_divisi').value = id;

    document.getElementById('edit_nama_divisi').value = nama;

    document
        .getElementById('modalEdit')
        .classList.remove('hidden');

    document
        .getElementById('modalEdit')
        .classList.add('flex');
}

function closeEditModal()
{
    document
        .getElementById('modalEdit')
        .classList.add('hidden');

    document
        .getElementById('modalEdit')
        .classList.remove('flex');
}

function openDeleteModal(id, nama)
{
    document.getElementById('deleteName').innerText = nama;

    document.getElementById('deleteBtn').href =
        "delete.php?id=" + id;

    document
        .getElementById('modalDelete')
        .classList.remove('hidden');

    document
        .getElementById('modalDelete')
        .classList.add('flex');
}

function closeDeleteModal()
{
    document
        .getElementById('modalDelete')
        .classList.add('hidden');

    document
        .getElementById('modalDelete')
        .classList.remove('flex');
}


</script>

<?php

$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';

?>