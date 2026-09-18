<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Secure Password Recovery & OTP Verification Handler
 */

require_once __DIR__ . '/../config/db.php';

$step = $_SESSION['reset_step'] ?? 1;
$message = '';
$error = '';
$demo_otp_code = $_SESSION['reset_otp'] ?? '';

// Step 1: Request Password Reset OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_otp'])) {
    $email = sanitize_input($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your registered email address.';
    } else {
        $user = fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if ($user) {
            $otp = strval(rand(100000, 999999));
            $_SESSION['reset_email'] = $email;
            $_SESSION['reset_user_id'] = $user['id'];
            $_SESSION['reset_otp'] = $otp;
            $_SESSION['reset_otp_time'] = time();
            $_SESSION['reset_step'] = 2;
            $step = 2;
            $demo_otp_code = $otp;

            $message = 'A 6-digit verification code has been generated. For testing/demo convenience, your code is: <strong class="text-primary font-monospace fs-5">' . $otp . '</strong>';
        } else {
            $error = 'No user account found with that email address. Please check and try again.';
        }
    }
}

// Step 2: Verify OTP and Update Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $entered_otp = sanitize_input($_POST['otp'] ?? '');
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $saved_otp = $_SESSION['reset_otp'] ?? '';
    $saved_user_id = $_SESSION['reset_user_id'] ?? 0;
    $otp_time = $_SESSION['reset_otp_time'] ?? 0;

    if (empty($entered_otp) || empty($new_password) || empty($confirm_password)) {
        $error = 'Please fill out all fields.';
        $step = 2;
    } elseif ($entered_otp !== $saved_otp) {
        $error = 'Invalid verification OTP code. Please check and enter the 6-digit code correctly.';
        $step = 2;
    } elseif (time() - $otp_time > 900) { // 15 minutes expiry
        $error = 'OTP code has expired. Please request a new code.';
        unset($_SESSION['reset_otp'], $_SESSION['reset_step']);
        $step = 1;
    } elseif (strlen($new_password) < 6) {
        $error = 'New password must be at least 6 characters long.';
        $step = 2;
    } elseif ($new_password !== $confirm_password) {
        $error = 'New password and confirmation password do not match.';
        $step = 2;
    } else {
        // Update user password
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        executeQuery("UPDATE users SET password = ? WHERE id = ?", [$hash, $saved_user_id]);

        // Clean up reset session
        unset($_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_otp'], $_SESSION['reset_otp_time'], $_SESSION['reset_step']);

        set_flash_message('success', 'Your password has been successfully updated! You can now log in with your new password.');
        header('Location: ' . APP_URL . '/auth/login.php');
        exit;
    }
}

// Reset workflow if user clicks restart
if (isset($_GET['action']) && $_GET['action'] === 'restart') {
    unset($_SESSION['reset_email'], $_SESSION['reset_user_id'], $_SESSION['reset_otp'], $_SESSION['reset_otp_time'], $_SESSION['reset_step']);
    header('Location: ' . APP_URL . '/auth/reset_password.php');
    exit;
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
                        <h3 class="text-white mb-1"><i class="fa-solid fa-key me-2"></i> Account Password Recovery</h3>
                        <p class="mb-0 text-white-50">
                            <?php echo ($step === 1) ? 'Step 1: Verify your registered email' : 'Step 2: Enter OTP code & choose new password'; ?>
                        </p>
                    </div>
                    <div class="card-body p-4 p-md-5">

                        <?php if (!empty($message)): ?>
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-circle-info me-2"></i> <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($step === 1): ?>
                            <!-- Step 1: Email Form -->
                            <form action="<?php echo APP_URL; ?>/auth/reset_password.php" method="POST">
                                <input type="hidden" name="request_otp" value="1">
                                
                                <div class="mb-4">
                                    <label for="email" class="form-label fw-bold">Registered Email Address <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" required placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                    </div>
                                    <small class="text-muted">We will send a 6-digit one-time verification code to this address.</small>
                                </div>

                                <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3 mb-3">
                                    <span>Send Verification OTP</span> <i class="fa-solid fa-paper-plane ms-1"></i>
                                </button>

                                <div class="text-center mt-3">
                                    <a href="<?php echo APP_URL; ?>/auth/login.php" class="text-decoration-none fw-bold text-primary">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                                    </a>
                                </div>
                            </form>

                        <?php else: ?>
                            <!-- Step 2: OTP Verification & New Password Form -->
                            <form action="<?php echo APP_URL; ?>/auth/reset_password.php" method="POST">
                                <input type="hidden" name="update_password" value="1">

                                <div class="mb-3">
                                    <label for="otp" class="form-label fw-bold">6-Digit Verification Code (OTP) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-shield-halved text-muted"></i></span>
                                        <input type="text" class="form-control font-monospace text-center fs-5" id="otp" name="otp" required maxlength="6" placeholder="123456" value="<?php echo htmlspecialchars($demo_otp_code); ?>">
                                    </div>
                                    <small class="text-muted">Associated email: <strong><?php echo htmlspecialchars($_SESSION['reset_email'] ?? ''); ?></strong></small>
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label fw-bold">New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-lock text-muted"></i></span>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Min 6 characters">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label fw-bold">Confirm New Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fa-solid fa-lock text-muted"></i></span>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" placeholder="Re-enter password">
                                    </div>
                                </div>

                                <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3 mb-3">
                                    <span>Set New Password</span> <i class="fa-solid fa-circle-check ms-1"></i>
                                </button>

                                <div class="d-flex justify-content-between align-items-center mt-3 small">
                                    <a href="<?php echo APP_URL; ?>/auth/reset_password.php?action=restart" class="text-muted text-decoration-none">
                                        <i class="fa-solid fa-rotate-left me-1"></i> Start Over
                                    </a>
                                    <a href="<?php echo APP_URL; ?>/auth/login.php" class="text-decoration-none fw-bold text-primary">
                                        <i class="fa-solid fa-arrow-left me-1"></i> Back to Login
                                    </a>
                                </div>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
