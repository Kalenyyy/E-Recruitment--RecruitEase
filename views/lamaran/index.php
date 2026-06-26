<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') {
    die("Access denied.");
}

$candidate     = CandidateController::getCandidateByUserId($_SESSION['user_id']);
$daftarLamaran = LamaranController::getCandidateHistory($conn, $_SESSION['user_id']);
$totalLamaran  = count($daftarLamaran);

$statusConfig = [
    'administrasi' => ['bg' => '#DBEAFE', 'color' => '#1D4ED8', 'label' => 'Administrasi'],
    'interview'    => ['bg' => '#FEF3C7', 'color' => '#B45309', 'label' => 'Interview'],
    'offering'     => ['bg' => '#EDE9FE', 'color' => '#6D28D9', 'label' => 'Offering'],
    'diterima'     => ['bg' => '#D1FAE5', 'color' => '#065F46', 'label' => 'Diterima'],
    'ditolak'      => ['bg' => '#FEE2E2', 'color' => '#991B1B', 'label' => 'Ditolak'],
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
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold" style="color:#1E293B;">Lamaran Saya</h1>
            <p class="text-sm mt-0.5" style="color:#64748B;">
                Pantau status seluruh lamaran pekerjaan yang telah Anda kirim
            </p>
        </div>
    </div>

    <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition hover:opacity-90"
        style="background:#F1F5F9;color:#334155;border:1px solid #E2E8F0;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Kembali ke Lowongan
    </a>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 px-5 py-4 rounded-2xl text-sm font-semibold"
        style="background:#D1FAE5;color:#065F46;border:1px solid #A7F3D0;">
        <?= htmlspecialchars($_SESSION['success']);
        unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<!-- STAT -->
<div class="mb-6">
    <div class="inline-flex flex-col px-5 py-3 rounded-2xl"
        style="background:#EFF6FF;border:1px solid #BFDBFE;">
        <span class="text-xs font-bold uppercase tracking-wider" style="color:#3B82F6;">Total Lamaran</span>
        <span class="text-2xl font-bold mt-0.5" style="color:#1E3A8A;"><?= $totalLamaran ?></span>
    </div>
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
            <h2 class="font-bold text-base text-white">Daftar Lamaran</h2>
            <p class="text-xs mt-0.5" style="color:rgba(255,255,255,0.65);">
                <?= $totalLamaran ?> lamaran tercatat
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr style="background:#F8FAFC;border-bottom:2px solid #E2E8F0;">
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color:#64748B;">Posisi / Tipe</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color:#64748B;">Lokasi</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider" style="color:#64748B;">Ringkasan</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center" style="color:#64748B;">Status</th>
                    <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-center" style="color:#64748B;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($totalLamaran > 0): ?>
                    <?php foreach ($daftarLamaran as $item): ?>
                        <?php
                        $statusKey   = strtolower($item['status_lamaran']);
                        $cfg         = $statusConfig[$statusKey] ?? ['bg' => '#F1F5F9', 'color' => '#475569', 'label' => $item['status_lamaran']];
                        $statusUpper = strtoupper($item['status_lamaran']);
                        ?>
                        <tr class="hover:bg-slate-50 transition" style="border-bottom:1px solid #F1F5F9;">

                            <!-- POSISI -->
                            <td class="px-6 py-5">
                                <div class="font-bold text-slate-800" style="font-size:15px;">
                                    <?= htmlspecialchars($item['judul_job']) ?>
                                </div>
                                <div class="text-xs font-medium mt-1" style="color:#64748B;">
                                    <?= htmlspecialchars($item['tipe_pekerjaan']) ?>
                                </div>
                            </td>

                            <!-- LOKASI -->
                            <td class="px-6 py-5">
                                <div class="flex items-center gap-1.5 text-sm" style="color:#64748B;">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        style="flex-shrink:0;">
                                        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <?= htmlspecialchars($item['lokasi']) ?>
                                </div>
                            </td>

                            <!-- RINGKASAN -->
                            <td class="px-6 py-5">
                                <div class="text-xs space-y-1" style="color:#64748B;">
                                    <div>
                                        <span class="font-semibold text-slate-700">Keahlian:</span>
                                        <?= htmlspecialchars($item['expert_bidang'] ?? '-') ?>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-slate-700">Pengalaman:</span>
                                        <?= htmlspecialchars($item['pengalaman_bidang'] ?? '-') ?>
                                    </div>
                                    <?php if (!empty($item['catatan'])): ?>
                                        <div class="text-slate-400 truncate" style="max-width:220px;">
                                            <span class="font-semibold text-slate-700">Catatan:</span>
                                            <?= htmlspecialchars(mb_substr($item['catatan'], 0, 40)) ?><?= mb_strlen($item['catatan']) > 40 ? '...' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
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
                                <?php if ($statusUpper === 'OFFERING'): ?>
                                    <button
                                        onclick='bukaModalDetail(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                        class="inline-flex items-center gap-2 text-sm font-bold px-5 py-2.5 rounded-xl text-white transition-all hover:opacity-90 hover:shadow-lg"
                                        style="background:linear-gradient(135deg,#1E3A8A,#2563EB);box-shadow:0 2px 8px rgba(37,99,235,0.25);">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                        Lihat Detail
                                    </button>

                                <?php elseif ($statusUpper === 'DITOLAK'): ?>
                                    <span style="display:inline-flex;align-items:center;gap:5px;
                                        padding:5px 12px;border-radius:999px;
                                        background:#FEE2E2;color:#991B1B;
                                        font-size:11px;font-weight:700;letter-spacing:0.06em;">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                        Ditolak
                                    </span>

                                <?php else: ?>
                                    <span style="color:#CBD5E1;font-size:18px;line-height:1;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-16">
                            <div class="flex flex-col items-center gap-3">
                                <span class="inline-flex items-center justify-center rounded-full"
                                    style="width:64px;height:64px;background:#F1F5F9;color:#94A3B8;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                    </svg>
                                </span>
                                <p class="text-base font-bold text-slate-700">Belum ada lamaran yang dikirim</p>
                                <p class="text-sm text-slate-400">Lamar pekerjaan yang tersedia untuk memulai.</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<!-- ================================================================
     MODAL DETAIL LAMARAN
     ================================================================ -->
<div id="modalDetailLamaran"
    style="display:none;position:fixed;inset:0;z-index:50;align-items:center;justify-content:center;padding:1rem;
            background:rgba(15,23,42,0.55);backdrop-filter:blur(5px);
            opacity:0;transition:opacity 0.25s ease;"
    onclick="if(event.target===this) tutupModalDetail()">

    <div style="background:#FFFFFF;border-radius:1.75rem;width:100%;max-width:500px;
                max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px rgba(15,23,42,0.2);
                transform:translateY(1.5rem);transition:transform 0.25s ease;">

        <!-- ── Header Gradien Biru ── -->
        <div style="background:linear-gradient(135deg,#1E3A8A,#2563EB);
                    border-radius:1.75rem 1.75rem 0 0;
                    padding:1.75rem 2rem 1.75rem;
                    position:relative;">

            <!-- Tombol Close -->
            <button onclick="tutupModalDetail()"
                style="position:absolute;top:1.25rem;right:1.25rem;
                           width:34px;height:34px;border-radius:50%;
                           border:1.5px solid rgba(255,255,255,0.3);
                           background:rgba(255,255,255,0.15);
                           color:#FFFFFF;cursor:pointer;
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

            <!-- Label -->
            <p style="font-size:10px;font-weight:700;letter-spacing:0.14em;
                       text-transform:uppercase;color:rgba(255,255,255,0.6);margin:0 0 5px;">
                Detail Lamaran
            </p>

            <!-- Judul Pekerjaan -->
            <h2 id="m-judul-job"
                style="font-size:1.3rem;font-weight:700;color:#FFFFFF;margin:0 0 1rem;line-height:1.3;
                       padding-right:2.5rem;">
            </h2>

            <!-- Status Badge -->
            <span id="m-status-badge"
                style="display:inline-block;padding:4px 14px;border-radius:999px;
                         font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;
                         background:rgba(255,255,255,0.2);color:#FFFFFF;
                         border:1px solid rgba(255,255,255,0.35);">
            </span>
        </div>

        <!-- ── Body ── -->
        <div style="padding:1.5rem 2rem 2rem;">

            <!-- SEKSI OFFERING -->
            <div id="section-offering" style="display:none;">

                <!-- Kartu Gaji -->
                <!-- Kartu Gaji — SESUDAH -->
                <div style="padding:1.25rem 1.5rem;border-radius:1.25rem;margin-bottom:1rem;
            background:linear-gradient(135deg,#EFF6FF,#DBEAFE);
            border:1px solid #BFDBFE;">
                    <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
               text-transform:uppercase;color:#3B82F6;margin:0 0 5px;">
                        Gaji Ditawarkan
                    </p>
                    <p id="m-gaji-offering"
                        style="font-size:1.8rem;font-weight:700;color:#1E3A8A;margin:0;line-height:1;">
                    </p>
                </div>

                <!-- Tombol Download -->
                <a id="m-download-pdf" href="#" target="_blank"
                    style="display:flex;align-items:center;gap:12px;
                          padding:0.875rem 1.25rem;border-radius:1rem;margin-bottom:1.25rem;
                          border:1px solid #E2E8F0;background:#F8FAFC;
                          color:#334155;font-size:13px;font-weight:600;
                          text-decoration:none;transition:all 0.15s;"
                    onmouseover="this.style.background='#EFF6FF';this.style.borderColor='#BFDBFE';this.style.color='#1D4ED8'"
                    onmouseout="this.style.background='#F8FAFC';this.style.borderColor='#E2E8F0';this.style.color='#334155'">
                    <div style="width:36px;height:36px;border-radius:0.75rem;flex-shrink:0;
                                background:linear-gradient(135deg,#1E3A8A,#2563EB);
                                display:flex;align-items:center;justify-content:center;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                            stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                    </div>
                    <div style="flex:1;">
                        <p style="margin:0;font-weight:700;font-size:13px;color:inherit;">Unduh Offering Letter</p>
                        <p style="margin:2px 0 0;font-size:11px;color:#94A3B8;font-weight:500;">Dokumen PDF resmi penawaran kerja</p>
                    </div>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                        stroke="#94A3B8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        style="flex-shrink:0;">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>

                <!-- Divider -->
                <div style="border-top:1px solid #F1F5F9;margin-bottom:1.25rem;"></div>

                <!-- Aksi Terima / Tolak -->
                <div id="offering-actions"
                    style="display:none;grid-template-columns:1fr 1fr;gap:10px;">
                    <button type="button" onclick="bukaConfirm('DITERIMA')"
                        style="padding:13px;border-radius:0.875rem;border:none;
                                   background:linear-gradient(135deg,#1E3A8A,#2563EB);
                                   color:#FFFFFF;font-size:13px;font-weight:700;cursor:pointer;
                                   box-shadow:0 2px 8px rgba(37,99,235,0.3);
                                   transition:opacity 0.15s;"
                        onmouseover="this.style.opacity='0.88'"
                        onmouseout="this.style.opacity='1'">
                        Terima Penawaran
                    </button>
                    <button type="button" onclick="bukaConfirm('DITOLAK')"
                        style="padding:13px;border-radius:0.875rem;
                                   border:1.5px solid #FECACA;background:#FFFFFF;
                                   color:#DC2626;font-size:13px;font-weight:700;cursor:pointer;
                                   transition:background 0.15s,border-color 0.15s;"
                        onmouseover="this.style.background='#FEF2F2';this.style.borderColor='#FCA5A5'"
                        onmouseout="this.style.background='#FFFFFF';this.style.borderColor='#FECACA'">
                        Tolak
                    </button>
                </div>

                <!-- Sudah direspon -->
                <p id="offering-responded"
                    style="display:none;text-align:center;font-size:12px;font-weight:600;
                          color:#2563EB;background:#EFF6FF;border:1px solid #BFDBFE;
                          border-radius:0.875rem;padding:12px 0;">
                    Penawaran sudah direspon
                </p>

            </div>

            <!-- DETAIL LAMARAN (non-offering) -->
            <div id="detail-lamaran">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                    <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:1rem;padding:1rem;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                                   text-transform:uppercase;color:#94A3B8;margin:0 0 6px;">
                            Keahlian
                        </p>
                        <p id="m-expert" style="font-size:14px;font-weight:600;color:#1E293B;margin:0;"></p>
                    </div>
                    <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:1rem;padding:1rem;">
                        <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                                   text-transform:uppercase;color:#94A3B8;margin:0 0 6px;">
                            Pengalaman
                        </p>
                        <p id="m-pengalaman" style="font-size:14px;font-weight:600;color:#1E293B;margin:0;"></p>
                    </div>
                </div>

                <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:1rem;padding:1rem;">
                    <p style="font-size:10px;font-weight:700;letter-spacing:0.1em;
                               text-transform:uppercase;color:#94A3B8;margin:0 0 6px;">
                        Catatan Lamaran
                    </p>
                    <p id="m-catatan"
                        style="font-size:13px;color:#475569;line-height:1.65;margin:0;"></p>
                </div>
            </div>

        </div>
    </div>
</div>


<!-- ================================================================
     MODAL KONFIRMASI
     ================================================================ -->
<div id="confirmModal"
    style="display:none;position:fixed;inset:0;z-index:100;align-items:center;justify-content:center;padding:1rem;
            background:rgba(15,23,42,0.65);backdrop-filter:blur(6px);
            opacity:0;transition:opacity 0.2s ease;">

    <div style="background:#FFFFFF;border-radius:1.75rem;width:100%;max-width:380px;padding:2rem;
                box-shadow:0 30px 70px rgba(15,23,42,0.2);
                transform:scale(0.93);transition:transform 0.2s ease;">

        <div id="confirmIcon"
            style="width:56px;height:56px;border-radius:1rem;margin:0 auto 1.25rem;
                    display:flex;align-items:center;justify-content:center;">
        </div>

        <div style="text-align:center;margin-bottom:1.75rem;">
            <h3 id="confirmTitle"
                style="font-size:1.1rem;font-weight:700;color:#0F172A;margin:0 0 8px;"></h3>
            <p id="confirmText"
                style="font-size:13px;color:#64748B;line-height:1.6;margin:0;"></p>
        </div>

        <form action="<?= BASE_URL ?>public/actions/respond_offering.php" method="POST">
            <input type="hidden" name="id_transaksi" id="confirm-id">
            <input type="hidden" name="respon" id="confirm-respon">
            <button type="submit" id="confirmBtn"
                style="width:100%;padding:13px;border-radius:0.875rem;border:none;
                           color:#FFFFFF;font-size:13px;font-weight:700;cursor:pointer;
                           margin-bottom:8px;transition:opacity 0.15s;"
                onmouseover="this.style.opacity='0.88'"
                onmouseout="this.style.opacity='1'">
            </button>
            <button type="button" onclick="tutupConfirm()"
                style="width:100%;padding:12px;border-radius:0.875rem;border:1px solid #E2E8F0;
                           background:#FFFFFF;color:#94A3B8;font-size:13px;font-weight:600;
                           cursor:pointer;transition:background 0.15s;"
                onmouseover="this.style.background='#F8FAFC'"
                onmouseout="this.style.background='#FFFFFF'">
                Batalkan
            </button>
        </form>
    </div>
</div>


<script>
    (function() {
        'use strict';

        let currentData = null;

        const statusConfig = {
            administrasi: {
                bg: '#DBEAFE',
                color: '#1D4ED8',
                label: 'Administrasi'
            },
            interview: {
                bg: '#FEF3C7',
                color: '#B45309',
                label: 'Interview'
            },
            offering: {
                bg: '#EDE9FE',
                color: '#6D28D9',
                label: 'Offering'
            },
            diterima: {
                bg: '#D1FAE5',
                color: '#065F46',
                label: 'Diterima'
            },
            ditolak: {
                bg: '#FEE2E2',
                color: '#991B1B',
                label: 'Ditolak'
            },
        };

        function showEl(id) {
            document.getElementById(id).style.display = '';
        }

        function hideEl(id) {
            document.getElementById(id).style.display = 'none';
        }

        function gridEl(id) {
            document.getElementById(id).style.display = 'grid';
        }

        function setText(id, val) {
            document.getElementById(id).textContent = val;
        }

        function openOverlay(el, boxEl) {
            el.style.display = 'flex';
            requestAnimationFrame(() => {
                el.style.opacity = '1';
                boxEl.style.transform = 'translateY(0) scale(1)';
            });
            document.body.style.overflow = 'hidden';
        }

        function closeOverlay(el, boxEl, resetTransform) {
            el.style.opacity = '0';
            boxEl.style.transform = resetTransform;
            setTimeout(() => {
                el.style.display = 'none';
                document.body.style.overflow = '';
            }, 250);
        }

        // ── Modal Detail ──
        window.bukaModalDetail = function(data) {
            currentData = data;

            const modal = document.getElementById('modalDetailLamaran');
            const box = modal.querySelector('div');

            hideEl('section-offering');
            hideEl('offering-actions');
            hideEl('offering-responded');
            showEl('detail-lamaran');

            setText('m-judul-job', data.judul_job);

            // Status badge (di dalam header biru — pakai warna putih transparan)
            const badge = document.getElementById('m-status-badge');
            const key = (data.status_lamaran || '').toLowerCase();
            const cfg = statusConfig[key] || {
                label: data.status_lamaran
            };
            badge.textContent = cfg.label;
            badge.style.background = 'rgba(255,255,255,0.2)';
            badge.style.color = '#FFFFFF';
            badge.style.border = '1px solid rgba(255,255,255,0.35)';

            if (key === 'offering') {
                hideEl('detail-lamaran');
                showEl('section-offering');

                document.getElementById('m-download-pdf').href =
                    `<?= BASE_URL ?>public/uploads/offering/${data.file_offering}`;

                const gaji = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(data.gaji_offering || 0);
                setText('m-gaji-offering', gaji);

                if (data.status_respon_offering) {
                    showEl('offering-responded');
                } else {
                    gridEl('offering-actions');
                }
            } else {
                setText('m-expert', data.expert_bidang || '-');
                setText('m-pengalaman', data.pengalaman_bidang || '-');
                setText('m-catatan', data.catatan || 'Tidak ada catatan khusus.');
            }

            box.style.transform = 'translateY(1.5rem)';
            openOverlay(modal, box);
        };

        window.tutupModalDetail = function() {
            const modal = document.getElementById('modalDetailLamaran');
            closeOverlay(modal, modal.querySelector('div'), 'translateY(1.5rem)');
        };

        // ── Modal Konfirmasi ──
        window.bukaConfirm = function(type) {
            const modal = document.getElementById('confirmModal');
            const box = modal.querySelector('div');

            document.getElementById('confirm-id').value = currentData.id;
            document.getElementById('confirm-respon').value = type;

            const icon = document.getElementById('confirmIcon');
            const btn = document.getElementById('confirmBtn');

            if (type === 'DITERIMA') {
                icon.style.background = '#D1FAE5';
                icon.innerHTML = `<svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline></svg>`;
                setText('confirmTitle', 'Terima Penawaran Kerja?');
                setText('confirmText', 'Pastikan Anda telah membaca seluruh syarat dalam Offering Letter sebelum menerima penawaran ini.');
                btn.style.background = '#059669';
                btn.textContent = 'Ya, Terima Penawaran';
            } else {
                icon.style.background = '#FEE2E2';
                icon.innerHTML = `<svg width="26" height="26" viewBox="0 0 24 24" fill="none"
                stroke="#DC2626" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line></svg>`;
                setText('confirmTitle', 'Tolak Penawaran?');
                setText('confirmText', 'Tindakan ini tidak dapat dibatalkan. Anda yakin ingin menolak penawaran ini?');
                btn.style.background = '#DC2626';
                btn.textContent = 'Ya, Tolak Penawaran';
            }

            box.style.transform = 'scale(0.93)';
            openOverlay(modal, box);
        };

        window.tutupConfirm = function() {
            const modal = document.getElementById('confirmModal');
            closeOverlay(modal, modal.querySelector('div'), 'scale(0.93)');
        };

    })();
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>