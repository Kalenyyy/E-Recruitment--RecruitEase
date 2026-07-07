<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isCandidate() or die("Access denied");

$candidateId =
    $_GET['candidate_id']
    ?? $_POST['candidate_id']
    ?? null;

if (!$candidateId) {
    die("Candidate ID tidak ditemukan");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Panggil controller dan tampung hasilnya
    $response = PendidikanController::create($conn, $_POST);

    if ($response['success']) {
        // Jika sukses, redirect
        header(
            "Location: " . BASE_URL . "views/candidate/profile.php?id=" .
                $_POST['candidate_id'] . "#pendidikan"
        );
        exit;
    } else {
        // Jika gagal, ambil pesan errornya
        $errors = $response['messages'];
    }
}

ob_start();
?>

<?php if (!empty($errors)): ?>
    <div class="mb-4 p-4 rounded-xl" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">
        <ul class="list-disc ml-5">
            <?php foreach ($errors as $error): ?>
                <li class="text-sm"><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color:#1E293B;">
            Tambah Pendidikan
        </h1>

        <p class="text-sm" style="color:#64748B;">
            Lengkapi data pendidikan kandidat
        </p>
    </div>

    <a
        href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidateId ?>"
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

    <form method="POST">

        <input
            type="hidden"
            name="candidate_id"
            value="<?= htmlspecialchars($candidateId) ?>">

        <div style="padding:24px;">

            <!-- INFORMASI PENDIDIKAN -->
            <div
                class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4"
                style="color:#94A3B8;">

                <span>Informasi Pendidikan</span>

                <div
                    style="flex:1;height:.5px;background:#E2E8F0;">
                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- INSTITUSI -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Institusi
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input
                        type="text"
                        name="institusi"
                        required
                        placeholder="Universitas Indonesia"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['institusi'] ?? '') ?>">
                </div>

                <!-- JENJANG -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Jenjang
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <select
                        name="jenjang"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">

                        <option value="">Pilih Jenjang</option>

                        <?php
                        $jenjangList = [
                            'SD',
                            'SMP',
                            'SMA',
                            'SMK',
                            'D1',
                            'D2',
                            'D3',
                            'D4',
                            'S1',
                            'S2',
                            'S3'
                        ];
                        foreach ($jenjangList as $j):
                        ?>
                            <option
                                value="<?= $j ?>"
                                <?= ($_POST['jenjang'] ?? '') === $j ? 'selected' : '' ?>>

                                <?= $j ?>

                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- JURUSAN -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Jurusan
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input
                        type="text"
                        name="jurusan"
                        id="jurusan"
                        placeholder="Teknik Informatika"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['jurusan'] ?? '') ?>">
                </div>

                <!-- IPK -->
                <div class="flex flex-col gap-1">

                    <label
                        id="nilaiLabel"
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        IPK / Nilai

                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="ipk"
                        id="ipk"
                        placeholder="3.75"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['ipk'] ?? '') ?>">

                    <small
                        id="ipkInfo"
                        class="text-xs text-slate-500">
                        Pilih jenjang terlebih dahulu
                    </small>
                </div>

            </div>

            <div class="grid grid-cols-2 gap-4">

                <!-- TAHUN MASUK -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Tahun Masuk
                        <span style="color:#EF4444;">*</span>

                    </label>

                    <input
                        type="number"
                        min="1900"
                        max="<?= date('Y') ?>"
                        name="tahun_masuk"
                        required
                        placeholder="2021"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['tahun_masuk'] ?? '') ?>">
                </div>

                <!-- TAHUN LULUS -->
                <div class="flex flex-col gap-1">

                    <label
                        class="text-xs font-semibold"
                        style="color:#475569;">

                        Tahun Lulus

                    </label>

                    <input
                        type="number"
                        min="1900"
                        max="<?= date('Y') + 10 ?>"
                        name="tahun_lulus"
                        placeholder="2025"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;"
                        value="<?= htmlspecialchars($_POST['tahun_lulus'] ?? '') ?>">
                </div>

            </div>

            <!-- BUTTON -->
            <div class="flex justify-end gap-3 mt-8">

                <a
                    href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidateId ?>#pendidikan"
                    class="px-4 py-2 rounded-lg border border-slate-300 text-sm">

                    Batal

                </a>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-800 text-white text-sm hover:bg-blue-900">

                    Simpan Pendidikan

                </button>

            </div>

        </div>

    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const jenjang = document.querySelector('[name="jenjang"]');
        const ipk = document.getElementById('ipk');
        const info = document.getElementById('ipkInfo');
        const label = document.getElementById('nilaiLabel');
        const jurusanInput = document.getElementById('jurusan');

        function updateValidation() {
            const jenjangValue = jenjang.value;

            // 1. Logika JURUSAN (Hanya SD, SMP, SMA yang tidak butuh jurusan)
            const listTanpaJurusan = ['SD', 'SMP', 'SMA'];

            if (listTanpaJurusan.includes(jenjangValue)) {
                jurusanInput.value = '';
                jurusanInput.disabled = true;
                jurusanInput.required = false;
                jurusanInput.placeholder = 'Tidak diperlukan';
            } else {
                jurusanInput.disabled = false;
                jurusanInput.required = true;
                jurusanInput.placeholder = 'Teknik Komputer / Akuntansi';
            }

            // 2. Logika NILAI vs IPK (SD, SMP, SMA, SMK pakai Nilai 0-100)
            const listSekolah = ['SD', 'SMP', 'SMA', 'SMK'];

            if (listSekolah.includes(jenjangValue)) {
                // Mode Nilai 0-100
                label.innerHTML = 'Nilai';
                ipk.max = 100;
                ipk.min = 0;
                ipk.step = 1;
                ipk.placeholder = '90';
                info.innerHTML = 'Nilai untuk SD/SMP/SMA/SMK: 0 - 100';
            } else {
                // Mode IPK 0-4
                label.innerHTML = 'IPK';
                ipk.max = 4;
                ipk.min = 0;
                ipk.step = 0.01;
                ipk.placeholder = '3.75';
                info.innerHTML = 'IPK untuk D1-S3: 0.00 - 4.00';
            }
        }

        jenjang.addEventListener(
            'change',
            updateValidation
        );

        updateValidation();

    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>