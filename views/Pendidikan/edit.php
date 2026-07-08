<?php

require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isCandidate() or die("Access denied");

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID pendidikan tidak ditemukan");
}

$data = PendidikanController::getById(
    $conn,
    $id
);

if (!$data) {
    die("Data pendidikan tidak ditemukan");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kirim data POST ke controller
    $response = PendidikanController::update($conn, $id, $_POST);

    if ($response['success']) {
        header("Location: " . BASE_URL . "views/candidate/profile.php?id=" . $data['candidate_id'] . "#pendidikan");
        exit;
    } else {
        $errors = $response['messages'];
        // Update data lokal agar input form tetap berisi apa yang diketik user saat error
        $data['institusi'] = $_POST['institusi'];
        $data['jenjang'] = $_POST['jenjang'];
        $data['jurusan'] = $_POST['jurusan'];
        $data['tahun_masuk'] = $_POST['tahun_masuk'];
        $data['tahun_lulus'] = $_POST['tahun_lulus'];
        $data['ipk'] = $_POST['ipk'];
    }
}
ob_start();
?>

<?php if (!empty($errors)): ?>
    <div class="mb-4 p-4 rounded-xl text-sm" style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">
        <ul class="list-disc ml-5">
            <?php foreach ($errors as $err): ?>
                <li><?= $err ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color:#1E293B;">
            Edit Pendidikan
        </h1>

        <p class="text-sm" style="color:#64748B;">
            Perbarui data pendidikan kandidat
        </p>
    </div>

    <a
        href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $data['candidate_id'] ?>#pendidikan"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;">
        ← Kembali
    </a>
</div>

<?php if (!empty($error)): ?>
    <div
        class="mb-4 p-4 rounded-xl"
        style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div
    class="rounded-2xl overflow-hidden"
    style="background:#FFFFFF;border:1px solid #E2E8F0;">

    <form method="POST">

        <div style="padding:24px;">
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

                    <label class="text-xs font-semibold text-slate-600">
                        Institusi
                    </label>

                    <input
                        type="text"
                        name="institusi"
                        required
                        value="<?= htmlspecialchars($data['institusi']) ?>"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">
                </div>

                <!-- JENJANG -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold text-slate-600">
                        Jenjang
                    </label>

                    <select
                        id="jenjang"
                        name="jenjang"
                        required
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">

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
                                <?= $data['jenjang'] === $j ? 'selected' : '' ?>>

                                <?= $j ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- JURUSAN -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold text-slate-600">
                        Jurusan
                    </label>

                    <input
                        type="text"
                        name="jurusan"
                        required
                        value="<?= htmlspecialchars($data['jurusan']) ?>"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">
                </div>

                <!-- IPK -->
                <div class="flex flex-col gap-1">
                    <label id="nilaiLabel" class="text-xs font-semibold text-slate-600">IPK / Nilai</label>
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        name="ipk"
                        id="ipk"
                        value="<?= htmlspecialchars($data['ipk'] ?? '') ?>"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none border border-slate-300 bg-slate-50">
                    <small id="ipkInfo" class="text-xs text-slate-500"></small>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">

                <!-- TAHUN MASUK -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold text-slate-600">
                        Tahun Masuk
                    </label>

                    <input
                        type="number"
                        min="1900"
                        max="<?= date('Y') ?>"
                        name="tahun_masuk"
                        required
                        value="<?= $data['tahun_masuk'] ?>"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">
                </div>

                <!-- TAHUN LULUS -->
                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold text-slate-600">
                        Tahun Lulus
                    </label>

                    <input
                        type="number"
                        min="1900"
                        max="<?= date('Y') + 10 ?>"
                        name="tahun_lulus"
                        value="<?= $data['tahun_lulus'] ?>"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">
                </div>

            </div>

            <div class="flex justify-end gap-3 mt-8">

                <a
                    href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $data['candidate_id'] ?>#pendidikan"
                    class="px-4 py-2 rounded-lg border border-slate-300 text-sm">

                    Batal

                </a>

                <button
                    type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-800 text-white text-sm hover:bg-blue-900">

                    Update Pendidikan

                </button>

            </div>

        </div>

    </form>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenjang = document.getElementById('jenjang');
        const ipk = document.getElementById('ipk');
        const info = document.getElementById('ipkInfo');
        const label = document.getElementById('nilaiLabel');
        const jurusan = document.querySelector('[name="jurusan"]'); // Pastikan selektor benar
        const thnMasuk = document.querySelector('[name="tahun_masuk"]');
        const thnLulus = document.querySelector('[name="tahun_lulus"]');

        function updateValidation() {
            const val = jenjang.value;
            const tanpaJurusan = ['SD', 'SMP', 'SMA'];
            const listSekolah = ['SD', 'SMP', 'SMA', 'SMK'];

            // --- LOGIKA JURUSAN ---
            if (tanpaJurusan.includes(val)) {
                jurusan.value = ''; // 1. Kosongkan isinya
                jurusan.disabled = true; // 2. Matikan inputnya
                jurusan.required = false; // 3. Tidak wajib diisi
                jurusan.placeholder = 'Tidak diperlukan untuk ' + val;
                jurusan.style.background = '#F1F5F9'; // Beri warna abu-abu tanda mati
                jurusan.style.cursor = 'not-allowed';
            } else {
                jurusan.disabled = false; // 1. Aktifkan kembali
                jurusan.required = true; // 2. Wajib diisi (untuk SMK - S3)
                jurusan.style.background = '#F8FAFC';
                jurusan.style.cursor = 'text';
                // Jangan kosongkan isinya di sini agar data lama tidak hilang saat milih SMK/D3
            }

            // --- LOGIKA IPK / NILAI ---
            if (listSekolah.includes(val)) {
                label.innerText = 'Nilai';
                ipk.max = 100;
                ipk.placeholder = '90.00';
                info.innerText = 'Nilai Sekolah (SD-SMK): 0 - 100';
            } else {
                label.innerText = 'IPK';
                ipk.max = 4;
                ipk.placeholder = '3.75';
                info.innerText = 'IPK Kuliah (D1-S3): 0.00 - 4.00';
            }
        }

        // Validasi Tahun
        function validateYears() {
            if (thnLulus.value && parseInt(thnMasuk.value) > parseInt(thnLulus.value)) {
                thnLulus.setCustomValidity('Tahun lulus tidak boleh lebih kecil dari tahun masuk');
            } else {
                thnLulus.setCustomValidity('');
            }
        }

        // Jalankan fungsi saat ada perubahan
        jenjang.addEventListener('change', updateValidation);
        thnMasuk.addEventListener('change', validateYears);
        thnLulus.addEventListener('change', validateYears);

        // JALANKAN SAAT PERTAMA KALI HALAMAN DIBUKA (Penting untuk Edit)
        updateValidation();
        validateYears();
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>