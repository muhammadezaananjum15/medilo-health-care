<?php
/**
 * Medilo Medical Appointment and Doctor Management System
 * Admin Content Management System (Diseases, Preventions, Cures & Medical News)
 */

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Strict Admin Access Control
require_role('admin');

$action = sanitize_input($_GET['action'] ?? '');
$id = intval($_GET['id'] ?? 0);

// Delete Health Info Item
if ($action === 'delete_health' && $id > 0) {
    executeQuery("DELETE FROM health_info WHERE id = ?", [$id]);
    set_flash_message('success', 'Health info item deleted successfully.');
    header('Location: ' . APP_URL . '/admin/content.php');
    exit;
}

// Delete News Item
if ($action === 'delete_news' && $id > 0) {
    executeQuery("DELETE FROM news WHERE id = ?", [$id]);
    set_flash_message('success', 'Medical news article deleted successfully.');
    header('Location: ' . APP_URL . '/admin/content.php?tab=news');
    exit;
}

// Save Health Info POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_health'])) {
    $title = sanitize_input($_POST['title'] ?? '');
    $category = sanitize_input($_POST['category'] ?? 'Disease');
    $content = sanitize_input($_POST['content'] ?? '');
    $edit_id = intval($_POST['health_id'] ?? 0);

    if (empty($title) || empty($content)) {
        set_flash_message('danger', 'Title and content are required.');
    } else {
        if ($edit_id > 0) {
            executeQuery("UPDATE health_info SET title = ?, category = ?, content = ? WHERE id = ?", [$title, $category, $content, $edit_id]);
            set_flash_message('success', 'Health article updated.');
        } else {
            executeQuery("INSERT INTO health_info (title, category, content, thumbnail) VALUES (?, ?, ?, 'assets/img/post_1.jpeg')", [$title, $category, $content]);
            set_flash_message('success', 'New health guide published.');
        }
        header('Location: ' . APP_URL . '/admin/content.php');
        exit;
    }
}

// Save News Article POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_news'])) {
    $title = sanitize_input($_POST['title'] ?? '');
    $excerpt = sanitize_input($_POST['excerpt'] ?? '');
    $content = sanitize_input($_POST['content'] ?? '');
    $author = sanitize_input($_POST['author'] ?? 'Medilo Admin');
    $edit_id = intval($_POST['news_id'] ?? 0);

    if (empty($title) || empty($content)) {
        set_flash_message('danger', 'News title and content are required.');
    } else {
        if ($edit_id > 0) {
            executeQuery("UPDATE news SET title = ?, excerpt = ?, content = ?, author = ? WHERE id = ?", [$title, $excerpt, $content, $author, $edit_id]);
            set_flash_message('success', 'News article updated.');
        } else {
            executeQuery("INSERT INTO news (title, excerpt, content, author, image) VALUES (?, ?, ?, ?, 'assets/img/post_2.jpeg')", [$title, $excerpt, $content, $author]);
            set_flash_message('success', 'New medical article published.');
        }
        header('Location: ' . APP_URL . '/admin/content.php?tab=news');
        exit;
    }
}

$tab = sanitize_input($_GET['tab'] ?? 'health');
$health_info_list = fetchAll("SELECT * FROM health_info ORDER BY id DESC");
$news_list = fetchAll("SELECT * FROM news ORDER BY id DESC");

$page_title = "Manage Website Content";
include_once __DIR__ . '/../includes/header.php';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row gy-4">
            
            <div class="col-lg-3">
                <?php include_once __DIR__ . '/../includes/sidebar.php'; ?>
            </div>

            <div class="col-lg-9">
                <div class="card border-0 shadow-sm rounded-3 p-4">
                    
                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-4 border-bottom">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($tab === 'health') ? 'active fw-bold text-primary' : ''; ?>" href="<?php echo APP_URL; ?>/admin/content.php?tab=health">
                                <i class="fa-solid fa-file-medical me-1"></i> Diseases, Preventions & Cures
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($tab === 'news') ? 'active fw-bold text-primary' : ''; ?>" href="<?php echo APP_URL; ?>/admin/content.php?tab=news">
                                <i class="fa-solid fa-newspaper me-1"></i> Medical News & Articles
                            </a>
                        </li>
                    </ul>

                    <?php if ($tab === 'health'): ?>
                        <!-- Add Health Info Form -->
                        <form action="<?php echo APP_URL; ?>/admin/content.php" method="POST" class="card bg-light p-4 border mb-4">
                            <input type="hidden" name="save_health" value="1">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-plus-circle me-1"></i> Add / Publish Medical Health Guide</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Guide Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required placeholder="e.g. Hypertension Prevention">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Category <span class="text-danger">*</span></label>
                                    <select class="form-select" name="category" required>
                                        <option value="Disease">Disease</option>
                                        <option value="Prevention">Prevention</option>
                                        <option value="Cure">Cure</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Content Body <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="content" rows="4" required placeholder="Detailed medical explanation, symptoms, or prevention steps"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-4 align-self-end">Publish Guide <i class="fa-solid fa-paper-plane ms-1"></i></button>
                        </form>

                        <!-- Health Info Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Category</th>
                                        <th>Title</th>
                                        <th>Content Preview</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($health_info_list as $h): ?>
                                        <tr>
                                            <td><span class="badge bg-primary"><?php echo htmlspecialchars($h['category']); ?></span></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($h['title']); ?></td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars(substr(strip_tags($h['content']), 0, 70)); ?>...</small></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/admin/content.php?action=delete_health&id=<?php echo $h['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this article?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>
                        <!-- Add News Article Form -->
                        <form action="<?php echo APP_URL; ?>/admin/content.php?tab=news" method="POST" class="card bg-light p-4 border mb-4">
                            <input type="hidden" name="save_news" value="1">
                            <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-newspaper me-1"></i> Add / Publish Medical News Article</h6>
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Article Title <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="title" required placeholder="e.g. Breakthrough AI Cardiology Diagnosis">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Author Name</label>
                                    <input type="text" class="form-control" name="author" value="Medilo Admin">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Short Excerpt</label>
                                <input type="text" class="form-control" name="excerpt" placeholder="Brief 1-sentence summary">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">Article Content <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="content" rows="4" required placeholder="Full news content"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm px-4 align-self-end">Publish News Article <i class="fa-solid fa-paper-plane ms-1"></i></button>
                        </form>

                        <!-- News Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Title & Author</th>
                                        <th>Excerpt</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($news_list as $n): ?>
                                        <tr>
                                            <td><small class="text-muted"><?php echo date('M d, Y', strtotime($n['published_at'])); ?></small></td>
                                            <td>
                                                <strong class="text-primary"><?php echo htmlspecialchars($n['title']); ?></strong><br>
                                                <small class="text-muted">By <?php echo htmlspecialchars($n['author']); ?></small>
                                            </td>
                                            <td><small class="text-muted"><?php echo htmlspecialchars(substr($n['excerpt'] ?? strip_tags($n['content']), 0, 70)); ?>...</small></td>
                                            <td>
                                                <a href="<?php echo APP_URL; ?>/admin/content.php?tab=news&action=delete_news&id=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this news article?');"><i class="fa-solid fa-trash"></i> Delete</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</section>

<?php include_once __DIR__ . '/../includes/footer.php'; ?>
