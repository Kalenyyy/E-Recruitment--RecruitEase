<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LamaranController.php';

AuthController::requireLogin();
$role = $_SESSION['role'];

// --- LOGIKA SEARCH, TAB & PAGINATION ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'upcoming'; // Tab default
if ($page < 1) $page = 1;

$perPage = 10;
$interviewData = LamaranController::getInterviewListPaginated($conn, $page, $perPage, $search, $activeTab);

$interviews = $interviewData['data'];
$totalData = $interviewData['total'];
$totalPages = ceil($totalData / $perPage);
if ($totalPages < 1) $totalPages = 1;

ob_start();
?>

<div class="min-h-screen bg-slate-50 pb-20">
    <header class="max-w-6xl mx-auto px-6 pt-10 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Log Jadwal Interview</h1>
                <p class="text-slate-500 mt-1 text-sm">Daftar riwayat dan jadwal pertemuan rekrutmen.</p>
            </div>

            <div class="flex flex-col md:flex-row items-center gap-4">
                <!-- SEARCH FORM -->
                <form id="searchForm" method="GET" class="relative w-full md:w-64">
                    <input type="hidden" name="tab" value="<?= $activeTab ?>">
                    <input type="text" name="search" id="searchInput" value="<?= htmlspecialchars($search) ?>"
                        placeholder="Cari job atau kandidat..." oninput="doSearch()"
                        class="w-full pl-10 pr-4 py-2 rounded-xl text-xs border border-slate-200 focus:ring-2 focus:ring-indigo-100 outline-none shadow-sm">
                    <div class="absolute left-3 top-2.5 text-slate-400">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                </form>

                <!-- Interactive Tabs -->
                <div class="flex bg-slate-200/50 p-1 rounded-xl w-fit border border-slate-200 shadow-sm">
                    <a href="?tab=upcoming&search=<?= urlencode($search) ?>"
                        class="px-5 py-2 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'upcoming' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500' ?>">
                        MENDATANG (<?= $interviewData['countUpcoming'] ?>)
                    </a>
                    <a href="?tab=past&search=<?= urlencode($search) ?>"
                        class="px-5 py-2 rounded-lg text-xs font-bold transition-all <?= $activeTab === 'past' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500' ?>">
                        SELESAI (<?= $interviewData['countPast'] ?>)
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6">
        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
            <?php if (empty($interviews)): ?>
                <?= renderEmptyState("Data interview tidak ditemukan."); ?>
            <?php else: ?>
                <?= renderInterviewTable($interviews, $role); ?>

                <!-- PAGINATION FOOTER -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-slate-100 bg-slate-50/30">
                    <span class="text-xs font-medium text-slate-500">
                        Menampilkan <?= (($page - 1) * $perPage) + 1 ?> - <?= ($page - 1) * $perPage + count($interviews) ?> dari <?= $totalData ?> data
                    </span>

                    <div class="flex items-center gap-1">
                        <?php $baseQuery = "?tab=$activeTab&search=" . urlencode($search); ?>

                        <?php if ($page > 1): ?>
                            <a href="<?= $baseQuery ?>&page=<?= $page - 1 ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold transition">‹</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="<?= $baseQuery ?>&page=<?= $i ?>"
                                class="px-2.5 py-1 text-xs rounded-lg font-bold transition <?= $i == $page ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="<?= $baseQuery ?>&page=<?= $page + 1 ?>" class="px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 font-bold transition">›</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php
function renderInterviewTable($data, $role)
{
    ob_start();
?>
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Waktu & Tanggal</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Posisi Pekerjaan</th>
                    <?php if ($role !== 'candidate'): ?>
                        <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Kandidat</th>
                    <?php endif; ?>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Lokasi / Link</th>
                    <th class="px-6 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($data as $row):
                    $date = date('d M Y', strtotime($row['tanggal_interview']));
                    $time = date('H:i', strtotime($row['tanggal_interview']));
                    $statusClass = ($row['status_interview'] === 'SELESAI') ? "bg-emerald-50 text-emerald-600 border-emerald-100" : (($row['status_interview'] === 'JADWAL') ? "bg-indigo-50 text-indigo-600 border-indigo-100" : "bg-rose-50 text-rose-600 border-rose-100");
                ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-700"><?= $date ?></span>
                                <span class="text-xs text-slate-400 font-medium"><?= $time ?> WIB</span>
                            </div>
                        </td>
                        <td class="px-6 py-5">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-slate-800"><?= htmlspecialchars($row['judul_job']) ?></span>
                                <span class="text-[11px] text-indigo-500 font-bold uppercase"><?= $row['status_lamaran'] ?></span>
                            </div>
                        </td>
                        <?php if ($role !== 'candidate'): ?>
                            <td class="px-6 py-5 text-sm font-medium text-slate-600"><?= htmlspecialchars($row['nama_kandidat']) ?></td>
                        <?php endif; ?>
                        <td class="px-6 py-5 text-sm text-slate-500"><?= htmlspecialchars($row['lokasi']) ?></td>
                        <td class="px-6 py-5 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black border <?= $statusClass ?>"><?= $row['status_interview'] ?></span>
                        </td>
                    </tr>
                    <?php if (!empty($row['catatan'])): ?>
                        <tr class="bg-white">
                            <td colspan="5" class="px-6 pb-4 pt-0">
                                <div class="bg-slate-50 rounded-xl p-3 text-[11px] text-slate-500 border-l-2 border-slate-200 italic">
                                    Catatan: "<?= htmlspecialchars($row['catatan']) ?>"
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php return ob_get_clean();
}

function renderEmptyState($msg)
{
    return "<div class='p-20 text-center'><div class='w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100'><span class='text-2xl'>📁</span></div><h4 class='text-slate-800 font-bold'>$msg</h4><p class='text-sm text-slate-400 mt-1'>Data tidak ditemukan.</p></div>";
}
?>

<script>
    let timeout = null;

    function doSearch() {
        clearTimeout(timeout);
        timeout = setTimeout(function() {
            document.getElementById('searchForm').submit();
        }, 500);
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>