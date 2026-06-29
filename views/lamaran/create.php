<?php
// views/lamaran/create.php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LamaranController.php';
require_once __DIR__ . '/../../controllers/LowonganPekerjaanController.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') {
    die("Access denied.");
}

$job_id = $_GET['job_id'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$job_id || !is_numeric($job_id)) {
    header("Location: " . BASE_URL . "views/lowonganPekerjaan/index.php");
    exit;
}

// 1. AMBIL DATA KANDIDAT VIA CONTROLLER
$candidate = LamaranController::getCandidateData($conn, $user_id);

if (!$candidate) {
    die("Error: Data kandidat tidak ditemukan.");
}

// 2. CEK KELENGKAPAN PROFIL
if (!LamaranController::isProfileComplete($candidate)) {
    // Jika tidak lengkap, arahkan ke halaman edit profile
    // Kita arahkan ke profile/index.php atau edit profile sambil bawa pesan error
    header("Location: " . BASE_URL . "views/candidate/profile.php?id=" . $candidate['id'] . "&msg=profile_incomplete");
    exit;
}

$candidate_id = $candidate['id'];

// 3. VALIDASI LOWONGAN VIA CONTROLLER
$lowongan = LowonganPekerjaanController::getById($conn, $job_id);
if (!$lowongan) {
    die("Lowongan pekerjaan tidak ditemukan.");
}

// 4. VALIDASI DUPLIKASI VIA CONTROLLER
if (LamaranController::checkExistingApply($conn, $candidate_id, $job_id)) {
    header("Location: " . BASE_URL . "views/lowonganPekerjaan/index.php?error=already_applied");
    exit;
}

// 5. PROSES SUBMIT
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expert_bidang = $_POST['expert_bidang'] ?? '';
    $pengalaman_bidang = $_POST['pengalaman_bidang'] ?? '';
    $catatan = $_POST['catatan'] ?? '';

    $sukses = LamaranController::kirimLamaran($conn, $candidate_id, $job_id, $catatan, $expert_bidang, $pengalaman_bidang);

    if ($sukses) {
        header("Location: " . BASE_URL . "views/lowonganPekerjaan/index.php?applied=success");
        exit;
    } else {
        $error_msg = "Gagal memproses data lamaran.";
    }
}

ob_start();
?>

<div class="w-auto mx-auto my-8 rounded-2xl overflow-hidden shadow-sm" style="background: #FFFFFF; border: 1px solid #E2E8F0; font-family: 'Inter', sans-serif;">

    <div class="px-6 py-5 border-b border-slate-100 bg-slate-50 flex items-center justify-center">
        <div class="flex items-center gap-2">
            <div id="circle-1" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 bg-blue-900 text-white shadow-sm">1</div>
            <div id="line-step" class="w-12 h-0.5 bg-slate-200 transition-all duration-300"></div>
            <div id="circle-2" class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 bg-slate-200 text-slate-600">2</div>
        </div>
    </div>

    <?php if (isset($error_msg)): ?>
        <div class="mx-6 mt-4 p-3 bg-red-50 text-red-700 border border-red-200 rounded-xl text-xs font-semibold">
            ⚠️ <?= $error_msg ?>
        </div>
    <?php endif; ?>

    <div class="p-6">
        <form id="multiStepForm" action="" method="POST" class="space-y-6">

            <div class="text-center mb-4">
                <p class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Anda Mendaftar Posisi</p>
                <h3 class="text-base font-bold text-slate-800"><?= htmlspecialchars($lowongan['judul_job']) ?></h3>
            </div>

            <div id="section-step-1" class="space-y-6 block">
                <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-100/70 mb-2">
                    <span class="text-xs font-bold text-blue-800 uppercase tracking-wide">Tahap 1: Screening Kualifikasi</span>
                    <p class="text-[11px] text-slate-500 mt-0.5">Isi data kompetensi software dan durasi pengalaman kerja Anda.</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2.5 text-slate-800">
                        Seberapa mahir kamu mengoperasikan software yang dibutuhkan? <span class="text-red-500">*</span>
                    </label>
                    <select name="expert_bidang" id="expert_bidang" required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none text-sm transition focus:border-blue-500"
                        style="color: #1E293B; background-color: #FAFBFF;">
                        <option value="" disabled selected>-- Pilih Tingkat Kemahiran --</option>
                        <option value="Baru Belajar">Baru Belajar</option>
                        <option value="Sedikit Mahir">Sedikit Mahir</option>
                        <option value="Cukup Mahir">Cukup Mahir</option>
                        <option value="Mahir">Mahir</option>
                        <option value="Sangat Mahir">Sangat Mahir</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2.5 text-slate-800">
                        Berapa tahun pengalaman yang kamu miliki dalam bidang ini? <span class="text-red-500">*</span>
                    </label>
                    <select name="pengalaman_bidang" id="pengalaman_bidang" required
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none text-sm transition focus:border-blue-500"
                        style="color: #1E293B; background-color: #FAFBFF;">
                        <option value="" disabled selected>-- Pilih Durasi Pengalaman --</option>
                        <option value="Tidak berpengalaman">Tidak berpengalaman</option>
                        <option value="Kurang dari 1 tahun">Kurang dari 1 tahun</option>
                        <option value="1 tahun">1 tahun</option>
                        <option value="2 tahun">2 tahun</option>
                        <option value="3 tahun">3 tahun</option>
                        <option value="4 tahun">4 tahun</option>
                        <option value="5 tahun">5 tahun</option>
                        <option value="Lebih dari 5 tahun">Lebih dari 5 tahun</option>
                    </select>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <?php
                    $backUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . 'views/lowonganPekerjaan/index.php';
                    ?>

                    <a href="<?= htmlspecialchars($backUrl) ?>"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">
                        Batal
                    </a>
                    <button type="button" onclick="goToStep2()"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-sm bg-blue-900 hover:bg-blue-800">
                        Lanjut Langkah Kedua →
                    </button>
                </div>
            </div>

            <div id="section-step-2" class="space-y-6 hidden">
                <div class="bg-emerald-50 text-emerald-800 p-4 rounded-xl border border-emerald-100 mb-2">
                    <span class="text-xs font-bold uppercase tracking-wide">Tahap 2: Catatan Tambahan</span>
                </div>

                <div>
                    <label class="block text-sm font-semibold mb-2.5 text-slate-800">Pesan Tambahan / Cover Letter <span class="text-slate-400 font-normal">(Opsional)</span></label>
                    <textarea name="catatan" rows="5"
                        class="w-full px-4 py-3 border border-slate-200 rounded-xl outline-none text-sm transition focus:border-blue-500 resize-none"
                        style="color: #1E293B; background-color: #FAFBFF;"
                        placeholder="Tulis ringkasan keahlian atau motivasi Anda di sini..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <button type="button" onclick="goToStep1()"
                        class="px-5 py-2.5 rounded-xl text-xs font-bold transition bg-slate-100 text-slate-600 hover:bg-slate-200">← Kembali</button>
                    <button type="submit"
                        class="px-6 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-sm bg-emerald-600 hover:bg-emerald-700">
                        ✓ Kirim Lamaran Sekarang
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

<script>
    function goToStep2() {
        const expert = document.getElementById('expert_bidang').value;
        const pengalaman = document.getElementById('pengalaman_bidang').value;

        if (!expert || !pengalaman) {
            alert("Harap pilih tingkat kemahiran dan durasi pengalaman kerja terlebih dahulu!");
            return;
        }

        document.getElementById('section-step-1').classList.replace('block', 'hidden');
        document.getElementById('section-step-2').classList.replace('hidden', 'block');

        document.getElementById('circle-1').classList.replace('bg-blue-900', 'bg-emerald-600');
        document.getElementById('line-step').classList.replace('bg-slate-200', 'bg-emerald-600');
        document.getElementById('circle-2').classList.replace('bg-slate-200', 'bg-emerald-600');
        document.getElementById('circle-2').classList.replace('text-slate-600', 'text-white');
    }

    function goToStep1() {
        document.getElementById('section-step-2').classList.replace('block', 'hidden');
        document.getElementById('section-step-1').classList.replace('hidden', 'block');

        document.getElementById('circle-1').classList.replace('bg-emerald-600', 'bg-blue-900');
        document.getElementById('line-step').classList.replace('bg-emerald-600', 'bg-slate-200');
        document.getElementById('circle-2').classList.replace('bg-emerald-600', 'bg-slate-200');
        document.getElementById('circle-2').classList.replace('text-white', 'text-slate-600');
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>