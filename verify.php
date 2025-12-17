<?php
session_start();
require_once __DIR__ . '/config/database.php';

$error = '';
$success = '';

if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = sanitize($_GET['token']);
    
    $stmt = $pdo->prepare("SELECT id, email, is_verified FROM users WHERE verification_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    
    if ($user) {
        if ($user['is_verified']) {
            $success = 'Your email is already verified. You can login now.';
        } else {
            $updateStmt = $pdo->prepare("UPDATE users SET is_verified = TRUE, verification_token = NULL WHERE id = ?");
            if ($updateStmt->execute([$user['id']])) {
                $success = 'Email verified successfully! You can now login to your account.';
            } else {
                $error = 'Verification failed. Please try again.';
            }
        }
    } else {
        $error = 'Invalid or expired verification link.';
    }
} else {
    $error = 'No verification token provided.';
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="auth-section">
    <div class="container">
        <div class="auth-card text-center">
            <?php if ($success): ?>
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="text-success">Email Verified!</h2>
            <p class="text-muted mb-4"><?php echo $success; ?></p>
            <a href="/login.php" class="btn btn-primary btn-lg">
                <i class="fas fa-sign-in-alt"></i> Login Now
            </a>
            <?php else: ?>
            <div class="mb-4">
                <i class="fas fa-exclamation-circle text-danger" style="font-size: 5rem;"></i>
            </div>
            <h2 class="text-danger">Verification Failed</h2>
            <p class="text-muted mb-4"><?php echo $error; ?></p>
            <a href="/register.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Register Again
            </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
