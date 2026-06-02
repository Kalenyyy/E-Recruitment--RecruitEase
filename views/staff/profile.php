<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

// Ambil ID dari URL
$id = $_GET['id'] ?? null;
if (!$id) header("Location: index.php");

$hrd = StaffController::show($conn, $id);
if (!$hrd) die("Data tidak ditemukan");

ob_start();
?>

<!-- BREADCRUMB & TITLE -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold" style="color: #1E293B;">User Profile</h1>
        <div class="flex items-center gap-2 text-xs" style="color: #64748B;">
            <span>Home</span> <span>›</span> <span style="color: #1E3A8A;">User Profile</span>
        </div>
    </div>
</div>

<div class="flex flex-col gap-6 mb-10">

    <!-- SECTION 1: MY PROFILE -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-bottom border-slate-100">
            <h2 class="font-bold text-slate-800">My Profile</h2>
            <button type="submit" form="mainForm" class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                ✏️ Edit
            </button>
        </div>

        <form id="mainForm" action="update_process.php" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="id" value="<?= $hrd['id'] ?>">

            <!-- Profil Header -->
            <?php
            // 1. Logika Inisial Nama
            $nama = $hrd['nama_staff'];
            $nameParts = explode(' ', trim($nama));
            $initials = strtoupper(substr($nameParts[0], 0, 1));
            if (isset($nameParts[1])) {
                $initials .= strtoupper(substr($nameParts[1], 0, 1));
            }

            // 2. Cek apakah file foto ada di server
            $fotoPathLocal = __DIR__ . "/../../public/uploads/staff/" . $hrd['foto'];
            $hasFoto = !empty($hrd['foto']) && file_exists($fotoPathLocal);
            ?>

            <div class="flex items-center gap-5 mb-8">
                <div class="relative group">

                    <!-- CONTAINER FOTO / INISIAL -->
                    <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-slate-100 flex-shrink-0">
                        <?php if ($hasFoto): ?>
                            <!-- Tampilkan Foto jika ada -->
                            <img src="<?= BASE_URL ?>public/uploads/staff/<?= $hrd['foto'] ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <!-- Tampilkan Inisial jika foto kosong -->
                            <div class="w-full h-full flex items-center justify-center text-xl font-bold"
                                style="background: #DBEAFE; color: #1E3A8A;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <label class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition text-white text-[10px] font-semibold">
                        Change
                        <input type="file" name="foto" class="hidden" onchange="previewImage(this)">
                    </label>
                </div>

                <div>
                    <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($nama) ?></h3>
                    <p class="text-sm text-slate-500">
                        HRD </span>
                    </p>
                </div>
            </div>

            <!-- Grid Input -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Nama Lengkap</label>
                    <input type="text" name="nama_staff" value="<?= $hrd['nama_staff'] ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-600 outline-none transition bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Username</label>
                    <input type="text" name="username" value="<?= $hrd['username'] ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-600 outline-none transition bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Email Address</label>
                    <input type="email" name="email" value="<?= $hrd['email'] ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-600 outline-none transition bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Phone</label>
                    <input type="text" name="no_telp" value="<?= $hrd['no_telp'] ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-600 outline-none transition bg-transparent">
                </div>
            </div>
        </form>
    </div>

    <!-- SECTION 2: ADDRESS -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-50">
            <h2 class="font-bold text-slate-800">Address</h2>
            <button type="submit" form="mainForm" class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                ✏️ Edit
            </button>
        </div>
        <div class="p-6">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-500">Alamat Lengkap</label>
                <textarea form="mainForm" name="alamat" rows="2"
                    class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-600 outline-none transition bg-transparent resize-none"><?= $hrd['alamat'] ?></textarea>
            </div>
        </div>
    </div>

    <!-- SECTION 3: SECURITY -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-50">
            <h2 class="font-bold text-slate-800">Security</h2>
        </div>
        <div class="p-6 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-semibold text-slate-800">Change Password</h4>
                <p class="text-xs text-slate-500">Perbarui kata sandi Anda secara berkala untuk menjaga keamanan akun.</p>
            </div>
            <a href="change_password.php?id=<?= $hrd['id'] ?>"
                class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 hover:bg-slate-50 transition flex items-center gap-2">
                🔒 Change Password
            </a>
        </div>
    </div>

    <!-- SECTION 4: DANGER ZONE -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-50">
            <h2 class="font-bold text-slate-800">Danger Zone</h2>
        </div>
        <div class="p-6 flex items-center justify-between border-b border-slate-50">
            <div>
                <h4 class="text-sm font-semibold text-slate-800">Delete account</h4>
                <p class="text-xs text-slate-500">Setelah Anda menghapus akun, data ini tidak dapat dikembalikan. Harap berhati-hati.</p>
            </div>
            <a href="delete.php?id=<?= $hrd['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus akun ini secara permanen?')"
                class="px-4 py-2 text-xs font-semibold rounded-xl text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 transition">
                Delete account
            </a>
        </div>
    </div>

</div>

<style>
    /* Styling input agar mirip gambar (border bawah tipis saat tidak fokus) */
    input:focus,
    textarea:focus {
        border-bottom-color: #1E3A8A !important;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>