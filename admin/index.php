<?php
require '../config/database.php';
require '../includes/header.php';

$message = "";

/* Traitement du formulaire */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre']);
    $description = trim($_POST['description']);

    if (!empty($titre)) {
        $stmt = $pdo->prepare(
            "INSERT INTO albums (titre, description)
             VALUES (?, ?)"
        );
        $stmt->execute([$titre, $description]);

        $message = "<div class='alert alert-success'>
                        Album ajouté avec succès.
                    </div>";
    } else {
        $message = "<div class='alert alert-danger'>
                        Le titre est obligatoire.
                    </div>";
    }
}
?>

<h2 class="mb-4">📁 Ajouter un album</h2>

<?= $message ?>

<form method="post">
    <div class="mb-3">
        <label class="form-label">Titre de l’album *</label>
        <input type="text"
               name="titre"
               class="form-control"
               required>
    </div>

    <div class="mb-3">
        <label class="form-label">Description</label>
        <textarea name="description"
                  class="form-control"
                  rows="4"></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
        ➕ Ajouter l’album
    </button>
</form>

<?php require '../includes/footer.php'; ?>
