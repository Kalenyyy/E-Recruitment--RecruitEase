<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

// Proteksi Akses HRD
AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$id_transaksi = $_GET['id_transaksi'] ?? null;

if (!$id_transaksi || !is_numeric($id_transaksi)) {
    header("Location: " . BASE_URL . "views/pelamarPekerjaan/index.php");
    exit;
}

// Ambil data detail transaksi pelamar
$appDetails = PelamarPekerjaanController::getApplication($conn, $id_transaksi);
$pengalamanList = PengalamanKerja::getByCandidateId(
    $conn,
    $appDetails['id_kandidat']
);
$sertifikasiList = Sertifikasi::getByCandidateId(
    $conn,
    $appDetails['id_kandidat']
);

$statusColor = match ($appDetails['status_lamaran']) {

    'ADMINISTRASI'
    => 'bg-yellow-100 text-yellow-700 border-yellow-200',

    'INTERVIEW'
    => 'bg-blue-100 text-blue-700 border-blue-200',

    'DITERIMA'
    => 'bg-green-100 text-green-700 border-green-200',

    'DITOLAK'
    => 'bg-red-100 text-red-700 border-red-200',

    default
    => 'bg-slate-100 text-slate-700 border-slate-200'
};

if (!$appDetails) {
    die("Data transaksi lamaran tidak ditemukan.");
}

ob_start();
?>

<!-- HEADER -->
<div class="mb-6 rounded-2xl p-6 text-white shadow-sm"
    style="background: linear-gradient(135deg,#1E3A8A,#2563EB);">

    <h1 class="text-2xl font-bold">
        Kelola Tahap Seleksi
    </h1>

    <p class="text-sm text-blue-100 mt-1">
        Detail kandidat dan proses seleksi rekrutmen
    </p>

</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- SIDEBAR PROFIL -->
    <div class="space-y-6">

        <div class="bg-gradient-to-b from-blue-50 to-white rounded-2xl shadow-md border border-blue-100 p-6">

            <div class="text-center">

                <?php if (!empty($appDetails['foto'])): ?>
                    <!-- PROFILE IMAGE -->
                    <img
                        src="<?= BASE_URL ?>public/uploads/candidate/<?= $appDetails['foto'] ?>"
                        class="w-28 h-28 rounded-full mx-auto object-cover border">
                <?php else: ?>

                    <div class="w-28 h-28 rounded-full mx-auto bg-slate-100 flex items-center justify-center text-slate-400">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>

                <?php endif; ?>

                <h2 class="mt-4 text-lg font-bold text-slate-800">
                    <?= htmlspecialchars($appDetails['nama_lengkap']) ?>
                </h2>

                <p class="text-sm text-slate-500">
                    <?= htmlspecialchars($appDetails['email']) ?>
                </p>

            </div>

            <div class="mt-6 border-t pt-4 space-y-3">

                <div>
                    <p class="text-xs text-slate-400">No HP</p>
                    <p class="font-medium"><?= $appDetails['no_hp'] ?></p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">Jenis Kelamin</p>
                    <p class="font-medium">
                        <?= $appDetails['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">Tanggal Lahir</p>
                    <p class="font-medium">
                        <?= !empty($appDetails['tanggal_lahir']) ? date('d F Y', strtotime($appDetails['tanggal_lahir'])) : '-' ?>
                    </p>
                </div>

            </div>

            <?php if (!empty($appDetails['cv_file'])): ?>

                <a
                    href="<?= BASE_URL ?>public/uploads/cv/<?= $appDetails['cv_file'] ?>"
                    target="_blank"
                    class="mt-6 flex items-center justify-center gap-2 text-center bg-[#1E3A8A] text-white rounded-xl py-2 font-semibold">

                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    Download CV

                </a>

            <?php endif; ?>

        </div>

    </div>

    <!-- KONTEN KANAN -->
    <div class="lg:col-span-2 flex flex-col space-y-6">

        <!-- INFORMASI LAMARAN -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-blue-800 flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    Informasi Lamaran
                </h2>
            </div>

            <div class="p-6 grid grid-cols-2 gap-5">

                <div>
                    <p class="text-xs text-slate-400">
                        Lowongan
                    </p>

                    <p class="font-semibold">
                        <?= htmlspecialchars($appDetails['judul_job']) ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        Tanggal Melamar
                    </p>

                    <p class="font-semibold">
                        <?= date('d F Y H:i', strtotime($appDetails['tanggal_melamar'])) ?>
                    </p>
                </div>

                <div>
                    <p class="text-xs text-slate-400">
                        Status Saat Ini
                    </p>

                    <span class="inline-block px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-xs font-bold">
                        <?= $appDetails['status_lamaran'] ?>
                    </span>
                </div>

            </div>

        </div>

        <!-- DATA DIRI -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                    Data Pribadi
                </h2>
            </div>

            <div class="p-6">

                <div class="mb-4">

                    <p class="text-xs text-slate-400">
                        Alamat
                    </p>

                    <p class="font-medium">
                        <?= !empty($appDetails['alamat']) ? nl2br(htmlspecialchars($appDetails['alamat'])) : '-' ?>
                    </p>

                </div>

            </div>

        </div>

        <!-- DISABILITAS -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="5" r="1.5"></circle>
                        <path d="M9 9h6l-1 4 4 2v6"></path>
                        <path d="M7 21h10"></path>
                        <path d="M9 13a4 4 0 0 0 4 6"></path>
                    </svg>
                    Informasi Disabilitas
                </h2>
            </div>

            <div class="p-6">

                <?php if ($appDetails['is_disabled']): ?>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">

                        <div class="font-bold text-amber-700 mb-3">
                            Kandidat Penyandang Disabilitas
                        </div>

                        <!-- Menampilkan Jenis Disabilitas dari tabel candidate_disabilities -->
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php
                            if (!empty($appDetails['disability_types'])):
                                $types = explode(', ', $appDetails['disability_types']);
                                foreach ($types as $type):
                            ?>
                                    <span class="px-3 py-1 bg-amber-200 text-amber-800 border border-amber-300 rounded-full text-xs font-bold uppercase">
                                        <?= htmlspecialchars($type) ?>
                                    </span>
                                <?php
                                endforeach;
                            else:
                                ?>
                                <span class="text-xs text-amber-600 italic">Jenis disabilitas tidak ditentukan</span>
                            <?php endif; ?>
                        </div>

                        <div class="text-xs text-slate-400 uppercase font-bold mb-1">Deskripsi & Dukungan:</div>
                        <div class="text-sm text-slate-700 leading-relaxed">
                            <?= !empty($appDetails['disability_description']) ? nl2br(htmlspecialchars($appDetails['disability_description'])) : '-' ?>
                        </div>

                    </div>

                <?php else: ?>

                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                        <span class="font-semibold text-blue-700">
                            Tidak memiliki disabilitas
                        </span>
                    </div>

                <?php endif; ?>

            </div>
        </div>

        <!-- Skill -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 14 9-5-9-5-9 5 9 5Z"></path>
                        <path d="m22 9-10 5L2 9"></path>
                        <path d="M6 11.5v4.5a6 3 0 0 0 12 0v-4.5"></path>
                    </svg>
                    Skill
                </h2>
            </div>

            <div class="p-6">
                <?php if (!empty($appDetails['skills'])): ?>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach (explode(', ', $appDetails['skills']) as $nama_skill): ?>
                            <span class="inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                <?= htmlspecialchars(trim($nama_skill)) ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-slate-500 italic">
                        Tidak memiliki skill yang terdaftar
                    </p>
                <?php endif; ?>
            </div>

        </div>

        <!-- Riwayat Prestasi -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"></path>
                        <circle cx="12" cy="8" r="6"></circle>
                    </svg>
                    Sertifikasi &amp; Prestasi
                </h2>
            </div>

            <div class="p-6">

                <?php if (mysqli_num_rows($sertifikasiList) > 0): ?>

                    <div class="space-y-4">

                        <?php while ($sertifikat = mysqli_fetch_assoc($sertifikasiList)): ?>

                            <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-md transition">

                                <div class="flex justify-between items-start">

                                    <div>

                                        <h3 class="font-bold text-slate-800 text-lg">
                                            <?= htmlspecialchars($sertifikat['nama_sertifikasi']) ?>
                                        </h3>

                                        <p class="text-blue-700 font-medium text-sm mt-1 flex items-center gap-1.5">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                                <path d="M3 21h18"></path>
                                                <path d="M5 21V7l8-4v18"></path>
                                                <path d="M19 21V11l-6-4"></path>
                                                <path d="M9 9v.01"></path>
                                                <path d="M9 12v.01"></path>
                                                <path d="M9 15v.01"></path>
                                                <path d="M9 18v.01"></path>
                                            </svg>
                                            <?= htmlspecialchars($sertifikat['penyelenggara']) ?>
                                        </p>

                                    </div>

                                    <div class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold flex items-center gap-1.5">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="8" r="6"></circle>
                                            <path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"></path>
                                        </svg>
                                        Sertifikasi
                                    </div>

                                </div>

                                <div class="mt-4 flex items-center justify-between">

                                    <div class="text-sm text-slate-500 flex items-center gap-1.5">

                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>
                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        Diterbitkan:
                                        <span class="font-medium text-slate-700">
                                            <?= date('d F Y', strtotime($sertifikat['tanggal_terbit'])) ?>
                                        </span>

                                    </div>

                                    <?php if (!empty($sertifikat['file_sertifikasi'])): ?>

                                        <a
                                            href="<?= BASE_URL ?>uploads/sertifikasi/<?= $sertifikat['file_sertifikasi'] ?>"
                                            target="_blank"
                                            class="px-4 py-2 bg-[#1E3A8A] hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition flex items-center gap-1.5">

                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                            </svg>
                                            Lihat Sertifikat

                                        </a>

                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="bg-white rounded-xl border border-dashed border-slate-300 p-8 text-center">

                        <div class="flex items-center justify-center mb-3 text-slate-300">
                            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M8.21 13.89 7 23l5-3 5 3-1.21-9.12"></path>
                                <circle cx="12" cy="8" r="6"></circle>
                            </svg>
                        </div>

                        <h3 class="font-semibold text-slate-700">
                            Belum Ada Sertifikasi
                        </h3>

                        <p class="text-sm text-slate-500 mt-1">
                            Kandidat belum mengunggah sertifikasi atau prestasi.
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <!-- Riwayat Pengalaman -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-slate-800 flex items-center gap-2">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                    </svg>
                    Riwayat Pengalaman
                </h2>
            </div>

            <div class="p-6">

                <?php if (!empty($pengalamanList)): ?>

                    <div class="space-y-4">

                        <?php foreach ($pengalamanList as $exp): ?>

                            <div class="bg-white border border-slate-200 rounded-xl p-4">

                                <div class="flex justify-between items-start">

                                    <div>

                                        <h3 class="font-bold text-slate-800">
                                            <?= htmlspecialchars($exp['posisi']) ?>
                                        </h3>

                                        <p class="text-blue-700 font-medium">
                                            <?= htmlspecialchars($exp['nama_perusahaan']) ?>
                                        </p>

                                    </div>

                                    <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full">

                                        <?= date('M Y', strtotime($exp['tanggal_mulai'])) ?>

                                        -

                                        <?= !empty($exp['tanggal_selesai'])
                                            ? date('M Y', strtotime($exp['tanggal_selesai']))
                                            : 'Sekarang'
                                        ?>

                                    </span>

                                </div>

                                <?php if (!empty($exp['deskripsi_pekerjaan'])): ?>

                                    <div class="mt-3 text-sm text-slate-600">

                                        <?= nl2br(htmlspecialchars($exp['deskripsi_pekerjaan'])) ?>

                                    </div>

                                <?php endif; ?>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php else: ?>

                    <div class="bg-white rounded-xl p-6 text-center border border-dashed border-slate-300">

                        <div class="flex items-center justify-center mb-2 text-slate-300">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                            </svg>
                        </div>

                        <p class="text-slate-500">
                            Belum memiliki riwayat pengalaman kerja
                        </p>

                    </div>

                <?php endif; ?>

            </div>

        </div>

        <div class="flex justify-end gap-4 mt-6">
            <a
                href="<?= BASE_URL ?>views/pelamarPekerjaan/detail.php?job_id=<?= $appDetails['id_lowongan'] ?>"
                class="px-5 py-3 rounded-xl bg-slate-900 text-white font-semibold border border-slate-800 hover:bg-slate-800 transition-colors duration-200">
                Kembali
            </a>
        </div>

    </div>
</div>


<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>