<?php
session_start();
require_once __DIR__ . '/config/database.php';

// 1. Kung naka-login na, ayaw na pabalika sa login page, adto na sa index
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Login Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role']; 
                
                // Redirect sa index (walay slash)
                header('Location: index.php');
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            $error = "System Error: " . $e->getMessage();
        }
    }
}

$csrf_token = generateCSRFToken();

// --- MAO KINI ANG SAKTO NGA LINE ---
// Dili navbar.php, kung dili header.php
require_once __DIR__ . '/includes/header.php';
// -----------------------------------
?>

<section class="auth-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2><i class="fas fa-sign-in-alt text-primary"></i> Login</h2>
                            <p class="text-muted">Welcome back to StreetGo</p>
                        </div>
                        
                        <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['logout'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> You have been logged out.
                        </div>
                        <?php endif; ?>
                        
                        <!-- Form Action walay slash -->
                        <form method="POST" action="login.php">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="********" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                                Login
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <p class="mb-0">No account yet? <a href="register.php" class="text-primary fw-bold text-decoration-none">Sign up</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>