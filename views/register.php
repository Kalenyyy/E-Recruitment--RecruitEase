<?php
require_once __DIR__ . "../../init.php";

$error = "";
$success = "";
$fieldErrors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $username         = trim($_POST['username'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // VALIDASI
    if (empty($full_name)) {
        $fieldErrors['full_name'] = "Nama lengkap wajib diisi.";
    }

    if (empty($email)) {
        $fieldErrors['email'] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $fieldErrors['email'] = "Format email tidak valid.";
    }

    if (empty($username)) {
        $fieldErrors['username'] = "Username wajib diisi.";
    }

    if (empty($phone)) {
        $fieldErrors['phone'] = "Nomor HP wajib diisi.";
    }

    if (empty($password)) {
        $fieldErrors['password'] = "Password wajib diisi.";
    } elseif (strlen($password) < 8) {
        $fieldErrors['password'] = "Password minimal 8 karakter.";
    }

    if (empty($confirm_password)) {
        $fieldErrors['confirm_password'] = "Konfirmasi password wajib diisi.";
    } elseif ($password !== $confirm_password) {
        $fieldErrors['confirm_password'] = "Konfirmasi password tidak cocok.";
    }

    if (empty($fieldErrors)) {
        $result = AuthController::register(
            $full_name,
            $email,
            $username,
            $phone,
            $password
        );

        if ($result === true) {

            $success = "Akun berhasil dibuat! Mengarahkan ke halaman login...";
            header("Refresh: 2; url=" . BASE_URL . "views/login.php");
        } elseif ($result === 'username_taken') {
            $fieldErrors['username'] = "Username sudah digunakan.";
        } elseif ($result === 'email_taken') {
            $fieldErrors['email'] = "Email sudah terdaftar.";
        } else {
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }

    if (!empty($fieldErrors)) {
        $error = "Mohon periksa kembali data yang diisi.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — RecruitEase</title>
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

<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4 py-10">

    <div class="flex w-full max-w-6xl min-h-[650px] rounded-2xl overflow-hidden shadow-lg border border-slate-200">

        <!-- Left Pane -->
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
                <i class="ti ti-user-plus text-blue-200 text-6xl"></i>
            </div>

            <div class="text-center">
                <h2 class="text-white text-lg font-medium mb-2">Mulai Karir Impianmu</h2>
                <p class="text-blue-300 text-sm leading-relaxed">
                    Bergabunglah dengan ribuan pelamar yang<br>telah menemukan pekerjaan terbaik mereka
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

        <!-- Right Pane -->
        <div class="w-full lg:w-[480px] bg-slate-50 flex items-center justify-center p-10">
            <div class="w-full">

                <div class="mb-7">
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700
                             text-xs px-3 py-1 rounded-full mb-3">
                        <i class="ti ti-user-check text-sm"></i> Daftar sebagai Pelamar
                    </span>
                    <h1 class="text-[22px] font-medium text-slate-800 mb-1">Buat Akun Baru</h1>
                    <p class="text-sm text-slate-500">Isi data diri Anda untuk mendaftar</p>
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

                <form action="register.php" method="POST" class="space-y-4" novalidate>

                    <!-- Nama Lengkap -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Nama Lengkap
                        </label>

                        <div class="relative">
                            <i class="ti ti-id-badge absolute left-3 top-1/2 -translate-y-1/2
                      text-slate-400 text-base pointer-events-none"></i>

                            <input type="text" name="full_name"
                                value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                                placeholder="Masukkan nama lengkap Anda"
                                class="w-full h-[42px] pl-9 pr-3 border rounded-lg
                       text-sm bg-white text-slate-800 placeholder-slate-400
                       outline-none transition focus:ring-2
                       <?= isset($fieldErrors['full_name'])
                            ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                            : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10'
                        ?>">
                        </div>

                        <?php if (isset($fieldErrors['full_name'])): ?>
                            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                <i class="ti ti-alert-circle"></i>
                                <?= $fieldErrors['full_name'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Email
                        </label>

                        <div class="relative">
                            <i class="ti ti-mail absolute left-3 top-1/2 -translate-y-1/2
                      text-slate-400 text-base pointer-events-none"></i>

                            <input type="email" name="email"
                                value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                                placeholder="contoh@email.com"
                                class="w-full h-[42px] pl-9 pr-3 border rounded-lg
                       text-sm bg-white text-slate-800 placeholder-slate-400
                       outline-none transition focus:ring-2
                       <?= isset($fieldErrors['email'])
                            ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                            : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10'
                        ?>">
                        </div>

                        <?php if (isset($fieldErrors['email'])): ?>
                            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                <i class="ti ti-alert-circle"></i>
                                <?= $fieldErrors['email'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Username & No HP -->
                    <div class="grid grid-cols-2 gap-3">

                        <!-- Username -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Username
                            </label>

                            <div class="relative">
                                <i class="ti ti-user absolute left-3 top-1/2 -translate-y-1/2
                          text-slate-400 text-base pointer-events-none"></i>

                                <input type="text" name="username"
                                    value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                                    placeholder="username_anda"
                                    class="w-full h-[42px] pl-9 pr-3 border rounded-lg
                           text-sm bg-white text-slate-800 placeholder-slate-400
                           outline-none transition focus:ring-2
                           <?= isset($fieldErrors['username'])
                                ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                                : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10'
                            ?>">
                            </div>

                            <?php if (isset($fieldErrors['username'])): ?>
                                <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                    <i class="ti ti-alert-circle"></i>
                                    <?= $fieldErrors['username'] ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Phone -->
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                No. HP
                            </label>

                            <div class="relative">
                                <i class="ti ti-phone absolute left-3 top-1/2 -translate-y-1/2
                          text-slate-400 text-base pointer-events-none"></i>

                                <input type="tel" name="phone"
                                    value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                                    placeholder="08xxxxxxxxxx"
                                    class="w-full h-[42px] pl-9 pr-3 border rounded-lg
                           text-sm bg-white text-slate-800 placeholder-slate-400
                           outline-none transition focus:ring-2
                           <?= isset($fieldErrors['phone'])
                                ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                                : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10'
                            ?>">
                            </div>

                            <?php if (isset($fieldErrors['phone'])): ?>
                                <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                    <i class="ti ti-alert-circle"></i>
                                    <?= $fieldErrors['phone'] ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Password
                        </label>

                        <div class="relative">
                            <i class="ti ti-lock absolute left-3 top-1/2 -translate-y-1/2
                      text-slate-400 text-base pointer-events-none"></i>

                            <input type="password" name="password" id="password"
                                placeholder="Minimal 8 karakter"
                                class="w-full h-[42px] pl-9 pr-10 border rounded-lg
                       text-sm bg-white text-slate-800 placeholder-slate-400
                       outline-none transition focus:ring-2
                       <?= isset($fieldErrors['password'])
                            ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                            : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10'
                        ?>">

                            <button type="button" id="togglePw"
                                class="absolute right-3 top-1/2 -translate-y-1/2
                       text-slate-400 hover:text-slate-600 transition">
                                <i class="ti ti-eye text-base" id="eyeIcon"></i>
                            </button>
                        </div>

                        <?php if (isset($fieldErrors['password'])): ?>
                            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                <i class="ti ti-alert-circle"></i>
                                <?= $fieldErrors['password'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Konfirmasi Password
                        </label>

                        <div class="relative">
                            <i class="ti ti-lock-check absolute left-3 top-1/2 -translate-y-1/2
                      text-slate-400 text-base pointer-events-none"></i>

                            <input type="password" name="confirm_password" id="confirm_password"
                                placeholder="Ulangi password Anda"
                                class="w-full h-[42px] pl-9 pr-10 border rounded-lg
                       text-sm bg-white text-slate-800 placeholder-slate-400
                       outline-none transition focus:ring-2
                       <?= isset($fieldErrors['confirm_password'])
                            ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10'
                            : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10'
                        ?>">

                            <button type="button" id="toggleConfirmPw"
                                class="absolute right-3 top-1/2 -translate-y-1/2
                       text-slate-400 hover:text-slate-600 transition">
                                <i class="ti ti-eye text-base" id="eyeConfirmIcon"></i>
                            </button>
                        </div>

                        <?php if (isset($fieldErrors['confirm_password'])): ?>
                            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                                <i class="ti ti-alert-circle"></i>
                                <?= $fieldErrors['confirm_password'] ?>
                            </p>
                        <?php endif; ?>
                    </div>

                    <button type="submit"
                        class="w-full h-11 bg-[#1E3A8A] hover:bg-[#1D4ED8]
               active:scale-[0.99] text-white text-sm font-medium
               rounded-lg transition flex items-center justify-center gap-2 mt-2">
                        Buat Akun
                        <i class="ti ti-arrow-right text-base"></i>
                    </button>

                </form>

                <div class="flex items-center gap-3 my-5">
                    <hr class="flex-1 border-slate-200">
                    <span class="text-xs text-slate-400">atau</span>
                    <hr class="flex-1 border-slate-200">
                </div>

                <p class="text-center text-sm text-slate-500">
                    Sudah punya akun?
                    <a href="login.php" class="text-blue-700 font-medium hover:underline">
                        Masuk sekarang
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

        document.getElementById('toggleConfirmPw').addEventListener('click', function() {
            const pw = document.getElementById('confirm_password');
            const icon = document.getElementById('eyeConfirmIcon');
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