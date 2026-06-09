    <?php
    require_once __DIR__ . '/../../init.php';

    AuthController::requireLogin();

    $errors = [];

    /*
    |--------------------------------------------------------------------------
    | Ambil Candidate ID dari URL
    |--------------------------------------------------------------------------
    */
    $candidate_id = $_GET['candidate_id'] ?? null;

    if (!$candidate_id) {
        die("Candidate ID tidak ditemukan");
    }

    /*
    |--------------------------------------------------------------------------
    | Submit Form
    |--------------------------------------------------------------------------
    */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $result = PengalamanKerjaController::store(
            $conn,
            $_POST
        );

        if ($result['status']) {

            $_SESSION['success'] =
                "Data pengalaman kerja berhasil ditambahkan";

            header(
                "Location: "
                    . BASE_URL .
                    "views/candidate/profile.php?id="
                    . $_POST['id_candidate']
                    . "#pengalaman-kerja"
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
                Tambah Pengalaman Kerja
            </h1>

            <p class="text-sm" style="color:#64748B;">
                Lengkapi data pengalaman kandidat
            </p>
        </div>

        <a
            href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidate_id ?>"
            class="inline-flex   items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
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

        <form method="POST">

            <!-- Hidden Candidate ID -->
            <input
                type="hidden"
                name="id_candidate"
                value="<?= htmlspecialchars($candidate_id) ?>">

            <div style="padding:24px;">

                <!-- INFORMASI PEKERJAAN -->
                <div
                    class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4"
                    style="color:#94A3B8;">

                    <span>Informasi Pekerjaan</span>

                    <div
                        style="flex:1;height:.5px;background:#E2E8F0;">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <!-- NAMA PERUSAHAAN -->
                    <div class="flex flex-col gap-1">

                        <label
                            class="text-xs font-semibold"
                            style="color:#475569;">

                            Nama Perusahaan
                            <span style="color:#EF4444;">*</span>

                        </label>

                        <input
                            type="text"
                            name="nama_perusahaan"
                            required
                            placeholder="PT Maju Bersama"
                            class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                            style="border:1px solid #CBD5E1;background:#F8FAFC;"
                            value="<?= htmlspecialchars($_POST['nama_perusahaan'] ?? '') ?>">
                    </div>

                    <!-- POSISI -->
                    <div class="flex flex-col gap-1">

                        <label
                            class="text-xs font-semibold"
                            style="color:#475569;">

                            Posisi
                            <span style="color:#EF4444;">*</span>

                        </label>

                        <input
                            type="text"
                            name="posisi"
                            required
                            placeholder="Software Engineer"
                            class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                            style="border:1px solid #CBD5E1;background:#F8FAFC;"
                            value="<?= htmlspecialchars($_POST['posisi'] ?? '') ?>">
                    </div>

                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">

                    <!-- TANGGAL MULAI -->
                    <div class="flex flex-col gap-1">

                        <label
                            class="text-xs font-semibold"
                            style="color:#475569;">

                            Tanggal Mulai
                            <span style="color:#EF4444;">*</span>

                        </label>

                        <input
                            type="date"
                            name="tanggal_mulai"
                            required
                            class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                            style="border:1px solid #CBD5E1;background:#F8FAFC;"
                            value="<?= htmlspecialchars($_POST['tanggal_mulai'] ?? '') ?>">
                    </div>

                    <!-- TANGGAL SELESAI -->
                    <div class="flex flex-col gap-1">

                        <label
                            class="text-xs font-semibold"
                            style="color:#475569;">
                            Tanggal Selesai
                        </label>

                        <input
                            type="date"
                            name="tanggal_selesai"
                            class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                            style="border:1px solid #CBD5E1;background:#F8FAFC;"
                            value="<?= htmlspecialchars($_POST['tanggal_selesai'] ?? '') ?>">

                        <?php if (isset($errors['tanggal_selesai'])): ?>
                            <small style="color:#DC2626;">
                                <?= $errors['tanggal_selesai']; ?>
                            </small>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- DESKRIPSI -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">
                        Deskripsi Pekerjaan
                    </label>

                    <textarea
                        name="deskripsi_pekerjaan"
                        rows="5"
                        placeholder="Jelaskan tugas dan tanggung jawab selama bekerja..."
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none resize-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"><?= htmlspecialchars($_POST['deskripsi_pekerjaan'] ?? '') ?></textarea>

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
                    💾 Simpan Data
                </button>

            </div>

        </form>

    </div>

    <?php
    $content = ob_get_clean();
    include __DIR__ . '/../layouts/app.php';
    ?>