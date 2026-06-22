<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

// Proteksi Akses
AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$job_id = $_GET['job_id'] ?? null;

if (!$job_id || !is_numeric($job_id)) {
    header("Location: " . BASE_URL . "views/pelamarPekerjaan/index.php");
    exit;
}

// Ambil data detail lowongan dan daftar pelamarnya
$jobDetails = PelamarPekerjaanController::getDetailJob($conn, $job_id);
$applicants = PelamarPekerjaanController::getApplicants($conn, $job_id);

if (!$jobDetails) {
    die("Data lowongan tidak ditemukan.");
}

ob_start();
?>

<!-- PAGE HEADER -->
<div class="flex items-center justify-between mb-8 font-sans">
    <div class="flex items-start gap-4">
        <div class="inline-flex items-center justify-center rounded-2xl flex-shrink-0" style="width:52px;height:52px;background:linear-gradient(135deg,#1E3A8A,#2563EB);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Pelamar: <?= htmlspecialchars($jobDetails['judul_job']) ?></h1>
            <p class="text-sm text-slate-500 mt-1">Posisi: <span class="font-semibold text-slate-700"><?= htmlspecialchars($jobDetails['nama_posisi']) ?></span> &mdash; Kelola berkas dan tahap seleksi kandidat</p>
        </div>
    </div>
    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-5 py-2.5 rounded-xl transition hover:shadow-md flex-shrink-0"
        style="background: #F1F5F9; color: #475569; border: 1px solid #CBD5E1;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
        Kembali
    </a>
</div>

<!-- MAIN CARD -->
<div class="rounded-2xl overflow-hidden shadow-md" style="background: #FFFFFF; border: 1px solid #E2E8F0;">

    <!-- CARD HEADER -->
    <div class="px-8 py-6 flex items-center justify-between" style="background: linear-gradient(135deg, #1E3A8A, #2563EB);">
        <div class="flex items-center gap-4">
            <span class="inline-flex items-center justify-center" style="width:48px;height:48px;border-radius:14px;background:rgba(255,255,255,0.15);">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </span>
            <div>
                <h2 class="font-bold text-xl text-white">Daftar Kandidat</h2>
                <p class="text-sm text-blue-200 mt-0.5">Total pelamar: <span class="font-bold text-white"><?= count($applicants) ?></span> kandidat</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <?php if (empty($applicants)): ?>
            <!-- EMPTY STATE -->
            <div class="text-center py-20">
                <span class="inline-flex items-center justify-center mb-5" style="width:80px;height:80px;border-radius:50%;background:#F1F5F9;color:#94A3B8;">
                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"></path><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"></path></svg>
                </span>
                <p class="text-lg font-bold text-slate-700">Belum ada pelamar untuk lowongan ini</p>
                <p class="text-sm text-slate-400 mt-2">Data pelamar baru akan muncul begitu kandidat menekan tombol kirim lamaran.</p>
            </div>
        <?php else: ?>
            <div class="space-y-5">
                <?php foreach ($applicants as $app): ?>
                   <div class="bg-white rounded-3xl border border-slate-100 shadow-[0_8px_30px_rgb(0,0,0,0.06)] overflow-hidden p-6">
                        <div class="p-6" style="background: #FFFFFF;">
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

                                <!-- KANDIDAT INFO -->
                                <div class="lg:col-span-1">
                                <div class="flex items-start gap-4">

                                <!-- Avatar -->
                                <div class="flex-shrink-0 flex items-center justify-center font-bold text-white"
                                     style="width:56px;height:56px;border-radius:14px;background:#1E40AF;font-size:24px;">
                                <?= strtoupper(substr($app['nama_lengkap'], 0, 1)) ?>
                                </div>

                                <div class="flex-1 min-w-0">
                                 <!-- Nama -->
                                    <h3 style="font-size:17px;font-weight:600;color:#0F172A;margin:0 0 4px;line-height:1.35;">
                                 <?= htmlspecialchars($app['nama_lengkap']) ?>
                                    </h3>

                                 <!-- Tanggal melamar -->
                                 <p class="flex items-center gap-1.5" style="font-size:13px;color:#94A3B8;margin:0;">
                                 <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                 Melamar <?= date('d M Y', strtotime($app['tanggal_melamar'])) ?>
                                 </p>

                                 <!-- Pemisah -->
                                    <div style="height:1px;background:#E2E8F0;margin:12px 0;"></div>

                                 <!-- Email -->
                                <p class="flex items-center gap-2 truncate" style="font-size:14px;color:#334155;margin:0 0 8px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:#94A3B8;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                     <?= htmlspecialchars($app['email']) ?>
                                 </p>

                                 <!-- No HP -->
                                 <p class="flex items-center gap-2" style="font-size:14px;color:#334155;margin:0;">
                                 <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;color:#94A3B8;"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                 <?= htmlspecialchars($app['no_hp']) ?>
                                 </p>
                                </div>
                            </div>
                        </div>
                                <!-- KUALIFIKASI -->
                                <div class="lg:col-span-1">
                                    <div class="space-y-3">
                                        <div class="p-3.5 rounded-xl" style="background: #EFF6FF; border: 1px solid #BFDBFE;">
                                            <span class="flex items-center gap-1.5 text-[11px] font-extrabold tracking-wider uppercase text-blue-600 mb-1.5">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                                Keahlian
                                            </span>
                                            <p class="text-sm font-semibold text-slate-800">
                                                <?= (!empty($app['expert_bidang']) && $app['expert_bidang'] !== '-') ? htmlspecialchars($app['expert_bidang']) : '<span class="text-slate-400 font-normal italic text-xs">Tidak ditentukan</span>' ?>
                                            </p>
                                        </div>

                                        <div class="p-3.5 rounded-xl" style="background: #FFFBEB; border: 1px solid #FDE68A;">
                                            <span class="flex items-center gap-1.5 text-[11px] font-extrabold tracking-wider uppercase text-amber-600 mb-1.5">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                                Pengalaman
                                            </span>
                                            <p class="text-sm font-semibold text-amber-900">
                                                <?= (!empty($app['pengalaman_bidang']) && $app['pengalaman_bidang'] !== '-') ? htmlspecialchars($app['pengalaman_bidang']) : '<span class="text-amber-600/60 font-normal italic text-xs">Fresh Graduate</span>' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- STATUS -->
                                <div class="lg:col-span-1 flex items-center justify-center">
                                    <?php 
                                    $status = $app['status_lamaran'];
                                    $badgeStyle = "bg-slate-100 text-slate-700"; 
                                    $statusIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>';
                                    $statusColor = '#64748B';
                                    if ($status === 'ADMINISTRASI') {
                                        $badgeStyle = "bg-amber-100 text-amber-800 border border-amber-300";
                                        $statusIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>';
                                        $statusColor = '#B45309';
                                    }
                                    if ($status === 'INTERVIEW') {
                                        $badgeStyle = "bg-blue-100 text-blue-800 border border-blue-300";
                                        $statusIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path><path d="M19 10v2a7 7 0 0 1-14 0v-2"></path><line x1="12" y1="19" x2="12" y2="23"></line><line x1="8" y1="23" x2="16" y2="23"></line></svg>';
                                        $statusColor = '#1D4ED8';
                                    }
                                    if ($status === 'DITERIMA') {
                                        $badgeStyle = "bg-emerald-100 text-emerald-800 border border-emerald-300";
                                        $statusIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
                                        $statusColor = '#047857';
                                    }
                                    if ($status === 'DITOLAK') {
                                        $badgeStyle = "bg-rose-100 text-rose-800 border border-rose-300";
                                        $statusIcon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                                        $statusColor = '#BE123C';
                                    }
                                    ?>
                                    <div class="text-center">
                                        <div class="flex items-center justify-center mb-2" style="color:<?= $statusColor ?>;"><?= $statusIcon ?></div>
                                        <span class="inline-block px-4 py-1.5 text-xs font-bold rounded-full <?= $badgeStyle ?>">
                                            <?= $status ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- AKSI -->
                                <div class="lg:col-span-1 flex flex-col gap-2.5 justify-center">
                                    <?php if (!empty($app['cv_file'])): ?>
                                        <a href="<?= BASE_URL . 'public/uploads/cv/' . $app['cv_file'] ?>" target="_blank"
                                           class="inline-flex items-center justify-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl transition hover:shadow-md"
                                           style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                            Lihat CV
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic text-center py-2">Tidak ada CV</span>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/riwayat_pelamar.php?id_transaksi=<?= $app['id_transaksi'] ?>"
                                       class="inline-flex items-center justify-center gap-2 text-sm font-bold px-4 py-2.5 rounded-xl text-white transition hover:shadow-lg"
                                       style="background: linear-gradient(135deg, #1E3A8A, #2563EB);">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"></path></svg>
                                        Detail
                                    </a>

                                    <?php if ($status === 'ADMINISTRASI'): ?>
                                        <div class="grid grid-cols-2 gap-2">
                                            <button type="button"
                                                data-action="INTERVIEW"
                                                data-transaksi="<?= $app['id_transaksi'] ?>"
                                                data-name="<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>"
                                                class="js-open-status-modal text-xs font-bold px-2 py-2 rounded-xl text-white transition hover:shadow-md flex items-center justify-center gap-1.5"
                                                style="background: linear-gradient(135deg, #10B981, #059669);">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                                Lolos
                                            </button>
                                            <button type="button"
                                                data-action="DITOLAK"
                                                data-transaksi="<?= $app['id_transaksi'] ?>"
                                                data-name="<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>"
                                                class="js-open-status-modal text-xs font-bold px-2 py-2 rounded-xl text-white transition hover:shadow-md flex items-center justify-center gap-1.5"
                                                style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                                Tolak
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL KONFIRMASI STATUS -->
<div id="status-confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl transform transition-all">
        <div class="flex items-start justify-between gap-4 mb-6">
            <h3 id="modal-title" class="text-xl font-bold text-slate-900 flex items-center gap-2">Konfirmasi Status</h3>
            <button type="button" id="modal-close" class="text-slate-400 hover:text-slate-600 transition">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form id="status-confirm-form" method="POST" class="space-y-5">
            <input type="hidden" name="status_lamaran" id="modal-status" value="">
            
            <div class="rounded-2xl border-2 p-5" id="modal-info-box">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-400 mb-3">Informasi Pelamar</p>
                <p id="modal-candidate-name" class="text-lg font-bold text-slate-900 mb-1.5"></p>
                <p id="modal-action-label" class="text-sm text-slate-600 flex items-center gap-2"></p>
            </div>

            <div class="rounded-2xl p-4" id="modal-status-box" style="background: #EFF6FF; border: 1px solid #BFDBFE;">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-600 mb-2">Status yang akan diubah</p>
                <div class="flex items-center gap-2">
                    <span id="modal-status-icon" class="inline-flex items-center justify-center"></span>
                    <p id="modal-status-text" class="font-bold text-base text-blue-900"></p>
                </div>
            </div>

            <div class="flex gap-3 pt-1">
                <button type="button" id="modal-cancel" class="flex-1 rounded-xl border-2 border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-600 hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button type="submit" id="modal-confirm" class="flex-1 rounded-xl text-white px-4 py-3 text-sm font-bold transition-all hover:shadow-lg"
                        style="background: linear-gradient(135deg, #1E3A8A, #2563EB);">
                    Ya, Lanjutkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('status-confirm-modal');
    const openButtons = document.querySelectorAll('.js-open-status-modal');
    const closeButton = document.getElementById('modal-close');
    const cancelButton = document.getElementById('modal-cancel');
    const modalTitle = document.getElementById('modal-title');
    const candidateName = document.getElementById('modal-candidate-name');
    const actionLabel = document.getElementById('modal-action-label');
    const modalStatus = document.getElementById('modal-status');
    const modalStatusText = document.getElementById('modal-status-text');
    const modalStatusIcon = document.getElementById('modal-status-icon');
    const modalInfoBox = document.getElementById('modal-info-box');
    const modalStatusBox = document.getElementById('modal-status-box');
    const form = document.getElementById('status-confirm-form');

    const ICON_CHECK_CIRCLE = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
    const ICON_ALERT = '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>';
    const ICON_CHECK_SMALL = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
    const ICON_X_SMALL = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';

    function closeModal() {
        modal.classList.add('hidden');
    }

    function openModal(action, transaksiId, name) {
        const isLolos = action === 'INTERVIEW';
        const label = isLolos ? 'Lolos Seleksi' : 'Ditolak';

        modalTitle.innerHTML = (isLolos ? ICON_CHECK_CIRCLE : ICON_ALERT) + '<span style="margin-left:8px;vertical-align:middle;">' + (isLolos ? 'Lolos Tahap Seleksi?' : 'Tolak Pelamar?') + '</span>';
        candidateName.textContent = name;
        actionLabel.innerHTML = (isLolos ? ICON_CHECK_SMALL : ICON_X_SMALL) +
            '<span>' + (isLolos
                ? 'Pelamar akan dilanjutkan ke tahap wawancara'
                : 'Pelamar tidak lolos dari tahap administrasi') + '</span>';

        modalStatus.value = action;
        modalStatusText.textContent = label;
        modalStatusIcon.innerHTML = isLolos ? ICON_CHECK_CIRCLE : ICON_ALERT;

        if (isLolos) {
            modalInfoBox.style.borderColor = '#D1FAE5';
            modalInfoBox.style.background = '#F0FDF4';
            modalStatusBox.style.background = '#F0FDF4';
            modalStatusBox.style.borderColor = '#86EFAC';
            modalStatusBox.style.color = '#166534';
        } else {
            modalInfoBox.style.borderColor = '#FECACA';
            modalInfoBox.style.background = '#FEF2F2';
            modalStatusBox.style.background = '#FEF2F2';
            modalStatusBox.style.borderColor = '#FCA5A5';
            modalStatusBox.style.color = '#7F1D1D';
        }
        
        form.action = '<?= BASE_URL ?>views/pelamarPekerjaan/riwayat_pelamar.php?id_transaksi=' + encodeURIComponent(transaksiId);
        modal.classList.remove('hidden');
    }

    openButtons.forEach(button => {
        button.addEventListener('click', function() {
            openModal(this.dataset.action, this.dataset.transaksi, this.dataset.name);
        });
    });

    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>