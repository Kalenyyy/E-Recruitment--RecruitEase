<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isCandidate() or die("Access denied");

$lowonganData = LowonganPekerjaanController::jelajahiLowongan($conn);
extract($lowonganData);

ob_start();
?>

<style>
    /* ── Design tokens ───────────────────────────────────── */
    :root {
        --ink: #1A1D2E;
        /* near-black for headings    */
        --ink-muted: #5A607A;
        /* body / secondary text      */
        --ink-faint: #9299B0;
        /* placeholder / meta         */
        --surface: #F4F6FB;
        /* page background            */
        --card: #FFFFFF;
        --border: #E4E8F3;

        --brand: #4F46E5;
        /* indigo-600 — primary       */
        --brand-dark: #3730A3;
        /* indigo-800                 */
        --brand-pale: #EEF0FF;
        /* indigo-50                  */
        --brand-mid: #C7D2FE;
        /* indigo-200                 */

        --teal: #0D9488;
        /* teal-600 — inklusif badge  */
        --teal-pale: #CCFBF1;
        --emerald: #059669;
        /* emerald — remote badge     */
        --emerald-pale: #D1FAE5;

        --amber: #B45309;
        /* amber-700 — salary         */
        --amber-pale: #FEF3C7;

        --radius-sm: 8px;
        --radius-md: 14px;
        --radius-lg: 22px;
        --radius-xl: 32px;

        --shadow-card: 0 2px 12px 0 rgba(79, 70, 229, .06), 0 1px 3px 0 rgba(26, 29, 46, .05);
        --shadow-hover: 0 8px 32px 0 rgba(79, 70, 229, .13), 0 2px 8px 0 rgba(26, 29, 46, .08);
    }

    /* ── Base reset for this page ────────────────────────── */
    .jl-wrap * {
        box-sizing: border-box;
    }

    .jl-wrap {
        font-family: 'Inter', system-ui, sans-serif;
        background: var(--surface);
        min-height: 100vh;
        padding: 0 0 80px;
    }

    /* ── Header ──────────────────────────────────────────── */
    .jl-header {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 28px 0;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 32px;
    }

    .jl-header h1 {
        font-size: 2rem;
        font-weight: 800;
        color: var(--ink);
        letter-spacing: -0.5px;
        margin: 0 0 6px;
    }

    .jl-header p {
        color: var(--ink-muted);
        margin: 0;
        font-size: .95rem;
    }

    .jl-count-pill {
        background: var(--brand-pale);
        color: var(--brand);
        padding: 8px 18px;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
        letter-spacing: .3px;
        border: 1px solid var(--brand-mid);
        white-space: nowrap;
    }

    .jl-count-pill span {
        font-size: 1.1rem;
        margin-right: 4px;
    }

    /* ── Search bar ──────────────────────────────────────── */
    .jl-search-section {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 28px 36px;
    }

    .jl-search-row {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }

    .jl-search-field {
        flex: 1;
        min-width: 220px;
        background: var(--card);
        border: 1.5px solid var(--border);
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 0 18px;
        transition: border-color .2s, box-shadow .2s;
        box-shadow: var(--shadow-card);
    }

    .jl-search-field:focus-within {
        border-color: var(--brand);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, .1);
    }

    .jl-search-field svg {
        color: var(--ink-faint);
        flex-shrink: 0;
    }

    .jl-search-field:focus-within svg {
        color: var(--brand);
    }

    .jl-search-field input {
        flex: 1;
        border: none;
        outline: none;
        background: transparent;
        font-size: .95rem;
        color: var(--ink);
        padding: 15px 0;
    }

    .jl-search-field input::placeholder {
        color: var(--ink-faint);
    }

    .jl-search-btn {
        background: var(--brand);
        color: #fff;
        border: none;
        cursor: pointer;
        font-size: .92rem;
        font-weight: 700;
        padding: 0 32px;
        border-radius: var(--radius-lg);
        transition: background .2s, transform .1s;
        box-shadow: 0 4px 14px rgba(79, 70, 229, .3);
        white-space: nowrap;
        min-height: 54px;
    }

    .jl-search-btn:hover {
        background: var(--brand-dark);
    }

    .jl-search-btn:active {
        transform: scale(.97);
    }

    /* ── Main layout ─────────────────────────────────────── */
    .jl-main {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 28px;
        display: flex;
        gap: 28px;
        align-items: flex-start;
    }

    /* ── Sidebar ─────────────────────────────────────────── */
    .jl-sidebar {
        width: 260px;
        flex-shrink: 0;
        background: var(--card);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border);
        padding: 28px 22px;
        box-shadow: var(--shadow-card);
        position: sticky;
        top: 20px;
    }

    .jl-sidebar-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 24px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--border);
    }

    .jl-sidebar-head span {
        font-weight: 800;
        color: var(--ink);
        font-size: .95rem;
    }

    .jl-sidebar-head a {
        font-size: .78rem;
        font-weight: 700;
        color: var(--brand);
        text-decoration: none;
        letter-spacing: .2px;
    }

    .jl-sidebar-head a:hover {
        text-decoration: underline;
    }

    .jl-filter-group {
        margin-bottom: 26px;
    }

    .jl-filter-group:last-child {
        margin-bottom: 0;
    }

    .jl-filter-label {
        font-size: .7rem;
        font-weight: 800;
        color: var(--ink-faint);
        letter-spacing: 1.2px;
        text-transform: uppercase;
        margin-bottom: 14px;
        display: block;
    }

    .jl-radio-row,
    .jl-check-row {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 10px;
        cursor: pointer;
    }

    .jl-radio-row input[type="radio"],
    .jl-check-row input[type="checkbox"] {
        accent-color: var(--brand);
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .jl-radio-row span,
    .jl-check-row span {
        font-size: .875rem;
        color: var(--ink-muted);
        transition: color .15s;
    }

    .jl-radio-row:hover span,
    .jl-check-row:hover span {
        color: var(--ink);
    }

    .jl-filter-divider {
        border: none;
        border-top: 1px solid var(--border);
        margin: 20px 0;
    }

    /* ── Job grid ────────────────────────────────────────── */
    .jl-grid {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* ── Job card ────────────────────────────────────────── */
    .jl-card {
        background: var(--card);
        border: 1px solid var(--border);
        border-left: 4px solid transparent;
        border-radius: var(--radius-lg);
        padding: 24px 26px;
        display: flex;
        gap: 20px;
        transition: box-shadow .22s, border-color .22s, transform .18s;
        box-shadow: var(--shadow-card);
        text-decoration: none;
        color: inherit;
    }

    .jl-card:hover {
        box-shadow: var(--shadow-hover);
        border-left-color: var(--brand);
        transform: translateY(-2px);
    }

    .jl-card-logo {
        width: 52px;
        height: 52px;
        flex-shrink: 0;
        background: var(--brand-pale);
        border-radius: var(--radius-md);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        font-weight: 900;
        color: var(--brand);
        border: 1.5px solid var(--brand-mid);
        transition: background .2s, color .2s;
    }

    .jl-card:hover .jl-card-logo {
        background: var(--brand);
        color: #fff;
        border-color: var(--brand);
    }

    .jl-card-body {
        flex: 1;
        min-width: 0;
    }

    .jl-card-top {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
    }

    .jl-card-title {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--ink);
        transition: color .15s;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 380px;
    }

    .jl-card:hover .jl-card-title {
        color: var(--brand);
    }

    .jl-card-location {
        font-size: .82rem;
        color: var(--ink-faint);
        display: flex;
        align-items: center;
        gap: 5px;
        margin-top: 3px;
    }

    .jl-card-type {
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        background: #F1F2F8;
        color: var(--ink-muted);
        padding: 5px 12px;
        border-radius: 999px;
        border: 1px solid var(--border);
        white-space: nowrap;
    }

    .jl-skills {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin: 14px 0;
    }

    .jl-skill {
        font-size: .76rem;
        font-weight: 600;
        background: var(--surface);
        color: var(--ink-muted);
        padding: 4px 12px;
        border-radius: 999px;
        border: 1px solid var(--border);
    }

    .jl-card-footer {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .jl-salary-group {
        display: flex;
        flex-direction: column;
    }

    .jl-salary-label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .8px;
        color: var(--ink-faint);
        margin-bottom: 3px;
    }

    .jl-salary-value {
        font-size: 1.05rem;
        font-weight: 800;
        color: var(--amber);
    }

    .jl-badges {
        display: flex;
        gap: 7px;
        flex-wrap: wrap;
    }

    .jl-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: .71rem;
        font-weight: 700;
        padding: 5px 11px;
        border-radius: 999px;
    }

    .jl-badge-inklusif {
        background: var(--teal-pale);
        color: var(--teal);
    }

    .jl-badge-remote {
        background: var(--emerald-pale);
        color: var(--emerald);
    }

    .jl-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        flex-shrink: 0;
    }

    .jl-badge-inklusif .jl-badge-dot {
        background: var(--teal);
        animation: pulse 1.8s infinite;
    }

    .jl-badge-remote .jl-badge-dot {
        background: var(--emerald);
    }

    @keyframes pulse {

        0%,
        100% {
            opacity: 1
        }

        50% {
            opacity: .4
        }
    }

    .jl-card-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .jl-btn-detail {
        font-size: .85rem;
        font-weight: 700;
        color: var(--ink-muted);
        text-decoration: none;
        padding: 9px 16px;
        border-radius: var(--radius-md);
        transition: background .15s, color .15s;
        border: 1px solid transparent;
    }

    .jl-btn-detail:hover {
        background: var(--surface);
        color: var(--ink);
        border-color: var(--border);
    }

    .jl-btn-apply {
        font-size: .85rem;
        font-weight: 800;
        color: #fff;
        text-decoration: none;
        background: var(--brand);
        padding: 9px 24px;
        border-radius: var(--radius-md);
        transition: background .15s, transform .1s;
        box-shadow: 0 3px 10px rgba(79, 70, 229, .3);
        white-space: nowrap;
    }

    .jl-btn-apply:hover {
        background: var(--brand-dark);
    }

    .jl-btn-apply:active {
        transform: scale(.97);
    }

    /* ── Empty state ─────────────────────────────────────── */
    .jl-empty {
        background: var(--card);
        border: 2px dashed var(--border);
        border-radius: var(--radius-xl);
        padding: 72px 24px;
        text-align: center;
    }

    .jl-empty-icon {
        font-size: 2.5rem;
        margin-bottom: 16px;
    }

    .jl-empty h4 {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--ink);
        margin: 0 0 8px;
    }

    .jl-empty p {
        color: var(--ink-muted);
        font-size: .9rem;
        margin: 0;
    }

    /* ── Pagination ──────────────────────────────────────── */
    .jl-pagination {
        margin-top: 36px;
        display: flex;
        justify-content: center;
    }

    .jl-pagination nav {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--card);
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 6px;
        box-shadow: var(--shadow-card);
    }

    .jl-pagination .pagination-btn {
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--radius-sm);
        font-weight: 700;
        font-size: .88rem;
        border: none;
        cursor: pointer;
        background: transparent;
        color: var(--ink-muted);
        transition: background .15s, color .15s;
    }

    .jl-pagination .pagination-btn:hover {
        background: var(--surface);
        color: var(--ink);
    }

    .jl-pagination .pagination-btn.active {
        background: var(--brand);
        color: #fff;
        box-shadow: 0 2px 8px rgba(79, 70, 229, .35);
    }

    /* ── Loading state ───────────────────────────────────── */
    .jl-grid.loading {
        opacity: .45;
        pointer-events: none;
        transition: opacity .2s;
    }

    /* ── Responsive ──────────────────────────────────────── */
    @media (max-width: 900px) {
        .jl-main {
            flex-direction: column;
        }

        .jl-sidebar {
            width: 100%;
            position: static;
        }
    }

    @media (max-width: 600px) {
        .jl-header h1 {
            font-size: 1.5rem;
        }

        .jl-card {
            flex-direction: column;
        }

        .jl-card-title {
            max-width: 100%;
            white-space: normal;
        }

        .jl-card-footer {
            flex-direction: column;
            align-items: flex-start;
        }

        .jl-card-actions {
            width: 100%;
        }

        .jl-btn-apply {
            flex: 1;
            text-align: center;
        }
    }
</style>

<div class="jl-wrap">

    <!-- ═══ HEADER ═══ -->
    <header class="jl-header">
        <div>
            <h1>Jelajahi Lowongan</h1>
            <p>Temukan karir impian Anda di platform rekrutmen inklusif.</p>
        </div>
        <div class="jl-count-pill">
            <span id="total-count"><?= number_format($total) ?></span> Lowongan aktif
        </div>
    </header>

    <!-- ═══ SEARCH ═══ -->
    <section class="jl-search-section">
        <form id="mainSearchForm" class="jl-search-row">
            <div class="jl-search-field">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                    placeholder="Cari posisi atau perusahaan…" autocomplete="off">
            </div>
            <button type="submit" class="jl-search-btn">Cari Lowongan</button>
        </form>
    </section>

    <!-- ═══ MAIN ═══ -->
    <main class="jl-main">

        <!-- Sidebar Filter -->
        <aside class="jl-sidebar">
            <div class="jl-sidebar-head">
                <span>Filter</span>
                <a href="?">Reset semua</a>
            </div>

            <form id="sidebarForm">
                <!-- Tipe pekerjaan -->
                <div class="jl-filter-group">
                    <span class="jl-filter-label">Tipe Pekerjaan</span>
                    <label class="jl-radio-row">
                        <input type="radio" name="tipe_pekerjaan" value=""
                            <?= empty($filters['tipe_pekerjaan']) ? 'checked' : '' ?>>
                        <span>Semua tipe</span>
                    </label>
                    <?php foreach (['Full Time', 'Part Time', 'Contract', 'Internship'] as $t): ?>
                        <label class="jl-radio-row">
                            <input type="radio" name="tipe_pekerjaan" value="<?= $t ?>"
                                <?= ($filters['tipe_pekerjaan'] ?? '') === $t ? 'checked' : '' ?>>
                            <span><?= $t ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <hr class="jl-filter-divider">

                <!-- Fitur tambahan -->
                <div class="jl-filter-group">
                    <span class="jl-filter-label">Fitur Khusus</span>
                    <label class="jl-check-row">
                        <input type="checkbox" name="is_disabilitas" value="1"
                            <?= ($filters['is_disabilitas'] ?? '') == '1' ? 'checked' : '' ?>>
                        <span>Ramah Disabilitas</span>
                    </label>
                    <label class="jl-check-row">
                        <input type="checkbox" name="is_remote_work" value="1"
                            <?= ($filters['is_remote_work'] ?? '') == '1' ? 'checked' : '' ?>>
                        <span>Remote Work</span>
                    </label>
                </div>
            </form>
        </aside>

        <!-- Job List -->
        <div class="jl-grid" id="job-wrapper">
            <?php if (empty($jobs)): ?>
                <div class="jl-empty">
                    <div class="jl-empty-icon">🔎</div>
                    <h4>Tidak ada lowongan ditemukan</h4>
                    <p>Coba ubah kata kunci atau hapus filter yang aktif.</p>
                </div>
            <?php else: ?>
                <?php foreach ($jobs as $job):
                    $skills = $job['skills'] ? explode(', ', $job['skills']) : [];
                ?>
                    <div class="jl-card">
                        <div class="jl-card-logo"><?= strtoupper(substr($job['judul_job'], 0, 1)) ?></div>
                        <div class="jl-card-body">
                            <div class="jl-card-top">
                                <div>
                                    <div class="jl-card-title"><?= htmlspecialchars($job['judul_job']) ?></div>
                                    <div class="jl-card-location">
                                        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        </svg>
                                        <?= htmlspecialchars($job['lokasi']) ?>
                                    </div>
                                </div>
                                <span class="jl-card-type"><?= $job['tipe_pekerjaan'] ?></span>
                            </div>

                            <?php if ($skills): ?>
                                <div class="jl-skills">
                                    <?php foreach (array_slice($skills, 0, 5) as $s): ?>
                                        <span class="jl-skill"><?= htmlspecialchars($s) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="jl-card-footer">
                                <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;">
                                    <div class="jl-salary-group">
                                        <span class="jl-salary-label">Estimasi Gaji</span>
                                        <span class="jl-salary-value"><?= $job['gaji'] ? 'Rp ' . number_format($job['gaji'], 0, ',', '.') : 'Kompetitif' ?></span>
                                    </div>
                                    <div class="jl-badges">
                                        <?php if ($job['is_disabilitas']): ?>
                                            <span class="jl-badge jl-badge-inklusif">
                                                <span class="jl-badge-dot"></span>Inklusif
                                            </span>
                                        <?php endif; ?>
                                        <?php if ($job['is_remote_work']): ?>
                                            <span class="jl-badge jl-badge-remote">
                                                <span class="jl-badge-dot"></span>Remote
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="jl-card-actions">
                                    <a href="/candidate/lowongan/detail?id=<?= $job['id'] ?>" class="jl-btn-detail">Lihat Detail</a>
                                    <a href="/candidate/lamaran/buat?job_id=<?= $job['id'] ?>" class="jl-btn-apply">Lamar Sekarang</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <!-- Pagination -->
    <div id="pagination-wrapper" class="jl-pagination">
        <?php if ($total_pages > 1): ?>
            <nav>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <button data-page="<?= $i ?>"
                        class="pagination-btn <?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </button>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    </div>

</div><!-- .jl-wrap -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarForm = document.getElementById('sidebarForm');
        const mainSearchForm = document.getElementById('mainSearchForm');
        const jobWrapper = document.getElementById('job-wrapper');
        const paginationWrapper = document.getElementById('pagination-wrapper');
        const totalCountLabel = document.getElementById('total-count');
        const BASE_URL = '<?= BASE_URL ?>';

        const formatIDR = v => v ?
            new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0
            }).format(v) :
            'Kompetitif';

        async function fetchJobs(page = 1) {
            const params = new URLSearchParams();
            params.append('search', new FormData(mainSearchForm).get('search') || '');
            for (const [k, v] of new FormData(sidebarForm).entries()) params.append(k, v);
            params.set('page', page);

            jobWrapper.classList.add('loading');

            try {
                const res = await fetch(`${BASE_URL}/public/actions/get_lowongan.php?${params}`);
                const data = await res.json();
                if (data.status === 'success') {
                    renderJobs(data.jobs);
                    renderPagination(data.page, data.total_pages);
                    totalCountLabel.textContent = Number(data.total).toLocaleString('id-ID');
                    window.history.pushState({}, '', '?' + params);
                }
            } catch (e) {
                console.error(e);
            } finally {
                jobWrapper.classList.remove('loading');
            }
        }

        function card(job) {
            const skills = job.skills ? job.skills.split(', ') : [];
            const skillsHtml = skills.slice(0, 5)
                .map(s => `<span class="jl-skill">${s}</span>`).join('');
            const bInklusif = job.is_disabilitas == 1 ?
                `<span class="jl-badge jl-badge-inklusif"><span class="jl-badge-dot"></span>Inklusif</span>` : '';
            const bRemote = job.is_remote_work == 1 ?
                `<span class="jl-badge jl-badge-remote"><span class="jl-badge-dot"></span>Remote</span>` : '';

            return `
    <div class="jl-card">
      <div class="jl-card-logo">${job.judul_job.charAt(0).toUpperCase()}</div>
      <div class="jl-card-body">
        <div class="jl-card-top">
          <div>
            <div class="jl-card-title">${job.judul_job}</div>
            <div class="jl-card-location">
              <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              </svg>
              ${job.lokasi}
            </div>
          </div>
          <span class="jl-card-type">${job.tipe_pekerjaan}</span>
        </div>
        ${skillsHtml ? `<div class="jl-skills">${skillsHtml}</div>` : ''}
        <div class="jl-card-footer">
          <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;">
            <div class="jl-salary-group">
              <span class="jl-salary-label">Estimasi Gaji</span>
              <span class="jl-salary-value">${formatIDR(job.gaji)}</span>
            </div>
            <div class="jl-badges">${bInklusif}${bRemote}</div>
          </div>
          <div class="jl-card-actions">
            <a href="/candidate/lowongan/detail?id=${job.id}" class="jl-btn-detail">Lihat Detail</a>
            <a href="/candidate/lamaran/buat?job_id=${job.id}" class="jl-btn-apply">Lamar Sekarang</a>
          </div>
        </div>
      </div>
    </div>`;
        }

        function renderJobs(jobs) {
            if (!jobs.length) {
                jobWrapper.innerHTML = `
        <div class="jl-empty">
          <div class="jl-empty-icon">🔎</div>
          <h4>Tidak ada lowongan ditemukan</h4>
          <p>Coba ubah kata kunci atau hapus filter yang aktif.</p>
        </div>`;
                return;
            }
            jobWrapper.innerHTML = jobs.map(card).join('');
        }

        function renderPagination(current, total) {
            if (total <= 1) {
                paginationWrapper.innerHTML = '';
                return;
            }
            let html = '<nav>';
            for (let i = 1; i <= total; i++) {
                html += `<button data-page="${i}" class="pagination-btn ${i == current ? 'active' : ''}">${i}</button>`;
            }
            paginationWrapper.innerHTML = html + '</nav>';
        }

        sidebarForm.addEventListener('change', () => fetchJobs(1));
        mainSearchForm.addEventListener('submit', e => {
            e.preventDefault();
            fetchJobs(1);
        });
        paginationWrapper.addEventListener('click', e => {
            const btn = e.target.closest('.pagination-btn');
            if (btn) {
                fetchJobs(btn.dataset.page);
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>