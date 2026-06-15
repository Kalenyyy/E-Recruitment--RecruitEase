<?php
// views/lamaran/index.php

require_once __DIR__ . '/../../init.php';

// 1. Proteksi Keamanan Akses Sesi Halaman
AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') {
    die("Access denied. Hanya akun kandidat yang dapat mengakses halaman ini.");
}

// 2. Ambil ID Kandidat berdasarkan user_id dari Session yang aktif
$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
if (!$candidate) {
    die("Profil kandidat tidak ditemukan. Silakan lengkapi profil Anda terlebih dahulu.");
}

// 3. Tarik data riwayat lamaran dari database
$daftarLamaran = LamaranModel::getLamaranByCandidateId($conn, $candidate['id']);
$totalLamaran = count($daftarLamaran);

ob_start();
?>

<style>
    /* ── Desain Tokens Aplikasi (Disamakan dengan Detail & Index Lowongan) ── */
    :root {
        --ink: #1A1D2E;
        --ink-muted: #5A607A;
        --ink-faint: #9299B0;
        --surface: #F4F6FB;
        --card: #FFFFFF;
        --border: #E4E8F3;
        --brand: #4F46E5;
        --brand-dark: #3730A3;
        --brand-pale: #EEF0FF;
        --radius-sm: 8px;
        --radius-md: 14px;
        --radius-lg: 22px;
        --radius-xl: 32px;
        --shadow-card: 0 2px 12px 0 rgba(79, 70, 229, .06), 0 1px 3px 0 rgba(26, 29, 46, .05);
        --shadow-hover: 0 8px 32px 0 rgba(79, 70, 229, .1), 0 2px 8px 0 rgba(26, 29, 46, .06);
    }

    .vls-wrap * { box-sizing: border-box; }

    .vls-wrap {
        font-family: 'Inter', system-ui, sans-serif;
        background: var(--surface);
        min-height: 100vh;
        padding: 40px 0 80px;
    }

    .vls-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 28px;
    }

    /* ── Back Navigation Link ── */
    .vls-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--ink-muted);
        text-decoration: none;
        font-size: .9rem;
        font-weight: 700;
        margin-bottom: 24px;
        transition: color .2s;
    }

    .vls-back:hover {
        color: var(--brand);
    }

    /* ── HEADER STYLE (KEMBAR DENGAN JELAJAHI LOWONGAN) ── */
    .jl-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 36px;
    }

    .jl-header h1 {
        font-size: 1.85rem;
        font-weight: 800;
        color: var(--ink);
        margin: 0 0 6px;
        letter-spacing: -0.5px;
    }

    .jl-header p {
        color: var(--ink-muted);
        margin: 0;
        font-size: 0.95rem;
    }

    .jl-count-pill {
        background: var(--brand-pale);
        color: var(--brand);
        border: 1px solid #C7D2FE;
        padding: 8px 18px;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .jl-count-pill span {
        font-weight: 900;
    }

    /* ── List Grid Cards Lamaran ── */
    .vls-grid {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .vls-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 26px 32px;
        box-shadow: var(--shadow-card);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .vls-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-hover);
        border-color: var(--brand);
    }

    .vls-job-info {
        flex: 1;
    }

    .vls-job-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--ink);
        margin: 0 0 6px;
        letter-spacing: -0.3px;
    }

    .vls-job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        color: var(--ink-muted);
        font-size: 0.85rem;
        margin-bottom: 12px;
    }

    .vls-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .vls-date-badge {
        font-size: 0.78rem;
        color: var(--ink-faint);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    /* ── Badges Status Alur Seleksi ── */
    .vls-status-wrap {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 14px;
        min-width: 160px;
    }

    .vls-badge {
        display: inline-flex;
        align-items: center;
        font-size: 0.75rem;
        font-weight: 700;
        padding: 6px 16px;
        border-radius: 999px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .vls-badge-administrasi { background: #E0F2FE; color: #0369A1; }
    .vls-badge-interview { background: #FEF3C7; color: #B45309; }
    .vls-badge-diterima { background: #D1FAE5; color: #065F46; }
    .vls-badge-ditolak { background: #FEE2E2; color: #991B1B; }

    .vls-btn-detail {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--ink-muted);
        padding: 8px 16px;
        font-size: 0.82rem;
        font-weight: 700;
        border-radius: var(--radius-sm);
        cursor: pointer;
        transition: background 0.2s, color 0.2s, border-color 0.2s;
    }

    .vls-btn-detail:hover {
        background: var(--brand-pale);
        color: var(--brand);
        border-color: var(--brand);
    }

    /* ── EMPTY STATE ── */
    .vls-empty {
        background: var(--card);
        border: 2px dashed var(--border);
        border-radius: var(--radius-xl);
        padding: 64px 24px;
        text-align: center;
        box-shadow: var(--shadow-card);
    }

    .vls-empty-icon { font-size: 3rem; margin-bottom: 16px; }
    .vls-empty h3 { font-size: 1.2rem; font-weight: 800; color: var(--ink); margin: 0 0 8px; }
    .vls-empty p { color: var(--ink-muted); font-size: 0.9rem; margin: 0 0 24px; }
    .vls-btn-explore {
        display: inline-flex;
        background: var(--brand);
        color: #FFFFFF;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 12px 28px;
        border-radius: var(--radius-md);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        transition: background 0.2s;
    }
    .vls-btn-explore:hover { background: var(--brand-dark); }

    /* ── Modal PopUp Detail Styles ── */
    .vls-modal {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(26, 29, 46, 0.5);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; pointer-events: none;
        z-index: 9999;
        transition: opacity 0.25s ease;
    }
    .vls-modal.is-open { opacity: 1; pointer-events: auto; }
    .vls-modal-card {
        background: var(--card); border-radius: var(--radius-xl);
        width: 100%; max-width: 540px; padding: 32px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        transform: translateY(20px); transition: transform 0.25s ease;
    }
    .vls-modal.is-open .vls-modal-card { transform: translateY(0); }
    .vls-modal-header { display: flex; align-items: flex-start; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 20px; }
    .vls-modal-header h2 { font-size: 1.25rem; font-weight: 800; color: var(--ink); margin: 0; }
    .vls-modal-close { background: transparent; border: none; font-size: 1.2rem; cursor: pointer; color: var(--ink-faint); }
    .vls-modal-close:hover { color: var(--ink); }
    .vls-detail-row { margin-bottom: 16px; }
    .vls-detail-label { font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: var(--ink-faint); letter-spacing: 0.5px; margin-bottom: 4px; display: block;}
    .vls-detail-val { font-size: 0.95rem; color: var(--ink); font-weight: 600; margin: 0; }
    .vls-detail-box { background: var(--surface); border: 1px solid var(--border); padding: 12px 16px; border-radius: var(--radius-sm); font-size: 0.9rem; color: var(--ink-muted); white-space: pre-line; line-height: 1.5; margin: 4px 0 0; }
</style>

<div class="vls-wrap">
    <div class="vls-container">
        
        <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="vls-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali ke Jelajahi Lowongan
        </a>

        <header class="jl-header">
            <div>
                <h1>Lamaran Saya</h1>
                <p>Pantau riwayat peninjauan berkas, kompetensi, dan status tahapan rekrutmen Anda.</p>
            </div>
            <div class="jl-count-pill">
                <span><?= number_format($totalLamaran) ?></span> Posisi dilamar
            </div>
        </header>

        <?php if (empty($daftarLamaran)): ?>
            <div class="vls-empty">
                <div class="vls-empty-icon">📄</div>
                <h3>Belum Ada Lamaran</h3>
                <p>Anda belum mengirimkan berkas lamaran ke posisi pekerjaan apa pun saat ini.</p>
                <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="vls-btn-explore">Jelajahi Lowongan</a>
            </div>
        <?php else: ?>
            <div class="vls-grid">
                <?php foreach ($daftarLamaran as $item): ?>
                    <div class="vls-card">
                        
                        <div class="vls-job-info">
                            <h2 class="vls-job-title"><?= htmlspecialchars($item['judul_job']) ?></h2>
                            <div class="vls-job-meta">
                                <div class="vls-meta-item">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                    <?= htmlspecialchars($item['lokasi']) ?>
                                </div>
                                <div class="vls-meta-item">💼 <?= htmlspecialchars($item['tipe_pekerjaan']) ?></div>
                                <div class="vls-meta-item">💰 <?= $item['gaji'] ? 'Rp ' . number_format($item['gaji'], 0, ',', '.') : 'Kompetitif' ?></div>
                            </div>
                            <div class="vls-date-badge">
                                <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Dilamar pada: <?= date('d M Y, H:i', strtotime($item['tanggal_melamar'])) ?> WIB
                            </div>
                        </div>

                        <div class="vls-status-wrap">
                            <span class="vls-badge vls-badge-<?= strtolower($item['status_lamaran']) ?>">
                                <?= htmlspecialchars($item['status_lamaran']) ?>
                            </span>
                            <button type="button" class="vls-btn-detail" 
                                    onclick="bukaModalDetail(<?= htmlspecialchars(json_encode($item)) ?>)">
                                Lihat Detail
                            </button>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<div class="vls-modal" id="modalDetailLamaran" onclick="tutupModalDetailOnOuterClick(event)">
    <div class="vls-modal-card">
        <div class="vls-modal-header">
            <h2 id="m-judul-job">Detail Informasi Lamaran</h2>
            <button type="button" class="vls-modal-close" onclick="tutupModalDetail()">✕</button>
        </div>
        <div class="vls-modal-body">
            <div class="vls-detail-row">
                <span class="vls-detail-label">Keahlian Utama Bidang</span>
                <p class="vls-detail-val" id="m-expert"></p>
            </div>
            <div class="vls-detail-row">
                <span class="vls-detail-label">Pengalaman Bidang</span>
                <p class="vls-detail-val" id="m-pengalaman"></p>
            </div>
            <div class="vls-detail-row">
                <span class="vls-detail-label">Catatan Tambahan Anda</span>
                <div class="vls-detail-box" id="m-catatan"></div>
            </div>
            <div class="vls-detail-row" style="margin-bottom: 0; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <span class="vls-detail-label">Status Peninjauan Perusahaan</span>
                <span class="vls-badge" id="m-status"></span>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('modalDetailLamaran');

    function bukaModalDetail(data) {
        document.getElementById('m-judul-job').textContent = data.judul_job;
        document.getElementById('m-expert').textContent = data.expert_bidang ? data.expert_bidang : '-';
        document.getElementById('m-pengalaman').textContent = data.pengalaman_bidang ? data.pengalaman_bidang : '-';
        document.getElementById('m-catatan').textContent = data.catatan ? data.catatan : 'Tidak ada catatan tambahan.';
        
        const badgeStatus = document.getElementById('m-status');
        badgeStatus.textContent = data.status_lamaran;
        badgeStatus.className = 'vls-badge vls-badge-' + data.status_lamaran.toLowerCase();

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function tutupModalDetail() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    function tutupModalDetailOnOuterClick(event) {
        if (event.target === modal) {
            tutupModalDetail();
        }
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>