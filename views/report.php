<?php
// views/report.php
require_once __DIR__ . '/../models/OjtLog.php';
$logModel  = new OjtLog();
$user      = $_SESSION['user'];
$allLogs   = $logModel->findByUser($user['id']);
$totals    = $logModel->getTotals($user['id']);
$rendered  = round((float)($totals['total'] ?? 0), 2);
$required  = (int)($user['required_hours'] ?? 500);
$percent   = $required > 0 ? min(100, ($rendered / $required) * 100) : 0;
$remaining = max(0, $required - $rendered);

// Filter by status
$approved  = array_filter($allLogs, fn($l) => $l['status'] === 'approved');
$pending   = array_filter($allLogs, fn($l) => $l['status'] === 'pending');

$title    = 'OJT Report';
$subtitle = 'Summary of your training hours';

ob_start();
?>

<style>
@media print {
    #sidebar, .topbar, #printBtn, #mob-overlay, .hamburger { display: none !important; }
    #main-wrap { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    .print-doc {
        box-shadow: none !important;
        border: none !important;
        max-width: none !important;
        border-radius: 0 !important;
    }
    body { background: white !important; }
    .no-print { display: none !important; }
    .prog-track { border: 1px solid #ddd !important; }
    .prog-fill { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
}
</style>

<!-- Print button (hidden on print) -->
<div class="no-print" style="margin-bottom:16px;display:flex;gap:10px;align-items:center">
    <button id="printBtn" onclick="window.print()" class="btn btn-primary">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        Print / Save as PDF
    </button>
    <span style="font-size:12px;color:var(--ink-3)">Use <kbd style="background:var(--paper);border:1px solid var(--border);padding:1px 5px;border-radius:4px;font-size:11px">Ctrl+P</kbd> and select "Save as PDF"</span>
</div>

<!-- Report document -->
<div class="print-doc" style="max-width:800px;background:#fff;border:1px solid var(--border);border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06)" class="fade-up">

    <!-- Header -->
    <div style="background:var(--navy);padding:36px 40px;position:relative;overflow:hidden">
        <div style="position:absolute;top:-60px;right:-60px;width:240px;height:240px;background:radial-gradient(circle,rgba(201,125,46,0.2),transparent 70%)"></div>
        <div style="position:relative;z-index:1;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
            <div>
                <div style="font-size:11px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:rgba(255,255,255,0.45);margin-bottom:6px">On-the-Job Training</div>
                <h2 style="font-family:'Instrument Serif',serif;font-size:32px;color:#fff;margin-bottom:4px;letter-spacing:-0.02em"><?php echo htmlspecialchars($user['full_name']); ?></h2>
                <div style="font-size:13px;color:rgba(255,255,255,0.5)"><?php echo htmlspecialchars($user['email']); ?></div>
                <?php if (!empty($user['department'])): ?>
                <div style="font-size:13px;color:rgba(255,255,255,0.5);margin-top:2px">
                    <span style="color:rgba(255,255,255,0.3)">Dept:</span> <?php echo htmlspecialchars($user['department']); ?>
                </div>
                <?php endif; ?>
            </div>
            <div style="text-align:right">
                <div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:rgba(255,255,255,0.4);margin-bottom:4px">Report Generated</div>
                <div style="font-size:14px;color:#fff;font-weight:500"><?php echo date('F j, Y'); ?></div>
                <div style="margin-top:8px;display:inline-flex;align-items:center;gap:6px;background:rgba(201,125,46,0.25);border:1px solid rgba(201,125,46,0.4);border-radius:99px;padding:4px 12px">
                    <span style="width:7px;height:7px;border-radius:50%;background:<?php echo $percent >= 100 ? '#4ade80' : 'var(--amber-md)'; ?>;flex-shrink:0"></span>
                    <span style="font-size:12px;font-weight:600;color:var(--amber-md)"><?php echo $percent >= 100 ? 'COMPLETED' : 'IN PROGRESS'; ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats row -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));border-bottom:1px solid var(--border)">
        <?php
        $metaItems = [
            ['label'=>'Hours Rendered', 'value'=> number_format($rendered,1).'h', 'color'=>'var(--amber)'],
            ['label'=>'Required Hours',  'value'=> $required.'h',                  'color'=>'var(--navy)'],
            ['label'=>'Remaining',       'value'=> number_format($remaining,1).'h','color'=>'var(--teal)'],
            ['label'=>'Completion',      'value'=> round($percent,1).'%',           'color'=>$percent>=100?'var(--green)':'var(--amber)'],
        ];
        if (!empty($user['department'])) {
            array_splice($metaItems, 2, 0, [[
                'label'=>'Department',
                'value'=> htmlspecialchars($user['department']),
                'color'=>'var(--ink)',
            ]]);
        }
        foreach ($metaItems as $i => $m): ?>
        <div style="padding:20px 24px;<?php echo $i>0?'border-left:1px solid var(--border);':''; ?>">
            <div style="font-size:10px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:6px"><?php echo $m['label']; ?></div>
            <div style="font-family:'Instrument Serif',serif;font-size:<?php echo is_numeric(str_replace(['h','%'], '', $m['value'])) ? '26' : '16'; ?>px;color:<?php echo $m['color']; ?>;line-height:1.2"><?php echo $m['value']; ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Progress -->
    <div style="padding:24px 32px;border-bottom:1px solid var(--border)">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:10px">Progress toward <?php echo $required; ?> hours</div>
        <div class="prog-track" style="height:12px">
            <div class="prog-fill" data-progress="<?php echo $percent; ?>" style="width:0%;height:12px"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--ink-3);margin-top:6px">
            <span>0 hrs</span>
            <span style="font-weight:600;color:var(--ink)"><?php echo number_format($rendered,1); ?> hrs rendered</span>
            <span><?php echo $required; ?> hrs</span>
        </div>
    </div>

    <!-- Approved Logs table -->
    <div style="padding:24px 32px;border-bottom:1px solid var(--border)">
        <div style="font-size:11px;font-weight:600;letter-spacing:0.08em;text-transform:uppercase;color:var(--ink-3);margin-bottom:14px">Approved Attendance Logs (<?php echo count($approved); ?> entries)</div>
        <?php if (empty($approved)): ?>
        <p style="font-size:13px;color:var(--ink-3);text-align:center;padding:24px 0">No approved logs yet.</p>
        <?php else: ?>
        <table class="data-table" style="font-size:12.5px">
            <thead><tr>
                <th>Date</th>
                <th>Day</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th style="text-align:right">Hours</th>
                <th>Description</th>
            </tr></thead>
            <tbody>
            <?php foreach ($approved as $l): ?>
            <tr>
                <td style="font-weight:500"><?php echo date('M j, Y', strtotime($l['date'])); ?></td>
                <td style="color:var(--ink-3);font-size:11px"><?php echo date('D', strtotime($l['date'])); ?></td>
                <td class="mono"><?php echo date('h:i A', strtotime($l['time_in'])); ?></td>
                <td class="mono"><?php echo date('h:i A', strtotime($l['time_out'])); ?></td>
                <td class="mono" style="text-align:right;font-weight:600"><?php echo number_format($l['total_hours'],2); ?>h</td>
                <td style="font-size:12px;color:var(--ink-3);max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars($l['description'] ?? '—'); ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="border-top:2px solid var(--border)">
                    <td colspan="4" style="font-weight:700;font-size:13px;color:var(--ink);padding:12px 16px">Total Approved Hours</td>
                    <td class="mono" style="text-align:right;font-weight:700;font-size:16px;color:var(--amber);font-family:'Instrument Serif',serif"><?php echo number_format($rendered,2); ?>h</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <?php endif; ?>
    </div>

    <!-- Signature section -->
    <div style="padding:32px 32px 36px;display:grid;grid-template-columns:1fr 1fr;gap:40px">
        <div>
            <div style="font-size:10px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:40px">Student Signature</div>
            <div style="border-top:1.5px solid var(--border-md);padding-top:10px">
                <div style="font-size:13.5px;font-weight:600;color:var(--ink)"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div style="font-size:11px;color:var(--ink-3);margin-top:2px">OJT Student</div>
            </div>
        </div>
        <div>
            <div style="font-size:10px;font-weight:600;letter-spacing:0.1em;text-transform:uppercase;color:var(--ink-3);margin-bottom:40px">Supervisor Signature</div>
            <div style="border-top:1.5px solid var(--border-md);padding-top:10px">
                <div style="font-size:13.5px;font-weight:600;color:var(--ink-3)">Company Supervisor</div>
                <div style="font-size:11px;color:var(--border-md);margin-top:2px">Date: _______________</div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';