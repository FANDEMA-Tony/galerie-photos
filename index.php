<?php
require 'config/database.php';
require 'includes/header.php';

/* ----------------------------
   Pagination
---------------------------- */
$albumsPerPage = 6; // nombre d'albums par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $albumsPerPage;

/* ----------------------------
   Suppression d'un album
---------------------------- */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $albumId = (int) $_GET['delete'];

    try {
        // Supprimer les fichiers photos (originaux + miniatures)
        $stmtPhotos = $pdo->prepare("SELECT nom_fichier FROM photos WHERE album_id = ?");
        $stmtPhotos->execute([$albumId]);
        $photos = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);

        foreach ($photos as $photo) {
            $file = __DIR__ . '/uploads/' . $photo['nom_fichier'];
            $thumb = __DIR__ . '/uploads/thumbs/' . $photo['nom_fichier'];
            if (file_exists($file)) unlink($file);
            if (file_exists($thumb)) unlink($thumb);
        }

        // Supprimer les photos en DB
        $pdo->prepare("DELETE FROM photos WHERE album_id = ?")->execute([$albumId]);

        // Supprimer l'album
        $pdo->prepare("DELETE FROM albums WHERE id = ?")->execute([$albumId]);

        echo "<div class='alert alert-success'>Album supprimé avec succès.</div>";
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Erreur lors de la suppression : " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}

/* ----------------------------
   Nombre total d'albums
---------------------------- */
$totalAlbums = $pdo->query("SELECT COUNT(*) FROM albums")->fetchColumn();
$totalPages = ceil($totalAlbums / $albumsPerPage);

/* ----------------------------
   Récupération albums avec miniatures
   (MIN() pour choisir une photo cohérente comme miniature)
---------------------------- */
$stmt = $pdo->prepare("
    SELECT a.*, MIN(p.nom_fichier) AS mini
    FROM albums a
    LEFT JOIN photos p ON p.album_id = a.id
    GROUP BY a.id
    ORDER BY a.date_creation DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit', $albumsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2 class="mb-4">📁 Albums photos</h2>

<?php if (empty($albums)): ?>
    <div class="alert alert-info">Aucun album disponible pour le moment.</div>
<?php else: ?>
<div class="row">
    <?php foreach ($albums as $album): ?>
        <div class="col-md-4 mb-3 d-flex align-items-stretch">
            <div class="card shadow-sm w-100">
                <?php if ($album['mini']): ?>
                    <img src="uploads/<?= htmlspecialchars($album['mini']) ?>"
                         class="card-img-top" alt="<?= htmlspecialchars($album['titre']) ?>"
                         style="height:180px; object-fit:cover;">
                <?php else: ?>
                    <div class="card-img-top" style="height:180px; background:#e9ecef; display:flex; justify-content:center; align-items:center;">
                        <span class="text-muted">Aucune photo</span>
                    </div>
                <?php endif; ?>
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?= htmlspecialchars($album['titre']) ?></h5>
                    <p class="card-text"><?= htmlspecialchars($album['description']) ?></p>
                    <div class="mt-auto d-flex justify-content-between">
                        <a href="album.php?id=<?= $album['id'] ?>" class="btn btn-primary btn-sm">Voir l’album</a>
                        <a href="?delete=<?= $album['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Voulez-vous vraiment supprimer cet album ?');">
                           Supprimer
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ----------------------------
     Pagination (affichée seulement si > 1 page)
---------------------------- -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Pagination">
    <ul class="pagination justify-content-center mt-4">
        <?php if ($page > 1): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">Précédent</a></li>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <li class="page-item <?= ($p == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?>"><?= $p ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">Suivant</a></li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>
