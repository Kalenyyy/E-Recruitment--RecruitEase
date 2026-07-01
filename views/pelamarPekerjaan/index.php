<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

// --- LOGIKA SEARCH & PAGINATION ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 5; // Set 5 atau sesuai keinginan
$totalData = PelamarPekerjaanController::getTotalCount($conn, $search);
$totalPages = ($totalData > 0) ? ceil($totalData / $perPage) : 1;

$openJobs = PelamarPekerjaanController::getPaginated($conn, $page, $perPage, $search);
$jobCountInPage = mysqli_num_rows($openJobs);

ob_start();
?>

<!-- PAGE HEADER -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div class="flex items-center gap-4">
        <div class="inline-flex items-center justify-center rounded-2xl shadow-sm" style="width:48px;height:48px;background:linear-gradient(135deg,#1E3A8A,#2563EB);">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div>
            <h1 class="text-xl font-bold" style="color: #1E293B;">Daftar Pelamar Kerja</h1>
            <p class="text-xs mt-0.5" style="color: #64748B;">Kelola kandidat pada lowongan aktif</p>
        </div>
    </div>

    <div class="flex items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mr-1">Export:</span>
            <a href="<?= BASE_URL ?>views/laporan/export_kandidat.php"
                class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-blue-200 text-blue-700 rounded-xl text-[10px] font-bold uppercase tracking-tight hover:bg-blue-50 transition-all shadow-sm">
                <i class="fa-solid fa-users text-sm text-blue-500"></i>
                Kandidat
            </a>
        </div>

        <!-- SEARCH FORM -->
        <form id="searchForm" method="GET" class="relative">
            <input type="text" name="search" id="searchInput"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari lowongan..."
                autocomplete="off"
                oninput="doSearch()"
                class="pl-10 pr-4 py-2 rounded-xl text-xs border border-slate-200 focus:ring-2 focus:ring-blue-100 outline-none w-64 shadow-inner">
            <div class="absolute left-3 top-2.5 text-slate-400">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </form>
    </div>
</div>

<div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 4px rgba(15,23,42,0.06);">
    <div class="px-6 py-5 flex items-center gap-3" style="border-bottom: 1px solid #E2E8F0; background: linear-gradient(135deg,#1E3A8A,#2563EB);">
        <h2 class="font-bold text-base text-white">Lowongan Pekerjaan Aktif</h2>
        <span class="px-2 py-0.5 rounded-lg bg-white/20 text-white text-[10px] font-bold">Total <?= $totalData ?></span>
    </div>

    <div class="p-0"> <!-- P-0 agar table border pas -->
        <?php if ($jobCountInPage === 0): ?>
            <div class="text-center py-16">
                <p class="text-sm text-slate-400 italic">Data tidak ditemukan.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr style="background: #F8FAFC; border-bottom: 1px solid #E2E8F0;">
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Judul Pekerjaan / Posisi</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Tipe / Lokasi</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Jumlah Pelamar</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($openJobs as $job): ?>
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-5">
                                    <div class="font-bold text-slate-800 text-[15px] group-hover:text-blue-600 transition-colors">
                                        <?= htmlspecialchars($job['judul_job']) ?>
                                    </div>
                                    <div class="text-[11px] font-bold text-slate-400 mt-1 uppercase tracking-tighter">
                                        <?= htmlspecialchars($job['nama_posisi']) ?>
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-semibold text-slate-600"><?= htmlspecialchars($job['lokasi']) ?></div>
                                    <div class="text-[10px] font-black text-blue-500 uppercase"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl <?= $job['total_pelamar'] > 0 ? 'bg-emerald-50 text-emerald-600 border-emerald-100' : 'bg-slate-50 text-slate-400 border-slate-100' ?> border">
                                        <span class="text-sm font-black"><?= $job['total_pelamar'] ?></span>
                                        <span class="text-[9px] font-black uppercase tracking-tighter">Pelamar</span>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/detail.php?job_id=<?= $job['id'] ?>"
                                        class="inline-flex items-center gap-2 px-6 py-3 bg-blue-900 text-white rounded-2xl text-[11px] font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow-lg shadow-blue-900/10 active:scale-95">
                                        Review Pelamar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION FOOTER -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                <span class="text-xs font-medium text-slate-500">
                    Menampilkan <?= ($jobCountInPage > 0) ? (($page - 1) * $perPage) + 1 : 0 ?> - <?= ($page - 1) * $perPage + $jobCountInPage ?> dari <?= $totalData ?> data
                </span>

                <div class="flex items-center gap-1">
                    <?php $searchQuery = !empty($search) ? "&search=" . urlencode($search) : ""; ?>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= $searchQuery ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition font-bold">‹</a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?= $i ?><?= $searchQuery ?>"
                            class="px-2.5 py-1 text-xs rounded-lg font-bold transition <?= $i == $page ? 'bg-blue-800 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="?page=<?= $page + 1 ?><?= $searchQuery ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition font-bold">›</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    let timeout = null;

    function doSearch() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('searchForm').submit();
        }, 500);
    }

    // Tetap fokus di input setelah reload
    window.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('searchInput');
        if (input.value !== '') {
            input.focus();
            const val = input.value;
            input.value = '';
            input.value = val;
        }
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>