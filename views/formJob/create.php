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
$userData = StaffController::getStaffByUserId($conn, $_SESSION['user_id'] ?? null);

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
    <a href="<?= BASE_URL ?>views/formJob/index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl transition hover:bg-slate-100"
        style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Kembali
    </a>
</div>

<?php if (isset($errors['umum'])): ?>
    <div class="mb-4 p-4 rounded-xl border flex items-center gap-3"
        style="background: #FEF2F2; border-color: #FECACA; color: #991B1B;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <span class="text-sm font-semibold"><?= $errors['umum'] ?></span>
    </div>
<?php endif; ?>

<form action="" method="POST" id="jobForm">
    <input type="hidden" name="status" value="draft">

    <div class="flex flex-col gap-5">

        <!-- ===== CARD 1: INFORMASI UTAMA ===== -->
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(15,23,42,0.04);">
            <div class="px-6 py-4 flex items-center gap-2.5" style="border-bottom: 1px solid #F1F5F9;">
                <span class="inline-flex items-center justify-center" style="width:32px;height:32px;border-radius:9px;background:#EFF6FF;color:#1E3A8A;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Informasi Utama</h2>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">

                <!-- JUDUL JOB -->
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['judul_job']) ? '#EF4444' : '#475569' ?>;">
                        Judul Pekerjaan <span style="color:#EF4444;">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </span>
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
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20.59 13.41 11 23H4v-7L13.41 5.59a2 2 0 0 1 2.82 0l4.36 4.36a2 2 0 0 1 0 2.82Z"></path>
                                <circle cx="7.5" cy="15.5" r=".5" fill="currentColor"></circle>
                            </svg>
                        </span>
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
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path>
                                <circle cx="12" cy="10" r="3"></circle>
                            </svg>
                        </span>
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
                        'Full Time'  => 'Full Time',
                        'Part Time'  => 'Part Time',
                        'Internship' => 'Magang',
                        'Freelance'  => 'Freelance',
                        'Contract'   => 'Kontrak',
                    ];
                    $selectedTipe = $_POST['tipe_pekerjaan'] ?? '';
                    ?>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2" style="color:#94A3B8;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </span>
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
                <div class="col-span-2 flex flex-col gap-1">
                    <label class="text-xs font-semibold" style="color: <?= isset($errors['gaji']) ? '#EF4444' : '#475569' ?>;">
                        Rentang Gaji <span class="font-normal" style="color:#94A3B8;">(opsional)</span>
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <!-- Gaji Minimal -->
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold" style="color:#94A3B8;">Rp</span>
                            <input type="text" name="gaji_min" id="gaji_min" placeholder="Min (Misal: 5.000.000)"
                                class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none gaji-input"
                                style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                                value="<?= htmlspecialchars($_POST['gaji_min'] ?? '') ?>">
                        </div>
                        <!-- Gaji Maksimal -->
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-bold" style="color:#94A3B8;">Rp</span>
                            <input type="text" name="gaji_max" id="gaji_max" placeholder="Max (Misal: 8.000.000)"
                                class="w-full pl-9 pr-3 py-2 text-sm rounded-lg outline-none gaji-input"
                                style="border: 1px solid #CBD5E1; background: #F8FAFC; color: #1E293B;"
                                value="<?= htmlspecialchars($_POST['gaji_max'] ?? '') ?>">
                        </div>
                    </div>
                    <?php if (isset($errors['gaji'])): ?>
                        <p class="text-[10px] font-bold" style="color:#EF4444;"><?= $errors['gaji'] ?></p>
                    <?php endif; ?>
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
        <div class="bg-white border border-slate-200 rounded-[16px] overflow-hidden shadow-sm mb-6">
            <div class="px-6 py-4 flex items-center gap-2.5" style="border-bottom: 1px solid #F1F5F9;">
                <span class="inline-flex items-center justify-center" style="width:32px;height:32px;border-radius:9px;background:#EFF6FF;color:#1E3A8A;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 14 9-5-9-5-9 5 9 5Z"></path>
                        <path d="m22 9-10 5L2 9"></path>
                        <path d="M6 11.5v4.5a6 3 0 0 0 12 0v-4.5"></path>
                    </svg>
                </span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Skills</h2>
            </div>
            <div class="p-6">
                <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wider uppercase text-slate-400 mb-3.5 after:content-[''] after:flex-1 after:h-[0.5px] after:bg-slate-200">
                    Cari &amp; Pilih Skill yang Dibutuhkan
                </div>

                <!-- Input Pencarian -->
                <div class="relative mb-2.5">
                    <span class="absolute left-[11px] top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="skillSearch"
                        class="w-full pl-[34px] pr-3 py-[9px] text-[13px] border border-slate-300 rounded-[10px] bg-slate-50 text-slate-800 outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all placeholder:text-slate-400"
                        placeholder="Ketik nama skill (misal: PHP, Project Management)..."
                        autocomplete="off">
                </div>

                <!-- Dropdown Hasil Pencarian (Hidden by default) -->
                <div id="skillDropdown" class="hidden border border-slate-200 rounded-[10px] bg-white overflow-hidden mb-3.5 shadow-lg max-h-[300px] overflow-y-auto"></div>

                <!-- Tempat Chips Skill Muncul -->
                <div class="flex items-center gap-2 text-[11px] font-semibold tracking-wider uppercase text-slate-400 mb-3.5 mt-5 after:content-[''] after:flex-1 after:h-[0.5px] after:bg-slate-200">
                    Skill Terpilih
                </div>
                <div id="selectedSkills" class="flex flex-wrap gap-2 min-h-[36px]">
                    <span id="emptyNote" class="text-[13px] text-slate-400 py-1">Belum ada skill dipilih</span>
                </div>

                <!-- Counter Kecil -->
                <p class="mt-3 text-[12px] text-slate-500">Total dipilih: <span id="countLabel" class="font-bold text-slate-800">0</span></p>
            </div>
        </div>

        <!-- ===== CARD 3: OPSI KERJA ===== -->
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(15,23,42,0.04);">
            <div class="px-6 py-4 flex items-center gap-2.5" style="border-bottom: 1px solid #F1F5F9;">
                <span class="inline-flex items-center justify-center" style="width:32px;height:32px;border-radius:9px;background:#EFF6FF;color:#1E3A8A;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"></path>
                    </svg>
                </span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Opsi Pekerjaan</h2>
            </div>
            <div class="p-6 flex flex-col gap-4">
                <?php
                $toggleOptions = [
                    'is_remote_work'      => [
                        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>',
                        'label' => 'Remote Work',
                        'desc' => 'Pekerjaan bisa dilakukan dari rumah',
                    ],
                    'is_remote_interview' => [
                        'icon' => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>',
                        'label' => 'Remote Interview',
                        'desc' => 'Proses wawancara dilakukan secara online',
                    ],
                ];
                foreach ($toggleOptions as $fieldName => $opt):
                    $isChecked = isset($_POST[$fieldName]) ? (bool)$_POST[$fieldName] : false;
                ?>
                    <div class="flex items-center justify-between py-1">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center justify-center" style="width:38px;height:38px;border-radius:10px;background:#F1F5F9;color:#1E3A8A;"><?= $opt['icon'] ?></span>
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
        <div class="rounded-2xl overflow-hidden" style="background: #FFFFFF; border: 1px solid #E2E8F0; box-shadow: 0 1px 2px rgba(15,23,42,0.04);">
            <div class="px-6 py-4 flex items-center gap-2.5" style="border-bottom: 1px solid #F1F5F9;">
                <span class="inline-flex items-center justify-center" style="width:32px;height:32px;border-radius:9px;background:#EFF6FF;color:#1E3A8A;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="5" r="1.5"></circle>
                        <path d="M9 9h6l-1 4 4 2v6"></path>
                        <path d="M7 21h10"></path>
                        <path d="M9 13a4 4 0 0 0 4 6"></path>
                    </svg>
                </span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Aksesibilitas &amp; Disabilitas</h2>
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
                            'fisik' => [
                                'label' => 'Disabilitas Fisik',
                                'desc'  => 'Terganggunya fungsi gerak, antara lain amputasi, lumpuh layu, paraplegi, atau akibat stroke.'
                            ],
                            'netra' => [
                                'label' => 'Disabilitas Sensorik Netra',
                                'desc'  => 'Gangguan pada daya lihat, baik sebagian (low vision) maupun total (blind).'
                            ],
                            'rungu' => [
                                'label' => 'Disabilitas Sensorik Rungu/Wicara',
                                'desc'  => 'Gangguan pada fungsi pendengaran dan/atau fungsi bicara atau artikulasi suara.'
                            ],
                            'intelektual' => [
                                'label' => 'Disabilitas Intelektual',
                                'desc'  => 'Hambatan fungsi kognitif disertai hambatan perilaku adaptif (Down Syndrome, lambat belajar).'
                            ],
                            'mental' => [
                                'label' => 'Disabilitas Mental',
                                'desc'  => 'Terganggunya fungsi pikir, emosi, dan perilaku (Skizofrenia, Bipolar, atau Autisme).'
                            ],
                            'ganda' => [
                                'label' => 'Disabilitas Ganda/Multipel',
                                'desc'  => 'Memiliki dua atau lebih jenis disabilitas dalam satu waktu (misal: Fisik sekaligus Netra).'
                            ],
                            'lainnya' => [
                                'label' => 'Lainnya',
                                'desc'  => 'Jenis hambatan lainnya yang memerlukan akomodasi khusus dalam bekerja.'
                            ],
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
            <a href="<?= BASE_URL ?>views/formjob/index.php"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold rounded-lg transition hover:bg-slate-50"
                style="background: #fff; color: #64748B; border: 1px solid #CBD5E1;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                Batal
            </a>
            <button type="submit"
                class="inline-flex items-center gap-2 px-8 py-2.5 text-sm font-bold rounded-xl text-white transition shadow-lg shadow-blue-900/20 hover:opacity-95"
                style="background: #1E3A8A; border: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Simpan
            </button>
        </div>

    </div>
</form>

<script>
    /* === JAVASCRIPT DISABILITAS (KODE LAMA TETAP ADA) === */
    function toggleDisabilitasSection(isChecked) {
        const section = document.getElementById('disabilitasSection');
        if (isChecked) {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    }

    /* === JAVASCRIPT SKILL DINAMIS (KODE BARU) === */
    const searchInput = document.getElementById('skillSearch');
    const dropdown = document.getElementById('skillDropdown');
    const chipsWrap = document.getElementById('selectedSkills');
    const emptyNote = document.getElementById('emptyNote');
    const selected = new Map();

    function updateUI() {
        emptyNote.style.display = selected.size === 0 ? '' : 'none';
    }

    function addChip(id, name) {
        id = Number(id);
        if (selected.has(id)) return;
        selected.set(id, name);

        const chip = document.createElement('div');
        chip.className = 'inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-[13px] font-semibold bg-blue-100 text-blue-900 border border-blue-200';
        chip.dataset.skillId = id;
        chip.innerHTML = `${name} <span class="cursor-pointer text-blue-500 hover:text-blue-700 ml-1 remove-skill-btn" data-id="${id}">✕</span><input type="hidden" name="skill_ids[]" value="${id}">`;

        chipsWrap.appendChild(chip);
        updateUI();
    }

    function removeChip(id) {
        id = Number(id);
        selected.delete(id);
        const chip = chipsWrap.querySelector(`[data-skill-id="${id}"]`);
        if (chip) chip.remove();
        updateUI();
        loadSkills(searchInput.value.trim()); // Refresh list
    }

    function renderDropdown(skills) {
        if (!skills.length) {
            dropdown.innerHTML = `<div class="p-3.5 text-[13px] text-slate-400">Skill tidak ditemukan</div>`;
            dropdown.classList.remove('hidden');
            return;
        }

        let html = '';
        skills.forEach(skill => {
            const isSelected = selected.has(Number(skill.id_skill));
            if (isSelected) {
                html += `<div class="px-3.5 py-2.5 text-[13px] flex items-center justify-between text-slate-400 bg-white border-b border-slate-50 cursor-not-allowed"><span>${skill.nama_skill}</span><span class="text-[11px] font-semibold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full">Terpilih</span></div>`;
            } else {
                html += `<div class="cs-skill-opt px-3.5 py-2.5 text-[13px] cursor-pointer flex items-center justify-between text-slate-800 border-b border-slate-50 hover:bg-blue-50 transition-colors" data-id="${skill.id_skill}" data-name="${skill.nama_skill}"><span>${skill.nama_skill}</span><span class="text-[11px] font-semibold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full">Tambah</span></div>`;
            }
        });
        dropdown.innerHTML = html;
        dropdown.classList.remove('hidden');
    }

    async function loadSkills(keyword = '') {
        try {
            const response = await fetch('<?= BASE_URL ?>public/actions/search_skill.php?q=' + encodeURIComponent(keyword));
            const data = await response.json();
            renderDropdown(data);
        } catch (error) {
            console.error(error);
        }
    }

    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadSkills(this.value.trim()), 300);
    });

    searchInput.addEventListener('focus', () => loadSkills(searchInput.value.trim()));

    dropdown.addEventListener('click', function(e) {
        const option = e.target.closest('.cs-skill-opt');
        if (!option) return;
        addChip(option.dataset.id, option.dataset.name);
        loadSkills(searchInput.value.trim()); // Biar dropdown tetap update statusnya
    });

    chipsWrap.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-skill-btn')) removeChip(e.target.dataset.id);
    });

    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.add('hidden');
    });

    /* === JAVASCRIPT FORMAT RIBUAN & LIMIT 1M === */
    const gajiInputs = document.querySelectorAll('.gaji-input');

    gajiInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // 1. Ambil angka saja (buang titik dan huruf jika ada)
            let rawValue = this.value.replace(/\D/g, '');

            // 2. KUNCI: Jika lebih dari 9 digit, potong paksa jadi 9 digit saja
            // Ini membuat user tidak bisa mengetik angka ke-10 (tidak bisa menyentuh 1 Miliar)
            if (rawValue.length > 9) {
                rawValue = rawValue.substring(0, 9);
            }

            // 3. Format kembali dengan titik ribuan
            if (rawValue !== "") {
                this.value = formatRupiah(rawValue);
            } else {
                this.value = "";
            }
        });
    });

    function formatRupiah(angka) {
        let number_string = angka.toString(),
            sisa = number_string.length % 3,
            rupiah = number_string.substr(0, sisa),
            ribuan = number_string.substr(sisa).match(/\d{3}/g);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }
        return rupiah;
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>