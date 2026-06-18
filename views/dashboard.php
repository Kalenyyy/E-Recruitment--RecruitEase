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
        $isProfileComplete = ProfileHelper::isComplete($conn, $candidateData['id']);
        $missing = ProfileHelper::getMissingFields($conn, $candidateData['id']);
    }
}

ob_start();
?>

<!-- Page Container -->
<div class="bg-[#F8FAFC] min-h-screen">
    <!-- Page Header -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <?php if ($role === 'candidate' && !$isProfileComplete): ?>
                <div id="profileAlert" class="mb-4 rounded-xl border border-amber-300 bg-amber-50 p-4 flex justify-between items-start shadow-sm">
                    <div>
                        <h3 class="font-semibold text-amber-800">Profil Belum Lengkap</h3>
                        <p class="text-sm text-amber-700 mt-1">Lengkapi data berikut sebelum melamar pekerjaan:</p>
                        <div class="mt-2 text-sm text-amber-800 space-y-1">
                            <?php if (!empty($missing)): ?>
                                <?php foreach ($missing as $section => $items): ?>
                                    <div>
                                        <span class="font-bold"><?= ucfirst($section) ?>:</span>
                                        <ul class="list-disc ml-5">
                                            <?php foreach ($items as $item): ?>
                                                <li><?= htmlspecialchars($item) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-amber-700">Data tidak lengkap, tapi detail tidak tersedia.</p>
                            <?php endif; ?>
                        </div>
                        <a href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidateData['id'] ?>" class="inline-block mt-3 text-sm font-semibold text-blue-700 hover:underline">
                            Lengkapi Sekarang →
                        </a>
                    </div>
                    <button onclick="document.getElementById('profileAlert').remove()" class="text-amber-700 font-bold hover:text-amber-900">✕</button>
                </div>
            <?php endif; ?>

            <h1 class="text-xl font-bold text-[#1E293B] tracking-tight">Dashboard</h1>
            <p class="text-sm text-[#64748B] mt-0.5">Selamat datang kembali, <span class="font-semibold text-[#1E3A8A]">
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <?= $_SESSION['username'] ?? 'User' ?>
                    <?php elseif ($_SESSION['role'] == 'hr'): ?>
                        <?= $userData['nama_staff'] ?? 'User' ?>
                    <?php else: ?>
                        <?= $candidateData['nama_lengkap'] ?? 'User' ?>
                    <?php endif; ?>
                </span> 👋</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="flex items-center gap-2 bg-white border border-[#E2E8F0] rounded-xl px-3 py-2 text-sm text-[#475569] shadow-sm">
                <svg class="w-4 h-4 text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <?= date('d M Y') ?>
            </div>
            <?php if ($_SESSION['role'] !== 'candidate'): ?>
                <a href="<?= BASE_URL ?>views/formJob/create.php">
                    <button class="flex items-center gap-2 bg-[#1E3A8A] hover:bg-[#1e40af] text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all duration-200 shadow-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Buat Lowongan
                    </button>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ====================================== -->
    <!-- DASHBOARD UNTUK HR & ADMIN -->
    <!-- ====================================== -->
    <?php if ($_SESSION['role'] !== 'candidate'): ?>
        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Lowongan Aktif -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#1E3A8A] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#EFF6FF] text-[#1E3A8A]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]"><?= number_format($dashboardData['stats']['active_jobs'] ?? 0) ?></p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Lowongan Aktif</p>
            </div>

            <!-- Total Pelamar -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#059669] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#ECFDF5] text-[#059669]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]"><?= number_format($dashboardData['stats']['total_pelamar'] ?? 0) ?></p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Total Pelamar</p>
            </div>

            <!-- Interview -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#D97706] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#FFFBEB] text-[#D97706]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]"><?= number_format($dashboardData['stats']['total_interview'] ?? 0) ?></p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Status Interview</p>
            </div>

            <!-- Hired -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#E11D48] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#FFF1F2] text-[#E11D48]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]"><?= number_format($dashboardData['stats']['total_hired'] ?? 0) ?></p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Total Diterima</p>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <!-- Bar Chart -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-[22px] lg:col-span-2 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <p class="text-sm font-semibold text-[#1E293B]">Grafik Pelamar Masuk</p>
                        <p class="text-xs text-[#94A3B8]">Tren 7 hari terakhir</p>
                    </div>
                </div>
                <div class="flex gap-4 mb-3">
                    <span class="flex items-center gap-1.5 text-xs text-[#64748B]">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#3B82F6]"></span> Pelamar Masuk
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-[#64748B]">
                        <span class="w-2.5 h-2.5 rounded-sm bg-[#10B981]"></span> Diterima
                    </span>
                </div>
                <div class="relative w-full h-[260px]">
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <!-- Donut Chart -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-[22px] flex flex-col shadow-sm">
                <div class="mb-4">
                    <p class="text-sm font-semibold text-[#1E293B]">Status Pelamar</p>
                    <p class="text-xs text-[#94A3B8]">Distribusi per status</p>
                </div>
                <div class="relative w-full h-[200px]">
                    <canvas id="donutChart"></canvas>
                </div>
                <div id="donutLegend" class="flex flex-col gap-2.5 mt-4">
                    <!-- Dinamis via JS -->
                </div>
            </div>
        </div>

        <!-- Chart.js Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // Ambil data PHP
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
                                backgroundColor: 'rgba(59,130,246,0.85)',
                                borderRadius: 6,
                                barPercentage: 0.55
                            },
                            {
                                label: 'Diterima',
                                data: weeklyData.diterima || [],
                                backgroundColor: 'rgba(16,185,129,0.85)',
                                borderRadius: 6,
                                barPercentage: 0.55
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { grid: { display: false }, ticks: { font: { size: 11 }, color: '#94A3B8' } },
                            y: { grid: { color: 'rgba(148,163,184,0.12)' }, ticks: { font: { size: 11 }, color: '#94A3B8' }, beginAtZero: true }
                        }
                    }
                });
            }

            function buildDonutChart() {
                const ctx = document.getElementById('donutChart');
                if (!ctx) return;

                const labels = statusDist.map(item => item.status);
                const counts = statusDist.map(item => item.jumlah);
                const colors = ['#3B82F6', '#F59E0B', '#10B981', '#F43F5E', '#8B5CF6'];

                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: counts,
                            backgroundColor: colors,
                            borderWidth: 3,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { display: false } }
                    }
                });

                // Generate Legend Dinamis
                const legendContainer = document.getElementById('donutLegend');
                const total = counts.reduce((a, b) => a + b, 0);
                labels.forEach((label, i) => {
                    const percent = total > 0 ? Math.round((counts[i] / total) * 100) : 0;
                    legendContainer.innerHTML += `
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs text-[#64748B]">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color: ${colors[i % colors.length]}"></span> ${label}
                            </span>
                            <span class="text-xs font-semibold text-[#1E293B]">${percent}%</span>
                        </div>
                    `;
                });
            }

            buildBarChart();
            buildDonutChart();
        </script>
    <?php endif; ?>

    <!-- ====================================== -->
    <!-- DASHBOARD UNTUK CANDIDATE -->
    <!-- ====================================== -->
    <?php if ($_SESSION['role'] === 'candidate'): ?>
        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Lamaran -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#1E3A8A] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#EFF6FF] text-[#1E3A8A]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <span class="bg-[#ECFDF5] text-[#059669] text-[11px] font-bold px-2 py-0.5 rounded-full">↑ 2</span>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]">8</p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Total Lamaran</p>
            </div>

            <!-- Sedang Direviu -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#D97706] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#FFFBEB] text-[#D97706]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]">3</p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Sedang Direviu</p>
            </div>

            <!-- Interview Dijadwalkan -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#1E3A8A] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#EFF6FF] text-[#1E3A8A]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]">2</p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Interview Dijadwalkan</p>
            </div>

            <!-- Diterima -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-xl hover:border-[#BFDBFE] relative overflow-hidden after:content-[''] after:absolute after:top-0 after:right-0 after:w-20 after:h-20 after:rounded-full after:bg-[#059669] after:opacity-[0.06] after:translate-x-5 after:-translate-y-5">
                <div class="flex items-start justify-between mb-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center bg-[#ECFDF5] text-[#059669]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <p class="text-2xl font-bold text-[#1E293B]">1</p>
                <p class="text-xs text-[#64748B] mt-0.5 font-medium">Diterima</p>
            </div>
        </div>

        <!-- Main Content - 2 Columns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
            <!-- Applied Positions -->
            <div class="bg-white border border-[#E2E8F0] rounded-2xl p-[22px] lg:col-span-2 shadow-sm">
                <div class="mb-4">
                    <p class="text-sm font-semibold text-[#1E293B]">Posisi yang Dilamar</p>
                    <p class="text-xs text-[#94A3B8]">Riwayat lamaran Anda</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-widest p-4 text-left border-bottom border-[#F1F5F9]">Posisi</th>
                                <th class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-widest p-4 text-left border-bottom border-[#F1F5F9]">Perusahaan</th>
                                <th class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-widest p-4 text-left border-bottom border-[#F1F5F9]">Tanggal Lamar</th>
                                <th class="text-[11px] font-bold text-[#94A3B8] uppercase tracking-widest p-4 text-left border-bottom border-[#F1F5F9]">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#F8FAFC]">
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="p-4 text-[13.5px] font-medium text-[#1E293B]">Frontend Developer</td>
                                <td class="p-4 text-[13.5px] text-[#64748B]">PT Tech Innovasi</td>
                                <td class="p-4 text-[13.5px] text-[#94A3B8]">12 Jan 2025</td>
                                <td class="p-4">
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 bg-[#EFF6FF] text-[#1D4ED8] before:content-[''] before:w-1 before:h-1 before:rounded-full before:bg-[#3B82F6]">Interview</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="p-4 text-[13.5px] font-medium text-[#1E293B]">UI/UX Designer</td>
                                <td class="p-4 text-[13.5px] text-[#64748B]">PT Creative Studio</td>
                                <td class="p-4 text-[13.5px] text-[#94A3B8]">10 Jan 2025</td>
                                <td class="p-4">
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 bg-[#ECFDF5] text-[#065F46] before:content-[''] before:w-1 before:h-1 before:rounded-full before:bg-[#10B981]">Diterima</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-[#F8FAFC] transition-colors">
                                <td class="p-4 text-[13.5px] font-medium text-[#1E293B]">Backend Developer</td>
                                <td class="p-4 text-[13.5px] text-[#64748B]">PT Digital Solution</td>
                                <td class="p-4 text-[13.5px] text-[#94A3B8]">08 Jan 2025</td>
                                <td class="p-4">
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-full inline-flex items-center gap-1.5 bg-[#FFFBEB] text-[#B45309] before:content-[''] before:w-1 before:h-1 before:rounded-full before:bg-[#D97706]">Review</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Column -->
            <div class="flex flex-col gap-4">
                <!-- Profile Status -->
                <div class="bg-white border border-[#E2E8F0] rounded-2xl p-[22px] shadow-sm">
                    <p class="text-sm font-semibold text-[#1E293B] mb-3">Status Profil</p>
                    <div class="flex flex-col gap-3.5">
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-[#475569] flex items-center gap-1.5">📄 CV</span>
                                <span class="text-xs font-bold text-[#059669]">✓ Ada</span>
                            </div>
                            <div class="h-1.5 w-full bg-[#F1F5F9] rounded-full overflow-hidden">
                                <div class="h-full bg-[#059669] rounded-full transition-all duration-1000" style="width:100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-[#475569] flex items-center gap-1.5">🖼️ Foto</span>
                                <span class="text-xs font-bold text-[#059669]">✓ Ada</span>
                            </div>
                            <div class="h-1.5 w-full bg-[#F1F5F9] rounded-full overflow-hidden">
                                <div class="h-full bg-[#059669] rounded-full transition-all duration-1000" style="width:100%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-[#475569] flex items-center gap-1.5">✍️ Bio</span>
                                <span class="text-xs font-bold text-[#D97706]">75%</span>
                            </div>
                            <div class="h-1.5 w-full bg-[#F1F5F9] rounded-full overflow-hidden">
                                <div class="h-full bg-[#D97706] rounded-full transition-all duration-1000" style="width:75%"></div>
                            </div>
                        </div>
                    </div>
                    <button class="w-full mt-4 bg-[#1E3A8A] hover:bg-[#1e40af] text-white text-xs font-semibold py-2 rounded-lg transition-colors">Edit Profil</button>
                </div>

                <!-- Upcoming Interviews -->
                <div class="bg-white border border-[#E2E8F0] rounded-2xl p-[22px] flex-1 shadow-sm">
                    <p class="text-sm font-semibold text-[#1E293B] mb-3">Interview Mendatang</p>
                    <div class="flex flex-col gap-2.5">
                        <div class="p-3 border border-[#E2E8F0] rounded-lg bg-[#F8FAFC]">
                            <p class="text-xs font-semibold text-[#1E293B]">Frontend Developer</p>
                            <p class="text-[11px] text-[#64748B] mt-0.5">PT Tech Innovasi</p>
                            <div class="flex items-center gap-2 text-[10px] text-[#3B82F6] font-semibold mt-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" />
                                </svg>
                                15 Jan 2025 • 10:00
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommended Positions -->
        <div class="bg-white border border-[#E2E8F0] rounded-2xl p-[22px] shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-sm font-semibold text-[#1E293B]">Posisi Rekomendasi Untuk Anda</p>
                    <p class="text-xs text-[#94A3B8]">Berdasarkan profil Anda</p>
                </div>
                <a href="/lowongan" class="text-xs font-semibold text-[#3B82F6] hover:text-[#1E3A8A] transition-colors">Lihat semua →</a>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Card Rekomendasi -->
                <div class="border border-[#E2E8F0] rounded-xl p-4 hover:border-[#BFDBFE] hover:shadow-md transition-all group">
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <p class="text-sm font-semibold text-[#1E293B] group-hover:text-[#1E3A8A]">Senior Frontend Developer</p>
                            <p class="text-xs text-[#64748B] mt-0.5">PT Tech Enterprise</p>
                        </div>
                        <span class="text-[10px] font-bold text-[#10B981] bg-[#ECFDF5] px-2 py-1 rounded-lg">95% Match</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-[#64748B] mb-3">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" stroke-width="2" />
                        </svg>
                        Jakarta, Indonesia
                    </div>
                    <button class="w-full bg-[#1E3A8A] hover:bg-[#1e40af] text-white text-xs font-semibold py-2 rounded-lg transition-colors">Lihat Detail</button>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include 'layouts/app.php';
?>