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

<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Daftar Pelamar Kerja</h1>
        <p class="text-sm" style="color: #64748B;">Memantau kandidat masuk pada lowongan aktif (Status: Open)</p>
    </div>
</div>

<div class="rounded-2xl overflow-hidden shadow-sm" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
    <div class="px-6 py-4 flex items-center gap-2" style="border-bottom: 1px solid #F1F5F9; background: #F8FAFC;">
        <span style="font-size:16px;">📂</span>
        <h2 class="font-bold text-sm" style="color: #1E293B;">Lowongan Pekerjaan Aktif</h2>
    </div>

    <div class="p-6">
        <?php if (empty($openJobs)): ?>
            <div class="text-center py-12">
                <span class="text-4xl block mb-2">📢</span>
                <p class="text-sm font-semibold text-slate-500">Belum ada lowongan pekerjaan berstatus "Open".</p>
                <p class="text-xs text-slate-400 mt-1">Aktifkan atau publish beberapa lowongan kerja terlebih dahulu.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="text-xs font-bold uppercase border-b border-slate-200" style="color: #475569; background: #F8FAFC;">
                            <th class="px-4 py-3">Judul Pekerjaan / Posisi</th>
                            <th class="px-4 py-3">Tipe / Lokasi</th>
                            <th class="px-4 py-3 text-center">Jumlah Pelamar</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm" style="color: #1E293B;">
                        <?php foreach ($openJobs as $job): ?>
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-4">
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($job['judul_job']) ?></div>
                                    <div class="text-xs text-slate-400 font-medium mt-0.5"><?= htmlspecialchars($job['nama_posisi']) ?></div>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-blue-50 text-blue-700 mb-1">
                                        <?= htmlspecialchars($job['tipe_pekerjaan']) ?>
                                    </span>
                                    <div class="text-xs text-slate-500">📍 <?= htmlspecialchars($job['lokasi']) ?></div>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <?php if ($job['total_pelamar'] > 0): ?>
                                        <span class="inline-flex items-center justify-center font-bold bg-emerald-100 text-emerald-800 rounded-full h-7 w-7 text-xs">
                                            <?= $job['total_pelamar'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs font-semibold text-slate-400 bg-slate-100 px-2 py-1 rounded-full">
                                            0 Pelamar
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/detail.php?job_id=<?= $job['id'] ?>" 
                                       class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white transition shadow-sm"
                                       style="background: #1E3A8A;">
                                        👁️ Lihat Pelamar
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