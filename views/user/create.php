<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isAdmin() or die("Access denied");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = StaffController::store($conn, $_POST, $_FILES);

    if ($result['status'] === true) {
        $_SESSION['success'] = "Akun HRD " . htmlspecialchars($_POST['nama_staff']) . " berhasil dibuat!";
        header(
            "Location: " . BASE_URL .
                "views/candidate/profile.php?id=" .
                $_POST['candidate_id'] .
                "#pendidikan"
        );
    } else {
        $errors = $result['errors'];
    }
    exit;
}

ob_start();

?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Tambah Data Staff</h1>
        <p class="text-sm" style="color: #64748B;">Lengkapi semua field yang wajib diisi</p>
    </div>
    <a href="<?= BASE_URL ?>views/user/index.php" class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
        ← Kembali
    </a>
</div>

<?php if (isset($errors['umum'])): ?>
    <div class="mb-4 p-4 rounded-xl border flex items-center gap-3 animate-fade-in-down"
        style="background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B;">
        <span>⚠️</span>
        <span class="text-sm font-semibold"><?= $errors['umum'] ?></span>
    </div>
<?php endif; ?>

<!-- FORM CARD -->
<div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">

    <form action="" method="POST" enctype="multipart/form-data">

        <div style="padding: 24px;">

            <!-- SECTION: INFORMASI AKUN -->
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4" style="color: #94A3B8;">
                <span>Informasi Akun</span>
                <div style="flex: 1; height: 0.5px; background: #E2E8F0;"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- NAMA STAFF -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        Nama Lengkap <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: #94A3B8;">👤</span>
                        <input type="text" name="nama_staff" required
                            placeholder="Contoh: Budi Santoso"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['nama_staff'] ?? '') ?>" />
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['email']) ? '#EF4444' : '#475569' ?>;">
                        Email <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: <?= isset($errors['email']) ? '#EF4444' : '#94A3B8' ?>;">✉️</span>
                        <input type="email" name="email" placeholder="Contoh: budi.santoso@example.com" required
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none transition-all"
                            style="border: 1px solid <?= isset($errors['email']) ? '#EF4444' : '#CBD5E1' ?>; 
                   background: <?= isset($errors['email']) ? '#FFF1F2' : '#F8FAFC' ?>; 
                   color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
                    </div>
                    <?php if (isset($errors['email'])): ?>
                        <p class="text-[10px] font-bold mt-1" style="color: #EF4444;"><?= $errors['email'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- USERNAME -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['username']) ? '#EF4444' : '#475569' ?>;">
                        Username <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: <?= isset($errors['username']) ? '#EF4444' : '#94A3B8' ?>;">👤</span>
                        <input type="text" name="username" placeholder="Contoh: budi_santoso" required
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none transition-all"
                            style="border: 1px solid <?= isset($errors['username']) ? '#EF4444' : '#CBD5E1' ?>; 
                   background: <?= isset($errors['username']) ? '#FFF1F2' : '#F8FAFC' ?>; 
                   color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
                    </div>
                    <?php if (isset($errors['username'])): ?>
                        <p class="text-[10px] font-bold mt-1" style="color: #EF4444;"><?= $errors['username'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- PASSWORD -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        Password <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: #94A3B8;">🔒</span>
                        <input type="password" name="password" required
                            placeholder="**********"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;" />
                    </div>
                </div>

            </div>

            <div class="grid gap-4 mb-6">

                <!-- NO TELEPON -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        No. Telepon <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: #94A3B8;">📞</span>
                        <input type="tel" name="no_telp" required
                            placeholder="08xx-xxxx-xxxx"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>" />
                    </div>
                </div>

            </div>

            <!-- SECTION: DATA PRIBADI -->
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4" style="color: #94A3B8;">
                <span>Data Pribadi</span>
                <div style="flex: 1; height: 0.5px; background: #E2E8F0;"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">

                <!-- TANGGAL LAHIR -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        Tanggal Lahir <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color: #94A3B8;">📅</span>
                        <input type="date" name="tanggal_lahir" required
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>" />
                    </div>
                </div>

                <!-- JENIS KELAMIN -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        Jenis Kelamin <span style="color: #EF4444;">*</span>
                    </label>
                    <div class="flex gap-2">
                        <?php
                        $selectedGender = $_POST['jenis_kelamin'] ?? '';
                        $genders = [
                            'L' => '♂ Laki-laki',
                            'P' => '♀ Perempuan'
                        ];
                        foreach ($genders as $val => $label):
                            $isSelected = ($selectedGender === $val);
                        ?>
                            <label class="flex-1 flex items-center gap-2 px-3 py-2 rounded-lg cursor-pointer text-sm transition"
                                style="border: 1px solid <?= $isSelected ? '#1E3A8A' : '#CBD5E1' ?>;
                                   background: <?= $isSelected ? '#EFF6FF' : '#F8FAFC' ?>;
                                   color: <?= $isSelected ? '#1E3A8A' : '#475569' ?>; font-weight: <?= $isSelected ? '600' : '400' ?>;">
                                <input type="radio" name="jenis_kelamin" value="<?= $val ?>" required
                                    class="hidden" <?= $isSelected ? 'checked' : '' ?> />
                                <?= $label ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

            <!-- ALAMAT -->
            <div class="flex flex-col gap-1 mb-6">
                <label class="text-xs font-semibold" style="color: #475569;">Alamat Lengkap <span style="color: #EF4444;">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-3" style="color: #94A3B8;">📍</span>
                    <textarea name="alamat" rows="3"
                        placeholder="Jl. Sudirman No.12, Jakarta Pusat..."
                        class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none resize-none"
                        style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- SECTION: FOTO -->
            <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider mb-4" style="color: #94A3B8;">
                <span>Foto Profil</span>
                <div style="flex: 1; height: 0.5px; background: #E2E8F0;"></div>
            </div>

            <div class="flex items-start gap-4">

                <!-- PREVIEW -->
                <div id="previewWrap"
                    class="flex items-center justify-center rounded-full flex-shrink-0 overflow-hidden"
                    style="width: 64px; height: 64px; background: #DBEAFE; border: 2px solid #BFDBFE;">
                    <span style="font-size: 28px;">👤</span>
                </div>

                <!-- UPLOAD BOX -->
                <div class="flex-1">
                    <div onclick="document.getElementById('fotoInput').click()"
                        class="flex flex-col items-center justify-center gap-1 py-5 rounded-xl cursor-pointer transition"
                        style="border: 1.5px dashed #BFDBFE; background: #F0F7FF;"
                        onmouseover="this.style.borderColor='#3B82F6'; this.style.background='#EFF6FF'"
                        onmouseout="this.style.borderColor='#BFDBFE'; this.style.background='#F0F7FF'">
                        <span style="font-size: 24px;">☁️</span>
                        <span class="text-sm font-semibold" style="color: #1E3A8A;">Klik untuk upload foto</span>
                        <span class="text-xs" style="color: #64748B;">PNG, JPG, JPEG — maks. 2MB</span>
                    </div>
                    <input type="file" id="fotoInput" name="foto" accept="image/png,image/jpg,image/jpeg"
                        class="hidden" onchange="previewFoto(event)" />
                    <p class="text-xs mt-1" style="color: #94A3B8;">
                        Disarankan foto formal dengan latar belakang polos
                    </p>
                </div>

            </div>

        </div>

        <!-- FOOTER -->
        <div class="flex items-center justify-between px-6 py-4" style="border-top: 1px solid #E2E8F0; background: #F8FAFC;">
            <a href="<?= BASE_URL ?>views/user/index.php"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg"
                style="background: #fff; color: #64748B; border: 1px solid #CBD5E1;">
                ← Batal
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-lg text-white transition"
                style="background: #1E3A8A; border: none;">
                💾 Simpan Data
            </button>
        </div>

    </form>
</div>

<script>
    function previewFoto(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validasi ukuran 2MB
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB');
            e.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(ev) {
            const wrap = document.getElementById('previewWrap');
            wrap.innerHTML = '<img src="' + ev.target.result + '" style="width:100%;height:100%;object-fit:cover;" />';
        };
        reader.readAsDataURL(file);
    }

    // Radio gender — toggle style saat diklik
    document.querySelectorAll('input[name="jenis_kelamin"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('input[name="jenis_kelamin"]').forEach(r => {
                const label = r.closest('label');
                if (r.checked) {
                    label.style.borderColor = '#1E3A8A';
                    label.style.background = '#EFF6FF';
                    label.style.color = '#1E3A8A';
                    label.style.fontWeight = '600';
                } else {
                    label.style.borderColor = '#CBD5E1';
                    label.style.background = '#F8FAFC';
                    label.style.color = '#475569';
                    label.style.fontWeight = '400';
                }
            });
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>