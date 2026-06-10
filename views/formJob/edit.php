<?php
require_once __DIR__ . '/../../init.php';

// Proteksi Halaman
AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

// 1. Ambil data via Controller (Model find() harus sudah support skill_ids & disability_types)
$job = JobFormController::show($conn, $id);
if (!$job) die("Data tidak ditemukan");

// Ambil data pendukung untuk form
$posisiList = PosisiController::read($conn);
$skillList  = SkillController::getAllSkill($conn);
$errors = [];

// 2. Proses Update Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = JobFormController::update($conn, $id, $_POST);

    if ($result['status'] === true) {
        $_SESSION['success'] = "Job posting \"" . htmlspecialchars($_POST['judul_job']) . "\" berhasil diperbarui!";
        header("Location: index.php");
        exit;
    } else {
        $errors = $result['errors'];
    }
}

ob_start();
?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Edit Job Posting</h1>
        <p class="text-sm" style="color: #64748B;">Perbarui data untuk lowongan ID: #<?= $job['id'] ?></p>
    </div>
    <a href="index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition"
        style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
        ← Kembali
    </a>
</div>

<?php if (isset($errors['umum'])): ?>
    <div class="mb-4 p-4 rounded-xl border flex items-center gap-3"
        style="background: #FEF2F2; border-color: #FECACA; color: #991B1B;">
        <span>⚠️</span>
        <span class="text-sm font-semibold"><?= $errors['umum'] ?></span>
    </div>
<?php endif; ?>

<form action="" method="POST" id="jobForm">
    <div class="flex flex-col gap-5">

        <!-- ===== CARD 1: INFORMASI UTAMA ===== -->
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="px-6 py-4 flex items-center gap-2" style="border-bottom: 1px solid #F1F5F9;">
                <span style="font-size:16px;">📋</span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Informasi Utama</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">

                <!-- JUDUL JOB -->
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['judul_job']) ? '#EF4444' : '#475569' ?>;">
                        Judul Pekerjaan <span style="color:#EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">💼</span>
                        <input type="text" name="judul_job" required placeholder="Contoh: Senior Backend Engineer"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid <?= isset($errors['judul_job']) ? '#EF4444' : '#CBD5E1' ?>;
                               background: <?= isset($errors['judul_job']) ? '#FFF1F2' : '#F8FAFC' ?>; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['judul_job'] ?? $job['judul_job']) ?>">
                    </div>
                </div>

                <!-- POSISI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">Posisi <span style="color:#EF4444;">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">🏷️</span>
                        <select name="posisi_id" required
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none appearance-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;">
                            <?php foreach ($posisiList as $posisi): ?>
                                <option value="<?= $posisi['id'] ?>" <?= (($_POST['posisi_id'] ?? $job['posisi_id']) == $posisi['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($posisi['nama_posisi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- LOKASI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">Lokasi <span style="color:#EF4444;">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">📍</span>
                        <input type="text" name="lokasi" required placeholder="Contoh: Jakarta"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['lokasi'] ?? $job['lokasi']) ?>">
                    </div>
                </div>

                <!-- TIPE PEKERJAAN -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">Tipe Pekerjaan <span style="color:#EF4444;">*</span></label>
                    <?php
                    $tipeList = ['Full Time' => '⏰ Full Time', 'Part Time' => '🕐 Part Time', 'Internship' => '🎓 Magang', 'Freelance' => '💻 Freelance', 'Contract' => '📄 Kontrak'];
                    $selectedTipe = $_POST['tipe_pekerjaan'] ?? $job['tipe_pekerjaan'];
                    ?>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">⏰</span>
                        <select name="tipe_pekerjaan" required class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none appearance-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;">
                            <?php foreach ($tipeList as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $selectedTipe === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- GAJI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">Gaji <span class="font-normal" style="color:#94A3B8;">(opsional)</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold" style="color:#94A3B8;">Rp</span>
                        <input type="number" name="gaji" placeholder="Contoh: 8000000"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['gaji'] ?? $job['gaji']) ?>">
                    </div>
                </div>

                <!-- DESKRIPSI -->
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">Deskripsi Pekerjaan <span style="color:#EF4444;">*</span></label>
                    <textarea name="deskripsi" rows="5" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 outline-none"
                        style="background:#F8FAFC;"><?= htmlspecialchars($_POST['deskripsi'] ?? $job['deskripsi']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- ===== CARD 2: SKILLS ===== -->
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="px-6 py-4 flex items-center justify-between" style="border-bottom: 1px solid #F1F5F9;">
                <div class="flex items-center gap-2">
                    <span style="font-size:16px;">🧠</span>
                    <h2 class="font-bold text-sm" style="color: #1E293B;">Skill yang Dibutuhkan</h2>
                </div>
                <div class="relative w-64">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#94A3B8;">🔍</span>
                    <input type="text" id="skillSearch" placeholder="Cari skill..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 outline-none">
                </div>
            </div>
            <div class="p-6">
                <div class="overflow-y-auto pr-2" style="max-height: 200px;" id="skillWrapper">
                    <div class="flex flex-wrap gap-2" id="skillContainer">
                        <?php
                        $selectedSkills = $_POST['skill_ids'] ?? $job['skill_ids'] ?? [];
                        foreach ($skillList as $skill):
                            $isSelected = in_array($skill['id_skill'], $selectedSkills);
                        ?>
                            <label class="skill-tag flex items-center gap-1.5 px-3 py-1.5 rounded-full cursor-pointer text-xs font-semibold transition border
                                <?= $isSelected ? 'border-blue-800 bg-blue-50 text-blue-800' : 'border-slate-300 bg-slate-50 text-slate-600' ?>"
                                data-nama="<?= strtolower(htmlspecialchars($skill['nama_skill'])) ?>">
                                <input type="checkbox" name="skill_ids[]" value="<?= $skill['id_skill'] ?>"
                                    class="hidden" <?= $isSelected ? 'checked' : '' ?>>
                                <?= htmlspecialchars($skill['nama_skill']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== CARD 3: OPSI KERJA ===== -->
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="px-6 py-4 flex items-center gap-2" style="border-bottom: 1px solid #F1F5F9;">
                <span style="font-size:16px;">⚙️</span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Opsi Pekerjaan</h2>
            </div>
            <div class="p-6 flex flex-col gap-4">
                <?php
                $toggleOptions = [
                    'is_remote_work'      => ['icon' => '🏠', 'label' => 'Remote Work',        'desc' => 'Pekerjaan bisa dilakukan dari rumah'],
                    'is_remote_interview' => ['icon' => '💬', 'label' => 'Remote Interview',   'desc' => 'Proses wawancara dilakukan secara online'],
                ];
                foreach ($toggleOptions as $fieldName => $opt):
                    $isChecked = (isset($_POST[$fieldName]) ? (bool)$_POST[$fieldName] : (bool)$job[$fieldName]);
                ?>
                    <div class="flex items-center justify-between py-1">
                        <div class="flex items-center gap-3">
                            <span style="font-size:20px;"><?= $opt['icon'] ?></span>
                            <div>
                                <p class="text-sm font-semibold" style="color:#1E293B;"><?= $opt['label'] ?></p>
                                <p class="text-xs" style="color:#64748B;"><?= $opt['desc'] ?></p>
                            </div>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="<?= $fieldName ?>" value="0">
                            <input type="checkbox" name="<?= $fieldName ?>" value="1"
                                class="sr-only peer" <?= $isChecked ? 'checked' : '' ?>>
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-blue-800 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ===== CARD 4: DISABILITAS ===== -->
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="px-6 py-4 flex items-center gap-2" style="border-bottom: 1px solid #F1F5F9;">
                <span style="font-size:16px;">♿</span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Aksesibilitas & Disabilitas</h2>
            </div>
            <div class="p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between py-1">
                    <div>
                        <p class="text-sm font-semibold" style="color:#1E293B;">Membuka lowongan untuk penyandang disabilitas</p>
                        <p class="text-xs" style="color:#64748B;">Aktifkan jika posisi ini terbuka untuk kandidat dengan disabilitas</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_disabilitas" value="0">
                        <?php $isDisActive = (isset($_POST['is_disabilitas']) ? $_POST['is_disabilitas'] : $job['is_disabilitas']) == '1'; ?>
                        <input type="checkbox" id="toggleDisabilitas" name="is_disabilitas" value="1"
                            class="sr-only peer" <?= $isDisActive ? 'checked' : '' ?>
                            onchange="toggleDisabilitasSection(this.checked)">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer peer-checked:bg-blue-800 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-5"></div>
                    </label>
                </div>

                <div id="disabilitasSection" class="flex flex-col gap-4 <?= $isDisActive ? '' : 'hidden' ?>">
                    <div>
                        <p class="text-xs font-semibold mb-3" style="color:#64748B;">Pilih jenis disabilitas yang diterima</p>
                        <?php
                        $jenisDisabilitas = [
                            'visual' => ['label' => 'Disabilitas Visual', 'desc' => 'Tunanetra, low vision'],
                            'hearing' => ['label' => 'Disabilitas Pendengaran', 'desc' => 'Tunarungu, hard of hearing'],
                            'physical' => ['label' => 'Disabilitas Fisik/Motorik', 'desc' => 'Keterbatasan gerak atau mobilitas'],
                            'intellectual' => ['label' => 'Disabilitas Intelektual', 'desc' => 'Tunagrahita dan sejenisnya'],
                            'mental' => ['label' => 'Disabilitas Mental', 'desc' => 'Gangguan jiwa/psikososial'],
                            'speech' => ['label' => 'Disabilitas Wicara', 'desc' => 'Tunawicara'],
                        ];
                        $selectedDisabilityTypes = $_POST['disability_types'] ?? $job['disability_types'] ?? [];
                        ?>
                        <div class="flex flex-col gap-2">
                            <?php foreach ($jenisDisabilitas as $key => $info): ?>
                                <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-100 bg-slate-50/50">
                                    <div>
                                        <p class="text-sm font-semibold" style="color:#1E293B;"><?= $info['label'] ?></p>
                                        <p class="text-xs" style="color:#64748B;"><?= $info['desc'] ?></p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer ml-4">
                                        <input type="checkbox" name="disability_types[]" value="<?= $key ?>"
                                            class="sr-only peer" <?= in_array($key, $selectedDisabilityTypes) ? 'checked' : '' ?>>
                                        <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-blue-800 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold" style="color:#475569;">Dukungan Tambahan</label>
                        <textarea name="additional_support" rows="3" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 outline-none bg-slate-50" placeholder="Contoh: ramp kursi roda..."><?= htmlspecialchars($_POST['additional_support'] ?? $job['additional_support']) ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="flex items-center justify-between px-6 py-4 rounded-2xl bg-slate-50 border border-slate-200">
            <a href="index.php" class="px-4 py-2 text-sm font-semibold rounded-lg bg-white border border-slate-300 text-slate-500">← Batal</a>
            <button type="submit" class="px-8 py-2.5 text-sm font-bold text-white bg-blue-900 rounded-xl shadow-lg transition">🚀 Simpan Perubahan</button>
        </div>

    </div>
</form>

<script>
    function toggleDisabilitasSection(isChecked) {
        document.getElementById('disabilitasSection').classList.toggle('hidden', !isChecked);
    }

    // Live Search Skills & Tag Toggle
    const skillSearch = document.getElementById('skillSearch');
    const skillTags = document.querySelectorAll('.skill-tag');

    skillSearch.addEventListener('input', function(e) {
        const term = e.target.value.toLowerCase().trim();
        skillTags.forEach(tag => {
            const name = tag.getAttribute('data-nama');
            tag.style.display = name.includes(term) ? 'flex' : 'none';
        });
    });

    document.querySelectorAll('.skill-tag input').forEach(input => {
        input.addEventListener('change', function() {
            const tag = this.parentElement;
            if (this.checked) {
                tag.style.borderColor = '#1E3A8A';
                tag.style.background = '#EFF6FF';
                tag.style.color = '#1E3A8A';
            } else {
                tag.style.borderColor = '#CBD5E1';
                tag.style.background = '#F8FAFC';
                tag.style.color = '#475569';
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>