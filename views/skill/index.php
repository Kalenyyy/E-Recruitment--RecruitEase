<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

// --- LOGIKA SEARCH & PAGINATION ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 7;
$totalData = SkillController::getTotalCount($conn, $search);

// Baris selanjutnya
$totalPages = ($totalData > 0) ? ceil($totalData / $perPage) : 1;

$skillList = SkillController::getPaginatedSkill($conn, $page, $perPage, $search);
$skillCountInPage = mysqli_num_rows($skillList);

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
        <h1 class="text-xl font-bold text-slate-800">Manajemen Skill</h1>
        <p class="text-sm text-slate-500">Kelola data skill yang digunakan pada sistem</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <!-- SEARCH FORM (Server Side) -->
        <form method="GET" action="" class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari skill..."
                class="pl-8 pr-3 py-2 text-xs rounded-xl outline-none w-64 transition focus:ring-2 focus:ring-blue-100"
                style="border: 1px solid #CBD5E1; background: #FFFFFF; color: #1E293B;" />
            <?php if (!empty($search)): ?>
                <a href="index.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg">×</a>
            <?php endif; ?>
        </form>

        <button onclick="openCreateModal()" class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2 rounded-xl transition"
            style="background:#1E3A8A;">
            + Tambah Skill
        </button>
    </div>
</div>

<!-- CARD TABEL -->
<div class="rounded-2xl overflow-hidden bg-white border border-slate-200">
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-200">
        <span class="text-sm font-semibold text-slate-800">
            <?= empty($search) ? 'Daftar Skill' : 'Hasil Pencarian: "' . htmlspecialchars($search) . '"' ?>
        </span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background:#EFF6FF;color:#1E3A8A;">
            <?= $totalData ?> Skill
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead style="background:#F8FAFC;">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400 w-20">No</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Skill</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = (($page - 1) * $perPage) + 1;
                if ($skillCountInPage > 0):
                    foreach ($skillList as $skill):
                ?>
                        <tr style="border-bottom:1px solid #F1F5F9;" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-medium text-slate-600"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg flex items-center justify-center text-blue-700" style="background:#DBEAFE;">🛠️</div>
                                    <span class="font-semibold text-slate-800"><?= htmlspecialchars($skill['nama_skill']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openEditModal(<?= $skill['id_skill'] ?>, '<?= htmlspecialchars(addslashes($skill['nama_skill'])) ?>')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background:#FEF3C7;color:#92400E;">✏️ Edit</button>
                                    <button onclick="openDeleteModal(<?= $skill['id_skill'] ?>, '<?= htmlspecialchars(addslashes($skill['nama_skill'])) ?>')"
                                        class="px-3 py-1.5 rounded-lg text-xs font-semibold" style="background:#FEF2F2;color:#991B1B;">🗑️ Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-4xl">🔍</span>
                                <span class="text-sm text-slate-400">Data skill tidak ditemukan</span>
                                <a href="index.php" class="text-xs text-blue-600 font-bold hover:underline">Reset Pencarian</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- FOOTER & PAGINATION -->
    <div class="flex items-center justify-between px-6 py-4 border-t border-slate-200">
        <span class="text-xs text-slate-500">
            Menampilkan <?= ($skillCountInPage > 0) ? (($page - 1) * $perPage) + 1 : 0 ?> - <?= ($page - 1) * $perPage + $skillCountInPage ?> dari <?= $totalData ?> data
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

<!-- MODAL CREATE -->
<div id="modalCreate" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#DBEAFE;">🛠️</div>
                <h2 class="text-lg font-bold text-slate-800">Tambah Skill</h2>
            </div>
            <button onclick="closeModal('modalCreate')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="create.php" method="POST" class="p-6">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Skill</label>
            <input type="text" name="nama_skill" required placeholder="Contoh: Laravel" class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-100 mb-6">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalCreate')" class="px-5 py-2.5 rounded-xl bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-white font-semibold" style="background:#1E3A8A;">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="modalEdit" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-3xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">
        <div class="px-6 py-5 border-b border-slate-200 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:#FEF3C7;">✏️</div>
                <h2 class="text-lg font-bold text-slate-800">Edit Skill</h2>
            </div>
            <button onclick="closeModal('modalEdit')" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form action="edit.php" method="POST" class="p-6">
            <input type="hidden" name="id_skill" id="edit_id_skill">
            <label class="block text-sm font-semibold text-slate-700 mb-2">Nama Skill</label>
            <input type="text" name="nama_skill" id="edit_nama_skill" required class="w-full px-4 py-3 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-100 mb-6">
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalEdit')" class="px-5 py-2.5 rounded-xl bg-slate-100 font-semibold">Batal</button>
                <button type="submit" class="px-5 py-2.5 rounded-xl text-white font-semibold" style="background:#F59E0B;">Update</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DELETE -->
<div id="modalDelete" class="fixed inset-0 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-3xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down p-8 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100"><span class="text-2xl">⚠️</span></div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Skill?</h3>
        <p class="text-sm text-slate-500 mb-8">Skill <span id="deleteName" class="font-bold text-slate-700"></span> akan dihapus permanen.</p>
        <div class="flex gap-3">
            <button onclick="closeModal('modalDelete')" class="flex-1 py-3 rounded-xl bg-slate-100 font-semibold">Batal</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 py-3 rounded-xl text-white font-semibold flex items-center justify-center" style="background:#DC2626;">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
    function openCreateModal() {
        document.getElementById('modalCreate').classList.replace('hidden', 'flex');
    }

    function openEditModal(id, nama) {
        document.getElementById('edit_id_skill').value = id;
        document.getElementById('edit_nama_skill').value = nama;
        document.getElementById('modalEdit').classList.replace('hidden', 'flex');
    }

    function openDeleteModal(id, nama) {
        document.getElementById('deleteName').innerText = nama;
        document.getElementById('confirmDeleteBtn').href = "delete.php?id=" + id;
        document.getElementById('modalDelete').classList.replace('hidden', 'flex');
    }

    function closeModal(id) {
        document.getElementById(id).classList.replace('flex', 'hidden');
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>