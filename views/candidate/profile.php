<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isCandidate() or die("Access denied");

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: index.php");
    exit;
}

$profile = ProfileController::getCandidateProfile($id);

if (!$profile)
    die("Data tidak ditemukan");

// ================= DATA READY =================
$candidate = $profile['candidate'];
$selectedDisabilityTypes = $profile['disabilities'] ?? [];
$pendidikanList = Pendidikan::getByCandidateId(
    $conn,
    $candidate['id']
); // $pengalamanList = $profile['experience'] ?? [];
$pengalamanList = PengalamanKerja::getByCandidateId($conn, $id);
$skillList = CandidateSkill::getByCandidateId($conn, $id);
$sertifikasiList = SertifikasiController::getByCandidateId($conn, $candidate['id']);
// ================= UI HELPERS (boleh di view) =================
$nama = $candidate['nama_lengkap'];
$parts = explode(' ', trim($nama));
$initials = strtoupper(substr($parts[0] ?? '', 0, 1));
if (isset($parts[1]))
    $initials .= strtoupper(substr($parts[1], 0, 1));

$fotoPathLocal = __DIR__ . "/../../public/uploads/candidate/" . $candidate['foto'];
$hasFoto = !empty($candidate['foto']) && file_exists($fotoPathLocal);

$isDisabled = !empty($candidate['is_disabled']);

$jenisDisabilitas = [
    'fisik' => ['label' => 'Disabilitas Fisik', 'desc' => 'Gangguan fungsi gerak, anggota tubuh, atau mobilitas'],
    'netra' => ['label' => 'Disabilitas Netra', 'desc' => 'Gangguan penglihatan sebagian atau total'],
    'rungu' => ['label' => 'Disabilitas Rungu/Wicara', 'desc' => 'Gangguan pendengaran atau kemampuan berbicara'],
    'intelektual' => ['label' => 'Disabilitas Intelektual', 'desc' => 'Hambatan dalam fungsi intelektual dan adaptif'],
    'mental' => ['label' => 'Disabilitas Mental', 'desc' => 'Kondisi kesehatan mental yang memengaruhi aktivitas'],
    'sensorik' => ['label' => 'Disabilitas Sensorik', 'desc' => 'Gangguan pada indra selain penglihatan dan pendengaran'],
    'lainnya' => ['label' => 'Lainnya', 'desc' => 'Jenis disabilitas lain yang tidak tercantum di atas'],
];

ob_start();
?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'profile_incomplete'): ?>
    <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3 text-amber-800">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>
        <div class="text-sm font-semibold">
            Mohon lengkapi Alamat, Tanggal Lahir, Jenis Kelamin, Foto, dan CV sebelum melamar pekerjaan.
        </div>
    </div>
<?php endif; ?>
<!-- BREADCRUMB & TITLE -->
<div class="flex items-center justify-between mb-8">
    <div>
        <h1 class="text-2xl font-bold" style="color:#1E293B;">User Profile</h1>
        <div class="flex items-center gap-2 text-sm mt-1" style="color:#64748B;">
            <span>Home</span> <span style="color:#CBD5E1;">/</span> <span style="color:#1E3A8A;font-weight:600;">User Profile</span>
        </div>
    </div>
</div>

<div class="flex flex-col gap-6 mb-10">

    <!-- ========== MY PROFILE (Informasi Pribadi & Alamat) ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <!-- Header Kartu dengan Tombol Simpan -->
        <div class="px-8 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Profil & Alamat</h2>
            <button type="submit" form="mainForm"
                class="flex items-center gap-2 px-5 py-2 text-xs font-bold rounded-lg bg-blue-800 text-white hover:bg-blue-900 transition shadow-sm">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Simpan Profil
            </button>
        </div>

        <form id="mainForm" action="<?= BASE_URL ?>public/actions/update_profile_candidate.php" method="POST" enctype="multipart/form-data" class="p-8">
            <input type="hidden" name="id" value="<?= $candidate['id'] ?>">
            <!-- Header Profile (Foto & Nama) -->
            <div class="flex flex-col md:flex-row items-center gap-6 mb-8 pb-2">
                <div class="relative group">
                    <div class="w-28 h-28 rounded-full overflow-hidden border-4 border-slate-50 shadow-sm">
                        <?php if ($hasFoto): ?>
                            <img id="fotoPreview" src="<?= BASE_URL ?>public/uploads/candidate/<?= $candidate['foto'] ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="fotoInitials" class="w-full h-full flex items-center justify-center text-2xl font-bold"
                                style="background:#DBEAFE;color:#1E3A8A;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition text-white text-xs font-semibold">
                        Ganti Foto
                        <input type="file" name="foto" class="hidden" accept="image/*" onchange="previewFoto(this)">
                    </label>
                </div>
                <div class="text-center md:text-left">
                    <h3 class="text-xl font-bold text-slate-800"><?= htmlspecialchars($nama) ?></h3>
                    <p class="text-sm text-slate-500 mt-0.5">Kandidat Terverifikasi</p>
                </div>
            </div>

            <!-- Grid Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <!-- Nama -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($candidate['nama_lengkap']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Email -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($candidate['email']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- No HP -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">No. HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="<?= htmlspecialchars($candidate['no_hp']) ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Tanggal Lahir -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir"
                        value="<?= htmlspecialchars($candidate['tanggal_lahir'] ?? '') ?>"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent transition-colors">
                </div>

                <!-- Jenis Kelamin -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Jenis Kelamin</label>
                    <select name="jenis_kelamin"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent cursor-pointer transition-colors">
                        <option value="">-- Pilih --</option>
                        <option value="L" <?= ($candidate['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($candidate['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <!-- Alamat (Sekarang masuk sini dan dibuat Full Width) -->
                <div class="flex flex-col gap-1.5 md:col-span-2">
                    <label class="text-xs font-semibold uppercase tracking-wide text-slate-400">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent resize-none transition-colors"><?= htmlspecialchars($candidate['alamat'] ?? '') ?></textarea>
                </div>
            </div>
        </form>
    </div>

    <!-- ========== CV / RESUME ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">CV / Resume</h2>
            <label id="cvUploadLabel"
                class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 cursor-pointer transition">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path>
                </svg>
                Upload CV
                <input type="file" id="cvFileInput" class="hidden" accept=".pdf,.doc,.docx">
            </label>
        </div>
        <div class="p-6 flex items-center justify-between">
            <div id="cvInfo" class="flex items-center gap-3">
                <?php if (!empty($candidate['cv_file'])): ?>
                    <span class="inline-flex items-center justify-center flex-shrink-0" style="width:40px;height:40px;border-radius:10px;background:#EFF6FF;color:#1E3A8A;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </span>
                    <div>
                        <p id="cvFileName" class="text-sm font-semibold text-slate-800">
                            <?= htmlspecialchars($candidate['cv_file']) ?>
                        </p>
                        <p class="text-xs text-slate-500 mt-0.5">File CV tersedia</p>
                    </div>
                <?php else: ?>
                    <p id="cvEmpty" class="text-sm text-slate-400 italic">Belum ada CV yang diupload.</p>
                <?php endif; ?>
            </div>
            <div id="cvActions" class="flex gap-2">
                <?php if (!empty($candidate['cv_file'])): ?>
                    <a id="cvPreviewBtn" href="<?= BASE_URL ?>public/uploads/cv/<?= $candidate['cv_file'] ?>"
                        target="_blank"
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path>
                            <circle cx="12" cy="12" r="3"></circle>
                        </svg>
                        Preview
                    </a>
                    <a id="cvDownloadBtn" href="<?= BASE_URL ?>public/uploads/cv/<?= $candidate['cv_file'] ?>" download
                        class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Unduh
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ========== DISABILITAS ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Informasi Disabilitas</h2>
            <button type="button" onclick="saveDisabilitas()"
                class="flex items-center gap-2 px-4 py-2 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"></path>
                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                    <polyline points="7 3 7 8 15 8"></polyline>
                </svg>
                Simpan
            </button>
        </div>
        <div class="p-6 flex flex-col gap-5">

            <!-- Toggle utama -->
            <div class="flex items-center justify-between py-1">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Saya memiliki disabilitas</p>
                    <p class="text-xs text-slate-500 mt-0.5">Aktifkan jika Anda memiliki kondisi disabilitas</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="toggleDisabilitas" class="sr-only peer"
                        <?= $isDisabled ? 'checked' : '' ?>
                        onchange="toggleDisabilitasSection(this.checked)">
                    <div class="w-11 h-6 bg-slate-200 rounded-full peer
                        peer-checked:bg-blue-800
                        after:content-[''] after:absolute after:top-0.5 after:left-0.5
                        after:bg-white after:rounded-full after:h-5 after:w-5
                        after:transition-all peer-checked:after:translate-x-5">
                    </div>
                </label>
            </div>

            <!-- Detail disabilitas (muncul kalau toggle ON) -->
            <div id="disabilitasSection" class="flex flex-col gap-4 <?= $isDisabled ? '' : 'hidden' ?>">

                <!-- Deskripsi tambahan -->
                <div class="flex flex-col gap-1.5">
                    <label class="text-xs font-semibold uppercase tracking-wide" style="color:#94A3B8;">Deskripsi Tambahan <span
                            class="normal-case font-normal" style="color:#CBD5E1;">(opsional)</span></label>
                    <textarea id="disabilityDescription" rows="2"
                        class="px-0 py-1.5 text-sm font-semibold text-slate-800 border-b-2 border-slate-200 focus:border-blue-800 outline-none bg-transparent resize-none transition-colors"><?= htmlspecialchars($candidate['disability_description'] ?? '') ?></textarea>
                </div>

                <!-- Jenis-jenis disabilitas -->
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide mb-3" style="color:#94A3B8;">Pilih Jenis Disabilitas</p>
                    <div class="flex flex-col gap-2">
                        <?php foreach ($jenisDisabilitas as $key => $info): ?>
                            <div
                                class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100 transition">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800"><?= $info['label'] ?></p>
                                    <p class="text-xs text-slate-500 mt-0.5"><?= $info['desc'] ?></p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                                    <input type="checkbox" class="sr-only peer disability-type-toggle"
                                        data-type="<?= $key ?>" <?= in_array($key, $selectedDisabilityTypes) ? 'checked' : '' ?>>
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

            </div>
        </div>
    </div>

    <!-- ========== PENDIDIKAN ========== -->
    <div
        id="pendidikan"
        class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Riwayat Pendidikan</h2>
            <a href="<?= BASE_URL ?>views/pendidikan/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($pendidikanList)): ?>
                <div class="p-10 text-center">
                    <span class="inline-flex items-center justify-center mb-3" style="width:52px;height:52px;border-radius:50%;background:#F1F5F9;color:#94A3B8;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"></path>
                            <path d="M6 12v5c3 3 9 3 12 0v-5"></path>
                        </svg>
                    </span>
                    <p class="text-sm text-slate-400 italic">Belum ada riwayat pendidikan.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pendidikanList as $p): ?>
                    <div class="px-6 py-5 flex items-start justify-between">
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-800 mt-2 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-bold text-slate-800">
                                    <?php

                                    $jenjangTanpaJurusan = [
                                        'SD',
                                        'SMP',
                                        'SMA'
                                    ];

                                    if (in_array($p['jenjang'], $jenjangTanpaJurusan)) {
                                        echo htmlspecialchars($p['jenjang']);
                                    } else {
                                        echo htmlspecialchars(
                                            $p['jenjang'] . ' ' . $p['jurusan']
                                        );
                                    }

                                    ?>
                                </p>
                                <p class="text-xs text-slate-500 mt-1">

                                    <?= htmlspecialchars($p['institusi']) ?>

                                    &mdash;

                                    <?= $p['tahun_masuk'] ?>

                                    &ndash;

                                    <?= $p['tahun_lulus'] ?? 'sekarang' ?>

                                    <?php if (!empty($p['ipk'])): ?>

                                        &bull;

                                        <?php

                                        $jenjangSekolah = [
                                            'SD',
                                            'SMP',
                                            'SMA'
                                        ];

                                        echo in_array(
                                            $p['jenjang'],
                                            $jenjangSekolah
                                        )
                                            ? 'Nilai: ' . $p['ipk']
                                            : 'IPK: ' . $p['ipk'];

                                        ?>

                                    <?php endif; ?>

                                </p>
                            </div>
                        </div>
                        <div class="flex gap-4 flex-shrink-0">
                            <a href="<?= BASE_URL ?>views/pendidikan/edit.php?id=<?= $p['id_pendidikan'] ?>"
                                class="text-xs font-semibold flex items-center gap-1" style="color:#64748B;" onmouseover="this.style.color='#1D4ED8'" onmouseout="this.style.color='#64748B'">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit
                            </a>
                            <a href="<?= BASE_URL ?>views/pendidikan/delete.php?id=<?= $p['id_pendidikan'] ?>"
                                onclick="return confirm('Hapus data pendidikan ini?')"
                                class="text-xs font-semibold flex items-center gap-1 text-red-500 hover:text-red-700">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Hapus
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== PENGALAMAN KERJA ========== -->
    <div id="pengalaman-kerja" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Pengalaman Kerja</h2>
            <a href="<?= BASE_URL ?>views/pengalamanKerja/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($pengalamanList)): ?>
                <div class="p-10 text-center">
                    <span class="inline-flex items-center justify-center mb-3" style="width:52px;height:52px;border-radius:50%;background:#F1F5F9;color:#94A3B8;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                        </svg>
                    </span>
                    <p class="text-sm text-slate-400 italic">Belum ada pengalaman kerja.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pengalamanList as $px): ?>
                    <div class="px-6 py-5 flex items-start justify-between">

                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-emerald-600 mt-2 flex-shrink-0"></div>

                            <div class="space-y-1.5">

                                <!-- POSISI -->
                                <p class="text-sm font-bold text-slate-800">
                                    <?= htmlspecialchars($px['posisi']) ?>
                                </p>

                                <!-- PERUSAHAAN -->
                                <p class="text-xs text-slate-500">
                                    Perusahaan: <?= htmlspecialchars($px['nama_perusahaan']) ?>
                                </p>

                                <!-- TANGGAL -->
                                <p class="text-xs text-slate-500">
                                    Tanggal mulai: <?= $px['tanggal_mulai'] ?> Tanggal selesai:
                                    <?= $px['tanggal_selesai'] ?? 'sekarang' ?>
                                </p>

                                <!-- DESKRIPSI -->
                                <?php if (!empty($px['deskripsi_pekerjaan'])): ?>
                                    <p class="text-xs text-slate-500 leading-relaxed max-w-xl">
                                        Deskripsi: <?= htmlspecialchars($px['deskripsi_pekerjaan']) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- ACTION -->
                        <div class="flex gap-4 flex-shrink-0">
                            <a href="<?= BASE_URL ?>views/pengalamanKerja/edit.php?id=<?= $px['id'] ?>"
                                class="text-xs font-semibold flex items-center gap-1" style="color:#64748B;" onmouseover="this.style.color='#1D4ED8'" onmouseout="this.style.color='#64748B'">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                </svg>
                                Edit
                            </a>

                            <button type="button" onclick="openDeleteModal(
                                    <?= $px['id'] ?>,
                                    <?= $candidate['id'] ?>,
                                    '<?= htmlspecialchars($px['posisi'], ENT_QUOTES) ?>'
                                )" class="text-xs font-semibold flex items-center gap-1 text-red-500 hover:text-red-700">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="3 6 5 6 21 6"></polyline>
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                </svg>
                                Hapus
                            </button>
                        </div>
                        <!-- DELETE MODAL -->
                        <div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">

                            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                                <div class="flex items-center gap-3 mb-4">

                                    <div
                                        class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 text-red-600">
                                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                                            <line x1="12" y1="9" x2="12" y2="13"></line>
                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                        </svg>
                                    </div>

                                    <div>
                                        <h3 class="font-bold text-slate-800">
                                            Konfirmasi Hapus
                                        </h3>

                                        <p class="text-xs text-slate-500 mt-0.5">
                                            Tindakan ini tidak dapat dibatalkan
                                        </p>
                                    </div>

                                </div>

                                <p id="deleteMessage" class="text-sm text-slate-600 mb-6">
                                </p>

                                <div class="flex justify-end gap-3">

                                    <button type="button" onclick="closeDeleteModal()"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg border border-slate-300 hover:bg-slate-50 transition">
                                        Batal
                                    </button>

                                    <a id="deleteConfirmBtn" href="#"
                                        class="px-4 py-2 text-sm font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                                        Ya, Hapus
                                    </a>

                                </div>

                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== SKILL ========== -->
    <div id="skill-section" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Skill</h2>
            <a href="<?= BASE_URL ?>views/candidateSkill/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah
            </a>
        </div>
        <div class="p-6">

            <?php if (empty($skillList)): ?>

                <div class="text-center py-10">
                    <div
                        class="w-14 h-14 mx-auto mb-3 rounded-full flex items-center justify-center"
                        style="background:#DBEAFE;color:#1E3A8A;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 14 9-5-9-5-9 5 9 5Z"></path>
                            <path d="m22 9-10 5L2 9"></path>
                            <path d="M6 11.5v4.5a6 3 0 0 0 12 0v-4.5"></path>
                        </svg>
                    </div>

                    <p class="text-sm text-slate-400 italic">
                        Belum ada skill yang ditambahkan.
                    </p>
                </div>

            <?php else: ?>

                <div class="flex flex-wrap gap-3">

                    <?php foreach ($skillList as $skill): ?>

                        <div

                            class="group flex items-center gap-2.5 px-4 py-2.5 rounded-full transition"
                            style="
                        background:#DBEAFE;
                        color:#1E3A8A;
                        border:1px solid #BFDBFE;
                    ">

                            <span class="text-sm font-semibold">
                                <?= htmlspecialchars($skill['nama_skill']) ?>
                            </span>

                            <a
                                href="<?= BASE_URL ?>views/candidateSkill/delete.php?id=<?= $skill['id'] ?>&candidate_id=<?= $candidate['id'] ?>" onclick="return confirm('Hapus skill ini?')"
                                class="opacity-60 hover:opacity-100 transition flex items-center"
                                style="color:#DC2626;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <!-- ========== SERTIFIKASI ========== -->
    <div id="sertifikasi" class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">

        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-base text-slate-800">Sertifikasi</h2>

            <a href="<?= BASE_URL ?>views/sertifikasi/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                Tambah
            </a>
        </div>

        <div class="p-5">

            <?php if (mysqli_num_rows($sertifikasiList) == 0): ?>

                <div class="text-center py-10">
                    <span class="inline-flex items-center justify-center mb-3" style="width:56px;height:56px;border-radius:50%;background:#F1F5F9;color:#94A3B8;">
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"></path>
                            <circle cx="12" cy="8" r="6"></circle>
                        </svg>
                    </span>
                    <p class="text-sm text-slate-400 italic">
                        Belum ada sertifikasi.
                    </p>
                </div>

            <?php else: ?>

                <div class="space-y-4">

                    <?php while ($sertifikasi = mysqli_fetch_assoc($sertifikasiList)): ?>

                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">

                            <div class="flex justify-between items-start">

                                <div>

                                    <h3 class="font-bold text-base text-slate-800">
                                        <?= htmlspecialchars($sertifikasi['nama_sertifikasi']) ?>
                                    </h3>

                                    <p class="text-sm text-slate-500 mt-1">
                                        <?= htmlspecialchars($sertifikasi['penyelenggara']) ?>
                                    </p>

                                    <p class="text-xs text-slate-400 mt-1.5">
                                        Terbit:
                                        <?= date('d M Y', strtotime($sertifikasi['tanggal_terbit'])) ?>
                                    </p>

                                    <?php if (!empty($sertifikasi['file_sertifikasi'])): ?>

                                        <?php
                                        $file = $sertifikasi['file_sertifikasi'];

                                        $ext = strtolower(
                                            pathinfo($file, PATHINFO_EXTENSION)
                                        );

                                        $fileUrl =
                                            BASE_URL .
                                            "uploads/sertifikasi/" .
                                            $file;
                                        ?>

                                        <div class="mt-4 flex items-start gap-4 cursor-pointer"
                                            onclick="openPreviewSertifikasi(
        '<?= $fileUrl ?>',
        '<?= $ext ?>'
    )">

                                            <div
                                                class="w-[180px] h-[110px] overflow-hidden rounded-xl border border-slate-200 bg-slate-100 flex-shrink-0">

                                                <?php if (
                                                    in_array(
                                                        $ext,
                                                        ['jpg', 'jpeg', 'png', 'webp']
                                                    )
                                                ): ?>

                                                    <img
                                                        src="<?= $fileUrl ?>"
                                                        class="w-full h-full object-cover">

                                                <?php elseif ($ext === 'pdf'): ?>

                                                    <canvas
                                                        class="pdf-thumb w-full h-full"
                                                        data-pdf="<?= $fileUrl ?>">
                                                    </canvas>

                                                <?php endif; ?>

                                            </div>

                                            <div class="min-w-0 flex-1">

                                                <p class="text-sm font-semibold text-slate-700 truncate">
                                                    <?= htmlspecialchars($file) ?>
                                                </p>

                                                <p class="text-xs text-slate-400 mt-1.5">
                                                    Klik untuk melihat dokumen
                                                </p>

                                            </div>

                                        </div>
                                    <?php endif; ?>

                                </div>

                                <div class="flex gap-4 flex-shrink-0">

                                    <a href="<?= BASE_URL ?>views/sertifikasi/edit.php?id=<?= $sertifikasi['id_sertifikasi'] ?>"
                                        class="text-xs font-semibold flex items-center gap-1 transition" style="color:#94A3B8;" onmouseover="this.style.color='#1D4ED8'" onmouseout="this.style.color='#94A3B8'">

                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                        </svg>
                                        Edit

                                    </a>

                                    <button type="button" onclick="openDeleteModalSertifikasi(
                                            <?= $sertifikasi['id_sertifikasi'] ?>,
                                            '<?= htmlspecialchars($sertifikasi['nama_sertifikasi'], ENT_QUOTES) ?>'
                                        )" class="text-xs font-semibold flex items-center gap-1 text-red-500 hover:text-red-700">

                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                        </svg>
                                        Hapus

                                    </button>

                                </div>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php endif; ?>

        </div>

    </div>

    <!-- MODAL DELETE SERTIFIKASI -->
    <div id="deleteModalSertifikasi" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 text-red-600">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800">
                        Konfirmasi Hapus
                    </h3>

                    <p class="text-xs text-slate-500 mt-0.5">
                        Tindakan ini tidak dapat dibatalkan
                    </p>
                </div>
            </div>
            <p id="deleteMessageSertifikasi" class="text-sm text-slate-600 mb-6">
            </p>

            <div class="flex justify-end gap-3">

                <button type="button" onclick="closeDeleteModalSertifikasi()"
                    class="px-4 py-2 text-sm font-semibold rounded-lg border border-slate-300 hover:bg-slate-50 transition">
                    Batal
                </button>

                <a id="deleteSertifikasiLink" href="#"
                    class="px-4 py-2 text-sm font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 transition">
                    Ya, Hapus
                </a>

            </div>


        </div>

    </div>

    <div id="previewModalSertifikasi" class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/70">

        <div class="bg-white rounded-2xl w-[95%] h-[90%] overflow-hidden relative">

            <button onclick="closePreviewSertifikasi()"
                class="absolute top-4 right-4 z-10 bg-white rounded-full p-2 shadow flex items-center justify-center">

                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="color:#475569;">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>

            <div id="previewContentSertifikasi" class="w-full h-full">

            </div>

        </div>

    </div>


    <!-- ========== DANGER ZONE ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-base text-red-600">Danger Zone</h2>
        </div>
        <div class="p-6 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Hapus Akun</h4>
                <p class="text-xs text-slate-500 mt-1">Setelah dihapus, semua data tidak dapat dikembalikan. Harap
                    berhati-hati.</p>
            </div>
            <a href="delete.php?id=<?= $candidate['id'] ?>"
                onclick="return confirm('Yakin ingin menghapus akun ini secara permanen?')"
                class="px-5 py-2.5 text-xs font-bold rounded-xl text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 transition">
                Hapus Akun
            </a>
        </div>
    </div>

</div>

<!-- Toast notification -->
<div id="toastMsg"
    class="fixed bottom-5 right-5 z-50 px-5 py-3 rounded-xl text-sm font-semibold shadow-lg transition-all duration-300 opacity-0 pointer-events-none">
</div>

<style>
    input:focus,
    textarea:focus,
    select:focus {
        border-bottom-color: #1E3A8A !important;
    }
</style>
<style>
    html {
        scroll-behavior: smooth;
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    // Preview foto sebelum upload
    function previewFoto(input) {
        if (!input.files || !input.files[0]) return;
        const reader = new FileReader();
        reader.onload = function(e) {
            // Kalau ada elemen img, update src-nya
            let img = document.getElementById('fotoPreview');
            const initials = document.getElementById('fotoInitials');
            if (!img) {
                // Buat elemen img baru kalau sebelumnya pakai inisial
                img = document.createElement('img');
                img.id = 'fotoPreview';
                img.className = 'w-full h-full object-cover';
                if (initials) initials.replaceWith(img);
            }
            img.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }

    // Toggle section disabilitas
    function toggleDisabilitasSection(isChecked) {
        const section = document.getElementById('disabilitasSection');
        if (isChecked) {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
            // Reset semua toggle jenis saat dimatikan
            document.querySelectorAll('.disability-type-toggle').forEach(cb => cb.checked = false);
        }
    }

    // Simpan data disabilitas via AJAX
    async function saveDisabilitas() {
        const isDisabled = document.getElementById('toggleDisabilitas').checked ? 1 : 0;
        const description = document.getElementById('disabilityDescription').value.trim();

        const selectedTypes = [];
        document.querySelectorAll('.disability-type-toggle:checked').forEach(cb => {
            selectedTypes.push(cb.dataset.type);
        });

        try {
            const res = await fetch('<?= BASE_URL ?>public/actions/update_disability.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    candidate_id: <?= (int) $candidate['id'] ?>,
                    is_disabled: isDisabled,
                    description: description,
                    types: selectedTypes
                })
            });
            const data = await res.json();
            if (data.success) {
                showToast('Data disabilitas berhasil disimpan.', 'success');
            } else {
                showToast(data.message ?? 'Gagal menyimpan. Coba lagi.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    }

    // Toast notifikasi
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toastMsg');
        toast.textContent = msg;
        toast.className = `fixed bottom-5 right-5 z-50 px-5 py-3 rounded-xl text-sm font-semibold shadow-lg transition-all duration-300
        ${type === 'success' ? 'bg-blue-800 text-white' : 'bg-red-600 text-white'}`;
        toast.style.opacity = '1';
        toast.style.pointerEvents = 'auto';
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.pointerEvents = 'none';
        }, 3000);
    }

    // ===== AJAX Upload CV =====
    document.getElementById('cvFileInput').addEventListener('change', async function() {
        const file = this.files[0];
        if (!file) return;

        const allowed = ['application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!allowed.includes(file.type)) {
            showToast('Format file tidak didukung. Gunakan PDF, DOC, atau DOCX.', 'error');
            this.value = '';
            return;
        }

        // Ganti label jadi loading
        const label = document.getElementById('cvUploadLabel');
        label.innerHTML = 'Mengunggah...';
        label.style.pointerEvents = 'none';

        const formData = new FormData();
        formData.append('cv_file', file);
        formData.append('candidate_id', <?= (int) $candidate['id'] ?>);

        try {
            const res = await fetch('<?= BASE_URL ?>public/actions/upload_cv.php', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                const baseUrl = '<?= BASE_URL ?>public/uploads/cv/';
                const fileName = data.filename;

                // Update info file
                document.getElementById('cvInfo').innerHTML = `
                <span class="inline-flex items-center justify-center flex-shrink-0" style="width:40px;height:40px;border-radius:10px;background:#EFF6FF;color:#1E3A8A;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                </span>
                <div>
                    <p id="cvFileName" class="text-sm font-semibold text-slate-800">${fileName}</p>
                    <p class="text-xs text-slate-500 mt-0.5">File CV tersedia</p>
                </div>`;

                // Update tombol preview & unduh
                document.getElementById('cvActions').innerHTML = `
                <a href="${baseUrl}${fileName}" target="_blank"
                    class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                    Preview
                </a>
                <a href="${baseUrl}${fileName}" download
                    class="flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Unduh
                </a>`;

                showToast('CV berhasil diupload.', 'success');
            } else {
                showToast(data.message ?? 'Gagal mengupload CV.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            label.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:inline-block;vertical-align:middle;margin-right:6px;"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>Upload CV';
            label.style.pointerEvents = 'auto';
            this.value = '';
        }
    });

    // ===== AJAX Submit Main Form =====
    document.getElementById('mainForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        try {
            const res = await fetch(this.action, {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (data.success) {
                showToast('Profil berhasil disimpan.', 'success');
            } else {
                showToast(data.message ?? 'Gagal menyimpan profil.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        }
    });

    function openDeleteModal(id, candidateId, posisi) {
        const modal = document.getElementById('deleteModal');

        document.getElementById('deleteMessage').innerHTML =
            `Apakah anda yakin ingin menghapus pengalaman kerja <b>${posisi}</b>?`;

        document.getElementById('deleteConfirmBtn').href =
            `<?= BASE_URL ?>views/pengalamanKerja/delete.php?id=${id}&candidate_id=${candidateId}`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openDeleteModalSertifikasi(
        idSertifikasi,
        namaSertifikasi
    ) {

        const modal =
            document.getElementById(
                'deleteModalSertifikasi'
            );

        document.getElementById(
                'deleteMessageSertifikasi'
            ).innerHTML =
            `Apakah anda yakin ingin menghapus sertifikasi <b>${namaSertifikasi}</b>?`;

        document.getElementById(
                'deleteSertifikasiLink'
            ).href =
            "<?= BASE_URL ?>views/sertifikasi/delete.php?id=" +
            idSertifikasi;

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeDeleteModalSertifikasi() {

        const modal =
            document.getElementById(
                'deleteModalSertifikasi'
            );

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function openPreviewSertifikasi(
        fileUrl,
        ext
    ) {

        const modal =
            document.getElementById(
                'previewModalSertifikasi'
            );

        const content =
            document.getElementById(
                'previewContentSertifikasi'
            );

        if (
            ext === 'jpg' ||
            ext === 'jpeg' ||
            ext === 'png' ||
            ext === 'webp'
        ) {

            content.innerHTML = `
            <div class="w-full h-full flex items-center justify-center bg-slate-100">
                <img
                    src="${fileUrl}"
                    class="max-w-full max-h-full object-contain">
            </div>
        `;

        } else {

            content.innerHTML = `
            <iframe
                src="${fileUrl}"
                class="w-full h-full"
                frameborder="0">
            </iframe>
        `;

        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closePreviewSertifikasi() {

        const modal =
            document.getElementById(
                'previewModalSertifikasi'
            );

        const content =
            document.getElementById(
                'previewContentSertifikasi'
            );

        content.innerHTML = '';

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document
        .getElementById(
            'previewModalSertifikasi'
        )
        .addEventListener(
            'click',
            function(e) {

                if (e.target === this) {
                    closePreviewSertifikasi();
                }

            }
        )
</script>

<script>
    document.addEventListener(
        'DOMContentLoaded',
        async function() {

            const thumbs =
                document.querySelectorAll(
                    '.pdf-thumb'
                );

            for (const canvas of thumbs) {

                const pdfUrl =
                    canvas.dataset.pdf;

                try {

                    const pdf =
                        await pdfjsLib
                        .getDocument(pdfUrl)
                        .promise;

                    const page =
                        await pdf.getPage(1);

                    const viewport =
                        page.getViewport({
                            scale: 1
                        });

                    const ctx =
                        canvas.getContext('2d');

                    canvas.style.width = "180px";
                    canvas.style.height = "110px";

                    await page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    }).promise;

                } catch (err) {

                    console.error(
                        err
                    );

                }

            }

        });
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>