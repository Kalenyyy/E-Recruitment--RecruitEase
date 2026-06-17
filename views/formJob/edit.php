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

// 1. Ambil data via Controller
$job = JobFormController::show($conn, $id);
if (!$job) die("Data tidak ditemukan");

// Ambil data pendukung untuk form
$posisiList = PosisiController::read($conn);
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
        <p class="text-sm" style="color: #64748B;">Perbarui data untuk lowongan </p>
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
        <div class="rounded-2xl" style="background: #FFFFFF; border: 1px solid #E2E8F0;">
            <div class="px-6 py-4 flex items-center gap-2" style="border-bottom: 1px solid #F1F5F9;">
                <span style="font-size:16px;">📋</span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Informasi Utama</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <!-- JUDUL JOB -->
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Judul Pekerjaan <span style="color:#EF4444;">*</span></label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">💼</span>
                        <input type="text" name="judul_job" required class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 outline-none focus:border-blue-500 bg-slate-50"
                            value="<?= htmlspecialchars($_POST['judul_job'] ?? $job['judul_job']) ?>">
                    </div>
                </div>

                <!-- POSISI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Posisi <span style="color:#EF4444;">*</span></label>
                    <select name="posisi_id" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 outline-none bg-slate-50">
                        <?php foreach ($posisiList as $posisi): ?>
                            <option value="<?= $posisi['id'] ?>" <?= (($_POST['posisi_id'] ?? $job['posisi_id']) == $posisi['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($posisi['nama_posisi']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- LOKASI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Lokasi <span style="color:#EF4444;">*</span></label>
                    <input type="text" name="lokasi" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 outline-none bg-slate-50"
                        value="<?= htmlspecialchars($_POST['lokasi'] ?? $job['lokasi']) ?>">
                </div>

                <!-- TIPE PEKERJAAN -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Tipe Pekerjaan <span style="color:#EF4444;">*</span></label>
                    <select name="tipe_pekerjaan" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 outline-none bg-slate-50">
                        <?php
                        $tipeList = ['Full Time' => '⏰ Full Time', 'Part Time' => '🕐 Part Time', 'Internship' => '🎓 Magang', 'Freelance' => '💻 Freelance', 'Contract' => '📄 Kontrak'];
                        $selectedTipe = $_POST['tipe_pekerjaan'] ?? $job['tipe_pekerjaan'];
                        foreach ($tipeList as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $selectedTipe === $val ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- GAJI -->
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Gaji (opsional)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">Rp</span>
                        <input type="number" name="gaji" class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-300 outline-none bg-slate-50"
                            value="<?= htmlspecialchars($_POST['gaji'] ?? $job['gaji']) ?>">
                    </div>
                </div>

                <!-- DESKRIPSI -->
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Deskripsi Pekerjaan <span style="color:#EF4444;">*</span></label>
                    <textarea name="deskripsi" rows="5" required class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 outline-none bg-slate-50"><?= htmlspecialchars($_POST['deskripsi'] ?? $job['deskripsi']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- ===== CARD 2: SKILLS (Tampilan Floating Dropdown) ===== -->
        <!-- PENTING: Jangan gunakan overflow-hidden di sini agar dropdown bisa melayang keluar card -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm relative">
            <div class="p-6">
                <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wider uppercase text-slate-400 mb-3.5 after:content-[''] after:flex-1 after:h-[0.5px] after:bg-slate-200">
                    Cari & Pilih Skill yang Dibutuhkan
                </div>

                <!-- Container Input & Dropdown -->
                <div class="relative">
                    <div class="relative">
                        <span class="absolute left-[11px] top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">🔍</span>
                        <input
                            type="text"
                            id="skillSearch"
                            class="w-full pl-[34px] pr-3 py-[10px] text-[13px] border border-slate-300 rounded-[10px] bg-slate-50 text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all placeholder:text-slate-400"
                            placeholder="Ketik nama skill..."
                            autocomplete="off">
                    </div>

                    <div id="skillDropdown"
                        class="hidden absolute left-0 right-0 top-[110%] z-[100] bg-white border border-slate-200 rounded-xl shadow-[0_10px_25px_-5px_rgba(0,0,0,0.1),0_8px_10px_-6px_rgba(0,0,0,0.1)] max-h-[250px] overflow-y-auto overflow-x-hidden">
                    </div>
                </div>

                <!-- Tempat Chips Skill Muncul -->
                <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wider uppercase text-slate-400 mb-3.5 mt-6 after:content-[''] after:flex-1 after:h-[0.5px] after:bg-slate-200">
                    Skill Terpilih
                </div>
                <div id="selectedSkills" class="flex flex-wrap gap-2 min-h-[40px] items-center">
                    <span id="emptyNote" class="text-[13px] text-slate-400 py-1">Belum ada skill dipilih</span>
                </div>

                <p class="mt-3 text-[12px] text-slate-500">Total dipilih: <span id="countLabel" class="font-bold text-slate-800">0</span></p>
            </div>
        </div>

        <!-- ===== CARD 3: OPSI KERJA ===== -->
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">
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
        <div class="rounded-2xl bg-white border border-slate-200 overflow-hidden">
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
                    <div class="flex flex-col gap-2">
                        <?php
                        $jenisDisabilitas = [
                            'visual' => ['label' => 'Visual', 'desc' => 'Tunanetra, low vision'],
                            'hearing' => ['label' => 'Pendengaran', 'desc' => 'Tunarungu, hard of hearing'],
                            'physical' => ['label' => 'Fisik/Motorik', 'desc' => 'Keterbatasan gerak'],
                            'intellectual' => ['label' => 'Intelektual', 'desc' => 'Tunagrahita'],
                            'mental' => ['label' => 'Mental', 'desc' => 'Gangguan jiwa'],
                            'speech' => ['label' => 'Wicara', 'desc' => 'Tunawicara'],
                        ];
                        $selectedDisabilityTypes = $_POST['disability_types'] ?? $job['disability_types'] ?? [];
                        foreach ($jenisDisabilitas as $key => $info): ?>
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-100 bg-slate-50/50">
                                <div>
                                    <p class="text-sm font-semibold" style="color:#1E293B;"><?= $info['label'] ?></p>
                                    <p class="text-[11px] text-slate-500"><?= $info['desc'] ?></p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="disability_types[]" value="<?= $key ?>"
                                        class="sr-only peer" <?= in_array($key, $selectedDisabilityTypes) ? 'checked' : '' ?>>
                                    <div class="w-10 h-5 bg-slate-200 rounded-full peer peer-checked:bg-blue-800 after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-5"></div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <textarea name="additional_support" rows="3" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-300 bg-slate-50 outline-none" placeholder="Dukungan tambahan..."><?= htmlspecialchars($_POST['additional_support'] ?? $job['additional_support']) ?></textarea>
                </div>
            </div>
        </div>

        <!-- FOOTER ACTIONS -->
        <div class="flex items-center justify-between px-6 py-4 rounded-2xl bg-slate-100 border border-slate-200">
            <a href="index.php" class="px-4 py-2 text-sm font-semibold rounded-lg bg-white border border-slate-300 text-slate-500">← Batal</a>
            <button type="submit" class="px-8 py-2.5 text-sm font-bold text-white bg-blue-900 rounded-xl shadow-lg transition hover:bg-blue-800">🚀 Simpan Perubahan</button>
        </div>
    </div>
</form>

<script>
    function toggleDisabilitasSection(isChecked) {
        document.getElementById('disabilitasSection').classList.toggle('hidden', !isChecked);
    }

    /* === JAVASCRIPT SKILL DINAMIS === */
    const searchInput = document.getElementById('skillSearch');
    const dropdown = document.getElementById('skillDropdown');
    const chipsWrap = document.getElementById('selectedSkills');
    const emptyNote = document.getElementById('emptyNote');
    const countLabel = document.getElementById('countLabel');
    const selected = new Map();

    function updateUI() {
        emptyNote.style.display = selected.size === 0 ? 'block' : 'none';
        countLabel.innerText = selected.size;
    }

    function addChip(id, name) {
        id = Number(id);
        if (selected.has(id)) return;
        selected.set(id, name);

        const chip = document.createElement('div');
        chip.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[13px] font-semibold bg-blue-100 text-blue-900 border border-blue-200 transition-all scale-95 animate-in fade-in duration-200';
        chip.dataset.skillId = id;
        chip.innerHTML = `
            ${name} 
            <span class="cursor-pointer text-blue-500 hover:text-blue-700 ml-1 remove-skill-btn" data-id="${id}">✕</span>
            <input type="hidden" name="skill_ids[]" value="${id}">
        `;
        chipsWrap.appendChild(chip);
        updateUI();
    }

    function removeChip(id) {
        id = Number(id);
        selected.delete(id);
        const chip = chipsWrap.querySelector(`[data-skill-id="${id}"]`);
        if (chip) chip.remove();
        updateUI();
    }

    /* INISIALISASI DATA AWAL */
    <?php if (!empty($job['skill_ids'])): ?>
        <?php foreach ($job['skill_ids'] as $idx => $sid): ?>
            addChip(<?= (int)$sid ?>, "<?= htmlspecialchars($job['skills'][$idx]) ?>");
        <?php endforeach; ?>
    <?php endif; ?>

    async function loadSkills(keyword = '') {
        try {
            const response = await fetch('<?= BASE_URL ?>public/actions/search_skill.php?q=' + encodeURIComponent(keyword));
            const data = await response.json();
            renderDropdown(data);
        } catch (error) {
            console.error(error);
        }
    }

    function renderDropdown(skills) {
        if (!skills.length) {
            dropdown.innerHTML = `<div class="p-4 text-[13px] text-slate-400 italic">Skill tidak ditemukan...</div>`;
        } else {
            let html = '';
            skills.forEach(skill => {
                const isSelected = selected.has(Number(skill.id_skill));
                if (isSelected) {
                    html += `<div class="px-4 py-3 text-[13px] flex items-center justify-between text-slate-300 bg-slate-50 cursor-not-allowed">
                                <span>${skill.nama_skill}</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-tighter">✓ Terpilih</span>
                             </div>`;
                } else {
                    html += `<div class="cs-skill-opt px-4 py-3 text-[13px] cursor-pointer flex items-center justify-between text-slate-700 border-b border-slate-50 hover:bg-blue-50 transition-colors" 
                                data-id="${skill.id_skill}" data-name="${skill.nama_skill}">
                                <span>${skill.nama_skill}</span>
                                <span class="text-blue-600 font-bold">+ Pilih</span>
                             </div>`;
                }
            });
            dropdown.innerHTML = html;
        }
        dropdown.classList.remove('hidden');
    }

    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadSkills(this.value.trim()), 300);
    });

    searchInput.addEventListener('focus', function() {
        loadSkills(this.value.trim());
    });

    dropdown.addEventListener('click', function(e) {
        const option = e.target.closest('.cs-skill-opt');
        if (!option) return;
        addChip(option.dataset.id, option.dataset.name);
        dropdown.classList.add('hidden');
        searchInput.value = '';
    });

    chipsWrap.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-skill-btn')) removeChip(e.target.dataset.id);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.add('hidden');
    });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>