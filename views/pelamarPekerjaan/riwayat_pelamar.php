<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$id_transaksi = $_GET['id_transaksi'] ?? null;
if (!$id_transaksi || !is_numeric($id_transaksi)) {
    header("Location: index.php");
    exit;
}

$appDetails = PelamarPekerjaanController::getApplication($conn, $id_transaksi);
if (!$appDetails) die("Data tidak ditemukan.");

$pengalamanList = PengalamanKerja::getByCandidateId($conn, $appDetails['id_kandidat']);
$sertifikasiList = Sertifikasi::getByCandidateId($conn, $appDetails['id_kandidat']);

$statusStyle = match (strtoupper($appDetails['status_lamaran'])) {
    'ADMINISTRASI' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
    'INTERVIEW'    => ['bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
    'DITERIMA', 'OFFERING' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-600'],
    'DITOLAK'      => ['bg' => 'bg-rose-50', 'text' => 'text-rose-600'],
    default        => ['bg' => 'bg-slate-50', 'text' => 'text-slate-600']
};

ob_start();
?>

<div class="min-h-screen bg-[#F8FAFC] pb-12">

    <!-- TOP BAR -->
    <div class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200">
        <div class="max-w-6xl mx-auto px-6 h-14 flex items-center justify-between">
            <a href="<?= BASE_URL ?>views/pelamarPekerjaan/detail.php?job_id=<?= $appDetails['id_lowongan'] ?>" class="text-slate-500 hover:text-blue-600 font-bold text-sm">
                <i class="fa-solid fa-arrow-left mr-2"></i> Kembali ke Daftar
            </a>
            <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg border <?= $statusStyle['bg'] ?> <?= $statusStyle['text'] ?> border-current/10">
                Status: <?= $appDetails['status_lamaran'] ?>
            </span>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-6 pt-8">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- KOLOM KIRI: KONTEN UTAMA -->
            <div class="lg:col-span-8 space-y-6">

                <!-- SUMMARY CARD -->
                <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
                    <p class="text-[10px] font-black text-blue-600 uppercase tracking-[0.2em] mb-2">Konteks Lamaran</p>
                    <h1 class="text-3xl font-black text-slate-800 tracking-tight mb-6">
                        <?= htmlspecialchars($appDetails['judul_job']) ?>
                    </h1>

                    <div class="grid grid-cols-2 gap-4 border-t border-slate-100 pt-6">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Daftar Pada</p>
                            <p class="text-sm font-bold text-slate-700"><?= date('d M Y, H:i', strtotime($appDetails['tanggal_melamar'])) ?></p>
                        </div>
                    </div>
                </div>

                <!-- DISABILITAS HIGHLIGHT -->
                <?php if ($appDetails['is_disabled']): ?>
                    <div class="bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden">
                        <div class="flex items-center gap-4 mb-4">
                            <i class="fa-solid fa-universal-access text-blue-400 text-xl"></i>
                            <h2 class="text-sm font-black uppercase tracking-widest">Informasi Disabilitas</h2>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-4">
                            <?php foreach (explode(', ', $appDetails['disability_types'] ?? '') as $type): ?>
                                <span class="px-3 py-1 bg-white/10 border border-white/10 rounded-lg text-[9px] font-black uppercase tracking-widest"><?= htmlspecialchars($type) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-slate-400 text-sm italic">"<?= nl2br(htmlspecialchars($appDetails['disability_description'])) ?>"</p>
                    </div>
                <?php endif; ?>

                <!-- PENGALAMAN -->
                <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Riwayat Pengalaman Kerja</h2>
                    <?php if (count($pengalamanList) > 0): ?>
                        <div class="space-y-8 relative border-l-2 border-slate-100 ml-3 pl-8">
                            <?php foreach ($pengalamanList as $exp): ?>
                                <div class="relative">
                                    <div class="absolute -left-[41px] top-1 w-4 h-4 rounded-full bg-white border-4 border-blue-600 shadow-sm"></div>
                                    <div class="flex justify-between items-start mb-1">
                                        <h3 class="text-base font-black text-slate-800"><?= htmlspecialchars($exp['posisi']) ?></h3>
                                        <span class="text-[10px] font-bold text-slate-400 bg-slate-50 px-2 py-1 rounded">
                                            <?= date('M Y', strtotime($exp['tanggal_mulai'])) ?> — <?= !empty($exp['tanggal_selesai']) ? date('M Y', strtotime($exp['tanggal_selesai'])) : 'Sekarang' ?>
                                        </span>
                                    </div>
                                    <p class="text-blue-600 font-bold text-xs mb-3"><?= htmlspecialchars($exp['nama_perusahaan']) ?></p>
                                    <div class="text-sm text-slate-500 leading-relaxed"><?= nl2br(htmlspecialchars($exp['deskripsi_pekerjaan'])) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-slate-400 text-sm italic py-4">Kandidat belum mengisi riwayat kerja.</p>
                    <?php endif; ?>
                </div>

                <!-- SKILLS -->
                <div class="bg-white border border-slate-200 rounded-3xl p-8 shadow-sm">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">Keahlian & Kompetensi</h2>
                    <div class="flex flex-wrap gap-2">
                        <?php if (!empty($appDetails['skills'])):
                            foreach (explode(', ', $appDetails['skills']) as $skill): ?>
                                <span class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-700">
                                    <i class="fa-solid fa-check-circle text-emerald-500 mr-2"></i><?= htmlspecialchars($skill) ?>
                                </span>
                        <?php endforeach;
                        endif; ?>
                    </div>
                </div>
            </div>

            <!-- KOLOM KANAN: SIDEBAR STICKY -->
            <div class="lg:col-span-4 space-y-6 sticky top-20">

                <!-- PROFIL CARD -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <div class="flex items-center gap-4 mb-6">
                        <?php if (!empty($appDetails['foto'])): ?>
                            <img src="<?= BASE_URL ?>public/uploads/candidate/<?= $appDetails['foto'] ?>" class="w-16 h-16 rounded-2xl object-cover border border-slate-100">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-300 border border-slate-200"><i class="fa-solid fa-user"></i></div>
                        <?php endif; ?>
                        <div>
                            <h3 class="text-base font-black text-slate-800 leading-tight"><?= htmlspecialchars($appDetails['nama_lengkap']) ?></h3>
                            <p class="text-[11px] font-medium text-slate-500"><?= htmlspecialchars($appDetails['email']) ?></p>
                        </div>
                    </div>

                    <div class="space-y-4 mb-6 border-t border-slate-50 pt-6">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-phone text-slate-300 w-4"></i>
                            <span class="text-xs font-bold text-slate-600"><?= $appDetails['no_hp'] ?></span>
                        </div>
                        <div class="flex items-center gap-3 text-sm">
                            <i class="fa-solid fa-venus-mars text-slate-300 w-4"></i>
                            <span class="text-xs font-bold text-slate-600"><?= $appDetails['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                        </div>
                    </div>

                    <?php if (!empty($appDetails['cv_file'])): ?>
                        <a href="<?= BASE_URL ?>public/uploads/cv/<?= $appDetails['cv_file'] ?>" target="_blank"
                            class="flex items-center justify-center gap-3 w-full py-3 bg-blue-900 text-white rounded-xl text-[10px] font-black uppercase tracking-widest hover:bg-blue-800 transition-all shadow-md shadow-blue-900/10">
                            <i class="fa-solid fa-file-pdf"></i> Unduh CV Kandidat
                        </a>
                    <?php endif; ?>
                </div>

                <!-- SERTIFIKASI CARD -->
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm">
                    <h2 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Sertifikasi</h2>
                    <div class="space-y-3">
                        <?php if (mysqli_num_rows($sertifikasiList) > 0):
                            while ($sertifikat = mysqli_fetch_assoc($sertifikasiList)): ?>
                                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center group">
                                    <div>
                                        <p class="text-xs font-black text-slate-700 leading-tight"><?= htmlspecialchars($sertifikat['nama_sertifikasi']) ?></p>
                                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-tighter mt-1"><?= htmlspecialchars($sertifikat['penyelenggara']) ?></p>
                                    </div>
                                    <?php if (!empty($sertifikat['file_sertifikasi'])): ?>
                                        <a href="<?= BASE_URL ?>public/uploads/sertifikasi/<?= $sertifikat['file_sertifikasi'] ?>" target="_blank" class="text-slate-300 hover:text-blue-600 transition-colors">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile;
                        else: ?>
                            <p class="text-[10px] text-slate-400 italic">Tidak ada data.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- ALASAN PENOLAKAN (Jika Ada) -->
                <?php if (strtoupper($appDetails['status_lamaran']) === 'DITOLAK' && !empty($appDetails['tolak_candidate'])): ?>
                    <div class="p-6 bg-rose-50 border border-rose-100 rounded-3xl">
                        <p class="text-[9px] font-black text-rose-600 uppercase tracking-widest mb-2">Respon Pelamar (Tolak Offering)</p>
                        <p class="text-xs italic text-rose-800">"<?= htmlspecialchars($appDetails['tolak_candidate']) ?>"</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>