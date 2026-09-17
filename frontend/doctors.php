<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Doctor Search and Directory Catalog
 */

require_once __DIR__ . '/../config/db.php';

// Fetch filter parameters
$search = sanitize_input($_GET['search'] ?? '');
$specialty_id = !empty($_GET['specialty_id']) ? intval($_GET['specialty_id']) : null;
$city_id = !empty($_GET['city_id']) ? intval($_GET['city_id']) : null;

// Fetch Dropdown options
$cities = fetchAll("SELECT * FROM cities ORDER BY name ASC");
$specialties = fetchAll("SELECT * FROM specialties ORDER BY name ASC");

// Build Dynamic Prepared SQL Query
$sql = "
    SELECT d.*, u.full_name, u.email, u.phone, s.name AS specialty_name, c.name AS city_name 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (u.full_name LIKE ? OR d.qualification LIKE ? OR s.name LIKE ?)";
    $search_param = "%" . $search . "%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if ($specialty_id) {
    $sql .= " AND d.specialty_id = ?";
    $params[] = $specialty_id;
}

if ($city_id) {
    $sql .= " AND d.city_id = ?";
    $params[] = $city_id;
}

$sql .= " ORDER BY d.id DESC";

$doctors = fetchAll($sql, $params);

$page_title = "Find Doctors";
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Banner Section -->
<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Find Specialist Doctors</h1>
        <p class="text-white-50 mb-0">Search through our network of experienced, board-certified doctors by specialty and city</p>
    </div>
</section>

<!-- Filter & Doctor Catalog -->
<section class="py-5 bg-light">
    <div class="container">
        
        <!-- Filter Card -->
        <div class="card border-0 shadow-sm p-4 mb-5 rounded-3 wow fadeInUp" data-wow-duration="0.8s">
            <form action="<?php echo APP_URL; ?>/frontend/doctors.php" method="GET" class="row g-3">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fw-bold">Search Doctor Name / Specialty</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold">Medical Specialty</label>
                    <select name="specialty_id" class="form-select">
                        <option value="">All Specialties</option>
                        <?php foreach ($specialties as $spec): ?>
                            <option value="<?php echo $spec['id']; ?>" <?php echo ($specialty_id == $spec['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($spec['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fw-bold">City Location</label>
                    <select name="city_id" class="form-select">
                        <option value="">All Cities</option>
                        <?php foreach ($cities as $city): ?>
                            <option value="<?php echo $city['id']; ?>" <?php echo ($city_id == $city['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['name'] . ', ' . $city['state']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 d-flex align-items-end">
                    <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-2">
                        <span>Filter</span> <i class="fa-solid fa-filter ms-1"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Doctor Grid -->
        <?php if (count($doctors) > 0): ?>
            <div class="row gy-4">
                <?php foreach ($doctors as $index => $doc): ?>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-duration="0.8s" data-wow-delay="<?php echo min(0.6, 0.1 * ($index % 6)); ?>s">
                        <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                            <div class="cs_doctor_img_holder position-relative overflow-hidden text-center" style="background-color: #f8fafd;">
                                <img src="<?php echo APP_URL . '/' . htmlspecialchars($doc['avatar']); ?>" class="card-img-top cs_doctor_card_img" style="height: 250px; object-fit: contain; padding: 12px;" alt="<?php echo htmlspecialchars($doc['full_name']); ?>">
                            </div>
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-info text-dark"><?php echo htmlspecialchars($doc['specialty_name']); ?></span>
                                        <span class="text-muted small"><i class="fa-solid fa-clock me-1"></i> <?php echo $doc['experience_years']; ?> Yrs Exp</span>
                                    </div>
                                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($doc['full_name']); ?></h5>
                                    <p class="text-muted small mb-2"><i class="fa-solid fa-graduation-cap text-primary me-1"></i> <?php echo htmlspecialchars($doc['qualification']); ?></p>
                                    <p class="text-muted small mb-3"><i class="fa-solid fa-location-dot text-primary me-1"></i> <?php echo htmlspecialchars($doc['city_name']); ?></p>
                                    <p class="card-text text-muted fs-7 mb-3"><?php echo htmlspecialchars(substr($doc['bio'], 0, 100)); ?>...</p>
                                </div>
                                <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted d-block">Fee per Visit</small>
                                        <span class="fw-bold text-success fs-5">$<?php echo number_format($doc['consultation_fee'], 2); ?></span>
                                    </div>
                                    <a href="<?php echo APP_URL; ?>/frontend/doctor_details.php?id=<?php echo $doc['id']; ?>" class="cs_btn cs_style_1 cs_color_1 py-2 px-3">
                                        <span>View & Book</span> <i class="fa-solid fa-calendar-plus"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fa-solid fa-user-doctor fs-1 text-muted mb-3"></i>
                <h4>No doctors found matching your criteria</h4>
                <p class="text-muted">Try resetting your filters or searching for another city/specialty.</p>
                <a href="<?php echo APP_URL; ?>/frontend/doctors.php" class="cs_btn cs_style_1 cs_color_2 py-2 px-4 mt-2">Reset Filters</a>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
