<?php
require_once __DIR__ . '/../../init.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$job = JobFormController::show($conn, $id);
if (!$job) die("Job not found");

// Mapping label disabilitas
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

<div class="min-h-screen bg-[#FDFDFD] pb-24">

    <!-- STICKY TOP BAR -->
    <div class="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-100">
        <div class="max-w-3xl mx-auto px-6 h-16 flex items-center justify-between">
            <a href="index.php" class="text-slate-400 hover:text-blue-600 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="flex items-center gap-4">
                <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg border <?= ($job['status'] === 'open') ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-200' ?>">
                    ● <?= strtoupper($job['status'] ?? 'DRAFT') ?>
                </span>
                <?php if ($job['status'] === 'draft'): ?>
                    <a href="edit.php?id=<?= $job['id'] ?>" class="text-[11px] font-black uppercase tracking-widest bg-blue-900 text-white px-5 py-2 rounded-xl shadow-lg shadow-blue-900/20 hover:bg-blue-800 transition-all">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Edit Lowongan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MAIN CONTENT CONTAINER -->
    <div class="max-w-3xl mx-auto px-6 pt-12">

        <!-- HEADER SECTION -->
        <header class="mb-12">
            <div class="flex flex-wrap gap-2 mb-6">
                <span class="px-2.5 py-1 bg-slate-100 text-slate-500 text-[10px] font-black uppercase tracking-widest rounded"><?= htmlspecialchars($job['nama_posisi']) ?></span>
                <span class="px-2.5 py-1 bg-blue-50 text-blue-600 text-[10px] font-black uppercase tracking-widest rounded"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></span>
            </div>

            <h1 class="text-4xl md:text-5xl font-black text-slate-900 leading-tight tracking-tighter mb-8">
                <?= htmlspecialchars($job['judul_job']) ?>
            </h1>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 py-8 border-y border-slate-100">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Lokasi</p>
                    <p class="text-sm font-bold text-slate-700"><?= htmlspecialchars($job['lokasi']) ?></p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Gaji Estimasi</p>
                    <p class="text-sm font-bold text-emerald-600">
                        <?php if (!empty($job['gaji_min'])): ?>
                            Rp<?= number_format($job['gaji_min'] / 1000, 0, ',', '.') ?>k+
                        <?php else: ?>
                            Kompetitif
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Metode</p>
                    <p class="text-sm font-bold text-slate-700"><?= $job['is_remote_work'] ? 'Remote' : 'On-site' ?></p>
                </div>
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Dibuat</p>
                    <p class="text-sm font-bold text-slate-700"><?= date('d M Y', strtotime($job['created_at'])) ?></p>
                </div>
            </div>
        </header>

        <!-- BODY CONTENT -->
        <div class="space-y-12">

            <!-- Deskripsi -->
            <section>
                <h2 class="text-xs font-black text-slate-300 uppercase tracking-[0.3em] mb-6">01. Deskripsi Pekerjaan</h2>
                <div class="text-slate-600 leading-relaxed text-lg font-medium whitespace-pre-line">
                    <?= htmlspecialchars($job['deskripsi']) ?>
                </div>
            </section>

            <!-- Keahlian -->
            <?php if (!empty($job['skills'])): ?>
                <section>
                    <h2 class="text-xs font-black text-slate-300 uppercase tracking-[0.3em] mb-6">02. Kebutuhan Keahlian</h2>
                    <div class="flex flex-wrap gap-2.5">
                        <?php foreach ($job['skills'] as $skill): ?>
                            <div class="px-5 py-2.5 bg-white border border-slate-200 rounded-2xl text-sm font-bold text-slate-700 shadow-sm">
                                <?= htmlspecialchars($skill) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Inklusivitas -->
            <?php if ($job['is_disabilitas']): ?>
                <section class="bg-[#1E293B] rounded-[2.5rem] p-10 text-white shadow-2xl">
                    <div class="flex items-center gap-4 mb-8">
                        <i class="fa-solid fa-universal-access text-2xl text-blue-400"></i>
                        <h2 class="text-xl font-black tracking-tight">Data Inklusivitas</h2>
                    </div>
                    <p class="text-slate-400 mb-8 font-medium leading-relaxed">Lowongan ini mendukung kandidat disabilitas untuk kategori berikut:</p>

                    <div class="flex flex-wrap gap-2 mb-8">
                        <?php foreach (($job['disability_types'] ?? []) as $type): ?>
                            <span class="px-4 py-2 bg-white/10 border border-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest">
                                <i class="fa-solid fa-check mr-2 text-blue-400"></i><?= $disabilityLabels[$type] ?? $type ?>
                            </span>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($job['additional_support']): ?>
                        <div class="p-6 bg-white/5 rounded-2xl border border-white/5">
                            <p class="text-xs font-black text-blue-400 uppercase tracking-widest mb-2">Dukungan Khusus</p>
                            <p class="text-sm font-medium italic text-slate-300">"<?= htmlspecialchars($job['additional_support']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <!-- Footer Meta -->
            <section class="pt-10 border-t border-slate-100">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="flex items-center justify-between p-5 rounded-[2rem] bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-video text-slate-400"></i>
                            <span class="text-sm font-bold text-slate-600">Online Interview</span>
                        </div>
                        <span class="text-[10px] font-black uppercase <?= $job['is_remote_interview'] ? 'text-blue-600' : 'text-slate-300' ?>">
                            <?= $job['is_remote_interview'] ? 'Mendukung' : 'Tidak' ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-5 rounded-[2rem] bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-house-laptop text-slate-400"></i>
                            <span class="text-sm font-bold text-slate-600">Status Lowongan</span>
                        </div>
                        <span class="text-[10px] font-black uppercase <?= ($job['status'] === 'open') ? 'text-emerald-600' : 'text-orange-500' ?>">
                            <?= $job['status'] ?>
                        </span>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>