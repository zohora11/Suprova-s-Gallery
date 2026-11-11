<?php
/**
 * Registration Page — Suprova's Gallery
 */
$pageTitle = "Create Account — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/'); exit; }

$errors = [];
$data   = ['name'=>'','email'=>'','phone'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data['name']  = trim($_POST['name']  ?? '');
    $data['email'] = trim($_POST['email'] ?? '');
    $data['phone'] = trim($_POST['phone'] ?? '');
    $password      = $_POST['password']  ?? '';
    $confirm       = $_POST['confirm']   ?? '';

    // Validation
    if (strlen($data['name']) < 2)    $errors['name']     = 'Name must be at least 2 characters';
    if (!$data['email'])              $errors['email']    = 'Email is required';
    elseif (!isValidEmail($data['email'])) $errors['email'] = 'Invalid email address';
    if ($data['phone'] && !isValidPhone($data['phone'])) $errors['phone'] = 'Invalid phone number (BD format)';
    if (strlen($password) < 6)        $errors['password'] = 'Password must be at least 6 characters';
    if ($password !== $confirm)       $errors['confirm']  = 'Passwords do not match';

    if (empty($errors)) {
        $db = getDB();
        // Check duplicate email
        $chk = $db->prepare("SELECT id FROM users WHERE email = ?");
        $chk->execute([$data['email']]);
        if ($chk->fetch()) {
            $errors['email'] = 'This email is already registered';
        } else {
            $ins = $db->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?,?,?,?,'customer')");
            $ins->execute([$data['name'], $data['email'], $data['phone'] ?: null, $password]);
            $userId = $db->lastInsertId();
            // Auto login
            $user = $db->prepare("SELECT * FROM users WHERE id = ?");
            $user->execute([$userId]);
            loginUser($user->fetch());
            setFlash('success', 'Welcome to Suprova\'s Gallery, ' . $data['name'] . '! 🎉');
            header('Location: ' . SITE_URL . '/');
            exit;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="container">
        <div class="auth-card" style="max-width:540px;">
            <div style="text-align:center;margin-bottom:var(--gap-md);">
                <div style="font-size:2.5rem;margin-bottom:8px;">🌸</div>
                <h1 class="auth-title">Create Account</h1>
                <p class="auth-subtitle">Join Suprova's Gallery for exclusive deals and more</p>
            </div>

            <form method="POST" id="registerForm" novalidate>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name <span>*</span></label>
                        <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                               id="name" name="name" value="<?= e($data['name']) ?>"
                               placeholder="Your full name" autocomplete="name">
                        <?php if (isset($errors['name'])): ?>
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['name']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                               id="phone" name="phone" value="<?= e($data['phone']) ?>"
                               placeholder="01XXXXXXXXX" autocomplete="tel">
                        <?php if (isset($errors['phone'])): ?>
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['phone']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span>*</span></label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= e($data['email']) ?>"
                           placeholder="you@example.com" autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span>*</span></label>
                        <div style="position:relative;">
                            <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                   id="password" name="password" placeholder="Min. 6 characters">
                            <button type="button" onclick="togglePwd('password')"
                                    style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);">
                                <i class="fas fa-eye" id="passwordEye"></i>
                            </button>
                        </div>
                        <?php if (isset($errors['password'])): ?>
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm">Confirm Password <span>*</span></label>
                        <div style="position:relative;">
                            <input type="password" class="form-control <?= isset($errors['confirm']) ? 'is-invalid' : '' ?>"
                                   id="confirm" name="confirm" placeholder="Repeat password">
                        </div>
                        <?php if (isset($errors['confirm'])): ?>
                            <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['confirm']) ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Password Strength -->
                <div id="passwordStrength" style="margin-bottom:var(--gap-md);display:none;">
                    <div style="height:4px;border-radius:2px;background:var(--secondary);overflow:hidden;">
                        <div id="strengthBar" style="height:100%;width:0;transition:width 0.3s,background 0.3s;border-radius:2px;"></div>
                    </div>
                    <p id="strengthText" style="font-size:0.75rem;margin-top:4px;color:var(--text-muted);"></p>
                </div>

                <div style="margin-bottom:var(--gap-md);">
                    <label style="display:flex;align-items:flex-start;gap:8px;font-size:0.85rem;cursor:pointer;">
                        <input type="checkbox" name="agree" id="agree" required style="margin-top:3px;accent-color:var(--accent);">
                        <span>I agree to the <a href="#" style="color:var(--accent);">Terms of Service</a> and <a href="#" style="color:var(--accent);">Privacy Policy</a></span>
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>

            <div class="auth-divider"><span>OR</span></div>

            <p style="text-align:center;font-size:0.9rem;color:var(--text-muted);">
                Already have an account? 
                <a href="<?= SITE_URL ?>/login.php" style="color:var(--accent);font-weight:600;">Sign In</a>
            </p>
        </div>
    </div>
</section>

<script>
function togglePwd(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById(id + 'Eye');
    input.type  = input.type === 'password' ? 'text' : 'password';
    eye.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

// Password strength
document.getElementById('password').addEventListener('input', function() {
    const val = this.value;
    const wrap = document.getElementById('passwordStrength');
    const bar  = document.getElementById('strengthBar');
    const txt  = document.getElementById('strengthText');
    if (!val) { wrap.style.display = 'none'; return; }
    wrap.style.display = 'block';
    let score = 0;
    if (val.length >= 6) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [['25%','#dc3545','Weak'],['50%','#ffc107','Fair'],['75%','#17a2b8','Good'],['100%','#28a745','Strong']];
    const [w,c,l] = levels[score-1] || ['10%','#dc3545','Too Short'];
    bar.style.width = w; bar.style.background = c; txt.textContent = 'Strength: ' + l; txt.style.color = c;
});

// Client-side validation
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;
    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirm = document.getElementById('confirm');
    const agree = document.getElementById('agree');

    if (name.value.trim().length < 2) { markInvalid(name, 'Name must be at least 2 characters'); valid = false; } else markValid(name);
    if (!/\S+@\S+\.\S+/.test(email.value)) { markInvalid(email, 'Invalid email'); valid = false; } else markValid(email);
    if (password.value.length < 6) { markInvalid(password, 'Min. 6 characters'); valid = false; } else markValid(password);
    if (password.value !== confirm.value) { markInvalid(confirm, 'Passwords do not match'); valid = false; } else markValid(confirm);
    if (!agree.checked) { showToast('warning', 'Please agree to the terms'); valid = false; }

    if (!valid) e.preventDefault();
});

function markInvalid(el, msg) {
    el.classList.add('is-invalid'); el.classList.remove('is-valid');
    let err = el.closest('.form-group').querySelector('.form-error');
    if (!err) { err = document.createElement('div'); err.className = 'form-error'; el.closest('.form-group').appendChild(err); }
    err.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg;
}
function markValid(el) {
    el.classList.remove('is-invalid'); el.classList.add('is-valid');
    const err = el.closest('.form-group')?.querySelector('.form-error');
    if (err) err.remove();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
