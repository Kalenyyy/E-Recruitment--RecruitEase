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

    /* Memperhalus tampilan form search */
    .search-input:focus+.search-icon {
        color: #1E3A8A;
    }
</style>

<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 7;
$totalData = StaffController::getTotalCount($conn, $search);
$totalPages = ceil($totalData / $perPage);

$hrdList = StaffController::getPaginatedStaff($conn, $page, $perPage, $search);
$hrdCountInPage = mysqli_num_rows($hrdList);

ob_start();
?>

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

<div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-2xl font-bold tracking-tight" style="color: #1E293B;">Manajemen HRD</h1>
        <p class="text-sm" style="color: #64748B;">Kelola dan pantau akses akun HRD dalam sistem</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <!-- SEARCH FORM -->
        <form method="GET" action="" class="relative group">
            <i class="fas fa-search search-icon absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs transition-colors"></i>
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama atau email..."
                class="search-input pl-9 pr-8 py-2 text-sm rounded-xl outline-none w-64 transition focus:ring-2 focus:ring-blue-100 border border-slate-300"
                style="background: #FFFFFF; color: #1E293B;" />
            <?php if (!empty($search)): ?>
                <a href="index.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 transition">
                    <i class="fas fa-times-circle"></i>
                </a>
            <?php endif; ?>
        </form>

        <a href="<?= BASE_URL ?>views/user/create.php"
            class="inline-flex items-center gap-2 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition hover:opacity-90 shadow-sm"
            style="background: #1E3A8A;">
            <i class="fas fa-plus text-xs"></i> Tambah HRD
        </a>
    </div>
</div>

<!-- TABLE CARD -->
<div class="rounded-2xl overflow-hidden shadow-sm" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
    <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid #E2E8F0;">
        <div class="flex items-center gap-3">
            <span class="text-sm font-bold" style="color: #1E293B;">
                <?= empty($search) ? 'Daftar Akun HRD' : 'Hasil Pencarian: "' . htmlspecialchars($search) . '"' ?>
            </span>
            <span class="text-xs font-bold px-2.5 py-1 rounded-full" style="background: #EFF6FF; color: #1E3A8A;">
                <?= $totalData ?> Total
            </span>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr style="background: #F8FAFC;">
                    <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Nama Lengkap</th>
                    <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Email</th>
                    <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="text-left px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Tgl Bergabung</th>
                    <th class="text-right px-6 py-4 text-xs font-bold uppercase tracking-wider text-slate-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if ($hrdCountInPage > 0): ?>
                    <?php foreach ($hrdList as $hrd): ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-4">
                                <div class="font-semibold text-slate-800"><?= htmlspecialchars($hrd['nama_staff']) ?></div>
                            </td>
                            <td class="px-6 py-4 text-slate-500 italic"><?= htmlspecialchars($hrd['email']) ?></td>
                            <td class="px-6 py-4">
                                <?php if ($hrd['status'] === 'active'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold rounded-full bg-emerald-100 text-emerald-700 uppercase">
                                        <i class="fas fa-check-circle text-[10px]"></i> Active
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 text-[11px] font-bold rounded-full bg-rose-100 text-rose-700 uppercase">
                                        <i class="fas fa-times-circle text-[10px]"></i> Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-slate-500">
                                <i class="far fa-calendar-alt mr-1 text-slate-400"></i> <?= date('d M Y', strtotime($hrd['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openStatusModal(<?= $hrd['user_id'] ?>, '<?= addslashes($hrd['nama_staff']) ?>', '<?= $hrd['status'] ?>')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-blue-50 hover:text-blue-700 hover:border-blue-200 transition shadow-sm">
                                        <i class="fas fa-sync-alt"></i> Status
                                    </button>
                                    <button onclick="openDeleteModal(<?= $hrd['user_id'] ?>, '<?= addslashes($hrd['nama_staff']) ?>')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-lg bg-white border border-slate-200 text-slate-600 hover:bg-rose-50 hover:text-rose-700 hover:border-rose-200 transition shadow-sm">
                                        <i class="far fa-trash-alt"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center">
                                    <i class="fas fa-search text-slate-300 text-2xl"></i>
                                </div>
                                <div class="text-slate-500 font-medium">Data tidak ditemukan untuk "<?= htmlspecialchars($search) ?>"</div>
                                <a href="index.php" class="text-sm text-blue-600 font-bold hover:underline">Tampilkan Semua Data</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="flex items-center justify-between px-6 py-4 bg-slate-50/50">
        <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">
            Halaman <?= $page ?> dari <?= $totalPages ?: 1 ?>
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


<!-- MODAL STATUS -->
<div id="modalStatus" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down p-8 text-center">
        <div class="w-20 h-20 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-blue-100">
            <i class="fas fa-user-shield text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Update Status Akun</h3>
        <p class="text-sm text-slate-500 mb-8 leading-relaxed">Anda akan mengubah status <span id="statusName" class="font-bold text-slate-700"></span> menjadi <span id="nextStatus" class="font-bold px-2 py-0.5 rounded bg-slate-100 uppercase text-[10px]"></span></p>
        <div class="flex gap-3">
            <button onclick="closeModal('modalStatus')" class="flex-1 py-3 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
            <a id="confirmStatusBtn" href="#" class="flex-1 py-3 text-sm font-bold text-white bg-blue-900 rounded-xl hover:bg-blue-800 transition shadow-lg shadow-blue-900/20">Konfirmasi</a>
        </div>
    </div>
</div>

<!-- MODAL DELETE -->
<div id="modalDelete" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down p-8 text-center">
        <div class="w-20 h-20 bg-rose-50 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-6 border-4 border-rose-100">
            <i class="fas fa-exclamation-triangle text-3xl"></i>
        </div>
        <h3 class="text-xl font-bold text-slate-800 mb-2">Hapus Akun HRD?</h3>
        <p class="text-sm text-slate-500 mb-8 leading-relaxed">Tindakan ini tidak dapat dibatalkan. Akun <span id="deleteName" class="font-bold text-slate-700"></span> akan dihapus permanen.</p>
        <div class="flex gap-3">
            <button onclick="closeModal('modalDelete')" class="flex-1 py-3 text-sm font-bold text-slate-500 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 py-3 text-sm font-bold text-white bg-rose-600 rounded-xl hover:bg-rose-700 transition shadow-lg shadow-rose-600/20">Ya, Hapus Data</a>
        </div>
    </div>
</div>

<script>
    function openStatusModal(id, name, currentStatus) {
        const nextStatus = (currentStatus === 'active' ? 'Inactive' : 'Active');
        document.getElementById('statusName').innerText = name;
        document.getElementById('nextStatus').innerText = nextStatus;
        document.getElementById('confirmStatusBtn').href = 'edit.php?id=' + id;
        document.getElementById('modalStatus').classList.replace('hidden', 'flex');
    }

    function openDeleteModal(id, name) {
        document.getElementById('deleteName').innerText = name;
        document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + id;
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