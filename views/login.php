<?php
session_start();
require_once __DIR__ . "../../init.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = "Username dan password tidak boleh kosong.";
    } else {
        $user = AuthController::login($username, $password);

        if ($user) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            $success = "Login berhasil! Mengarahkan ke dashboard...";
            header("Refresh: 1; url=" . BASE_URL . "views/dashboard.php");
        } else {
            $error = "Username atau password salah. Silakan coba lagi.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — RecruitEase</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>
        .input-error {
            border-color: #EF4444 !important;
            background: #FFF8F8;
        }

        .input-error:focus {
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12) !important;
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">

    <div class="flex w-full max-w-6xl min-h-[600px] rounded-2xl overflow-hidden shadow-lg border border-slate-200">

        <div class="hidden lg:flex flex-col items-center justify-center flex-1 bg-[#1E3A8A] p-10 gap-8">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-[#3B82F6] flex items-center justify-center">
                    <i class="ti ti-briefcase text-white text-xl"></i>
                </div>
                <div>
                    <p class="text-white text-lg font-medium leading-none">RecruitEase</p>
                    <p class="text-blue-300 text-xs mt-1">E-Recruitment Platform</p>
                </div>
            </div>

            <div class="w-64 h-40 rounded-xl bg-[#2563EB]/50 flex items-center justify-center">
                <i class="ti ti-users text-blue-200 text-6xl"></i>
            </div>

            <div class="text-center">
                <h2 class="text-white text-lg font-medium mb-2">Find Top Talent Faster</h2>
                <p class="text-blue-300 text-sm leading-relaxed">
                    Kelola proses rekrutmen dari satu<br>platform yang terintegrasi
                </p>
            </div>

            <div class="flex gap-8">
                <div class="text-center">
                    <p class="text-white text-xl font-medium">2.4K</p>
                    <p class="text-blue-300 text-xs mt-1">Pelamar</p>
                </div>
                <div class="w-px bg-white/20"></div>
                <div class="text-center">
                    <p class="text-white text-xl font-medium">180</p>
                    <p class="text-blue-300 text-xs mt-1">Posisi</p>
                </div>
                <div class="w-px bg-white/20"></div>
                <div class="text-center">
                    <p class="text-white text-xl font-medium">94%</p>
                    <p class="text-blue-300 text-xs mt-1">Success Rate</p>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-[420px] bg-slate-50 flex items-center justify-center p-10">
            <div class="w-full">

                <div class="mb-8">
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700
                             text-xs px-3 py-1 rounded-full mb-3">
                        <i class="ti ti-shield-check text-sm"></i> Secure Login
                    </span>
                    <h1 class="text-[22px] font-medium text-slate-800 mb-1">Selamat Datang Kembali</h1>
                    <p class="text-sm text-slate-500">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                <?php if ($error): ?>
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200
                        text-red-800 text-sm rounded-lg px-4 py-3 mb-5">
                        <i class="ti ti-alert-circle text-base flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200
                        text-green-800 text-sm rounded-lg px-4 py-3 mb-5">
                        <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="space-y-4" novalidate id="loginForm">

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Username</label>
                        <div class="relative">
                            <i class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2
                                  text-slate-400 text-base pointer-events-none"></i>
                            <input type="text" name="username" id="username"
                                value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                placeholder="Masukkan username Anda"
                                class="w-full h-[42px] pl-9 pr-3 border border-slate-300 rounded-lg
                                      text-sm bg-white text-slate-800 placeholder-slate-400
                                      outline-none transition
                                      focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10
                                      <?= ($error && empty(trim($_POST['username'] ?? ''))) ? 'input-error' : '' ?>">
                        </div>
                        <?php if ($error && empty(trim($_POST['username'] ?? ''))): ?>
                            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                <i class="ti ti-alert-circle text-xs"></i> Username tidak boleh kosong
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Password</label>
                        <div class="relative">
                            <i class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2
                                  text-slate-400 text-base pointer-events-none"></i>
                            <input type="password" name="password" id="password"
                                placeholder="Masukkan password Anda"
                                class="w-full h-[42px] pl-9 pr-10 border border-slate-300 rounded-lg
                                      text-sm bg-white text-slate-800 placeholder-slate-400
                                      outline-none transition
                                      focus:border-blue-500 focus:ring-2 focus:ring-blue-500/10
                                      <?= ($error && empty(trim($_POST['password'] ?? ''))) ? 'input-error' : '' ?>">
                            <button type="button" id="togglePw"
                                class="absolute right-3 top-1/2 -translate-y-1/2
                                       text-slate-400 hover:text-slate-600 transition"
                                aria-label="Tampilkan password">
                                <i class="ti ti-eye text-base" id="eyeIcon"></i>
                            </button>
                        </div>
                        <?php if ($error && empty(trim($_POST['password'] ?? ''))): ?>
                            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                <i class="ti ti-alert-circle text-xs"></i> Password tidak boleh kosong
                            </p>
                        <?php endif; ?>
                    </div>

                    <button type="submit"
                        class="w-full h-11 bg-[#1E3A8A] hover:bg-[#1D4ED8] active:scale-[0.99]
                               text-white text-sm font-medium rounded-lg transition
                               flex items-center justify-center gap-2 mt-2">
                        Masuk
                        <i class="ti ti-arrow-right text-base"></i>
                    </button>

                </form>

                <div class="flex items-center gap-3 my-5">
                    <hr class="flex-1 border-slate-200">
                    <span class="text-xs text-slate-400">atau</span>
                    <hr class="flex-1 border-slate-200">
                </div>

                <p class="text-center text-sm text-slate-500">
                    Belum punya akun?
                    <a href="register.php" class="text-blue-700 font-medium hover:underline">
                        Daftar sekarang
                    </a>
                </p>

            </div>
        </div>
    </div>

    <script>
        document.getElementById('togglePw').addEventListener('click', function() {
            const pw = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pw.type === 'password') {
                pw.type = 'text';
                icon.className = 'ti ti-eye-off text-base';
            } else {
                pw.type = 'password';
                icon.className = 'ti ti-eye text-base';
            }
        });
    </script>
</body>

</html>