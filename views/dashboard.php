<?php
// views/dashboard.php
require_once __DIR__ . '/../models/OjtLog.php';
require_once __DIR__ . '/../models/User.php';

$user = $_SESSION['user'];
$logModel = new OjtLog();
$totals = $logModel->getTotals($user['id']);
$rendered = round((float)($totals['total'] ?? 0), 2);
$required = (int)($user['required_hours'] ?? 500);
$percent  = $required > 0 ? min(100, ($rendered / $required) * 100) : 0;
$remaining = max(0, $required - $rendered);

// Recent 5 logs
$recent = array_slice($logModel->findByUser($user['id']), 0, 5);

// Status counts
$all_logs  = $logModel->findByUser($user['id']);
$nPending  = count($all_logs); // treat all as recent
$nApproved = $nPending;
$nRejected = 0;

$title = 'Dashboard';
$subtitle = 'Good ' . (date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening')) . ', ' . explode(' ', $user['full_name'])[0];
if (!empty($user['course'])) {
    $subtitle .= ' – ' . $user['course'];
}

ob_start();
?>

<!-- STAT TILES -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:28px">
    <div class="stat-tile amber fade-up">
        <div class="tile-label">Hours Rendered</div>
        <div class="tile-value"><?php echo number_format($rendered, 1); ?></div>
        <div class="tile-sub">of <?php echo $required; ?> required</div>
    </div>
    <div class="stat-tile teal fade-up delay-1">
        <div class="tile-label">Hours Remaining</div>
        <div class="tile-value"><?php echo number_format($remaining, 1); ?></div>
        <div class="tile-sub"><?php echo $percent >= 100 ? '🎉 Completed!' : round(100 - $percent, 1) . '% to go'; ?></div>
    </div>
    <div class="stat-tile navy fade-up delay-2">
        <div class="tile-label">Logs Submitted</div>
        <div class="tile-value"><?php echo $nPending; ?></div>
        <div class="tile-sub"><?php echo $nPending; ?> recent log<?php echo $nPending !== 1 ? 's' : ''; ?></div>
    </div>
</div>

<!-- PROGRESS CARD -->
<div class="card fade-up delay-2" style="margin-bottom:24px">
    <div class="card-header">
        <h3>Overall Completion</h3>
        <span style="font-size:13px;font-weight:600;color:var(--amber)"><?php echo round($percent, 1); ?>%</span>
    </div>
    <div class="card-body">
        <div style="margin-bottom:12px">
            <div class="prog-track">
                <div class="prog-fill" data-progress="<?php echo $percent; ?>" style="width:0%"></div>
            </div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--ink-3)">
            <span>0 hrs</span>
            <span style="font-weight:600;color:var(--ink)"><?php echo number_format($rendered,1); ?> / <?php echo $required; ?> hrs rendered</span>
            <span><?php echo $required; ?> hrs</span>
        </div>

        <?php if ($percent >= 100): ?>
        <div style="margin-top:16px;background:var(--amber-lt);border:1px solid var(--amber-md);border-radius:8px;padding:12px 16px;display:flex;align-items:center;gap:10px">
            <svg width="18" height="18" fill="none" stroke="var(--amber)" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            <span style="font-size:13.5px;font-weight:600;color:#7a4010">Congratulations — you have completed all required OJT hours!</span>
        </div>
        <?php endif; ?>

     
    </div>
</div>

<!-- RECENT ACTIVITY -->
<div class="card fade-up delay-3">
    <div class="card-header">
        <h3>Recent Logs</h3>
        <a href="index.php?p=history" style="font-size:13px;color:var(--amber);font-weight:600;text-decoration:none">View all →</a>
    </div>
    <?php if (empty($recent)): ?>
    <div class="empty-state">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        <p>No logs yet. <a href="index.php?p=log" style="color:var(--amber);font-weight:600">Log your first hours →</a></p>
    </div>
    <?php else: ?>
    <table class="data-table">
        <thead><tr>
            <th>Date</th>
            <th>Time In</th>
            <th>Time Out</th>
            <th>Hours</th>
        </tr></thead>
        <tbody>
        <?php foreach ($recent as $log): ?>
        <tr>
            <td style="font-weight:500;color:var(--ink)"><?php echo date('M j, Y', strtotime($log['date'])); ?></td>
            <td class="mono"><?php echo date('h:i A', strtotime($log['time_in'])); ?></td>
            <td class="mono"><?php echo date('h:i A', strtotime($log['time_out'])); ?></td>
            <td class="mono" style="font-weight:600"><?php echo number_format($log['total_hours'],2); ?>h</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
$topbarAction = '<a href="index.php?p=log" class="btn btn-amber btn-sm">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
    Log Hours
</a>';
require __DIR__ . '/layout.php';
