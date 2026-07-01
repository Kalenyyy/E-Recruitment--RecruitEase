<?php
require_once __DIR__ . '/../../init.php';

AuthController::requireLogin();
AuthController::isHRD() or AuthController::isAdmin() or die("Access denied");

// --- LOGIKA SEARCH & PAGINATION ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$perPage = 7; // Jumlah data per halaman
$totalData = JobFormController::getTotalCount($conn, $search);
$totalPages = ($totalData > 0) ? ceil($totalData / $perPage) : 1;

$jobList = JobFormController::getPaginated($conn, $page, $perPage, $search);
$jobCountInPage = mysqli_num_rows($jobList);

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

<div>

    <?php if (isset($_SESSION['success'])): ?>
        <div id="alert-success" class="mb-6 flex items-center justify-between p-4 rounded-xl border border-green-100 bg-green-50 text-green-800 shadow-sm animate-slide-down">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">✅</span>
                <div>
                    <h4 class="font-bold text-sm tracking-tight">Berhasil!</h4>
                    <p class="text-xs text-green-700 mt-0.5"><?= $_SESSION['success'] ?></p>
                </div>
            </div>
            <button onclick="document.getElementById('alert-success').remove()" class="text-green-500 hover:text-green-700 transition font-bold text-sm p-1">✕</button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color: #1E293B">Job Posting</h1>
            <p class="text-sm mt-0.5" style="color: #64748B;">Kelola dan pantau seluruh lowongan pekerjaan perusahaan Anda</p>
        </div>
        <a href="<?= BASE_URL ?>views/formJob/create.php"
            class="inline-flex items-center gap-2 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition hover:opacity-90 shadow-xs active:scale-95 shrink-0"
            style="background: linear-gradient(135deg,#1E3A8A,#2563EB);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Buat Lowongan Baru
        </a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-xs">

        <div class="px-6 py-5 flex items-center justify-between" style="background: linear-gradient(135deg, #1E3A8A, #2563EB);">
            <div class="flex items-center gap-4">
                <div class="inline-flex items-center justify-center rounded-2xl" style="width:44px; height:44px; background: rgba(255,255,255,0.15);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-base font-bold text-white tracking-tight">Daftar Lowongan Kerja</h2>
                    <p class="text-xs text-blue-100 font-medium mt-0.5">Total <?= $totalData ?> data lowongan tersimpan</p>
                </div>
            </div>
        </div>

        <div class="p-6 border-b border-slate-100 bg-slate-50/50 flex flex-col md:flex-row md:items-center justify-end gap-4">
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400 mr-1">Export:</span>
                <a href="<?= BASE_URL ?>views/laporan/export_lowongan.php<?= !empty($search) ? '?search=' . urlencode($search) : '' ?>"
                    title="Export Data Lowongan ke Excel"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-emerald-200 text-emerald-700 rounded-xl text-[10px] font-bold uppercase tracking-tight hover:bg-emerald-50 transition-all active:scale-95 shadow-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-500">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    Lowongan (Excel)
                </a>
            </div>
            
            <form id="searchForm" method="GET" class="flex items-center gap-2 max-w-md w-full md:w-auto">
                <div class="relative flex-1 md:w-72 flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-blue-50 text-blue-700 border border-blue-100 shadow-inner font-bold shrink-0">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" id="searchInput" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul lowongan..." autocomplete="off"
                        oninput="doSearch()"
                        class="w-full px-4 py-2 rounded-xl text-xs font-semibold text-slate-700 border border-slate-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 outline-none transition placeholder-slate-400 bg-white shadow-inner">
                </div>
                <?php if (!empty($search)): ?>
                    <a href="index.php" class="px-3 py-2 rounded-xl text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 transition">
                        Reset
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left border-collapse">
                <thead class="bg-slate-50/50 border-b border-slate-100 text-[11px] uppercase tracking-wider text-slate-400 font-bold">
                    <tr>
                        <th class="px-6 py-4 font-bold">Informasi Pekerjaan</th>
                        <th class="px-6 py-4 font-bold">Lokasi & Tipe</th>
                        <th class="px-6 py-4 font-bold">Fitur Kerja</th>
                        <th class="px-6 py-4 font-bold">Status</th>
                        <th class="px-6 py-4 font-bold">Dibuat Pada</th>
                        <th class="text-right px-6 py-4 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600 text-xs font-semibold">
                    <?php foreach ($jobList as $job): ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-4.5">
                                <div class="font-bold text-slate-800 text-sm tracking-tight"><?= htmlspecialchars($job['judul_job']) ?></div>
                                <div class="text-[10px] text-slate-400 mt-1 font-medium">ID Lowongan: #<?= $job['id'] ?></div>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="text-slate-700 font-bold"><?= htmlspecialchars($job['lokasi']) ?></div>
                                <div class="text-[11px] text-slate-400 mt-0.5 font-medium"><?= htmlspecialchars($job['tipe_pekerjaan']) ?></div>
                            </td>
                            <td class="px-6 py-4.5">
                                <div class="flex flex-wrap gap-1">
                                    <?php if ($job['is_remote_work']): ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-indigo-50 text-indigo-600 border border-indigo-100 uppercase tracking-wide">Remote</span>
                                    <?php endif; ?>
                                    <?php if ($job['is_disabilitas']): ?>
                                        <span class="px-2 py-0.5 text-[9px] font-bold rounded-md bg-emerald-50 text-emerald-600 border border-emerald-100 uppercase tracking-wide">Inklusif</span>
                                    <?php endif; ?>
                                    <?php if (!$job['is_remote_work'] && !$job['is_disabilitas']): ?>
                                        <span class="text-slate-400 text-xs italic">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4.5">
                                <?php if ($job['status'] === 'draft'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wide">Draft</span>
                                <?php elseif ($job['status'] === 'open'): ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wide">Open</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wide">Closed</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4.5 text-slate-400 font-medium text-xs">
                                <?= date('d M Y', strtotime($job['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4.5 text-right">
                                <div class="flex justify-end items-center gap-1.5">
                                    <?php if ($job['status'] === 'draft'): ?>
                                        <button onclick="openModal('status', <?= $job['id'] ?>, 'open')"
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg text-white bg-blue-800 hover:bg-blue-900 transition shadow-2xs active:scale-95">
                                            PUBLISH
                                        </button>
                                        <a href="edit.php?id=<?= $job['id'] ?>" class="p-1.5 text-slate-400 hover:text-amber-600 border border-transparent hover:border-slate-100 hover:bg-slate-50 rounded-lg transition" title="Edit Data">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                        </a>
                                        <button onclick="openModal('delete', <?= $job['id'] ?>)" class="p-1.5 text-slate-400 hover:text-red-600 border border-transparent hover:border-slate-100 hover:bg-slate-50 rounded-lg transition" title="Hapus Permanen">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </button>
                                    <?php elseif ($job['status'] === 'open'): ?>
                                        <button onclick="openModal('status', <?= $job['id'] ?>, 'closed')"
                                            class="px-2.5 py-1 text-[11px] font-bold rounded-lg border border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100 transition active:scale-95">
                                            TUTUP LOKER
                                        </button>
                                    <?php endif; ?>
                                    <a href="view.php?id=<?= $job['id'] ?>" class="p-1.5 text-slate-400 hover:text-slate-700 border border-transparent hover:border-slate-100 hover:bg-slate-50 rounded-lg transition" title="Lihat Detail">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"></path>
                                            <circle cx="12" cy="12" r="3"></circle>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if ($jobCountInPage === 0): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-xs text-slate-400 italic bg-slate-50/30 font-medium">
                                Tidak ada data pekerjaan ditemukan pada halaman ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 bg-slate-50/30">
            <span class="text-xs font-medium text-slate-500">
                Menampilkan <?= ($jobCountInPage > 0) ? (($page - 1) * $perPage) + 1 : 0 ?> - <?= ($page - 1) * $perPage + $jobCountInPage ?> dari <?= $totalData ?> data lowongan
            </span>

            <div class="flex items-center gap-1">
                <?php $searchQuery = !empty($search) ? "&search=" . urlencode($search) : ""; ?>
                <?php if ($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?><?= $searchQuery ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition font-bold">‹</a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?= $i ?><?= $searchQuery ?>"
                        class="px-2.5 py-1 text-xs rounded-lg font-bold transition <?= $i == $page ? 'bg-blue-800 text-white shadow-2xs' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?><?= $searchQuery ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition font-bold">›</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="confirmModal" class="hidden fixed inset-0 z-[999] flex justify-center items-center p-4 modal-backdrop">
    <div class="bg-white rounded-2xl w-full max-w-md shadow-xl animate-slide-down overflow-hidden border border-slate-100">

        <div id="modalAccent" class="h-1.5 w-full bg-blue-600"></div>

        <div class="p-6">
            <div class="flex gap-4">
                <div id="modalIcon" class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 shadow-inner font-bold text-xl">
                    🚀
                </div>
                <div class="flex-1">
                    <h3 id="modalTitle" class="text-base font-bold text-slate-800 tracking-tight">Publish Lowongan Pekerjaan?</h3>
                    <p id="modalDesc" class="text-xs text-slate-500 leading-relaxed mt-1">Lowongan akan segera di-publish secara meluas ke publik. Pelamar kerja akan bisa melihat dan mengirimkan lamaran mereka ke posisi ini.</p>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 mt-6 pt-4 border-t border-slate-100">
                <button onclick="closeModal()" class="px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 border border-slate-200 bg-white hover:bg-slate-50 transition">
                    Batal
                </button>
                <a id="modalConfirmBtn" href="#" class="px-5 py-2 text-xs font-bold text-white rounded-xl bg-blue-800 hover:bg-blue-900 transition shadow-md active:scale-95">
                    Ya, Konfirmasi
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Fitur Debounce pencarian: otomatis kirim form setelah 400ms berhenti ngetik
    let timeout = null;

    function doSearch() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('searchForm').submit();
        }, 400);
    }

    // Mempertahankan fokus kursor teks di inputan setelah submit/reload halaman
    window.addEventListener('DOMContentLoaded', () => {
        const input = document.getElementById('searchInput');
        if (input.value !== '') {
            input.focus();
            const val = input.value;
            input.value = '';
            input.value = val;
        }
    });

    function openModal(type, id, extra = '') {
        const modal = document.getElementById('confirmModal');
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDesc');
        const icon = document.getElementById('modalIcon');
        const accent = document.getElementById('modalAccent');
        const btn = document.getElementById('modalConfirmBtn');

        if (type === 'status') {
            if (extra === 'open') {
                title.innerText = 'Publish Lowongan Pekerjaan?';
                desc.innerText = 'Lowongan akan segera di-publish secara meluas ke publik. Pelamar kerja akan bisa melihat dan mengirimkan lamaran mereka ke posisi ini.';
                icon.innerText = '🚀';
                icon.className = 'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 border border-blue-100 shadow-inner';
                accent.className = 'h-1.5 w-full bg-blue-600';
                btn.className = 'px-5 py-2 text-xs font-bold text-white rounded-xl bg-blue-800 hover:bg-blue-900 transition shadow-md active:scale-95';
                btn.href = "<?= BASE_URL ?>public/actions/handle_update_delete_job.php?status_id=" + id + "&to=open";
            } else if (extra === 'closed') {
                title.innerText = 'Tutup Lowongan Kerja?';
                desc.innerText = 'Lowongan akan dihentikan dan diarsipkan. Pelamar tidak akan dapat lagi melihat posisi ini atau mengirimkan berkas lamaran baru.';
                icon.innerText = '🔒';
                icon.className = 'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-amber-50 text-amber-600 border border-amber-100 shadow-inner';
                accent.className = 'h-1.5 w-full bg-amber-500';
                btn.className = 'px-5 py-2 text-xs font-bold text-white rounded-xl bg-amber-600 hover:bg-amber-700 transition shadow-md active:scale-95';
                btn.href = "<?= BASE_URL ?>public/actions/handle_update_delete_job.php?status_id=" + id + "&to=closed";
            }
        } else if (type === 'delete') {
            title.innerText = 'Hapus Permanen?';
            desc.innerText = 'Data pekerjaan ini akan dihapus selamanya. Anda tidak dapat membatalkan tindakan ini setelah dikonfirmasi.';
            icon.innerText = '🗑️';
            icon.className = 'flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center bg-red-50 text-red-600 border border-red-100 shadow-inner';
            accent.className = 'h-1.5 w-full bg-red-600';
            btn.className = 'px-5 py-2 text-xs font-bold text-white rounded-xl bg-red-600 hover:bg-red-700 transition shadow-md active:scale-95';
            btn.href = "<?= BASE_URL ?>public/actions/handle_update_delete_job.php?delete_id=" + id;
        }

        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('confirmModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

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