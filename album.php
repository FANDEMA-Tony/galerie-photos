<?php
require 'config/database.php';
require 'includes/header.php';

/* Vérification ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Album introuvable</div>";
    require 'includes/footer.php';
    exit;
}

$album_id = (int)$_GET['id'];

/* Pagination */
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

/* Récupération infos album */
$stmtAlbum = $pdo->prepare("SELECT * FROM albums WHERE id = :album_id");
$stmtAlbum->bindValue(':album_id', $album_id, PDO::PARAM_INT);
$stmtAlbum->execute();
$album = $stmtAlbum->fetch();

if (!$album) {
    echo "<div class='alert alert-danger'>Album introuvable</div>";
    require 'includes/footer.php';
    exit;
}

/* Photos avec pagination sécurisée */
$stmtPhotos = $pdo->prepare("
    SELECT * FROM photos 
    WHERE album_id = :album_id
    ORDER BY date_upload DESC
    LIMIT :limit OFFSET :offset
");
$stmtPhotos->bindValue(':album_id', $album_id, PDO::PARAM_INT);
$stmtPhotos->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmtPhotos->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmtPhotos->execute();
$photos = $stmtPhotos->fetchAll();

/* Nombre total pour pagination */
$stmtCount = $pdo->prepare("SELECT COUNT(*) FROM photos WHERE album_id = :album_id");
$stmtCount->bindValue(':album_id', $album_id, PDO::PARAM_INT);
$stmtCount->execute();
$totalPhotos = $stmtCount->fetchColumn();
$totalPages = ceil($totalPhotos / $limit);
?>

<h2 class="mb-3">📁 <?= htmlspecialchars($album['titre']) ?></h2>
<p><?= htmlspecialchars($album['description']) ?></p>

<?php if (!$photos): ?>
    <div class="alert alert-info">Aucune photo dans cet album.</div>
<?php else: ?>
<div class="row">
    <?php foreach ($photos as $photo): ?>
        <div class="col-md-3 mb-3">
            <a href="uploads/<?= htmlspecialchars($photo['nom_fichier']) ?>" class="lightbox-link">
                <img src="uploads/<?= htmlspecialchars($photo['nom_fichier']) ?>"
                     class="card-img-top"
                     style="height:200px; object-fit:cover;"
                     alt="<?= htmlspecialchars($photo['titre']) ?>">
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php if ($totalPages > 1): ?>
<nav>
    <ul class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?id=<?= $album_id ?>&page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
<?php endif; ?>
<?php endif; ?>

<!-- Lightbox overlay -->
<div id="lightbox-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
    <img id="lightbox-img" src="" style="max-width:90%; max-height:90%; box-shadow:0 0 15px #000;">
</div>

<script>
document.querySelectorAll('.lightbox-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const src = this.getAttribute('href');
        const overlay = document.getElementById('lightbox-overlay');
        const img = document.getElementById('lightbox-img');
        img.src = src;
        overlay.style.display = 'flex';
    });
});

document.getElementById('lightbox-overlay').addEventListener('click', function() {
    this.style.display = 'none';
});
</script>

<?php require 'includes/footer.php'; ?>
