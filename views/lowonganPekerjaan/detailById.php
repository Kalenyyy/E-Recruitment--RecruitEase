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

$job = LowonganPekerjaanController::detailLowongan($conn, $id);
if (!$job) {
    die("Lowongan tidak ditemukan atau sudah ditutup.");
}

$skills = $job['skills'] ? explode(', ', $job['skills']) : [];
$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
$sudahMelamar = $candidate ? LamaranModel::hasApplied($conn, $candidate['id'], $id) : false;

$disabilityLabels = [
    'visual' => 'Visual',
    'hearing' => 'Pendengaran',
    'physical' => 'Fisik/Motorik',
    'intellectual' => 'Intelektual',
    'mental' => 'Mental',
    'speech' => 'Wicara'
];

ob_start();
?>

<div class="min-h-screen bg-[#F8FAFC] pb-20">

    <!-- STICKY HEADER RINGKAS -->
    <div class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-6 h-14 flex items-center justify-between">
            <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="text-slate-500 hover:text-blue-600 transition-colors font-bold text-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali
            </a>
            <div class="flex items-center gap-4">
                <?php if ($sudahMelamar): ?>
                    <span class="text-[10px] font-black text-emerald-600 uppercase tracking-widest bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-100">Lamaran Terkirim</span>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>views/lamaran/create.php?job_id=<?= $job['id'] ?>" class="text-[10px] font-black uppercase tracking-widest bg-blue-600 text-white px-5 py-2 rounded-xl shadow-lg shadow-blue-600/20 active:scale-95 transition-all">
                        Lamar Sekarang
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT AREA -->
    <div class="max-w-3xl mx-auto px-6 pt-10">

        <!-- HEADER SECTION -->
        <header class="mb-10">
            <div class="flex flex-wrap gap-2 mb-4">
                <span class="px-2.5 py-1 bg-white text-slate-500 text-[10px] font-bold uppercase tracking-widest rounded border border-slate-200"><?= $job['tipe_pekerjaan'] ?></span>
                <?php if ($job['is_disabilitas']): ?>
                    <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold uppercase tracking-widest rounded border border-blue-100">Inklusif</span>
                <?php endif; ?>
            </div>

            <h1 class="text-3xl md:text-4xl font-black text-slate-800 leading-tight tracking-tight mb-6">
                <?= htmlspecialchars($job['judul_job']) ?>
            </h1>

            <!-- Meta Info Grid (Lebih Rapat) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 py-6 border-y border-slate-200">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-xs font-bold text-slate-700"><?= htmlspecialchars($job['lokasi']) ?></p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gaji Bulanan</p>
                    <p class="text-xs font-bold text-emerald-600">
                        <?php
                        $min = $job['gaji_min'];
                        $max = $job['gaji_max'];

                        if ($min > 0 && $max > 0) {
                            // Rentang Gaji ringkas: Rp5.000k - 8.000k
                            echo "Rp" . number_format($min / 1000, 0, ',', '.') . "k - " . number_format($max / 1000, 0, ',', '.') . "k";
                        } elseif ($min > 0) {
                            // Hanya Min: Rp5.000k+
                            echo "Rp" . number_format($min / 1000, 0, ',', '.') . "k+";
                        } elseif ($max > 0) {
                            // Hanya Max: < Rp8.000k
                            echo "< Rp" . number_format($max / 1000, 0, ',', '.') . "k";
                        } else {
                            // Kosong
                            echo "Kompetitif";
                        }
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Metode Kerja</p>
                    <p class="text-xs font-bold text-slate-700"><?= $job['is_remote_work'] ? 'Remote' : 'On-site' ?></p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Diposting</p>
                    <p class="text-xs font-bold text-slate-700"><?= date('d M Y', strtotime($job['created_at'])) ?></p>
                </div>
            </div>
        </header>

        <!-- BODY SECTION (Gap Diperkecil) -->
        <div class="space-y-10">

            <!-- Deskripsi -->
            <section>
                <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">01. Deskripsi Pekerjaan</h2>
                <div class="text-slate-600 leading-relaxed text-base font-medium whitespace-pre-line">
                    <?= htmlspecialchars($job['deskripsi']) ?>
                </div>
            </section>

            <!-- Skills -->
            <?php if ($skills): ?>
                <section>
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-4">02. Keahlian</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach ($skills as $s): ?>
                            <div class="px-4 py-2 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-700 shadow-sm">
                                <?= htmlspecialchars($s) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Inklusivitas (Warna Lebih Solid) -->
            <?php if ($job['is_disabilitas']): ?>
                <section class="bg-[#1E293B] rounded-3xl p-8 text-white shadow-xl">
                    <div class="flex items-center gap-3 mb-4">
                        <i class="fa-solid fa-universal-access text-blue-400"></i>
                        <h2 class="text-sm font-black uppercase tracking-widest">Informasi Inklusif</h2>
                    </div>
                    <p class="text-slate-400 mb-6 text-xs font-medium leading-relaxed">Posisi ini secara aktif mendukung rekan-rekan disabilitas untuk bergabung dengan akomodasi kategori:</p>

                    <div class="flex flex-wrap gap-2 mb-6">
                        <?php foreach (($job['supported_disabilities'] ?? []) as $type): ?>
                            <span class="px-3 py-1.5 bg-white/10 border border-white/10 rounded-lg text-[9px] font-black uppercase tracking-widest">
                                <i class="fa-solid fa-check mr-1.5 text-blue-400"></i><?= $disabilityLabels[$type] ?? $type ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($job['additional_support']): ?>
                        <div class="p-4 bg-white/5 rounded-xl border border-white/5">
                            <p class="text-[9px] font-black text-blue-400 uppercase tracking-widest mb-1">Dukungan</p>
                            <p class="text-xs font-medium italic text-slate-300">"<?= htmlspecialchars($job['additional_support']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- PENAWARAN GAJI (Gak Gede Banget) -->
            <section class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-sm font-black text-slate-800 mb-1">Informasi Gaji</h2>
                    <p class="text-xs text-slate-500 font-medium tracking-tight">Kompensasi bulanan yang ditawarkan perusahaan.</p>
                </div>
                <div class="text-center md:text-right">
                    <p class="text-xl font-black text-blue-600">
                        <?php
                        $min = $job['gaji_min'];
                        $max = $job['gaji_max'];

                        if ($min > 0 && $max > 0) {
                            // Jika keduanya diisi
                            echo "Rp" . number_format($min, 0, ',', '.') . " - " . number_format($max, 0, ',', '.');
                        } elseif ($min > 0) {
                            // Jika hanya Min yang diisi
                            echo "Mulai dari Rp" . number_format($min, 0, ',', '.');
                        } elseif ($max > 0) {
                            // Jika hanya Max yang diisi
                            echo "Hingga Rp" . number_format($max, 0, ',', '.');
                        } else {
                            // Jika keduanya kosong atau null
                            echo "Gaji Kompetitif";
                        }
                        ?>
                    </p>
                    <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mt-0.5">Nett / Month</p>
                </div>
            </section>

            <!-- FOOTER CTA -->
            <div class="pt-8 pb-12 text-center">
                <?php if ($sudahMelamar): ?>
                    <div class="inline-flex items-center gap-3 px-8 py-4 bg-emerald-50 text-emerald-700 rounded-2xl font-black text-xs border border-emerald-100 uppercase tracking-widest">
                        <i class="fa-solid fa-circle-check"></i> Sudah Dilamar
                    </div>
                <?php else: ?>
                    <h3 class="text-xl font-black text-slate-800 mb-2 tracking-tight">Siap untuk melangkah maju?</h3>
                    <p class="text-xs text-slate-500 mb-6 font-medium">Pastikan data profil dan CV Anda sudah yang terbaru.</p>
                    <a href="<?= BASE_URL ?>views/lamaran/create.php?job_id=<?= $job['id'] ?>" class="inline-flex items-center gap-3 bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl font-black text-[11px] uppercase tracking-[0.2em] shadow-xl shadow-blue-600/30 transition-all active:scale-95">
                        Kirim Lamaran Sekarang <i class="fa-solid fa-paper-plane text-[10px]"></i>
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>