<?php
/**
 * dashboard.php
 * Unified Multi-Role Dashboard (Customer, Staff, Admin)
 * Handles booking simulations, package updates, role creation, and reports.
 */
session_start();
require_once 'db_connection.php';

// 1. Force Login / Handle Access Control
if (!isset($_SESSION['user'])) {
    // Render a friendly login prompt container
    $login_required = true;
} else {
    $login_required = false;
    $user = $_SESSION['user'];
    $user_id = $user['id'];
    $user_role = $user['role'];
    $user_email = $user['email'];
}

$alert_msg = "";
$alert_type = ""; // 'success' or 'danger'

// 2. Handle POST Actions (Only if logged in)
if (!$login_required && $_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION A: Customer - Booking Simulation
    if (isset($_POST['action_create_booking']) && $user_role === 'customer') {
        $package_id = intval($_POST['package_id'] ?? 0);
        $travel_date = $_POST['travel_date'] ?? '';
        $travelers = intval($_POST['travelers'] ?? 1);

        if ($package_id <= 0 || empty($travel_date) || $travelers <= 0) {
            $alert_msg = "Please fill in all booking simulation fields.";
            $alert_type = "danger";
        } else {
            try {
                // Fetch package details to calculate total price
                $pkg_stmt = $pdo->prepare("SELECT price FROM packages WHERE id = ?");
                $pkg_stmt->execute([$package_id]);
                $pkg = $pkg_stmt->fetch();

                if ($pkg) {
                    $total_price = $pkg['price'] * $travelers;
                    
                    // Insert booking
                    $book_stmt = $pdo->prepare("INSERT INTO bookings (user_id, package_id, travel_date, travelers, total_price, status) VALUES (?, ?, ?, ?, ?, 'Pending')");
                    $book_stmt->execute([$user_id, $package_id, $travel_date, $travelers, $total_price]);
                    
                    $alert_msg = "Booking simulation submitted successfully! Pending approval from travel staff.";
                    $alert_type = "success";
                } else {
                    $alert_msg = "Selected package does not exist.";
                    $alert_type = "danger";
                }
            } catch (PDOException $e) {
                $alert_msg = "Error creating booking: " . htmlspecialchars($e->getMessage());
                $alert_type = "danger";
            }
        }
    }

    // ACTION B: Staff - Process Booking Status (Approve/Reject)
    if (isset($_POST['action_update_booking_status']) && $user_role === 'staff') {
        $booking_id = intval($_POST['booking_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';

        if ($booking_id > 0 && in_array($new_status, ['Approved', 'Rejected'])) {
            try {
                $status_stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $status_stmt->execute([$new_status, $booking_id]);
                $alert_msg = "Booking #$booking_id has been successfully $new_status.";
                $alert_type = "success";
            } catch (PDOException $e) {
                $alert_msg = "Error updating status: " . htmlspecialchars($e->getMessage());
                $alert_type = "danger";
            }
        }
    }

    // ACTION C: Staff - Add New Package
    if (isset($_POST['action_add_package']) && $user_role === 'staff') {
        $title = trim($_POST['title'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $images = trim($_POST['images'] ?? '');
        $expertise_type = $_POST['expertise_type'] ?? '';

        if (empty($title) || empty($destination) || $price <= 0 || empty($description) || empty($expertise_type)) {
            $alert_msg = "Please fill in all package creation fields.";
            $alert_type = "danger";
        } else {
            // Apply default image if URL is blank
            if (empty($images)) {
                $images = "https://images.unsplash.com/photo-1544735716-392fe2489ffa?q=80&w=800";
            }
            try {
                $add_stmt = $pdo->prepare("INSERT INTO packages (title, destination, price, description, images, expertise_type) VALUES (?, ?, ?, ?, ?, ?)");
                $add_stmt->execute([$title, $destination, $price, $description, $images, $expertise_type]);
                $alert_msg = "New package '$title' added successfully to the marketplace!";
                $alert_type = "success";
            } catch (PDOException $e) {
                $alert_msg = "Error adding package: " . htmlspecialchars($e->getMessage());
                $alert_type = "danger";
            }
        }
    }

    // ACTION D: Staff - Resolve Inquiry
    if (isset($_POST['action_resolve_inquiry']) && $user_role === 'staff') {
        $inquiry_id = intval($_POST['inquiry_id'] ?? 0);
        if ($inquiry_id > 0) {
            try {
                $inq_stmt = $pdo->prepare("UPDATE inquiries SET status = 'Resolved' WHERE id = ?");
                $inq_stmt->execute([$inquiry_id]);
                $alert_msg = "Inquiry #$inquiry_id marked as Resolved.";
                $alert_type = "success";
            } catch (PDOException $e) {
                $alert_msg = "Error resolving inquiry: " . htmlspecialchars($e->getMessage());
                $alert_type = "danger";
            }
        }
    }

    // ACTION E: Admin - Create Staff Account
    if (isset($_POST['action_create_staff']) && $user_role === 'admin') {
        $name = trim($_POST['staff_name'] ?? '');
        $email = trim($_POST['staff_email'] ?? '');
        $password = $_POST['staff_password'] ?? '';
        $role = $_POST['staff_role'] ?? 'staff';

        if (empty($name) || empty($email) || empty($password)) {
            $alert_msg = "All fields are required to create staff accounts.";
            $alert_type = "danger";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $alert_msg = "Please enter a valid email address.";
            $alert_type = "danger";
        } elseif (strlen($password) < 6) {
            $alert_msg = "Password must be at least 6 characters long.";
            $alert_type = "danger";
        } else {
            try {
                // Check if email already exists
                $chk_stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $chk_stmt->execute([$email]);
                if ($chk_stmt->fetch()) {
                    $alert_msg = "An account with this email address already exists.";
                    $alert_type = "danger";
                } else {
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $ins_staff = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
                    $ins_staff->execute([$name, $email, $password_hash, $role]);
                    
                    $alert_msg = "New $role account created successfully for $name.";
                    $alert_type = "success";
                }
            } catch (PDOException $e) {
                $alert_msg = "Error creating staff: " . htmlspecialchars($e->getMessage());
                $alert_type = "danger";
            }
        }
    }
}

// 3. Fetch Dashboard Queries (Only if logged in)
if (!$login_required) {
    try {
        // Fetch all packages for dropdown references
        $pkg_dropdown_stmt = $pdo->query("SELECT id, title, price FROM packages ORDER BY title ASC");
        $all_packages = $pkg_dropdown_stmt->fetchAll();

        if ($user_role === 'customer') {
            // Customer - Fetch personal bookings
            $bookings_query = "SELECT b.id, b.travel_date, b.travelers, b.total_price, b.status, b.created_at, p.title, p.destination 
                               FROM bookings b 
                               JOIN packages p ON b.package_id = p.id 
                               WHERE b.user_id = ? 
                               ORDER BY b.created_at DESC";
            $book_stmt = $pdo->prepare($bookings_query);
            $book_stmt->execute([$user_id]);
            $my_bookings = $book_stmt->fetchAll();

            // Transportation bookings
$transport_stmt = $pdo->prepare("
    SELECT tb.*, t.transport_type, t.vehicle_name, t.price
    FROM transportation_bookings tb
    JOIN transportation t ON tb.transport_id = t.id
    WHERE tb.user_id = ?
");
$transport_stmt->execute([$user_id]);
$my_transportations = $transport_stmt->fetchAll();


// Accommodation bookings
$accommodation_stmt = $pdo->prepare("
    SELECT ab.*, a.hotel_name, a.room_type, a.price_per_night
    FROM accommodation_bookings ab
    JOIN accommodations a ON ab.accommodation_id = a.id
    WHERE ab.user_id = ?
");
$accommodation_stmt->execute([$user_id]);
$my_accommodations = $accommodation_stmt->fetchAll();


        


            // Customer - Fetch personal inquiries (match by email)
            $inq_stmt = $pdo->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC");
            $inq_stmt->execute([$user_email]);
            $my_inquiries = $inq_stmt->fetchAll();
        }

        if ($user_role === 'staff') {
            // Staff - Fetch stats
            $pending_books_count = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Pending'")->fetchColumn();
            $pending_inqs_count = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE status = 'Pending'")->fetchColumn();
            $approved_revenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'Approved'")->fetchColumn();
            $approved_revenue = $approved_revenue ? $approved_revenue : 0;

            // Staff - Fetch all bookings
            $all_bookings_query = "SELECT b.id, b.travel_date, b.travelers, b.total_price, b.status, b.created_at, u.name as customer_name, u.email as customer_email, p.title 
                                   FROM bookings b 
                                   JOIN users u ON b.user_id = u.id 
                                   JOIN packages p ON b.package_id = p.id 
                                   ORDER BY b.created_at DESC";
            $all_bookings = $pdo->query($all_bookings_query)->fetchAll();

            // Staff - Fetch all inquiries
            $all_inquiries = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC")->fetchAll();

            // Staff - Fetch packages catalog
            $packages_catalog = $pdo->query("SELECT * FROM packages ORDER BY id DESC")->fetchAll();
        }

        if ($user_role === 'admin') {
            // Admin - Fetch stats
            $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
            $total_packages = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
            $total_bookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
            $total_revenue = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'Approved'")->fetchColumn();
            $total_revenue = $total_revenue ? $total_revenue : 0;

            // Admin - Fetch all users
            $all_users = $pdo->query("SELECT id, name, email, role, created_at FROM users ORDER BY role ASC, name ASC")->fetchAll();
        }

    } catch (PDOException $e) {
        die("Error loading dashboard details: " . htmlspecialchars($e->getMessage()));
    }
}

// Pre-fill package selection from index.php GET
$preselected_package = intval($_GET['book_package_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | GlobeTrek Adventures</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
        }
        .dashboard-container {
            min-height: 80vh;
            padding-top: 2rem;
            padding-bottom: 4rem;
        }
        .nav-tabs-custom {
            border: none;
            background-color: #e2e8f0;
            border-radius: 12px;
            padding: 0.35rem;
        }
        .nav-tabs-custom .nav-link {
            border: none;
            border-radius: 9px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            color: #64748b !important;
            transition: all 0.3s ease;
        }
        .nav-tabs-custom .nav-link.active {
            background-color: #ffffff;
            color: #0a2540 !important;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }
        .dashboard-card-accent {
            border-top: 4px solid #0a2540 !important;
        }
    </style>
</head>
<body>

    <!-- Responsive Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top navbar-custom">
        <div class="container">
            <a class="navbar-brand text-gradient d-flex align-items-center" href="index.php">
                <i class="bi bi-compass-fill me-2 fs-3 text-primary"></i>Globe<span>Trek</span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#expertiseSection">Our Expertise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#packagesSection">Browse Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#inquirySection">Contact Us</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (!$login_required): ?>
                        <span class="navbar-text me-2 d-none d-lg-inline">
                            Logged in as: <strong class="text-primary"><?php echo htmlspecialchars($user['name']); ?></strong>
                        </span>
                        <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-box-arrow-in-right me-1"></i> Log In</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="container dashboard-container">
        
        <!-- ==============================================
             0. LOGIN REQUIRED PROMPT (GUEST PERSPECTIVE)
             ============================================== -->
        <?php if ($login_required): ?>
            <div class="row justify-content-center py-5">
                <div class="col-md-6 text-center">
                    <div class="dashboard-card py-5">
                        <i class="bi bi-lock-fill text-primary display-3 mb-3"></i>
                        <h2 class="fw-bold">Authentication Required</h2>
                        <p class="text-muted mx-auto mb-4" style="max-width: 400px;">Please log in or register a new travel profile to access simulated bookings and management panels.</p>
                        <div class="d-flex justify-content-center gap-3">
                            <a href="login.php" class="btn btn-primary rounded-pill px-4 py-2">Log In Here</a>
                            <a href="register.php" class="btn btn-outline-primary rounded-pill px-4 py-2">Register Profile</a>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>

            <!-- Notification Alerts -->
            <?php if (!empty($alert_msg)): ?>
                <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show rounded-3 mb-4" role="alert">
                    <i class="bi <?php echo ($alert_type === 'success') ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill'; ?> me-2"></i>
                    <?php echo htmlspecialchars($alert_msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- User Banner -->
            <div class="dashboard-card mb-4 bg-white shadow-sm border-0 rounded-4">
                <div class="row align-items-center justify-content-between p-2">
                    <div class="col-md-8">
                        <span class="text-uppercase text-primary fw-bold fs-7" style="letter-spacing: 1px;">GlobeTrek Operations Portal</span>
                        <h1 class="fw-bold mt-1 mb-2">Welcome Back, <?php echo htmlspecialchars($user['name']); ?></h1>
                        <p class="text-muted mb-0"><i class="bi bi-person-fill text-primary"></i> Account Type: <strong class="text-uppercase text-primary"><?php echo htmlspecialchars($user_role); ?></strong> &nbsp;|&nbsp; <i class="bi bi-envelope text-primary"></i> Email: <?php echo htmlspecialchars($user_email); ?></p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="index.php" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-house-door-fill me-1"></i> Visit Homepage</a>
                    </div>
                </div>
            </div>

            <!-- ==============================================
                 1. CUSTOMER DASHBOARD PERSPECTIVE
                 ============================================== -->
            <?php if ($user_role === 'customer'): ?>
                
                <div class="row">
                    <!-- Left: Booking Simulation Card -->
                    <div class="col-lg-5 mb-4">
                        <div class="dashboard-card dashboard-card-accent border-0 shadow-sm rounded-4 h-100">
                            <h3 class="fw-bold text-dark mb-3"><i class="bi bi-bookmark-plus text-primary me-2"></i>Booking Simulation</h3>
                            <p class="text-muted small">Choose an excursion package, select a travel date, and set travelers. The system simulates total price dynamically.</p>
                            
                            <form action="dashboard.php" method="POST">
                                <input type="hidden" name="action_create_booking" value="1">

                                <div class="dashboard-card border-0 shadow-sm rounded-4 mb-4 p-3">
                                  <h4>Transportation</h4>

                                   <a href="transportation.php"
                                      class="btn btn-primary">
                                      View Transportation
                                   </a>

                                   

                                   <div class="dashboard-card border-0 shadow-sm rounded-4 p-3">
                                  <h4>Accommodation</h4>

                                  <a href="accommodations.php"
                                     class="btn btn-success">
                                     View Accommodation
                                  </a>

                                  
                                   </div>
                </div>               
                                
                                <!-- Select Package -->
                                <div class="mb-3">
                                    <label for="package_id" class="form-label">Select Package</label>
                                    <select class="form-select form-control-custom" id="package_id" name="package_id" required onchange="calculatePrice()">
                                        <option value="" data-price="0">-- Choose Package --</option>
                                        <?php foreach ($all_packages as $pkg): ?>
                                            <option value="<?php echo $pkg['id']; ?>" data-price="<?php echo $pkg['price']; ?>" <?php echo ($preselected_package === intval($pkg['id'])) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($pkg['title']); ?> ($<?php echo number_format($pkg['price'], 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <!-- Travel Date -->
                                <div class="mb-3">
                                    <label for="travel_date" class="form-label">Travel Date</label>
                                    <input type="date" class="form-control form-control-custom" id="travel_date" name="travel_date" min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <!-- Group Size -->
                                <div class="mb-3">
                                    <label for="travelers" class="form-label">Number of Travelers</label>
                                    <input type="number" class="form-control form-control-custom" id="travelers" name="travelers" min="1" max="50" value="1" required oninput="calculatePrice()">
                                </div>
                                
                                <!-- Display Simulation Total -->
                                <div class="p-3 bg-light rounded-3 mb-4">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold text-muted">Estimated Total Cost:</span>
                                        <span id="booking-total-price" class="fs-4 fw-extrabold text-primary">$0.00</span>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2"><i class="bi bi-send-check me-1"></i> Submit Booking</button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Booking History & Inquiries -->
                    <div class="col-lg-7 mb-4">
                        <div class="dashboard-card border-0 shadow-sm rounded-4 mb-4">
                            <h3 class="fw-bold text-dark mb-4"><i class="bi bi-journal-check text-primary me-2"></i>My Tour Reservations</h3>
                            
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Package Title</th>
                                            <th>Date</th>
                                            <th>Travelers</th>
                                            <th>Total Paid</th>
                                            <th>Status</th>

                                             

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($my_bookings) > 0): ?>
                                            <?php foreach ($my_bookings as $mb): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $mb['id']; ?></strong></td>
                                                    <td><?php echo htmlspecialchars($mb['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($mb['travel_date']); ?></td>
                                                    <td class="text-center"><?php echo $mb['travelers']; ?></td>
                                                    <td class="fw-bold text-dark">$<?php echo htmlspecialchars(number_format($mb['total_price'], 2)); ?></td>
                                                    <td>
                                                        <?php if ($mb['status'] === 'Pending'): ?>
                                                            <span class="badge badge-status-pending rounded-pill px-3 py-1 text-warning"><i class="bi bi-clock-history"></i> Pending</span>
                                                        <?php elseif ($mb['status'] === 'Approved'): ?>
                                                            <span class="badge badge-status-approved rounded-pill px-3 py-1 text-success"><i class="bi bi-check-circle-fill"></i> Approved</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-status-rejected rounded-pill px-3 py-1 text-danger"><i class="bi bi-x-circle-fill"></i> Rejected</span>
                                                        <?php endif; ?>
                                                    </td>

                                                    <td>
                                                      <a href="payment.php?booking_id=<?php echo $mb['id']; ?>"
                                                         class="btn btn-success btn-sm">
                                                         Pay
                                                      </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">You have no active tour bookings. Use the simulation form to book one!</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                        <div class="dashboard-card border-0 shadow-sm rounded-4 mb-4">
    <h3 class="fw-bold text-dark mb-3">My Transportation Reservations</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Type</th>
                <th>Vehicle</th>
                <th>Price</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($my_transportations)): ?>
                <?php foreach ($my_transportations as $tr): ?>
                    <tr>
                        <td><?= htmlspecialchars($tr['transport_type']) ?></td>
                        <td><?= htmlspecialchars($tr['vehicle_name']) ?></td>
                        <td>$<?= number_format($tr['price'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No transportation bookings found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>


<div class="dashboard-card border-0 shadow-sm rounded-4 mb-4">
    <h3 class="fw-bold text-dark mb-3">My Accommodation Reservations</h3>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Hotel</th>
                <th>Room Type</th>
                <th>Price Per Night</th>
            </tr>
        </thead>

        <tbody>
            <?php if (!empty($my_accommodations)): ?>
                <?php foreach ($my_accommodations as $ac): ?>
                    <tr>
                        <td><?= htmlspecialchars($ac['hotel_name']) ?></td>
                        <td><?= htmlspecialchars($ac['room_type']) ?></td>
                        <td>$<?= number_format($ac['price_per_night'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">No accommodation bookings found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

         

                        <!-- Personal Query Log -->
                        <div class="dashboard-card border-0 shadow-sm rounded-4">
                            <h3 class="fw-bold text-dark mb-3"><i class="bi bi-chat-left-text-fill text-primary me-2"></i>My Inquiries Status</h3>
                            <p class="text-muted small">Queries submitted under <strong><?php echo htmlspecialchars($user_email); ?></strong> on our contact form.</p>
                            
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($my_inquiries) > 0): ?>
                                            <?php foreach ($my_inquiries as $mi): ?>
                                                <tr>
                                                    <td><?php echo date('Y-m-d', strtotime($mi['created_at'])); ?></td>
                                                    <td><strong><?php echo htmlspecialchars($mi['subject']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($mi['message']); ?></td>
                                                    <td>
                                                        <?php if ($mi['status'] === 'Pending'): ?>
                                                            <span class="badge bg-warning rounded-pill px-2 py-1 text-dark">Pending Coordinator</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success rounded-pill px-2 py-1 text-white">Resolved / Replied</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted">No query records found. Use our footer form to submit inquiries.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    // Real-time calculation of total price for the simulation booking
                    function calculatePrice() {
                        var packageSelect = document.getElementById("package_id");
                        var selectedOption = packageSelect.options[packageSelect.selectedIndex];
                        var price = parseFloat(selectedOption.getAttribute("data-price"));
                        var travelers = parseInt(document.getElementById("travelers").value);
                        
                        if (isNaN(price)) price = 0;
                        if (isNaN(travelers) || travelers < 1) travelers = 1;
                        
                        var total = price * travelers;
                        document.getElementById("booking-total-price").innerText = "$" + total.toFixed(2);
                    }
                    // Run once on page load to check parameters
                    window.onload = function() {
                        calculatePrice();
                    };
                </script>

            <!-- ==============================================
                 2. STAFF DASHBOARD PERSPECTIVE
                 ============================================== -->
            <?php elseif ($user_role === 'staff'): ?>
                
                <!-- Stat Cards Row -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="dashboard-card stat-widget shadow-sm border-0">
                            <div class="stat-icon"><i class="bi bi-clock-history"></i></div>
                            <div>
                                <div class="stat-val"><?php echo $pending_books_count; ?></div>
                                <div class="stat-lbl">Pending Bookings</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card stat-widget shadow-sm border-0">
                            <div class="stat-icon"><i class="bi bi-chat-left-exclamation-fill"></i></div>
                            <div>
                                <div class="stat-val"><?php echo $pending_inqs_count; ?></div>
                                <div class="stat-lbl">Pending Inquiries</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dashboard-card stat-widget shadow-sm border-0">
                            <div class="stat-icon text-success" style="background-color: rgba(25, 135, 84, 0.1);"><i class="bi bi-cash-stack"></i></div>
                            <div>
                                <div class="stat-val text-success">$<?php echo number_format($approved_revenue, 2); ?></div>
                                <div class="stat-lbl">Approved Revenue</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Tabs for Staff operations -->
                <ul class="nav nav-tabs nav-tabs-custom mb-4" id="staffTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="bookings-tab" data-bs-toggle="tab" data-bs-target="#bookings-panel" type="button" role="tab">Bookings Management</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="packages-tab" data-bs-toggle="tab" data-bs-target="#packages-panel" type="button" role="tab">Tour Package catalog</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="inquiries-tab" data-bs-toggle="tab" data-bs-target="#inquiries-panel" type="button" role="tab">Inquiries Inbox</button>
                    </li>
                </ul>

                <div class="tab-content" id="staffTabContent">
                    
                    <!-- PANEL 1: Bookings Management -->
                    <div class="tab-pane fade show active" id="bookings-panel" role="tabpanel">
                        <div class="dashboard-card border-0 shadow-sm rounded-4">
                            <h3 class="fw-bold mb-4">Customer Package Bookings</h3>
                            
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer Info</th>
                                            <th>Package Title</th>
                                            <th>Date</th>
                                            <th>Travelers</th>
                                            <th>Total Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($all_bookings) > 0): ?>
                                            <?php foreach ($all_bookings as $ab): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $ab['id']; ?></strong></td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($ab['customer_name']); ?></div>
                                                        <div class="text-muted small"><?php echo htmlspecialchars($ab['customer_email']); ?></div>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($ab['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($ab['travel_date']); ?></td>
                                                    <td class="text-center"><?php echo $ab['travelers']; ?></td>
                                                    <td class="fw-bold text-dark">$<?php echo htmlspecialchars(number_format($ab['total_price'], 2)); ?></td>
                                                    <td>
                                                        <?php if ($ab['status'] === 'Pending'): ?>
                                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1">Pending</span>
                                                        <?php elseif ($ab['status'] === 'Approved'): ?>
                                                            <span class="badge bg-success rounded-pill px-3 py-1">Approved</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger rounded-pill px-3 py-1">Rejected</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($ab['status'] === 'Pending'): ?>
                                                            <form action="dashboard.php" method="POST" class="d-inline">
                                                                <input type="hidden" name="action_update_booking_status" value="1">
                                                                <input type="hidden" name="booking_id" value="<?php echo $ab['id']; ?>">
                                                                <button type="submit" name="status" value="Approved" class="btn btn-success btn-xs rounded-pill me-1"><i class="bi bi-check"></i> Approve</button>
                                                                <button type="submit" name="status" value="Rejected" class="btn btn-danger btn-xs rounded-pill"><i class="bi bi-x"></i> Reject</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted small">Processed</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted">No package bookings found in database.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL 2: Package Catalogue & Adding -->
                    <div class="tab-pane fade" id="packages-panel" role="tabpanel">
                        <div class="row">
                            <!-- Left: Package Add Form -->
                            <div class="col-lg-5 mb-4">
                                <div class="dashboard-card border-0 shadow-sm rounded-4">
                                    <h3 class="fw-bold mb-3"><i class="bi bi-plus-circle-fill text-primary me-2"></i>Add Travel Package</h3>
                                    
                                    <form action="dashboard.php" method="POST">
                                        <input type="hidden" name="action_add_package" value="1">
                                        
                                        <div class="mb-3">
                                            <label for="title" class="form-label">Package Title</label>
                                            <input type="text" class="form-control form-control-custom" id="title" name="title" placeholder="e.g. Negombo Lagoon Fishing Excursion" required>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="destination" class="form-label">Destination Address</label>
                                            <input type="text" class="form-control form-control-custom" id="destination" name="destination" placeholder="e.g. Negombo, Sri Lanka" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="price" class="form-label">Price per Traveler ($)</label>
                                            <input type="number" step="0.01" class="form-control form-control-custom" id="price" name="price" placeholder="45.00" required>
                                        </div>

                                        <div class="mb-3">
                                            <label for="expertise_type" class="form-label">Expertise Category</label>
                                            <select class="form-select form-control-custom" id="expertise_type" name="expertise_type" required>
                                                <option value="">-- Choose Category --</option>
                                                <option value="Culture & Heritage">Culture & Heritage</option>
                                                <option value="Nature & Adventure">Nature & Adventure</option>
                                                <option value="Wellness & Spa">Wellness & Spa</option>
                                                <option value="Wildlife Safari">Wildlife Safari</option>
                                                <option value="Shore Excursions">Shore Excursions</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label for="images" class="form-label">Image URL / Path</label>
                                            <input type="text" class="form-control form-control-custom" id="images" name="images" placeholder="https://images.unsplash.com/... or blank for default">
                                        </div>

                                        <div class="mb-3">
                                            <label for="description" class="form-label">Package Description</label>
                                            <textarea class="form-control form-control-custom" id="description" name="description" rows="4" placeholder="Detail the activities, duration, tour guidelines..." required></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-primary w-100 rounded-pill py-2"><i class="bi bi-file-earmark-plus-fill me-1"></i> Add Package to Site</button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Right: Live Packages List -->
                            <div class="col-lg-7 mb-4">
                                <div class="dashboard-card border-0 shadow-sm rounded-4">
                                    <h3 class="fw-bold mb-4">Current Active Packages</h3>
                                    
                                    <div class="table-responsive">
                                        <table class="table align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Title</th>
                                                    <th>Category</th>
                                                    <th>Price</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($packages_catalog as $cat): ?>
                                                    <tr>
                                                        <td><strong>#<?php echo $cat['id']; ?></strong></td>
                                                        <td>
                                                            <div class="fw-semibold"><?php echo htmlspecialchars($cat['title']); ?></div>
                                                            <div class="text-muted small"><?php echo htmlspecialchars($cat['destination']); ?></div>
                                                        </td>
                                                        <td><span class="badge bg-primary"><?php echo htmlspecialchars($cat['expertise_type']); ?></span></td>
                                                        <td class="fw-bold text-dark">$<?php echo htmlspecialchars(number_format($cat['price'], 2)); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PANEL 3: Inquiries Inbox -->
                    <div class="tab-pane fade" id="inquiries-panel" role="tabpanel">
                        <div class="dashboard-card border-0 shadow-sm rounded-4">
                            <h3 class="fw-bold mb-4">Customer Inquiries Inbox</h3>
                            
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Customer Details</th>
                                            <th>Subject</th>
                                            <th>Message</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($all_inquiries) > 0): ?>
                                            <?php foreach ($all_inquiries as $inq): ?>
                                                <tr>
                                                    <td><strong>#<?php echo $inq['id']; ?></strong></td>
                                                    <td>
                                                        <div class="fw-semibold"><?php echo htmlspecialchars($inq['name']); ?></div>
                                                        <div class="text-muted small"><?php echo htmlspecialchars($inq['email']); ?></div>
                                                    </td>
                                                    <td><strong><?php echo htmlspecialchars($inq['subject']); ?></strong></td>
                                                    <td><?php echo htmlspecialchars($inq['message']); ?></td>
                                                    <td>
                                                        <?php if ($inq['status'] === 'Pending'): ?>
                                                            <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Pending</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success text-white rounded-pill px-2 py-1">Resolved</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($inq['status'] === 'Pending'): ?>
                                                            <form action="dashboard.php" method="POST">
                                                                <input type="hidden" name="action_resolve_inquiry" value="1">
                                                                <input type="hidden" name="inquiry_id" value="<?php echo $inq['id']; ?>">
                                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill"><i class="bi bi-check-circle"></i> Resolve</button>
                                                            </form>
                                                        <?php else: ?>
                                                            <span class="text-muted small">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted">No inquiry records found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>

            <!-- ==============================================
                 3. ADMINISTRATOR DASHBOARD PERSPECTIVE
                 ============================================== -->
            <?php elseif ($user_role === 'admin'): ?>
                
                <!-- Admin Stats Widgets -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="dashboard-card stat-widget shadow-sm border-0 h-100">
                            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                            <div>
                                <div class="stat-val"><?php echo $total_users; ?></div>
                                <div class="stat-lbl">System Profiles</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="dashboard-card stat-widget shadow-sm border-0 h-100">
                            <div class="stat-icon"><i class="bi bi-box-fill"></i></div>
                            <div>
                                <div class="stat-val"><?php echo $total_packages; ?></div>
                                <div class="stat-lbl">Active Packages</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="dashboard-card stat-widget shadow-sm border-0 h-100">
                            <div class="stat-icon"><i class="bi bi-journal-album"></i></div>
                            <div>
                                <div class="stat-val"><?php echo $total_bookings; ?></div>
                                <div class="stat-lbl">Total Bookings</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3 mb-md-0">
                        <div class="dashboard-card stat-widget shadow-sm border-0 h-100">
                            <div class="stat-icon text-success" style="background-color: rgba(25, 135, 84, 0.1);"><i class="bi bi-currency-dollar"></i></div>
                            <div>
                                <div class="stat-val text-success">$<?php echo number_format($total_revenue, 2); ?></div>
                                <div class="stat-lbl">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Left: Create Staff Account Form -->
                    <div class="col-lg-5 mb-4">
                        <div class="dashboard-card border-0 shadow-sm rounded-4">
                            <h3 class="fw-bold mb-3"><i class="bi bi-shield-plus text-primary me-2"></i>Create Agency Account</h3>
                            <p class="text-muted small">Register accounts for staff members to manage package entries and confirm traveler bookings, or add fellow administrators.</p>
                            
                            <form action="dashboard.php" method="POST">
                                <input type="hidden" name="action_create_staff" value="1">
                                
                                <div class="mb-3">
                                    <label for="staff_name" class="form-label">Full Name</label>
                                    <input type="text" class="form-control form-control-custom" id="staff_name" name="staff_name" placeholder="Sarah Jenkins" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="staff_email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control form-control-custom" id="staff_email" name="staff_email" placeholder="sarah@globetrek.com" required>
                                </div>

                                <div class="mb-3">
                                    <label for="staff_password" class="form-label">Password</label>
                                    <input type="password" class="form-control form-control-custom" id="staff_password" name="staff_password" placeholder="Min 6 characters" required>
                                </div>

                                <div class="mb-4">
                                    <label for="staff_role" class="form-label">System Role</label>
                                    <select class="form-select form-control-custom" id="staff_role" name="staff_role" required>
                                        <option value="staff">Travel Agency Staff</option>
                                        <option value="admin">Administrator</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2"><i class="bi bi-person-check-fill me-1"></i> Register Account</button>
                            </form>
                        </div>
                    </div>

                    <!-- Right: Registered User Directory -->
                    <div class="col-lg-7 mb-4">
                        <div class="dashboard-card border-0 shadow-sm rounded-4">
                            <h3 class="fw-bold mb-4"><i class="bi bi-people-fill text-primary me-2"></i>System Users Directory</h3>
                            
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Profile Name</th>
                                            <th>Email Address</th>
                                            <th>Role</th>
                                            <th>Joined Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($all_users as $usr): ?>
                                            <tr>
                                                <td><strong>#<?php echo $usr['id']; ?></strong></td>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($usr['name']); ?></td>
                                                <td><?php echo htmlspecialchars($usr['email']); ?></td>
                                                <td>
                                                    <?php if ($usr['role'] === 'admin'): ?>
                                                        <span class="badge bg-danger rounded-pill px-3 py-1">Administrator</span>
                                                    <?php elseif ($usr['role'] === 'staff'): ?>
                                                        <span class="badge bg-primary rounded-pill px-3 py-1">Agency Staff</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary rounded-pill px-3 py-1">Traveler</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="small text-muted"><?php echo date('Y-m-d', strtotime($usr['created_at'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        <?php endif; ?>

    </div>

    <!-- Professional Dark Theme Footer -->
    <footer class="footer-custom mt-auto">
        <div class="container">
            <div class="row g-4 mb-4">
                <div class="col-lg-4 col-md-6">
                    <a class="navbar-brand text-gradient d-flex align-items-center mb-3" href="index.php" style="font-size: 1.6rem;">
                        <i class="bi bi-compass-fill me-2 text-primary"></i>Globe<span class="text-white">Trek</span>
                    </a>
                    <p class="text-muted">A premier travel and tourism management agency based in Negombo, Sri Lanka. Offering hand-crafted experiences combining rich colonial history, wellness, and beach sports.</p>
                </div>
                <div class="col-lg-2 col-md-6">
                    <h5>Useful Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="footer-link">Home</a></li>
                        <li><a href="index.php#expertiseSection" class="footer-link">Our Expertise</a></li>
                        <li><a href="index.php#packagesSection" class="footer-link">Browse Packages</a></li>
                        <li><a href="index.php#inquirySection" class="footer-link">Submit Inquiry</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Expertise Types</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php?expertise_type=Culture+%26+Heritage" class="footer-link">Culture & Heritage</a></li>
                        <li><a href="index.php?expertise_type=Nature+%26+Adventure" class="footer-link">Nature & Adventure</a></li>
                        <li><a href="index.php?expertise_type=Wellness+%26+Spa" class="footer-link">Wellness & Spa</a></li>
                        <li><a href="index.php?expertise_type=Wildlife+Safari" class="footer-link">Wildlife Safari</a></li>
                        <li><a href="index.php?expertise_type=Shore+Excursions" class="footer-link">Shore Excursions</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Newsletter</h5>
                    <p class="text-muted">Subscribe to receive exclusive Negombo travel deals and safari discounts.</p>
                    <div class="input-group mb-3">
                        <input type="email" class="form-control bg-dark border-secondary text-white" placeholder="Your Email" style="border-radius: 50px 0 0 50px; font-size: 0.9rem;">
                        <button class="btn btn-primary px-3" type="button" style="border-radius: 0 50px 50px 0;"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0 text-muted">&copy; 2026 GlobeTrek Adventures (Negombo). All Rights Reserved. Built for Academic Assessment.</p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <a href="login.php" class="text-muted text-decoration-none me-3" style="font-size: 0.85rem;"><i class="bi bi-shield-lock-fill text-primary"></i> Internal Login</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
