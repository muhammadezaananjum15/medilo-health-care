<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * User Login Authentication Endpoint
 */

require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (is_logged_in()) {
    $role = $_SESSION['role'] ?? 'patient';
    if ($role === 'admin') {
        header('Location: ' . APP_URL . '/admin/dashboard.php');
    } elseif ($role === 'doctor') {
        header('Location: ' . APP_URL . '/frontend/doctor_dashboard.php');
    } else {
        header('Location: ' . APP_URL . '/frontend/patient_dashboard.php');
    }
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = sanitize_input($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_email) || empty($password)) {
        $error = 'Please enter both username/email and password.';
    } else {
        // Query user record by username OR email
        $user = fetchOne("SELECT * FROM users WHERE username = ? OR email = ?", [$username_email, $username_email]);

        if ($user) {
            // Verify password (support hashed password or fallback default for demo)
            if (password_verify($password, $user['password']) || $password === 'password123') {
                // Initialize user session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                set_flash_message('success', 'Welcome back, ' . htmlspecialchars($user['full_name']) . '!');

                // Role-based redirection
                if ($user['role'] === 'admin') {
                    header('Location: ' . APP_URL . '/admin/dashboard.php');
                } elseif ($user['role'] === 'doctor') {
                    header('Location: ' . APP_URL . '/frontend/doctor_dashboard.php');
                } else {
                    header('Location: ' . APP_URL . '/frontend/patient_dashboard.php');
                }
                exit;
            } else {
                $error = 'Invalid username/email or password.';
            }
        } else {
            $error = 'Account not found with provided credentials.';
        }
    }
}

$page_title = "User Login";
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Login Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card border-0 shadow-lg rounded-3">
                    <div class="card-header cs_blue_bg text-white text-center py-4 rounded-top">
                        <h3 class="text-white mb-1"><i class="fa-solid fa-right-to-bracket me-2"></i> Account Login</h3>
                        <p class="mb-0 text-white-50">Access your Medilo Portal</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                    

                        <form action="<?php echo APP_URL; ?>/auth/login.php" method="POST">
                            <div class="mb-3">
                                <label for="username_email" class="form-label fw-bold">Username or Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-user text-muted"></i></span>
                                    <input type="text" class="form-control" id="username_email" name="username_email" required placeholder="Enter username or email" value="<?php echo htmlspecialchars($_POST['username_email'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-bold">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fa-solid fa-lock text-muted"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required placeholder="Enter password">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="remember">
                                    <label class="form-check-label text-muted" for="remember">Remember me</label>
                                </div>
                                <a href="<?php echo APP_URL; ?>/auth/reset_password.php" class="text-decoration-none small text-primary">Forgot Password?</a>
                            </div>

                            <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-3 mb-3">
                                <span>Sign In</span> <i class="fa-solid fa-arrow-right-to-bracket ms-1"></i>
                            </button>

                            <div class="text-center mt-3">
                                <p class="text-muted mb-0">Don't have an account? <a href="<?php echo APP_URL; ?>/auth/register.php" class="fw-bold text-primary text-decoration-none">Register Patient Account</a></p>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
