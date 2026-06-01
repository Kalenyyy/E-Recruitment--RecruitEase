<?php
require_once __DIR__ . "/../../init.php";

$role = $_SESSION['role'] ?? 'guest';
// get user data for footer
$userData = StaffController::show($conn, $_SESSION['user_id'] ?? null);
?>

<style>
    #sidebar {
        width: 240px;
        transition: width 0.3s ease;
    }

    #sidebar.collapsed {
        width: 68px;
    }

    /* HIDE BRAND TOTAL SAAT COLLAPSE */
    #sidebar.collapsed .brand-wrapper {
        display: none !important;
    }

    /* CENTER NAV ITEM */
    #sidebar.collapsed .nav-item {
        justify-content: center;
    }

    /* HIDE TEXT */
    #sidebar.collapsed .sidebar-text {
        display: none;
    }

    /* HIDE BADGE */
    #sidebar.collapsed .nav-badge {
        display: none;
    }
</style>

<aside id="sidebar"
    class="fixed left-0 top-0 z-40 flex h-screen flex-col overflow-hidden bg-gradient-to-b from-[#0F2557] via-[#1E3A8A] to-blue-600 shadow-2xl shadow-blue-950/50">

    <!-- HEADER -->
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-4">

        <!-- BRAND -->
        <div class="brand-wrapper flex items-center gap-3">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 border border-white/20">
                <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>

            <div class="sidebar-text">
                <p class="text-sm font-bold text-white">RecruitEase</p>
                <p class="text-[9px] uppercase tracking-widest text-blue-300">E-Recruitment</p>
            </div>
        </div>

        <!-- TOGGLE -->
        <button onclick="toggleSidebar()"
            class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10 text-white/70 hover:bg-white/20 hover:text-white transition">
            <svg id="toggleIcon"
                class="h-3.5 w-3.5 transition-transform duration-300"
                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
            </svg>
        </button>
    </div>

    <!-- NAVIGATION -->
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        <p class="px-3 text-[9px] font-bold uppercase tracking-widest text-white/30 sidebar-text">
            Menu Utama
        </p>

        <a href="<?= BASE_URL ?>views/dashboard.php" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="sidebar-text">Dashboard</span>
        </a>

        <a href="/lowongan" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
            </svg>
            <span class="sidebar-text">Lowongan</span>
            <span class="nav-badge sidebar-text ml-auto rounded-full bg-blue-500 px-2 text-[10px] font-bold text-white">
                12
            </span>
        </a>

        <a href="/pelamar" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <span class="sidebar-text">Pelamar</span>
            <span class="nav-badge sidebar-text ml-auto rounded-full bg-blue-500 px-2 text-[10px] font-bold text-white">
                48
            </span>
        </a>

        <a href="/seleksi" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="sidebar-text">Seleksi</span>
        </a>

        <a href="/jadwal" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span class="sidebar-text">Jadwal Interview</span>
        </a>

        <p class="px-3 pt-3 text-[9px] font-bold uppercase tracking-widest text-white/30 sidebar-text">
            Laporan
        </p>

        <a href="/laporan"
            class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white transition">

            <svg class="h-[18px] w-[18px] flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>

            <span class="sidebar-text">Statistik</span>
        </a>

        <a href="/rekap" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
            <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <span class="sidebar-text">Rekap Data</span>
        </a>

        <p class="px-3 pt-3 text-[9px] font-bold uppercase tracking-widest text-white/30 sidebar-text">
            Sistem
        </p>

        <?php if ($role == 'admin'): ?>
            <a href="<?= BASE_URL ?>views/user/index.php" class="nav-item flex items-center gap-3 rounded-xl px-3 py-2 text-white/70 hover:bg-white/10 hover:text-white">
                <svg class="h-[18px] w-[18px]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="sidebar-text">Pengguna</span>
            </a>
        <?php endif ?>

    </nav>

    <!-- FOOTER -->
    <div class="flex items-center gap-3 border-t border-white/10 px-3 py-3">
        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-400 text-xs font-bold text-white">
            <?php if($_SESSION['role'] == 'admin'): ?>
                <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?>
            <?php else: ?>
                <?= strtoupper(substr($userData['nama_staff'] ?? 'U', 0, 2)) ?>
            <?php endif; ?>
        </div>

        <div class="sidebar-text">
            <p class="text-xs font-semibold text-white">
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <?= $_SESSION['username'] ?? 'User' ?>
                <?php else: ?>
                    <?= $userData['nama_staff'] ?? 'User' ?>
                <?php endif; ?>
            </p>
            <p class="text-[10px] text-blue-300">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    Administrator
                <?php elseif ($_SESSION['role'] === 'hr'): ?>
                    HRD
                <?php else: ?>
                    Candidate
                <?php endif; ?></p>
        </div>
    </div>

</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const icon = document.getElementById('toggleIcon');

        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed');

        const isCollapsed = sidebar.classList.contains('collapsed');

        icon.style.transform = isCollapsed ?
            'rotate(180deg)' :
            'rotate(0deg)';
    }
</script>