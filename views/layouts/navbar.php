<?php
require_once __DIR__ . "/../../controllers/AuthController.php";
require_once __DIR__ . "../../../init.php";
?>

<style>
    @keyframes pulse-badge {

        0%,
        100% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.2);
        }
    }

    @keyframes blink {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.3;
        }
    }
</style>

<nav class="fixed top-0 left-[var(--sidebar-width)] right-0 z-30 flex h-16 items-center justify-between border-b border-blue-100/40 bg-white/90 px-6 backdrop-blur-xl transition-all duration-300">
    <!-- Left: Brand -->
    <div class="flex items-center gap-3">
        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-gradient-to-br from-blue-900 to-blue-500 shadow-md shadow-blue-200">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
        </div>

        <div class="hidden flex-col leading-tight md:flex">
            <span class="text-base font-bold tracking-tight text-slate-800">
                RecruitEase
                <span class="ml-0.5 inline-block h-2 w-2 animate-[blink_2s_ease-in-out_infinite] rounded-full bg-blue-500"></span>
            </span>
            <span class="text-[10px] font-medium uppercase tracking-widest text-slate-500">
                E-Recruitment
            </span>
        </div>
    </div>

    <!-- Right -->
    <div class="flex items-center gap-2">

        <!-- Notification -->
        <button class="relative flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 transition hover:bg-blue-100 group">
            <svg class="h-4 w-4 text-slate-500 transition group-hover:text-blue-900" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>

            <span class="absolute -top-0.5 -right-0.5 flex h-4 w-4 animate-[pulse-badge_2s_ease-in-out_infinite] items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white">
                3
            </span>
        </button>

        <div class="mx-1 h-6 w-px bg-slate-200"></div>

        <!-- User -->
        <div class="relative">

            <button
                id="dropdownAvatarNameButton"
                data-dropdown-toggle="dropdownAvatarName"
                class="flex items-center gap-2.5 rounded-xl px-3 py-1.5 transition hover:bg-slate-100">

                <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-blue-900 to-blue-500 text-sm font-bold text-white">
                    <?= strtoupper(substr($_SESSION['username'] ?? 'U', 0, 2)) ?>
                </div>

                <div class="hidden flex-col text-left leading-tight md:flex">
                    <span class="text-sm font-semibold text-slate-800">
                        <?= $_SESSION['username'] ?? 'User' ?>
                    </span>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <span class="text-[10px] text-slate-500">Administrator</span>
                    <?php elseif ($_SESSION['role'] == 'hrd'): ?>
                        <span class="text-[10px] text-slate-500">HRD</span>
                    <?php elseif ($_SESSION['role'] == 'candidate'): ?>
                        <span class="text-[10px] text-slate-500">Kandidat</span>
                    <?php endif; ?>
                </div>

                <svg class="ml-1 h-3.5 w-3.5 text-slate-400 transition group-hover:text-blue-900" fill="none" viewBox="0 0 10 6">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4" />
                </svg>
            </button>

            <!-- Dropdown -->
            <div
                id="dropdownAvatarName"
                class="absolute right-0 z-50 mt-2 hidden w-52 overflow-hidden rounded-2xl border border-blue-100 bg-white shadow-xl">

                <div class="border-b border-blue-100 bg-gradient-to-br from-blue-50 to-blue-100 px-4 py-3">
                    <p class="mb-0.5 text-xs text-slate-500">Masuk sebagai</p>
                    <p class="truncate text-sm font-semibold text-blue-900">
                        <?= $_SESSION['username'] ?? 'User' ?>
                    </p>
                </div>

                <?php if ($_SESSION['role'] == 'hr'): ?>
                    <ul class="py-1.5 text-sm">
                        <li>
                            <a href="<?= BASE_URL ?>views/staff/edit.php?id=<?= $_SESSION['user_id']; ?>"
                                class="flex items-center gap-3 px-4 py-2.5 text-slate-600 transition hover:bg-gradient-to-r hover:from-blue-50 hover:to-blue-100 hover:text-blue-900">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                Profil Saya
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>

                <div class="border-t border-slate-100 py-1.5">
                    <a href="<?= BASE_URL; ?>/views/logout.php"
                        class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-500 transition hover:bg-red-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        Keluar
                    </a>
                </div>

            </div>
        </div>

    </div>
</nav>