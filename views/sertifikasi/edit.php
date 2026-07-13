<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

$errors = [];

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID Sertifikasi tidak ditemukan");
}

$sertifikasi = SertifikasiController::findById(
    $conn,
    $id
);

if (!$sertifikasi) {
    die("Data sertifikasi tidak ditemukan");
}

$candidate_id = $sertifikasi['candidate_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = SertifikasiController::update(
        $conn,
        // $id,
        $_POST,
        $_FILES
    );

    if ($result['status']) {

        $_SESSION['success'] =
            "Data sertifikasi berhasil diperbarui";

        header(
            "Location: "
                . BASE_URL .
                "views/candidate/profile.php?id="
                . $candidate_id .
                "&status=success_update#sertifikasi"
        );
        exit;
    }

    $errors = $result['errors'];
}

ob_start();
?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">

    <div>

        <h1 class="text-xl font-bold" style="color:#1E293B;">
            Edit Sertifikasi
        </h1>

        <p class="text-sm" style="color:#64748B;">
            Perbarui data sertifikasi kandidat
        </p>

    </div>

    <a href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidate_id ?>#sertifikasi"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;">

        ← Kembali

    </a>

</div>

<?php if (isset($errors['umum'])): ?>

    <div class="mb-4 p-4 rounded-xl" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">

        <?= $errors['umum']; ?>

    </div>

<?php endif; ?>

<div class="rounded-2xl overflow-hidden" style="background:#FFFFFF;border:1px solid #E2E8F0;">

    <form method="POST" enctype="multipart/form-data">

        <input type="hidden" name="candidate_id" value="<?= $candidate_id ?>">

        <input type="hidden" name="id" value="<?= $sertifikasi['id_sertifikasi'] ?>">

        <div style="padding:24px;">

            <!-- INFORMASI -->
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4"
                style="color:#94A3B8;">

                <span>Informasi Sertifikasi</span>

                <div style="flex:1;height:.5px;background:#E2E8F0;">
                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- NAMA -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold" style="color:#475569;">

                        Nama Sertifikasi
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input type="text" name="nama_sertifikasi" required placeholder="Laravel Certified Developer"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;" value="<?= htmlspecialchars(
                                                                                        $_POST['nama_sertifikasi']
                                                                                            ?? $sertifikasi['nama_sertifikasi']
                                                                                    ) ?>">

                </div>

                <!-- PENYELENGGARA -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold" style="color:#475569;">

                        Penyelenggara
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input type="text" name="penyelenggara" required placeholder="Dicoding Indonesia"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;" value="<?= htmlspecialchars(
                                                                                        $_POST['penyelenggara']
                                                                                            ?? $sertifikasi['penyelenggara']
                                                                                    ) ?>">

                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- TANGGAL -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold" style="color:#475569;">

                        Tanggal Terbit
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input type="date" name="tanggal_terbit" required
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;" value="<?= htmlspecialchars(
                                                                                        $_POST['tanggal_terbit']
                                                                                            ?? $sertifikasi['tanggal_terbit']
                                                                                    ) ?>">

                </div>

                <!-- FILE -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold" style="color:#475569;">

                        File Sertifikasi

                    </label>

                    <input type="file" name="file_sertifikasi" id="file_sertifikasi"
                        accept=".pdf,.jpg,.jpeg,.png"
                        onchange="validateSertifikat(this)"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">

                    <small id="file_error_msg" style="color:#64748B;">
                        Format: PDF, JPG, JPEG, PNG (Maks. 2MB). Kosongkan jika tidak ingin mengganti file.
                    </small>

                    <?php if (!empty($sertifikasi['file_sertifikasi'])): ?>

                        <div class="mt-3 p-3 rounded-xl border" style="border-color:#E2E8F0;">

                            <p class="text-xs font-semibold text-slate-600 mb-1">
                                File Saat Ini
                            </p>

                            <a href="<?= BASE_URL ?>uploads/sertifikasi/<?= $sertifikasi['file_sertifikasi'] ?>"
                                target="_blank" class="text-blue-600 text-xs hover:underline">

                                📎 <?= htmlspecialchars($sertifikasi['file_sertifikasi']) ?>

                            </a>

                        </div>

                    <?php endif; ?>

                    <?php if (isset($errors['file_sertifikasi'])): ?>

                        <small style="color:#DC2626;">
                            <?= $errors['file_sertifikasi']; ?>
                        </small>

                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="flex items-center justify-between px-6 py-4"
            style="border-top:1px solid #E2E8F0;background:#F8FAFC;">

            <a href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidate_id ?>#sertifikasi"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg"
                style="background:#FFF;color:#64748B;border:1px solid #CBD5E1;">

                ← Batal

            </a>

            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-lg text-white"
                style="background:#1E3A8A;">

                💾 Update Sertifikasi

            </button>

        </div>

    </form>

</div>

<script>
    function validateSertifikat(input) {
        const file = input.files[0];
        const errorMsg = document.getElementById('file_error_msg');
        const submitBtn = document.querySelector('button[type="submit"]');
        const allowedExtensions = ['pdf', 'jpg', 'jpeg', 'png'];

        if (file) {
            const extension = file.name.split('.').pop().toLowerCase();
            const fileSize = file.size / 1024 / 1024; // hitung dalam MB

            // 1. Validasi Ekstensi
            if (!allowedExtensions.includes(extension)) {
                errorMsg.textContent = "Format file salah! Harus PDF, JPG, atau PNG.";
                errorMsg.style.color = "#DC2626"; // Merah
                input.style.borderColor = "#DC2626";
                submitBtn.disabled = true;
                submitBtn.style.opacity = "0.5";
                submitBtn.style.cursor = "not-allowed";
                return;
            }

            // 2. Validasi Ukuran (2MB)
            if (fileSize > 2) {
                errorMsg.textContent = "Ukuran file terlalu besar! Maksimal adalah 2MB.";
                errorMsg.style.color = "#DC2626";
                input.style.borderColor = "#DC2626";
                submitBtn.disabled = true;
                submitBtn.style.opacity = "0.5";
                submitBtn.style.cursor = "not-allowed";
                return;
            }

            // 3. Jika Lolos Validasi
            errorMsg.textContent = "File siap diganti: " + file.name;
            errorMsg.style.color = "#16A34A"; // Hijau
            input.style.borderColor = "#16A34A";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
            submitBtn.style.cursor = "pointer";
        } else {
            // Jika batal pilih file (dikosongkan kembali)
            errorMsg.textContent = "Kosongkan jika tidak ingin mengganti file (Format: PDF, JPG, PNG)";
            errorMsg.style.color = "#64748B";
            input.style.borderColor = "#CBD5E1";
            submitBtn.disabled = false;
            submitBtn.style.opacity = "1";
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>