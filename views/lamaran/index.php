<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
if ($_SESSION['role'] !== 'candidate') {
    die("Access denied.");
}

$candidate = CandidateController::getCandidateByUserId($_SESSION['user_id']);
$daftarLamaran = LamaranController::getCandidateHistory($conn, $_SESSION['user_id']);
$totalLamaran = count($daftarLamaran);

$statusClasses = [
    'administrasi' => 'bg-blue-100 text-blue-700',
    'interview'    => 'bg-amber-100 text-amber-700',
    'offering'     => 'bg-violet-100 text-violet-700',
    'diterima'     => 'bg-emerald-100 text-emerald-700',
    'ditolak'      => 'bg-red-100 text-red-700'
];

ob_start();
?>

<div class="bg-slate-50 min-h-screen py-10">
    <div class="max-w-5xl mx-auto px-6">

        <!-- Notifikasi Sukses/Gagal -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-6 p-4 bg-emerald-100 text-emerald-700 rounded-2xl border border-emerald-200 font-bold text-sm animate-bounce">
                🎉 <?= $_SESSION['success'];
                    unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <!-- Header -->
        <a href="<?= BASE_URL ?>views/lowonganPekerjaan/index.php" class="inline-flex items-center gap-2 text-slate-500 hover:text-indigo-600 font-bold mb-8 transition-colors">
            ← Kembali ke Jelajahi Lowongan
        </a>

        <header class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-10">
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Lamaran Saya</h1>
            <div class="bg-indigo-50 text-indigo-600 px-5 py-2 rounded-full text-sm font-bold shadow-sm">
                <?= $totalLamaran ?> Posisi dilamar
            </div>
        </header>

        <!-- List Lamaran -->
        <div class="flex flex-col gap-5">
            <?php foreach ($daftarLamaran as $item):
                $statusKey = strtolower($item['status_lamaran']);
                $currentStatusClass = $statusClasses[$statusKey] ?? 'bg-slate-100 text-slate-600';
            ?>
                <div class="bg-white border border-slate-200 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all flex flex-col md:flex-row justify-between items-center gap-6 group">
                    <div class="flex-1">
                        <h2 class="text-xl font-black text-slate-900 group-hover:text-indigo-600 transition-colors"><?= htmlspecialchars($item['judul_job']) ?></h2>
                        <p class="text-slate-500 text-sm font-medium"><?= htmlspecialchars($item['lokasi']) ?> • <?= htmlspecialchars($item['tipe_pekerjaan']) ?></p>
                    </div>
                    <div class="flex flex-col items-end gap-3">
                        <span class="px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest border <?= $currentStatusClass ?>">
                            <?= $item['status_lamaran'] ?>
                        </span>
                        <button onclick="bukaModalDetail(<?= htmlspecialchars(json_encode($item)) ?>)" class="text-xs font-black text-slate-600 border-2 border-slate-100 px-6 py-2.5 rounded-2xl hover:bg-slate-900 hover:text-white transition-all shadow-sm">Lihat Detail</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- MODAL DETAIL UTAMA -->
<div id="modalDetailLamaran" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[50] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300" onclick="if(event.target===this) tutupModalDetail()">
    <div class="bg-white rounded-[2.5rem] w-full max-w-lg p-8 shadow-2xl transform translate-y-8 transition-transform duration-300 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-start border-b border-slate-100 pb-6 mb-6">
            <h2 id="m-judul-job" class="text-2xl font-black text-slate-900 tracking-tight">Detail Lamaran</h2>
            <button onclick="tutupModalDetail()" class="w-10 h-10 flex items-center justify-center rounded-full bg-slate-50 text-slate-400 hover:bg-slate-100 transition-colors">✕</button>
        </div>

        <div class="space-y-8">
            <!-- SEKSI OFFERING -->
            <div id="section-offering" class="hidden">
                <div class="bg-violet-50 border-2 border-violet-100 rounded-[2rem] p-6 shadow-inner">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 bg-violet-600 text-white rounded-xl flex items-center justify-center text-xl shadow-lg shadow-violet-200">🎉</div>
                        <h3 class="text-violet-900 font-black text-xs uppercase tracking-[0.2em]">Penawaran Kerja</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-6 mb-8">
                        <div>
                            <label class="text-[10px] font-black text-violet-400 uppercase tracking-widest block mb-1">Gaji Penawaran</label>
                            <p id="m-gaji-offering" class="text-2xl font-black text-slate-900"></p>
                        </div>
                        <a id="m-download-pdf" href="#" target="_blank" class="flex justify-center items-center gap-3 w-full bg-white border-2 border-violet-200 text-violet-700 font-black py-4 rounded-2xl hover:bg-violet-600 hover:text-white hover:border-violet-600 transition-all shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                        </a>
                    </div>

                    <!-- Tombol Aksi -->
                    <div id="offering-actions" class="grid grid-cols-2 gap-4 hidden">
                        <button type="button" onclick="bukaConfirm('DITERIMA')" class="bg-emerald-600 text-white font-black py-4 rounded-2xl shadow-lg shadow-emerald-100 hover:bg-emerald-700 active:scale-95 transition-all">Terima</button>
                        <button type="button" onclick="bukaConfirm('DITOLAK')" class="bg-white border-2 border-rose-100 text-rose-600 font-black py-4 rounded-2xl hover:bg-rose-50 active:scale-95 transition-all">Tolak</button>
                    </div>

                    <p id="offering-responded" class="hidden text-center text-[11px] font-bold text-violet-500 bg-white/50 py-3 rounded-xl border border-violet-100 italic uppercase tracking-wider">
                        Sudah direspon
                    </p>
                </div>
            </div>

            <!-- Detail Lamaran -->
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Keahlian</label>
                    <p id="m-expert" class="font-bold text-slate-800"></p>
                </div>
                <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Pengalaman</label>
                    <p id="m-pengalaman" class="font-bold text-slate-800"></p>
                </div>
            </div>
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Catatan Lamaran</label>
                <div id="m-catatan" class="text-sm text-slate-600 leading-relaxed font-medium"></div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI (NEW & COOL) -->
<div id="confirmModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[100] hidden flex items-center justify-center p-4 opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[3rem] w-full max-w-sm p-8 shadow-2xl transform scale-90 transition-transform duration-300">
        <div id="confirmIcon" class="w-20 h-20 mx-auto mb-6 rounded-3xl flex items-center justify-center text-4xl shadow-xl"></div>

        <div class="text-center mb-8">
            <h3 id="confirmTitle" class="text-2xl font-black text-slate-900 mb-2"></h3>
            <p id="confirmText" class="text-slate-500 text-sm font-medium leading-relaxed"></p>
        </div>

        <form action="<?= BASE_URL ?>public/actions/respond_offering.php" method="POST" class="space-y-3">
            <input type="hidden" name="id_transaksi" id="confirm-id">
            <input type="hidden" name="respon" id="confirm-respon">

            <button type="submit" id="confirmBtn" class="w-full py-4 rounded-2xl text-white font-black shadow-lg transition-all active:scale-95 uppercase tracking-widest text-xs"></button>
            <button type="button" onclick="tutupConfirm()" class="w-full py-4 rounded-2xl text-slate-400 font-bold hover:bg-slate-50 transition-all uppercase tracking-widest text-[10px]">Batalkan</button>
        </form>
    </div>
</div>

<script>
    let currentData = null;

    function bukaModalDetail(data) {
        currentData = data;
        const modal = document.getElementById('modalDetailLamaran');
        const modalBox = modal.querySelector('div');

        // Reset
        document.getElementById('section-offering').classList.add('hidden');
        document.getElementById('offering-actions').classList.add('hidden');
        document.getElementById('offering-responded').classList.add('hidden');

        // Data Dasar
        document.getElementById('m-judul-job').textContent = data.judul_job;
        document.getElementById('m-expert').textContent = data.expert_bidang || '-';
        document.getElementById('m-pengalaman').textContent = data.pengalaman_bidang || '-';
        document.getElementById('m-catatan').textContent = data.catatan || 'Tidak ada catatan khusus.';

        // Logic Offering
        if (data.status_lamaran.toUpperCase() === 'OFFERING') {
            document.getElementById('section-offering').classList.remove('hidden');
            document.getElementById('m-download-pdf').href = `<?= BASE_URL ?>public/uploads/offering/${data.file_offering}`;

            const gaji = new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(data.gaji_offering);
            document.getElementById('m-gaji-offering').textContent = gaji;

            if (data.status_respon_offering) {
                document.getElementById('offering-responded').classList.remove('hidden');
            } else {
                document.getElementById('offering-actions').classList.remove('hidden');
            }
        }

        modal.classList.remove('hidden');
        setTimeout(() => {
            modal.classList.add('opacity-100');
            modalBox.classList.remove('translate-y-8');
        }, 10);
        document.body.style.overflow = 'hidden';
    }

    function tutupModalDetail() {
        const modal = document.getElementById('modalDetailLamaran');
        modal.classList.remove('opacity-100');
        modal.querySelector('div').classList.add('translate-y-8');
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
        document.body.style.overflow = '';
    }

    // LOGIC KONFIRMASI MODERN
    function bukaConfirm(type) {
        const confirmModal = document.getElementById('confirmModal');
        const confirmBox = confirmModal.querySelector('div');
        const icon = document.getElementById('confirmIcon');
        const title = document.getElementById('confirmTitle');
        const text = document.getElementById('confirmText');
        const btn = document.getElementById('confirmBtn');

        document.getElementById('confirm-id').value = currentData.id;
        document.getElementById('confirm-respon').value = type;

        if (type === 'DITERIMA') {
            icon.className = "w-20 h-20 mx-auto mb-6 rounded-3xl flex items-center justify-center text-4xl shadow-xl bg-emerald-100 text-emerald-600";
            icon.innerHTML = "🤝";
            title.innerHTML = "Terima Pekerjaan?";
            text.innerHTML = "Pastikan Anda telah membaca seluruh syarat di Offering Letter. Keputusan ini akan mengubah status Anda menjadi karyawan.";
            btn.className = "w-full py-4 rounded-2xl bg-emerald-600 text-white font-black shadow-lg shadow-emerald-100 hover:bg-emerald-700 transition-all active:scale-95 uppercase tracking-widest text-xs";
            btn.textContent = "Ya, Saya Terima";
        } else {
            icon.className = "w-20 h-20 mx-auto mb-6 rounded-3xl flex items-center justify-center text-4xl shadow-xl bg-rose-100 text-rose-600";
            icon.innerHTML = "✕";
            title.innerHTML = "Tolak Penawaran?";
            text.innerHTML = "Tindakan ini tidak dapat dibatalkan. Berikan kesempatan ini untuk kandidat lain jika Anda merasa tidak cocok.";
            btn.className = "w-full py-4 rounded-2xl bg-rose-600 text-white font-black shadow-lg shadow-rose-100 hover:bg-rose-700 transition-all active:scale-95 uppercase tracking-widest text-xs";
            btn.textContent = "Ya, Saya Tolak";
        }

        confirmModal.classList.remove('hidden');
        setTimeout(() => {
            confirmModal.classList.add('opacity-100');
            confirmBox.classList.remove('scale-90');
        }, 10);
    }

    function tutupConfirm() {
        const confirmModal = document.getElementById('confirmModal');
        confirmModal.classList.remove('opacity-100');
        confirmModal.querySelector('div').classList.add('scale-90');
        setTimeout(() => {
            confirmModal.classList.add('hidden');
        }, 300);
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>