<?php
// views/log.php
require_once __DIR__ . '/../models/OjtLog.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Check CSRF token
        if (!isset($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? '')) {
            throw new Exception('Invalid or missing security token. Please refresh and try again.');
        }
        
        // Validate required fields
        $date = $_POST['date'] ?? '';
        $in   = $_POST['time_in'] ?? '';
        $out  = $_POST['time_out'] ?? '';
        $desc = $_POST['description'] ?? '';
        
        if (!$date || !$in || !$out) {
            throw new Exception('All required fields must be filled.');
        }
        
        $start = strtotime($date . ' ' . $in);
        $end   = strtotime($date . ' ' . $out);
        
        if (!$start || !$end) {
            throw new Exception('Invalid date or time format.');
        }
        
        $hours = ($end - $start) / 3600;
        
        // Check for overtime (past 5 PM / 17:00)
        $cutoff = strtotime($date . ' 17:00');
        $isOvertime = $end > $cutoff;
        $overtimeHours = 0;
        if ($isOvertime) {
            $overtimeHours = ($end - $cutoff) / 3600;
        }
        
        // apply lateness penalty relative to 08:00 start
        $scheduled = strtotime($date . ' 08:00');
        $lateness = max(0, $start - $scheduled) / 60; // minutes
        if ($lateness > 0) {
            // penalty formula: 1 minute late = 0.5h, 10 minutes late ≈1h
            $penalty = 0.5 + 0.05 * max(0, $lateness - 1);
            if ($penalty > 1) $penalty = 1;
            $hours -= $penalty;
            if ($hours < 0) $hours = 0;
        }
        
        if ($hours <= 0 || $hours > 24) {
            throw new Exception('Invalid time range. Time out must be after time in.');
        }
        
        $logModel = new OjtLog();
        $existing = $logModel->findByUser($_SESSION['user']['id'], $date, $date);
        if (count($existing)) {
            throw new Exception('A log entry for ' . date('F j, Y', strtotime($date)) . ' already exists.');
        }
        
        $success = $logModel->create([
            'user_id'     => $_SESSION['user']['id'],
            'date'        => $date,
            'time_in'     => $in,
            'time_out'    => $out,
            'total_hours' => round($hours, 2),
            'description' => $desc
        ]);
        
        if (!$success) {
            throw new Exception('Failed to save log entry. Please try again.');
        }
        
        $msg = 'Log recorded (' . round($hours, 2) . ' hours on ' . date('M j', strtotime($date));
        if ($isOvertime) {
            $msg .= ' • ' . round($overtimeHours, 2) . 'h overtime';
        }
        $msg .= ')';
        $message = $msg;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$title = 'Log Hours';
$subtitle = 'Submit your daily OJT attendance';

ob_start();
?>
<div style="max-width:600px">
    <div class="card fade-up">
        <div class="card-header">
            <h3>New Attendance Log</h3>
            <span id="live-hours" style="font-size:22px;font-family:'Instrument Serif',serif;color:var(--amber);display:none">
                <span id="hours-val">0.00</span><span style="font-size:14px;color:var(--ink-3);margin-left:4px">hrs</span>
            </span>
        </div>
        <div class="card-body">
            <form id="logForm" method="post">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

                <div class="form-group">
                    <label class="form-label">Date <span style="color:var(--red)">*</span></label>
                    <input type="date" name="date" id="logDate" required
                        max="<?php echo date('Y-m-d'); ?>"
                        value="<?php echo date('Y-m-d'); ?>"
                        class="form-input">
                    <div style="font-size:11px;color:var(--ink-3);margin-top:4px">Future dates are not allowed.</div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
                    <div class="form-group">
                        <label class="form-label">Time In <span style="color:var(--red)">*</span></label>
                        <input type="time" name="time_in" id="timeIn" required class="form-input" oninput="calcHours()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time Out <span style="color:var(--red)">*</span></label>
                        <input type="time" name="time_out" id="timeOut" required class="form-input" oninput="calcHours()">
                    </div>
                </div>

                <!-- Preview box -->
                <div id="hoursPreview" style="display:none;background:var(--amber-lt);border:1px solid var(--amber-md);border-radius:8px;padding:12px 16px;margin-bottom:16px;margin-top:-6px;display:none;align-items:center;gap:12px">
                    <div style="background:var(--amber);border-radius:7px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                        <svg width="18" height="18" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;letter-spacing:0.06em;text-transform:uppercase;color:#8a4e10;margin-bottom:2px">Computed Duration</div>
                        <div id="previewHours" style="font-family:'Instrument Serif',serif;font-size:22px;color:var(--ink);line-height:1">0.00 <span style="font-size:14px;color:var(--ink-3)">hours</span></div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Work Description <span style="color:var(--ink-3);font-weight:400;font-size:11px">(optional)</span></label>
                    <textarea name="description" rows="3" placeholder="Briefly describe what you worked on today…" class="form-input"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>

                <div style="display:flex;gap:10px;align-items:center">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        Submit Log
                    </button>
                    <a href="index.php?p=history" class="btn btn-ghost">View History</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Guidelines -->
    <div style="margin-top:16px;background:#fff;border:1px solid var(--border);border-radius:12px;padding:18px 20px" class="fade-up delay-1">
        <div style="font-size:12px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;color:var(--ink-3);margin-bottom:12px">Submission Guidelines</div>
        <div style="display:flex;flex-direction:column;gap:8px">
            <?php
            $rules = [
                ['icon'=>'calendar','text'=>'One log entry per calendar date'],
                ['icon'=>'clock',   'text'=>'Time out must be after time in'],
                ['icon'=>'alert',   'text'=>'Hours after 5:00 PM are marked as overtime'],
                ['icon'=>'alert',   'text'=>'Logs must be submitted for dates up to today only'],
                ['icon'=>'check',   'text'=>'All logged hours count toward your total'],
            ];
            $ri = ['calendar'=>'<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
                   'clock'=>'<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
                   'alert'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>',
                   'check'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'];
            foreach ($rules as $r): ?>
            <div style="display:flex;align-items:center;gap:9px;font-size:13px;color:var(--ink-2)">
                <svg width="14" height="14" fill="none" stroke="var(--amber)" stroke-width="2" viewBox="0 0 24 24"><?php echo $ri[$r['icon']]; ?></svg>
                <?php echo $r['text']; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function calcHours() {
    const tin  = document.getElementById('timeIn').value;
    const tout = document.getElementById('timeOut').value;
    const preview = document.getElementById('hoursPreview');
    if (!tin || !tout) { preview.style.display = 'none'; return; }
    const [h1,m1] = tin.split(':').map(Number);
    const [h2,m2] = tout.split(':').map(Number);
    let diff = ((h2 * 60 + m2) - (h1 * 60 + m1)) / 60;
    
    // Check for overtime (past 5 PM / 17:00)
    const endMinutes = h2 * 60 + m2;
    const cutoff = 17 * 60; // 5 PM
    let overtimeHours = 0;
    let isOvertime = false;
    if (endMinutes > cutoff) {
        isOvertime = true;
        overtimeHours = (endMinutes - cutoff) / 60;
    }
    
    // lateness penalty relative to 08:00
    const startMinutes = h1 * 60 + m1;
    const scheduled = 8 * 60; // 08:00
    const lateness = Math.max(0, startMinutes - scheduled);
    if (lateness > 0) {
        let penalty = 0.5 + 0.05 * Math.max(0, lateness - 1);
        if (penalty > 1) penalty = 1;
        diff -= penalty;
    }
    if (diff > 0 && diff <= 24) {
        let displayText = diff.toFixed(2) + ' <span style="font-size:14px;color:var(--ink-3)">hours</span>';
        if (isOvertime) {
            displayText += '<div style="font-size:12px;color:var(--amber);margin-top:6px">⚡ ' + overtimeHours.toFixed(2) + 'h overtime</div>';
        }
        document.getElementById('previewHours').innerHTML = displayText;
        preview.style.display = 'flex';
        preview.style.background = isOvertime ? 'var(--amber-lt)' : 'var(--amber-lt)';
        preview.style.borderColor = isOvertime ? 'var(--amber)' : 'var(--amber-md)';
    } else {
        document.getElementById('previewHours').innerHTML = 'Invalid range';
        preview.style.display = 'flex';
        preview.style.background = 'var(--red-lt)';
        preview.style.borderColor = '#f0c0c0';
    }
}

function refreshDateField() {
    const now = new Date();
    const yyyy = now.getFullYear();
    const mm = String(now.getMonth() + 1).padStart(2, '0');
    const dd = String(now.getDate()).padStart(2, '0');
    const today = `${yyyy}-${mm}-${dd}`;
    const el = document.getElementById('logDate');
    if (!el) return;
    el.max = today;
    // if empty or somehow ahead (past-midnight open page), reset
    if (!el.value || el.value > today) {
        el.value = today;
    }
}
// initialize and keep updated (every minute)
refreshDateField();
setInterval(refreshDateField, 60000);

// AJAX submit
document.getElementById('logForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.innerHTML = '<svg class="spin" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Submitting…';
    btn.disabled = true;

    const data = new FormData(this);
    const res = await fetch(location.href, { method: 'POST', body: data });
    const html = res.ok ? await res.text() : '';

    // look for a toast call in the returned HTML and replay its message/type
    const toastMatch = html.match(/showToast\("([^"]+)",\s*'([^']+)'\)/);
    if (toastMatch) {
        showToast(toastMatch[1], toastMatch[2]);
        // clear form only on success
        if (toastMatch[2] === 'success') {
            this.reset();
            document.getElementById('logDate').value = new Date().toISOString().split('T')[0];
            document.getElementById('hoursPreview').style.display = 'none';
        }
    } else {
        showToast('Submission failed. Please try again.', 'error');
    }
    btn.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Submit Log';
    btn.disabled = false;
});
</script>
<style>
.spin { animation: spin 0.8s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
