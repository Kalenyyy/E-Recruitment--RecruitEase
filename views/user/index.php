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

<?php
require_once __DIR__ . '/../../init.php';
AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

// --- LOGIKA SEARCH & PAGINATION ---
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

<div class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Manajemen HRD</h1>
        <p class="text-sm" style="color: #64748B;">Kelola akun HRD dalam sistem</p>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <!-- SEARCH FORM -->
        <form method="GET" action="" class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama atau email..."
                class="pl-8 pr-3 py-2 text-xs rounded-xl outline-none w-64 transition focus:ring-2 focus:ring-blue-100"
                style="border: 1px solid #CBD5E1; background: #FFFFFF; color: #1E293B;" />
            <?php if (!empty($search)): ?>
                <a href="index.php" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-red-500 text-lg">×</a>
            <?php endif; ?>
        </form>

        <a href="<?= BASE_URL ?>views/user/create.php"
            class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2 rounded-xl transition hover:opacity-90"
            style="background: #1E3A8A;">
            + Tambah HRD
        </a>
    </div>
</div>

<!-- TABLE CARD -->
<div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
    <div class="px-6 py-4" style="border-bottom: 1px solid #E2E8F0;">
        <span class="text-sm font-semibold" style="color: #1E293B;">
            <?= empty($search) ? 'Daftar HRD' : 'Hasil Pencarian: "' . htmlspecialchars($search) . '"' ?>
        </span>
        <span class="ml-2 text-xs font-semibold px-2 py-0.5 rounded-full" style="background: #EFF6FF; color: #1E3A8A;">
            <?= $totalData ?> akun
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead style="background: #F8FAFC;">
                <tr>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color: #94A3B8;">Nama</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color: #94A3B8;">Email</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color: #94A3B8;">Status</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color: #94A3B8;">Dibuat</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold uppercase tracking-wide" style="color: #94A3B8;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($hrdCountInPage > 0): ?>
                    <?php foreach ($hrdList as $hrd):
                        $nama = $hrd['nama_staff'];
                        $nameParts = explode(' ', trim($nama));
                        $initials = strtoupper(substr($nameParts[0], 0, 1));
                        if (isset($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));
                    ?>
                        <tr style="border-bottom: 1px solid #F1F5F9;" class="hover:bg-slate-50">
                            <td class="px-6 py-4 font-semibold"><?= htmlspecialchars($nama) ?></td>
                            <td class="px-6 py-4 text-slate-500"><?= htmlspecialchars($hrd['email']) ?></td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full"
                                    style="<?= $hrd['status'] === 'active' ? 'background: #DCFCE7; color: #166534;' : 'background: #FEE2E2; color: #991B1B;' ?>">
                                    <?= ucfirst($hrd['status']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500"><?= date('d M Y', strtotime($hrd['created_at'])) ?></td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <button onclick="openStatusModal(<?= $hrd['user_id'] ?>, '<?= addslashes($hrd['nama_staff']) ?>', '<?= $hrd['status'] ?>')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 text-blue-700 border border-blue-200 hover:bg-blue-100">🔄 Status</button>
                                    <button onclick="openDeleteModal(<?= $hrd['user_id'] ?>, '<?= addslashes($hrd['nama_staff']) ?>')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-700 border border-red-200 hover:bg-red-100">🗑️ Hapus</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-12">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-3xl">🔍</span>
                                <span class="text-sm text-slate-400">Tidak menemukan hasil untuk "<?= htmlspecialchars($search) ?>"</span>
                                <a href="index.php" class="text-xs text-blue-600 font-bold hover:underline">Reset Pencarian</a>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION DENGAN SEARCH PARAMETER -->
    <div class="flex items-center justify-between px-6 py-4" style="border-top: 1px solid #E2E8F0;">
        <span class="text-xs text-slate-500">
            Menampilkan <?= ($hrdCountInPage > 0) ? (($page - 1) * $perPage) + 1 : 0 ?> - <?= ($page - 1) * $perPage + $hrdCountInPage ?> dari <?= $totalData ?> data
        </span>

        <div class="flex gap-1">
            <?php
            // Fungsi pembantu agar URL pagination tetap membawa kata kunci search
            $searchQuery = !empty($search) ? "&search=" . urlencode($search) : "";
            ?>

            <!-- Prev -->
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $searchQuery ?>" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">‹</a>
            <?php endif; ?>

            <!-- Numbers -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $searchQuery ?>"
                    class="px-3 py-1 text-xs rounded-lg font-semibold <?= $i == $page ? 'bg-blue-900 text-white' : 'border border-slate-200 hover:bg-slate-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <!-- Next -->
            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $searchQuery ?>" class="px-3 py-1 text-xs rounded-lg border border-slate-200 hover:bg-slate-50">›</a>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- MODAL STATUS & DELETE -->
<div id="modalStatus" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down p-6 text-center">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100"><span class="text-2xl">🔄</span></div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">Ubah Status?</h3>
        <p class="text-sm text-slate-500 mb-6">Ubah status <span id="statusName" class="font-bold"></span> menjadi <span id="nextStatus" class="font-bold"></span>?</p>
        <div class="flex gap-3">
            <button onclick="closeModal('modalStatus')" class="flex-1 py-2.5 text-sm font-semibold bg-slate-100 rounded-xl">Batal</button>
            <a id="confirmStatusBtn" href="#" class="flex-1 py-2.5 text-sm font-semibold text-white bg-blue-900 rounded-xl">Ya, Ubah</a>
        </div>
    </div>
</div>

<div id="modalDelete" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down p-6 text-center">
        <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100"><span class="text-2xl">⚠️</span></div>
        <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Data?</h3>
        <p class="text-sm text-slate-500 mb-6">Hapus <span id="deleteName" class="font-bold"></span> permanen?</p>
        <div class="flex gap-3">
            <button onclick="closeModal('modalDelete')" class="flex-1 py-2.5 text-sm font-semibold bg-slate-100 rounded-xl">Batal</button>
            <a id="confirmDeleteBtn" href="#" class="flex-1 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl">Ya, Hapus</a>
        </div>
    </div>
</div>

<script>
    function openStatusModal(id, name, currentStatus) {
        document.getElementById('statusName').innerText = name;
        document.getElementById('nextStatus').innerText = (currentStatus === 'active' ? 'Inactive' : 'Active');
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