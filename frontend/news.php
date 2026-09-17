<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Medical News and Blog Articles Page
 */

require_once __DIR__ . '/../config/db.php';

$news_id = intval($_GET['id'] ?? 0);

if ($news_id > 0) {
    $article = fetchOne("SELECT * FROM news WHERE id = ?", [$news_id]);
}

$news_list = fetchAll("SELECT * FROM news ORDER BY published_at DESC");

$page_title = "Medical News & Articles";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 cs_blue_bg text-white">
    <div class="container text-center py-4">
        <h1 class="text-white mb-2">Medical News & Healthcare Articles</h1>
        <p class="text-white-50 mb-0">Stay updated with the latest clinical breakthroughs, health advice, and medical research</p>
    </div>
</section>

<section class="py-5 bg-light">
    <div class="container py-3">
        <?php if (isset($article) && $article): ?>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-lg rounded-3 p-4 mb-4">
                        <span class="text-muted small mb-2"><i class="fa-solid fa-user me-1"></i> By <?php echo htmlspecialchars($article['author']); ?> | <i class="fa-solid fa-calendar me-1"></i> <?php echo date('M d, Y', strtotime($article['published_at'])); ?></span>
                        <h2 class="mb-3 text-primary"><?php echo htmlspecialchars($article['title']); ?></h2>
                        <img src="<?php echo APP_URL . '/' . htmlspecialchars($article['image']); ?>" class="img-fluid rounded-3 mb-4 object-fit-cover" style="max-height: 380px; width: 100%;" alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <div class="text-muted leading-relaxed fs-6">
                            <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                        </div>
                        <div class="mt-4 pt-3 border-top">
                            <a href="<?php echo APP_URL; ?>/frontend/news.php" class="btn btn-outline-primary"><i class="fa-solid fa-arrow-left me-1"></i> Back to News List</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="row gy-4">
                <?php foreach ($news_list as $item): ?>
                    <div class="col-lg-4 col-md-6">
                        <div class="card border-0 shadow-sm h-100 rounded-3 overflow-hidden">
                            <img src="<?php echo APP_URL . '/' . htmlspecialchars($item['image']); ?>" class="card-img-top object-fit-cover" style="height: 220px;" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <div class="card-body p-4 d-flex flex-column justify-content-between">
                                <div>
                                    <small class="text-muted d-block mb-2"><i class="fa-solid fa-user me-1"></i> <?php echo htmlspecialchars($item['author']); ?> | <?php echo date('M d, Y', strtotime($item['published_at'])); ?></small>
                                    <h5 class="card-title mb-2"><?php echo htmlspecialchars($item['title']); ?></h5>
                                    <p class="text-muted small mb-3"><?php echo htmlspecialchars(substr($item['excerpt'] ?? strip_tags($item['content']), 0, 110)); ?>...</p>
                                </div>
                                <a href="<?php echo APP_URL; ?>/frontend/news.php?id=<?php echo $item['id']; ?>" class="fw-bold text-primary text-decoration-none">Read Full Article <i class="fa-solid fa-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
