<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') {
    die("Access denied. Hanya akun kandidat yang dapat mengakses halaman ini.");
}

// 1. Validasi ID Parameter wajib ada dan numerik
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    header("Location: " . BASE_URL . "views/lowonganPekerjaan/index.php");
    exit;
}

// 2. Ambil data detail lowongan secara spesifik
$job = LowonganPekerjaanController::detailLowongan($conn, $id);
if (!$job) {
    die("Lowongan tidak ditemukan atau sudah ditutup.");
}

$skills = $job['skills'] ? explode(', ', $job['skills']) : [];

// 3. LOGIKA INTEGRASI: Ambil ID Kandidat untuk cek riwayat apply
$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
$sudahMelamar = false;

if ($candidate) {
    $sudahMelamar = LamaranModel::hasApplied($conn, $candidate['id'], $id);
}

ob_start();
?>

<style>
    /* ── Design Tokens ──────────────── */
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
        --brand-mid: #C7D2FE;
        --teal: #0D9488;
        --teal-pale: #CCFBF1;
        --emerald: #059669;
        --emerald-pale: #D1FAE5;
        --amber: #B45309;
        --amber-pale: #FEF3C7;
        --rose: #E11D48;
        --rose-pale: #FFE4E6;
        --radius-sm: 8px;
        --radius-md: 14px;
        --radius-lg: 22px;
        --radius-xl: 32px;
        --shadow-card: 0 2px 12px 0 rgba(79, 70, 229, .06), 0 1px 3px 0 rgba(26, 29, 46, .05);
    }

    .jld-wrap * {
        box-sizing: border-box;
    }

    .jld-wrap {
        font-family: 'Inter', system-ui, sans-serif;
        background: var(--surface);
        min-height: 100vh;
        padding: 40px 0 80px;
    }

    .jld-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 28px;
    }

    /* ── Back Navigation ────────────────── */
    .jld-back {
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

    .jld-back:hover {
        color: var(--brand);
    }

    /* ── Main Layout Split ────────────────── */
    .jld-main-grid {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 28px;
        align-items: flex-start;
    }

    @media (max-width: 900px) {
        .jld-main-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ── Card Styles ──────────────────────── */
    .jld-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 36px;
        box-shadow: var(--shadow-card);
        margin-bottom: 28px;
    }

    /* ── Header Area ──────────────────────── */
    .jld-header-block {
        display: flex;
        gap: 24px;
        align-items: flex-start;
        border-bottom: 1px solid var(--border);
        padding-bottom: 32px;
        margin-bottom: 32px;
    }

    .jld-logo {
        width: 64px;
        height: 64px;
        background: var(--brand-pale);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.6rem;
        font-weight: 900;
        color: var(--brand);
        border: 1.5px solid var(--brand-mid);
    }

    .jld-header-info {
        flex: 1;
    }

    .jld-header-info h1 {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--ink);
        margin: 0 0 8px;
        letter-spacing: -0.5px;
    }

    .jld-meta-row {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        color: var(--ink-muted);
        font-size: .9rem;
    }

    .jld-meta-item {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* ── Badges ─────────────────────────── */
    .jld-badges {
        display: flex;
        gap: 8px;
        margin-top: 14px;
        flex-wrap: wrap;
    }

    .jld-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: .75rem;
        font-weight: 700;
        padding: 6px 14px;
        border-radius: 999px;
    }

    .jld-badge-type {
        background: #F1F2F8;
        color: var(--ink-muted);
        border: 1px solid var(--border);
        text-transform: uppercase;
        letter-spacing: .5px;
    }

    .jld-badge-inklusif {
        background: var(--teal-pale);
        color: var(--teal);
    }

    .jld-badge-remote {
        background: var(--emerald-pale);
        color: var(--emerald);
    }

    /* ── Content Section ─────────────────── */
    .jld-section {
        margin-bottom: 32px;
    }

    .jld-section:last-child {
        margin-bottom: 0;
    }

    .jld-section h3 {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--ink);
        margin: 0 0 16px;
    }

    .jld-rich-text {
        color: var(--ink-muted);
        font-size: .975rem;
        line-height: 1.7;
        white-space: pre-line;
    }

    /* Box Info Disabilitas Eksplisit */
    .disability-status-box {
        padding: 16px 20px;
        border-radius: var(--radius-md);
        font-size: 0.9rem;
        line-height: 1.5;
    }

    .disability-status-box.active {
        background: var(--teal-pale);
        border: 1px solid var(--teal);
        color: #0F534C;
    }

    .disability-status-box.inactive {
        background: var(--rose-pale);
        border: 1px solid var(--rose);
        color: #84102B;
    }

    /* ── Skills ─────────────────────────── */
    .jld-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .jld-skill {
        font-size: .8rem;
        font-weight: 600;
        background: var(--surface);
        color: var(--ink-muted);
        padding: 6px 14px;
        border-radius: 999px;
        border: 1px solid var(--border);
    }

    /* ── Sidebar Sticky Panel ────────────── */
    .jld-sidebar {
        position: sticky;
        top: 40px;
    }

    .jld-action-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-xl);
        padding: 28px;
        box-shadow: var(--shadow-card);
        text-align: center;
    }

    .jld-salary-box {
        background: var(--amber-pale);
        border-radius: var(--radius-md);
        padding: 16px;
        margin-bottom: 24px;
        text-align: left;
    }

    .jld-salary-label {
        font-size: .7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--amber);
        letter-spacing: .5px;
        display: block;
        margin-bottom: 4px;
    }

    .jld-salary-val {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--amber);
    }

    .jld-btn-apply {
        display: block;
        width: 100%;
        background: var(--brand);
        color: #fff;
        text-align: center;
        text-decoration: none;
        font-weight: 800;
        font-size: .95rem;
        padding: 16px;
        border-radius: var(--radius-md);
        box-shadow: 0 4px 14px rgba(79, 70, 229, .3);
        transition: background .2s, transform .1s;
        margin-bottom: 12px;
        cursor: pointer;
        border: none;
    }

    .jld-btn-apply:hover {
        background: var(--brand-dark);
    }

    .jld-btn-apply:active {
        transform: scale(.98);
    }

    .jld-btn-apply.is-disabled {
        background: #E4E8F3 !important;
        color: var(--ink-faint) !important;
        cursor: not-allowed !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .jld-alert-applied {
        background: var(--brand-pale);
        border: 1px solid var(--brand-mid);
        color: var(--brand-dark);
        border-radius: var(--radius-md);
        padding: 14px;
        font-size: 0.82rem;
        font-weight: 600;
        margin-bottom: 16px;
        text-align: left;
        line-height: 1.4;
    }

    .jld-info-tip {
        font-size: .8rem;
        color: var(--ink-faint);
        margin: 0;
    }

    /* ── FORM INPUT INLINE STYLES ──────────────── */
    #form-lamaran-container {
        display: none;
        animation: fadeIn 0.4s ease forwards;
    }

    .form-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .form-label {
        display: block;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 8px;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        font-size: 0.9rem;
        border: 1.5px solid var(--border);
        border-radius: var(--radius-sm);
        color: var(--ink);
        background: var(--surface);
        transition: border-color 0.2s;
    }

    .form-control:focus {
        outline: none;
        border-color: var(--brand);
        background: #FFFFFF;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="jld-wrap">
    <div class="jld-container">

        <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="jld-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Jelajahi Lowongan
        </a>

        <div class="jld-main-grid">

            <div class="jld-content-area">
                <div class="jld-card">
                    <div class="jld-header-block">
                        <div class="jld-logo"><?= strtoupper(substr($job['judul_job'], 0, 1)) ?></div>
                        <div class="jld-header-info">
                            <h1><?= htmlspecialchars($job['judul_job']) ?></h1>
                            <div class="jld-meta-row">
                                <div class="jld-meta-item">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    </svg>
                                    <?= htmlspecialchars($job['lokasi']) ?>
                                </div>
                            </div>
                            <div class="jld-badges">
                                <span class="jld-badge window-badge jld-badge-type"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></span>
                                <?php if ($job['is_disabilitas']): ?>
                                    <span class="jld-badge jld-badge-inklusif">✓ Ramah Disabilitas</span>
                                <?php endif; ?>
                                <?php if ($job['is_remote_work']): ?>
                                    <span class="jld-badge jld-badge-remote">🌐 Remote Work</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="jld-section">
                        <h3>Deskripsi Pekerjaan</h3>
                        <div class="jld-rich-text">
                            <?= htmlspecialchars($job['deskripsi'] ?? 'Tidak ada deskripsi yang disediakan.') ?>
                        </div>
                    </div>

                    <?php if (!empty($job['persyaratan'])): ?>
                        <div class="jld-section">
                            <h3>Persyaratan</h3>
                            <div class="jld-rich-text">
                                <?= htmlspecialchars($job['persyaratan']) ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="jld-section">
                        <h3>Informasi Aksesibilitas & Kategori Disabilitas</h3>

                        <?php if ($job['is_disabilitas']): ?>
                            <div class="disability-status-box active" style="margin-bottom: 16px;">
                                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800;">
                                    <span>♿</span> Lowongan Ini Terbuka untuk Penyandang Disabilitas
                                </div>
                            </div>

                            <p style="font-size: 0.85rem; font-weight: 700; color: var(--ink); margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                Ragam Disabilitas yang Dapat Mengajukan Lamaran:
                            </p>

                            <?php if (!empty($job['supported_disabilities'])): ?>
                                <div style="display: flex; flex-direction: column; gap: 12px;">
                                    <?php
                                    foreach ($job['supported_disabilities'] as $typeKey):
                                        // Ambil detail label dan deskripsi berdasarkan key master data
                                        if (isset($job['disability_options'][$typeKey])):
                                            $detail = $job['disability_options'][$typeKey];
                                    ?>
                                            <div style="background: #FFFFFF; border: 1px solid var(--teal); border-left: 4px solid var(--teal); padding: 12px 16px; border-radius: var(--radius-sm); display: flex; flex-direction: column; gap: 2px;">
                                                <span style="font-size: 0.9rem; font-weight: 800; color: var(--teal);">
                                                    ✓ <?= htmlspecialchars($detail['label']) ?>
                                                </span>
                                                <span style="font-size: 0.8rem; color: var(--ink-muted);">
                                                    Kualifikasi: <?= htmlspecialchars($detail['desc']) ?>
                                                </span>
                                            </div>
                                    <?php
                                        endif;
                                    endforeach;
                                    ?>
                                </div>
                            <?php else: ?>
                                <div style="background: var(--surface); padding: 14px; border-radius: var(--radius-md); border: 1px dashed var(--border);">
                                    <p style="margin: 0; font-size: 0.85rem; color: var(--ink-muted); font-style: italic;">
                                        Terbuka untuk semua ragam disabilitas umum. Silakan ajukan berkas Anda.
                                    </p>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($job['additional_support'])): ?>
                                <div style="margin-top: 16px; padding: 12px 16px; background: var(--brand-pale); border: 1px solid var(--brand-mid); border-radius: var(--radius-md);">
                                    <strong style="display: block; font-size: 0.75rem; color: var(--brand-dark); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">
                                        Dukungan & Fasilitas Aksesibilitas dari Perusahaan:
                                    </strong>
                                    <p style="margin: 0; font-size: 0.85rem; color: var(--ink-muted); font-weight: 500;">
                                        <?= htmlspecialchars($job['additional_support']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="disability-status-box inactive">
                                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800;">
                                    <span>⚠️</span> Lowongan Ini Belum Mendukung Jalur Khusus Disabilitas
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($skills): ?>
                        <div class="jld-section">
                            <h3>Keahlian yang Dibutuhkan</h3>
                            <div class="jld-skills">
                                <?php foreach ($skills as $s): ?>
                                    <span class="jld-skill"><?= htmlspecialchars($s) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- <div class="jld-card" id="form-lamaran-container">
                    <div class="jld-section">
                        <h3>Form Pengajuan Lamaran</h3>
                        <p class="jld-info-tip" style="margin-bottom: 20px;">Isi berkas tambahan jika diperlukan untuk posisi <strong><?= htmlspecialchars($job['judul_job']) ?></strong>.</p>

                        <form action="<?= BASE_URL ?>controllers/LamaranController.php?action=store" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                            <input type="hidden" name="candidate_id" value="<?= htmlspecialchars($candidate['id'] ?? '') ?>">

                            <div class="form-group">
                                <label class="form-label">Surat Pengantar / Cover Letter (Opsional)</label>
                                <textarea name="cover_letter" rows="5" class="form-control" placeholder="Tulis alasan mengapa Anda tertarik dengan posisi ini..."></textarea>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Unggah CV / Portofolio Terbaru (PDF max 2MB)</label>
                                <input type="file" name="dokumen_cv" class="form-control" required accept=".pdf">
                            </div>

                            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 24px;">
                                <button type="button" id="btn-batal-lamar" class="jld-skill" style="padding: 12px 24px; cursor: pointer;">Batal</button>
                                <button type="submit" class="jld-btn-apply" style="width: auto; margin-bottom: 0; padding: 12px 28px;">Kirim Lamaran Sekarang</button>
                            </div>
                        </form>
                    </div>
                </div> -->
            </div>

            <aside class="jld-sidebar">
                <div class="jld-action-card">
                    <div class="jld-salary-box">
                        <span class="jld-salary-label">Estimasi Gaji</span>
                        <span class="jld-salary-val">
                            <?= $job['gaji'] ? 'Rp ' . number_format($job['gaji'], 0, ',', '.') : 'Kompetitif' ?>
                        </span>
                    </div>

                    <?php if ($sudahMelamar): ?>
                        <div class="jld-alert-applied">
                            🎉 Anda sudah mengirimkan lamaran untuk posisi ini. Silakan pantau status seleksi di halaman "Lamaran Saya".
                        </div>
                        <button class="jld-btn-apply is-disabled" disabled>
                            Lamaran Sudah Terkirim
                        </button>
                    <?php else: ?>
                        <a
                            href="<?= BASE_URL ?>views/lamaran/create.php?job_id=<?= $job['id'] ?>"
                            class="jld-btn-apply">
                            Lamar Sekarang
                        </a>
                    <?php endif; ?>

                    <p class="jld-info-tip">Pastikan profil dan CV Anda sudah diperbarui sebelum melamar.</p>
                </div>
            </aside>

        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnTrigger = document.getElementById('btn-trigger-lamar');
        const btnBatal = document.getElementById('btn-batal-lamar');
        const formContainer = document.getElementById('form-lamaran-container');

        if (btnTrigger && formContainer) {
            btnTrigger.addEventListener('click', function() {
                formContainer.style.display = 'block';
                formContainer.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            });
        }

        if (btnBatal && formContainer) {
            btnBatal.addEventListener('click', function() {
                formContainer.style.display = 'none';
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>