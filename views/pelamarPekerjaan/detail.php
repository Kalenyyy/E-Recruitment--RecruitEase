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

<!-- BREADCRUMB & HEADER -->
<div class="max-w-6xl mx-auto px-4 sm:px-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-2 font-medium">
                <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php" class="hover:text-blue-700">Manajemen Lowongan</a>
                <span>/</span>
                <span class="text-slate-900">Daftar Pelamar</span>
            </nav>
            <h1 class="text-3xl font-black text-slate-900 tracking-tight">Pelamar: <?= htmlspecialchars($jobDetails['judul_job']) ?></h1>
            <p class="text-slate-500 font-medium">Total <span class="text-blue-600 font-bold"><?= count($applicants) ?></span> kandidat dalam database.</p>
        </div>
        <a href="<?= BASE_URL ?>views/pelamarPekerjaan/index.php" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-2xl hover:bg-slate-50 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
    </div>

    <!-- MAIN LIST -->
    <div class="space-y-4">
        <?php if (empty($applicants)): ?>
            <div class="bg-white border-2 border-dashed border-slate-200 rounded-[32px] p-20 text-center">
                <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 text-slate-300 text-4xl">👥</div>
                <h3 class="text-xl font-bold text-slate-800">Belum Ada Pelamar</h3>
                <p class="text-slate-500">Lowongan ini belum menerima lamaran dari kandidat manapun.</p>
            </div>
        <?php else: ?>
            <?php foreach ($applicants as $app):
                $status = $app['status_lamaran'];
                $statusConfig = match ($status) {
                    'ADMINISTRASI' => ['color' => 'bg-amber-100 text-amber-700 border-amber-200', 'label' => 'Administrasi', 'accent' => 'border-l-amber-500'],
                    'INTERVIEW'    => ['color' => 'bg-blue-100 text-blue-700 border-blue-200', 'label' => 'Interview', 'accent' => 'border-l-blue-500'],
                    'OFFERING'     => ['color' => 'bg-violet-100 text-violet-700 border-violet-200', 'label' => 'Offering', 'accent' => 'border-l-violet-500'],
                    'DITERIMA'     => ['color' => 'bg-emerald-100 text-emerald-700 border-emerald-200', 'label' => 'Diterima', 'accent' => 'border-l-emerald-500'],
                    'DITOLAK'      => ['color' => 'bg-rose-100 text-rose-700 border-rose-200', 'label' => 'Ditolak', 'accent' => 'border-l-rose-500'],
                    default        => ['color' => 'bg-slate-100 text-slate-700 border-slate-200', 'label' => $status, 'accent' => 'border-l-slate-400']
                };
            ?>
                <div class="bg-white border border-slate-200 border-l-4 <?= $statusConfig['accent'] ?> rounded-[24px] p-6 shadow-sm hover:shadow-md transition-all group">
                    <div class="flex flex-col lg:flex-row gap-8 items-start lg:items-center">

                        <!-- Profile Info -->
                        <div class="flex items-center gap-5 flex-1 min-w-0">
                            <div class="w-16 h-16 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-2xl font-black shadow-lg shrink-0 uppercase">
                                <?= substr($app['nama_lengkap'], 0, 1) ?>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-lg font-extrabold text-slate-900 truncate group-hover:text-blue-700 transition-colors"><?= htmlspecialchars($app['nama_lengkap']) ?></h3>
                                <p class="text-sm text-slate-400 font-medium mt-0.5 tracking-tight">Melamar pada <?= date('d M Y', strtotime($app['tanggal_melamar'])) ?></p>
                                <div class="flex items-center gap-4 mt-2">
                                    <span class="text-xs text-slate-500 flex items-center gap-1.5 font-semibold">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                        <?= htmlspecialchars($app['email']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Qualification -->
                        <div class="flex flex-wrap gap-3 lg:w-72">
                            <div class="bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold border border-slate-100 flex-1 text-center">
                                <p class="text-[9px] uppercase opacity-60 mb-0.5 tracking-widest">Expertise</p>
                                <?= htmlspecialchars($app['expert_bidang'] ?: '-') ?>
                            </div>
                            <div class="bg-slate-50 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold border border-slate-100 flex-1 text-center">
                                <p class="text-[9px] uppercase opacity-60 mb-0.5 tracking-widest">Experience</p>
                                <?= htmlspecialchars($app['pengalaman_bidang'] ?: '-') ?>
                            </div>
                        </div>

                        <!-- Status & Actions -->
                        <div class="flex flex-row lg:flex-col items-center lg:items-end gap-4 shrink-0 w-full lg:w-auto">
                            <span class="px-4 py-1.5 rounded-full text-[10px] font-black tracking-widest uppercase border <?= $statusConfig['color'] ?>">
                                <?= $statusConfig['label'] ?>
                            </span>

                            <div class="flex items-center gap-2 ml-auto lg:ml-0">
                                <a href="<?= BASE_URL ?>views/pelamarPekerjaan/riwayat_pelamar.php?id_transaksi=<?= $app['id_transaksi'] ?>" class="p-2.5 bg-slate-100 text-slate-600 rounded-xl hover:bg-blue-600 hover:text-white transition shadow-sm" title="Lihat Profil Lengkap">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>

                                <?php if ($status === 'ADMINISTRASI'): ?>
                                    <button type="button" onclick="openStatusModal('INTERVIEW', '<?= $app['id_transaksi'] ?>', '<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')" class="bg-emerald-600 text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-emerald-700 transition shadow-lg shadow-emerald-100">Lolos</button>
                                    <button type="button" onclick="openStatusModal('DITOLAK', '<?= $app['id_transaksi'] ?>', '<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')" class="bg-rose-50 text-rose-600 px-5 py-2.5 rounded-xl text-xs font-bold border border-rose-100 hover:bg-rose-100 transition">Tolak</button>
                                <?php endif; ?>

                                <?php if ($status === 'INTERVIEW'): ?>
                                    <button onclick="openOfferingModal('<?= $app['id_transaksi'] ?>','<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')" class="bg-violet-600 text-white px-5 py-2.5 rounded-xl font-bold text-xs hover:bg-violet-700 transition shadow-lg shadow-violet-100 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Kirim Offering
                                    </button>
                                    <button onclick="openStatusModal('DITOLAK','<?= $app['id_transaksi'] ?>','<?= htmlspecialchars($app['nama_lengkap'], ENT_QUOTES) ?>')" class="bg-rose-50 text-rose-600 border border-rose-100 px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-rose-100 transition">Gagal</button>
                                <?php endif; ?>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- MODAL KONFIRMASI STATUS -->
<div id="statusModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[32px] w-full max-w-lg shadow-2xl overflow-hidden transform translate-y-10 transition-transform duration-300">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
            <h2 id="modalTitle" class="text-xl font-black text-slate-900">Konfirmasi Seleksi</h2>
            <button onclick="closeModal('statusModal')" class="p-2 text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form id="modalForm" method="POST" class="p-8 space-y-6">
            <input type="hidden" name="status_lamaran" id="inputStatus">
            <div id="modalInfoBox" class="p-5 rounded-2xl border-2 flex items-center gap-4">
                <div id="modalIcon" class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 font-bold"></div>
                <div>
                    <p id="modalKandidat" class="font-black text-slate-900 text-lg leading-tight"></p>
                    <p id="modalSubtext" class="text-sm font-medium text-slate-500 mt-0.5"></p>
                </div>
            </div>
            <div id="interviewFields" class="hidden animate-fadeIn">
                <div class="p-6 bg-blue-50 rounded-2xl border border-blue-100 space-y-4">
                    <label class="block text-[10px] font-black text-blue-600 uppercase tracking-widest">Jadwal Wawancara</label>
                    <input type="datetime-local" name="tanggal_interview" id="tanggalInput" class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm font-bold focus:border-blue-600 outline-none">
                    <textarea name="catatan" rows="2" placeholder="Catatan (Lokasi/Link Zoom)..." class="w-full px-4 py-3 bg-white border-2 border-slate-200 rounded-xl text-sm outline-none focus:border-blue-600 resize-none"></textarea>
                </div>
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="closeModal('statusModal')" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold">Batal</button>
                <button type="submit" id="submitBtn" class="flex-1 px-6 py-4 text-white rounded-2xl text-sm font-bold transition shadow-lg active:scale-95"></button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL OFFERING LETTER -->
<div id="offeringModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[32px] w-full max-w-lg shadow-2xl overflow-hidden transform translate-y-10 transition-transform duration-300">
        <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-violet-50/50">
            <h2 class="text-xl font-black text-violet-900 tracking-tight">Kirim Offering Letter</h2>
            <button onclick="closeModal('offeringModal')" class="p-2 text-violet-400 hover:text-violet-600">✕</button>
        </div>

        <form action="<?= BASE_URL ?>public/actions/create_offering.php" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
            <input type="hidden" name="id_transaksi" id="offering_id_transaksi">

            <div class="flex items-center gap-4 p-4 bg-violet-50 rounded-2xl border border-violet-100">
                <div class="w-12 h-12 bg-violet-600 text-white rounded-xl flex items-center justify-center text-xl">📄</div>
                <div>
                    <p id="offering_kandidat_name" class="font-bold text-slate-900"></p>
                    <p class="text-xs text-violet-600 font-semibold uppercase tracking-wider">Tahap Penawaran Kerja</p>
                </div>
            </div>

            <div class="space-y-4">
                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Gaji yang Ditawarkan (IDR)</label>
                    <div class="relative group">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400 group-focus-within:text-violet-600">Rp</span>
                        <input type="number" name="gaji_offering" required placeholder="Contoh: 5000000" class="w-full pl-12 pr-4 py-4 bg-slate-50 border-2 border-slate-200 rounded-2xl text-lg font-black text-slate-800 outline-none focus:border-violet-600 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-black text-slate-500 uppercase tracking-widest ml-1">Dokumen Surat Penawaran (PDF)</label>
                    <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-[24px] cursor-pointer hover:bg-slate-50 hover:border-violet-400 transition-all">
                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                            <svg class="w-8 h-8 mb-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm text-slate-500 font-bold" id="fileNameDisplay">Klik untuk upload file PDF</p>
                        </div>
                        <input type="file" name="file_offering" class="hidden" accept=".pdf" required onchange="updateFileName(this)">
                    </label>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeModal('offeringModal')" class="flex-1 px-6 py-4 bg-slate-100 text-slate-600 rounded-2xl text-sm font-bold">Batal</button>
                <button type="submit" class="flex-1 px-6 py-4 bg-violet-600 text-white rounded-2xl text-sm font-bold shadow-lg shadow-violet-100 hover:bg-violet-700 active:scale-95 transition">Kirim Penawaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openStatusModal(action, id_transaksi, name) {
        const modal = document.getElementById('statusModal');
        const modalBox = modal.querySelector('div');
        const isLolos = action === 'INTERVIEW';

        document.getElementById('inputStatus').value = action;
        document.getElementById('modalForm').action = `<?= BASE_URL ?>public/actions/update_status_interview.php?id_transaksi=${id_transaksi}`;
        document.getElementById('modalKandidat').textContent = name;

        const infoBox = document.getElementById('modalInfoBox');
        const iconDiv = document.getElementById('modalIcon');
        const submitBtn = document.getElementById('submitBtn');

        if (isLolos) {
            document.getElementById('modalTitle').textContent = 'Loloskan Tahap Seleksi';
            document.getElementById('modalSubtext').textContent = 'Kandidat akan lanjut ke tahap interview.';
            infoBox.className = 'p-5 rounded-2xl border-2 border-emerald-100 bg-emerald-50 flex items-center gap-4 text-emerald-800';
            iconDiv.className = 'w-12 h-12 rounded-xl bg-emerald-600 text-white flex items-center justify-center';
            iconDiv.innerHTML = '✓';
            submitBtn.className = 'flex-1 px-6 py-4 bg-emerald-600 text-white rounded-2xl text-sm font-bold hover:bg-emerald-700 transition';
            submitBtn.textContent = 'Ya, Atur Jadwal';
            document.getElementById('interviewFields').classList.remove('hidden');
            document.getElementById('tanggalInput').required = true;
            setMinDate();
        } else {
            document.getElementById('modalTitle').textContent = 'Tolak Kandidat';
            document.getElementById('modalSubtext').textContent = 'Berikan keputusan untuk menolak pelamar ini.';
            infoBox.className = 'p-5 rounded-2xl border-2 border-rose-100 bg-rose-50 flex items-center gap-4 text-rose-800';
            iconDiv.className = 'w-12 h-12 rounded-xl bg-rose-600 text-white flex items-center justify-center';
            iconDiv.innerHTML = '✕';
            submitBtn.className = 'flex-1 px-6 py-4 bg-rose-600 text-white rounded-2xl text-sm font-bold hover:bg-rose-700 transition';
            submitBtn.textContent = 'Ya, Tolak';
            document.getElementById('interviewFields').classList.add('hidden');
            document.getElementById('tanggalInput').required = false;
        }

        showModalUI(modal, modalBox);
    }

    function openOfferingModal(id_transaksi, name) {
        const modal = document.getElementById('offeringModal');
        const modalBox = modal.querySelector('div');
        document.getElementById('offering_id_transaksi').value = id_transaksi;
        document.getElementById('offering_kandidat_name').textContent = name;
        showModalUI(modal, modalBox);
    }

    function showModalUI(modal, modalBox) {
        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalBox.classList.remove('translate-y-10');
        }, 10);
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        const modalBox = modal.querySelector('div');
        modal.classList.remove('opacity-100');
        modalBox.classList.add('translate-y-10');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }

    function updateFileName(input) {
        const name = input.files[0] ? input.files[0].name : 'Klik untuk upload file PDF';
        document.getElementById('fileNameDisplay').textContent = name;
    }

    function setMinDate() {
        const now = new Date();
        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
        document.getElementById('tanggalInput').min = now.toISOString().slice(0, 16);
    }

    window.onclick = (e) => {
        if (e.target.id.includes('Modal')) closeModal(e.target.id);
    }
</script>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.4s ease-out forwards;
    }
</style>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>