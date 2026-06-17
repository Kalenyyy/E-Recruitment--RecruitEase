<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/PelamarPekerjaanController.php';

// Proteksi Akses
AuthController::requireLogin();
AuthController::isHRD() or die("Access denied");

$job_id = $_GET['job_id'] ?? null;

if (!$job_id || !is_numeric($job_id)) {
    header("Location: " . BASE_URL . "views/pelamarPekerjaan/index.php");
    exit;
}

// Ambil data detail lowongan dan daftar pelamarnya
$jobDetails = PelamarPekerjaanController::getDetailJob($conn, $job_id);
$applicants = PelamarPekerjaanController::getApplicants($conn, $job_id);

if (!$jobDetails) {
    die("Data lowongan tidak ditemukan.");
}

ob_start();
?>

<div class="flex items-center justify-between mb-6 font-sans">
    <div>
        <h1 class="text-3xl font-bold text-slate-900">📋 Pelamar: <?= htmlspecialchars($jobDetails['judul_job']) ?></h1>
        <p class="text-sm text-slate-600 mt-2">📌 Posisi: <span class="font-semibold"><?= htmlspecialchars($jobDetails['nama_posisi']) ?></span> | Kelola berkas dan tahap seleksi kandidat</p>
    </div>
    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php"
        class="inline-flex items-center gap-2 text-sm font-semibold px-5 py-3 rounded-xl transition hover:shadow-md"
        style="background: linear-gradient(135deg, #F1F5F9, #E2E8F0); color: #475569; border: 1px solid #CBD5E1;">
        ← Kembali
    </a>
</div>

<div class="rounded-2xl overflow-hidden shadow-md" style="background: linear-gradient(135deg, #FFFFFF, #F8FAFC); border: 1px solid #E2E8F0;">
    <div class="px-8 py-6 flex items-center justify-between" style="background: linear-gradient(135deg, #1E3A8A, #2563EB);">
        <div class="flex items-center gap-3">
            <span class="text-2xl">📂</span>
            <div>
                <h2 class="font-bold text-lg text-white">Daftar Kandidat</h2>
                <p class="text-xs text-slate-300 mt-1">Total pelamar: <span class="font-bold text-blue-300"><?= count($applicants) ?></span> kandidat</p>
            </div>
        </div>
    </div>

    <div class="p-8">
        <?php if (empty($applicants)): ?>
            <div class="text-center py-16">
                <span class="text-6xl block mb-4">📥</span>
                <p class="text-lg font-semibold text-slate-700">Belum ada pelamar untuk lowongan ini</p>
                <p class="text-sm text-slate-500 mt-2">Data pelamar baru akan muncul begitu kandidat menekan tombol kirim lamaran.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($applicants as $app): ?>
                    <div class="group rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg transition-all duration-300 hover:border-blue-300">
                        <div class="p-6" style="background: #FFFFFF;">
                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                                <!-- KANDIDAT INFO -->
                                <div class="lg:col-span-1">
                                    <div class="flex items-start gap-4">
                                        <div class="w-14 h-14 rounded-full flex-shrink-0 flex items-center justify-center font-bold text-lg text-white"
                                             style="background: linear-gradient(135deg, #3B82F6, #1E40AF);">
                                            <?= strtoupper(substr($app['nama_lengkap'], 0, 1)) ?>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-bold text-slate-900 text-base leading-tight">
                                                <?= htmlspecialchars($app['nama_lengkap']) ?>
                                            </h3>
                                            <p class="text-xs text-slate-500 mt-1.5 truncate">
                                                📧 <?= htmlspecialchars($app['email']) ?>
                                            </p>
                                            <p class="text-xs text-slate-500 truncate">
                                                📞 <?= htmlspecialchars($app['no_hp']) ?>
                                            </p>
                                            <p class="text-xs text-slate-400 mt-1">
                                                📅 <?= date('d M Y H:i', strtotime($app['tanggal_melamar'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- KUALIFIKASI -->
                                <div class="lg:col-span-1.5">
                                    <div class="space-y-2.5">
                                        <div class="p-3 rounded-xl" style="background: #F0F4F8; border: 1px solid #D1D5DB;">
                                            <span class="block text-[10px] font-extrabold tracking-wider uppercase text-blue-600 mb-1">💻 Keahlian</span>
                                            <p class="text-sm font-semibold text-slate-800">
                                                <?= (!empty($app['expert_bidang']) && $app['expert_bidang'] !== '-') ? htmlspecialchars($app['expert_bidang']) : '<span class="text-slate-400 font-normal italic">Tidak ditentukan</span>' ?>
                                            </p>
                                        </div>

                                        <div class="p-3 rounded-xl" style="background: #FFFDF5; border: 1px solid #FDE68A;">
                                            <span class="block text-[10px] font-extrabold tracking-wider uppercase text-amber-600 mb-1">⏱️ Pengalaman</span>
                                            <p class="text-sm font-semibold text-amber-900">
                                                <?= (!empty($app['pengalaman_bidang']) && $app['pengalaman_bidang'] !== '-') ? htmlspecialchars($app['pengalaman_bidang']) : '<span class="text-amber-600/60 font-normal italic">Fresh Graduate</span>' ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- STATUS -->
                                <div class="lg:col-span-0.75 flex items-center justify-center">
                                    <?php 
                                    $status = $app['status_lamaran'];
                                    $badgeStyle = "bg-slate-100 text-slate-700"; 
                                    $statusIcon = "⏳";
                                    if ($status === 'ADMINISTRASI') {
                                        $badgeStyle = "bg-amber-100 text-amber-800 border border-amber-300";
                                        $statusIcon = "📋";
                                    }
                                    if ($status === 'INTERVIEW') {
                                        $badgeStyle = "bg-blue-100 text-blue-800 border border-blue-300";
                                        $statusIcon = "🎤";
                                    }
                                    if ($status === 'DITERIMA') {
                                        $badgeStyle = "bg-emerald-100 text-emerald-800 border border-emerald-300";
                                        $statusIcon = "✅";
                                    }
                                    if ($status === 'DITOLAK') {
                                        $badgeStyle = "bg-rose-100 text-rose-800 border border-rose-300";
                                        $statusIcon = "❌";
                                    }
                                    ?>
                                    <div class="text-center">
                                        <div class="text-2xl mb-1"><?= $statusIcon ?></div>
                                        <span class="inline-block px-3 py-2 text-xs font-bold rounded-full <?= $badgeStyle ?>">
                                            <?= $status ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- AKSI -->
                                <div class="lg:col-span-0.75 flex flex-col gap-2">
                                    <?php if (!empty($app['cv_file'])): ?>
                                        <a href="<?= BASE_URL . 'public/uploads/cv/' . $app['cv_file'] ?>" target="_blank"
                                           class="inline-flex items-center justify-center gap-1.5 text-xs font-bold px-3 py-2 rounded-lg transition hover:shadow-md"
                                           style="background: #F1F5F9; color: #475569; border: 1px solid #E2E8F0;">
                                            📄 CV
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic text-center py-2">-</span>
                                    <?php endif; ?>

                                    <a href="<?= BASE_URL ?>views/pelamarPekerjaan/riwayat_pelamar.php?id_transaksi=<?= $app['id_transaksi'] ?>"
                                       class="inline-flex items-center justify-center gap-1 text-xs font-bold px-3 py-2 rounded-lg text-white transition hover:shadow-lg"
                                       style="background: linear-gradient(135deg, #3B82F6, #1E40AF);">
                                        ⚙️ Detail
                                    </a>

                                    <?php if ($status === 'ADMINISTRASI'): ?>
                                        <div class="grid grid-cols-2 gap-1.5">
                                            <button type="button"
                                                data-action="INTERVIEW"
                                                data-transaksi="<?= $app['id_transaksi'] ?>"
                                                data-name="<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>"
                                                class="js-open-status-modal text-[10px] font-bold uppercase px-2 py-1.5 rounded-lg text-white transition hover:shadow-md"
                                                style="background: linear-gradient(135deg, #10B981, #059669);">
                                                ✓ Lolos
                                            </button>
                                            <button type="button"
                                                data-action="DITOLAK"
                                                data-transaksi="<?= $app['id_transaksi'] ?>"
                                                data-name="<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>"
                                                class="js-open-status-modal text-[10px] font-bold uppercase px-2 py-1.5 rounded-lg text-white transition hover:shadow-md"
                                                style="background: linear-gradient(135deg, #EF4444, #DC2626);">
                                                ✗ Tolak
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div id="status-confirm-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 px-4 py-6 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl transform transition-all">
        <div class="flex items-start justify-between gap-4 mb-6">
            <div>
                <h3 id="modal-title" class="text-2xl font-bold text-slate-900">Konfirmasi Status</h3>
            </div>
            <button type="button" id="modal-close" class="text-slate-400 hover:text-slate-600 text-2xl">✕</button>
        </div>

        <form id="status-confirm-form" method="POST" class="space-y-6">
            <input type="hidden" name="status_lamaran" id="modal-status" value="">
            
            <div class="rounded-2xl border-2 p-5" id="modal-info-box">
                <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-3">Informasi Pelamar</p>
                <p id="modal-candidate-name" class="text-lg font-bold text-slate-900 mb-2"></p>
                <p id="modal-action-label" class="text-sm text-slate-600"></p>
            </div>

            <div class="rounded-2xl p-4" id="modal-status-box" style="background: #F0F4F8; border: 1px solid #BFDBFE;">
                <p class="text-xs font-bold uppercase tracking-widest text-blue-700 mb-2">Status yang akan diubah</p>
                <div class="flex items-center gap-2">
                    <span id="modal-status-icon" class="text-2xl">📋</span>
                    <p id="modal-status-text" class="font-bold text-blue-900"></p>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="button" id="modal-cancel" class="flex-1 rounded-xl border-2 border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50 transition-all">
                    Batal
                </button>
                <button type="submit" id="modal-confirm" class="flex-1 rounded-xl text-white px-4 py-3 text-sm font-bold transition-all hover:shadow-lg"
                        style="background: linear-gradient(135deg, #1E40AF, #1E3A8A);">
                    Ya, Lanjutkan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const modal = document.getElementById('status-confirm-modal');
    const openButtons = document.querySelectorAll('.js-open-status-modal');
    const closeButton = document.getElementById('modal-close');
    const cancelButton = document.getElementById('modal-cancel');
    const modalTitle = document.getElementById('modal-title');
    const candidateName = document.getElementById('modal-candidate-name');
    const actionLabel = document.getElementById('modal-action-label');
    const modalStatus = document.getElementById('modal-status');
    const modalStatusText = document.getElementById('modal-status-text');
    const modalStatusIcon = document.getElementById('modal-status-icon');
    const modalInfoBox = document.getElementById('modal-info-box');
    const modalStatusBox = document.getElementById('modal-status-box');
    const form = document.getElementById('status-confirm-form');

    function closeModal() {
        modal.classList.add('hidden');
    }

    function openModal(action, transaksiId, name) {
        const isLolos = action === 'INTERVIEW';
        const label = isLolos ? 'Lolos Seleksi' : 'Ditolak';
        const icon = isLolos ? '✅' : '❌';
        
        modalTitle.textContent = isLolos ? '🎉 Lolos Tahap Seleksi?' : '⚠️ Tolak Pelamar?';
        candidateName.textContent = name;
        actionLabel.textContent = isLolos 
            ? '✓ Pelamar akan dilanjutkan ke tahap wawancara'
            : '✗ Pelamar tidak lolos dari tahap administrasi';
        
        modalStatus.value = action;
        modalStatusText.textContent = label;
        modalStatusIcon.textContent = icon;
        
        if (isLolos) {
            modalInfoBox.style.borderColor = '#D1FAE5';
            modalInfoBox.style.background = '#F0FDF4';
            modalStatusBox.style.background = '#F0FDF4';
            modalStatusBox.style.borderColor = '#86EFAC';
            modalStatusBox.style.color = '#166534';
        } else {
            modalInfoBox.style.borderColor = '#FECACA';
            modalInfoBox.style.background = '#FEF2F2';
            modalStatusBox.style.background = '#FEF2F2';
            modalStatusBox.style.borderColor = '#FCA5A5';
            modalStatusBox.style.color = '#7F1D1D';
        }
        
        form.action = '<?= BASE_URL ?>views/pelamarPekerjaan/riwayat_pelamar.php?id_transaksi=' + encodeURIComponent(transaksiId);
        modal.classList.remove('hidden');
    }

    openButtons.forEach(button => {
        button.addEventListener('click', function() {
            openModal(this.dataset.action, this.dataset.transaksi, this.dataset.name);
        });
    });

    closeButton.addEventListener('click', closeModal);
    cancelButton.addEventListener('click', closeModal);

    window.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
})();
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>