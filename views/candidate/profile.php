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

if (!$profile) die("Data tidak ditemukan");

// ================= DATA READY =================
$candidate = $profile['candidate'];
$selectedDisabilityTypes = $profile['disabilities'] ?? [];
// $pendidikanList = $profile['education'] ?? [];
// $pengalamanList = $profile['experience'] ?? [];
$pengalamanList = PengalamanKerja::getByCandidateId($conn, $id);
$skillList = CandidateSkill::getByCandidateId($conn, $id);
// $sertifikasiList = $profile['certifications'] ?? [];

// ================= UI HELPERS (boleh di view) =================
$nama = $candidate['nama_lengkap'];
$parts = explode(' ', trim($nama));
$initials = strtoupper(substr($parts[0] ?? '', 0, 1));
if (isset($parts[1])) $initials .= strtoupper(substr($parts[1], 0, 1));

$fotoPathLocal = __DIR__ . "/../../public/uploads/candidate/" . $candidate['foto'];
$hasFoto = !empty($candidate['foto']) && file_exists($fotoPathLocal);

$isDisabled = !empty($candidate['is_disabled']);

$jenisDisabilitas = [
    'fisik'       => ['label' => 'Disabilitas Fisik', 'desc' => 'Gangguan fungsi gerak, anggota tubuh, atau mobilitas'],
    'netra'       => ['label' => 'Disabilitas Netra', 'desc' => 'Gangguan penglihatan sebagian atau total'],
    'rungu'       => ['label' => 'Disabilitas Rungu/Wicara', 'desc' => 'Gangguan pendengaran atau kemampuan berbicara'],
    'intelektual' => ['label' => 'Disabilitas Intelektual', 'desc' => 'Hambatan dalam fungsi intelektual dan adaptif'],
    'mental'      => ['label' => 'Disabilitas Mental', 'desc' => 'Kondisi kesehatan mental yang memengaruhi aktivitas'],
    'sensorik'    => ['label' => 'Disabilitas Sensorik', 'desc' => 'Gangguan pada indra selain penglihatan dan pendengaran'],
    'lainnya'     => ['label' => 'Lainnya', 'desc' => 'Jenis disabilitas lain yang tidak tercantum di atas'],
];

ob_start();
?>

<!-- BREADCRUMB & TITLE -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-2xl font-bold" style="color:#1E293B;">User Profile</h1>
        <div class="flex items-center gap-2 text-xs" style="color:#64748B;">
            <span>Home</span> <span>›</span> <span style="color:#1E3A8A;">User Profile</span>
        </div>
    </div>
</div>

<div class="flex flex-col gap-6 mb-10">

    <!-- ========== MY PROFILE ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">My Profile</h2>
            <button type="submit" form="mainForm"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                💾 Simpan
            </button>
        </div>

        <form id="mainForm" action="<?= BASE_URL ?>public/actions/update_profile_candidate.php" method="POST" enctype="multipart/form-data" class="p-6">
            <input type="hidden" name="id" value="<?= $candidate['id'] ?>">

            <!-- Avatar -->
            <div class="flex items-center gap-5 mb-8">
                <div class="relative group">
                    <div class="w-20 h-20 rounded-full overflow-hidden border-2 border-slate-100 flex-shrink-0">
                        <?php if ($hasFoto): ?>
                            <img id="fotoPreview"
                                src="<?= BASE_URL ?>public/uploads/candidate/<?= $candidate['foto'] ?>"
                                class="w-full h-full object-cover">
                        <?php else: ?>
                            <div id="fotoInitials"
                                class="w-full h-full flex items-center justify-center text-xl font-bold"
                                style="background:#DBEAFE;color:#1E3A8A;">
                                <?= $initials ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <label
                        class="absolute inset-0 flex items-center justify-center bg-black/40 rounded-full opacity-0 group-hover:opacity-100 cursor-pointer transition text-white text-[10px] font-semibold">
                        Ganti
                        <input type="file" name="foto" class="hidden" accept="image/*"
                            onchange="previewFoto(this)">
                    </label>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($nama) ?></h3>
                    <p class="text-sm text-slate-500">Candidate</p>
                </div>
            </div>

            <!-- Fields -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap"
                        value="<?= htmlspecialchars($candidate['nama_lengkap']) ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Email</label>
                    <input type="email" name="email"
                        value="<?= htmlspecialchars($candidate['email']) ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">No. HP</label>
                    <input type="text" name="no_hp"
                        value="<?= htmlspecialchars($candidate['no_hp']) ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir"
                        value="<?= htmlspecialchars($candidate['tanggal_lahir'] ?? '') ?>"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent">
                </div>
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Jenis Kelamin</label>
                    <select name="jenis_kelamin"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent cursor-pointer">
                        <option value="">-- Pilih --</option>
                        <option value="L" <?= ($candidate['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($candidate['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>
            </div>
        </form>
    </div>

    <!-- ========== CV / RESUME ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">CV / Resume</h2>
            <label id="cvUploadLabel"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 cursor-pointer transition">
                📎 Upload CV
                <input type="file" id="cvFileInput" class="hidden" accept=".pdf,.doc,.docx">
            </label>
        </div>
        <div class="p-6 flex items-center justify-between">
            <div id="cvInfo" class="flex items-center gap-3">
                <?php if (!empty($candidate['cv_file'])): ?>
                    <span class="text-2xl">📄</span>
                    <div>
                        <p id="cvFileName" class="text-sm font-semibold text-slate-800">
                            <?= htmlspecialchars($candidate['cv_file']) ?>
                        </p>
                        <p class="text-xs text-slate-500">File CV tersedia</p>
                    </div>
                <?php else: ?>
                    <p id="cvEmpty" class="text-sm text-slate-400 italic">Belum ada CV yang diupload.</p>
                <?php endif; ?>
            </div>
            <div id="cvActions" class="flex gap-2">
                <?php if (!empty($candidate['cv_file'])): ?>
                    <a id="cvPreviewBtn" href="<?= BASE_URL ?>public/uploads/cv/<?= $candidate['cv_file'] ?>" target="_blank"
                        class="px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                        👁️ Preview
                    </a>
                    <a id="cvDownloadBtn" href="<?= BASE_URL ?>public/uploads/cv/<?= $candidate['cv_file'] ?>" download
                        class="px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                        ⬇️ Unduh
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ========== ALAMAT ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Alamat</h2>
            <button type="submit" form="mainForm"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                💾 Simpan
            </button>
        </div>
        <div class="p-6">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-semibold text-slate-500">Alamat Lengkap</label>
                <textarea form="mainForm" name="alamat" rows="2"
                    class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent resize-none"><?= htmlspecialchars($candidate['alamat'] ?? '') ?></textarea>
            </div>
        </div>
    </div>

    <!-- ========== DISABILITAS ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Informasi Disabilitas</h2>
            <button type="button" onclick="saveDisabilitas()"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                💾 Simpan
            </button>
        </div>
        <div class="p-6 flex flex-col gap-4">

            <!-- Toggle utama -->
            <div class="flex items-center justify-between py-1">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Saya memiliki disabilitas</p>
                    <p class="text-xs text-slate-500">Aktifkan jika Anda memiliki kondisi disabilitas</p>
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
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-semibold text-slate-500">Deskripsi Tambahan <span class="font-normal">(opsional)</span></label>
                    <textarea id="disabilityDescription" rows="2"
                        class="px-0 py-1 text-sm font-semibold text-slate-800 border-b border-slate-200 focus:border-blue-800 outline-none bg-transparent resize-none"><?= htmlspecialchars($candidate['disability_description'] ?? '') ?></textarea>
                </div>

                <!-- Jenis-jenis disabilitas -->
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-3">Pilih Jenis Disabilitas</p>
                    <div class="flex flex-col gap-2">
                        <?php foreach ($jenisDisabilitas as $key => $info): ?>
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-slate-100 bg-slate-50 hover:bg-slate-100 transition">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800"><?= $info['label'] ?></p>
                                    <p class="text-xs text-slate-500"><?= $info['desc'] ?></p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer ml-4 flex-shrink-0">
                                    <input type="checkbox"
                                        class="sr-only peer disability-type-toggle"
                                        data-type="<?= $key ?>"
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

            </div>
        </div>
    </div>

    <!-- ========== PENDIDIKAN ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Riwayat Pendidikan</h2>
            <a href="pendidikan/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                + Tambah
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($pendidikanList)): ?>
                <div class="p-8 text-center">
                    <p class="text-2xl mb-2">🎓</p>
                    <p class="text-sm text-slate-400 italic">Belum ada riwayat pendidikan.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pendidikanList as $p): ?>
                    <div class="px-6 py-4 flex items-start justify-between">
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-800 mt-1.5 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?= htmlspecialchars($p['jenjang'] . ' ' . $p['jurusan']) ?>
                                </p>
                                <p class="text-xs text-slate-500">
                                    <?= htmlspecialchars($p['institusi']) ?>
                                    &mdash; <?= $p['tahun_masuk'] ?> &ndash; <?= $p['tahun_lulus'] ?? 'sekarang' ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3 flex-shrink-0">
                            <a href="pendidikan/edit.php?id=<?= $p['id'] ?>"
                                class="text-xs text-slate-400 hover:text-blue-700 transition">✏️ Edit</a>
                            <a href="pendidikan/delete.php?id=<?= $p['id'] ?>"
                                onclick="return confirm('Hapus data pendidikan ini?')"
                                class="text-xs text-slate-400 hover:text-red-600 transition">🗑️ Hapus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== PENGALAMAN KERJA ========== -->
    <div
        id="pengalaman-kerja"
        class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Pengalaman Kerja</h2>
            <a href="<?= BASE_URL ?>views/pengalamanKerja/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                + Tambah
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($pengalamanList)): ?>
                <div class="p-8 text-center">
                    <p class="text-2xl mb-2">💼</p>
                    <p class="text-sm text-slate-400 italic">Belum ada pengalaman kerja.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pengalamanList as $px): ?>
                    <div class="px-6 py-4 flex items-start justify-between">

                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-emerald-600 mt-1.5 flex-shrink-0"></div>

                            <div class="space-y-1">

                                <!-- POSISI -->
                                <p class="text-sm font-semibold text-slate-800">
                                    <?= htmlspecialchars($px['posisi']) ?>
                                </p>

                                <!-- PERUSAHAAN -->
                                <p class="text-xs text-500">
                                    Perusahaan: <?= htmlspecialchars($px['nama_perusahaan']) ?>
                                </p>

                                <!-- TANGGAL -->
                                <p class="text-xs text-500">
                                    Tanggal mulai: <?= $px['tanggal_mulai'] ?> Tanggal selesai: <?= $px['tanggal_selesai'] ?? 'sekarang' ?>
                                </p>

                                <!-- DESKRIPSI -->
                                <?php if (!empty($px['deskripsi_pekerjaan'])): ?>
                                    <p class="text-xs text-500 leading-relaxed max-w-xl">
                                        Deskripsi: <?= htmlspecialchars($px['deskripsi_pekerjaan']) ?>
                                    </p>
                                <?php endif; ?>

                            </div>
                        </div>

                        <!-- ACTION -->
                        <div class="flex gap-3 flex-shrink-0">
                            <a href="<?= BASE_URL ?>views/pengalamanKerja/edit.php?id=<?= $px['id'] ?>"
                                class="text-xs text-500 hover:text-blue-700">
                                ✏️ Edit
                            </a>

                            <button
                                type="button"
                                onclick="openDeleteModal(
                                    <?= $px['id'] ?>,
                                    <?= $candidate['id'] ?>,
                                    '<?= htmlspecialchars($px['posisi'], ENT_QUOTES) ?>'
                                )"
                                class="text-xs text-red-500 hover:text-red-700">
                                🗑️ Hapus
                            </button>
                        </div>
                        <!-- DELETE MODAL -->
                        <div
                            id="deleteModal"
                            class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40">

                            <div
                                class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6">

                                <div class="flex items-center gap-3 mb-4">

                                    <div
                                        class="w-12 h-12 rounded-full flex items-center justify-center bg-red-100 text-red-600 text-xl">
                                        ⚠️
                                    </div>

                                    <div>
                                        <h3 class="font-bold text-slate-800">
                                            Konfirmasi Hapus
                                        </h3>

                                        <p class="text-xs text-slate-500">
                                            Tindakan ini tidak dapat dibatalkan
                                        </p>
                                    </div>

                                </div>

                                <p
                                    id="deleteMessage"
                                    class="text-sm text-slate-600 mb-6">
                                </p>

                                <div class="flex justify-end gap-3">

                                    <button
                                        type="button"
                                        onclick="closeDeleteModal()"
                                        class="px-4 py-2 text-sm rounded-lg border border-slate-300">
                                        Batal
                                    </button>

                                    <a
                                        id="deleteConfirmBtn"
                                        href="#"
                                        class="px-4 py-2 text-sm rounded-lg bg-red-600 text-white hover:bg-red-700">
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
            <h2 class="font-bold text-slate-800">Skill</h2>
            <a href="<?= BASE_URL ?>views/candidateSkill/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                + Tambah
            </a>
        </div>
        <div class="p-6">

            <?php if (empty($skillList)): ?>

                <div class="text-center py-8">
                    <div
                        class="w-14 h-14 mx-auto mb-3 rounded-full flex items-center justify-center"
                        style="background:#DBEAFE;color:#1E3A8A;">
                        🛠️
                    </div>

                    <p class="text-sm text-slate-400 italic">
                        Belum ada skill yang ditambahkan.
                    </p>
                </div>

            <?php else: ?>

                <div class="flex flex-wrap gap-3">

                    <?php foreach ($skillList as $skill): ?>

                        <div
                            class="group flex items-center gap-2 px-4 py-2 rounded-full transition"
                            style="
                        background:#DBEAFE;
                        color:#1E3A8A;
                        border:1px solid #BFDBFE;
                    ">

                            <span class="text-xs font-semibold">
                                <?= htmlspecialchars($skill['nama_skill']) ?>
                            </span>

                            <a
                                href="<?= BASE_URL ?>views/candidateSkill/delete.php?id=<?= $skill['id'] ?>&candidate_id=<?= $candidate['id'] ?>"
                                onclick="return confirm('Hapus skill ini?')"
                                class="opacity-60 hover:opacity-100 transition"
                                style="color:#DC2626;">
                                ✕
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <!-- ========== SERTIFIKASI ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 flex items-center justify-between border-b border-slate-100">
            <h2 class="font-bold text-slate-800">Sertifikasi</h2>
            <a href="sertifikasi/create.php?candidate_id=<?= $candidate['id'] ?>"
                class="flex items-center gap-2 px-4 py-1.5 text-xs font-semibold rounded-lg bg-blue-50 border border-blue-200 text-blue-800 hover:bg-blue-100 transition">
                + Tambah
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php if (empty($sertifikasiList)): ?>
                <div class="p-8 text-center">
                    <p class="text-2xl mb-2">🏆</p>
                    <p class="text-sm text-slate-400 italic">Belum ada sertifikasi.</p>
                </div>
            <?php else: ?>
                <?php foreach ($sertifikasiList as $s): ?>
                    <div class="px-6 py-4 flex items-start justify-between">
                        <div class="flex gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-600 mt-1.5 flex-shrink-0"></div>
                            <div>
                                <p class="text-sm font-semibold text-slate-800">
                                    <?= htmlspecialchars($s['nama_sertifikasi']) ?>
                                </p>
                                <p class="text-xs text-slate-500">
                                    <?= htmlspecialchars($s['penerbit']) ?>
                                    &mdash; Berlaku s/d <?= $s['tanggal_kadaluarsa'] ?? '&infin;' ?>
                                </p>
                                <?php if (!empty($s['file_sertifikat'])): ?>
                                    <a href="<?= BASE_URL ?>public/uploads/sertifikasi/<?= $s['file_sertifikat'] ?>"
                                        target="_blank"
                                        class="text-xs text-blue-600 hover:underline mt-0.5 inline-block">
                                        📎 Lihat sertifikat
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="flex gap-3 flex-shrink-0">
                            <a href="sertifikasi/edit.php?id=<?= $s['id'] ?>"
                                class="text-xs text-slate-400 hover:text-blue-700 transition">✏️ Edit</a>
                            <a href="sertifikasi/delete.php?id=<?= $s['id'] ?>"
                                onclick="return confirm('Hapus sertifikasi ini?')"
                                class="text-xs text-slate-400 hover:text-red-600 transition">🗑️ Hapus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ========== DANGER ZONE ========== -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-bold text-red-600">Danger Zone</h2>
        </div>
        <div class="p-6 flex items-center justify-between">
            <div>
                <h4 class="text-sm font-semibold text-slate-800">Hapus Akun</h4>
                <p class="text-xs text-slate-500">Setelah dihapus, semua data tidak dapat dikembalikan. Harap berhati-hati.</p>
            </div>
            <a href="delete.php?id=<?= $candidate['id'] ?>"
                onclick="return confirm('Yakin ingin menghapus akun ini secara permanen?')"
                class="px-4 py-2 text-xs font-semibold rounded-xl text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 transition">
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
        label.innerHTML = '⏳ Mengunggah...';
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
                <span class="text-2xl">📄</span>
                <div>
                    <p id="cvFileName" class="text-sm font-semibold text-slate-800">${fileName}</p>
                    <p class="text-xs text-slate-500">File CV tersedia</p>
                </div>`;

                // Update tombol preview & unduh
                document.getElementById('cvActions').innerHTML = `
                <a href="${baseUrl}${fileName}" target="_blank"
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    👁️ Preview
                </a>
                <a href="${baseUrl}${fileName}" download
                    class="px-4 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                    ⬇️ Unduh
                </a>`;

                showToast('CV berhasil diupload.', 'success');
            } else {
                showToast(data.message ?? 'Gagal mengupload CV.', 'error');
            }
        } catch (err) {
            showToast('Terjadi kesalahan jaringan.', 'error');
        } finally {
            label.innerHTML = '📎 Upload CV';
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
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>