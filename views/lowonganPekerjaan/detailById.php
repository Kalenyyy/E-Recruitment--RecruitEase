<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LamaranController.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') {
    die("Access denied.");
}

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: " . BASE_URL . "views/lowonganPekerjaan/index.php");
    exit;
}

// Ambil data detail lowongan
$job = LowonganPekerjaanController::detailLowongan($conn, $id);
if (!$job) {
    die("Lowongan tidak ditemukan atau sudah ditutup.");
}

$skills = $job['skills'] ? explode(', ', $job['skills']) : [];

// Cek status lamaran
$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
$sudahMelamar = false;
if ($candidate) {
    $sudahMelamar = LamaranModel::hasApplied($conn, $candidate['id'], $id);
}

ob_start();
?>

<div class="min-h-screen bg-slate-50 py-12">
    <div class="max-w-6xl mx-auto px-6">

        <!-- Back Button -->
        <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="inline-flex items-center gap-2 text-sm font-bold text-slate-500 hover:text-indigo-600 transition-colors mb-8 group">
            <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Jelajahi Lowongan
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-10 items-start">

            <!-- Left Column: Content -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white border border-slate-200 rounded-[32px] p-8 md:p-12 shadow-sm">

                    <!-- Header Info -->
                    <div class="flex flex-col md:flex-row gap-8 items-start border-b border-slate-100 pb-10 mb-10">
                        <div class="w-20 h-20 bg-indigo-50 text-indigo-600 rounded-3xl flex items-center justify-center text-3xl font-black border border-indigo-100 shrink-0 shadow-sm">
                            <?= strtoupper(substr($job['judul_job'], 0, 1)) ?>
                        </div>
                        <div class="flex-1">
                            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight leading-tight"><?= htmlspecialchars($job['judul_job']) ?></h1>
                            <div class="flex items-center gap-2 text-slate-500 font-semibold mt-2">
                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <?= htmlspecialchars($job['lokasi']) ?>
                            </div>
                            <div class="flex flex-wrap gap-2 mt-6">
                                <span class="bg-slate-100 text-slate-600 px-4 py-1.5 rounded-full text-[11px] font-bold uppercase tracking-widest border border-slate-200"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></span>
                                <?php if ($job['is_disabilitas']): ?>
                                    <span class="bg-teal-50 text-teal-700 px-4 py-1.5 rounded-full text-[11px] font-bold border border-teal-100">✓ RAMAH DISABILITAS</span>
                                <?php endif; ?>
                                <?php if ($job['is_remote_work']): ?>
                                    <span class="bg-indigo-50 text-indigo-700 px-4 py-1.5 rounded-full text-[11px] font-bold border border-indigo-100 text-nowrap">🌐 REMOTE WORK</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description -->
                    <section class="mb-12">
                        <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-indigo-600 rounded-full"></span>
                            Deskripsi Pekerjaan
                        </h3>
                        <div class="text-slate-600 leading-relaxed whitespace-pre-line text-lg">
                            <?= htmlspecialchars($job['deskripsi'] ?? 'Tidak ada deskripsi yang disediakan.') ?>
                        </div>
                    </section>

                    <!-- Accessibility Info -->
                    <section class="mb-12">
                        <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                            <span class="w-1.5 h-6 bg-teal-500 rounded-full"></span>
                            Aksesibilitas & Kategori Disabilitas
                        </h3>

                        <?php if ($job['is_disabilitas']): ?>
                            <div class="bg-teal-50 border border-teal-200 rounded-2xl p-6 mb-8">
                                <div class="flex items-center gap-3 text-teal-800 font-extrabold text-lg">
                                    <span>♿</span> Lowongan Ini Terbuka untuk Penyandang Disabilitas
                                </div>
                            </div>

                            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-6">Ragam Disabilitas yang Didukung:</p>

                            <?php if (!empty($job['supported_disabilities'])): ?>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <?php foreach ($job['supported_disabilities'] as $typeKey):
                                        if (isset($job['disability_options'][$typeKey])):
                                            $detail = $job['disability_options'][$typeKey];
                                    ?>
                                            <div class="bg-white border border-teal-100 border-l-4 border-l-teal-500 p-5 rounded-2xl shadow-sm">
                                                <h4 class="font-bold text-teal-700 mb-1">✓ <?= htmlspecialchars($detail['label']) ?></h4>
                                                <p class="text-sm text-slate-500 leading-snug"><?= htmlspecialchars($detail['desc']) ?></p>
                                            </div>
                                    <?php endif;
                                    endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="bg-slate-50 border-2 border-dashed border-slate-200 p-8 rounded-2xl text-center italic text-slate-500">
                                    Terbuka untuk semua ragam disabilitas. Silakan ajukan berkas Anda.
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($job['additional_support'])): ?>
                                <div class="mt-8 p-6 bg-indigo-50 border border-indigo-100 rounded-2xl">
                                    <strong class="block text-[10px] font-black text-indigo-600 uppercase tracking-widest mb-2">Dukungan & Fasilitas:</strong>
                                    <p class="text-slate-700 font-medium leading-relaxed"><?= htmlspecialchars($job['additional_support']) ?></p>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="bg-rose-50 border border-rose-100 text-rose-700 rounded-2xl p-6 font-bold flex items-center gap-3">
                                <span>⚠️</span> Lowongan ini belum mendukung jalur khusus disabilitas.
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- Required Skills -->
                    <?php if ($skills): ?>
                        <section>
                            <h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3">
                                <span class="w-1.5 h-6 bg-amber-500 rounded-full"></span>
                                Keahlian yang Dibutuhkan
                            </h3>
                            <div class="flex flex-wrap gap-3">
                                <?php foreach ($skills as $s): ?>
                                    <span class="bg-slate-100 text-slate-700 px-5 py-2 rounded-xl text-sm font-bold border border-slate-200"><?= htmlspecialchars($s) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Sidebar Action -->
            <aside class="lg:col-span-1">
                <div class="bg-white border border-slate-200 rounded-[32px] p-8 sticky top-10 shadow-xl shadow-slate-200/50">
                    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6 mb-8">
                        <label class="block text-[10px] font-black text-amber-600 uppercase tracking-widest mb-2">Estimasi Gaji Bulanan</label>
                        <p class="text-2xl font-black text-amber-700">
                            <?= $job['gaji'] ? 'Rp ' . number_format($job['gaji'], 0, ',', '.') : 'Gaji Kompetitif' ?>
                        </p>
                    </div>

                    <?php if ($sudahMelamar): ?>
                        <div class="bg-indigo-50 border border-indigo-100 text-indigo-800 rounded-2xl p-6 text-sm font-semibold leading-relaxed mb-6">
                            🎉 Anda sudah melamar posisi ini. Pantau terus status lamaran Anda di dashboard.
                        </div>
                        <button disabled class="w-full py-5 bg-slate-100 text-slate-400 font-black rounded-2xl cursor-not-allowed">
                            ✓ SUDAH DILAMAR
                        </button>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>views/lamaran/create.php?job_id=<?= $job['id'] ?>"
                            class="block w-full py-5 bg-indigo-600 text-white text-center font-black rounded-2xl shadow-lg shadow-indigo-200 hover:bg-indigo-700 transition-all active:scale-95 mb-4 uppercase tracking-widest text-sm">
                            Lamar Sekarang
                        </a>
                    <?php endif; ?>

                    <p class="text-center text-xs text-slate-400 font-medium px-4 mt-6 leading-relaxed">
                        Pastikan Profil, Foto, dan CV Anda sudah lengkap dan terbaru sebelum menekan tombol lamar.
                    </p>
                </div>
            </aside>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>