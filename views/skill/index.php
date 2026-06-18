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

    .search-input:focus+.search-icon {
        color: #1E3A8A;
    }
</style>

<!-- ALERT NOTIFIKASI -->
<?php if (isset($_SESSION['success'])): ?>
    <div id="alert-success" class="mb-6 flex items-center justify-between p-4 rounded-2xl border animate-fade-in-down shadow-sm"
        style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;">
        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-full flex-shrink-0" style="width:40px;height:40px;background:#DCFCE7;border:1px solid #86EFAC;">
                <i class="fas fa-check-circle text-lg"></i>
            </div>
            <div>
                <h4 class="font-bold text-sm">Berhasil!</h4>
                <p class="text-xs"><?= $_SESSION['success'] ?></p>
            </div>
        </div>
        <button onclick="document.getElementById('alert-success').remove()" class="hover:opacity-70 transition">
            <i class="fas fa-times px-2"></i>
        </button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- HEADER -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-slate-800">Manajemen Skill</h1>
        <p class="text-sm text-slate-500">Kelola daftar keahlian dan kompetensi karyawan</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <!-- SEARCH FORM -->
        <form method="GET" action="" class="relative group">
            <i class="fas fa-search search-icon absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs transition-colors"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari skill..."
                class="search-input pl-9 pr-10 py-2.5 text-sm rounded-xl outline-none w-64 border border-slate-300 focus:ring-2 focus:ring-blue-100 transition-all">
            <?php if (!empty($search)): ?>
                <a href="index.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition">
                    <i class="fas fa-times-circle"></i>
                </a>
            <?php endif; ?>
        </form>

        <button onclick="openCreateModal()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white font-semibold text-sm transition hover:opacity-90 shadow-md shadow-blue-900/20" style="background:#1E3A8A;">
            <i class="fas fa-plus text-xs"></i> Tambah Skill
        </button>
    </div>
</div>

<!-- CARD TABEL -->
<div class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-white">
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold text-slate-800">
                <?= empty($search) ? 'Daftar Kompetensi' : 'Hasil Pencarian: "' . htmlspecialchars($search) . '"' ?>
            </span>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full uppercase tracking-wider" style="background:#EFF6FF;color:#1E3A8A;">
                <?= $totalData ?> Total Skill
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead>
                <tr class="bg-slate-50 text-slate-500 uppercase text-[11px] font-bold tracking-widest">
                    <th class="px-6 py-4 w-24 text-center">No</th>
                    <th class="px-6 py-4">Nama Keahlian / Skill</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php
                $no = (($page - 1) * $perPage) + 1;
                if ($skillCountInPage > 0):
                    foreach ($skillList as $skill):
                ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4 text-center text-slate-400 font-medium"><?= str_pad($no++, 2, '0', STR_PAD_LEFT) ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                                        <i class="fas fa-screwdriver-wrench text-xs"></i>
                                    </div>
                                    <span class="font-bold text-slate-700"><?= htmlspecialchars($skill['nama_skill']) ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openEditModal(<?= $skill['id_skill'] ?>, '<?= htmlspecialchars(addslashes($skill['nama_skill'])) ?>')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-amber-50 hover:text-amber-700 hover:border-amber-200 transition shadow-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="openDeleteModal(<?= $skill['id_skill'] ?>, '<?= htmlspecialchars(addslashes($skill['nama_skill'])) ?>')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-white border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition shadow-sm">
                                        <i class="far fa-trash-alt"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-16">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center">
                                    <i class="fas fa-magnifying-glass text-slate-300 text-2xl"></i>
                                </div>
                                <span class="text-slate-400 font-medium">Data skill tidak ditemukan</span>
                                <a href="index.php" class="text-xs text-blue-600 font-bold hover:underline">Tampilkan Semua Data</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- FOOTER & PAGINATION -->
    <div class="flex items-center justify-between px-6 py-4 bg-slate-50/50 border-t border-slate-200">
        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">
            Halaman <?= $page ?> dari <?= $totalPages ?>
        </span>

        <div class="flex gap-1">
            <?php $searchQuery = !empty($search) ? "&search=" . urlencode($search) : ""; ?>

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $searchQuery ?>" class="w-8 h-8 flex items-center justify-center text-xs rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $searchQuery ?>"
                    class="w-8 h-8 flex items-center justify-center text-xs rounded-lg font-bold transition <?= $i == $page ? 'bg-blue-900 text-white shadow-md' : 'border border-slate-200 bg-white hover:bg-slate-50 text-slate-600' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $searchQuery ?>" class="w-8 h-8 flex items-center justify-center text-xs rounded-lg border border-slate-200 bg-white hover:bg-slate-50 transition">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MODAL CREATE -->
<div id="modalCreate" class="fixed inset-0 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-plus"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 leading-none">Tambah Skill</h2>
                    <p class="text-[11px] text-slate-500 mt-1 uppercase tracking-wider font-semibold">Master Data Kompetensi</p>
                </div>
            </div>
            <button onclick="closeModal('modalCreate')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form action="create.php" method="POST">
            <div class="p-8">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Keahlian / Skill</label>
                <div class="relative">
                    <i class="fas fa-screwdriver-wrench absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="nama_skill" required placeholder="Contoh: Laravel, Adobe Photoshop, Sales Management"
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-blue-500 focus:ring-4 focus:ring-blue-50 transition outline-none text-sm font-medium">
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalCreate')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white text-sm font-bold transition hover:opacity-90 shadow-lg shadow-blue-900/20" style="background:#1E3A8A;">Simpan Skill</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT -->
<div id="modalEdit" class="fixed inset-0 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-md overflow-hidden shadow-2xl animate-fade-in-down">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-pen-to-square"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-slate-800 leading-none">Edit Skill</h2>
                    <p class="text-[11px] text-slate-500 mt-1 uppercase tracking-wider font-semibold">Perbarui Kompetensi</p>
                </div>
            </div>
            <button onclick="closeModal('modalEdit')" class="text-slate-400 hover:text-slate-600 transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <form action="edit.php" method="POST">
            <input type="hidden" name="id_skill" id="edit_id_skill">
            <div class="p-8">
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-widest mb-2">Nama Skill Baru</label>
                <div class="relative">
                    <i class="fas fa-screwdriver-wrench absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="nama_skill" id="edit_nama_skill" required
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-4 focus:ring-amber-50 transition outline-none text-sm font-medium">
                </div>
            </div>
            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" onclick="closeModal('modalEdit')" class="px-5 py-2.5 rounded-xl text-sm font-bold text-slate-500 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" class="px-6 py-2.5 rounded-xl text-white text-sm font-bold transition hover:opacity-90 shadow-lg shadow-amber-600/20" style="background:#D97706;">Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL DELETE -->
<div id="modalDelete" class="fixed inset-0 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm z-50 p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down p-8 text-center">
        <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-rose-100">
            <i class="fas fa-exclamation-triangle text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Skill?</h3>
        <p class="text-sm text-slate-500 mb-8 leading-relaxed">Skill <span id="deleteName" class="font-bold text-slate-700"></span> akan dihapus permanen. Hal ini akan menghapus data skill tersebut dari profil karyawan.</p>
        <div class="flex gap-3">
            <button onclick="closeModal('modalDelete')" class="flex-1 py-3 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition shadow-lg shadow-rose-600/20 text-center">Ya, Hapus</a>
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