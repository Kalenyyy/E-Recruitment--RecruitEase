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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {

        PendidikanController::update(
            $conn,
            $id,
            [
                'institusi'    => $_POST['institusi'],
                'jenjang'      => $_POST['jenjang'],
                'jurusan'      => $_POST['jurusan'],
                'tahun_masuk'  => $_POST['tahun_masuk'],
                'tahun_lulus'  => $_POST['tahun_lulus'],
                'ipk'          => $_POST['ipk']
            ]
        );

        header(
            "Location: " .
                BASE_URL .
                "views/candidate/profile.php?id=" .
                $data['candidate_id'] .
                "#pendidikan"
        );
        exit;
    } catch (Exception $e) {

        $error = $e->getMessage();
    }
}

ob_start();
?>

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

                    <label
                        id="nilaiLabel"
                        class="text-xs font-semibold text-slate-600">

                        IPK / Nilai

                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="4"
                        name="ipk"
                        id="ipk"
                        placeholder="3.75"
                        value="<?= htmlspecialchars($data['ipk']) ?>"
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none"
                        style="border:1px solid #CBD5E1;background:#F8FAFC;">

                    <small
                        id="ipkInfo"
                        class="text-xs text-slate-500">
                    </small>
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

        function updateValidation() {

            const tanpaJurusan = [
                'SD',
                'SMP',
                'SMA'
            ];

            if (tanpaJurusan.includes(jenjang.value)) {

                label.innerHTML = 'Nilai';
                info.innerHTML = 'Nilai untuk SD/SMP/SMA/SMK : 0 - 100';
                ipk.max = 100;
                ipk.placeholder = '90.50';

            } else {

                label.innerHTML = 'IPK';
                info.innerHTML = 'IPK untuk D1-D4/S1-S3 : 0.00 - 4.00';
                ipk.max = 4;
                ipk.placeholder = '3.75';
            }
        }

        updateValidation();

        jenjang.addEventListener(
            'change',
            updateValidation
        );
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>