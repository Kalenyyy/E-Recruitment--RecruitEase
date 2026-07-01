<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

// Ambil ID dari URL
$id = $_GET['id'] ?? null;
if (!$id)
    header("Location: index.php");

$hrd = StaffController::show($conn, $id);
if (!$hrd)
    die("Data tidak ditemukan");

ob_start();
?>

<!-- BREADCRUMB & TITLE -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold" style="color:#1E293B;">User Profile</h1>
        <div class="flex items-center gap-2 text-sm mt-1" style="color:#64748B;">
            <span>Home</span> <span style="color:#CBD5E1;">/</span> <span style="color:#1E3A8A;font-weight:600;">User
                Profile</span>
        </div>
    </div>
</div>

<div class="flex flex-col gap-6 mb-10">

    <!-- MAIN PROFILE & ADDRESS CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-8 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Profil & Alamat</h2>
            <button type="submit" form="mainForm"
                class="flex items-center gap-2 px-5 py-2 text-xs font-bold rounded-lg bg-blue-800 text-white hover:bg-blue-900 transition shadow-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Simpan Profil
            </button>
        </div>

        <form id="mainForm" action="<?= BASE_URL ?>public/actions/update_profile_staff.php" method="POST" enctype="multipart/form-data" class="p-8">
            <input type="hidden" name="id" value="<?= $hrd['id'] ?>">

            <!-- Profil Header -->
            <?php
            $nama = $hrd['nama_staff'];
            $nameParts = explode(' ', trim($nama));
            $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1));
            if (isset($nameParts[1])) {
                $initials .= strtoupper(substr($nameParts[1], 0, 1));
            }

            $fotoPathLocal = __DIR__ . "/../../public/uploads/staff/" . $hrd['foto'];
            $hasFoto = !empty($hrd['foto']) && file_exists($fotoPathLocal);
            ?>

            <div class="flex flex-col md:flex-row items-center gap-6 mb-8 pb-2">
                <div class="relative group">
                    <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-slate-50 shadow-sm bg-slate-100">
                        <?php if ($hasFoto): ?>
                            <img src="<?= BASE_URL ?>public/uploads/staff/<?= $hrd['foto'] ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-2xl font-bold"
                                style="background:#DBEAFE;color:#1E3A8A;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label
                        class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition text-white text-xs font-semibold">
                        Ganti Foto
                        <input type="file" name="foto" class="hidden" accept="image/*" onchange="previewImage(this)">
                    </label>
                </div>

                <div class="text-center md:text-left">
                    <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($nama) ?></h3>
                    <p class="text-sm text-slate-500 mt-0.5">Human Resources Development</p>
                </div>
            </div>

            <!-- Grid Input Data Pribadi -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Nama Lengkap -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Lengkap</label>
                    <input type="text" name="nama_staff" value="<?= htmlspecialchars($hrd['nama_staff']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Username -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Username</label>
                    <input type="text" name="username" value="<?= htmlspecialchars($hrd['username']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Email Address -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($hrd['email']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Phone Number -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Phone Number</label>
                    <input type="text" name="no_telp" value="<?= htmlspecialchars($hrd['no_telp']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Alamat Lengkap -->
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent resize-none transition-colors"><?= htmlspecialchars($hrd['alamat'] ?? '') ?></textarea>
                </div>
            </div>
        </form>
    </div>

</div>
<div id="toastMsg"
    class="fixed bottom-5 right-5 z-50 px-5 py-3 rounded-xl text-sm font-semibold shadow-lg transition-all duration-300 opacity-0 pointer-events-none">
</div>
<script>
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toastMsg');
        toast.textContent = msg;
        toast.className = `fixed bottom-5 right-5 z-50 px-5 py-3 rounded-xl text-sm font-semibold shadow-lg transition-all duration-300
        ${type === 'success' ? 'bg-blue-800 text-white' : 'bg-red-600 text-white'}`;
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'auto';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
        }, 3000);
    }

    // ===== AJAX Submit Main Form =====
    document.getElementById('mainForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const res = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                showToast('Profil berhasil disimpan.', 'success');
            } else {
                showToast(data.message ?? 'Gagal menyimpan profil.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    });
</script>

<style>
    input:focus,
    textarea:focus,
    select:focus {
        border-bottom-color: #1E3A8A !important;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>