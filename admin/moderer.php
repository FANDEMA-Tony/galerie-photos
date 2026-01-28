<?php
require '../config/database.php';
require '../includes/header.php';

/* -------------------------------
   Suppression d'un commentaire
-------------------------------- */
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM commentaires WHERE id = ?");
    $stmt->execute([$id]);
    echo "<div class='alert alert-success'>Commentaire supprimé.</div>";
}

/* -------------------------------
   Filtres
-------------------------------- */
$album_id_filter = isset($_GET['album_id']) && is_numeric($_GET['album_id']) ? (int)$_GET['album_id'] : null;
$photo_id_filter = isset($_GET['photo_id']) && is_numeric($_GET['photo_id']) ? (int)$_GET['photo_id'] : null;

/* -------------------------------
   Récupération des albums
-------------------------------- */
$albums = $pdo->query(
    "SELECT id, titre FROM albums ORDER BY titre ASC"
)->fetchAll(); // fetchAll avec mode FETCH_ASSOC par défaut

/* -------------------------------
   Récupération des photos
-------------------------------- */
if ($album_id_filter) {
    $stmtPhotos = $pdo->prepare("
        SELECT p.id, p.titre, a.titre AS album_titre
        FROM photos p
        JOIN albums a ON p.album_id = a.id
        WHERE a.id = ?
        ORDER BY p.titre ASC
    ");
    $stmtPhotos->execute([$album_id_filter]);
    $photos = $stmtPhotos->fetchAll();
} else {
    $photos = $pdo->query("
        SELECT p.id, p.titre, a.titre AS album_titre
        FROM photos p
        JOIN albums a ON p.album_id = a.id
        ORDER BY p.date_upload DESC
    ")->fetchAll();
}

/* -------------------------------
   Récupération des commentaires
-------------------------------- */
$sql = "
    SELECT c.*, 
           p.titre AS photo_titre,
           p.nom_fichier,
           a.titre AS album_titre
    FROM commentaires c
    JOIN photos p ON c.photo_id = p.id
    JOIN albums a ON p.album_id = a.id
";

$params = [];
$conditions = [];

if ($album_id_filter) {
    $conditions[] = "a.id = ?";
    $params[] = $album_id_filter;
}
if ($photo_id_filter) {
    $conditions[] = "p.id = ?";
    $params[] = $photo_id_filter;
}

if ($conditions) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY c.date_creation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$commentaires = $stmt->fetchAll();
?>

<h2 class="mb-4">📝 Modération des commentaires</h2>

<!-- ===============================
     Filtres
================================= -->
<form method="get" class="mb-4 row g-2">
    <div class="col-md-6">
        <label class="form-label">Filtrer par album</label>
        <select name="album_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Tous les albums --</option>
            <?php foreach ($albums as $a): ?>
                <option value="<?= $a['id'] ?>" <?= ($album_id_filter == $a['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($a['titre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Filtrer par photo</label>
        <select name="photo_id" class="form-control" onchange="this.form.submit()">
            <option value="">-- Toutes les photos --</option>
            <?php foreach ($photos as $p): ?>
                <option value="<?= $p['id'] ?>" <?= ($photo_id_filter == $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['titre']) ?> (<?= htmlspecialchars($p['album_titre']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</form>

<!-- ===============================
     Tableau commentaires
================================= -->
<?php if (empty($commentaires)): ?>
    <p>Aucun commentaire à modérer.</p>
<?php else: ?>
<table class="table table-bordered table-striped align-middle">
    <thead>
        <tr>
            <th>ID</th>
            <th>Auteur</th>
            <th>Email</th>
            <th>Commentaire</th>
            <th>Photo</th>
            <th>Aperçu</th>
            <th>Album</th>
            <th>Date</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($commentaires as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['auteur']) ?></td>
            <td><?= htmlspecialchars($c['email']) ?></td>
            <td><?= nl2br(htmlspecialchars($c['commentaire'])) ?></td>
            <td><?= htmlspecialchars($c['photo_titre']) ?></td>
            <td>
                <img src="../uploads/<?= htmlspecialchars($c['nom_fichier']) ?>"
                     style="width:50px;height:50px;object-fit:cover;border-radius:4px;">
            </td>
            <td><?= htmlspecialchars($c['album_titre']) ?></td>
            <td><?= $c['date_creation'] ?></td>
            <td>
                <a href="?delete=<?= $c['id'] ?>&album_id=<?= $album_id_filter ?>&photo_id=<?= $photo_id_filter ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Supprimer ce commentaire ?');">
                   Supprimer
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<?php require '../includes/footer.php'; ?>
