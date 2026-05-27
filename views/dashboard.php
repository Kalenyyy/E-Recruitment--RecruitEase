<?php

require_once __DIR__ . '/../init.php';
$title = "Dashboard";
AuthController::requireLogin();

$role = $_SESSION['role'];
ob_start();
?>

<style>
    .page-bg {
        background: #F8FAFC;
        min-height: 100vh;
    }

    /* Stat Cards */
    .stat-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 20px 22px;
        transition: all 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(30, 58, 138, 0.10);
        border-color: #BFDBFE;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        opacity: 0.06;
        transform: translate(20px, -20px);
    }

    .stat-card.blue::after {
        background: #1E3A8A;
    }

    .stat-card.green::after {
        background: #059669;
    }

    .stat-card.amber::after {
        background: #D97706;
    }

    .stat-card.rose::after {
        background: #E11D48;
    }

    .stat-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stat-icon-wrap.blue {
        background: #EFF6FF;
        color: #1E3A8A;
    }

    .stat-icon-wrap.green {
        background: #ECFDF5;
        color: #059669;
    }

    .stat-icon-wrap.amber {
        background: #FFFBEB;
        color: #D97706;
    }

    .stat-icon-wrap.rose {
        background: #FFF1F2;
        color: #E11D48;
    }

    .stat-trend-up {
        background: #ECFDF5;
        color: #059669;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
    }

    .stat-trend-down {
        background: #FFF1F2;
        color: #E11D48;
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 999px;
    }

    /* Chart Cards */
    .chart-card {
        background: #ffffff;
        border: 1px solid #E2E8F0;
        border-radius: 16px;
        padding: 22px;
    }

    .chart-card-title {
        font-size: 14px;
        font-weight: 600;
        color: #1E293B;
    }

    .chart-card-subtitle {
        font-size: 12px;
        color: #94A3B8;
    }

    /* Filter Pills */
    .filter-pill {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
        border: 1px solid #E2E8F0;
        color: #64748B;
        background: transparent;
        cursor: pointer;
        transition: all 0.15s ease;
    }

    .filter-pill:hover,
    .filter-pill.active {
        background: #EFF6FF;
        color: #1E3A8A;
        border-color: #BFDBFE;
    }

    /* Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
    }

    .data-table th {
        font-size: 11px;
        font-weight: 700;
        color: #94A3B8;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        padding: 10px 16px;
        text-align: left;
        border-bottom: 1px solid #F1F5F9;
    }

    .data-table td {
        padding: 14px 16px;
        font-size: 13.5px;
        color: #334155;
        border-bottom: 1px solid #F8FAFC;
    }

    .data-table tr:hover td {
        background: #F8FAFC;
    }

    .data-table tr:last-child td {
        border-bottom: none;
    }

    /* Status Badges */
    .badge {
        font-size: 11px;
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 999px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .badge::before {
        content: '';
        width: 5px;
        height: 5px;
        border-radius: 50%;
    }

    .badge-review {
        background: #FFFBEB;
        color: #B45309;
    }

    .badge-review::before {
        background: #D97706;
    }

    .badge-interview {
        background: #EFF6FF;
        color: #1D4ED8;
    }

    .badge-interview::before {
        background: #3B82F6;
    }

    .badge-diterima {
        background: #ECFDF5;
        color: #065F46;
    }

    .badge-diterima::before {
        background: #10B981;
    }

    .badge-ditolak {
        background: #FFF1F2;
        color: #9F1239;
    }

    .badge-ditolak::before {
        background: #F43F5E;
    }

    /* Activity Feed */
    .activity-item {
        display: flex;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #F1F5F9;
    }

    .activity-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .activity-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-top: 5px;
        flex-shrink: 0;
    }

    /* Progress Bar */
    .progress-bar-bg {
        height: 6px;
        background: #F1F5F9;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-bar-fill {
        height: 100%;
        border-radius: 999px;
        transition: width 1s ease;
    }

    /* Legend dots */
    .legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 2px;
        flex-shrink: 0;
    }
</style>

<!-- Page Header -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-[#1E293B] tracking-tight">Dashboard</h1>
        <p class="text-sm text-[#64748B] mt-0.5">Selamat datang kembali, <span class="font-semibold text-[#1E3A8A]"><?= $_SESSION['username'] ?? 'Admin' ?></span> 👋</p>
    </div>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-2 bg-white border border-[#E2E8F0] rounded-xl px-3 py-2 text-sm text-[#475569]">
            <svg class="w-4 h-4 text-[#94A3B8]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <?= date('d M Y') ?>
        </div>
        <button class="flex items-center gap-2 bg-[#1E3A8A] hover:bg-[#1e40af] text-white text-sm font-semibold px-4 py-2 rounded-xl transition-colors duration-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Buat Lowongan
        </button>
    </div>
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="stat-card blue">
        <div class="flex items-start justify-between mb-3">
            <div class="stat-icon-wrap blue">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <span class="stat-trend-up">↑ 12%</span>
        </div>
        <p class="text-2xl font-bold text-[#1E293B]">24</p>
        <p class="text-xs text-[#64748B] mt-0.5 font-medium">Lowongan Aktif</p>
    </div>

    <div class="stat-card green">
        <div class="flex items-start justify-between mb-3">
            <div class="stat-icon-wrap green">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <span class="stat-trend-up">↑ 8%</span>
        </div>
        <p class="text-2xl font-bold text-[#1E293B]">348</p>
        <p class="text-xs text-[#64748B] mt-0.5 font-medium">Total Pelamar</p>
    </div>

    <div class="stat-card amber">
        <div class="flex items-start justify-between mb-3">
            <div class="stat-icon-wrap amber">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <span class="stat-trend-up">↑ 5%</span>
        </div>
        <p class="text-2xl font-bold text-[#1E293B]">18</p>
        <p class="text-xs text-[#64748B] mt-0.5 font-medium">Interview Minggu Ini</p>
    </div>

    <div class="stat-card rose">
        <div class="flex items-start justify-between mb-3">
            <div class="stat-icon-wrap rose">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <span class="stat-trend-down">↓ 3%</span>
        </div>
        <p class="text-2xl font-bold text-[#1E293B]">42</p>
        <p class="text-xs text-[#64748B] mt-0.5 font-medium">Diterima Bulan Ini</p>
    </div>

</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">

    <!-- Bar Chart - 2/3 width -->
    <div class="chart-card lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="chart-card-title">Grafik Pelamar Masuk</p>
                <p class="chart-card-subtitle">Tren pelamar 7 hari terakhir</p>
            </div>
            <div class="flex gap-1.5">
                <button class="filter-pill active" onclick="setFilter(this,'7')">7 Hari</button>
                <button class="filter-pill" onclick="setFilter(this,'30')">30 Hari</button>
            </div>
        </div>
        <!-- Legend -->
        <div class="flex gap-4 mb-3">
            <span class="flex items-center gap-1.5 text-xs text-[#64748B]">
                <span class="legend-dot" style="background:#3B82F6"></span> Pelamar Masuk
            </span>
            <span class="flex items-center gap-1.5 text-xs text-[#64748B]">
                <span class="legend-dot" style="background:#10B981"></span> Diterima
            </span>
        </div>
        <div style="position:relative; width:100%; height:260px;">
            <canvas id="barChart" role="img" aria-label="Grafik pelamar masuk dan diterima per hari">Data pelamar harian.</canvas>
        </div>
    </div>

    <!-- Donut Chart - 1/3 width -->
    <div class="chart-card flex flex-col">
        <div class="mb-4">
            <p class="chart-card-title">Status Pelamar</p>
            <p class="chart-card-subtitle">Distribusi per status</p>
        </div>
        <div style="position:relative; width:100%; height:200px;">
            <canvas id="donutChart" role="img" aria-label="Distribusi status pelamar">Data status pelamar.</canvas>
        </div>
        <div class="flex flex-col gap-2.5 mt-4">
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-xs text-[#64748B]">
                    <span class="legend-dot rounded-full" style="background:#3B82F6"></span> Review
                </span>
                <span class="text-xs font-semibold text-[#1E293B]">42%</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-xs text-[#64748B]">
                    <span class="legend-dot rounded-full" style="background:#F59E0B"></span> Interview
                </span>
                <span class="text-xs font-semibold text-[#1E293B]">28%</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-xs text-[#64748B]">
                    <span class="legend-dot rounded-full" style="background:#10B981"></span> Diterima
                </span>
                <span class="text-xs font-semibold text-[#1E293B]">18%</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="flex items-center gap-2 text-xs text-[#64748B]">
                    <span class="legend-dot rounded-full" style="background:#F43F5E"></span> Ditolak
                </span>
                <span class="text-xs font-semibold text-[#1E293B]">12%</span>
            </div>
        </div>
    </div>

</div>

<!-- Bottom Row: Table + Activity -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    <!-- Recent Applicants Table -->
    <div class="chart-card lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="chart-card-title">Pelamar Terbaru</p>
                <p class="chart-card-subtitle">5 pelamar paling baru</p>
            </div>
            <a href="/pelamar" class="text-xs font-semibold text-[#3B82F6] hover:text-[#1E3A8A] transition-colors">Lihat semua →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Posisi</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold flex-shrink-0">AR</div>
                                <span class="font-medium text-[#1E293B]">Andi Rahmawan</span>
                            </div>
                        </td>
                        <td class="text-[#64748B]">Frontend Developer</td>
                        <td class="text-[#94A3B8]">12 Jan 2025</td>
                        <td><span class="badge badge-interview">Interview</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold flex-shrink-0">SP</div>
                                <span class="font-medium text-[#1E293B]">Siti Permata</span>
                            </div>
                        </td>
                        <td class="text-[#64748B]">HR Manager</td>
                        <td class="text-[#94A3B8]">11 Jan 2025</td>
                        <td><span class="badge badge-diterima">Diterima</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center text-xs font-bold flex-shrink-0">BP</div>
                                <span class="font-medium text-[#1E293B]">Budi Pratama</span>
                            </div>
                        </td>
                        <td class="text-[#64748B]">Backend Developer</td>
                        <td class="text-[#94A3B8]">10 Jan 2025</td>
                        <td><span class="badge badge-review">Review</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center text-xs font-bold flex-shrink-0">DK</div>
                                <span class="font-medium text-[#1E293B]">Dewi Kartika</span>
                            </div>
                        </td>
                        <td class="text-[#64748B]">UI/UX Designer</td>
                        <td class="text-[#94A3B8]">10 Jan 2025</td>
                        <td><span class="badge badge-ditolak">Ditolak</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-lg bg-violet-100 text-violet-700 flex items-center justify-center text-xs font-bold flex-shrink-0">MF</div>
                                <span class="font-medium text-[#1E293B]">Muhammad Farhan</span>
                            </div>
                        </td>
                        <td class="text-[#64748B]">Data Analyst</td>
                        <td class="text-[#94A3B8]">09 Jan 2025</td>
                        <td><span class="badge badge-review">Review</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Right Column -->
    <div class="flex flex-col gap-4">

        <!-- Top Positions -->
        <div class="chart-card">
            <p class="chart-card-title mb-1">Posisi Paling Diminati</p>
            <p class="chart-card-subtitle mb-4">Berdasarkan jumlah pelamar</p>
            <div class="flex flex-col gap-3">
                <?php
                $positions = [
                    ['name' => 'Frontend Developer', 'count' => 87, 'pct' => 87],
                    ['name' => 'Backend Developer', 'count' => 65, 'pct' => 65],
                    ['name' => 'UI/UX Designer', 'count' => 52, 'pct' => 52],
                    ['name' => 'Data Analyst', 'count' => 38, 'pct' => 38],
                ];
                $barColors = ['#3B82F6', '#6366F1', '#10B981', '#F59E0B'];
                foreach ($positions as $i => $pos): ?>
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-medium text-[#475569]"><?= $pos['name'] ?></span>
                            <span class="text-xs font-bold text-[#1E293B]"><?= $pos['count'] ?></span>
                        </div>
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" style="width:<?= $pos['pct'] ?>%; background:<?= $barColors[$i] ?>;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Activity Feed -->
        <div class="chart-card flex-1">
            <p class="chart-card-title mb-4">Aktivitas Terbaru</p>
            <div class="flex flex-col">
                <div class="activity-item">
                    <div class="activity-dot bg-[#3B82F6]"></div>
                    <div>
                        <p class="text-xs font-semibold text-[#1E293B]">Pelamar baru masuk</p>
                        <p class="text-[11px] text-[#94A3B8] mt-0.5">Andi Rahmawan – Frontend Dev</p>
                        <p class="text-[10px] text-[#CBD5E1] mt-0.5">2 menit lalu</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-dot bg-[#10B981]"></div>
                    <div>
                        <p class="text-xs font-semibold text-[#1E293B]">Pelamar diterima</p>
                        <p class="text-[11px] text-[#94A3B8] mt-0.5">Siti Permata – HR Manager</p>
                        <p class="text-[10px] text-[#CBD5E1] mt-0.5">1 jam lalu</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-dot bg-[#F59E0B]"></div>
                    <div>
                        <p class="text-xs font-semibold text-[#1E293B]">Jadwal interview dibuat</p>
                        <p class="text-[11px] text-[#94A3B8] mt-0.5">3 kandidat – 15 Jan 2025</p>
                        <p class="text-[10px] text-[#CBD5E1] mt-0.5">3 jam lalu</p>
                    </div>
                </div>
                <div class="activity-item">
                    <div class="activity-dot bg-[#F43F5E]"></div>
                    <div>
                        <p class="text-xs font-semibold text-[#1E293B]">Lowongan dibuka</p>
                        <p class="text-[11px] text-[#94A3B8] mt-0.5">Data Analyst – Dept. IT</p>
                        <p class="text-[10px] text-[#CBD5E1] mt-0.5">5 jam lalu</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Chart.js Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const data7 = {
        labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
        pelamar: [22, 35, 28, 47, 31, 18, 42],
        diterima: [5, 8, 6, 12, 7, 4, 10]
    };

    const data30 = {
        labels: Array.from({
            length: 30
        }, (_, i) => i + 1 + ''),
        pelamar: [12, 18, 25, 30, 22, 35, 28, 47, 31, 18, 42, 38, 29, 44, 52, 35, 27, 40, 33, 48, 20, 36, 41, 28, 55, 39, 24, 46, 31, 58],
        diterima: [3, 4, 6, 8, 5, 9, 7, 12, 8, 4, 10, 9, 7, 11, 13, 9, 6, 10, 8, 12, 5, 9, 10, 7, 14, 10, 6, 12, 8, 15]
    };

    let barChartInstance;

    function buildBarChart(days) {
        const d = days === '7' ? data7 : data30;
        if (barChartInstance) barChartInstance.destroy();
        barChartInstance = new Chart(document.getElementById('barChart'), {
            type: 'bar',
            data: {
                labels: d.labels,
                datasets: [{
                        label: 'Pelamar Masuk',
                        data: d.pelamar,
                        backgroundColor: 'rgba(59,130,246,0.85)',
                        borderRadius: 6,
                        barPercentage: 0.55
                    },
                    {
                        label: 'Diterima',
                        data: d.diterima,
                        backgroundColor: 'rgba(16,185,129,0.85)',
                        borderRadius: 6,
                        barPercentage: 0.55
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
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
                            color: '#94A3B8',
                            autoSkip: days === '30',
                            maxRotation: days === '30' ? 45 : 0
                        }
                    },
                    y: {
                        grid: {
                            color: 'rgba(148,163,184,0.12)',
                            drawBorder: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            color: '#94A3B8'
                        },
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function buildDonutChart() {
        new Chart(document.getElementById('donutChart'), {
            type: 'doughnut',
            data: {
                labels: ['Review', 'Interview', 'Diterima', 'Ditolak'],
                datasets: [{
                    data: [42, 28, 18, 12],
                    backgroundColor: ['#3B82F6', '#F59E0B', '#10B981', '#F43F5E'],
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ' ' + ctx.label + ': ' + ctx.parsed + '%'
                        }
                    }
                }
            }
        });
    }

    function setFilter(btn, days) {
        document.querySelectorAll('.filter-pill').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        buildBarChart(days);
    }

    buildBarChart('7');
    buildDonutChart();
</script>

<?php
$content = ob_get_clean();
include 'layouts/app.php';
?>