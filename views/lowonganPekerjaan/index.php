<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LamaranController.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') die("Access denied");

// Ambil data lowongan via Controller
$lowonganData = LowonganPekerjaanController::jelajahiLowongan($conn);
extract($lowonganData);

// Ambil data kandidat
$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
if (!$candidate) {
    $_SESSION['error'] = 'Profil belum dibuat.';
    header('Location: ' . BASE_URL . 'views/dashboard.php');
    exit;
}

// Ambil ID job yang sudah dilamar
$appliedJobs = LamaranController::getAppliedJobIds($conn, $candidate['id']);

ob_start();
?>

<div class="min-h-screen bg-[#F8FAFC] pb-20">

    <!-- HEADER SEDERHANA -->
    <div class="max-w-7xl mx-auto px-6 pt-10 pb-6">
        <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 border-b border-slate-200 pb-8">
            <div>
                <h1 class="text-3xl font-black text-slate-800 tracking-tight">Jelajahi Lowongan</h1>
                <p class="text-slate-500 mt-1 font-medium">Temukan kesempatan karir yang sesuai dengan keahlian Anda.</p>
            </div>
            <div class="text-sm font-bold text-slate-500 bg-white px-4 py-2 rounded-xl border border-slate-200 shadow-sm">
                <i class="fa-solid fa-briefcase text-blue-600 mr-2"></i>
                Total: <span id="total-count"><?= number_format($total) ?></span> Lowongan Aktif
            </div>
        </div>
    </div>

    <!-- SEARCH BAR -->
    <div class="max-w-7xl mx-auto px-6 mb-10">
        <form id="mainSearchForm" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1 relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </div>
                <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>"
                    placeholder="Cari judul pekerjaan atau lokasi..."
                    class="w-full pl-12 pr-4 py-4 bg-white border border-slate-200 rounded-2xl outline-none focus:border-blue-600 focus:ring-4 focus:ring-blue-600/5 transition-all text-slate-700 font-medium shadow-sm">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl font-bold transition-all shadow-lg shadow-blue-600/20 active:scale-95">
                Cari Sekarang
            </button>
        </form>
    </div>

    <main class="max-w-7xl mx-auto px-6 flex flex-col lg:flex-row gap-8">

        <!-- SIDEBAR FILTERS -->
        <aside class="w-full lg:w-72 flex-shrink-0">
            <div class="bg-white border border-slate-200 rounded-2xl p-6 sticky top-6 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-slate-800 uppercase text-xs tracking-widest">
                        <i class="fa-solid fa-filter mr-2"></i> Filter
                    </h3>
                    <a href="?" class="text-[10px] font-bold text-blue-600 hover:underline tracking-tighter">RESET SEMUA</a>
                </div>

                <form id="sidebarForm" class="space-y-6">
                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-3 block">Tipe Kontrak</label>
                        <div class="space-y-2">
                            <?php foreach (['Full Time', 'Part Time', 'Contract', 'Internship', 'Freelance'] as $t): ?>
                                <label class="flex items-center gap-3 cursor-pointer group">
                                    <input type="radio" name="tipe_pekerjaan" value="<?= $t ?>" <?= ($filters['tipe_pekerjaan'] ?? '') === $t ? 'checked' : '' ?> class="w-4 h-4 accent-blue-600">
                                    <span class="text-sm font-semibold text-slate-600 group-hover:text-slate-900"><?= $t ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="h-px bg-slate-100"></div>

                    <div>
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider mb-3 block">Kebutuhan Khusus</label>
                        <div class="space-y-3">
                            <label class="flex items-center justify-between cursor-pointer group">
                                <span class="text-sm font-semibold text-slate-600 group-hover:text-slate-900">Ramah Disabilitas</span>
                                <input type="checkbox" name="is_disabilitas" value="1" <?= ($filters['is_disabilitas'] ?? '') == '1' ? 'checked' : '' ?> class="w-4 h-4 rounded accent-blue-600">
                            </label>
                            <label class="flex items-center justify-between cursor-pointer group">
                                <span class="text-sm font-semibold text-slate-600 group-hover:text-slate-900">Remote Work</span>
                                <input type="checkbox" name="is_remote_work" value="1" <?= ($filters['is_remote_work'] ?? '') == '1' ? 'checked' : '' ?> class="w-4 h-4 rounded accent-blue-600">
                            </label>
                        </div>
                    </div>
                </form>
            </div>
        </aside>

        <!-- JOB LIST -->
        <div class="flex-1">
            <div id="job-wrapper" class="space-y-4">
                <?php if (empty($jobs)): ?>
                    <div class="bg-white border border-slate-200 rounded-2xl p-16 text-center shadow-sm">
                        <i class="fa-solid fa-folder-open text-slate-200 text-5xl mb-4 block"></i>
                        <p class="text-slate-400 font-bold">Tidak ada lowongan yang ditemukan.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($jobs as $job):
                        $isApplied = in_array($job['id'], $appliedJobs);
                        $skills = $job['skills'] ? explode(', ', $job['skills']) : [];
                    ?>
                        <!-- JOB CARD -->
                        <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:border-blue-300 hover:shadow-md transition-all group">
                            <div class="flex flex-col md:flex-row justify-between gap-6">
                                <div class="flex gap-5">
                                    <div class="w-14 h-14 bg-slate-50 text-blue-600 rounded-xl flex items-center justify-center text-xl font-bold border border-slate-200 shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                        <?= strtoupper(substr($job['judul_job'], 0, 1)) ?>
                                    </div>

                                    <div>
                                        <h2 class="text-xl font-bold text-slate-800 group-hover:text-blue-600 transition-colors">
                                            <?= htmlspecialchars($job['judul_job']) ?>
                                        </h2>
                                        <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-[13px] font-semibold text-slate-500 mt-1">
                                            <span><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> <?= htmlspecialchars($job['lokasi']) ?></span>
                                            <span><i class="fa-solid fa-clock text-slate-400 mr-1"></i> <?= $job['tipe_pekerjaan'] ?></span>
                                            <span class="text-emerald-600 font-bold">
                                                <i class="fa-solid fa-money-bill-wave mr-1"></i>
                                                <?php
                                                $min = $job['gaji_min'];
                                                $max = $job['gaji_max'];

                                                if ($min > 0 && $max > 0) {
                                                    // Jika keduanya diisi
                                                    echo "Rp" . number_format($min, 0, ',', '.') . " - " . number_format($max, 0, ',', '.');
                                                } elseif ($min > 0) {
                                                    // Jika hanya Min yang diisi
                                                    echo "Mulai dari Rp" . number_format($min, 0, ',', '.');
                                                } elseif ($max > 0) {
                                                    // Jika hanya Max yang diisi
                                                    echo "Hingga Rp" . number_format($max, 0, ',', '.');
                                                } else {
                                                    // Jika keduanya kosong atau null
                                                    echo "Gaji Kompetitif";
                                                }
                                                ?>
                                            </span>
                                        </div>

                                        <!-- SKILLS LIMITED TO 4 -->
                                        <div class="flex flex-wrap gap-2 mt-4">
                                            <?php foreach (array_slice($skills, 0, 4) as $s): ?>
                                                <span class="px-3 py-1 bg-slate-50 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-200 uppercase tracking-tight">
                                                    <?= htmlspecialchars($s) ?>
                                                </span>
                                            <?php endforeach; ?>

                                            <?php if (count($skills) > 4): ?>
                                                <span class="text-[10px] font-bold text-slate-400 self-center">+<?= count($skills) - 4 ?> lainnya</span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <?php if ($job['is_disabilitas']): ?>
                                                <span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-lg border border-emerald-100 uppercase tracking-tight">
                                                    <i class="fa-solid fa-universal-access mr-1"></i> Inklusif
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($job['is_remote_work']): ?>
                                                <span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 uppercase tracking-tight">
                                                    <i class="fa-solid fa-house-laptop mr-1"></i> Remote
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <!-- ACTIONS (DETAIL & LAMAR) -->
                                <div class="flex flex-col items-center justify-center gap-2 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6 shrink-0 w-full md:w-[160px]">
                                    <a href="<?= BASE_URL ?>views/lowonganPekerjaan/detailById.php?id=<?= $job['id'] ?>"
                                        class="w-full text-center px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 transition-colors">
                                        <i class="fa-solid fa-circle-info mr-1"></i> Detail
                                    </a>

                                    <?php if ($isApplied): ?>
                                        <button disabled class="w-full px-4 py-2.5 bg-slate-100 text-slate-400 rounded-xl text-sm font-bold cursor-not-allowed">
                                            <i class="fa-solid fa-check mr-1"></i> Dilamar
                                        </button>
                                    <?php else: ?>
                                        <a href="<?= BASE_URL ?>views/lamaran/create.php?job_id=<?= $job['id'] ?>"
                                            class="w-full text-center px-4 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-sm active:scale-95">
                                            <i class="fa-solid fa-paper-plane mr-1"></i> Lamar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Pagination -->
            <div id="pagination-wrapper" class="mt-12 flex justify-center">
                <?php if ($total_pages > 1): ?>
                    <nav class="flex gap-2">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <button data-page="<?= $i ?>" class="pagination-btn w-10 h-10 rounded-xl text-sm font-bold transition-all <?= $i == $page ? 'bg-blue-600 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50' ?>">
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
                jobWrapper.innerHTML = `
                <div class="bg-white border border-slate-200 rounded-2xl p-16 text-center shadow-sm">
                    <i class="fa-solid fa-folder-open text-slate-200 text-5xl mb-4 block"></i>
                    <p class="text-slate-400 font-bold">Tidak ada lowongan ditemukan.</p>
                </div>`;
                return;
            }

            jobWrapper.innerHTML = jobs.map(job => {
                const isApplied = appliedJobs.includes(Number(job.id));
                const allSkills = job.skills ? job.skills.split(', ') : [];
                const skillsToShow = allSkills.slice(0, 4); // LIMIT 4 DISINI
                const moreCount = allSkills.length - 4;

                const charLogo = job.judul_job.charAt(0).toUpperCase();
                const salary = (job.gaji_min && job.gaji_max) ?
                    `Rp${new Intl.NumberFormat('id-ID').format(job.gaji_min)} - ${new Intl.NumberFormat('id-ID').format(job.gaji_max)}` :
                    'Gaji Kompetitif';

                return `
                <div class="bg-white border border-slate-200 rounded-2xl p-6 hover:border-blue-300 hover:shadow-md transition-all group">
                    <div class="flex flex-col md:flex-row justify-between gap-6">
                        <div class="flex gap-5">
                            <div class="w-14 h-14 bg-slate-50 text-blue-600 rounded-xl flex items-center justify-center text-xl font-bold border border-slate-200 shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                ${charLogo}
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-slate-800 group-hover:text-blue-600 transition-colors">${job.judul_job}</h2>
                                <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-[13px] font-semibold text-slate-500 mt-1">
                                    <span><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> ${job.lokasi}</span>
                                    <span><i class="fa-solid fa-clock text-slate-400 mr-1"></i> ${job.tipe_pekerjaan}</span>
                                    <span class="text-emerald-600 font-bold"><i class="fa-solid fa-money-bill-wave mr-1"></i> ${salary}</span>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 mt-4">
                                    ${skillsToShow.map(s => `<span class="px-3 py-1 bg-slate-50 text-slate-600 text-[10px] font-bold rounded-lg border border-slate-200 uppercase">${s}</span>`).join('')}
                                    ${moreCount > 0 ? `<span class="text-[10px] font-bold text-slate-400 self-center">+${moreCount} lainnya</span>` : ''}
                                </div>
                                <div class="flex flex-wrap gap-2 mt-2">
                                    ${job.is_disabilitas == 1 ? '<span class="px-3 py-1 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded-lg border border-emerald-100 uppercase"><i class="fa-solid fa-universal-access mr-1"></i> Inklusif</span>' : ''}
                                    ${job.is_remote_work == 1 ? '<span class="px-3 py-1 bg-blue-50 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 uppercase"><i class="fa-solid fa-house-laptop mr-1"></i> Remote</span>' : ''}
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-center justify-center gap-2 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6 shrink-0 w-full md:w-[160px]">
                            <a href="${BASE_URL}views/lowonganPekerjaan/detailById.php?id=${job.id}" class="w-full text-center px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 border border-slate-200 transition-colors">
                                <i class="fa-solid fa-circle-info mr-1"></i> Detail
                            </a>
                            ${isApplied 
                                ? '<button disabled class="w-full px-4 py-2.5 bg-slate-100 text-slate-400 rounded-xl text-xs font-bold cursor-not-allowed"><i class="fa-solid fa-check mr-1"></i> Dilamar</button>'
                                : `<a href="${BASE_URL}views/lamaran/create.php?job_id=${job.id}" class="w-full px-4 py-2.5 bg-blue-600 text-white rounded-xl text-xs font-bold hover:bg-blue-700 transition-all shadow-sm active:scale-95"><i class="fa-solid fa-paper-plane mr-1"></i> Lamar</a>`
                            }
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
            let html = `<nav class="flex gap-2">`;
            for (let i = 1; i <= total; i++) {
                html += `<button data-page="${i}" class="pagination-btn w-10 h-10 rounded-xl text-sm font-bold transition-all ${i == current ? 'bg-blue-600 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-500 hover:bg-slate-50'}">${i}</button>`;
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