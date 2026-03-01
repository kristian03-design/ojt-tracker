<?php
// views/history.php
require_once __DIR__ . '/../models/OjtLog.php';
$logModel = new OjtLog();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    check_csrf();
    $logModel->delete($_POST['delete_id'], $_SESSION['user']['id']);
    header('Location: index.php?p=history'); exit;
}

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $logs = $logModel->findByUser($_SESSION['user']['id']);
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="ojt_history_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Date', 'Time In', 'Time Out', 'Total Hours', 'Description']);
    foreach ($logs as $l) fputcsv($out, [$l['date'], $l['time_in'], $l['time_out'], $l['total_hours'], $l['description'] ?? '']);
    fclose($out); exit;
}

// pagination parameters
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = 15;
$totalLogs = $logModel->countByUser($_SESSION['user']['id']);
$totalPages = max(1, ceil($totalLogs / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$logs = $logModel->findByUser($_SESSION['user']['id'], null, null, $perPage, ($page - 1) * $perPage);
// total approved hours for user (all pages)
$allHours = $logModel->getTotals($_SESSION['user']['id'])['total'] ?? 0;
// hours on current page for display if needed
$totalHours = array_sum(array_column($logs, 'total_hours')); 

$title    = 'Attendance History';
$subtitle = ($totalLogs > 0 ? (($page - 1) * $perPage + 1) . '-' . (($page - 1) * $perPage + count($logs)) . ' of ' . $totalLogs : 'No logs') . ' submitted';

ob_start();
?>

<!-- Filter + export bar -->
<div style="display:flex;gap:10px;align-items:center;margin-bottom:16px;flex-wrap:wrap">
    <div style="position:relative;flex:1;min-width:200px">
        <svg style="position:absolute;left:11px;top:50%;transform:translateY(-50%);pointer-events:none;opacity:0.4" width="14" height="14" fill="none" stroke="var(--ink)" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input type="text" id="filterInput" placeholder="Search by date or description…"
            oninput="filterRows()"
            style="width:100%;padding:9px 14px 9px 34px;border:1px solid var(--border-md);border-radius:8px;background:var(--paper);font-family:'Geist',sans-serif;font-size:13.5px;color:var(--ink);outline:none;transition:all 0.2s"
            onfocus="this.style.borderColor='var(--amber)';this.style.background='#fff';this.style.boxShadow='0 0 0 3px rgba(201,125,46,0.12)'"
            onblur="this.style.borderColor='';this.style.background='';this.style.boxShadow=''">
    </div>


    <a href="?p=history&export=csv" class="btn btn-ghost btn-sm" style="gap:6px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Export CSV
    </a>
</div>

<!-- Summary strip -->
<div style="display:flex;gap:0;margin-bottom:16px;border:1px solid var(--border);border-radius:10px;overflow:hidden;background:#fff">
    <?php
    $stats = ['Total logs' => $totalLogs];
    $colors = ['Total logs'=>'var(--navy)'];
    $i = 0;
    foreach ($stats as $label => $val): $i++;
    ?>
    <div style="flex:1;padding:14px 16px;<?php echo $i > 1 ? 'border-left:1px solid var(--border);' : ''; ?>">
        <div style="font-size:10px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px"><?php echo $label; ?></div>
        <div style="font-family:'Instrument Serif',serif;font-size:24px;color:<?php echo $colors[$label]; ?>;line-height:1"><?php echo $val; ?></div>
    </div>
    <?php endforeach; ?>
    <div style="flex:1;padding:14px 16px;border-left:1px solid var(--border)">
        <div style="font-size:10px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:4px">Approved Hrs (all)</div>
        <div style="font-family:'Instrument Serif',serif;font-size:24px;color:var(--teal);line-height:1"><?php echo number_format($allHours,1); ?></div>
    </div>
</div>

<!-- Delete Modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(4px);z-index:100;align-items:center;justify-content:center;padding:20px">
    <div style="background:#fff;border-radius:14px;padding:28px;max-width:360px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,0.2)">
        <div style="width:48px;height:48px;background:var(--red-lt);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
            <svg width="22" height="22" fill="none" stroke="var(--red)" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path stroke-linecap="round" stroke-linejoin="round" d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m5 0V4a1 1 0 011-1h2a1 1 0 011 1v2"/></svg>
        </div>
        <h3 style="font-family:'Instrument Serif',serif;font-size:20px;color:var(--ink);margin-bottom:8px">Delete this log?</h3>
        <p style="font-size:13px;color:var(--ink-3);margin-bottom:24px;line-height:1.6">This action cannot be undone. The log entry will be permanently removed.</p>
        <div style="display:flex;gap:10px">
            <button onclick="closeDeleteModal()" class="btn btn-ghost" style="flex:1">Cancel</button>
            <form id="deleteForm" method="post" style="flex:1">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <input type="hidden" name="delete_id" id="deleteId">
                <button type="submit" class="btn btn-danger" style="width:100%">Delete</button>
            </form>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card fade-up">
    <?php if (empty($totalLogs)): ?>
    <div class="empty-state">
        <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p>No logs yet. <a href="index.php?p=log" style="color:var(--amber);font-weight:600">Submit your first entry →</a></p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto">
        <table class="data-table" id="logsTable">
            <thead><tr>
                <th>Date</th>
                <th>Day</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Hours</th>
                <th>Description</th>
                <th></th>
            </tr></thead>
            <tbody>
            <?php foreach ($logs as $log): ?>
            <tr data-desc="<?php echo htmlspecialchars(strtolower($log['description'] ?? '')); ?>"
                data-date="<?php echo $log['date']; ?>">
                <td style="font-weight:500;color:var(--ink)"><?php echo date('M j, Y', strtotime($log['date'])); ?></td>
                <td style="color:var(--ink-3);font-size:12px"><?php echo date('D', strtotime($log['date'])); ?></td>
                <td class="mono"><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
                <td class="mono"><?php echo date('h:i A', strtotime($log['time_out'])); ?></td>
                <td class="mono" style="font-weight:600;color:var(--ink)"><?php echo number_format($log['total_hours'],2); ?>h</td>
                <td style="max-width:220px">
                    <?php if (!empty($log['description'])): ?>
                    <span style="font-size:12.5px;color:var(--ink-3);display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($log['description']); ?></span>
                    <?php else: ?>
                    <span style="color:var(--border-md)">—</span>
                    <?php endif; ?>
                </td>
                <td style="text-align:right;padding-right:16px">
                    <button onclick="openDeleteModal(<?php echo (int)$log['id']; ?>)" class="btn btn-danger btn-sm">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6m5 0V4a1 1 0 011-1h2a1 1 0 011 1v2"/></svg>
                        Delete
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($totalPages > 1): ?>
    <div style="margin-top:12px;text-align:center">
        <?php if ($page > 1): ?>
        <a href="?p=history&page=<?php echo $page-1; ?>" class="btn btn-ghost btn-sm" style="margin-right:8px">&larr; Prev</a>
        <?php endif; ?>
        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <?php if ($p == $page): ?>
                <span class="btn btn-sm" style="font-weight:600;"><?php echo $p; ?></span>
            <?php else: ?>
                <a href="?p=history&page=<?php echo $p; ?>" class="btn btn-ghost btn-sm"><?php echo $p; ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
        <a href="?p=history&page=<?php echo $page+1; ?>" class="btn btn-ghost btn-sm" style="margin-left:8px">Next &rarr;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div id="noResults" style="display:none;text-align:center;padding:48px 24px;color:var(--ink-3);font-size:14px">
    No logs match your search.
</div>

<script>
function filterRows() {
    const q = document.getElementById('filterInput').value.toLowerCase();
    const s = '';
    const rows = document.querySelectorAll('#logsTable tbody tr');
    let visible = 0;
    rows.forEach(row => {
        const textMatch = !q || row.textContent.toLowerCase().includes(q) || row.dataset.date.includes(q);
        const statusMatch = true;
        const show = textMatch && statusMatch;
        row.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    document.getElementById('noResults').style.display = visible === 0 && document.querySelectorAll('#logsTable').length ? 'block' : 'none';
}

function openDeleteModal(id) {
    document.getElementById('deleteId').value = id;
    const modal = document.getElementById('deleteModal');
    modal.style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});
</script>

<?php
$content = ob_get_clean();
$topbarAction = '<a href="index.php?p=log" class="btn btn-amber btn-sm">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    New Log
</a>';
require __DIR__ . '/layout.php';
