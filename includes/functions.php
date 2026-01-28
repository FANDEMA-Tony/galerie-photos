<?php
function createThumbnail($source, $destination, $newWidth) {
    $imageInfo = getimagesize($source);

    if (!$imageInfo) return false;

    // Choix du type d'image
    switch($imageInfo['mime']) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            break;
        default:
            return false; // format non supporté
    }

    // Dimensions
    $width = imagesx($image);
    $height = imagesy($image);
    $newHeight = ($height / $width) * $newWidth;

    // Création miniature
    $thumb = imagecreatetruecolor($newWidth, $newHeight);
    imagecopyresampled($thumb, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    // Vérifie dossier destination
    $dir = dirname($destination);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    if (!is_writable($dir)) die("Le dossier $dir n'est pas accessible en écriture !");

    // Sauvegarde miniature
    $result = imagejpeg($thumb, $destination, 85);

    // Libère mémoire
    imagedestroy($image);
    imagedestroy($thumb);

    // Vérification finale
    if (!$result) return false;

    return true;
}
