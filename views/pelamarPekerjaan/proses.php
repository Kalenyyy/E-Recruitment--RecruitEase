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
<div class="mb-6">
    <h1 class="text-xl font-bold" style="color: #1E293B;">Kelola Tahap Seleksi</h1>
    <p class="text-sm" style="color: #64748B;">Ubah status kelulusan berkas atau wawancara kandidat</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- LEFT COLUMN: RINGKASAN DATA PELAMAR -->
    <div class="md:col-span-1 flex flex-col gap-4">
        <div class="rounded-2xl p-6 shadow-sm border" style="background: #FFFFFF; border-color: #E2E8F0;">
            <div class="text-center pb-4 mb-4 border-b border-slate-100">
                <span class="text-4xl block mb-2">👤</span>
                <h3 class="font-bold text-base text-slate-800"><?= htmlspecialchars($appDetails['nama_lengkap']) ?></h3>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($appDetails['email']) ?></p>
            </div>
            
            <div class="space-y-3 text-xs">
                <div>
                    <span class="block text-slate-400 font-medium">Lowongan yang Dilamar:</span>
                    <span class="font-bold text-slate-700"><?= htmlspecialchars($appDetails['judul_job']) ?></span>
                </div>
                <div>
                    <span class="block text-slate-400 font-medium">Tanggal Masuk Dokumen:</span>
                    <span class="font-semibold text-slate-700"><?= date('d F Y, H:i', strtotime($appDetails['tanggal_melamar'])) ?></span>
                </div>
                <div>
                    <span class="block text-slate-400 font-medium">Status Saat Ini:</span>
                    <span class="inline-block mt-1 px-2.5 py-0.5 font-bold rounded bg-slate-100 text-slate-700">
                        <?= $appDetails['status_lamaran'] ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN: FORM PERUBAHAN STATUS -->
    <div class="md:col-span-2">
        <div class="rounded-2xl shadow-sm border overflow-hidden" style="background: #FFFFFF; border-color: #E2E8F0;">
            <div class="px-6 py-4 flex items-center gap-2" style="border-bottom: 1px solid #F1F5F9; background: #F8FAFC;">
                <span style="font-size:16px;">⚙️</span>
                <h2 class="font-bold text-sm" style="color: #1E293B;">Pilih Tahapan Status Baru</h2>
            </div>
            
            <form action="" method="POST" class="p-6">
                <?php if (isset($error_msg)): ?>
                    <div class="mb-4 p-3 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        ⚠️ <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <div class="flex flex-col gap-2 mb-6">
                    <label class="text-xs font-semibold text-slate-600">Status Seleksi <span class="text-rose-500">*</span></label>
                    <div class="relative">
                        <select name="status_lamaran" required
                            class="w-full px-3 py-2 text-sm rounded-lg outline-none border transition appearance-none bg-slate-50"
                            style="border-color: #CBD5E1; color: #1E293B;">
                            
                            <option value="ADMINISTRASI" <?= $appDetails['status_lamaran'] === 'ADMINISTRASI' ? 'selected' : '' ?>>🟡 ADMINISTRASI (Tahap Sortir Berkas/Awal)</option>
                            <option value="INTERVIEW" <?= $appDetails['status_lamaran'] === 'INTERVIEW' ? 'selected' : '' ?>>🔵 INTERVIEW (Tahap Wawancara)</option>
                            <option value="DITERIMA" <?= $appDetails['status_lamaran'] === 'DITERIMA' ? 'selected' : '' ?>>🟢 DITERIMA (Lolos Seleksi Akhir)</option>
                            <option value="DITOLAK" <?= $appDetails['status_lamaran'] === 'DITOLAK' ? 'selected' : '' ?>>🔴 DITOLAK (Gagal Tahapan)</option>
                        </select>
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Mengubah status ini akan langsung memperbarui catatan di riwayat lamaran milik akun kandidat.</p>
                </div>

                <!-- ACTIONS FOOTER -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/detail.php?job_id=<?= $appDetails['id_lowongan'] ?>"
                        class="px-4 py-2 text-xs font-semibold rounded-lg border text-slate-500 bg-white hover:bg-slate-50 transition"
                        style="border-color: #CBD5E1;">
                        Batal
                    </a>
                    <button type="submit"
                        class="px-5 py-2 text-xs font-bold rounded-lg text-white transition shadow-sm"
                        style="background: #1E3A8A;">
                        💾 Perbarui Status
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>