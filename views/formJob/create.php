<?php
require_once __DIR__ . '/../../init.php';

// Proteksi Halaman
AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$errors = [];

// 1. Ambil data pendukung untuk form
// Pastikan parameter $conn disertakan jika controller memerlukannya
$posisiList   = PosisiController::read($conn);
$skillList    = SkillController::getAllSkill($conn);
$userData = StaffController::show($conn, $_SESSION['user_id'] ?? null);

// 2. Proses Form saat tombol Publish ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = JobFormController::store($conn, $_POST, $userData['id']);

    if ($result['status'] === true) {
        $_SESSION['success'] = "Job posting \"" . htmlspecialchars($_POST['judul_job']) . "\" berhasil disimpan sebagai draft!";
        header("Location: " . BASE_URL . "views/formJob/index.php");
        exit;
    } else {
        // Jika gagal (validasi atau sistem), ambil pesan error-nya
        $errors = $result['errors'];
    }
}

ob_start();
?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold" style="color: #1E293B;">Buat Job Posting</h1>
        <p class="text-sm" style="color: #64748B;">Lengkapi data untuk mempublikasikan lowongan baru</p>
    </div>
    <a href="<?= BASE_URL ?>views/job/index.php"
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
    <input type="hidden" name="status" value="draft">

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
                            value="<?= htmlspecialchars($_POST['judul_job'] ?? '') ?>">
                    </div>
                    <?php if (isset($errors['judul_job'])): ?>
                        <p class="text-[10px] font-bold" style="color:#EF4444;"><?= $errors['judul_job'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- POSISI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['posisi_id']) ? '#EF4444' : '#475569' ?>;">
                        Posisi <span style="color:#EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">🏷️</span>
                        <select name="posisi_id" required
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none appearance-none"
                            style="border: 1px solid <?= isset($errors['posisi_id']) ? '#EF4444' : '#CBD5E1' ?>;
                               background: <?= isset($errors['posisi_id']) ? '#FFF1F2' : '#F8FAFC' ?>; color: #1E293B;">
                            <option value="">-- Pilih Posisi --</option>
                            <?php foreach ($posisiList as $posisi): ?>
                                <option value="<?= $posisi['id'] ?>"
                                    <?= (($_POST['posisi_id'] ?? '') == $posisi['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($posisi['nama_posisi']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php if (isset($errors['posisi_id'])): ?>
                        <p class="text-[10px] font-bold" style="color:#EF4444;"><?= $errors['posisi_id'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- LOKASI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['lokasi']) ? '#EF4444' : '#475569' ?>;">
                        Lokasi <span style="color:#EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">📍</span>
                        <input type="text" name="lokasi" required placeholder="Contoh: Jakarta Selatan"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid <?= isset($errors['lokasi']) ? '#EF4444' : '#CBD5E1' ?>;
                               background: <?= isset($errors['lokasi']) ? '#FFF1F2' : '#F8FAFC' ?>; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['lokasi'] ?? '') ?>">
                    </div>
                    <?php if (isset($errors['lokasi'])): ?>
                        <p class="text-[10px] font-bold" style="color:#EF4444;"><?= $errors['lokasi'] ?></p>
                    <?php endif; ?>
                </div>

                <!-- TIPE PEKERJAAN -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        Tipe Pekerjaan <span style="color:#EF4444;">*</span>
                    </label>
                    <?php
                    $tipeList = [
                        'Full Time'  => '⏰ Full Time',
                        'Part Time'  => '🕐 Part Time',
                        'Internship' => '🎓 Magang',
                        'Freelance'  => '💻 Freelance',
                        'Contract'   => '📄 Kontrak',
                    ];
                    $selectedTipe = $_POST['tipe_pekerjaan'] ?? '';
                    ?>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">⏰</span>
                        <select name="tipe_pekerjaan" required
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none appearance-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;">
                            <option value="">-- Pilih Tipe --</option>
                            <?php foreach ($tipeList as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $selectedTipe === $val ? 'selected' : '' ?>>
                                    <?= $label ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- GAJI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: #475569;">
                        Gaji <span class="font-normal" style="color:#94A3B8;">(opsional)</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold" style="color:#94A3B8;">Rp</span>
                        <input type="number" name="gaji" placeholder="Contoh: 8000000" min="0"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                            value="<?= htmlspecialchars($_POST['gaji'] ?? '') ?>">
                    </div>
                </div>

                <!-- DESKRIPSI -->
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['deskripsi']) ? '#EF4444' : '#475569' ?>;">
                        Deskripsi Pekerjaan <span style="color:#EF4444;">*</span>
                    </label>
                    <textarea name="deskripsi" rows="5" required
                        placeholder="Deskripsikan tanggung jawab, kualifikasi, dan hal lain yang relevan..."
                        class="w-full px-3 py-2 text-sm rounded-lg outline-none resize-none"
                        style="border: 1px solid <?= isset($errors['deskripsi']) ? '#EF4444' : '#CBD5E1' ?>;
                           background: <?= isset($errors['deskripsi']) ? '#FFF1F2' : '#F8FAFC' ?>; color: #1E293B;"><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                    <?php if (isset($errors['deskripsi'])): ?>
                        <p class="text-[10px] font-bold" style="color:#EF4444;"><?= $errors['deskripsi'] ?></p>
                    <?php endif; ?>
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
                <!-- Live Search Input -->
                <div class="relative w-64">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:#94A3B8;">🔍</span>
                    <input type="text" id="skillSearch" placeholder="Cari skill..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg outline-none transition"
                        style="border: 1px solid #E2E8F0; background: #F8FAFC;">
                </div>
            </div>
            <div class="p-6">
                <p class="text-xs mb-4" style="color:#64748B;">Pilih satu atau lebih skill yang relevan. Skill yang dipilih akan tetap muncul di atas.</p>

                <!-- Container dengan Scrollbar jika data banyak -->
                <div class="overflow-y-auto pr-2" style="max-height: 200px;" id="skillWrapper">
                    <div class="flex flex-wrap gap-2" id="skillContainer">
                        <?php
                        $selectedSkills = $_POST['skill_ids'] ?? [];
                        foreach ($skillList as $skill):
                            $isSelected = in_array($skill['id_skill'], $selectedSkills);
                        ?>
                            <label class="flex items-center gap-1.5 px-3 py-1.5 rounded-full cursor-pointer text-xs font-semibold transition skill-tag"
                                data-nama="<?= strtolower(htmlspecialchars($skill['nama_skill'])) ?>"
                                style="border: 1px solid <?= $isSelected ? '#1E3A8A' : '#CBD5E1' ?>;
                                   background: <?= $isSelected ? '#EFF6FF' : '#F8FAFC' ?>;
                                   color: <?= $isSelected ? '#1E3A8A' : '#475569' ?>;">
                                <input type="checkbox" name="skill_ids[]" value="<?= $skill['id_skill'] ?>"
                                    class="hidden" <?= $isSelected ? 'checked' : '' ?>>
                                <?= htmlspecialchars($skill['nama_skill']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pesan jika skill tidak ditemukan -->
                    <div id="noSkillMsg" class="hidden py-4 text-center text-xs text-slate-400">
                        Skill tidak ditemukan...
                    </div>
                </div>

                <?php if (isset($errors['skills'])): ?>
                    <p class="text-[10px] font-bold mt-2" style="color:#EF4444;"><?= $errors['skills'] ?></p>
                <?php endif; ?>
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
                    $isChecked = isset($_POST[$fieldName]) ? (bool)$_POST[$fieldName] : false;
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
                            <div class="w-11 h-6 bg-slate-200 rounded-full peer
                        peer-checked:bg-blue-800
                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                        after:bg-white after:rounded-full after:h-5 after:w-5
                        after:transition-all peer-checked:after:translate-x-5">
                            </div>
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
                        <input type="checkbox" id="toggleDisabilitas" name="is_disabilitas" value="1"
                            class="sr-only peer" <?= (isset($_POST['is_disabilitas']) && $_POST['is_disabilitas'] == '1') ? 'checked' : '' ?>
                            onchange="toggleDisabilitasSection(this.checked)">
                        <div class="w-11 h-6 bg-slate-200 rounded-full peer
                        peer-checked:bg-blue-800
                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                        after:bg-white after:rounded-full after:h-5 after:w-5
                        after:transition-all peer-checked:after:translate-x-5">
                        </div>
                    </label>
                </div>

                <div id="disabilitasSection" class="flex flex-col gap-4 <?= (isset($_POST['is_disabilitas']) && $_POST['is_disabilitas'] == '1') ? '' : 'hidden' ?>">
                    <div>
                        <p class="text-xs font-semibold mb-3" style="color:#64748B;">Pilih jenis disabilitas yang diterima</p>
                        <?php
                        $jenisDisabilitas = [
                            'visual'         => ['label' => 'Disabilitas Visual',        'desc' => 'Tunanetra, low vision'],
                            'hearing'        => ['label' => 'Disabilitas Pendengaran',   'desc' => 'Tunarungu, hard of hearing'],
                            'physical'       => ['label' => 'Disabilitas Fisik/Motorik', 'desc' => 'Keterbatasan gerak atau mobilitas'],
                            'intellectual'   => ['label' => 'Disabilitas Intelektual',   'desc' => 'Tunagrahita dan sejenisnya'],
                            'mental'         => ['label' => 'Disabilitas Mental',        'desc' => 'Gangguan jiwa/psikososial'],
                            'speech'         => ['label' => 'Disabilitas Wicara',        'desc' => 'Tunawicara'],
                        ];
                        $selectedDisabilityTypes = $_POST['disability_types'] ?? [];
                        ?>
                        <div class="flex flex-col gap-2">
                            <?php foreach ($jenisDisabilitas as $key => $info): ?>
                                <div class="flex items-center justify-between px-4 py-3 rounded-xl transition"
                                    style="border: 1px solid #E2E8F0; background: #F8FAFC;">
                                    <div>
                                        <p class="text-sm font-semibold" style="color:#1E293B;"><?= $info['label'] ?></p>
                                        <p class="text-xs" style="color:#64748B;"><?= $info['desc'] ?></p>
                                    </div>
                                    <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                                        <input type="checkbox" name="disability_types[]" value="<?= $key ?>"
                                            class="sr-only peer"
                                            <?= in_array($key, $selectedDisabilityTypes) ? 'checked' : '' ?>>
                                        <div class="w-10 h-5 bg-slate-200 rounded-full peer
                                        peer-checked:bg-blue-800
                                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                                        after:bg-white after:rounded-full after:h-4 after:w-4
                                        after:transition-all peer-checked:after:translate-x-5">
                                        </div>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold" style="color:#475569;">
                            Dukungan Tambahan <span class="font-normal" style="color:#94A3B8;">(opsional)</span>
                        </label>
                        <textarea name="additional_support" rows="3"
                            placeholder="Contoh: tersedia ramp kursi roda, interpreter bahasa isyarat..."
                            class="w-full px-3 py-2 text-sm rounded-lg outline-none resize-none"
                            style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"><?= htmlspecialchars($_POST['additional_support'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="flex items-center justify-between px-6 py-4 rounded-2xl"
            style="background: #F8FAFC; border: 1px solid #E2E8F0;">
            <a href="<?= BASE_URL ?>views/job/index.php"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg"
                style="background: #fff; color: #64748B; border: 1px solid #CBD5E1;">
                ← Batal
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-8 py-2.5 text-sm font-bold rounded-xl text-white transition shadow-lg shadow-blue-900/20"
                style="background: #1E3A8A; border: none;">
                🚀 Simpan
            </button>
        </div>

    </div>
</form>

<script>
    function toggleDisabilitasSection(isChecked) {
        const section = document.getElementById('disabilitasSection');
        section.classList.toggle('hidden', !isChecked);
    }

    document.querySelectorAll('.skill-tag').forEach(tag => {
        tag.addEventListener('click', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            const isSelected = checkbox.checked;
            this.style.borderColor = isSelected ? '#1E3A8A' : '#CBD5E1';
            this.style.background = isSelected ? '#EFF6FF' : '#F8FAFC';
            this.style.color = isSelected ? '#1E3A8A' : '#475569';
        });
    });

    // ── Fitur Live Search Skill ──────────────────────────────────
    const skillSearch = document.getElementById('skillSearch');
    const skillTags = document.querySelectorAll('.skill-tag');
    const noSkillMsg = document.getElementById('noSkillMsg');

    skillSearch.addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        let hasResults = false;

        skillTags.forEach(tag => {
            const skillName = tag.getAttribute('data-nama');
            const isChecked = tag.querySelector('input').checked;

            // Jika sedang mencari, tampilkan yang cocok
            // Jika input kosong, tampilkan semua
            if (skillName.includes(searchTerm)) {
                tag.style.display = 'flex';
                hasResults = true;
            } else {
                // Skill yang sudah dicentang tetap tampilkan saja atau sembunyikan? 
                // Biasanya lebih baik sembunyi agar pencarian akurat
                tag.style.display = 'none';
            }
        });

        // Tampilkan pesan jika tidak ada yang cocok
        noSkillMsg.classList.toggle('hidden', hasResults);
    });

    // Perbarui style tag saat diklik (Event Listener ini sudah ada di kode sebelumnya, pastikan tetap ada)
    document.querySelectorAll('.skill-tag').forEach(tag => {
        tag.addEventListener('change', function() {
            const checkbox = this.querySelector('input[type="checkbox"]');
            const isSelected = checkbox.checked;
            this.style.borderColor = isSelected ? '#1E3A8A' : '#CBD5E1';
            this.style.background = isSelected ? '#EFF6FF' : '#F8FAFC';
            this.style.color = isSelected ? '#1E3A8A' : '#475569';
        });
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>