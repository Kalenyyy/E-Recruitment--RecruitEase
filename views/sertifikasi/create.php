<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

$errors = [];

$candidate_id = $_GET['candidate_id'] ?? null;

if (!$candidate_id) {
    die("Candidate ID tidak ditemukan");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = SertifikasiController::store(
        $conn,
        $_POST,
        $_FILES
    );

    if ($result['status']) {

        $_SESSION['success'] =
            "Data sertifikasi berhasil ditambahkan";

        header(
            "Location: "
                . BASE_URL .
                "views/candidate/profile.php?id="
                . $_POST['id_candidate']
                . "#sertifikasi"
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
        <h1
            class="text-xl font-bold"
            style="color:#1E293B;">
            Tambah Sertifikasi
        </h1>

        <p
            class="text-sm"
            style="color:#64748B;">
            Lengkapi data sertifikasi kandidat
        </p>
    </div>

    <a
        href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidate_id ?>"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;">
        ← Kembali
    </a>
</div>

<?php if (isset($errors['umum'])): ?>
    <div
        class="mb-4 p-4 rounded-xl"
        style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">
        <?= $errors['umum']; ?>
    </div>
<?php endif; ?>

<div
    class="rounded-2xl overflow-hidden"
    style="background:#FFFFFF;border:1px solid #E2E8F0;">

    <form
        method="POST"
        enctype="multipart/form-data">

        <input
            type="hidden"
            name="id_candidate"
            value="<?= htmlspecialchars($candidate_id) ?>">

        <div style="padding:24px;">

            <!-- INFORMASI SERTIFIKASI -->
            <div
                class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4"
                style="color:#94A3B8;">

                <span>Informasi Sertifikasi</span>

                <div
                    style="flex:1;height:.5px;background:#E2E8F0;">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- NAMA SERTIFIKASI -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Nama Sertifikasi
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input
                        type="text"
                        name="nama_sertifikasi"
                        required
                        placeholder="Laravel Certified Developer"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['nama_sertifikasi'] ?? '') ?>">
                </div>

                <!-- PENYELENGGARA -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Penyelenggara
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input
                        type="text"
                        name="penyelenggara"
                        required
                        placeholder="Dicoding Indonesia"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['penyelenggara'] ?? '') ?>">
                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- TANGGAL TERBIT -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Tanggal Terbit
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input
                        type="date"
                        name="tanggal_terbit"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['tanggal_terbit'] ?? '') ?>">
                </div>

                <!-- FILE -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        File Sertifikasi
                    </label>

                    <input
                        type="file"
                        name="file_sertifikasi"
                        accept=".pdf,.jpg,.jpeg,.png"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">

                    <small style="color:#64748B;">
                        Format: PDF, JPG, JPEG, PNG
                    </small>

                    <?php if (isset($errors['file_sertifikasi'])): ?>
                        <small style="color:#DC2626;">
                            <?= $errors['file_sertifikasi']; ?>
                        </small>
                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div
            class="flex items-center justify-between px-6 py-4"
            style="border-top:1px solid #E2E8F0;background:#F8FAFC;">

            <a
                href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidate_id ?>"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg"
                style="background:#FFF;color:#64748B;border:1px solid #CBD5E1;">
                ← Batal
            </a>

            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-lg text-white"
                style="background:#1E3A8A;">
                📜 Simpan Sertifikasi
            </button>

        </div>

    </form>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>