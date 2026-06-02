<?php
require_once __DIR__ . "/../init.php";

// ==========================================
// INITIALIZE VARIABLES
// ==========================================
$error = "";
$success = "";
$fieldErrors = [];
$formData = [
    'full_name' => '',
    'email' => '',
    'username' => '',
    'phone' => '',
];

// ==========================================
// PROCESS FORM SUBMISSION
// ==========================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Tangkap dan trim semua input dari form
    $full_name        = trim($_POST['full_name'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $username         = trim($_POST['username'] ?? '');
    $phone            = trim($_POST['phone'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Simpan ke formData untuk repopulate form jika ada error
    $formData = compact('full_name', 'email', 'username', 'phone');

    // ------------------------------------------
    // VALIDASI INPUT
    // ------------------------------------------
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

    // ------------------------------------------
    // PROSES REGISTRASI JIKA VALIDASI LOLOS
    // ------------------------------------------
    if (empty($fieldErrors)) {
        $result = AuthController::register(
            $full_name,
            $email,
            $username,
            $phone,
            $password
        );

        if ($result === true) {
            $success = "Akun berhasil dibuat! Mengarahkan ke halaman login dalam 3 detik...";
        } elseif ($result === 'username_taken') {
            // Username sudah terdaftar
            $fieldErrors['username'] = "Username sudah digunakan.";
        } elseif ($result === 'email_taken') {
            // Email sudah terdaftar
            $fieldErrors['email'] = "Email sudah terdaftar.";
        } else {
            // Error lainnya
            $error = "Terjadi kesalahan. Silakan coba lagi.";
        }
    }

    // Jika ada field error, tampilkan pesan error umum
    if (!empty($fieldErrors)) {
        $error = "Mohon periksa kembali data yang diisi.";
    }
}

/**
 * Helper function untuk print input field dengan konsisten
 * Mengurangi duplikasi kode dan membuat form lebih mudah dimaintain
 * 
 * @param string $name       Field name dan id
 * @param string $label      Label text yang ditampilkan
 * @param string $type       Input type (text, email, password, tel, dll)
 * @param string $icon       Icon class dari Tabler Icons
 * @param string $placeholder Placeholder text
 * @param array $fieldErrors  Array error dari validasi
 * @param array $formData    Array data form untuk repopulate
 * @param bool $isPassword   Apakah field adalah password (untuk toggle visibility)
 */
function printInputField($name, $label, $type, $icon, $placeholder, $fieldErrors, $formData, $isPassword = false) {
    $hasError = isset($fieldErrors[$name]);
    $errorClass = $hasError 
        ? 'border-red-500 bg-red-50 focus:border-red-500 focus:ring-red-500/10' 
        : 'border-slate-300 focus:border-blue-500 focus:ring-blue-500/10';
    
    $value = htmlspecialchars($formData[$name] ?? '');
    $id = $isPassword ? "id=\"{$name}\"" : "";
    
    $toggleButton = '';
    if ($isPassword) {
        $eyeId = $name === 'password' ? 'eyeIcon' : 'eyeConfirmIcon';
        $toggleId = $name === 'password' ? 'togglePw' : 'toggleConfirmPw';
        $toggleButton = "
            <button type=\"button\" id=\"{$toggleId}\"
                class=\"absolute right-3 top-1/2 -translate-y-1/2
               text-slate-400 hover:text-slate-600 transition\">
                <i class=\"ti ti-eye text-base\" id=\"{$eyeId}\"></i>
            </button>";
    }
    
    ?>
    <div>
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
            <?= $label ?>
        </label>

        <div class="relative">
            <i class="ti <?= $icon ?> absolute left-3 top-1/2 -translate-y-1/2
          text-slate-400 text-base pointer-events-none"></i>

            <input type="<?= $type ?>" name="<?= $name ?>" <?= $id ?>
                value="<?= $isPassword ? '' : $value ?>"
                placeholder="<?= $placeholder ?>"
                class="w-full h-[42px] pl-9 <?= $isPassword ? 'pr-10' : 'pr-3' ?> border rounded-lg
               text-sm bg-white text-slate-800 placeholder-slate-400
               outline-none transition focus:ring-2 <?= $errorClass ?>">
            
            <?= $toggleButton ?>
        </div>

        <?php if ($hasError): ?>
            <p class="flex items-center gap-1 text-xs text-red-500 mt-1">
                <i class="ti ti-alert-circle"></i>
                <?= $fieldErrors[$name] ?>
            </p>
        <?php endif; ?>
    </div>
    <?php
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

        <!-- Right Pane - Form Section -->
        <div class="w-full lg:w-[480px] bg-slate-50 flex items-center justify-center p-10">
            <div class="w-full">

                <!-- Header Section -->
                <div class="mb-7">
                    <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700
                             text-xs px-3 py-1 rounded-full mb-3">
                        <i class="ti ti-user-check text-sm"></i> Daftar sebagai Pelamar
                    </span>
                    <h1 class="text-[22px] font-medium text-slate-800 mb-1">Buat Akun Baru</h1>
                    <p class="text-sm text-slate-500">Isi data diri Anda untuk mendaftar</p>
                </div>

                <!-- Error Message Alert -->
                <?php if ($error): ?>
                    <div class="flex items-center gap-2 bg-red-50 border border-red-200
                        text-red-800 text-sm rounded-lg px-4 py-3 mb-5">
                        <i class="ti ti-alert-circle text-base flex-shrink-0"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Success Message Alert -->
                <?php if ($success): ?>
                    <div class="flex items-center gap-2 bg-green-50 border border-green-200
                        text-green-800 text-sm rounded-lg px-4 py-3 mb-5">
                        <i class="ti ti-circle-check text-base flex-shrink-0"></i>
                        <span><?= htmlspecialchars($success) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Registration Form -->
                <form action="register.php" method="POST" class="space-y-4" novalidate>

                    <!-- Nama Lengkap -->
                    <?php printInputField('full_name', 'Nama Lengkap', 'text', 'ti-id-badge', 'Masukkan nama lengkap Anda', $fieldErrors, $formData); ?>

                    <!-- Email -->
                    <?php printInputField('email', 'Email', 'email', 'ti-mail', 'contoh@email.com', $fieldErrors, $formData); ?>

                    <!-- Username & No HP (Grid 2 Kolom) -->
                    <div class="grid grid-cols-2 gap-3">
                        <div><?php printInputField('username', 'Username', 'text', 'ti-user', 'username_anda', $fieldErrors, $formData); ?></div>
                        <div><?php printInputField('phone', 'No. HP', 'tel', 'ti-phone', '08xxxxxxxxxx', $fieldErrors, $formData); ?></div>
                    </div>

                    <!-- Password -->
                    <?php printInputField('password', 'Password', 'password', 'ti-lock', 'Minimal 8 karakter', $fieldErrors, $formData, true); ?>

                    <!-- Confirm Password -->
                    <?php printInputField('confirm_password', 'Konfirmasi Password', 'password', 'ti-lock-check', 'Ulangi password Anda', $fieldErrors, $formData, true); ?>

                    <!-- Submit Button -->
                    <button type="submit"
                        class="w-full h-11 bg-[#1E3A8A] hover:bg-[#1D4ED8]
               active:scale-[0.99] text-white text-sm font-medium
               rounded-lg transition flex items-center justify-center gap-2 mt-2">
                        Buat Akun
                        <i class="ti ti-arrow-right text-base"></i>
                    </button>

                </form>

                <!-- Divider Section -->
                <div class="flex items-center gap-3 my-5">
                    <hr class="flex-1 border-slate-200">
                    <span class="text-xs text-slate-400">atau</span>
                    <hr class="flex-1 border-slate-200">
                </div>

                <!-- Login Link Section -->
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

        window.addEventListener('DOMContentLoaded', function() {
            // Cek apakah ada success alert (bg-green-50)
            const successAlert = document.querySelector('.bg-green-50');
            if (successAlert) {
                let countdown = 3; // Timer 3 detik
                const successText = successAlert.querySelector('span');
                const originalText = successText.textContent;

                // Update countdown setiap detik
                const countdownInterval = setInterval(function() {
                    countdown--;
                    successText.textContent = `Akun berhasil dibuat! Mengarahkan ke halaman login dalam ${countdown} detik...`;
                    
                    // Jika countdown habis, redirect
                    if (countdown <= 0) {
                        clearInterval(countdownInterval);
                        window.location.href = '<?= BASE_URL ?>views/login.php';
                    }
                }, 1000);

                // Redirect juga setelah 3 detik sebagai backup
                setTimeout(function() {
                    clearInterval(countdownInterval);
                    window.location.href = '<?= BASE_URL ?>views/login.php';
                }, 3000);
            }
        });
    </script>
</body>

</html>