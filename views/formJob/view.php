<?php
require_once __DIR__ . '/../../init.php';

$job = JobFormController::show($conn, $_GET['id']);
if (!$job) die("Job not found");

ob_start();
?>

<style>
    .job-description h3,
    .job-description strong {
        color: #1E293B;
        font-weight: 700;
    }

    .job-description {
        line-height: 1.8;
        color: #475569;
    }

    .skill-tag {
        transition: background 0.15s, color 0.15s, border-color 0.15s;
    }

    .skill-tag:hover {
        background: #1E3A8A !important;
        color: #ffffff !important;
        border-color: #1E3A8A !important;
    }
</style>

<div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8 pb-20" style="background: #F8FAFC; min-height: 100vh;">

    <!-- NAV -->
    <div class="flex items-center justify-between py-6">
        <a href="index.php"
            class="flex items-center gap-2 text-slate-500 hover:text-blue-600 transition-all font-medium text-sm
                  px-4 py-2 rounded-xl border border-slate-200 bg-white hover:border-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Daftar
        </a>
        <a href="edit.php?id=<?= $job['id'] ?>"
            class="flex items-center gap-2 px-5 py-2 bg-white text-[#1E3A8A] border border-[#1E3A8A]
                  rounded-xl font-semibold text-sm hover:bg-[#1E3A8A] hover:text-white transition-all">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Lowongan
        </a>
    </div>

    <!-- HERO -->
    <div class="relative overflow-hidden rounded-2xl p-8 md:p-12 mb-8" style="background: #1E3A8A;">
        <!-- Dekorasi -->
        <div class="absolute top-0 right-0 w-72 h-72 rounded-full -mt-16 -mr-16"
            style="background: rgba(59,130,246,0.08);"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 rounded-full -mb-12 -ml-12"
            style="background: rgba(99,102,241,0.06);"></div>

        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
            <div class="max-w-2xl">
                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-3 mb-5">
                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest"
                        style="background: rgba(59,130,246,0.2); color: #93C5FD; border: 1px solid rgba(59,130,246,0.3);">
                        <?= htmlspecialchars($job['nama_posisi']) ?>
                    </span>
                    <?php
                    $isOpen = $job['status'] === 'open';
                    $statusBg = $isOpen ? 'rgba(16,185,129,0.18)' : 'rgba(245,158,11,0.18)';
                    $statusColor = $isOpen ? '#6EE7B7' : '#FCD34D';
                    $statusBorder = $isOpen ? 'rgba(16,185,129,0.3)' : 'rgba(245,158,11,0.3)';
                    ?>
                    <span class="px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-widest"
                        style="background: <?= $statusBg ?>; color: <?= $statusColor ?>; border: 1px solid <?= $statusBorder ?>;">
                        ● <?= $job['status'] ?>
                    </span>
                </div>

                <h1 class="text-3xl md:text-5xl font-bold text-white mb-6 leading-tight">
                    <?= htmlspecialchars($job['judul_job']) ?>
                </h1>

                <!-- Meta Info -->
                <div class="flex flex-wrap gap-5">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-sm"
                            style="background: rgba(255,255,255,0.08);">📍</span>
                        <span class="text-slate-300 text-sm font-medium"><?= htmlspecialchars($job['lokasi']) ?></span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-sm"
                            style="background: rgba(255,255,255,0.08);">💰</span>
                        <span class="text-white font-bold text-base">
                            <?= $job['gaji'] ? 'Rp ' . number_format($job['gaji'], 0, ',', '.') : 'Gaji Kompetitif' ?>
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg text-sm"
                            style="background: rgba(255,255,255,0.08);">⏰</span>
                        <span class="text-slate-300 text-sm font-medium"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Date Box -->
            <div class="flex flex-col items-center text-center px-8 py-5 rounded-xl flex-shrink-0"
                style="background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12);">
                <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color: #94A3B8;">Diterbitkan</p>
                <p class="text-3xl font-bold text-white leading-none"><?= date('d', strtotime($job['created_at'])) ?></p>
                <p class="text-xs font-bold uppercase tracking-widest mt-1" style="color: #3B82F6;">
                    <?= date('M Y', strtotime($job['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

        <!-- LEFT (col-8) -->
        <div class="lg:col-span-8 space-y-6">

            <!-- Detail Pekerjaan -->
            <div class="bg-white rounded-2xl p-8 border border-slate-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0"
                        style="background: #EFF6FF; color: #1E3A8A;">01</div>
                    <h2 class="text-xl font-bold" style="color: #1E293B;">Detail Pekerjaan</h2>
                </div>
                <div class="job-description prose prose-slate max-w-none">
                    <?= nl2br(htmlspecialchars($job['deskripsi'])) ?>
                </div>
            </div>

            <!-- Keahlian -->
            <div class="bg-white rounded-2xl p-8 border border-slate-100">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm font-bold flex-shrink-0"
                        style="background: #EEF2FF; color: #4338CA;">02</div>
                    <h2 class="text-xl font-bold" style="color: #1E293B;">Kebutuhan Keahlian</h2>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php if (!empty($job['skills'])): ?>
                        <?php foreach ($job['skills'] as $skillName): ?>
                            <span class="skill-tag px-5 py-2 rounded-xl text-sm font-semibold cursor-default"
                                style="background: #F8FAFC; color: #334155; border: 0.5px solid #E2E8F0;">
                                # <?= htmlspecialchars($skillName) ?>
                            </span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="w-full p-6 text-center rounded-xl border border-dashed border-slate-200 text-slate-400 text-sm">
                            Tidak ada spesifikasi keahlian khusus
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDEBAR (col-4) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- Inklusif Card -->
            <?php if ($job['is_disabilitas']): ?>
                <div class="rounded-2xl p-7 text-white" style="background: #1E3A8A;">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                            style="background: rgba(255,255,255,0.15);">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold">Lowongan Inklusif</h3>
                    </div>
                    <p class="text-sm mb-4 leading-relaxed" style="color: #BFDBFE;">
                        Kami membuka pintu seluas-luasnya untuk rekan-rekan dengan disabilitas:
                    </p>
                    <div class="space-y-2 mb-4">
                        <?php foreach ($job['disability_types'] as $type): ?>
                            <div class="flex items-center gap-2 px-3 py-2 rounded-lg"
                                style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.12);">
                                <span style="color: #93C5FD;" class="font-bold text-xs">✔</span>
                                <span class="text-xs font-bold uppercase tracking-wider"><?= htmlspecialchars($type) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php if ($job['additional_support']): ?>
                        <div class="p-4 rounded-xl" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.06);">
                            <p class="text-xs font-bold uppercase tracking-widest mb-2" style="color: #93C5FD;">Support System</p>
                            <p class="text-sm italic" style="color: #E0F2FE;">"<?= htmlspecialchars($job['additional_support']) ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Informasi Tambahan -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100">
                <h3 class="text-xs font-bold uppercase tracking-widest mb-4" style="color: #94A3B8;">Informasi Tambahan</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 rounded-xl" style="background: #F8FAFC; border: 0.5px solid #E9EEF4;">
                        <span class="text-sm font-semibold" style="color: #64748B;">Remote Kerja</span>
                        <span class="text-xs font-bold uppercase tracking-wide <?= $job['is_remote_work'] ? 'text-blue-600' : 'text-slate-300' ?>">
                            <?= $job['is_remote_work'] ? 'Mendukung' : 'Tidak' ?>
                        </span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl" style="background: #F8FAFC; border: 0.5px solid #E9EEF4;">
                        <span class="text-sm font-semibold" style="color: #64748B;">Interview Online</span>
                        <span class="text-xs font-bold uppercase tracking-wide <?= $job['is_remote_interview'] ? 'text-blue-600' : 'text-slate-300' ?>">
                            <?= $job['is_remote_interview'] ? 'Mendukung' : 'Tidak' ?>
                        </span>
                    </div>
                </div>

                <!-- CTA -->
                <div class="mt-6 p-5 rounded-xl text-center" style="background: #1E293B;">
                    <p class="text-xs font-bold uppercase tracking-widest mb-3" style="color: #64748B;">Siap Memproses Lowongan?</p>
                    <button class="w-full py-3 rounded-xl font-bold text-sm text-white transition-all hover:opacity-90"
                        style="background: #3B82F6;">
                        PUBLIKASIKAN SEKARANG
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>