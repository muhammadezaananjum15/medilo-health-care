<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Diseases, Preventions & Cures Medical Knowledge Directory
 */

require_once __DIR__ . '/../config/db.php';

$category_filter = sanitize_input($_GET['category'] ?? '');
$search = sanitize_input($_GET['search'] ?? '');
$single_id = intval($_GET['id'] ?? 0);

if ($single_id > 0) {
    $item = fetchOne("SELECT * FROM health_info WHERE id = ?", [$single_id]);
}

$sql = "SELECT * FROM health_info WHERE 1=1";
$params = [];

if (!empty($category_filter)) {
    $sql .= " AND category = ?";
    $params[] = $category_filter;
}

if (!empty($search)) {
    $sql .= " AND (title LIKE ? OR content LIKE ?)";
    $params[] = "%" . $search . "%";
    $params[] = "%" . $search . "%";
}

$sql .= " ORDER BY id DESC";
$health_info_list = fetchAll($sql, $params);

$page_title = "Diseases, Preventions & Cures";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Diseases, Preventions & Medical Cures</h1>
        <p class="text-white-50 mb-0">Evidence-based medical guides managed by certified healthcare practitioners</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-3">

        <?php if (isset($item) && $item): ?>
            <!-- Single Detail View -->
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg rounded-3 p-4 mb-4">
                        <span class="badge bg-primary w-auto align-self-start mb-3"><?php echo htmlspecialchars($item['category']); ?></span>
                        <h2 class="mb-3 text-primary"><?php echo htmlspecialchars($item['title']); ?></h2>
                        <img src="<?php echo APP_URL . '/' . htmlspecialchars($item['thumbnail']); ?>" class="img-fluid rounded-3 mb-4 object-fit-cover" style="max-height: 350px; width: 100%;" alt="<?php echo htmlspecialchars($item['title']); ?>">
                        <div class="text-muted leading-relaxed fs-6">
                            <?php echo nl2br(htmlspecialchars($item['content'])); ?>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <a href="<?php echo APP_URL; ?>/frontend/health_info.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i> Back to Directory</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Directory List View -->
            <div class="card border-0 shadow-sm p-4 mb-5 rounded-3">
                <form action="<?php echo APP_URL; ?>/frontend/health_info.php" method="GET" class="row g-3">
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Search disease, prevention or cure..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">All Categories (Disease, Prevention, Cure)</option>
                            <option value="Disease" <?php echo ($category_filter === 'Disease') ? 'selected' : ''; ?>>Disease</option>
                            <option value="Prevention" <?php echo ($category_filter === 'Prevention') ? 'selected' : ''; ?>>Prevention</option>
                            <option value="Cure" <?php echo ($category_filter === 'Cure') ? 'selected' : ''; ?>>Cure</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="cs_btn cs_style_1 cs_color_1 w-100 py-2">Filter</button>
                    </div>
                </form>
            </div>

            <div class="row gy-4">
                <?php foreach ($health_info_list as $info): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                            <img src="<?php echo APP_URL . '/' . htmlspecialchars($info['thumbnail']); ?>" class="card-img-top object-fit-cover" style="height: 200px;" alt="<?php echo htmlspecialchars($info['title']); ?>">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <span class="badge bg-info text-dark mb-2"><?php echo htmlspecialchars($info['category']); ?></span>
                                    <h5 class="card-title"><?php echo htmlspecialchars($info['title']); ?></h5>
                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars(substr(strip_tags($info['content']), 0, 120)); ?>...</p>
                                </div>
                                <a href="<?php echo APP_URL; ?>/frontend/health_info.php?id=<?php echo $info['id']; ?>" class="fw-bold text-primary text-decoration-none">
                                    Read Full Article <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
