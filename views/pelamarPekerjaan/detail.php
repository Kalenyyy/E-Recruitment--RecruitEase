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

<div class="flex items-center justify-between mb-6 font-sans">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Pelamar: <?= htmlspecialchars($jobDetails['judul_job']) ?></h1>
        <p class="text-sm" style="color: #64748B;">Posisi: <?= htmlspecialchars($jobDetails['nama_posisi']) ?> | Kelola berkas dan tahap seleksi kandidat</p>
    </div>
    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
        ← Kembali ke Daftar Job
    </a>
</div>

<div class="rounded-2xl overflow-hidden shadow-sm" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
    <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid #F1F5F9; background: #F8FAFC;">
        <div class="flex items-center gap-2">
            <span style="font-size:16px;">👥</span>
            <h2 class="font-bold text-sm" style="color: #1E293B;">Daftar Kandidat Masuk (Total: <?= count($applicants) ?>)</h2>
        </div>
    </div>

    <div class="p-6">
        <?php if (empty($applicants)): ?>
            <div class="text-center py-12">
                <span class="text-4xl block mb-2">📥</span>
                <p class="text-sm font-semibold text-slate-500">Belum ada pelamar untuk lowongan ini.</p>
                <p class="text-xs text-slate-400 mt-1">Data pelamar baru akan muncul begitu kandidat menekan tombol kirim lamaran.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs font-bold uppercase border-b border-slate-200" style="color: #475569; background: #F8FAFC;">
                            <th class="px-4 py-3">Nama Lengkap / Kontak</th>
                            <th class="px-4 py-3">Tanggal Melamar</th>
                            <th class="px-4 py-3" style="width: 280px;">Kualifikasi Screening</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Berkas / Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm" style="color: #1E293B;">
                        <?php foreach ($applicants as $app): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($app['nama_lengkap']) ?></div>
                                    <div class="text-xs text-slate-500 mt-0.5">📧 <?= htmlspecialchars($app['email']) ?></div>
                                    <div class="text-xs text-slate-400">📞 <?= htmlspecialchars($app['no_hp']) ?></div>
                                </td>
                                
                                <td class="px-4 py-4 text-xs text-slate-600 font-medium">
                                    <?= date('d M Y, H:i', strtotime($app['tanggal_melamar'])) ?>
                                </td>
                                
                                <td class="px-4 py-4">
                                    <div class="flex flex-col gap-2">
                                        <div class="p-2 rounded-xl border" style="background-color: #F8FAFC; border-color: #E2E8F0;">
                                            <span class="block text-[10px] font-extrabold tracking-wider uppercase text-indigo-600 mb-0.5">Keahlian Utama</span>
                                            <div class="flex items-start gap-1.5 text-xs font-semibold text-slate-800">
                                                <span class="text-slate-500 flex-shrink-0 mt-0.5">💻</span>
                                                <span class="leading-tight">
                                                    <?= (!empty($app['expert_bidang']) && $app['expert_bidang'] !== '-') ? htmlspecialchars($app['expert_bidang']) : '<span class="text-slate-400 font-normal italic">Tidak ditentukan</span>' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="p-2 rounded-xl border" style="background-color: #FFFDF5; border-color: #FEF3C7;">
                                            <span class="block text-[10px] font-extrabold tracking-wider uppercase text-amber-600 mb-0.5">Pengalaman</span>
                                            <div class="flex items-center gap-1.5 text-xs font-bold text-amber-900">
                                                <span class="text-amber-500 flex-shrink-0">⏱️</span>
                                                <span>
                                                    <?= (!empty($app['pengalaman_bidang']) && $app['pengalaman_bidang'] !== '-') ? htmlspecialchars($app['pengalaman_bidang']) : '<span class="text-amber-600/50 font-normal italic">Fresh Graduate</span>' ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-4 py-4 text-center">
                                    <?php 
                                    $status = $app['status_lamaran'];
                                    $badgeStyle = "bg-slate-100 text-slate-700"; 
                                    if ($status === 'ADMINISTRASI') $badgeStyle = "bg-amber-50 text-amber-700 border border-amber-200";
                                    if ($status === 'INTERVIEW') $badgeStyle = "bg-blue-50 text-blue-700 border border-blue-200";
                                    if ($status === 'DITERIMA') $badgeStyle = "bg-emerald-50 text-emerald-700 border border-emerald-200";
                                    if ($status === 'DITOLAK') $badgeStyle = "bg-rose-50 text-rose-700 border border-rose-200";
                                    ?>
                                    <span class="inline-block px-2.5 py-1 text-xs font-bold rounded-full <?= $badgeStyle ?>">
                                        <?= $status ?>
                                    </span>
                                </td>
                                
                                <td class="px-4 py-4 text-right space-y-1">
                                    <?php if (!empty($app['cv_file'])): ?>
                                        <a href="<?= BASE_URL . 'uploads/cv/' . $app['cv_file'] ?>" target="_blank"
                                           class="inline-flex items-center justify-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition w-full">
                                            📄 Lihat CV
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic block text-center py-1">CV tidak ada</span>
                                    <?php endif; ?>
                                    
                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/proses.php?id_transaksi=<?= $app['id_transaksi'] ?>"
                                       class="inline-flex items-center justify-center gap-1 text-xs font-bold px-3 py-1.5 rounded-lg bg-blue-900 text-white hover:bg-blue-800 transition shadow-sm w-full text-center">
                                        ⚙️ Kelola
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>