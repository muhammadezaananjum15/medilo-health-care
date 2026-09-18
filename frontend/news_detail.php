<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Dedicated Medical News & Health Article Detail View
 */

require_once __DIR__ . '/../config/db.php';

$news_id = intval($_GET['id'] ?? 0);

if ($news_id <= 0) {
    header('Location: ' . APP_URL . '/frontend/news.php');
    exit;
}

$article = fetchOne("SELECT * FROM news WHERE id = ?", [$news_id]);

if (!$article) {
    set_flash_message('danger', 'The requested article could not be found.');
    header('Location: ' . APP_URL . '/frontend/news.php');
    exit;
}

// Fetch related/recent articles
$related_articles = fetchAll("SELECT * FROM news WHERE id != ? ORDER BY published_at DESC LIMIT 3", [$news_id]);

$page_title = $article['title'];
include_once __DIR__ . '/../includes/header.php';
?>

<!-- Header Banner -->
<section class="py-4 cs_blue_bg text-white">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/index.php" class="text-white-50">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo APP_URL; ?>/frontend/news.php" class="text-white-50">Medical News</a></li>
                <li class="breadcrumb-item active text-white text-truncate" style="max-width: 400px;" aria-current="page"><?php echo htmlspecialchars($article['title']); ?></li>
            </ol>
        </nav>
    </div>
</section>

<!-- Main Article Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <!-- Main Content Area -->
            <div class="col-lg-8">
                <article class="card border-0 shadow-sm rounded-3 overflow-hidden p-4 p-md-5 bg-white">
                    
                    <div class="d-flex align-items-center gap-3 text-muted small mb-3 flex-wrap">
                        <span><i class="fa-solid fa-user-doctor text-primary me-1"></i> <?php echo htmlspecialchars($article['author']); ?></span>
                        <span>•</span>
                        <span><i class="fa-solid fa-calendar text-primary me-1"></i> <?php echo date('F d, Y', strtotime($article['published_at'])); ?></span>
                        <span>•</span>
                        <span class="badge bg-info text-dark">Healthcare Insights</span>
                    </div>

                    <h1 class="text-primary fw-bold mb-4 fs-2"><?php echo htmlspecialchars($article['title']); ?></h1>

                    <div class="mb-4 overflow-hidden rounded-3">
                        <img src="<?php echo APP_URL . '/' . htmlspecialchars($article['image']); ?>" class="img-fluid w-100 object-fit-cover" style="max-height: 420px;" alt="<?php echo htmlspecialchars($article['title']); ?>">
                    </div>

                    <?php if (!empty($article['excerpt'])): ?>
                        <div class="p-3 bg-light rounded-3 border-start border-4 border-primary mb-4">
                            <p class="fst-italic text-dark mb-0 fw-semibold fs-6"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="article-body text-dark leading-relaxed fs-6 mb-4" style="line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                    </div>

                    <!-- Article Footer Actions -->
                    <div class="d-flex justify-content-between align-items-center pt-4 border-top mt-4 flex-wrap gap-2">
                        <a href="<?php echo APP_URL; ?>/frontend/news.php" class="btn btn-outline-primary">
                            <i class="fa-solid fa-arrow-left me-1"></i> Back to All Articles
                        </a>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small text-muted">Share:</span>
                            <a href="https://twitter.com/share?url=<?php echo urlencode(APP_URL . '/frontend/news_detail.php?id=' . $article['id']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-twitter"></i></a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(APP_URL . '/frontend/news_detail.php?id=' . $article['id']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-facebook"></i></a>
                            <a href="https://www.linkedin.com/shareArticle?url=<?php echo urlencode(APP_URL . '/frontend/news_detail.php?id=' . $article['id']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-circle" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center;"><i class="fa-brands fa-linkedin"></i></a>
                        </div>
                    </div>

                </article>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                
                <!-- Quick Consultation CTA -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4 cs_blue_bg text-white">
                    <h5 class="text-white fw-bold mb-2"><i class="fa-solid fa-stethoscope me-2"></i> Need Medical Guidance?</h5>
                    <p class="text-white-50 small mb-3">Schedule a consultation with our verified healthcare specialists today.</p>
                    <a href="<?php echo APP_URL; ?>/frontend/book_appointment.php" class="btn btn-light fw-bold w-100 py-2">
                        <span>Book an Appointment</span> <i class="fa-solid fa-arrow-right ms-1"></i>
                    </a>
                </div>

                <!-- Related News Articles -->
                <div class="card border-0 shadow-sm rounded-3 p-4 bg-white">
                    <h5 class="text-primary fw-bold border-bottom pb-2 mb-3"><i class="fa-solid fa-newspaper me-2"></i> Related News</h5>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($related_articles as $rel): ?>
                            <div class="d-flex gap-3 align-items-center">
                                <img src="<?php echo APP_URL . '/' . htmlspecialchars($rel['image']); ?>" class="rounded-2 object-fit-cover" width="75" height="70" alt="<?php echo htmlspecialchars($rel['title']); ?>">
                                <div>
                                    <small class="text-muted d-block" style="font-size: 11px;"><i class="fa-solid fa-calendar me-1"></i> <?php echo date('M d, Y', strtotime($rel['published_at'])); ?></small>
                                    <a href="<?php echo APP_URL; ?>/frontend/news_detail.php?id=<?php echo $rel['id']; ?>" class="fw-bold text-dark text-decoration-none small line-clamp-2">
                                        <?php echo htmlspecialchars($rel['title']); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
