<?php

require_once __DIR__ . '/../init.php';
$title = "Dashboard";
AuthController::requireLogin();

$role = $_SESSION['role'];
$dashboardData = [];
$candidateDashboard = [];

if ($role === 'hr' || $role === 'admin') {
    $userData = StaffController::getStaffByUserId($conn, $_SESSION['user_id'] ?? null);
    $dashboardData = DashboardController::getHRDashboardData($conn);
}

$candidate = null;
$isProfileComplete = true;
$missing = [];

if ($role === 'candidate') {
    $candidateData = CandidateController::getCandidateByUserId($_SESSION['user_id'] ?? null);
    if ($candidateData && isset($candidateData['id'])) {
        $candidateId = $candidateData['id'];
        // Hapus tanda komentar di bawah jika ingin mengaktifkan fitur deteksi profil otomatis
        // $isProfileComplete = ProfileHelper::isComplete($conn, $candidateId);
        // $missing = ProfileHelper::getMissingFields($conn, $candidateId);

        $candidateDashboard = DashboardController::getCandidateDashboardData($conn, $candidateId);
    }
}

ob_start();
?>

<div class="bg-[#F8FAFC] min-h-screen p-4 md:p-6 font-sans antialiased text-slate-800">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <?php if ($role === 'candidate' && !$isProfileComplete): ?>
                <div id="profileAlert" class="mb-5 rounded-xl border border-amber-200 bg-amber-50 p-4 flex justify-between items-start shadow-sm transition-all">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <h3 class="font-semibold text-amber-900 text-sm">Profil Belum Lengkap</h3>
                            <p class="text-xs text-amber-700 mt-1">Lengkapi data berikut sebelum melamar pekerjaan:</p>
                            <div class="mt-2 text-xs text-amber-800 space-y-1">
                                <?php if (!empty($missing)): ?>
                                    <?php foreach ($missing as $section => $items): ?>
                                        <div>
                                            <span class="font-semibold"><?= ucfirst($section) ?>:</span>
                                            <ul class="list-disc ml-5 mt-0.5 space-y-0.5">
                                                <?php foreach ($items as $item): ?>
                                                    <li><?= htmlspecialchars($item) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-amber-700">Data tidak lengkap, detail tidak tersedia.</p>
                                <?php endif; ?>
                            </div>
                            <a href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidateData['id'] ?>" class="inline-flex items-center gap-1 mt-3 text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">
                                Lengkapi Sekarang
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>
                    <button onclick="document.getElementById('profileAlert').remove()" class="text-amber-400 hover:text-amber-600 transition-colors p-1 rounded-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>

            <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Dashboard</h1>
            <p class="text-xs text-slate-500 mt-0.5">Selamat datang kembali, <span class="font-semibold text-blue-700">
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <?= $_SESSION['username'] ?? 'User' ?>
                    <?php elseif ($_SESSION['role'] == 'hr'): ?>
                        <?= $userData['nama_staff'] ?? 'User' ?>
                    <?php else: ?>
                        <?= $candidateData['nama_lengkap'] ?? 'User' ?>
                    <?php endif; ?>
                </span></p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Kotak Tanggal -->
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-600 shadow-sm">
                <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <?= date('d M Y') ?>
            </div>

            <?php if ($_SESSION['role'] !== 'candidate'): ?>
                <!-- Tombol Export (Rapi & Seimbang) -->
                <a href="<?= BASE_URL ?>views/laporan/export_rekap_status_job.php"
                    title="Export Rekapitulasi Status"
                    class="inline-flex items-center gap-2 px-3 py-2 bg-white border border-emerald-200 text-emerald-700 rounded-xl text-[11px] font-bold uppercase tracking-tight hover:bg-emerald-50 transition-all active:scale-95 shadow-sm">
                    <i class="fa-solid fa-file-excel text-sm text-emerald-500"></i>
                    <span>Export Recap</span>
                </a>

                <!-- Tombol Buat Lowongan -->
                <a href="<?= BASE_URL ?>views/formJob/create.php">
                    <button class="flex items-center gap-2 bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-all duration-200 shadow-sm active:scale-95">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Lowongan
                    </button>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($_SESSION['role'] !== 'candidate'): ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Lowongan Aktif</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= number_format($dashboardData['stats']['active_jobs'] ?? 0) ?></p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Pelamar</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= number_format($dashboardData['stats']['total_pelamar'] ?? 0) ?></p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Status Interview</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= number_format($dashboardData['stats']['total_interview'] ?? 0) ?></p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Diterima</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-rose-50 text-rose-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= number_format($dashboardData['stats']['total_hired'] ?? 0) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:col-span-2 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Grafik Pelamar Masuk</h3>
                        <p class="text-xs text-slate-400">Tren 7 hari terakhir</p>
                    </div>
                </div>
                <div class="flex gap-4 mb-3">
                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> Pelamar Masuk
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-slate-500">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Diterima
                    </span>
                </div>
                <div class="relative w-full h-[260px]">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col shadow-sm">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-900">Status Pelamar</h3>
                    <p class="text-xs text-slate-400">Distribusi per status</p>
                </div>
                <div class="relative w-full h-[200px] flex items-center justify-center">
                    <canvas id="donutChart"></canvas>
                </div>
                <div id="donutLegend" class="flex flex-col gap-2 mt-4 overflow-y-auto max-h-[120px] pr-1">
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const weeklyData = <?= json_encode($dashboardData['chart_weekly'] ?? []) ?>;
            const statusDist = <?= json_encode($dashboardData['chart_status'] ?? []) ?>;

            function buildBarChart() {
                const ctx = document.getElementById('barChart');
                if (!ctx) return;
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: weeklyData.labels || [],
                        datasets: [{
                                label: 'Pelamar',
                                data: weeklyData.pelamar || [],
                                backgroundColor: '#3b82f6',
                                borderRadius: 4,
                                barPercentage: 0.5
                            },
                            {
                                label: 'Diterima',
                                data: weeklyData.diterima || [],
                                backgroundColor: '#10b981',
                                borderRadius: 4,
                                barPercentage: 0.5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#94a3b8'
                                }
                            },
                            y: {
                                grid: {
                                    color: '#f1f5f9'
                                },
                                ticks: {
                                    font: {
                                        size: 11
                                    },
                                    color: '#94a3b8'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            function buildDonutChart() {
                const ctx = document.getElementById('donutChart');
                if (!ctx) return;

                const labels = statusDist.map(item => item.status);
                const counts = statusDist.map(item => item.jumlah);
                const colors = ['#3b82f6', '#f59e0b', '#10b981', '#f43f5e', '#8b5cf6'];

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: colors,
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '75%',
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });

                const legendContainer = document.getElementById('donutLegend');
                const total = counts.reduce((a, b) => a + b, 0);
                legendContainer.innerHTML = '';
                labels.forEach((label, i) => {
                    const percent = total > 0 ? Math.round((counts[i] / total) * 100) : 0;
                    legendContainer.innerHTML += `
                        <div class="flex items-center justify-between py-0.5 border-b border-slate-50">
                            <span class="flex items-center gap-2 text-xs text-slate-500">
                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color: ${colors[i % colors.length]}"></span> ${label}
                            </span>
                            <span class="text-xs font-semibold text-slate-700">${percent}%</span>
                        </div>
                    `;
                });
            }

            buildBarChart();
            buildDonutChart();
        </script>
    <?php endif; ?>

    <?php if ($_SESSION['role'] === 'candidate'): ?>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Total Lamaran</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= $candidateDashboard['stats']['total_apply'] ?? 0 ?></p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Sedang Direviu</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-amber-50 text-amber-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= $candidateDashboard['stats']['review'] ?? 0 ?></p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Interview</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-indigo-50 text-indigo-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= $candidateDashboard['stats']['interview'] ?? 0 ?></p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">Diterima</p>
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-emerald-50 text-emerald-600">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-slate-900"><?= $candidateDashboard['stats']['diterima'] ?? 0 ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 lg:col-span-2 shadow-sm flex flex-col">
                <div class="mb-4">
                    <h3 class="text-sm font-semibold text-slate-900">Posisi yang Dilamar</h3>
                    <p class="text-xs text-slate-400">Riwayat lamaran terbaru Anda</p>
                </div>
                <div class="overflow-x-auto -mx-5 flex-1">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-slate-50/75">
                                <th class="text-[10px] font-bold text-slate-400 uppercase tracking-wider p-4 text-left border-b border-slate-100">Posisi</th>
                                <th class="text-[10px] font-bold text-slate-400 uppercase tracking-wider p-4 text-left border-b border-slate-100">Tanggal Lamar</th>
                                <th class="text-[10px] font-bold text-slate-400 uppercase tracking-wider p-4 text-left border-b border-slate-100">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php if (!empty($candidateDashboard['applications'])): ?>
                                <?php foreach ($candidateDashboard['applications'] as $app): ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-4 text-xs font-semibold text-slate-700"><?= htmlspecialchars($app['judul_job']) ?></td>
                                        <td class="p-4 text-xs text-slate-400"><?= date('d M Y', strtotime($app['tanggal_melamar'])) ?></td>
                                        <td class="p-4">
                                            <?php
                                            $status = $app['status_lamaran'];
                                            $class = "bg-slate-100 text-slate-600";
                                            if ($status == 'INTERVIEW') $class = "bg-blue-50 text-blue-700";
                                            if ($status == 'DITERIMA') $class = "bg-emerald-50 text-emerald-700";
                                            if ($status == 'ADMINISTRASI') $class = "bg-amber-50 text-amber-700";
                                            if ($status == 'DITOLAK') $class = "bg-rose-50 text-rose-700";
                                            ?>
                                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-md inline-block uppercase tracking-wide <?= $class ?>">
                                                <?= $status ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="p-8 text-center text-xs text-slate-400">Belum ada lamaran terkirim.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900 mb-4">Kelengkapan Profil (<?= $candidateDashboard['profile_pct']['total_pct'] ?>%)</h3>
                    <div class="flex flex-col gap-4">
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-slate-600 flex items-center gap-1.5">Curriculum Vitae (CV)</span>
                                <span class="text-[11px] font-bold <?= $candidateDashboard['profile_pct']['has_cv'] ? 'text-emerald-600' : 'text-amber-600' ?>">
                                    <?= $candidateDashboard['profile_pct']['has_cv'] ? 'Lengkap' : 'Belum Ada' ?>
                                </span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: <?= $candidateDashboard['profile_pct']['has_cv'] ? '100%' : '0%' ?>"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-slate-600 flex items-center gap-1.5">Foto Profil</span>
                                <span class="text-[11px] font-bold <?= $candidateDashboard['profile_pct']['has_foto'] ? 'text-emerald-600' : 'text-amber-600' ?>">
                                    <?= $candidateDashboard['profile_pct']['has_foto'] ? 'Lengkap' : 'Belum Ada' ?>
                                </span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: <?= $candidateDashboard['profile_pct']['has_foto'] ? '100%' : '0%' ?>"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-slate-600 flex items-center gap-1.5">Riwayat Pendidikan</span>
                                <span class="text-[11px] font-bold <?= $candidateDashboard['profile_pct']['has_edu'] ? 'text-emerald-600' : 'text-amber-600' ?>">
                                    <?= $candidateDashboard['profile_pct']['has_edu'] ? 'Lengkap' : 'Belum Ada' ?>
                                </span>
                            </div>
                            <div class="h-1.5 w-full bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: <?= $candidateDashboard['profile_pct']['has_edu'] ? '100%' : '0%' ?>"></div>
                            </div>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidateData['id'] ?>" class="block w-full mt-5">
                        <button class="w-full bg-blue-700 hover:bg-blue-800 text-white text-xs font-semibold py-2 rounded-xl transition-colors shadow-sm">Perbarui Profil</button>
                    </a>
                </div>

                <div class="bg-white border border-slate-200 rounded-2xl p-5 flex-1 shadow-sm">
                    <h3 class="text-sm font-semibold text-slate-900 mb-3">Interview Mendatang</h3>
                    <div class="flex flex-col gap-3">
                        <?php if (!empty($candidateDashboard['interviews'])): ?>
                            <?php foreach ($candidateDashboard['interviews'] as $iv): ?>
                                <div class="p-3.5 border border-slate-100 rounded-xl bg-slate-50/50 hover:border-blue-200 hover:bg-white transition-all shadow-2xs">
                                    <div class="flex items-start gap-3">
                                        <div class="flex flex-col items-center justify-center bg-white border border-slate-200 rounded-xl p-1.5 min-w-[48px] shadow-2xs">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider"><?= date('M', strtotime($iv['tanggal_interview'])) ?></span>
                                            <span class="text-sm font-bold text-slate-800 leading-none mt-0.5"><?= date('d', strtotime($iv['tanggal_interview'])) ?></span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-xs font-semibold text-slate-800 truncate"><?= htmlspecialchars($iv['judul_job']) ?></h4>
                                            <p class="text-[11px] text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2" />
                                                </svg>
                                                <?= date('H:i', strtotime($iv['tanggal_interview'])) ?> WIB
                                            </p>
                                            <?php if (!empty($iv['catatan'])): ?>
                                                <div class="mt-2 p-2 bg-blue-50/60 border border-blue-100/50 rounded-lg">
                                                    <p class="text-[10px] text-blue-700 leading-relaxed">
                                                        <span class="font-semibold">Catatan:</span> <?= htmlspecialchars($iv['catatan']) ?>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-6 border border-dashed border-slate-200 rounded-xl bg-slate-50/50 text-center">
                                <p class="text-[11px] text-slate-400 font-medium">Belum ada jadwal interview terdekat.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Rekomendasi Posisi</h3>
                    <p class="text-xs text-slate-400">Disesuaikan dengan kompetensi dan fleksibilitas Anda</p>
                </div>
                <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="text-xs font-semibold text-blue-600 hover:text-blue-800 transition-colors">Lihat Semua →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php if (!empty($candidateDashboard['recommendations'])): ?>
                    <?php foreach ($candidateDashboard['recommendations'] as $job): ?>
                        <div class="group bg-white border border-slate-200 rounded-xl p-4 transition-all duration-200 hover:border-blue-200 hover:bg-slate-50/40 flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-2 mb-3">
                                    <div class="min-w-0">
                                        <h4 class="text-sm font-bold text-slate-800 truncate leading-tight group-hover:text-blue-700 transition-colors">
                                            <?= htmlspecialchars($job['judul_job']) ?>
                                        </h4>
                                        <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" />
                                            </svg>
                                            <?= htmlspecialchars($job['lokasi']) ?>
                                        </p>
                                    </div>
                                    <span class="text-[10px] font-bold px-2 py-0.5 rounded shrink-0 
    <?= $job['match_percent'] >= 75 ? 'bg-emerald-50 text-emerald-700' : ($job['match_percent'] >= 40 ? 'bg-amber-50 text-amber-700' : 'bg-slate-50 text-slate-600') ?>">
                                        <?= round($job['match_percent']) ?>% Match
                                    </span>
                                </div>

                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    <span class="text-[10px] font-medium px-2 py-0.5 bg-slate-100 text-slate-600 rounded">
                                        <?= $job['tipe_pekerjaan'] ?>
                                    </span>

                                    <?php if ($job['is_remote_work']): ?>
                                        <span class="text-[10px] font-medium px-2 py-0.5 bg-blue-50 text-blue-700 rounded flex items-center gap-1">
                                            Remote
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($job['is_disabilitas']): ?>
                                        <span class="text-[10px] font-medium px-2 py-0.5 bg-purple-50 text-purple-700 rounded flex items-center gap-1">
                                            Akses Disabilitas
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 mt-2">
                                <div class="min-w-0">
                                    <?php if (!empty($job['gaji_min']) && !empty($job['gaji_max'])): ?>
                                        <!-- Menampilkan Rentang Gaji -->
                                        <p class="text-xs font-bold text-slate-800 truncate">
                                            Rp <?= number_format($job['gaji_min'], 0, ',', '.') ?> - <?= number_format($job['gaji_max'], 0, ',', '.') ?>
                                        </p>
                                    <?php else: ?>
                                        <p class="text-[11px] text-slate-400 italic">Gaji Kompetitif</p>
                                    <?php endif; ?>
                                </div>

                                <a href="<?= BASE_URL ?>views/lowonganPekerjaan/detailById.php?id=<?= $job['id'] ?>" class="text-xs font-bold text-blue-700 hover:text-blue-900 flex items-center gap-1 whitespace-nowrap">
                                    Detail
                                    <svg class="w-3 h-3 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M9 5l7 7-7 7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full p-6 text-center border-2 border-dashed border-slate-200 rounded-xl">
                        <p class="text-xs text-slate-400">Belum ada rekomendasi yang sesuai dengan profil Anda.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'layouts/app.php';
?>