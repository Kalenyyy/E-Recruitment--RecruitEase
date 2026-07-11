<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();

$errors = [];
$candidateId = $_GET['candidate_id'] ?? null;

if (!$candidateId) {
    die("Candidate ID tidak ditemukan");
}

// Ambil skill yang sudah dimiliki kandidat
$existingSkills = CandidateSkill::getByCandidateId($conn, $candidateId);
$existingSkillIds = array_column($existingSkills, 'skill_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = CandidateSkillController::store($conn, $_POST);

    if ($result['status']) {
        $_SESSION['success'] = "Skill kandidat berhasil ditambahkan";
        header("Location: " . BASE_URL . "views/candidate/profile.php?id=" . $candidateId . "&status=success_add#pengalaman-kerja");
        exit;
    }

    $errors = $result['errors'];
}

ob_start();
?>

<!-- Header -->
<div class="flex items-start justify-between mb-6">
    <div>
        <h1 class="text-[20px] font-semibold text-slate-800">Tambah Skill Kandidat</h1>
        <p class="text-[13px] text-slate-500 mt-0.5">Pilih skill yang dimiliki kandidat dari daftar berikut</p>
    </div>
    <a href="<?= BASE_URL ?>views/candidate/profile.php?id=<?= $candidateId ?>" class="inline-flex items-center gap-1.5 text-[13px] font-medium px-3.5 py-1.5 rounded-[10px] bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-colors">
        ← Kembali
    </a>
</div>

<!-- Card Container -->
<div class="bg-white border border-slate-200 rounded-[16px] overflow-hidden shadow-sm">
    <form method="POST" id="skillForm">
        <input type="hidden" name="candidate_id" value="<?= $candidateId ?>">

        <div class="p-6">
            <!-- Search Section -->
            <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wider uppercase text-slate-400 mb-3.5 after:content-[''] after:flex-1 after:h-[0.5px] after:bg-slate-200">
                Cari &amp; Pilih Skill
            </div>

            <div class="relative mb-2.5">
                <span class="absolute left-[11px] top-1/2 -translate-y-1/2 text-[15px] text-slate-400 pointer-events-none">🔍</span>
                <input
                    type="text"
                    id="skillSearch"
                    class="w-full pl-[34px] pr-3 py-[9px] text-[13px] border border-slate-300 rounded-[10px] bg-slate-50 text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all placeholder:text-slate-400"
                    placeholder="Ketik nama skill, misal: JavaScript, PHP..."
                    autocomplete="off">
            </div>

            <!-- Dropdown Results -->
            <div id="skillDropdown" class="hidden border border-slate-200 rounded-[10px] bg-white overflow-hidden mb-3.5 shadow-lg max-h-[300px] overflow-y-auto"></div>

            <?php if (isset($errors['skills'])): ?>
                <p class="text-[12px] text-red-500 mt-1.5"><?= $errors['skills'] ?></p>
            <?php endif; ?>

            <!-- Selected Chips -->
            <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wider uppercase text-slate-400 mb-3.5 mt-5 after:content-[''] after:flex-1 after:h-[0.5px] after:bg-slate-200">
                Skill Terpilih
            </div>
            <div id="selectedSkills" class="flex flex-wrap gap-2 min-h-[36px]">
                <span id="emptyNote" class="text-[13px] text-slate-400 py-1">Belum ada skill dipilih</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between px-6 py-3.5 border-t border-slate-200 bg-slate-50">
            <span class="text-[13px] text-slate-500">Dipilih: <strong id="countLabel" class="text-slate-800 font-bold">0</strong> skill</span>
            <button type="submit" class="inline-flex items-center gap-1.5 px-5 py-2 rounded-[10px] bg-[#1E3A8A] text-white text-[13px] font-semibold hover:opacity-90 transition-opacity disabled:opacity-40 disabled:cursor-not-allowed border-none cursor-pointer" id="saveBtn" disabled>
                💾 Simpan Skill
            </button>
        </div>
    </form>
</div>

<script>
    const alreadyIds = new Set(
        <?= json_encode(array_map('intval', $existingSkillIds)) ?>
    );

    const searchInput = document.getElementById('skillSearch');
    const dropdown = document.getElementById('skillDropdown');
    const chipsWrap = document.getElementById('selectedSkills');
    const countLabel = document.getElementById('countLabel');
    const saveBtn = document.getElementById('saveBtn');
    const emptyNote = document.getElementById('emptyNote');

    const selected = new Map();

    function updateUI() {
        const total = selected.size;
        countLabel.textContent = total;
        saveBtn.disabled = total === 0;
        emptyNote.style.display = total === 0 ? '' : 'none';
    }

    function addChip(id, name) {
        id = Number(id);
        if (selected.has(id) || alreadyIds.has(id)) return;

        selected.set(id, name);

        const chip = document.createElement('div');
        chip.className = 'cs-chip inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[13px] font-semibold bg-blue-100 text-blue-900';
        chip.dataset.skillId = id;

        chip.innerHTML = `
            ${name}
            <span class="cs-chip-remove cursor-pointer text-[13px] text-blue-500 opacity-70 hover:opacity-100" data-id="${id}">✕</span>
            <input type="hidden" name="skills[]" value="${id}">
        `;

        chipsWrap.appendChild(chip);
        updateUI();
    }

    function removeChip(id) {
        id = Number(id);
        selected.delete(id);
        const chip = chipsWrap.querySelector('[data-skill-id="' + id + '"]');
        if (chip) chip.remove();
        updateUI();
    }

    function renderDropdown(skills) {
        if (!skills.length) {
            dropdown.innerHTML = `<div class="p-3.5 text-[13px] text-slate-400">Skill tidak ditemukan</div>`;
            dropdown.style.display = 'block';
            return;
        }

        const keyword = searchInput.value.trim();
        const groupAlready = skills.filter(s => alreadyIds.has(Number(s.id_skill)));
        const groupSelected = skills.filter(s => !alreadyIds.has(Number(s.id_skill)) && selected.has(Number(s.id_skill)));
        const groupNew = skills.filter(s => !alreadyIds.has(Number(s.id_skill)) && !selected.has(Number(s.id_skill)));

        let html = '';
        const renderDivider = (text) => `<div class="text-[11px] font-semibold tracking-wider uppercase text-slate-400 px-3.5 py-1.5 bg-slate-50 border-b border-slate-200">${text}</div>`;

        if (groupNew.length) {
            // Berikan label berbeda jika sedang mencari atau hanya rekomendasi awal
            const label = keyword.length > 0 ? 'Tersedia' : 'Rekomendasi Skill';
            html += renderDivider(label);
            groupNew.forEach(skill => {
                html += `
                <div class="cs-skill-opt px-3.5 py-2.5 text-[13px] cursor-pointer flex items-center justify-between text-slate-800 border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors" data-id="${skill.id_skill}" data-name="${skill.nama_skill}">
                    <span>${skill.nama_skill}</span>
                    <span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">+ Tambah</span>
                </div>`;
            });
        }

        if (groupSelected.length) {
            html += renderDivider('Sudah Dipilih');
            groupSelected.forEach(skill => {
                html += `
                <div class="cs-already px-3.5 py-2.5 text-[13px] flex items-center justify-between text-slate-400 bg-white border-b border-slate-50 last:border-0 cursor-not-allowed">
                    <span>${skill.nama_skill}</span>
                    <span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">✓ Dipilih</span>
                </div>`;
            });
        }

        if (groupAlready.length) {
            html += renderDivider('Sudah Dimiliki Kandidat');
            groupAlready.forEach(skill => {
                html += `
                <div class="cs-already px-3.5 py-2.5 text-[13px] flex items-center justify-between text-slate-400 bg-white border-b border-slate-50 last:border-0 cursor-not-allowed">
                    <span>${skill.nama_skill}</span>
                    <span class="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full">✓ Sudah Ada</span>
                </div>`;
            });
        }

        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');
        dropdown.style.display = 'block';
    }

    // Fungsi fetch data dari API
    async function loadSkills(keyword = '') {
        try {
            const response = await fetch('<?= BASE_URL ?>public/actions/search_skill.php?q=' + encodeURIComponent(keyword));
            const data = await response.json();
            renderDropdown(data);
        } catch (error) {
            dropdown.innerHTML = `<div class="p-3.5 text-[13px] text-slate-400">Gagal mengambil data</div>`;
            dropdown.style.display = 'block';
        }
    }

    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const keyword = this.value.trim();

        // Tetap mencari meskipun keyword pendek (atau kosong) agar daftar rekomendasi muncul kembali
        debounceTimer = setTimeout(() => {
            loadSkills(keyword);
        }, 250);
    });

    // BARU: Saat input diklik/fokus, langsung ambil data (rekomendasi)
    searchInput.addEventListener('focus', function() {
        const keyword = this.value.trim();
        loadSkills(keyword);
    });

    dropdown.addEventListener('click', function(e) {
        const option = e.target.closest('.cs-skill-opt');
        if (!option || option.classList.contains('cs-already')) return;

        addChip(option.dataset.id, option.dataset.name);
        searchInput.value = '';
        // Setelah pilih, tampilkan lagi daftar rekomendasi
        loadSkills('');
        searchInput.focus();
    });

    chipsWrap.addEventListener('click', function(e) {
        if (!e.target.classList.contains('cs-chip-remove')) return;
        removeChip(e.target.dataset.id);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    updateUI();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>