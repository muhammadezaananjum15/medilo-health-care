<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Doctor Detailed Profile & Availability Schedule
 */

require_once __DIR__ . '/../config/db.php';

$doctor_id = intval($_GET['id'] ?? 0);

$doctor = fetchOne("
    SELECT d.*, u.full_name, u.email, u.phone, u.address, s.name AS specialty_name, s.description AS specialty_desc, c.name AS city_name, c.state 
    FROM doctors d 
    JOIN users u ON d.user_id = u.id 
    JOIN specialties s ON d.specialty_id = s.id 
    JOIN cities c ON d.city_id = c.id 
    WHERE d.id = ?
", [$doctor_id]);

if (!$doctor) {
    set_flash_message('danger', 'Doctor profile not found.');
    header('Location: ' . APP_URL . '/frontend/doctors.php');
    exit;
}

// Fetch Doctor Schedules
$schedules = fetchAll("SELECT * FROM doctor_schedules WHERE doctor_id = ? AND is_available = 1 ORDER BY FIELD(day_of_week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')", [$doctor_id]);

$page_title = $doctor['full_name'] . " - Doctor Details";
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Banner -->
<section class="py-4 cs_blue_bg text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/frontend/doctors.php" class="text-white-50">Doctors</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?php echo htmlspecialchars($doctor['full_name']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Doctor Main Details -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Left Info Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <img src="<?php echo APP_URL . '/' . htmlspecialchars($doctor['avatar']); ?>" class="card-img-top w-100 object-fit-cover" style="height: 300px;" alt="<?php echo htmlspecialchars($doctor['full_name']); ?>">
                    <div class="card-body p-4">
                        <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($doctor['specialty_name']); ?></span>
                        <h4 class="card-title mb-1"><?php echo htmlspecialchars($doctor['full_name']); ?></h4>
                        <p class="text-primary fw-bold mb-3"><?php echo htmlspecialchars($doctor['qualification']); ?></p>

                        <div class="list-group list-group-flush fs-7 mb-4">
                            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                <span class="text-muted"><i class="fa-solid fa-briefcase me-2 text-primary"></i> Experience:</span>
                                <span class="fw-bold"><?php echo $doctor['experience_years']; ?> Years</span>
                            </div>
                            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                <span class="text-muted"><i class="fa-solid fa-location-dot me-2 text-primary"></i> Practice City:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($doctor['city_name'] . ', ' . $doctor['state']); ?></span>
                            </div>
                            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                <span class="text-muted"><i class="fa-solid fa-dollar-sign me-2 text-primary"></i> Consultation Fee:</span>
                                <span class="fw-bold text-success fs-6">$<?php echo number_format($doctor['consultation_fee'], 2); ?></span>
                            </div>
                            <div class="list-group-item bg-transparent px-0 d-flex justify-content-between">
                                <span class="text-muted"><i class="fa-solid fa-phone me-2 text-primary"></i> Contact:</span>
                                <span class="fw-bold"><?php echo htmlspecialchars($doctor['phone']); ?></span>
                            </div>
                        </div>

                        <a href="<?php echo APP_URL; ?>/frontend/book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>" class="cs_btn cs_style_1 cs_color_1 w-100 py-3 text-center">
                            <span>Book Appointment Now</span> <i class="fa-solid fa-calendar-check ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Details & Weekly Availability Schedule -->
            <div class="col-lg-8">
                
                <!-- Biography -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h4 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-user-doctor me-2"></i> About Dr. <?php echo htmlspecialchars($doctor['full_name']); ?></h4>
                    <p class="text-muted leading-relaxed mb-0">
                        <?php echo nl2br(htmlspecialchars($doctor['bio'])); ?>
                    </p>
                </div>

                <!-- Specialty Focus -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h4 class="border-bottom pb-2 mb-3 text-primary"><i class="fa-solid fa-stethoscope me-2"></i> Specialty Expertise</h4>
                    <p class="text-muted mb-0">
                        <?php echo htmlspecialchars($doctor['specialty_desc']); ?>
                    </p>
                </div>

                <!-- Weekly Availability Schedule -->
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    <h4 class="border-bottom pb-2 mb-3 text-primary d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-calendar-days me-2"></i> Weekly Availability Schedule</span>
                        <span class="badge bg-success fs-7">Real-Time Slots</span>
                    </h4>

                    <?php if (count($schedules) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Day of Week</th>
                                        <th>Available Hours</th>
                                        <th>Slot Duration</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($schedules as $sched): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($sched['day_of_week']); ?></td>
                                            <td>
                                                <i class="fa-solid fa-clock text-primary me-1"></i>
                                                <?php echo date("g:i A", strtotime($sched['start_time'])); ?> - <?php echo date("g:i A", strtotime($sched['end_time'])); ?>
                                            </td>
                                            <td><?php echo $sched['slot_duration']; ?> Mins</td>
                                            <td><span class="badge bg-success">Available</span></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/frontend/book_appointment.php?doctor_id=<?php echo $doctor['id']; ?>&day=<?php echo $sched['day_of_week']; ?>" class="btn btn-sm btn-outline-primary">
                                                    Select <i class="fa-solid fa-chevron-right ms-1"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">No active availability schedule listed for this doctor currently.</p>
                    <?php endif; ?>
                </div>

            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
