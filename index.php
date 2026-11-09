<?php
/**
 * index.php
 * GlobeTrek Adventures landing page with dynamic search,
 * Aitken Spence Travels-inspired expertise grid, and MySQL-driven packages.
 */
session_start();
require_once 'db_connection.php';

// Retrieve search and category filters
$search_query = trim($_GET['search_query'] ?? '');
$selected_expertise = trim($_GET['expertise_type'] ?? '');

try {
    // Construct dynamic PDO query based on user search filters
    $sql = "SELECT * FROM packages WHERE 1=1";
    $params = [];

    if (!empty($search_query)) {
        $sql .= " AND (title LIKE ? OR destination LIKE ? OR description LIKE ?)";
        $search_param = "%$search_query%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }

    if (!empty($selected_expertise)) {
        $sql .= " AND expertise_type = ?";
        $params[] = $selected_expertise;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $packages = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Query Error: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GlobeTrek Adventures | Negombo Travel & Tourism</title>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Discover Negombo, Sri Lanka with GlobeTrek Adventures. Book lagoon tours, wellness ayurvedic spa retreats, safaris, and beach excursions.">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts (Outfit) -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom Stylesheet -->
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- Sticky Navigation Bar with Glassmorphism -->
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
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#expertiseSection">Our Expertise</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#packagesSection">Browse Packages</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#inquirySection">Contact Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <?php if (isset($_SESSION['user'])): ?>
                        <span class="navbar-text me-2 d-none d-lg-inline">
                            Hello, <strong class="text-primary"><?php echo htmlspecialchars($_SESSION['user']['name']); ?></strong> 
                            <span class="badge bg-secondary text-uppercase ms-1"><?php echo htmlspecialchars($_SESSION['user']['role']); ?></span>
                        </span>
                        <a href="dashboard.php" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="bi bi-person-fill"></i> Panel</a>
                        <a href="logout.php" class="btn btn-danger btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-right"></i> Log Out</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-box-arrow-in-right me-1"></i> Log In</a>
                        <a href="register.php" class="btn btn-primary rounded-pill px-4">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Stunning Hero Banner Section with Aitken Spence Inspired Background Slider -->
    <section class="hero-section position-relative overflow-hidden" id="homeSection">
        <!-- Background Carousel Slider -->
        <div id="heroSlider" class="carousel slide carousel-fade position-absolute top-0 start-0 w-100 h-100" data-bs-ride="carousel" data-bs-interval="5000" style="z-index: 1;">
            <div class="carousel-inner w-100 h-100">
                <!-- Slide 1: Beach -->
                <div class="carousel-item active w-100 h-100">
                    <div class="hero-slider-img w-100 h-100" style="background-image: url('https://images.unsplash.com/photo-1544735716-392fe2489ffa?q=80&w=1600');"></div>
                </div>
                <!-- Slide 2: Wetland Nature -->
                <div class="carousel-item w-100 h-100">
                    <div class="hero-slider-img w-100 h-100" style="background-image: url('https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?q=80&w=1600');"></div>
                </div>
                <!-- Slide 3: Wildlife Elephant -->
                <div class="carousel-item w-100 h-100">
                    <div class="hero-slider-img w-100 h-100" style="background-image: url('https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?q=80&w=1600');"></div>
                </div>
                <!-- Slide 4: Sigiriya Temple Ruins -->
                <div class="carousel-item w-100 h-100">
                    <div class="hero-slider-img w-100 h-100" style="background-image: url('https://images.unsplash.com/photo-1563182631-0ea522513f5c?q=80&w=1600');"></div>
                </div>
            </div>
        </div>
        <div class="hero-overlay" style="z-index: 2;"></div>
        
        <div class="container hero-content text-center py-5" style="z-index: 3;">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <span class="badge bg-primary px-3 py-2 rounded-pill mb-3 text-uppercase fw-semibold" style="letter-spacing: 1.5px;">Negombo, Sri Lanka</span>
                    <h1 class="display-3 fw-extrabold mb-3">GlobeTrek Adventures</h1>
                    <p class="lead mb-4 text-white-50">Explore pristine canals, golden beaches, rich wildlife safaris, and healing Ayurvedic wellness centers in Sri Lanka's historic coastal capital.</p>
                    
                    <!-- Search Filter Form -->
                    <div class="search-container mx-auto mt-4 p-2 rounded-pill" style="max-width: 800px; background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.25);">
                        <form action="index.php#packagesSection" method="GET" class="row g-2 align-items-center m-0">
                            <!-- Keyword Input -->
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0 text-white"><i class="bi bi-search"></i></span>
                                    <input type="text" name="search_query" class="form-control bg-transparent border-0 text-white placeholder-white-50" placeholder="Search destination, tour package..." value="<?php echo htmlspecialchars($search_query); ?>">
                                </div>
                            </div>
                            
                            <!-- Package Type Dropdown -->
                            <div class="col-md-4 border-start border-white-25">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-0 text-white"><i class="bi bi-grid-fill"></i></span>
                                    <select name="expertise_type" class="form-select bg-transparent border-0 text-white-50" style="color: #ffffff !important;">
                                        <option value="" style="color: #333;">All Expertise Categories</option>
                                        <option value="Culture & Heritage" <?php echo ($selected_expertise === 'Culture & Heritage') ? 'selected' : ''; ?> style="color: #333;">Culture & Heritage</option>
                                        <option value="Nature & Adventure" <?php echo ($selected_expertise === 'Nature & Adventure') ? 'selected' : ''; ?> style="color: #333;">Nature & Adventure</option>
                                        <option value="Wellness & Spa" <?php echo ($selected_expertise === 'Wellness & Spa') ? 'selected' : ''; ?> style="color: #333;">Wellness & Spa</option>
                                        <option value="Wildlife Safari" <?php echo ($selected_expertise === 'Wildlife Safari') ? 'selected' : ''; ?> style="color: #333;">Wildlife Safari</option>
                                        <option value="Shore Excursions" <?php echo ($selected_expertise === 'Shore Excursions') ? 'selected' : ''; ?> style="color: #333;">Shore Excursions</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Search Button -->
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2"><i class="bi bi-search me-1"></i> Find Excursions</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Aitken Spence Inspired Niche Travel Expertise Section -->
    <section class="py-5" id="expertiseSection">
        <div class="container py-4">
            <div class="text-center mb-5">
                <span class="text-primary fw-bold text-uppercase" style="letter-spacing: 1.5px;">Curated Travel Styles</span>
                <h2 class="display-5 fw-bold mt-2">Our Niche Travel Expertise</h2>
                <p class="text-muted mx-auto" style="max-width: 600px;">We specialize in dedicated travel categories crafted by local destination experts to deliver authentic Sri Lankan moments.</p>
            </div>

            <!-- 5-Column Grid Layout -->
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-4">
                
                <!-- Category 1: Culture & Heritage -->
                <div class="col">
                    <a href="index.php?expertise_type=Culture+%26+Heritage#packagesSection" class="expertise-card-link text-decoration-none">
                        <div class="expertise-card" style="background-image: url('https://images.unsplash.com/photo-1544735716-392fe2489ffa?q=80&w=600');">
                            <div class="expertise-overlay"></div>
                            <div class="expertise-card-content text-center">
                                <div class="expertise-icon"><i class="bi bi-bank2"></i></div>
                                <h4 class="expertise-title">Culture & Heritage</h4>
                                <span class="expertise-explore-btn">Explore Packages</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 2: Nature & Adventure -->
                <div class="col">
                    <a href="index.php?expertise_type=Nature+%26+Adventure#packagesSection" class="expertise-card-link text-decoration-none">
                        <div class="expertise-card" style="background-image: url('https://images.unsplash.com/photo-1588668214407-6ea9a6d8c272?q=80&w=600');">
                            <div class="expertise-overlay"></div>
                            <div class="expertise-card-content text-center">
                                <div class="expertise-icon"><i class="bi bi-compass-fill"></i></div>
                                <h4 class="expertise-title">Nature & Adventure</h4>
                                <span class="expertise-explore-btn">Explore Packages</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 3: Wellness & Spa -->
                <div class="col">
                    <a href="index.php?expertise_type=Wellness+%26+Spa#packagesSection" class="expertise-card-link text-decoration-none">
                        <div class="expertise-card" style="background-image: url('https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?q=80&w=600');">
                            <div class="expertise-overlay"></div>
                            <div class="expertise-card-content text-center">
                                <div class="expertise-icon"><i class="bi bi-flower1"></i></div>
                                <h4 class="expertise-title">Wellness & Spa</h4>
                                <span class="expertise-explore-btn">Explore Packages</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 4: Wildlife Safari -->
                <div class="col">
                    <a href="index.php?expertise_type=Wildlife+Safari#packagesSection" class="expertise-card-link text-decoration-none">
                        <div class="expertise-card" style="background-image: url('https://images.unsplash.com/photo-1547471080-7cc2caa01a7e?q=80&w=600');">
                            <div class="expertise-overlay"></div>
                            <div class="expertise-card-content text-center">
                                <div class="expertise-icon"><i class="bi bi-binoculars-fill"></i></div>
                                <h4 class="expertise-title">Wildlife Safari</h4>
                                <span class="expertise-explore-btn">Explore Packages</span>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Category 5: Shore Excursions -->
                <div class="col">
                    <a href="index.php?expertise_type=Shore+Excursions#packagesSection" class="expertise-card-link text-decoration-none">
                        <div class="expertise-card" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?q=80&w=600');">
                            <div class="expertise-overlay"></div>
                            <div class="expertise-card-content text-center">
                                <div class="expertise-icon"><i class="bi bi-tsunami"></i></div>
                                <h4 class="expertise-title">Shore Excursions</h4>
                                <span class="expertise-explore-btn">Explore Packages</span>
                            </div>
                        </div>
                    </a>
                </div>

            </div>
        </div>
    </section>

    <!-- Dynamic Tour Packages Section -->
    <section class="py-5 bg-light" id="packagesSection">
        <div class="container py-4">
            <div class="d-flex flex-wrap justify-content-between align-items-end mb-5">
                <div>
                    <span class="text-primary fw-bold text-uppercase" style="letter-spacing: 1.5px;">Browse Excursions</span>
                    <h2 class="display-6 fw-bold mt-2">Available Tour Packages</h2>
                </div>
                
                <!-- Reset Filters Option if filtered -->
                <?php if (!empty($search_query) || !empty($selected_expertise)): ?>
                    <a href="index.php#packagesSection" class="btn btn-outline-secondary btn-sm rounded-pill"><i class="bi bi-x-circle me-1"></i> Clear Filters & Show All</a>
                <?php endif; ?>
            </div>

            <!-- Dynamic package display -->
            <div class="row g-4">
                <?php if (count($packages) > 0): ?>
                    <?php foreach ($packages as $pkg): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="package-card h-100 card shadow-sm border-0">
                                <div class="package-img-wrapper position-relative">
                                    <img src="<?php echo htmlspecialchars($pkg['images']); ?>" class="package-img" alt="<?php echo htmlspecialchars($pkg['title']); ?>">
                                    <span class="package-badge"><?php echo htmlspecialchars($pkg['expertise_type']); ?></span>
                                    <span class="package-rating bg-dark text-warning"><i class="bi bi-star-fill me-1"></i> 5.0</span>
                                </div>
                                <div class="package-content card-body d-flex flex-column">
                                    <h4 class="package-title mt-2"><?php echo htmlspecialchars($pkg['title']); ?></h4>
                                    <div class="d-flex align-items-center text-muted mb-2 fs-6">
                                        <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                                        <?php echo htmlspecialchars($pkg['destination']); ?>
                                    </div>
                                    <p class="text-muted flex-grow-1"><?php echo htmlspecialchars(substr($pkg['description'], 0, 140)) . '...'; ?></p>
                                    <div class="border-top pt-3 mt-3 d-flex justify-content-between align-items-center">
                                        <div class="package-price">$<?php echo htmlspecialchars(number_format($pkg['price'], 2)); ?> <span class="fs-6 text-muted fw-normal">/ pax</span></div>
                                        
                                        <!-- Actions conditional to role -->
                                        <?php if (isset($_SESSION['user'])): ?>
                                            <a href="dashboard.php?book_package_id=<?php echo $pkg['id']; ?>" class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-bookmark-plus me-1"></i> Book Now</a>
                                        <?php else: ?>
                                            <a href="login.php" class="btn btn-outline-primary btn-sm rounded-pill px-3"><i class="bi bi-box-arrow-in-right me-1"></i> Login to Book</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-search-heart display-2 text-muted"></i>
                        <h3 class="mt-3 text-muted">No Packages Found Matching Filters</h3>
                        <p class="text-muted">Try adjusting your search criteria or choosing a different travel category.</p>
                        <a href="index.php#packagesSection" class="btn btn-primary rounded-pill mt-2">Show All Packages</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Inquiry Submission Form (Academic requirements: query submissions) -->
    <section class="py-5" id="inquirySection">
        <div class="container py-4">
            <div class="row align-items-stretch">
                <!-- Info Section -->
                <div class="col-lg-5 mb-4 mb-lg-0">
                    <div class="contact-info-box d-flex flex-column justify-content-between h-100">
                        <div>
                            <h3 class="fw-bold mb-3">GlobeTrek Negombo Office</h3>
                            <p class="text-white-70">Have a custom tour request or need specific travel packages arranged? Our local Negombo agency coordinators are available 24/7.</p>
                        </div>
                        <div class="my-4">
                            <div class="contact-item">
                                <div class="contact-icon-box"><i class="bi bi-geo-alt-fill text-white"></i></div>
                                <div>
                                    <div class="fw-bold">Address</div>
                                    <div class="text-white-70">45 Beach Road, Negombo, Sri Lanka</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon-box"><i class="bi bi-telephone-fill text-white"></i></div>
                                <div>
                                    <div class="fw-bold">Phone Number</div>
                                    <div class="text-white-70">+94 31 223 4567 / +94 77 123 4567</div>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon-box"><i class="bi bi-envelope-fill text-white"></i></div>
                                <div>
                                    <div class="fw-bold">Email Address</div>
                                    <div class="text-white-70">info@globetrekyours.com</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="#" class="footer-social-icon text-white"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="footer-social-icon text-white"><i class="bi bi-instagram"></i></a>
                            <a href="#" class="footer-social-icon text-white"><i class="bi bi-twitter-x"></i></a>
                        </div>
                    </div>
                </div>

                <!-- Submission Form Card -->
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
                        <span class="text-primary fw-bold text-uppercase" style="letter-spacing: 1.5px;">Get In Touch</span>
                        <h2 class="fw-bold mb-4 mt-2">Submit a Travel Inquiry</h2>
                        
                        <?php
                        // Process Inquiry Form
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
                            $name = trim($_POST['inquiry_name'] ?? '');
                            $email = trim($_POST['inquiry_email'] ?? '');
                            $subject = trim($_POST['inquiry_subject'] ?? '');
                            $message = trim($_POST['inquiry_message'] ?? '');

                            if (empty($name) || empty($email) || empty($subject) || empty($message)) {
                                echo '<div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i> All query fields are required.</div>';
                            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                echo '<div class="alert alert-danger rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i> Please enter a valid email address.</div>';
                            } else {
                                try {
                                    $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, subject, message, status) VALUES (?, ?, ?, ?, 'Pending')");
                                    $stmt->execute([$name, $email, $subject, $message]);
                                    echo '<div class="alert alert-success rounded-3"><i class="bi bi-check-circle-fill me-2"></i> Inquiry submitted successfully! Our travel coordinators will get back to you shortly.</div>';
                                } catch (PDOException $e) {
                                    echo '<div class="alert alert-danger rounded-3">Error submitting inquiry: ' . htmlspecialchars($e->getMessage()) . '</div>';
                                }
                            }
                        }
                        ?>

                        <form action="index.php#inquirySection" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-semibold">Your Name</label>
                                    <input type="text" name="inquiry_name" class="form-control form-control-custom" placeholder="John Doe" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-dark fw-semibold">Email Address</label>
                                    <input type="email" name="inquiry_email" class="form-control form-control-custom" placeholder="john@example.com" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-dark fw-semibold">Subject / Travel Package</label>
                                    <input type="text" name="inquiry_subject" class="form-control form-control-custom" placeholder="Inquiry about customized Lagoon Tour" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label text-dark fw-semibold">Detail Message / Travel Requirements</label>
                                    <textarea name="inquiry_message" rows="4" class="form-control form-control-custom" placeholder="Let us know your dates, group size, and specific requirements..." required></textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" name="submit_inquiry" class="btn btn-primary rounded-pill px-4 py-2 w-100 w-md-auto"><i class="bi bi-send-fill me-1"></i> Send Inquiry</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="#expertiseSection" class="footer-link">Our Expertise</a></li>
                        <li><a href="#packagesSection" class="footer-link">Browse Packages</a></li>
                        <li><a href="#inquirySection" class="footer-link">Submit Inquiry</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h5>Expertise Types</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php?expertise_type=Culture+%26+Heritage#packagesSection" class="footer-link">Culture & Heritage</a></li>
                        <li><a href="index.php?expertise_type=Nature+%26+Adventure#packagesSection" class="footer-link">Nature & Adventure</a></li>
                        <li><a href="index.php?expertise_type=Wellness+%26+Spa#packagesSection" class="footer-link">Wellness & Spa</a></li>
                        <li><a href="index.php?expertise_type=Wildlife+Safari#packagesSection" class="footer-link">Wildlife Safari</a></li>
                        <li><a href="index.php?expertise_type=Shore+Excursions#packagesSection" class="footer-link">Shore Excursions</a></li>
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
