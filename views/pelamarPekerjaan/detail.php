<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$job_id = $_GET['job_id'] ?? null;

if (!$job_id || !is_numeric($job_id)) {
    header("Location: " . BASE_URL . "views/pelamarPekerjaan/index.php");
    exit;
}

// --- LOGIKA SEARCH & PAGINATION ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 5; // Jumlah pelamar per halaman
$totalData = PelamarPekerjaanController::getTotalApplicants($conn, $job_id, $search);
$totalPages = ($totalData > 0) ? ceil($totalData / $perPage) : 1;

$jobDetails = PelamarPekerjaanController::getDetailJob($conn, $job_id);
// Panggil fungsi paginated
$applicants = PelamarPekerjaanController::getApplicantsPaginated($conn, $job_id, $page, $perPage, $search);
$appCountInPage = mysqli_num_rows($applicants);

if (!$jobDetails) {
    die("Data lowongan tidak ditemukan.");
}

$statusConfig = [
    'ADMINISTRASI' => ['bg' => '#FEF3C7', 'color' => '#B45309', 'label' => 'Administrasi'],
    'INTERVIEW'    => ['bg' => '#DBEAFE', 'color' => '#1D4ED8', 'label' => 'Interview'],
    'OFFERING'     => ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'label' => 'Offering'],
    'DITERIMA'     => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Diterima'],
    'DITOLAK'      => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'label' => 'Ditolak'],
];

ob_start();
?>

<div class="flex flex-col md:flex-row items-center justify-between mb-8 gap-4">
    <div class="flex items-center gap-4 w-full md:w-auto">
        <div class="inline-flex items-center justify-center rounded-2xl"
            style="width:52px;height:52px;background:linear-gradient(135deg,#1E3A8A,#2563EB);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
            </svg>
        </div>
        <div>
            <nav class="flex items-center gap-1.5 text-xs font-semibold mb-1" style="color:#94A3B8;">
                <a href="index.php" class="hover:underline" style="color:#3B82F6;">Manajemen Lowongan</a>
                <span>/</span>
                <span style="color:#64748B;">Daftar Pelamar</span>
            </nav>
            <h1 class="text-2xl font-bold" style="color:#1E293B;"><?= htmlspecialchars($jobDetails['judul_job']) ?></h1>
            <p class="text-sm mt-0.5" style="color:#64748B;">
                Ditemukan <span style="color:#2563EB;font-weight:700;"><?= $totalData ?></span> kandidat
            </p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
        <!-- SEARCH FORM -->
        <form id="searchForm" method="GET" class="relative">
            <input type="hidden" name="job_id" value="<?= $job_id ?>">
            <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>"
                placeholder="Cari nama pelamar..." autocomplete="off" oninput="doSearch()"
                class="pl-10 pr-4 py-2.5 rounded-xl text-xs border border-slate-200 focus:ring-2 focus:ring-blue-100 outline-none w-full md:w-64 shadow-sm">
            <div class="absolute left-3 top-3 text-slate-400">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
        </form>

        <a href="<?= BASE_URL ?>views/laporan/export_pelamar_job.php?job_id=<?= $job_id ?>"
            class="inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-widest px-4 py-2.5 rounded-xl transition-all hover:bg-emerald-50 text-emerald-700 bg-white border border-emerald-200 shadow-sm">
            <i class="fa-solid fa-file-excel"></i> Export
        </a>

        <a href="index.php" class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition bg-slate-100 text-slate-600 border border-slate-200">
            Kembali
        </a>
    </div>
</div>

<!-- MAIN CARD -->
<div class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
    <div class="px-6 py-5 flex items-center justify-between" style="background:linear-gradient(135deg,#1E3A8A,#2563EB);">
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center rounded-xl bg-white/20" style="width:40px;height:40px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                    <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
                </svg>
            </span>
            <h2 class="font-bold text-base text-white">Daftar Pelamar Terdaftar</h2>
        </div>
        <?php if (!empty($search)): ?>
            <a href="?job_id=<?= $job_id ?>" class="text-[10px] font-bold text-white bg-white/10 px-2 py-1 rounded-lg hover:bg-white/20">Reset Pencarian</a>
        <?php endif; ?>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b-2 border-slate-100">
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Kandidat</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Keahlian / Pengalaman</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500">Tanggal Melamar</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Status</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase text-slate-500 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php if ($appCountInPage > 0): ?>
                    <?php foreach ($applicants as $app): ?>
                        <?php
                        $status  = strtoupper($app['status_lamaran']);
                        $cfg     = $statusConfig[$status] ?? ['bg' => '#F1F5F9', 'color' => '#475569', 'label' => $app['status_lamaran']];
                        $inisial = mb_strtoupper(mb_substr($app['nama_lengkap'], 0, 1));
                        ?>
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex items-center justify-center rounded-xl font-bold text-white bg-blue-700" style="width:40px;height:40px;flex-shrink:0;">
                                        <?= $inisial ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($app['nama_lengkap']) ?></div>
                                        <div class="text-[11px] text-slate-500"><?= htmlspecialchars($app['email']) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="text-[11px] text-slate-600">
                                    <p><span class="font-bold">Keahlian:</span> <?= htmlspecialchars($app['expert_bidang'] ?: '-') ?></p>
                                    <p><span class="font-bold">Pengalaman:</span> <?= htmlspecialchars($app['pengalaman_bidang'] ?: '-') ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-5 text-xs text-slate-500">
                                <?= date('d M Y', strtotime($app['tanggal_melamar'])) ?>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                                    <?= htmlspecialchars($cfg['label']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="riwayat_pelamar.php?id_transaksi=<?= $app['id_transaksi'] ?>" class="p-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 transition" title="Lihat Profil">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                    <?php if ($status === 'ADMINISTRASI'): ?>
                                        <button onclick="openStatusModal('INTERVIEW', '<?= $app['id_transaksi'] ?>', '<?= addslashes($app['nama_lengkap']) ?>')" class="px-3 py-1.5 bg-emerald-600 text-white text-[11px] font-bold rounded-lg hover:bg-emerald-700 transition">Lolos</button>
                                        <button onclick="openStatusModal('DITOLAK', '<?= $app['id_transaksi'] ?>', '<?= addslashes($app['nama_lengkap']) ?>')" class="px-3 py-1.5 border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition">Tolak</button>
                                    <?php endif; ?>
                                    <?php if ($status === 'INTERVIEW'): ?>
                                        <button onclick="openOfferingModal('<?= $app['id_transaksi'] ?>', '<?= addslashes($app['nama_lengkap']) ?>')" class="px-3 py-1.5 bg-blue-800 text-white text-[11px] font-bold rounded-lg hover:bg-blue-900 transition">Offering</button>
                                        <button onclick="openStatusModal('DITOLAK', '<?= $app['id_transaksi'] ?>', '<?= addslashes($app['nama_lengkap']) ?>')" class="px-3 py-1.5 border border-red-200 text-red-600 text-[11px] font-bold rounded-lg hover:bg-red-50 transition">Tolak</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic text-sm">Tidak ada pelamar ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 bg-slate-50/30">
        <span class="text-xs font-medium text-slate-500">
            Menampilkan <?= ($appCountInPage > 0) ? (($page - 1) * $perPage) + 1 : 0 ?> - <?= ($page - 1) * $perPage + $appCountInPage ?> dari <?= $totalData ?> kandidat
        </span>

        <div class="flex items-center gap-1">
            <?php
            $searchQuery = "&job_id=$job_id";
            if (!empty($search)) $searchQuery .= "&search=" . urlencode($search);
            ?>
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?><?= $searchQuery ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold transition">‹</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?= $i ?><?= $searchQuery ?>"
                    class="px-2.5 py-1 text-xs rounded-lg font-bold transition <?= $i == $page ? 'bg-blue-800 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?><?= $searchQuery ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold transition">›</a>
            <?php endif; ?>
        </div>
    </div>
</div>


<!-- ================================================================
    MODAL KONFIRMASI STATUS (Lolos / Tolak + Alasan)
    ================================================================ -->
<div id="statusModal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:1rem;
            background:rgba(15,23,42,0.55);backdrop-filter:blur(5px);
            opacity:0;transition:opacity 0.25s ease;"
    onclick="if(event.target===this) closeModal('statusModal')">

    <div style="background:#FFFFFF;border-radius:1rem;width:100%;max-width:460px;
                box-shadow:0 25px 60px rgba(15,23,42,0.18);border:1px solid #E2E8F0;
                transform:translateY(1.5rem);transition:transform 0.25s ease;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:1.25rem 1.75rem;border-bottom:1px solid #F1F5F9;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <div id="modalIcon"
                    style="width:34px;height:34px;border-radius:0.625rem;flex-shrink:0;
                            background:#F1F5F9;border:1px solid #E2E8F0;
                            display:flex;align-items:center;justify-content:center;"></div>
                <h2 id="modalTitle"
                    style="font-size:1rem;font-weight:700;color:#0F172A;margin:0;"></h2>
            </div>
            <button onclick="closeModal('statusModal')"
                style="width:34px;height:34px;border-radius:50%;border:1px solid #E2E8F0;
                        background:#F8FAFC;color:#64748B;cursor:pointer;
                        display:flex;align-items:center;justify-content:center;
                        transition:background 0.15s;"
                onmouseover="this.style.background='#F1F5F9'"
                onmouseout="this.style.background='#F8FAFC'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form id="modalForm" method="POST" style="padding:1.5rem 1.75rem 1.75rem;">
            <input type="hidden" name="status_lamaran" id="inputStatus">

            <!-- Info Kandidat -->
            <div id="modalInfoBox"
                style="display:flex;align-items:center;gap:1rem;
                        padding:1rem 1.25rem;border-radius:1rem;margin-bottom:1.25rem;">
                <div id="modalIcon"
                    style="width:44px;height:44px;border-radius:0.875rem;flex-shrink:0;
                            display:flex;align-items:center;justify-content:center;"></div>
                <div>
                    <p id="modalKandidat"
                        style="font-weight:700;color:#0F172A;font-size:15px;margin:0;"></p>
                    <p id="modalSubtext"
                        style="font-size:12px;color:#64748B;margin:4px 0 0;"></p>
                </div>
            </div>

            <!-- Field Jadwal Interview (hanya saat LOLOS) -->
            <div id="interviewFields" style="display:none;margin-bottom:1.25rem;">
                <div style="padding:1.125rem;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:0.75rem;">
                    <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;
                            text-transform:uppercase;color:#94A3B8;margin:0 0 0.75rem;">
                        Jadwal Wawancara
                    </p>
                    <input type="datetime-local" name="tanggal_interview" id="tanggalInput"
                        style="width:100%;padding:10px 14px;border-radius:0.75rem;
                                border:1px solid #E2E8F0;background:#FFFFFF;
                                font-size:13px;font-weight:600;color:#1E293B;
                                outline:none;margin-bottom:10px;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#2563EB'"
                        onblur="this.style.borderColor='#E2E8F0'">
                    <textarea name="catatan" rows="2" placeholder="Catatan (Lokasi / Link Zoom)..."
                        style="width:100%;padding:10px 14px;border-radius:0.75rem;
                                    border:1px solid #E2E8F0;background:#FFFFFF;
                                    font-size:13px;color:#475569;resize:none;
                                    outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#2563EB'"
                        onblur="this.style.borderColor='#E2E8F0'"></textarea>
                </div>
            </div>

            <!-- Field Alasan Penolakan (hanya saat TOLAK) -->
            <div id="alasanFields" style="display:none;margin-bottom:1.25rem;">
                <div style="padding:1.125rem;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:0.75rem;">
                    <p style="font-size:10px;font-weight:700;letter-spacing:0.08em;
                            text-transform:uppercase;color:#94A3B8;margin:0 0 0.75rem;">
                        Alasan Penolakan
                    </p>
                    <textarea name="alasan_tolak" id="alasanInput" rows="3"
                        placeholder="Tuliskan alasan penolakan kandidat ini..."
                        style="width:100%;padding:10px 14px;border-radius:0.75rem;
                                    border:1px solid #FECACA;background:#FFFFFF;
                                    font-size:13px;color:#7F1D1D;resize:none;
                                    outline:none;box-sizing:border-box;"
                        onfocus="this.style.borderColor='#DC2626'"
                        onblur="this.style.borderColor='#FECACA'"></textarea>
                </div>
            </div>

            <!-- Tombol -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <button type="button" onclick="closeModal('statusModal')"
                    style="padding:12px;border-radius:0.875rem;border:1px solid #E2E8F0;
                            background:#FFFFFF;color:#64748B;font-size:13px;font-weight:600;
                            cursor:pointer;transition:background 0.15s;"
                    onmouseover="this.style.background='#F8FAFC'"
                    onmouseout="this.style.background='#FFFFFF'">
                    Batal
                </button>
                <button type="submit" id="submitBtn"
                    style="padding:12px;border-radius:0.875rem;border:none;
                            color:#FFFFFF;font-size:13px;font-weight:700;
                            cursor:pointer;transition:opacity 0.15s;"
                    onmouseover="this.style.opacity='0.88'"
                    onmouseout="this.style.opacity='1'">
                </button>
            </div>
        </form>
    </div>
</div>


<!-- ================================================================
    MODAL OFFERING LETTER
    ================================================================ -->
<div id="offeringModal"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:1rem;
            background:rgba(15,23,42,0.55);backdrop-filter:blur(5px);
            opacity:0;transition:opacity 0.25s ease;"
    onclick="if(event.target===this) closeModal('offeringModal')">

    <div style="background:#FFFFFF;border-radius:1rem;width:100%;max-width:460px;
                box-shadow:0 25px 60px rgba(15,23,42,0.18);overflow:hidden;
                border:1px solid #E2E8F0;
                transform:translateY(1.5rem);transition:transform 0.25s ease;">

        <!-- Header -->
        <div style="padding:1.25rem 1.75rem;position:relative;border-bottom:1px solid #F1F5F9;background:#F8FAFC;">
            <button onclick="closeModal('offeringModal')"
                style="position:absolute;top:1.25rem;right:1.25rem;
                           width:34px;height:34px;border-radius:50%;
                           border:1.5px solid rgba(255,255,255,0.3);
                           background:rgba(255,255,255,0.15);color:#FFFFFF;cursor:pointer;
                           display:flex;align-items:center;justify-content:center;
                           transition:background 0.15s;"
                onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                       text-transform:uppercase;color:#94A3B8;margin:0 0 5px;">
                Tahap Penawaran Kerja
            </p>
            <h2 style="font-size:1.05rem;font-weight:700;color:#0F172A;margin:0 0 0.875rem;padding-right:2.5rem;">
                Kirim Offering Letter
            </h2>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:32px;height:32px;border-radius:0.625rem;flex-shrink:0;
                            background:#1E293B;
                            display:flex;align-items:center;justify-content:center;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <p id="offering_kandidat_name"
                    style="font-size:14px;font-weight:700;color:#FFFFFF;margin:0;"></p>
            </div>
        </div>

        <form action="<?= BASE_URL ?>public/actions/create_offering.php" method="POST"
            enctype="multipart/form-data" style="padding:1.5rem 2rem 2rem;">
            <input type="hidden" name="id_transaksi" id="offering_id_transaksi">

            <!-- Gaji -->
            <div style="margin-bottom:1rem;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.1em; text-transform:uppercase;color:#94A3B8;margin:0 0 8px;">
                    Gaji yang Ditawarkan (IDR)
                </p>

                <!-- Tampilkan Range Gaji dari Database -->
                <div style="margin-bottom: 8px; font-size: 11px; color: #64748B; font-weight: 600;">
                    <?php
                    $min = $jobDetails['gaji_min'] ?? 0;
                    $max = $jobDetails['gaji_max'] ?? 0;

                    if ($min > 0 && $max > 0) {
                        echo "Rentang Anggaran: <span class='text-blue-600'>Rp " . number_format($min, 0, ',', '.') . " - Rp " . number_format($max, 0, ',', '.') . "</span>";
                    } elseif ($min > 0) {
                        echo "Anggaran: <span class='text-blue-600'>Mulai dari Rp " . number_format($min, 0, ',', '.') . "</span>";
                    } else {
                        // Jika tidak diisi atau 0
                        echo "Anggaran: <span class='text-slate-500 italic'>Sesuai Kebijakan Perusahaan (Negosiasi)</span>";
                    }
                    ?>
                </div>

                <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%); font-size:13px;font-weight:700;color:#94A3B8;">Rp</span>
                    <input type="text" name="gaji_offering" id="gaji_input" required placeholder="Contoh: 5.000.000"
                        style="width:100%;padding:12px 14px 12px 42px;border-radius:0.875rem; border:1px solid #E2E8F0;background:#F8FAFC; font-size:15px;font-weight:700;color:#1E293B; outline:none;box-sizing:border-box;">
                </div>

                <!-- Pesan Error Gaji -->
                <p id="gaji_error_msg" style="display:none; color: #EF4444; font-size: 11px; font-weight: 600; margin-top: 6px;">
                    <i class="fa-solid fa-circle-exclamation"></i> Gaji harus berada di rentang yang ditentukan.
                </p>
            </div>

            <!-- Upload PDF -->
            <div style="margin-bottom:1.5rem;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.08em; text-transform:uppercase;color:#94A3B8;margin:0 0 8px;">
                    Dokumen Surat Penawaran (PDF)
                </p>
                <label id="dropzone" style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                  padding:1.5rem;border-radius:0.75rem;cursor:pointer;
                  border:1.5px dashed #CBD5E1;background:#F8FAFC;
                  transition:all 0.2s ease;">

                    <div id="iconContainer" style="width:40px;height:40px;border-radius:0.75rem;margin-bottom:10px;
                    background:linear-gradient(135deg,#1E3A8A,#2563EB);
                    display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>

                    <p id="fileNameDisplay" style="font-size:13px;font-weight:600;color:#475569;margin:0; text-align:center;">
                        Klik untuk upload file PDF
                    </p>
                    <p id="fileSubtitle" style="font-size:11px;color:#94A3B8;margin:4px 0 0;">Format: .pdf</p>

                    <!-- Input file tetap sama -->
                    <input type="file" name="file_offering" id="fileInput" accept=".pdf" required
                        onchange="updateFileName(this)" style="display:none;">
                </label>
            </div>

            <!-- Tombol -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
                <button type="button" onclick="closeModal('offeringModal')"
                    style="padding:12px;border-radius:0.875rem;border:1px solid #E2E8F0;
                               background:#FFFFFF;color:#64748B;font-size:13px;font-weight:600;
                               cursor:pointer;transition:background 0.15s;"
                    onmouseover="this.style.background='#F8FAFC'"
                    onmouseout="this.style.background='#FFFFFF'">
                    Batal
                </button>
                <button type="submit"
                    style="padding:12px;border-radius:0.875rem;border:none;
                               background:linear-gradient(135deg,#1E3A8A,#2563EB);
                               color:#FFFFFF;font-size:13px;font-weight:700;cursor:pointer;
                               box-shadow:0 2px 8px rgba(37,99,235,0.3);transition:opacity 0.15s;"
                    onmouseover="this.style.opacity='0.88'"
                    onmouseout="this.style.opacity='1'">
                    Kirim Penawaran
                </button>
            </div>
        </form>
    </div>
</div>


<script>
    (function() {
        'use strict';

        function openOverlay(el, boxEl) {
            el.style.display = 'flex';
            requestAnimationFrame(() => {
                el.style.opacity = '1';
                boxEl.style.transform = 'translateY(0)';
            });
            document.body.style.overflow = 'hidden';
        }

        function closeOverlay(el, boxEl) {
            el.style.opacity = '0';
            boxEl.style.transform = 'translateY(1.5rem)';
            setTimeout(() => {
                el.style.display = 'none';
                document.body.style.overflow = '';
            }, 250);
        }

        const GAJI_MIN = <?= (int)$jobDetails['gaji_min'] ?>;
        const GAJI_MAX = <?= (int)$jobDetails['gaji_max'] ?>;

        const gajiInput = document.getElementById('gaji_input');
        const gajiErrorMsg = document.getElementById('gaji_error_msg');
        const submitOfferingBtn = document.querySelector('#offeringModal button[type="submit"]');

        gajiInput.addEventListener('input', function(e) {
            // 1. Bersihkan karakter non-angka
            let rawValue = this.value.replace(/\D/g, '');
            let numericValue = parseInt(rawValue) || 0;

            // 2. Format Tampilan ke Rupiah
            this.value = formatRupiah(rawValue);

            // 3. Validasi Range (DIPERBAIKI)
            let isError = false;

            if (rawValue !== "") {
                // Kondisi A: Jika ada batas Min DAN batas Max (Rentang Lengkap)
                if (GAJI_MIN > 0 && GAJI_MAX > 0) {
                    if (numericValue < GAJI_MIN || numericValue > GAJI_MAX) {
                        isError = true;
                    }
                }
                // Kondisi B: Jika hanya ada batas Min (Mulai dari...)
                else if (GAJI_MIN > 0 && GAJI_MAX === 0) {
                    if (numericValue < GAJI_MIN) {
                        isError = true;
                    }
                }
                // Kondisi C: Jika hanya ada batas Max (Maksimal...)
                else if (GAJI_MAX > 0 && GAJI_MIN === 0) {
                    if (numericValue > GAJI_MAX) {
                        isError = true;
                    }
                }
            }

            // 4. Update UI berdasarkan status error
            if (isError) {
                // Tampilan jika ERROR
                gajiInput.style.borderColor = "#EF4444";
                gajiInput.style.background = "#FEF2F2";
                gajiErrorMsg.style.display = "block";
                submitOfferingBtn.disabled = true;
                submitOfferingBtn.style.opacity = "0.5";
                submitOfferingBtn.style.cursor = "not-allowed";
            } else if (rawValue !== "") {
                // Tampilan jika BENAR
                gajiInput.style.borderColor = "#E2E8F0";
                gajiInput.style.background = "#F8FAFC";
                gajiErrorMsg.style.display = "none";
                submitOfferingBtn.disabled = false;
                submitOfferingBtn.style.opacity = "1";
                submitOfferingBtn.style.cursor = "pointer";
            } else {
                // Tampilan jika KOSONG
                gajiInput.style.borderColor = "#E2E8F0";
                gajiInput.style.background = "#F8FAFC";
                gajiErrorMsg.style.display = "none";
                submitOfferingBtn.disabled = true; // Tetap matikan tombol jika kosong
            }
        });

        function formatRupiah(angka) {
            if (!angka) return "";
            let number_string = angka.toString(),
                sisa = number_string.length % 3,
                rupiah = number_string.substr(0, sisa),
                ribuan = number_string.substr(sisa).match(/\d{3}/g);

            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            return rupiah;
        }

        window.openStatusModal = function(action, id_transaksi, name) {
            const modal = document.getElementById('statusModal');
            const box = modal.querySelector('div');
            const isLolos = action === 'INTERVIEW';
            const infoBox = document.getElementById('modalInfoBox');
            const icon = document.getElementById('modalIcon');
            const submitBtn = document.getElementById('submitBtn');
            const interviewFields = document.getElementById('interviewFields');
            const alasanFields = document.getElementById('alasanFields');
            const alasanInput = document.getElementById('alasanInput');
            const tanggalInput = document.getElementById('tanggalInput');

            document.getElementById('inputStatus').value = action;
            document.getElementById('modalForm').action =
                `<?= BASE_URL ?>public/actions/update_status_interview.php?id_transaksi=${id_transaksi}`;
            document.getElementById('modalKandidat').textContent = name;

            // Reset field
            interviewFields.style.display = 'none';
            alasanFields.style.display = 'none';
            tanggalInput.required = false;
            alasanInput.required = false;
            alasanInput.value = '';

            if (isLolos) {
                document.getElementById('modalTitle').textContent = 'Loloskan Tahap Seleksi';
                document.getElementById('modalSubtext').textContent = 'Kandidat akan lanjut ke tahap interview.';
                infoBox.style.background = '#F0FDF4';
                infoBox.style.border = '1px solid #BBF7D0';
                icon.style.background = '#059669';
                icon.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline></svg>`;
                submitBtn.style.background = '#059669';
                submitBtn.textContent = 'Ya, Atur Jadwal';
                interviewFields.style.display = 'block';
                tanggalInput.required = true;
                setMinDate();
            } else {
                document.getElementById('modalTitle').textContent = 'Tolak Kandidat';
                document.getElementById('modalSubtext').textContent = 'Berikan alasan penolakan untuk kandidat ini.';
                infoBox.style.background = '#FFF1F2';
                infoBox.style.border = '1px solid #FECDD3';
                icon.style.background = '#DC2626';
                icon.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
                submitBtn.style.background = '#DC2626';
                submitBtn.textContent = 'Ya, Tolak';
                alasanFields.style.display = 'block';
                alasanInput.required = true;
            }

            box.style.transform = 'translateY(1.5rem)';
            openOverlay(modal, box);
        };

        window.openOfferingModal = function(id_transaksi, name) {
            const modal = document.getElementById('offeringModal');
            const box = modal.querySelector('div');
            document.getElementById('offering_id_transaksi').value = id_transaksi;
            document.getElementById('offering_kandidat_name').textContent = name;
            box.style.transform = 'translateY(1.5rem)';
            openOverlay(modal, box);
        };

        window.closeModal = function(modalId) {
            const modal = document.getElementById(modalId);
            closeOverlay(modal, modal.querySelector('div'));
        };

        window.updateFileName = function(input) {
            const file = input.files[0];
            const fileNameDisplay = document.getElementById('fileNameDisplay');
            const fileSubtitle = document.getElementById('fileSubtitle');
            const dropzone = document.getElementById('dropzone');
            const iconContainer = document.getElementById('iconContainer');

            if (file) {
                const extension = file.name.split('.').pop().toLowerCase();

                if (extension !== 'pdf') {
                    // JIKA BUKAN PDF (Eror)
                    fileNameDisplay.textContent = "Format Salah: " + file.name;
                    fileNameDisplay.style.color = "#B91C1C"; // Merah
                    fileSubtitle.textContent = "Hanya file PDF yang diperbolehkan!";
                    fileSubtitle.style.color = "#EF4444";

                    // Ubah Box jadi Merah
                    dropzone.style.borderColor = "#EF4444";
                    dropzone.style.background = "#FEF2F2";
                    iconContainer.style.background = "#EF4444";

                    // Reset input agar tidak bisa disubmit
                    input.value = "";
                } else {
                    // JIKA BENAR (PDF)
                    fileNameDisplay.textContent = file.name;
                    fileNameDisplay.style.color = "#1E293B"; // Normal
                    fileSubtitle.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";
                    fileSubtitle.style.color = "#64748B";

                    // Ubah Box jadi Hijau/Biru (Sukses)
                    dropzone.style.borderColor = "#10B981";
                    dropzone.style.background = "#F0FDF4";
                    iconContainer.style.background = "#10B981";
                }
            } else {
                // Jika batal pilih file (Reset ke awal)
                fileNameDisplay.textContent = 'Klik untuk upload file PDF';
                fileNameDisplay.style.color = "#475569";
                fileSubtitle.textContent = "Format: .pdf";
                fileSubtitle.style.color = "#94A3B8";
                dropzone.style.borderColor = "#CBD5E1";
                dropzone.style.background = "#F8FAFC";
                iconContainer.style.background = "linear-gradient(135deg,#1E3A8A,#2563EB)";
            }
        };

        function setMinDate() {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            document.getElementById('tanggalInput').min = now.toISOString().slice(0, 16);
        }

    })();

    let timeout = null;

    function doSearch() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('searchForm').submit();
        }, 500);
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>