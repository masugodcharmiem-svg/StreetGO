<?php
session_start();
require_once __DIR__ . '/config/database.php';

// Redirect kung naka-login na
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php'); // Removed slash
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Gamit ang sanitize function gikan sa config/database.php
    $username = sanitize($_POST['username'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $full_name = sanitize($_POST['full_name'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // Validations
    if (!validateCSRFToken($csrf_token)) {
        $error = 'Invalid request. Please try again.';
    } elseif (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($username) < 3 || strlen($username) > 20) {
        $error = 'Username must be between 3 and 20 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = 'Username can only contain letters, numbers, and underscores.';
    } else {
        // Check kung naa nay existing user
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);
        
        if ($stmt->fetch()) {
            $error = 'Email or username already exists.';
        } else {
            // Insert new user
            $verification_token = bin2hex(random_bytes(32));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name, phone, verification_token, is_verified) VALUES (?, ?, ?, ?, ?, ?, 0)");
            
            if ($stmt->execute([$username, $email, $hashed_password, $full_name, $phone, $verification_token])) {
                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                $host = $_SERVER['HTTP_HOST'];
                // Removed slash in link
                $verification_link = $protocol . '://' . $host . '/streetgo/verify.php?token=' . $verification_token;
                
                $success = 'Registration successful! <br><br>
                           <a href="login.php" class="btn btn-sm btn-primary">Login Now</a>';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}

$csrf_token = generateCSRFToken();

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card">
            <div class="text-center mb-4">
                <h2><i class="fas fa-user-plus text-primary"></i> Create Account</h2>
                <p class="subtitle">Join StreetGo and order delicious Filipino street food</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
            </div>
            <?php else: ?>
            
            <!-- Removed slash in action -->
            <form method="POST" action="register.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="mb-3">
                    <label class="form-label">Full Name *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="full_name" class="form-control" placeholder="Juan Dela Cruz" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Username *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-at"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="juandelacruz" required pattern="[a-zA-Z0-9_]+" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Email Address *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="juan@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" name="phone" class="form-control" placeholder="0912 345 6789" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Password *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Minimum 8 characters" required minlength="8">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Confirm Password *</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat your password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-3">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <p class="mb-0">Already have an account? <a href="login.php" class="text-primary fw-bold">Login here</a></p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>