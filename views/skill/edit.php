<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

$skill = SkillController::getSkillById($conn, $id);

if (!$skill) {
    die("Data skill tidak ditemukan");
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_skill = trim($_POST['nama_skill']);

    if (empty($nama_skill)) {
        $errors['nama_skill'] = "Nama skill wajib diisi";
    }

    if (empty($errors)) {

        if (SkillController::updateSkill(
            $conn,
            $id,
            $nama_skill
        )) {

            $_SESSION['success'] = "Skill berhasil diperbarui!";

            header("Location: index.php");
            exit;
        }

        $errors['umum'] = "Gagal memperbarui skill";
    }
}

ob_start();
?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color:#1E293B;">
            Edit Skill
        </h1>

        <p class="text-sm" style="color:#64748B;">
            Perbarui data skill pada sistem rekrutmen
        </p>
    </div>

    <a href="index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;">
        ← Kembali
    </a>
</div>

<?php if (isset($errors['umum'])): ?>
    <div class="mb-4 p-4 rounded-xl border"
        style="background:#FEF2F2;border:1px solid #FECACA;color:#991B1B;">
        <?= $errors['umum'] ?>
    </div>
<?php endif; ?>

<!-- CARD -->
<div class="rounded-2xl overflow-hidden"
    style="background:#FFFFFF;border:1px solid #E2E8F0;">

    <form method="POST">

        <div style="padding:24px;">

            <!-- SECTION -->
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4"
                style="color:#94A3B8;">
                <span>Informasi Skill</span>
                <div style="flex:1;height:.5px;background:#E2E8F0;"></div>
            </div>

            <!-- ICON -->
            <div class="flex items-start gap-4 mb-6">

                <div class="flex items-center justify-center rounded-full flex-shrink-0"
                    style="width:64px;height:64px;background:#DBEAFE;border:2px solid #BFDBFE;">
                    <span style="font-size:28px;">✏️</span>
                </div>

                <div>
                    <h4 class="font-semibold text-sm"
                        style="color:#1E293B;">
                        Edit Data Skill
                    </h4>

                    <p class="text-xs mt-1"
                        style="color:#64748B;">
                        Ubah nama skill yang sudah tersedia pada sistem.
                    </p>
                </div>

            </div>

            <!-- FIELD -->
            <div class="grid gap-4">

                <div class="flex flex-col gap-1">

                    <label class="text-xs font-semibold"
                        style="color:#475569;">
                        Nama Skill
                        <span style="color:#EF4444;">*</span>
                    </label>

                    <div class="relative">

                        <span class="absolute left-3 top-1/2 -translate-y-1/2"
                            style="color:#94A3B8;">
                            🛠️
                        </span>

                        <input
                            type="text"
                            name="nama_skill"
                            required
                            value="<?= htmlspecialchars($_POST['nama_skill'] ?? $skill['nama_skill']) ?>"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border:1px solid #CBD5E1;background:#F8FAFC;color:#1E293B;">
                    </div>

                    <?php if(isset($errors['nama_skill'])): ?>
                        <small style="color:#DC2626;">
                            <?= $errors['nama_skill'] ?>
                        </small>
                    <?php endif; ?>

                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="flex items-center justify-between px-6 py-4"
            style="border-top:1px solid #E2E8F0;background:#F8FAFC;">

            <a href="index.php"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg"
                style="background:#fff;color:#64748B;border:1px solid #CBD5E1;">
                ← Batal
            </a>

            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-lg text-white"
                style="background:#1E3A8A;border:none;">
                💾 Update Skill
            </button>

        </div>

    </form>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>