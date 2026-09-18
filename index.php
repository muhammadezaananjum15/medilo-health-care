<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Main Homepage Entry Point
 */

require_once __DIR__ . '/config/db.php';

// Fetch dynamic data for homepage
$cities = fetchAll("SELECT * FROM cities ORDER BY name ASC");
$specialties = fetchAll("SELECT * FROM specialties ORDER BY name ASC");

// Fetch featured doctors
$featured_doctors = fetchAll("
    SELECT d.*, u.full_name, s.name AS specialty_name, c.name AS city_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    LIMIT 6
");

// Fetch counts for animated counter
$doctor_count = fetchOne("SELECT COUNT(*) AS total FROM doctors")['total'] ?? 0;
$patient_count = fetchOne("SELECT COUNT(*) AS total FROM patients")['total'] ?? 0;
$appointment_count = fetchOne("SELECT COUNT(*) AS total FROM appointments")['total'] ?? 0;
$city_count = fetchOne("SELECT COUNT(*) AS total FROM cities")['total'] ?? 0;

// Fetch latest health info & news
$health_info_list = fetchAll("SELECT * FROM health_info ORDER BY id DESC LIMIT 3");
$latest_news = fetchAll("SELECT * FROM news ORDER BY id DESC LIMIT 3");

$page_title = "Home";
include_once __DIR__ . '/includes/header.php';
?>

<!-- Start Hero Section with Quick Search -->
<section class="position-relative">
    <div class="cs_hero_slider_thumb">
        <div class="cs_hero_slider_thumb_item">
            <div class="cs_hero cs_style_1 cs_center cs_bg_filed" data-src="<?php echo APP_URL; ?>/assets/img/hero_slider_3.jpg">
                <div class="container">
                    <div class="cs_hero_text">
                        <div class="cs_hero_text_in">
                            <h1 class="cs_hero_title">Hospital &amp; Doctor <span>Care Service.</span></h1>
                            <p class="cs_hero_subtitle">Book appointments with top-rated medical specialists near you. Access real-time availability, secure online payment, and electronic medical records.</p>
                            <div class="cs_hero_info">
                                <h3>24/7 Medical Care Assistance</h3>
                                <p>Call Us at: +1 (800) 555-MEDILO</p>
                            </div>
                            <div class="cs_hero_btns">
                                <a class="cs_btn cs_style_1 cs_color_1" href="<?php echo APP_URL; ?>/frontend/doctors.php">
                                    <span>Find Doctors</span> <i class="fa-solid fa-angles-right"></i>
                                </a>
                                <a class="cs_btn cs_style_1 cs_color_2" href="<?php echo APP_URL; ?>/frontend/book_appointment.php">
                                    <span>Book Appointment</span> <i class="fa-solid fa-angles-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cs_hero_slider_thumb_item">
            <div class="cs_hero cs_style_1 cs_center cs_bg_filed" data-src="<?php echo APP_URL; ?>/assets/img/hero_slider_2.jpg">
                <div class="container">
                    <div class="cs_hero_text">
                        <div class="cs_hero_text_in">
                            <h1 class="cs_hero_title">Your Center for <br>Advanced <span>Health.</span></h1>
                            <p class="cs_hero_subtitle">Providing compassionate, expert medical solutions across Cardiology, Neurology, Pediatrics, Orthopedics, and more.</p>
                            <div class="cs_hero_info">
                                <h3>Direct Doctor Booking</h3>
                                <p>Instant Slot Confirmation</p>
                            </div>
                            <div class="cs_hero_btns">
                                <a class="cs_btn cs_style_1 cs_color_1" href="<?php echo APP_URL; ?>/frontend/doctors.php">
                                    <span>Search Specialists</span> <i class="fa-solid fa-angles-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="cs_hero_slider_thumb_item">
            <div class="cs_hero cs_style_1 cs_center cs_bg_filed" data-src="<?php echo APP_URL; ?>/assets/img/hero_slider_1.jpg">
                <div class="container">
                    <div class="cs_hero_text">
                        <div class="cs_hero_text_in">
                            <h1 class="cs_hero_title">Expert Care <br>When You <span>Need It.</span></h1>
                            <p class="cs_hero_subtitle">Connect with verified physicians, schedule visits in seconds, and manage your complete health journey &mdash; all in one trusted platform.</p>
                            <div class="cs_hero_info">
                                <h3>Trusted by 10,000+ Patients</h3>
                                <p>Join Medilo Healthcare Today</p>
                            </div>
                            <div class="cs_hero_btns">
                                <a class="cs_btn cs_style_1 cs_color_1" href="<?php echo APP_URL; ?>/auth/register.php">
                                    <span>Get Started</span> <i class="fa-solid fa-angles-right"></i>
                                </a>
                                <a class="cs_btn cs_style_1 cs_color_2" href="<?php echo APP_URL; ?>/frontend/services.php">
                                    <span>Our Services</span> <i class="fa-solid fa-angles-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Hero Navigation Arrows & Dots -->
    <div class="cs_hero_dots"></div>
    <button class="cs_hero_prev_arrow" id="heroPrev" aria-label="Previous slide"><i class="fa-solid fa-chevron-left"></i></button>
    <button class="cs_hero_next_arrow" id="heroNext" aria-label="Next slide"><i class="fa-solid fa-chevron-right"></i></button>
</section>
<!-- End Hero Section -->

<!-- Quick Doctor & City Search Filter Bar -->
<section class="py-4 bg-white shadow-sm position-relative z-index-2" style="margin-top: -30px;">
    <div class="container">
        <div class="card border-0 shadow-lg rounded-3 p-4 cs_blue_bg text-white">
            <h4 class="text-white mb-3"><i class="fa-solid fa-magnifying-glass-location me-2"></i> Find & Book a Doctor</h4>
            <form action="<?php echo APP_URL; ?>/frontend/doctors.php" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label text-white-50">Specialty</label>
                    <select name="specialty_id" class="form-select py-2">
                        <option value="">All Medical Specialties</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo $spec['id']; ?>"><?php echo htmlspecialchars($spec['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label text-white-50">City / Location</label>
                    <select name="city_id" class="form-select py-2">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>"><?php echo htmlspecialchars($city['name'] . ', ' . $city['state']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-2">
                        <span>Search Doctors Now</span> <i class="fa-solid fa-arrow-right"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<!-- About & Features Section -->
<section class="cs_about_section py-5">
    <div class="container py-4">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <div class="cs_about_img position-relative">
                    <img src="<?php echo APP_URL; ?>/assets/img/about_img_1.jpg" alt="About Medilo" class="img-fluid rounded-3 shadow">
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <span class="cs_section_subtitle text-primary fw-bold text-uppercase"><i class="fa-solid fa-heart-pulse me-2"></i> About Medilo System</span>
                    <h2 class="cs_section_title my-3">Dedicated to Delivering Exceptional Healthcare Management</h2>
                    <p class="text-muted">
                        Medilo is an all-in-one healthcare management solution designed to empower patients, doctors, and healthcare administrators. Search experienced doctors by specialty and city, verify live availability schedules, and pay securely for instant booking confirmations.
                    </p>
                    <div class="row mt-4">
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check fs-4 text-info me-3"></i>
                                <div>
                                    <h6 class="mb-0">Verified Doctors</h6>
                                    <small class="text-muted">Board-certified specialists</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check fs-4 text-info me-3"></i>
                                <div>
                                    <h6 class="mb-0">Instant Slot Booking</h6>
                                    <small class="text-muted">Real-time availability</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check fs-4 text-info me-3"></i>
                                <div>
                                    <h6 class="mb-0">Secure Gateway</h6>
                                    <small class="text-muted">Stripe & PayPal ready</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="d-flex align-items-center">
                                <i class="fa-solid fa-circle-check fs-4 text-info me-3"></i>
                                <div>
                                    <h6 class="mb-0">Patient History</h6>
                                    <small class="text-muted">E-Vaccination & Records</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Counter Statistics Section -->
<section class="cs_counter_area cs_blue_bg py-5 text-white">
    <div class="container">
        <div class="row text-center gy-4 align-items-center justify-content-center">

            <!-- Specialist Doctors -->
            <div class="col-md-3 col-6">
                <div class="cs_counter_item">
                    <div class="cs_counter_icon mb-2">
                        <i class="fa-solid fa-user-doctor" style="font-size:2rem; color: var(--accent-color);"></i>
                    </div>
                    <h2 class="text-white fw-bold mb-1"><?php echo $doctor_count; ?>+</h2>
                    <p class="text-white-50 mb-0">Specialist Doctors</p>
                </div>
            </div>

            <!-- Divider (desktop only) -->
            <div class="col-md-auto d-none d-md-block">
                <div style="width:1px; height:80px; background:rgba(255,255,255,0.2);"></div>
            </div>

            <!-- Registered Patients -->
            <div class="col-md-3 col-6">
                <div class="cs_counter_item">
                    <div class="cs_counter_icon mb-2">
                        <i class="fa-solid fa-users" style="font-size:2rem; color: var(--accent-color);"></i>
                    </div>
                    <h2 class="text-white fw-bold mb-1"><?php echo $patient_count; ?>+</h2>
                    <p class="text-white-50 mb-0">Registered Patients</p>
                </div>
            </div>

            <!-- Divider (desktop only) -->
            <div class="col-md-auto d-none d-md-block">
                <div style="width:1px; height:80px; background:rgba(255,255,255,0.2);"></div>
            </div>

            <!-- Appointments Completed -->
            <div class="col-md-3 col-6">
                <div class="cs_counter_item">
                    <div class="cs_counter_icon mb-2">
                        <i class="fa-solid fa-calendar-check" style="font-size:2rem; color: var(--accent-color);"></i>
                    </div>
                    <h2 class="text-white fw-bold mb-1"><?php echo $appointment_count; ?>+</h2>
                    <p class="text-white-50 mb-0">Appointments Completed</p>
                </div>
            </div>

            <!-- Divider (desktop only) -->
            <div class="col-md-auto d-none d-md-block">
                <div style="width:1px; height:80px; background:rgba(255,255,255,0.2);"></div>
            </div>

            <!-- Cities Covered -->
            <div class="col-md-3 col-6">
                <div class="cs_counter_item">
                    <div class="cs_counter_icon mb-2">
                        <i class="fa-solid fa-location-dot" style="font-size:2rem; color: var(--accent-color);"></i>
                    </div>
                    <h2 class="text-white fw-bold mb-1"><?php echo $city_count; ?>+</h2>
                    <p class="text-white-50 mb-0">Cities Covered</p>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Featured Doctors Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase"><i class="fa-solid fa-user-doctor me-2"></i> Our Specialists</span>
            <h2 class="cs_section_title">Meet Experienced Medical Practitioners</h2>
        </div>

        <div class="row gy-4">
            <?php foreach ($featured_doctors as $doc): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                        <img src="<?php echo APP_URL . '/' . htmlspecialchars($doc['avatar']); ?>" class="card-img-top object-fit-cover" style="height: 240px;" alt="<?php echo htmlspecialchars($doc['full_name']); ?>">
                        <div class="card-body p-4">
                            <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($doc['specialty_name']); ?></span>
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($doc['full_name']); ?></h5>
                            <p class="text-muted small mb-2"><i class="fa-solid fa-location-dot me-1 text-primary"></i> <?php echo htmlspecialchars($doc['city_name']); ?></p>
                            <p class="text-muted small mb-3"><?php echo htmlspecialchars(substr($doc['bio'], 0, 90)); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <span class="fw-bold text-success fs-5">$<?php echo number_format($doc['consultation_fee'], 2); ?> <small class="text-muted fs-7">/ visit</small></span>
                                <a href="<?php echo APP_URL; ?>/frontend/doctor_details.php?id=<?php echo $doc['id']; ?>" class="cs_btn cs_style_1 cs_color_1 py-2 px-3">
                                    <span>Book Slot</span> <i class="fa-solid fa-calendar-plus"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo APP_URL; ?>/frontend/doctors.php" class="cs_btn cs_style_1 cs_color_2">
                <span>View All Doctors</span> <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- Health Info Directory Section (Diseases, Preventions, Cures) -->
<section class="py-5 bg-white">
    <div class="container py-4">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase"><i class="fa-solid fa-book-medical me-2"></i> Medical Knowledge Base</span>
            <h2 class="cs_section_title">Diseases, Preventions & Modern Cures</h2>
        </div>

        <div class="row gy-4">
            <?php foreach ($health_info_list as $info): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 rounded-3">
                        <img src="<?php echo APP_URL . '/' . htmlspecialchars($info['thumbnail']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($info['title']); ?>">
                        <div class="card-body p-4">
                            <span class="badge bg-primary mb-2"><?php echo htmlspecialchars($info['category']); ?></span>
                            <h5 class="card-title"><?php echo htmlspecialchars($info['title']); ?></h5>
                            <p class="text-muted small"><?php echo htmlspecialchars(substr(strip_tags($info['content']), 0, 110)); ?>...</p>
                            <a href="<?php echo APP_URL; ?>/frontend/health_info.php?id=<?php echo $info['id']; ?>" class="text-primary fw-bold text-decoration-none small">Read Medical Guide <i class="fa-solid fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
