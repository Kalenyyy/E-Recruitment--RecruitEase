<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

// Proteksi Halaman: Wajib login dan harus HR
AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

// Ambil data lowongan berstatus open
$openJobs = PelamarPekerjaanController::index($conn);

ob_start();
?>

<!-- PAGE HEADER -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
        <div class="inline-flex items-center justify-center rounded-2xl" style="width:52px;height:52px;background:linear-gradient(135deg,#1E3A8A,#2563EB);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold" style="color: #1E293B;">Daftar Pelamar Kerja</h1>
            <p class="text-sm mt-0.5" style="color: #64748B;">Memantau kandidat masuk pada lowongan aktif (Status: Open)</p>
        </div>
    </div>
</div>

<!-- MAIN CARD -->
<div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 4px rgba(15,23,42,0.06);">

    <!-- CARD HEADER -->
    <div class="px-6 py-5 flex items-center gap-3" style="border-bottom: 1px solid #E2E8F0; background: linear-gradient(135deg,#1E3A8A,#2563EB);">
        <span class="inline-flex items-center justify-center rounded-xl" style="width:40px;height:40px;background:rgba(255,255,255,0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
        </span>
        <div>
            <h2 class="font-bold text-base text-white">Lowongan Pekerjaan Aktif</h2>
            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65);">
                <?= count($openJobs) ?> lowongan tersedia
            </p>
        </div>
    </div>

    <div class="p-6">
        <?php if (empty($openJobs)): ?>
            <!-- EMPTY STATE -->
            <div class="text-center py-16">
                <span class="inline-flex items-center justify-center mb-5 rounded-full" style="width:72px;height:72px;background:#F1F5F9;color:#94A3B8;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                </span>
                <p class="text-base font-bold text-slate-700">Belum ada lowongan pekerjaan berstatus "Open"</p>
                <p class="text-sm text-slate-400 mt-2">Aktifkan atau publish beberapa lowongan kerja terlebih dahulu.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 2px solid #E2E8F0;">
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider" style="color: #64748B;">Judul Pekerjaan / Posisi</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider" style="color: #64748B;">Tipe / Lokasi</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-center" style="color: #64748B;">Jumlah Pelamar</th>
                            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-center" style="color: #64748B;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody style="color: #1E293B;">
                        <?php foreach ($openJobs as $job): ?>
                            <tr class="hover:bg-slate-50 transition" style="border-bottom: 1px solid #F1F5F9;">
                                <!-- JUDUL / POSISI -->
                                <td class="px-5 py-5">
                                    <div class="font-bold text-slate-800" style="font-size:15px;"><?= htmlspecialchars($job['judul_job']) ?></div>
                                    <div class="text-xs font-medium mt-1" style="color:#64748B;"><?= htmlspecialchars($job['nama_posisi']) ?></div>
                                </td>

                                <!-- TIPE / LOKASI -->
                                <td class="px-5 py-5">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg mb-1.5" style="background:#EFF6FF;color:#1D4ED8;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        <?= htmlspecialchars($job['tipe_pekerjaan']) ?>
                                    </span>
                                    <div class="flex items-center gap-1.5 text-xs mt-0.5" style="color:#64748B;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                        <?= htmlspecialchars($job['lokasi']) ?>
                                    </div>
                                </td>

                                <!-- JUMLAH PELAMAR -->
                                <td class="px-5 py-5 text-center">
                                    <?php if ($job['total_pelamar'] > 0): ?>
                                        <div class="inline-flex flex-col items-center gap-0.5">
                                            <span class="inline-flex items-center justify-center font-bold rounded-full text-sm" style="width:36px;height:36px;background:#DCFCE7;color:#166534;">
                                                <?= $job['total_pelamar'] ?>
                                            </span>
                                            <span class="text-[10px] font-semibold" style="color:#64748B;">Pelamar</span>
                                        </div>
                                    <?php else: ?>
                                        <div class="inline-flex flex-col items-center gap-0.5">
                                            <span class="inline-flex items-center justify-center font-bold rounded-full text-sm" style="width:36px;height:36px;background:#F1F5F9;color:#94A3B8;">
                                                0
                                            </span>
                                            <span class="text-[10px] font-semibold" style="color:#94A3B8;">Pelamar</span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- AKSI -->
                                <td class="px-5 py-5 text-center">
                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/detail.php?job_id=<?= $job['id'] ?>"
                                       class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2.5 rounded-xl text-white transition hover:opacity-90 hover:shadow-md"
                                       style="background: linear-gradient(135deg,#1E3A8A,#2563EB);">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                        Lihat Pelamar
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