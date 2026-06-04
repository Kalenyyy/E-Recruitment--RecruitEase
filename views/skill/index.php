<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$skillList = SkillController::getAllSkill($conn);
$skillCount = mysqli_num_rows($skillList);

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

<?php if (isset($_SESSION['success'])): ?>
    <div id="alert-success"
        class="mb-6 flex items-center justify-between p-4 rounded-2xl border animate-fade-in-down"
        style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;">

        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-full flex-shrink-0"
                style="width:40px;height:40px;background:#DCFCE7;border:1px solid #86EFAC;">
                <span style="font-size:20px;">✅</span>
            </div>

            <div>
                <h4 class="font-bold text-sm">Berhasil!</h4>
                <p class="text-xs"><?= $_SESSION['success'] ?></p>
            </div>
        </div>

        <button onclick="document.getElementById('alert-success').remove()">
            <span class="text-xl px-2">×</span>
        </button>
    </div>

    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Manajemen Skill</h1>
        <p class="text-sm text-slate-500">
            Kelola data skill yang digunakan pada sistem
        </p>
    </div>

    <a href="<?= BASE_URL ?>views/skill/create.php"
        class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background:#1E3A8A;">
        + Tambah Skill
    </a>
</div>

<!-- CARD -->
<div class="rounded-2xl overflow-hidden bg-white border border-slate-200">

    <!-- HEADER TABLE -->
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-200">
        <span class="text-sm font-semibold text-slate-800">
            Daftar Skill
        </span>

        <span class="text-xs font-semibold px-2 py-0.5 rounded-full"
            style="background:#EFF6FF;color:#1E3A8A;">
            <?= $skillCount ?> Skill
        </span>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm">

            <thead style="background:#F8FAFC;">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        No
                    </th>

                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Nama Skill
                    </th>

                    <th class="text-right px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Aksi
                    </th>
                </tr>
            </thead>

            <tbody>

                <?php
                $no = 1;
                foreach ($skillList as $skill):
                ?>

                    <tr
                        style="border-bottom:1px solid #F1F5F9;"
                        onmouseover="this.style.background='#F8FAFC'"
                        onmouseout="this.style.background='#FFFFFF'">

                        <td class="px-6 py-4 font-medium text-slate-600">
                            <?= $no++ ?>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">

                                <div class="w-9 h-9 rounded-lg flex items-center justify-center text-blue-700"
                                    style="background:#DBEAFE;">
                                    🛠️
                                </div>

                                <span class="font-semibold text-slate-800">
                                    <?= htmlspecialchars($skill['nama_skill']) ?>
                                </span>

                            </div>
                        </td>

                        <td class="px-6 py-4">
                            <div class="flex justify-end gap-2">

                                <a href="edit.php?id=<?= $skill['id_skill'] ?>"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                                    style="background:#EFF6FF;color:#1E3A8A;border:1px solid #BFDBFE;">
                                    ✏️ Edit
                                </a>

                                <button
                                    onclick="openDeleteModal(
                                    <?= $skill['id_skill'] ?>,
                                    '<?= htmlspecialchars($skill['nama_skill']) ?>'
                                )"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition"
                                    style="background:#FEF2F2;color:#991B1B;border:1px solid #FECACA;">
                                    🗑️ Hapus
                                </button>

                            </div>
                        </td>

                    </tr>

                <?php endforeach; ?>

                <?php if ($skillCount == 0): ?>
                    <tr>
                        <td colspan="3" class="text-center py-12 text-slate-400">

                            <div class="flex flex-col items-center gap-2">
                                <span class="text-4xl">🛠️</span>
                                <span>Belum ada data skill</span>
                            </div>

                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>

        </table>
    </div>

    <!-- FOOTER -->
    <div class="flex items-center justify-between px-6 py-3 border-t border-slate-200">
        <span class="text-xs text-slate-400">
            Menampilkan <?= $skillCount ?> data skill
        </span>
    </div>

</div>

<!-- MODAL DELETE -->
<div id="modalDelete"
    class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">

    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down">

        <div class="p-6 text-center">

            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100">
                <span class="text-2xl">⚠️</span>
            </div>

            <h3 class="text-lg font-bold text-slate-800 mb-2">
                Hapus Skill?
            </h3>

            <p class="text-sm text-slate-500 mb-6">
                Skill
                <span id="deleteName" class="font-bold text-slate-700"></span>
                akan dihapus permanen.
            </p>

            <div class="flex gap-3">

                <button
                    onclick="closeModal()"
                    class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl">
                    Batal
                </button>

                <a id="confirmDeleteBtn"
                    href="#"
                    class="flex-1 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl">
                    Ya, Hapus
                </a>

            </div>

        </div>

    </div>

</div>

<script>
    function openDeleteModal(id, nama) {
        document.getElementById('deleteName').innerText = nama;
        document.getElementById('confirmDeleteBtn').href =
            'delete.php?id=' + id;

        document.getElementById('modalDelete').classList.remove('hidden');
        document.getElementById('modalDelete').classList.add('flex');
    }

    function closeModal() {
        document.getElementById('modalDelete').classList.add('hidden');
        document.getElementById('modalDelete').classList.remove('flex');
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>