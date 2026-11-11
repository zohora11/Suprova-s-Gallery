<?php
/**
 * Contact Page — Suprova's Gallery
 */
$pageTitle = "Contact Us — Suprova's Gallery";
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$errors = []; $success = false;
$data = ['name'=>'','email'=>'','phone'=>'','subject'=>'','message'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $k => $v) $data[$k] = trim($_POST[$k] ?? '');
    if (strlen($data['name']) < 2)       $errors['name']    = 'Name required';
    if (!isValidEmail($data['email']))   $errors['email']   = 'Valid email required';
    if (!$data['subject'])               $errors['subject'] = 'Subject required';
    if (strlen($data['message']) < 10)  $errors['message'] = 'Message too short';

    if (empty($errors)) {
        $db = getDB();
        $db->prepare("INSERT INTO contact_messages (name,email,phone,subject,message) VALUES (?,?,?,?,?)")
           ->execute([$data['name'],$data['email'],$data['phone'],$data['subject'],$data['message']]);
        $success = true;
        $data = ['name'=>'','email'=>'','phone'=>'','subject'=>'','message'=>''];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container"><h1 class="page-hero-title">Get In Touch</h1></div>
</section>

<div class="contact-page">
    <div class="container">
        <div class="contact-layout">
            <!-- Contact Info -->
            <div class="contact-info-card">
                <h2 style="font-family:var(--font-heading);font-size:1.8rem;font-weight:700;margin-bottom:var(--gap);">We'd Love to Hear From You</h2>
                <p style="opacity:0.85;margin-bottom:var(--gap-xl);line-height:1.7;">Have a question, feedback, or need help with your order? Our team is here for you!</p>

                <?php $contacts = [['fas fa-map-marker-alt','Visit Us','123 Fashion Street, Gulshan, Dhaka, Bangladesh'],['fas fa-phone','Call Us','+880 1700-000000'],['fas fa-envelope','Email Us','hello@suprovasgallery.com'],['fas fa-clock','Business Hours','Mon–Sat: 9AM–9PM | Sun: 11AM–7PM']]; ?>
                <?php foreach ($contacts as [$icon,$title,$text]): ?>
                    <div class="contact-info-item">
                        <div class="contact-info-icon"><i class="<?= $icon ?>"></i></div>
                        <div>
                            <strong><?= $title ?></strong>
                            <p style="font-size:0.875rem;opacity:0.85;margin-top:2px;"><?= $text ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="margin-top:var(--gap-xl);">
                    <p style="font-size:0.85rem;opacity:0.75;margin-bottom:var(--gap);">Follow us on social media:</p>
                    <div style="display:flex;gap:10px;">
                        <?php foreach (['fab fa-facebook-f','fab fa-instagram','fab fa-pinterest-p'] as $si): ?>
                            <a href="#" style="width:40px;height:40px;background:rgba(255,255,255,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;transition:var(--transition);" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                                <i class="<?= $si ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-card">
                <?php if ($success): ?>
                    <div style="text-align:center;padding:var(--gap-xl);">
                        <div style="font-size:4rem;margin-bottom:var(--gap);">✉️</div>
                        <h2 style="font-family:var(--font-heading);font-size:1.5rem;font-weight:700;margin-bottom:8px;color:var(--success);">Message Sent!</h2>
                        <p style="color:var(--text-muted);">Thank you for reaching out. We'll get back to you within 24 hours.</p>
                        <a href="contact.php" class="btn btn-primary" style="margin-top:var(--gap-md);">Send Another</a>
                    </div>
                <?php else: ?>
                    <h2 style="font-family:var(--font-heading);font-size:1.4rem;font-weight:700;margin-bottom:var(--gap-md);">Send Us a Message</h2>
                    <form method="POST" id="contactForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Your Name <span>*</span></label>
                                <input class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>" type="text" name="name" value="<?= e($data['name']) ?>" placeholder="Full name">
                                <?php if (isset($errors['name'])): ?><div class="form-error"><?= e($errors['name']) ?></div><?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input class="form-control" type="tel" name="phone" value="<?= e($data['phone']) ?>" placeholder="01XXXXXXXXX">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email <span>*</span></label>
                            <input class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" type="email" name="email" value="<?= e($data['email']) ?>" placeholder="email@example.com">
                            <?php if (isset($errors['email'])): ?><div class="form-error"><?= e($errors['email']) ?></div><?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Subject <span>*</span></label>
                            <select class="form-control <?= isset($errors['subject']) ? 'is-invalid' : '' ?>" name="subject">
                                <option value="">Select a subject</option>
                                <?php foreach (['Order Inquiry','Product Question','Return Request','Payment Issue','General Feedback','Other'] as $s): ?>
                                    <option <?= $data['subject'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Message <span>*</span></label>
                            <textarea class="form-control <?= isset($errors['message']) ? 'is-invalid' : '' ?>" name="message" rows="5" placeholder="Write your message here..."><?= e($data['message']) ?></textarea>
                            <?php if (isset($errors['message'])): ?><div class="form-error"><?= e($errors['message']) ?></div><?php endif; ?>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
