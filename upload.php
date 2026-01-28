<?php
require 'config/database.php';
require 'includes/functions.php';
require 'includes/header.php';

/* Albums disponibles */
$albums = $pdo->query("SELECT * FROM albums ORDER BY titre")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $album_id = isset($_POST['album_id']) ? (int)$_POST['album_id'] : 0;
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');

    /* Vérification album */
    $stmtAlbum = $pdo->prepare("SELECT id FROM albums WHERE id = ?");
    $stmtAlbum->execute([$album_id]);
    if (!$stmtAlbum->fetch()) {
        echo "<div class='alert alert-danger'>Album invalide.</div>";
    }
    /* Vérification fichier */
    elseif (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== 0) {
        echo "<div class='alert alert-danger'>Erreur lors de l’upload</div>";
    } else {
        $file = $_FILES['photo'];
        $allowed = ['image/jpeg', 'image/png'];
        if (!in_array($file['type'], $allowed)) {
            echo "<div class='alert alert-danger'>Format non autorisé</div>";
        } elseif ($file['size'] > 2*1024*1024) {
            echo "<div class='alert alert-danger'>Fichier trop volumineux (max 2 Mo)</div>";
        } else {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $fileName = uniqid() . '.' . $ext;
            $destination = 'uploads/' . $fileName;
            $thumbDest = 'uploads/thumbs/' . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                if (!createThumbnail($destination, $thumbDest, 300)) {
                    echo "<div class='alert alert-warning'>Erreur lors de la création du thumbnail</div>";
                }
                $pdo->prepare("INSERT INTO photos (album_id, nom_fichier, titre, description) VALUES (?, ?, ?, ?)")
                    ->execute([$album_id, $fileName, $titre, $description]);
                echo "<div class='alert alert-success'>Photo ajoutée avec succès</div>";
            } else {
                echo "<div class='alert alert-danger'>Impossible de déplacer le fichier uploadé.</div>";
            }
        }
    }
}
?>

<h2 class="mb-4">⬆️ Ajouter une photo</h2>

<form method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Album</label>
        <select name="album_id" class="form-control" required>
            <?php foreach ($albums as $album): ?>
                <option value="<?= $album['id'] ?>"><?= htmlspecialchars($album['titre']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-3">
        <label class="form-label">Titre</label>
        <input type="text" name="titre" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Photo</label>
        <input type="file" name="photo" class="form-control" required>
    </div>
    <button class="btn btn-success">Uploader</button>
</form>

<?php require 'includes/footer.php'; ?>
