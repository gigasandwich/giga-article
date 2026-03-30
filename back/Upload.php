<?php

function uploadImages($temporaryFile, $destinationFinal) {
    // 1. Verifier s'il y a eu une erreur lors de l'upload
    if ($temporaryFile['error'] !== UPLOAD_ERR_OK) {
        return ["success" => false, "message" => "Erreur lors de l'envoi du fichier."];
    }

    // 2. Verifier la taille du fichier (ex: limite a 5 Mo)
    $tailleMax = 5 * 1024 * 1024; // 5 Mo en octets
    if ($temporaryFile['size'] > $tailleMax) {
        return ["success" => false, "message" => "Le fichier est trop volumineux (max 5 Mo)."];
    }

    // 3. Verifier l'extension du fichier (securite)
    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    // pathinfo permet d'extraire l'extension du nom original. strtolower la met en minuscules.
    $extensionFichier = strtolower(pathinfo($temporaryFile['name'], PATHINFO_EXTENSION));

    if (!in_array($extensionFichier, $extensionsAutorisees)) {
        return ["success" => false, "message" => "Format de fichier non autorise. Images uniquement."];
    }

    // 4. Renommer le fichier pour eviter les doublons et les problemes de caracteres speciaux
    // uniqid() genere un identifiant unique (ex: img_64f1a2b3c4d5.jpg)
    $nouveauNomFichier = uniqid('img_') . '.' . $extensionFichier;

    // 5. Creer le dossier de destination s'il n'existe pas encore
    if (!is_dir($destinationFinal)) {
         mkdir($destinationFinal, 0755, true);
    }

    // 6. On cree le chemin complet avec le NOUVEAU nom securise
    $cheminComplet = $destinationFinal . '/' . $nouveauNomFichier;

    // 7. On deplace le fichier
    if (move_uploaded_file($temporaryFile['tmp_name'], $cheminComplet)) {
        return ["success" => true, "message" => "Image uploadée avec succes!", "nom_fichier" => $nouveauNomFichier];
    } else {
        return ["success" => false, "message" => "Erreur lors de la sauvegarde sur le serveur."];
    }
}

