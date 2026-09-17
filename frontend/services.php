<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Medical Services Catalogue Page
 */

require_once __DIR__ . '/../config/db.php';

$specialties = fetchAll("SELECT * FROM specialties ORDER BY id ASC");

$page_title = "Our Medical Services";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Our Healthcare & Medical Services</h1>
        <p class="text-white-50 mb-0">Providing comprehensive diagnostic, surgical, and preventative healthcare solutions</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-3">
        <div class="row gy-4">
            <?php foreach ($specialties as $spec): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm h-100 rounded-3 p-4">
                        <div class="mb-3 text-info fs-1">
                            <i class="<?php echo htmlspecialchars($spec['icon']); ?>"></i>
                        </div>
                        <h4 class="card-title text-primary mb-2"><?php echo htmlspecialchars($spec['name']); ?></h4>
                        <p class="text-muted leading-relaxed mb-4"><?php echo htmlspecialchars($spec['description']); ?></p>
                        <div class="mt-auto">
                            <a href="<?php echo APP_URL; ?>/frontend/doctors.php?specialty_id=<?php echo $spec['id']; ?>" class="cs_btn cs_style_1 cs_color_1 py-2 px-3">
                                <span>Find Specialists</span> <i class="fa-solid fa-angle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
