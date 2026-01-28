# Galerie Photos Web

## Description

Galerie Photos Web est une application web permettant de créer, gérer et consulter des albums photos. Les utilisateurs peuvent naviguer dans les albums, visualiser les photos, et laisser des commentaires. Ce projet a été développé dans le cadre du module **Développement Web et Mobile (DWM)** en Licence Professionnelle.

---

## Fonctionnalités principales

- Création et gestion d’albums photos
- Upload sécurisé de photos (JPEG / PNG)
- Génération automatique de miniatures (thumbnails)
- Affichage des photos avec pagination
- Visualisation d’une photo individuelle avec compteur de vues
- Ajout et consultation de commentaires
- Interface responsive et ergonomique avec Bootstrap
- Effet lightbox pour l’affichage des images en grand format

---

## Technologies utilisées

| Technologie | Utilisation |
|------------|-------------|
| PHP 8 | Logique serveur, gestion dynamique |
| MySQL | Base de données relationnelle |
| PDO | Connexion sécurisée à la base de données |
| HTML5 / CSS3 | Structure et style des pages |
| Bootstrap 5 | Design responsive et ergonomique |
| JavaScript | Interactions (ex : lightbox) |

---

## Prérequis

- Serveur web local (ex : XAMPP, WAMP)
- PHP 8 ou supérieur
- MySQL / MariaDB
- Navigateur moderne (Chrome, Edge, Firefox)
- Git (pour gestion du dépôt)

---

## Installation

1. **Cloner le dépôt GitHub/GitLab :**
```bash
git clone https://github.com/ton-utilisateur/galerie_photos_pro.git
Importer la base de données :

Ouvrir phpMyAdmin

Créer une base nommée galerie_photos

Importer le fichier galerie_photos.sql fourni

Configurer la connexion à la base de données :

Ouvrir le fichier config/database.php

Modifier les paramètres si nécessaire :

$host = 'localhost';
$dbname = 'galerie_photos';
$user = 'root';
$password = '';
Déployer l’application :

Copier le dossier galerie_photos dans le répertoire htdocs (XAMPP) ou www (WAMP)

Lancer le serveur Apache et MySQL

Accéder via le navigateur : http://localhost/galerie_photos/

Structure du projet
galerie_photos_pro/
│
├── config/
│   └── database.php         # Connexion à la base de données
│
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php        # Fonctions PHP réutilisables
│
├── uploads/                 # Dossier des images uploadées
│   └── thumbs/              # Miniatures des images
│
├── css/
│   └── style.css
│
├── index.php                # Page d’accueil (liste des albums)
├── album.php                # Page d’un album (grille des photos)
├── photo.php                # Page photo individuelle
├── upload.php               # Formulaire et traitement d’upload
└── README.md
Utilisation
Créer un album : depuis la page d’accueil (formulaire)

Ajouter une photo : sélectionner l’album et uploader l’image

Consulter un album : cliquer sur « Voir l’album »

Visualiser une photo : cliquer sur la miniature (lightbox)

Ajouter un commentaire : remplir le formulaire sous chaque photo

Sécurité
Vérification du type de fichier (JPEG / PNG)

Limite de taille à 2 Mo

Renommage unique des fichiers uploadés

Protection contre les attaques XSS avec htmlspecialchars()

Miniatures générées automatiquement pour optimiser le chargement

Tests
Connexion à la base de données

Création d’albums et ajout de photos

Pagination et tri des photos

Affichage individuel des photos et compteur de vues

Ajout et validation des commentaires

Lightbox et interactions JavaScript

Auteurs
Tony Bienheureux Fandema Mandandji Cappy
Étudiant Licence Professionnelle DWM
Email : tonybienheureuxfandema@gmail.com

Licence
Ce projet est libre à usage pédagogique. Toute reproduction ou modification doit citer l’auteur.

