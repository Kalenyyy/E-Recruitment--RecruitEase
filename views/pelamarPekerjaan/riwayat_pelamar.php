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

$statusColor = match($appDetails['status_lamaran']) {

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

// Proses jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status_baru = $_POST['status_lamaran'] ?? '';
    
    // Validasi opsi enum biar gak dimanipulasi dari inspect element
    $allowedStatus = ['ADMINISTRASI', 'INTERVIEW', 'DITERIMA', 'DITOLAK'];
    if (in_array($status_baru, $allowedStatus)) {
        $sukses = PelamarPekerjaanController::ubahStatus($conn, $id_transaksi, $status_baru);
        if ($sukses) {
            // Balikkin ke halaman detail lowongan tadi
            header("Location: " . BASE_URL . "views/pelamarPekerjaan/detail.php?job_id=" . $appDetails['id_lowongan']);
            exit;
        } else {
            $error_msg = "Gagal memperbarui status pelamar.";
        }
    } else {
        $error_msg = "Status seleksi tidak valid.";
    }
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

                <?php if(!empty($appDetails['foto'])): ?>

                    <img
                        src="<?= BASE_URL ?>public/uploads/candidates/<?= $appDetails['foto'] ?>"
                        class="w-28 h-28 rounded-full mx-auto object-cover border">

                <?php else: ?>

                    <div class="w-28 h-28 rounded-full mx-auto bg-slate-100 flex items-center justify-center text-5xl">
                        👤
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

            <?php if(!empty($appDetails['cv_file'])): ?>

                <a
                    href="<?= BASE_URL ?>public/uploads/cv/<?= $appDetails['cv_file'] ?>"
                    target="_blank"
                    class="mt-6 block text-center bg-[#1E3A8A] text-white rounded-xl py-2 font-semibold">

                    📄 Download CV

                </a>

            <?php endif; ?>

        </div>

    </div>

    <!-- KONTEN KANAN -->
    <div class="lg:col-span-2 flex flex-col space-y-6">

        <!-- INFORMASI LAMARAN -->
        <div class="bg-blue-50 rounded-2xl shadow-sm border border-blue-100">

            <div class="p-5 border-b border-blue-100 bg-blue-100">
                <h2 class="font-bold text-blue-800">
                    💼 Informasi Lamaran
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
                <h2 class="font-bold text-slate-800">
                    👤 Data Pribadi
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
                <h2 class="font-bold text-slate-800">
                    ♿ Informasi Disabilitas
                </h2>
            </div>

            <div class="p-6">

                <?php if($appDetails['is_disabled']): ?>

                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">

                        <div class="font-bold text-amber-700">
                            Kandidat Penyandang Disabilitas
                        </div>

                        <div class="mt-2 text-sm text-slate-700">
                            <?= htmlspecialchars($appDetails['disability_description']) ?>
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
                <h2 class="font-bold text-slate-800">
                    🛠️ Skill
                </h2>
            </div>

            <div class="p-6">
                <?php if(!empty($appDetails['skills'])): ?>
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
        <h2 class="font-bold text-slate-800">
            🏆 Sertifikasi & Prestasi
        </h2>
    </div>

    <div class="p-6">

        <?php if(mysqli_num_rows($sertifikasiList) > 0): ?>

            <div class="space-y-4">

                <?php while($sertifikat = mysqli_fetch_assoc($sertifikasiList)): ?>

                    <div class="bg-white border border-slate-200 rounded-xl p-5 hover:shadow-md transition">

                        <div class="flex justify-between items-start">

                            <div>

                                <h3 class="font-bold text-slate-800 text-lg">
                                    <?= htmlspecialchars($sertifikat['nama_sertifikasi']) ?>
                                </h3>

                                <p class="text-blue-700 font-medium text-sm mt-1">
                                    🏢 <?= htmlspecialchars($sertifikat['penyelenggara']) ?>
                                </p>

                            </div>

                            <div class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-semibold">
                                🏅 Sertifikasi
                            </div>

                        </div>

                        <div class="mt-4 flex items-center justify-between">

                            <div class="text-sm text-slate-500">

                                📅 Diterbitkan:
                                <span class="font-medium text-slate-700">
                                    <?= date('d F Y', strtotime($sertifikat['tanggal_terbit'])) ?>
                                </span>

                            </div>

                            <?php if(!empty($sertifikat['file_sertifikasi'])): ?>
                               
                                <a
                                    href="<?= BASE_URL ?>uploads/sertifikasi/<?= $sertifikat['file_sertifikasi'] ?>"
                                    target="_blank"
                                    class="px-4 py-2 bg-[#1E3A8A] hover:bg-blue-800 text-white rounded-lg text-sm font-semibold transition">

                                    📄 Lihat Sertifikat

                                </a>

                            <?php endif; ?>

                        </div>

                    </div>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="bg-white rounded-xl border border-dashed border-slate-300 p-8 text-center">

                <div class="text-5xl mb-3">
                    🏆
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
        <h2 class="font-bold text-slate-800">
            💼 Riwayat Pengalaman
        </h2>
    </div>

    <div class="p-6">

        <?php if(!empty($pengalamanList)): ?>

            <div class="space-y-4">

                <?php foreach($pengalamanList as $exp): ?>

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

                        <?php if(!empty($exp['deskripsi_pekerjaan'])): ?>

                            <div class="mt-3 text-sm text-slate-600">

                                <?= nl2br(htmlspecialchars($exp['deskripsi_pekerjaan'])) ?>

                            </div>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="bg-white rounded-xl p-6 text-center border border-dashed border-slate-300">

                <div class="text-4xl mb-2">
                    💼
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


