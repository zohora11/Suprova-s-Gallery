<?php
/**
 * Login Page — Suprova's Gallery
 */
$pageTitle = "Login — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) { header('Location: ' . SITE_URL . '/'); exit; }

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    // Validation
    if (!$email)                   $errors['email']    = 'Email is required';
    elseif (!isValidEmail($email)) $errors['email']    = 'Invalid email address';
    if (!$password)                $errors['password'] = 'Password is required';

    if (empty($errors)) {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            loginUser($user);
            // Remember me cookie (30 days)
            if ($remember) {
                $token = bin2hex(random_bytes(32));
                $db->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$token, $user['id']]);
                setcookie('remember_token', $token, time() + (86400 * 30), '/', '', false, true);
            }
            $redirect = $_SESSION['redirect_after_login'] ?? SITE_URL . '/';
            unset($_SESSION['redirect_after_login']);
            setFlash('success', 'Welcome back, ' . $user['name'] . '! 💖');
            header('Location: ' . $redirect);
            exit;
        } else {
            $errors['general'] = 'Invalid email or password.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div style="text-align:center;margin-bottom:var(--gap-md);">
                <div style="font-size:2.5rem;margin-bottom:8px;">💖</div>
                <h1 class="auth-title">Welcome Back</h1>
                <p class="auth-subtitle">Sign in to your Suprova's Gallery account</p>
            </div>

            <?php if (!empty($errors['general'])): ?>
                <div class="flash-message flash-error" style="margin-bottom:var(--gap);">
                    <i class="fas fa-exclamation-circle"></i> <?= e($errors['general']) ?>
                </div>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span>*</span></label>
                    <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                           id="email" name="email" value="<?= e($email) ?>"
                           placeholder="you@example.com" autocomplete="email">
                    <?php if (isset($errors['email'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password <span>*</span></label>
                    <div style="position:relative;">
                        <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                               id="password" name="password" placeholder="Enter your password" autocomplete="current-password">
                        <button type="button" onclick="togglePassword('password')"
                                style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:var(--text-muted);">
                            <i class="fas fa-eye" id="passwordEye"></i>
                        </button>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <div class="form-error"><i class="fas fa-exclamation-circle"></i> <?= e($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:var(--gap-md);">
                    <label style="display:flex;align-items:center;gap:8px;font-size:0.875rem;cursor:pointer;">
                        <input type="checkbox" name="remember" style="accent-color:var(--accent);"> Remember me
                    </label>
                    <a href="#" style="font-size:0.875rem;color:var(--accent);">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-lg" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="auth-divider"><span>OR</span></div>

            <p style="text-align:center;font-size:0.9rem;color:var(--text-muted);">
                Don't have an account? 
                <a href="<?= SITE_URL ?>/register.php" style="color:var(--accent);font-weight:600;">Sign Up Free</a>
            </p>
        </div>
    </div>
</section>

<script>
function togglePassword(id) {
    const input = document.getElementById(id);
    const eye   = document.getElementById(id + 'Eye');
    if (input.type === 'password') {
        input.type = 'text';
        eye.className = 'fas fa-eye-slash';
    } else {
        input.type = 'password';
        eye.className = 'fas fa-eye';
    }
}

document.getElementById('loginForm').addEventListener('submit', function(e) {
    let valid = true;
    const email    = document.getElementById('email');
    const password = document.getElementById('password');

    if (!email.value.trim()) { markInvalid(email, 'Email is required'); valid = false; }
    else if (!/\S+@\S+\.\S+/.test(email.value)) { markInvalid(email, 'Invalid email'); valid = false; }
    else markValid(email);

    if (!password.value) { markInvalid(password, 'Password is required'); valid = false; }
    else markValid(password);

    if (!valid) e.preventDefault();
});

function markInvalid(el, msg) {
    el.classList.add('is-invalid'); el.classList.remove('is-valid');
    let err = el.parentElement.querySelector('.form-error');
    if (!err) { err = document.createElement('div'); err.className = 'form-error'; el.parentElement.appendChild(err); }
    err.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + msg;
}

function markValid(el) {
    el.classList.remove('is-invalid'); el.classList.add('is-valid');
    const err = el.parentElement.querySelector('.form-error');
    if (err) err.remove();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
