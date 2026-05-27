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

$hrdList = StaffController::getAllStaff($conn);
$hrdCount = mysqli_num_rows($hrdList);

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");
ob_start();
?>

<?php if (isset($_SESSION['success'])): ?>
    <div id="alert-success" class="mb-6 flex items-center justify-between p-4 rounded-2xl border animate-fade-in-down"
        style="background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534;">

        <div class="flex items-center gap-3">
            <!-- Icon Checkmark Bulat -->
            <div class="flex items-center justify-center rounded-full flex-shrink-0"
                style="width: 40px; height: 40px; background: #DCFCE7; border: 1px solid #86EFAC;">
                <span style="font-size: 20px;">✅</span>
            </div>

            <div>
                <h4 class="font-bold text-sm" style="color: #14532D;">Berhasil Disimpan!</h4>
                <p class="text-xs opacity-90"><?= $_SESSION['success'] ?></p>
            </div>
        </div>

        <!-- Tombol Close -->
        <button onclick="document.getElementById('alert-success').remove()"
            class="transition hover:opacity-70">
            <span class="text-xl px-2">×</span>
        </button>
    </div>
    <?php unset($_SESSION['success']); // Hapus session agar tidak muncul lagi saat refresh 
    ?>
<?php endif; ?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Manajemen HRD</h1>
        <p class="text-sm" style="color: #64748B;">Kelola akun HRD dalam sistem</p>
    </div>

    <a href="<?= BASE_URL ?>views/user/create.php"
        class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background: #1E3A8A;">
        + Tambah HRD
    </a>
</div>

<!-- TABLE CARD -->
<div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">

    <!-- CARD HEADER -->
    <div class="flex items-center gap-3 px-6 py-4" style="border-bottom: 1px solid #E2E8F0;">
        <span class="text-sm font-semibold" style="color: #1E293B;">Daftar HRD</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full" style="background: #EFF6FF; color: #1E3A8A;">
            <?= $hrdCount ?> akun
        </span>

        <!-- SEARCH -->
        <div class="ml-auto relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs">🔍</span>
            <input
                type="text"
                placeholder="Cari nama atau email..."
                class="pl-8 pr-3 py-1.5 text-xs rounded-lg outline-none"
                style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B; width: 200px;" />
        </div>
    </div>

    <!-- TABLE -->
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
                <?php foreach ($hrdList as $hrd): ?>
                    <?php
                    // Logika Inisial Nama (untuk cadangan jika foto kosong)
                    $nama = $hrd['nama_staff'];
                    $nameParts = explode(' ', trim($nama));
                    $initials = strtoupper(substr($nameParts[0], 0, 1));
                    if (isset($nameParts[1])) $initials .= strtoupper(substr($nameParts[1], 0, 1));

                    // Lokasi Foto
                    $fotoPath = "../../public/uploads/staff/" . $hrd['foto'];
                    ?>
                    <tr style="border-bottom: 1px solid #F1F5F9;" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='#FFFFFF'">

                        <!-- NAMA + FOTO -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <?php if (!empty($hrd['foto']) && file_exists(__DIR__ . "/../../public/uploads/staff/" . $hrd['foto'])): ?>
                                    <img src="<?= BASE_URL ?>public/uploads/staff/<?= $hrd['foto'] ?>"
                                        class="rounded-full object-cover"
                                        style="width: 34px; height: 34px; border: 1px solid #E2E8F0;">
                                <?php else: ?>
                                    <div class="flex items-center justify-center rounded-full text-xs font-semibold flex-shrink-0"
                                        style="width: 34px; height: 34px; background: #DBEAFE; color: #1E3A8A;">
                                        <?= $initials ?>
                                    </div>
                                <?php endif; ?>
                                <span class="font-semibold" style="color: #1E293B;"><?= htmlspecialchars($nama) ?></span>
                            </div>
                        </td>

                        <!-- EMAIL -->
                        <td class="px-6 py-4" style="color: #64748B;">
                            <?= htmlspecialchars($hrd['email']) ?>
                        </td>

                        <!-- STATUS -->
                        <td class="px-6 py-4">
                            <?php if ($hrd['status'] === 'active'): ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full"
                                    style="background: #DCFCE7; color: #166534;">
                                    <span class="rounded-full" style="width:6px; height:6px; background:#16A34A; display:inline-block;"></span>
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full"
                                    style="background: #FEE2E2; color: #991B1B;">
                                    <span class="rounded-full" style="width:6px; height:6px; background:#DC2626; display:inline-block;"></span>
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- DATE -->
                        <td class="px-6 py-4" style="color: #64748B;">
                            <?= date('d M Y', strtotime($hrd['created_at'])) ?>
                        </td>

                        <!-- ACTION -->
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end gap-2">
                                <!-- Tombol Ubah Status (Modal) -->
                                <button onclick="openStatusModal(<?= $hrd['user_id'] ?>, '<?= htmlspecialchars($hrd['nama_staff']) ?>', '<?= $hrd['status'] ?>')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition hover:bg-blue-100"
                                    style="background: #EFF6FF; color: #1E3A8A; border: 1px solid #BFDBFE;">
                                    🔄 Status
                                </button>

                                <!-- Tombol Hapus (Modal) -->
                                <button onclick="openDeleteModal(<?= $hrd['user_id'] ?>, '<?= htmlspecialchars($hrd['nama_staff']) ?>')"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg transition hover:bg-red-100"
                                    style="background: #FEF2F2; color: #991B1B; border: 1px solid #FECACA;">
                                    🗑️ Hapus
                                </button>
                            </div>
                        </td>

                    </tr>
                <?php endforeach; ?>

                <!-- EMPTY STATE -->
                <?php if (empty($hrdList)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-12" style="color: #94A3B8;">
                            <div class="flex flex-col items-center gap-2">
                                <span class="text-3xl">👥</span>
                                <span class="text-sm">Belum ada data HRD</span>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="flex items-center justify-between px-6 py-3" style="border-top: 1px solid #E2E8F0;">
        <span class="text-xs" style="color: #94A3B8;">
            Menampilkan <?= $hrdCount ?> dari <?= $hrdCount ?> data
        </span>
        <div class="flex gap-1">
            <button class="px-3 py-1 text-xs rounded-lg" style="border: 1px solid #E2E8F0; color: #64748B; background: #fff;">‹</button>
            <button class="px-3 py-1 text-xs rounded-lg font-semibold" style="background: #1E3A8A; color: #fff; border: none;">1</button>
            <button class="px-3 py-1 text-xs rounded-lg" style="border: 1px solid #E2E8F0; color: #64748B; background: #fff;">›</button>
        </div>
    </div>

</div>

<!-- MODAL STATUS -->
<div id="modalStatus" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-blue-100">
                <span class="text-2xl">🔄</span>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Ubah Status Staff?</h3>
            <p class="text-sm text-slate-500 mb-6">
                Anda akan mengubah status <span id="statusName" class="font-bold text-slate-700"></span>
                menjadi <span id="nextStatus" class="font-bold px-2 py-0.5 rounded-full bg-slate-100"></span>.
            </p>
            <div class="flex gap-3">
                <button onclick="closeModal('modalStatus')" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <a id="confirmStatusBtn" href="#" class="flex-1 py-2.5 text-sm font-semibold text-white rounded-xl transition" style="background: #1E3A8A;">Ya, Ubah</a>
            </div>
        </div>
    </div>
</div>

<!-- MODAL DELETE -->
<div id="modalDelete" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl w-full max-w-sm overflow-hidden shadow-2xl animate-fade-in-down">
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4 border border-red-100">
                <span class="text-2xl">⚠️</span>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Hapus Data HRD?</h3>
            <p class="text-sm text-slate-500 mb-6">
                Data <span id="deleteName" class="font-bold text-slate-700"></span> dan akun loginnya akan dihapus permanen. Tindakan ini tidak bisa dibatalkan!
            </p>
            <div class="flex gap-3">
                <button onclick="closeModal('modalDelete')" class="flex-1 py-2.5 text-sm font-semibold text-slate-600 bg-slate-100 rounded-xl hover:bg-slate-200 transition">Batal</button>
                <a id="confirmDeleteBtn" href="#" class="flex-1 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition">Ya, Hapus</a>
            </div>
        </div>
    </div>
</div>

<script>
    function openStatusModal(id, name, currentStatus) {
        const modal = document.getElementById('modalStatus');
        const nextStatus = currentStatus === 'active' ? 'Inactive' : 'Active';

        document.getElementById('statusName').innerText = name;
        document.getElementById('nextStatus').innerText = nextStatus;
        document.getElementById('confirmStatusBtn').href = 'edit.php?id=' + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function openDeleteModal(id, name) {
        const modal = document.getElementById('modalDelete');

        document.getElementById('deleteName').innerText = name;
        document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + id;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // Tutup modal jika klik di luar area modal
    window.onclick = function(event) {
        if (event.target.classList.contains('bg-slate-900/50')) {
            event.target.classList.add('hidden');
            event.target.classList.remove('flex');
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>