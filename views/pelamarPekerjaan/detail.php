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

$jobDetails = PelamarPekerjaanController::getDetailJob($conn, $job_id);
$applicants = PelamarPekerjaanController::getApplicants($conn, $job_id);

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

<!-- PAGE HEADER -->
<div class="flex items-center justify-between mb-8">
    <div class="flex items-center gap-4">
        <div class="inline-flex items-center justify-center rounded-2xl"
             style="width:52px;height:52px;background:linear-gradient(135deg,#1E3A8A,#2563EB);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                 stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div>
            <nav class="flex items-center gap-1.5 text-xs font-semibold mb-1" style="color:#94A3B8;">
                <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php"
                   class="hover:underline" style="color:#3B82F6;">Manajemen Lowongan</a>
                <span>/</span>
                <span style="color:#64748B;">Daftar Pelamar</span>
            </nav>
            <h1 class="text-2xl font-bold" style="color:#1E293B;">
                <?= htmlspecialchars($jobDetails['judul_job']) ?>
            </h1>
            <p class="text-sm mt-0.5" style="color:#64748B;">
                Total <span style="color:#2563EB;font-weight:700;"><?= count($applicants) ?></span> kandidat dalam database
            </p>
        </div>
    </div>

    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php"
       class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition hover:opacity-90"
       style="background:#F1F5F9;color:#334155;border:1px solid #E2E8F0;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Kembali
    </a>
</div>

<!-- MAIN CARD -->
<div class="rounded-2xl overflow-hidden"
     style="background:#FFFFFF;border:1px solid #E2E8F0;box-shadow:0 1px 4px rgba(15,23,42,0.06);">

    <!-- CARD HEADER -->
    <div class="px-6 py-5 flex items-center gap-3"
         style="border-bottom:1px solid #E2E8F0;background:linear-gradient(135deg,#1E3A8A,#2563EB);">
        <span class="inline-flex items-center justify-center rounded-xl"
              style="width:40px;height:40px;background:rgba(255,255,255,0.15);">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path>
            </svg>
        </span>
        <div>
            <h2 class="font-bold text-base text-white">Daftar Pelamar</h2>
            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65);">
                <?= count($applicants) ?> pelamar tercatat
            </p>
        </div>
    </div>

    <?php if (empty($applicants)): ?>
        <div class="text-center py-16">
            <span class="inline-flex items-center justify-center mb-5 rounded-full"
                  style="width:72px;height:72px;background:#F1F5F9;color:#94A3B8;">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </span>
            <p class="text-base font-bold text-slate-700">Belum Ada Pelamar</p>
            <p class="text-sm text-slate-400 mt-2">Lowongan ini belum menerima lamaran dari kandidat manapun.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr style="background:#F8FAFC;border-bottom:2px solid #E2E8F0;">
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color:#64748B;">Kandidat</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color:#64748B;">Keahlian / Pengalaman</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color:#64748B;">Tanggal Melamar</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center" style="color:#64748B;">Status</th>
                        <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center" style="color:#64748B;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($applicants as $app): ?>
                        <?php
                            $status  = strtoupper($app['status_lamaran']);
                            $cfg     = $statusConfig[$status] ?? ['bg' => '#F1F5F9', 'color' => '#475569', 'label' => $app['status_lamaran']];
                            $inisial = mb_strtoupper(mb_substr($app['nama_lengkap'], 0, 1));
                        ?>
                        <tr class="hover:bg-slate-50 transition" style="border-bottom:1px solid #F1F5F9;">

                            <!-- KANDIDAT -->
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-3">
                                    <div class="inline-flex items-center justify-center rounded-xl font-bold text-white text-base"
                                         style="width:42px;height:42px;flex-shrink:0;
                                                background:linear-gradient(135deg,#1E3A8A,#2563EB);">
                                        <?= $inisial ?>
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800" style="font-size:15px;">
                                            <?= htmlspecialchars($app['nama_lengkap']) ?>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs mt-0.5" style="color:#64748B;">
                                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                <polyline points="22,6 12,13 2,6"></polyline>
                                            </svg>
                                            <?= htmlspecialchars($app['email']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- KEAHLIAN / PENGALAMAN -->
                            <td class="px-6 py-5">
                                <div class="text-xs space-y-1" style="color:#64748B;">
                                    <div>
                                        <span class="font-semibold text-slate-700">Keahlian:</span>
                                        <?= htmlspecialchars($app['expert_bidang'] ?: '-') ?>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-700">Pengalaman:</span>
                                        <?= htmlspecialchars($app['pengalaman_bidang'] ?: '-') ?>
                                    </div>
                                </div>
                            </td>

                            <!-- TANGGAL -->
                            <td class="px-6 py-5 text-sm" style="color:#64748B;">
                                <?= date('d M Y', strtotime($app['tanggal_melamar'])) ?>
                            </td>

                            <!-- STATUS -->
                            <td class="px-6 py-5 text-center">
                                <span class="inline-block px-3 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider"
                                      style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                                    <?= htmlspecialchars($cfg['label']) ?>
                                </span>
                            </td>

                            <!-- AKSI -->
                            <td class="px-6 py-5 text-center">
                                <div class="inline-flex items-center justify-center gap-2">

                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/riwayat_pelamar.php?id_transaksi=<?= $app['id_transaksi'] ?>"
                                       class="inline-flex items-center justify-center rounded-xl transition hover:opacity-90"
                                       style="width:36px;height:36px;background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;"
                                       title="Lihat Profil">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>

                                    <?php if ($status === 'ADMINISTRASI'): ?>
                                        <button type="button"
                                                onclick="openStatusModal('INTERVIEW', '<?= $app['id_transaksi'] ?>', '<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white transition hover:opacity-90"
                                                style="background:linear-gradient(135deg,#059669,#10B981);box-shadow:0 2px 6px rgba(5,150,105,0.25);">
                                            Lolos
                                        </button>
                                        <button type="button"
                                                onclick="openStatusModal('DITOLAK', '<?= $app['id_transaksi'] ?>', '<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl transition hover:bg-red-50"
                                                style="background:#FFFFFF;color:#DC2626;border:1.5px solid #FECACA;">
                                            Tolak
                                        </button>
                                    <?php endif; ?>

                                    <?php if ($status === 'INTERVIEW'): ?>
                                        <button type="button"
                                                onclick="openOfferingModal('<?= $app['id_transaksi'] ?>', '<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl text-white transition hover:opacity-90"
                                                style="background:linear-gradient(135deg,#1E3A8A,#2563EB);box-shadow:0 2px 6px rgba(37,99,235,0.25);">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M22 2 11 13"></path>
                                                <path d="M22 2 15 22 11 13 2 9l20-7z"></path>
                                            </svg>
                                            Kirim Offering
                                        </button>
                                        <button type="button"
                                                onclick="openStatusModal('DITOLAK', '<?= $app['id_transaksi'] ?>', '<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')"
                                                class="inline-flex items-center gap-1.5 text-xs font-bold px-4 py-2 rounded-xl transition hover:bg-red-50"
                                                style="background:#FFFFFF;color:#DC2626;border:1.5px solid #FECACA;">
                                            Gagal
                                        </button>
                                    <?php endif; ?>

                                </div>
                            </td>

                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>


<!-- ================================================================
     MODAL KONFIRMASI STATUS (Lolos / Tolak + Alasan)
     ================================================================ -->
<div id="statusModal"
     style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:1rem;
            background:rgba(15,23,42,0.55);backdrop-filter:blur(5px);
            opacity:0;transition:opacity 0.25s ease;"
     onclick="if(event.target===this) closeModal('statusModal')">

    <div style="background:#FFFFFF;border-radius:1.75rem;width:100%;max-width:480px;
                box-shadow:0 25px 60px rgba(15,23,42,0.18);
                transform:translateY(1.5rem);transition:transform 0.25s ease;">

        <!-- Header -->
        <div style="display:flex;align-items:center;justify-content:space-between;
                    padding:1.5rem 2rem;border-bottom:1px solid #F1F5F9;">
            <h2 id="modalTitle"
                style="font-size:1.1rem;font-weight:700;color:#0F172A;margin:0;"></h2>
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

        <form id="modalForm" method="POST" style="padding:1.5rem 2rem 2rem;">
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
                <div style="padding:1.25rem;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:1rem;">
                    <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                               text-transform:uppercase;color:#94A3B8;margin:0 0 0.875rem;">
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
                <div style="padding:1.25rem;background:#FEF2F2;border:1px solid #FECACA;border-radius:1rem;">
                    <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                               text-transform:uppercase;color:#DC2626;margin:0 0 0.875rem;">
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

    <div style="background:#FFFFFF;border-radius:1.75rem;width:100%;max-width:480px;
                box-shadow:0 25px 60px rgba(15,23,42,0.18);overflow:hidden;
                transform:translateY(1.5rem);transition:transform 0.25s ease;">

        <!-- Header Gradien -->
        <div style="background:linear-gradient(135deg,#1E3A8A,#2563EB);padding:1.5rem 2rem;position:relative;">
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
            <p style="font-size:10px;font-weight:700;letter-spacing:0.14em;
                       text-transform:uppercase;color:rgba(255,255,255,0.6);margin:0 0 5px;">
                Tahap Penawaran Kerja
            </p>
            <h2 style="font-size:1.2rem;font-weight:700;color:#FFFFFF;margin:0 0 0.875rem;padding-right:2.5rem;">
                Kirim Offering Letter
            </h2>
            <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:0.75rem;flex-shrink:0;
                            background:rgba(255,255,255,0.2);border:1px solid rgba(255,255,255,0.3);
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
                <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                           text-transform:uppercase;color:#94A3B8;margin:0 0 8px;">
                    Gaji yang Ditawarkan (IDR)
                </p>
                <div style="position:relative;">
                    <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);
                                 font-size:13px;font-weight:700;color:#94A3B8;">Rp</span>
                    <input type="number" name="gaji_offering" required placeholder="Contoh: 5000000"
                           style="width:100%;padding:12px 14px 12px 42px;border-radius:0.875rem;
                                  border:1px solid #E2E8F0;background:#F8FAFC;
                                  font-size:15px;font-weight:700;color:#1E293B;
                                  outline:none;box-sizing:border-box;transition:border-color 0.15s,background 0.15s;"
                           onfocus="this.style.borderColor='#2563EB';this.style.background='#FFFFFF'"
                           onblur="this.style.borderColor='#E2E8F0';this.style.background='#F8FAFC'">
                </div>
            </div>

            <!-- Upload PDF -->
            <div style="margin-bottom:1.5rem;">
                <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                           text-transform:uppercase;color:#94A3B8;margin:0 0 8px;">
                    Dokumen Surat Penawaran (PDF)
                </p>
                <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                              padding:1.5rem;border-radius:1rem;cursor:pointer;
                              border:1.5px dashed #BFDBFE;background:#F8FAFC;
                              transition:background 0.15s,border-color 0.15s;"
                       onmouseover="this.style.background='#EFF6FF';this.style.borderColor='#2563EB'"
                       onmouseout="this.style.background='#F8FAFC';this.style.borderColor='#BFDBFE'">
                    <div style="width:40px;height:40px;border-radius:0.75rem;margin-bottom:10px;
                                background:linear-gradient(135deg,#1E3A8A,#2563EB);
                                display:flex;align-items:center;justify-content:center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="17 8 12 3 7 8"></polyline>
                            <line x1="12" y1="3" x2="12" y2="15"></line>
                        </svg>
                    </div>
                    <p id="fileNameDisplay"
                       style="font-size:13px;font-weight:600;color:#475569;margin:0;">
                        Klik untuk upload file PDF
                    </p>
                    <p style="font-size:11px;color:#94A3B8;margin:4px 0 0;">Format: .pdf</p>
                    <input type="file" name="file_offering" accept=".pdf" required
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
(function () {
    'use strict';

    function openOverlay(el, boxEl) {
        el.style.display = 'flex';
        requestAnimationFrame(() => {
            el.style.opacity      = '1';
            boxEl.style.transform = 'translateY(0)';
        });
        document.body.style.overflow = 'hidden';
    }

    function closeOverlay(el, boxEl) {
        el.style.opacity      = '0';
        boxEl.style.transform = 'translateY(1.5rem)';
        setTimeout(() => {
            el.style.display             = 'none';
            document.body.style.overflow = '';
        }, 250);
    }

    window.openStatusModal = function (action, id_transaksi, name) {
        const modal     = document.getElementById('statusModal');
        const box       = modal.querySelector('div');
        const isLolos   = action === 'INTERVIEW';
        const infoBox   = document.getElementById('modalInfoBox');
        const icon      = document.getElementById('modalIcon');
        const submitBtn = document.getElementById('submitBtn');
        const interviewFields = document.getElementById('interviewFields');
        const alasanFields    = document.getElementById('alasanFields');
        const alasanInput     = document.getElementById('alasanInput');
        const tanggalInput    = document.getElementById('tanggalInput');

        document.getElementById('inputStatus').value = action;
        document.getElementById('modalForm').action  =
            `<?= BASE_URL ?>public/actions/update_status_interview.php?id_transaksi=${id_transaksi}`;
        document.getElementById('modalKandidat').textContent = name;

        // Reset field
        interviewFields.style.display = 'none';
        alasanFields.style.display    = 'none';
        tanggalInput.required         = false;
        alasanInput.required          = false;
        alasanInput.value             = '';

        if (isLolos) {
            document.getElementById('modalTitle').textContent   = 'Loloskan Tahap Seleksi';
            document.getElementById('modalSubtext').textContent = 'Kandidat akan lanjut ke tahap interview.';
            infoBox.style.background  = '#F0FDF4';
            infoBox.style.border      = '1px solid #BBF7D0';
            icon.style.background     = '#059669';
            icon.innerHTML            = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline></svg>`;
            submitBtn.style.background = '#059669';
            submitBtn.textContent      = 'Ya, Atur Jadwal';
            interviewFields.style.display = 'block';
            tanggalInput.required         = true;
            setMinDate();
        } else {
            document.getElementById('modalTitle').textContent   = 'Tolak Kandidat';
            document.getElementById('modalSubtext').textContent = 'Berikan alasan penolakan untuk kandidat ini.';
            infoBox.style.background  = '#FFF1F2';
            infoBox.style.border      = '1px solid #FECDD3';
            icon.style.background     = '#DC2626';
            icon.innerHTML            = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
            submitBtn.style.background = '#DC2626';
            submitBtn.textContent      = 'Ya, Tolak';
            alasanFields.style.display = 'block';
            alasanInput.required       = true;
        }

        box.style.transform = 'translateY(1.5rem)';
        openOverlay(modal, box);
    };

    window.openOfferingModal = function (id_transaksi, name) {
        const modal = document.getElementById('offeringModal');
        const box   = modal.querySelector('div');
        document.getElementById('offering_id_transaksi').value       = id_transaksi;
        document.getElementById('offering_kandidat_name').textContent = name;
        box.style.transform = 'translateY(1.5rem)';
        openOverlay(modal, box);
    };

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        closeOverlay(modal, modal.querySelector('div'));
    };

    window.updateFileName = function (input) {
        document.getElementById('fileNameDisplay').textContent =
            input.files[0] ? input.files[0].name : 'Klik untuk upload file PDF';
    };

    function setMinDate() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('tanggalInput').min = now.toISOString().slice(0, 16);
    }

})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>