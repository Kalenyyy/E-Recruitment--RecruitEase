<?php
require_once __DIR__ . '/../../init.php';
require_once __DIR__ . '/../../controllers/LamaranController.php';

AuthController::requireLogin();
$role = $_SESSION['role'];

// Ambil data interview (Sudah dipisah upcoming & past oleh controller)
$interviewData = LamaranController::getInterviewList($conn);
$upcoming = $interviewData['upcoming'];
$past = $interviewData['past'];

ob_start();
?>

<div class="min-h-screen bg-slate-50 pb-20">

    <!-- Header Section -->
    <header class="max-w-6xl mx-auto px-6 pt-10 mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">Log Jadwal Interview</h1>
                <p class="text-slate-500 mt-1 text-sm">Daftar riwayat dan jadwal pertemuan rekrutmen.</p>
            </div>
            <!-- Interactive Tabs -->
            <div class="flex bg-slate-200/50 p-1 rounded-xl w-fit border border-slate-200">
                <button onclick="switchTab('upcoming')" id="btn-upcoming" class="tab-btn px-5 py-2 rounded-lg text-xs font-bold transition-all bg-white text-indigo-600 shadow-sm">
                    MENDATANG (<?= count($upcoming) ?>)
                </button>
                <button onclick="switchTab('past')" id="btn-past" class="tab-btn px-5 py-2 rounded-lg text-xs font-bold transition-all text-slate-500 hover:text-slate-700">
                    SELESAI (<?= count($past) ?>)
                </button>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6">
        <!-- Content Wrapper -->
        <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">

            <!-- Tab: Mendatang -->
            <div id="tab-upcoming" class="tab-content">
                <?php if (empty($upcoming)): ?>
                    <?= renderEmptyState("Belum ada jadwal mendatang."); ?>
                <?php else: ?>
                    <?= renderInterviewTable($upcoming, $role); ?>
                <?php endif; ?>
            </div>

            <!-- Tab: Selesai -->
            <div id="tab-past" class="tab-content hidden">
                <?php if (empty($past)): ?>
                    <?= renderEmptyState("Tidak ada riwayat interview."); ?>
                <?php else: ?>
                    <?= renderInterviewTable($past, $role); ?>
                <?php endif; ?>
            </div>

        </div>
    </main>
</div>

<?php
/**
 * Helper: Render Table
 */
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

                    // Logic Warna Status
                    $statusClass = "bg-slate-100 text-slate-600";
                    if ($row['status_interview'] === 'SELESAI') $statusClass = "bg-emerald-50 text-emerald-600 border-emerald-100";
                    if ($row['status_interview'] === 'JADWAL') $statusClass = "bg-indigo-50 text-indigo-600 border-indigo-100";
                    if ($row['status_interview'] === 'BATAL') $statusClass = "bg-rose-50 text-rose-600 border-rose-100";
                ?>
                    <tr class="hover:bg-slate-50/50 transition-colors group">
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
                            <td class="px-6 py-5">
                                <span class="text-sm font-medium text-slate-600"><?= htmlspecialchars($row['nama_kandidat']) ?></span>
                            </td>
                        <?php endif; ?>
                        <td class="px-6 py-5">
                            <div class="flex items-center gap-2 text-sm text-slate-500">
                                <svg class="w-4 h-4 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                </svg>
                                <?= htmlspecialchars($row['lokasi']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-5 text-center">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black border <?= $statusClass ?>">
                                <?= $row['status_interview'] ?>
                            </span>
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
<?php
    return ob_get_clean();
}

/**
 * Helper: Empty State
 */
function renderEmptyState($msg)
{
    return "
    <div class='p-20 text-center'>
        <div class='w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-slate-100'>
            <span class='text-2xl'>📁</span>
        </div>
        <h4 class='text-slate-800 font-bold'>$msg</h4>
        <p class='text-sm text-slate-400 mt-1'>Data tidak ditemukan dalam database.</p>
    </div>";
}
?>

<script>
    function switchTab(type) {
        // Hide all
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));

        // Reset buttons style
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('bg-white', 'text-indigo-600', 'shadow-sm');
            el.classList.add('text-slate-500');
        });

        // Show active
        document.getElementById('tab-' + type).classList.remove('hidden');

        // Active button style
        const activeBtn = document.getElementById('btn-' + type);
        activeBtn.classList.remove('text-slate-500');
        activeBtn.classList.add('bg-white', 'text-indigo-600', 'shadow-sm');
    }
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>