<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LamaranController.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') die("Access denied");

// Ambil data lowongan
$lowonganData = LowonganPekerjaanController::jelajahiLowongan($conn);
extract($lowonganData);

// Ambil data kandidat
$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
if (!$candidate) {
    $_SESSION['error'] = 'Profil belum dibuat.';
    header('Location: ' . BASE_URL . 'views/dashboard.php');
    exit;
}

// Fix Error: Ambil ID job yang sudah dilamar
$appliedJobs = LamaranController::getAppliedJobIds($conn, $candidate['id']);

ob_start();
?>

<div class="min-h-screen bg-slate-50 pb-20">

    <!-- Notifikasi Alert -->
    <div id="alert-container" class="max-w-6xl mx-auto px-6 pt-6">
        <?php if (isset($_GET['applied']) && $_GET['applied'] === 'success'): ?>
            <div class="flex items-center justify-between p-4 mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm">
                <div class="flex items-center gap-3">
                    <span class="text-xl">🎉</span>
                    <div>
                        <p class="text-sm font-bold">Lamaran Berhasil!</p>
                        <p class="text-xs opacity-80">Data kualifikasi Anda telah terekam di sistem rekrutmen.</p>
                    </div>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">✕</button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Header Section -->
    <header class="max-w-6xl mx-auto px-6 pt-10 flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Jelajahi Lowongan</h1>
            <p class="text-slate-500 mt-2">Temukan karir impian Anda di platform rekrutmen inklusif.</p>
        </div>
        <div class="bg-indigo-50 text-indigo-700 px-5 py-2.5 rounded-full border border-indigo-100 text-sm font-bold shadow-sm inline-flex items-center">
            <span id="total-count" class="text-lg mr-2"><?= number_format($total) ?></span> Lowongan Aktif
        </div>
    </header>

    <!-- Search Section -->
    <section class="max-w-6xl mx-auto px-6 mb-10">
        <form id="mainSearchForm" class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative group">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 group-focus-within:text-indigo-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                    placeholder="Cari posisi atau perusahaan..."
                    class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl outline-none focus:border-indigo-600 focus:ring-4 focus:ring-indigo-50 transition-all shadow-sm">
            </div>
            <button type="submit" class="bg-indigo-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-indigo-700 transition-all shadow-lg shadow-indigo-200 active:scale-95">
                Cari Sekarang
            </button>
        </form>
    </section>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-6 flex flex-col lg:flex-row gap-10">

        <!-- Sidebar Filter -->
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white border border-slate-200 rounded-3xl p-8 sticky top-6 shadow-sm">
                <div class="flex items-center justify-between mb-8 pb-4 border-b border-slate-100">
                    <span class="font-extrabold text-slate-900">Filter</span>
                    <a href="?" class="text-xs font-bold text-indigo-600 hover:underline">Reset</a>
                </div>

                <form id="sidebarForm" class="space-y-8">
                    <!-- Tipe Pekerjaan -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Tipe Pekerjaan</label>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="radio" name="tipe_pekerjaan" value="" <?= empty($filters['tipe_pekerjaan']) ? 'checked' : '' ?> class="w-5 h-5 accent-indigo-600">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Semua Tipe</span>
                            </label>
                            <?php foreach (['Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance'] as $t): ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="tipe_pekerjaan" value="<?= $t ?>" <?= ($filters['tipe_pekerjaan'] ?? '') === $t ? 'checked' : '' ?> class="w-5 h-5 accent-indigo-600">
                                    <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors"><?= $t ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="border-t border-slate-100"></div>

                    <!-- Fitur Khusus -->
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4 block">Fitur Khusus</label>
                        <div class="space-y-3">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_disabilitas" value="1" <?= ($filters['is_disabilitas'] ?? '') == '1' ? 'checked' : '' ?> class="w-5 h-5 rounded accent-indigo-600">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Ramah Disabilitas</span>
                            </label>
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="is_remote_work" value="1" <?= ($filters['is_remote_work'] ?? '') == '1' ? 'checked' : '' ?> class="w-5 h-5 rounded accent-indigo-600">
                                <span class="text-sm text-slate-600 group-hover:text-slate-900 transition-colors">Remote Work</span>
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        <!-- Job List -->
        <div class="flex-1">
            <div id="job-wrapper" class="space-y-4">
                <?php if (empty($jobs)): ?>
                    <div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-20 text-center">
                        <span class="text-4xl mb-4 block">🔎</span>
                        <h4 class="text-lg font-bold text-slate-800">Tidak ada lowongan</h4>
                        <p class="text-sm text-slate-500">Coba ubah filter atau kata kunci pencarian Anda.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job):
                        $isApplied = in_array($job['id'], $appliedJobs);
                        $skills = $job['skills'] ? explode(', ', $job['skills']) : [];
                    ?>
                        <!-- Job Card -->
                        <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:border-indigo-200 transition-all group flex flex-col sm:flex-row gap-6 border-l-4 hover:border-l-indigo-600">
                            <!-- Logo Placeholder -->
                            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl font-black border border-indigo-100 flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                <?= strtoupper(substr($job['judul_job'], 0, 1)) ?>
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="text-lg font-extrabold text-slate-900 truncate group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($job['judul_job']) ?></h3>
                                        <div class="flex items-center gap-2 text-slate-500 text-sm mt-1 font-medium">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            <?= htmlspecialchars($job['lokasi']) ?>
                                        </div>
                                    </div>
                                    <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-slate-200"><?= $job['tipe_pekerjaan'] ?></span>
                                </div>

                                <!-- Skills -->
                                <div class="flex flex-wrap gap-2 my-4">
                                    <?php foreach (array_slice($skills, 0, 4) as $s): ?>
                                        <span class="bg-slate-50 text-slate-500 px-3 py-1 rounded-lg text-xs font-semibold border border-slate-100"><?= htmlspecialchars($s) ?></span>
                                    <?php endforeach; ?>
                                </div>

                                <div class="pt-4 border-t border-slate-50 flex flex-wrap items-center justify-between gap-4">
                                    <div class="flex items-center gap-6">
                                        <div>
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Estimasi Gaji</p>
                                            <p class="text-md font-black text-amber-600"><?= $job['gaji'] ? 'Rp ' . number_format($job['gaji'], 0, ',', '.') : 'Kompetitif' ?></p>
                                        </div>
                                        <div class="flex gap-2">
                                            <?php if ($job['is_disabilitas']): ?>
                                                <span class="bg-teal-50 text-teal-700 px-3 py-1 rounded-full text-[10px] font-bold border border-teal-100 inline-flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-pulse"></span> Inklusif
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($job['is_remote_work']): ?>
                                                <span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-bold border border-emerald-100 inline-flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Remote
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 w-full sm:w-auto">
                                        <a href="<?= BASE_URL ?>views/lowonganPekerjaan/detailById.php?id=<?= $job['id'] ?>" class="flex-1 sm:flex-none text-center px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Detail</a>

                                        <?php if ($isApplied): ?>
                                            <button disabled class="flex-1 sm:flex-none px-6 py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm font-bold cursor-not-allowed">✓ Sudah Dilamar</button>
                                        <?php else: ?>
                                            <a href="<?= BASE_URL ?>views/lamaran/create.php?job_id=<?= $job['id'] ?>" class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all active:scale-95">Lamar Sekarang</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div id="pagination-wrapper" class="mt-12 flex justify-center">
                <?php if ($total_pages > 1): ?>
                    <nav class="inline-flex items-center p-1 bg-white border border-slate-200 rounded-2xl shadow-sm gap-1">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <button data-page="<?= $i ?>" class="pagination-btn w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold transition-all <?= $i == $page ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50' ?>">
                                <?= $i ?>
                            </button>
                        <?php endfor; ?>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarForm = document.getElementById('sidebarForm');
        const mainSearchForm = document.getElementById('mainSearchForm');
        const jobWrapper = document.getElementById('job-wrapper');
        const paginationWrapper = document.getElementById('pagination-wrapper');
        const totalCountLabel = document.getElementById('total-count');
        const BASE_URL = '<?= BASE_URL ?>';

        // Simpan list ID yang sudah dilamar dari PHP ke JS
        const appliedJobs = <?= json_encode($appliedJobs) ?>;

        async function fetchJobs(page = 1) {
            const params = new URLSearchParams();
            params.append('search', new FormData(mainSearchForm).get('search') || '');
            for (const [k, v] of new FormData(sidebarForm).entries()) params.append(k, v);
            params.set('page', page);

            jobWrapper.style.opacity = '0.5';

            try {
                const res = await fetch(`${BASE_URL}/public/actions/get_lowongan.php?${params}`);
                const data = await res.json();
                if (data.status === 'success') {
                    renderJobs(data.jobs);
                    renderPagination(data.page, data.total_pages);
                    totalCountLabel.textContent = data.total;
                    window.history.pushState({}, '', '?' + params);
                }
            } catch (e) {
                console.error(e);
            } finally {
                jobWrapper.style.opacity = '1';
            }
        }

        function renderJobs(jobs) {
            if (!jobs.length) {
                jobWrapper.innerHTML = `<div class="bg-white border-2 border-dashed border-slate-200 rounded-3xl p-20 text-center"><span class="text-4xl mb-4 block">🔎</span><h4 class="text-lg font-bold text-slate-800">Tidak ada lowongan</h4><p class="text-sm text-slate-500">Coba ubah filter atau kata kunci pencarian Anda.</p></div>`;
                return;
            }

            jobWrapper.innerHTML = jobs.map(job => {
                const skills = job.skills ? job.skills.split(', ').slice(0, 4) : [];
                const isApplied = appliedJobs.includes(Number(job.id));
                const salary = job.gaji ? new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0
                }).format(job.gaji) : 'Kompetitif';

                return `
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-xl hover:border-indigo-200 transition-all group flex flex-col sm:flex-row gap-6 border-l-4 hover:border-l-indigo-600">
                    <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-2xl font-black border border-indigo-100 flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                        ${job.judul_job.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="text-lg font-extrabold text-slate-900 truncate group-hover:text-indigo-600 transition-colors">${job.judul_job}</h3>
                                <div class="flex items-center gap-2 text-slate-500 text-sm mt-1 font-medium">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    ${job.lokasi}
                                </div>
                            </div>
                            <span class="bg-slate-100 text-slate-600 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider border border-slate-200">${job.tipe_pekerjaan}</span>
                        </div>
                        <div class="flex flex-wrap gap-2 my-4">
                            ${skills.map(s => `<span class="bg-slate-50 text-slate-500 px-3 py-1 rounded-lg text-xs font-semibold border border-slate-100">${s}</span>`).join('')}
                        </div>
                        <div class="pt-4 border-t border-slate-50 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex items-center gap-6">
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Estimasi Gaji</p>
                                    <p class="text-md font-black text-amber-600">${salary}</p>
                                </div>
                                <div class="flex gap-2">
                                    ${job.is_disabilitas == 1 ? '<span class="bg-teal-50 text-teal-700 px-3 py-1 rounded-full text-[10px] font-bold border border-teal-100 inline-flex items-center gap-1"><span class="w-1.5 h-1.5 bg-teal-500 rounded-full animate-pulse"></span> Inklusif</span>' : ''}
                                    ${job.is_remote_work == 1 ? '<span class="bg-emerald-50 text-emerald-700 px-3 py-1 rounded-full text-[10px] font-bold border border-emerald-100 inline-flex items-center gap-1"><span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Remote</span>' : ''}
                                </div>
                            </div>
                            <div class="flex items-center gap-3 w-full sm:w-auto">
                                <a href="${BASE_URL}views/lowonganPekerjaan/detailById.php?id=${job.id}" class="flex-1 sm:flex-none text-center px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">Detail</a>
                                ${isApplied 
                                    ? '<button disabled class="flex-1 sm:flex-none px-6 py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm font-bold cursor-not-allowed">✓ Sudah Dilamar</button>' 
                                    : `<a href="${BASE_URL}views/lamaran/create.php?job_id=${job.id}" class="flex-1 sm:flex-none px-6 py-2.5 bg-indigo-600 text-white rounded-xl text-sm font-bold hover:bg-indigo-700 shadow-md shadow-indigo-100 transition-all active:scale-95">Lamar Sekarang</a>`
                                }
                            </div>
                        </div>
                    </div>
                </div>`;
            }).join('');
        }

        function renderPagination(current, total) {
            if (total <= 1) {
                paginationWrapper.innerHTML = '';
                return;
            }
            let html = `<nav class="inline-flex items-center p-1 bg-white border border-slate-200 rounded-2xl shadow-sm gap-1">`;
            for (let i = 1; i <= total; i++) {
                html += `<button data-page="${i}" class="pagination-btn w-10 h-10 flex items-center justify-center rounded-xl text-sm font-bold transition-all ${i == current ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200' : 'text-slate-500 hover:bg-slate-50'}">${i}</button>`;
            }
            paginationWrapper.innerHTML = html + `</nav>`;
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