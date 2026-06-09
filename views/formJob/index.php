<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isHRD() or AuthController::isAdmin() or die("Access denied");

$jobList = JobFormController::getAllJobs($conn);
$jobCount = mysqli_num_rows($jobList);

ob_start();
?>

<style>
    /* Animasi Slide Down dari Atas */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-down {
        animation: slideDown 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    }

    /* Backdrop Blur untuk fokus */
    .modal-backdrop {
        background-color: rgba(15, 23, 42, 0.3);
        backdrop-filter: blur(4px);
    }
</style>
<?php if (isset($_SESSION['success'])): ?>
    <div id="alert-success" class="mb-6 flex items-center justify-between p-4 rounded-2xl border animate-fade-in-down"
        style="background:#F0FDF4;border:1px solid #BBF7D0;color:#166534;">

        <div class="flex items-center gap-3">
            <div class="flex items-center justify-center rounded-full flex-shrink-0"
                style="width:40px;height:40px;background:#DCFCE7;border:1px solid #86EFAC;">
                <span style="font-size:20px;">✅</span>
            </div>

            <div>
                <h4 class="font-bold text-sm">Berhasil!</h4>
                <p class="text-xs"><?= $_SESSION['success'] ?></p>
            </div>
        </div>

        <button onclick="document.getElementById('alert-success').remove()">
            <span class="text-xl px-2">×</span>
        </button>
    </div>

    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<!-- HEADER -->
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-800">Job Posting</h1>
        <p class="text-sm text-slate-500">Kelola semua lowongan pekerjaan</p>
    </div>

    <a href="<?= BASE_URL ?>views/formJob/create.php"
        class="inline-flex items-center gap-2 text-white text-sm font-semibold px-4 py-2 rounded-xl transition hover:opacity-90 shadow-md active:scale-95"
        style="background:#1E3A8A;">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Tambah Job
    </a>
</div>

<!-- TABLE CARD -->
<div class="rounded-2xl overflow-hidden bg-white border border-slate-200 shadow-sm">
    <!-- HEADER TABEL -->
    <div class="flex items-center gap-3 px-6 py-4 border-b border-slate-200">
        <span class="text-sm font-bold text-slate-800">Daftar Lowongan</span>
        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-100">
            <?= $jobCount ?> Total
        </span>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-slate-50">
                <tr class="text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                    <th class="px-6 py-4 font-bold">Informasi Pekerjaan</th>
                    <th class="px-6 py-4 font-bold">Lokasi & Tipe</th>
                    <th class="px-6 py-4 font-bold">Fitur</th>
                    <th class="px-6 py-4 font-bold">Status</th>
                    <th class="px-6 py-4 font-bold">Tanggal</th>
                    <th class="text-right px-6 py-4 font-bold">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">
                <?php foreach ($jobList as $job): ?>
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($job['judul_job']) ?></div>
                            <div class="text-[10px] font-medium text-slate-400">ID: #<?= $job['id'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-slate-700 font-medium"><?= htmlspecialchars($job['lokasi']) ?></div>
                            <div class="text-[11px] text-slate-500 italic"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                <?php if ($job['is_remote_work']): ?>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase">Remote</span>
                                <?php endif; ?>
                                <?php if ($job['is_disabilitas']): ?>
                                    <span class="px-2 py-0.5 text-[9px] font-bold rounded bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase">Inklusif</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($job['status'] === 'draft'): ?>
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-amber-100 text-amber-700">DRAFT</span>
                            <?php elseif ($job['status'] === 'open'): ?>
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-green-100 text-green-700 uppercase">Open</span>
                            <?php else: ?>
                                <span class="px-2 py-1 text-[10px] font-bold rounded-full bg-red-100 text-red-700 uppercase">Closed</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-slate-500 text-xs italic">
                            <?= date('d/m/Y', strtotime($job['created_at'])) ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex justify-end items-center gap-2">
                                <?php if ($job['status'] === 'draft'): ?>
                                    <button onclick="openModal('status', <?= $job['id'] ?>, 'open')"
                                        class="px-3 py-1 text-[11px] font-bold rounded bg-blue-600 text-white hover:bg-blue-700 transition shadow-sm">
                                        PUBLISH
                                    </button>
                                    <a href="edit.php?id=<?= $job['id'] ?>" class="p-1.5 text-slate-400 hover:text-amber-600 transition" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    <button onclick="openModal('delete', <?= $job['id'] ?>)" class="p-1.5 text-slate-400 hover:text-red-600 transition" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                <?php elseif ($job['status'] === 'open'): ?>
                                    <button onclick="openModal('status', <?= $job['id'] ?>, 'closed')"
                                        class="px-3 py-1 text-[11px] font-bold rounded border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 transition">
                                        TUTUP LOKER
                                    </button>
                                <?php endif; ?>
                                <a href="view.php?id=<?= $job['id'] ?>" class="p-1.5 text-slate-400 hover:text-blue-600 transition" title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL KONFIRMASI (FIXED TOP) -->
<div id="confirmModal" class="hidden fixed inset-0 z-[999] flex justify-center items-start p-4 modal-backdrop">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl animate-slide-down overflow-hidden mt-2 border border-slate-100">
        <!-- Progress Bar Indicator (Hiasan) -->
        <div id="modalAccent" class="h-1.5 w-full bg-blue-600"></div>

        <div class="p-6">
            <div class="flex items-start gap-4">
                <div id="modalIcon" class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-2xl shadow-inner">
                </div>
                <div class="flex-1">
                    <h3 id="modalTitle" class="text-lg font-bold text-slate-800 mb-1">Konfirmasi</h3>
                    <p id="modalDesc" class="text-sm text-slate-500 leading-relaxed">Apakah anda yakin ingin melakukan tindakan ini?</p>
                </div>
                <button onclick="closeModal()" class="text-slate-400 hover:text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex justify-end gap-3 mt-8">
                <button onclick="closeModal()" class="px-5 py-2 text-sm font-semibold text-slate-600 bg-slate-50 rounded-xl hover:bg-slate-100 transition border border-slate-200">
                    Batal
                </button>
                <a id="modalConfirmBtn" href="#" class="px-5 py-2 text-sm font-semibold text-white rounded-xl transition shadow-md active:scale-95">
                    Ya, Lanjutkan
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function openModal(type, id, extra = '') {
        const modal = document.getElementById('confirmModal');
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDesc');
        const btn = document.getElementById('modalConfirmBtn');
        const icon = document.getElementById('modalIcon');
        const accent = document.getElementById('modalAccent');

        if (type === 'status') {
            if (extra === 'open') {
                title.innerText = 'Publish Lowongan?';
                desc.innerText = 'Lowongan akan dipublikasikan. Pelamar akan dapat melihat dan mengirimkan lamaran mereka segera.';
                icon.innerText = '🚀';
                icon.className = 'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 shadow-inner';
                accent.className = 'h-1.5 w-full bg-blue-600';
                btn.className = 'px-5 py-2 text-sm font-semibold text-white rounded-xl bg-blue-600 hover:bg-blue-700 transition shadow-md active:scale-95';
                btn.href = "<?= BASE_URL ?>public/actions/handle_update_delete_job.php?status_id=" + id + "&to=open";
            } else {
                title.innerText = 'Tutup Lowongan?';
                desc.innerText = 'Kandidat tidak akan bisa lagi melihat atau melamar pada posisi ini jika Anda menutup loker.';
                icon.innerText = '🔒';
                icon.className = 'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-orange-50 text-orange-600 border border-orange-100 shadow-inner';
                accent.className = 'h-1.5 w-full bg-orange-500';
                btn.className = 'px-5 py-2 text-sm font-semibold text-white rounded-xl bg-orange-600 hover:bg-orange-700 transition shadow-md active:scale-95';
                btn.href = "<?= BASE_URL ?>public/actions/handle_update_delete_job.php?status_id=" + id + "&to=closed";
            }
        } else if (type === 'delete') {
            title.innerText = 'Hapus Permanen?';
            desc.innerText = 'Data pekerjaan ini akan dihapus selamanya. Anda tidak dapat membatalkan tindakan ini setelah dikonfirmasi.';
            icon.innerText = '🗑️';
            icon.className = 'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-red-50 text-red-600 border border-red-100 shadow-inner';
            accent.className = 'h-1.5 w-full bg-red-600';
            btn.className = 'px-5 py-2 text-sm font-semibold text-white rounded-xl bg-red-600 hover:bg-red-700 transition shadow-md active:scale-95';
            btn.href = "<?= BASE_URL ?>public/actions/handle_update_delete_job.php?delete_id=" + id;
        }

        modal.classList.remove('hidden');
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Menutup modal jika klik di luar area modal
    window.onclick = function(event) {
        const modal = document.getElementById('confirmModal');
        if (event.target == modal) {
            closeModal();
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>