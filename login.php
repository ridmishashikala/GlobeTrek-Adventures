<?php
/**
 * login.php
 * Handles user login with password verification and sets sessions.
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

// Check if redirected after successful registration
if (isset($_SESSION['registration_success'])) {
    $success_msg = $_SESSION['registration_success'];
    unset($_SESSION['registration_success']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_msg = "Please fill in all fields.";
    } else {
        try {
            // Retrieve user details
            $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Set session details
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                // Redirect to homepage
                header("Location: index.php");
                exit();
            } else {
                $error_msg = "Incorrect email address or password.";
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
    <title>Log In | GlobeTrek Adventures</title>
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
            max-width: 480px;
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
        .demo-credentials {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 1rem;
            margin-top: 1.5rem;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="brand-header">
            <i class="bi bi-compass"></i>
            <h2>Globe<span>Trek</span> Adventures</h2>
            <p class="text-white-50 mb-0">Travel & Tourism Management System</p>
        </div>
        
        <div class="card-body">
            <!-- Success Notification -->
            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($success_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Error Notification -->
            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <!-- Email Field -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-envelope-fill text-muted"></i></span>
                        <input type="email" class="form-control form-control-custom border-start-0" id="email" name="email" placeholder="customer@globetrek.com" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <!-- Password Field -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label">Password</label>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-cbd5e1" style="border-radius: 12px 0 0 12px;"><i class="bi bi-lock-fill text-muted"></i></span>
                        <input type="password" class="form-control form-control-custom border-start-0" id="password" name="password" placeholder="••••••••" required style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>

                <button type="submit" class="btn btn-auth w-100">Log In</button>
            </form>

            <div class="text-center mt-4">
                <span class="text-muted">Don't have an account?</span> 
                <a href="register.php" class="link-text ms-1">Register Here</a>
            </div>

            <!-- Demo Credentials for Testing (Assessment helper) -->
            <div class="demo-credentials mt-4">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle-fill text-primary me-1"></i> Testing Credentials (Password: password123)</div>
                <div class="row">
                    <div class="col-12"><strong>Customer:</strong> customer@globetrek.com</div>
                    <div class="col-12"><strong>Agency Staff:</strong> staff@globetrek.com</div>
                    <div class="col-12"><strong>Administrator:</strong> admin@globetrek.com</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
