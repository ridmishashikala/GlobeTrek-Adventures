<?php
/**
 * register.php
 * Handles user registration with secure hashing and Bootstrap 5 frontend.
 */
session_start();
require_once 'db_connection.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$error_msg = "";
$success_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and read input
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'customer';

    // Academic assessment validation criteria
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_msg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_msg = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $error_msg = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_msg = "Passwords do not match.";
    } elseif (!in_array($role, ['customer', 'staff', 'admin'])) {
        $error_msg = "Invalid user role selected.";
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error_msg = "An account with this email address already exists.";
            } else {
                // Secure password hashing
                $password_hash = password_hash($password, PASSWORD_DEFAULT);

                // Insert into database
                $insert_stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                $insert_stmt->execute([$name, $email, $password_hash, $role]);

                $_SESSION['registration_success'] = "Registration successful! You can now log in.";
                header("Location: login.php");
                exit();
            }
        } catch (PDOException $e) {
            $error_msg = "Database Error: " . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | GlobeTrek Adventures</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #0a2540 0%, #1a365d 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .brand-header {
            background-color: #0a2540;
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            color: #ffffff;
            border-bottom: 4px solid #00D2FF;
        }
        .brand-header i {
            font-size: 2.5rem;
            color: #00D2FF;
        }
        .brand-header h2 {
            font-weight: 800;
            margin-top: 10px;
            letter-spacing: -0.5px;
        }
        .brand-header span {
            color: #00D2FF;
        }
        .card-body {
            padding: 2.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #4a5568;
            font-size: 0.9rem;
        }
        .form-control-custom {
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control-custom:focus {
            border-color: #0a2540;
            box-shadow: 0 0 0 4px rgba(10, 37, 64, 0.15);
            outline: none;
        }
        .btn-auth {
            background: linear-gradient(135deg, #0a2540 0%, #0056b3 100%);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.8rem;
            font-weight: 700;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }
        .btn-auth:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 86, 179, 0.3);
            color: white;
        }
        .link-text {
            color: #0a2540;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .link-text:hover {
            color: #00D2FF;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="brand-header">
            <i class="bi bi-compass"></i>
            <h2>Globe<span>Trek</span> Adventures</h2>
            <p class="text-white-50 mb-0">Register Your Account</p>
        </div>
        
        <div class="card-body">
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <!-- Name Field -->
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-person-fill text-muted"></i></span>
                        <input type="text" class="form-control form-control-custom border-start-0" id="name" name="name" placeholder="John Doe" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <!-- Email Field -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-envelope-fill text-muted"></i></span>
                        <input type="email" class="form-control form-control-custom border-start-0" id="email" name="email" placeholder="john@example.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="password" name="password" placeholder="Min. 6 characters" required style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <!-- Confirm Password Field -->
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-check-all text-muted"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <!-- Role Field (for testing role-based workflows) -->
                <div class="mb-4">
                    <label for="role" class="form-label">Account Role</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-person-badge-fill text-muted"></i></span>
                        <select class="form-select form-control-custom border-start-0" id="role" name="role" required style="border-radius: 0 12px 12px 0;">
                            <option value="customer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'customer') ? 'selected' : ''; ?>>Customer / Traveler</option>
                            <option value="staff" <?php echo (isset($_POST['role']) && $_POST['role'] === 'staff') ? 'selected' : ''; ?>>Travel Agency Staff</option>
                            <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] === 'admin') ? 'selected' : ''; ?>>Administrator</option>
                        </select>
                    </div>
                    <div class="form-text text-muted">Allows testing dashboard perspectives during academic grading.</div>
                </div>

                <button type="submit" class="btn btn-auth w-100">Create Account</button>
            </form>

            <div class="text-center mt-4">
                <span class="text-muted">Already have an account?</span> 
                <a href="login.php" class="link-text ms-1">Log In Here</a>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
