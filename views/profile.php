<?php
// views/profile.php
require_once __DIR__ . '/../models/User.php';
$userModel = new User();
$user = $_SESSION['user'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $updated = false;
    $uploadError = '';

    // handle name, hours, course
    if (!empty($_POST['full_name'])) {
        $updated = true;
        $user['full_name'] = $_POST['full_name'];
    }
    if (isset($_POST['required_hours'])) {
        $updated = true;
        $user['required_hours'] = (int)$_POST['required_hours'];
    }
    if (isset($_POST['course'])) {
        $updated = true;
        $user['course'] = trim($_POST['course']);
    }
    if (isset($_POST['department'])) {
        $updated = true;
        $user['department'] = trim($_POST['department']);
    }

    // handle photo upload
    if (!empty($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['photo']['tmp_name'];
        $destDir = __DIR__ . '/../public/uploads';
        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user['id'] . '_' . time() . '.' . $ext;
        $dest = $destDir . '/' . $filename;
        if (move_uploaded_file($tmp, $dest)) {
            $user['photo'] = 'uploads/' . $filename;
            $updated = true;
        } else {
            $uploadError = 'Failed to save uploaded photo.';
        }
    } elseif (!empty($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadError = 'Photo upload error code: ' . $_FILES['photo']['error'];
    }

    if ($updated) {
        $userModel->update($user['id'], $user['full_name'], $user['required_hours'], $user['course'] ?? null, $user['photo'] ?? null, $user['department'] ?? null);
        $_SESSION['user'] = $user;
        $message = 'Profile updated successfully.';
    }
    if (!empty($uploadError)) {
        $error = $uploadError;
    }
}

$title    = 'Profile';
$subtitle = 'Manage your account details';

ob_start();
?>
<div style="max-width:580px;display:flex;flex-direction:column;gap:16px">

    <!-- Identity card -->
    <div style="background:var(--navy);border-radius:14px;padding:28px 28px 24px;position:relative;overflow:hidden" class="fade-up">
        <div style="position:absolute;top:-60px;right:-60px;width:200px;height:200px;background:radial-gradient(circle,rgba(201,125,46,0.2),transparent 70%)"></div>
        <div style="display:flex;align-items:center;gap:18px;position:relative;z-index:1">
            <?php if (!empty($user['photo'])): ?>
            <img src="<?php echo htmlspecialchars($user['photo']); ?>" alt="Profile photo" style="width:84px;height: 84px;px;border-radius:14px;object-fit:cover;flex-shrink:0">
            <?php else: ?>
            <div style="width:64px;height:64px;background:var(--amber);border-radius:8px;display:flex;align-items:center;justify-content:center;font-family:'Instrument Serif',serif;font-size:28px;color:var(--navy);font-weight:400;flex-shrink:0">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <?php endif; ?>
            <div>
                <div style="font-family:'Instrument Serif',serif;font-size:22px;color:#fff;margin-bottom:3px"><?php echo htmlspecialchars($user['full_name']); ?></div>
                <div style="font-size:12px;color:rgba(255,255,255,0.5)"><?php echo htmlspecialchars(mask_email($user['email'])); ?></div>
                <div style="margin-top:8px;display:flex;gap:8px">
                    <span style="background:rgba(201,125,46,0.25);color:#f5c87a;font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px;letter-spacing:0.04em;text-transform:capitalize"><?php echo $user['role']; ?></span>
                    <span style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px"><?php echo $user['required_hours']; ?> hrs required</span>
                    <?php if (!empty($user['course'])): ?><span style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px"><?php echo htmlspecialchars($user['course']); ?></span><?php endif; ?>
                    <?php if (!empty($user['department'])): ?><span style="background:rgba(255,255,255,0.1);color:rgba(255,255,255,0.6);font-size:11px;font-weight:600;padding:3px 10px;border-radius:99px"><?php echo htmlspecialchars($user['department']); ?></span><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit form -->
    <div class="card fade-up delay-1">
        <div class="card-header">
            <h3>Edit Information</h3>
        </div>
        <div class="card-body">
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" required
                        value="<?php echo htmlspecialchars($user['full_name']); ?>"
                        class="form-input" placeholder="Your full name">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                        class="form-input" disabled style="opacity:0.5;cursor:not-allowed">
                                </div>
                <div class="form-group">
                    <label class="form-label">Required OJT Hours</label>
                    <input type="number" name="required_hours" min="1" max="9999"
                        value="<?php echo (int)$user['required_hours']; ?>"
                        class="form-input" placeholder="e.g. 500">
                    <div style="font-size:11px;color:var(--ink-3);margin-top:4px">This is the total number of hours you need to complete.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Course / Program</label>
                    <input type="text" name="course" class="form-input" placeholder="e.g. BS Computer Science"
                        value="<?php echo htmlspecialchars($user['course'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Company Department</label>
                    <input type="text" name="department" class="form-input" placeholder="e.g. Information Technology"
                        value="<?php echo htmlspecialchars($user['department'] ?? ''); ?>">
                    <div style="font-size:11px;color:var(--ink-3);margin-top:4px">The department you are assigned to at your OJT company.</div>
                </div>
                <div class="form-group">
                    <label class="form-label">Profile photo <span style="color:var(--ink-3);font-weight:400;font-size:11px">(optional, JPG/PNG)</span></label>
                    <input type="file" name="photo" id="photoInput" accept="image/*" class="form-input">
                    <div style="margin-top:6px">
                        <img id="photoPreview" src="<?php echo htmlspecialchars($user['photo'] ?? ''); ?>" alt="Preview" style="max-width:80px;border-radius:8px;<?php echo empty($user['photo']) ? 'display:none;' : ''; ?>">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <!-- Quick links -->
    <div class="card fade-up delay-2">
        <div class="card-header"><h3>Quick Actions</h3></div>
        <div style="padding:12px 16px;display:flex;flex-direction:column;gap:4px">
            <?php
            $actions = [
                ['href'=>'index.php?p=dashboard', 'label'=>'View Dashboard',        'icon'=>'grid'],
                ['href'=>'index.php?p=log',        'label'=>'Log Today\'s Hours',   'icon'=>'plus'],
                ['href'=>'index.php?p=history',    'label'=>'View Attendance History','icon'=>'list'],
                ['href'=>'index.php?p=report',     'label'=>'Generate OJT Report',  'icon'=>'file'],
            ];
            $qi = ['grid'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>',
               'plus'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>',
               'list'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>',
               'file'=>'<path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'];
            foreach ($actions as $a): ?>
            <a href="<?php echo $a['href']; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 8px;border-radius:8px;font-size:13.5px;color:var(--ink-2);font-weight:500;text-decoration:none;transition:background 0.15s" onmouseover="this.style.background='var(--paper)'" onmouseout="this.style.background='transparent'">
                <svg width="15" height="15" fill="none" stroke="var(--amber)" stroke-width="1.8" viewBox="0 0 24 24"><?php echo $qi[$a['icon']]; ?></svg>
                <?php echo $a['label']; ?>
                <svg style="margin-left:auto;opacity:0.3" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function(e) {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(ev) {
        const img = document.getElementById('photoPreview');
        img.src = ev.target.result;
        img.style.display = 'block';
    };
    reader.readAsDataURL(file);
});
</script>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';