<?php
require 'config/database.php';
require 'includes/header.php';

/* Vérification ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Photo introuvable.</div>";
    require 'includes/footer.php';
    exit;
}

$photo_id = (int) $_GET['id'];

/* Incrémentation sécurisée des vues */
$pdo->prepare("UPDATE photos SET vues = vues + 1 WHERE id = ?")->execute([$photo_id]);

/* Récupération photo avec info album */
$stmt = $pdo->prepare("
    SELECT p.*, a.titre AS album_titre
    FROM photos p
    JOIN albums a ON p.album_id = a.id
    WHERE p.id = ?
");
$stmt->execute([$photo_id]);
$photo = $stmt->fetch();

if (!$photo) {
    echo "<div class='alert alert-danger'>Photo introuvable.</div>";
    require 'includes/footer.php';
    exit;
}

/* Gestion commentaire */
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auteur      = trim($_POST['auteur'] ?? '');
    $email       = trim($_POST['email'] ?? '');
    $commentaire = trim($_POST['commentaire'] ?? '');

    if ($auteur && $email && $commentaire && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $pdo->prepare("
            INSERT INTO commentaires (photo_id, auteur, email, commentaire)
            VALUES (?, ?, ?, ?)
        ")->execute([$photo_id, $auteur, $email, $commentaire]);

        $message = "<div class='alert alert-success'>Commentaire ajouté avec succès.</div>";
    } else {
        $message = "<div class='alert alert-danger'>Veuillez remplir correctement tous les champs.</div>";
    }
}

/* Récupération commentaires */
$commentaires = $pdo->prepare("SELECT * FROM commentaires WHERE photo_id = ? ORDER BY date_creation DESC");
$commentaires->execute([$photo_id]);
$commentaires = $commentaires->fetchAll();
?>

<h2 class="mb-3"><?= htmlspecialchars($photo['titre']) ?></h2>
<p>📁 Album : <a href="album.php?id=<?= $photo['album_id'] ?>"><?= htmlspecialchars($photo['album_titre']) ?></a></p>

<a href="uploads/<?= htmlspecialchars($photo['nom_fichier']) ?>" class="lightbox-link">
    <img src="uploads/<?= htmlspecialchars($photo['nom_fichier']) ?>"
         class="img-fluid mb-2"
         alt="<?= htmlspecialchars($photo['titre']) ?>">
</a>

<p class="text-muted">👁️ <?= (int)$photo['vues'] ?> vue(s)</p>
<p><?= htmlspecialchars($photo['description']) ?></p>

<hr>
<h4>💬 Commentaires</h4>
<?= $message ?? '' ?>
<?php if (!$commentaires): ?>
    <p>Aucun commentaire pour le moment.</p>
<?php else: ?>
    <?php foreach ($commentaires as $com): ?>
        <div class="mb-3 border-bottom pb-2">
            <strong><?= htmlspecialchars($com['auteur']) ?></strong>
            <small class="text-muted">(<?= htmlspecialchars($com['email']) ?> — <?= $com['date_creation'] ?>)</small>
            <p><?= nl2br(htmlspecialchars($com['commentaire'])) ?></p>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<hr>
<h4>✍️ Ajouter un commentaire</h4>
<form method="post">
    <div class="mb-3">
        <label class="form-label">Nom</label>
        <input type="text" name="auteur" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Commentaire</label>
        <textarea name="commentaire" class="form-control" required></textarea>
    </div>
    <button class="btn btn-primary">Envoyer</button>
</form>

<!-- Lightbox overlay -->
<div id="lightbox-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); justify-content:center; align-items:center; z-index:9999;">
    <img id="lightbox-img" src="" style="max-width:90%; max-height:90%; box-shadow:0 0 15px #000;">
</div>

<script>
document.querySelectorAll('.lightbox-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const overlay = document.getElementById('lightbox-overlay');
        document.getElementById('lightbox-img').src = this.href;
        overlay.style.display = 'flex';
    });
});
document.getElementById('lightbox-overlay').addEventListener('click', function() { this.style.display = 'none'; });
</script>

<?php require 'includes/footer.php'; ?>
