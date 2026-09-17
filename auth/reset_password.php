<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Password Reset Request Handler
 */

require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your registered email address.';
    } else {
        $user = fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if ($user) {
            // For demo system, update password directly to a default or notify
            $default_new_pass = 'password123';
            $hash = password_hash($default_new_pass, PASSWORD_BCRYPT);
            executeQuery("UPDATE users SET password = ? WHERE id = ?", [$hash, $user['id']]);

            $message = 'Password reset link sent! (Demo system: Password has been reset to: <strong>password123</strong>)';
        } else {
            $error = 'No user account found with that email address.';
        }
    }
}

$page_title = "Reset Password";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header cs_blue_bg text-white text-center py-4 rounded-top">
                        <h3 class="text-white mb-1"><i class="fa-solid fa-key me-2"></i> Reset Password</h3>
                        <p class="mb-0 text-white-50">Enter your email to receive recovery instructions</p>
                    </div>
                    <div class="card-body p-4 p-md-5">

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-check me-2"></i> <?php echo $message; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo APP_URL; ?>/auth/reset_password.php" method="POST">
                            <div class="mb-4">
                                <label for="email" class="form-label fw-bold">Registered Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-envelope text-muted"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required placeholder="Enter registered email">
                                </div>
                            </div>

                            <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3 mb-3">
                                <span>Reset Password</span> <i class="fa-solid fa-paper-plane ms-1"></i>
                            </button>

                            <div class="text-center mt-3">
                                <a href="<?php echo APP_URL; ?>/auth/login.php" class="text-decoration-none fw-bold text-primary">
                                    <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
