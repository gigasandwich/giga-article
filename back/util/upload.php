<?php

function uploadImages($temporaryFile, $level, $title, $date) {    
    if (!$temporaryFile || !isset($temporaryFile['error'])) {
        throw new RuntimeException("Fichier manquant ou invalide", 400);
    }
    // 1. Verifier s'il y a eu une erreur lors de l'upload
    if ($temporaryFile['error'] !== UPLOAD_ERR_OK) {
        switch ($temporaryFile['error']) {
            case UPLOAD_ERR_INI_SIZE:
                $message = "Le fichier dépasse la limite autorisée par le serveur (php.ini).";
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = "Le fichier dépasse la limite autorisée par le formulaire.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = "L'envoi du fichier a été interrompu.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = "Aucun fichier n'a été envoyé.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = "Dossier temporaire manquant sur le serveur.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = "Échec de l'écriture du fichier sur le disque.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = "Une extension PHP a arrêté l'envoi.";
                break;
            default:
                $message = "Erreur inconnue lors de l'envoi du fichier.";
        }
        throw new RuntimeException($message, 400);
    }

    // 2. Verifier la taille du fichier (ex: limite a 5 Mo)
    $tailleMax = 5 * 1024 * 1024; // 5 Mo en octets
    if ($temporaryFile['size'] > $tailleMax) {
        throw new RuntimeException("Le fichier est trop volumineux (max 5 Mo).", 413);
    }

    // 3. Verifier l'extension du fichier (securite)
    $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    // pathinfo permet d'extraire l'extension du nom original. strtolower la met en minuscules.
    $extensionFichier = strtolower(pathinfo($temporaryFile['name'], PATHINFO_EXTENSION));

    if (!in_array($extensionFichier, $extensionsAutorisees)) {
        throw new RuntimeException("Format de fichier non autorise. Images uniquement.", 422);
    }

    // Keep the original name for now as requested
    $originalName = $temporaryFile['name'];
    
    $uploadFile = 'uploads';
    $destinationFinal = $level . '/' . $uploadFile;
    // 5. Creer le dossier de destination s'il n'existe pas encore
    if (!is_dir($destinationFinal)) {
         mkdir($destinationFinal, 0755, true);
    }

    // 6. On cree le chemin complet avec le nom original
    $cheminComplet = $destinationFinal . '/' . $originalName;

    // 7. On deplace le fichier
    if (move_uploaded_file($temporaryFile['tmp_name'], $cheminComplet)) {
        // Store in session
        if (!isset($_SESSION['article'])) {
            $_SESSION['article'] = ['content_photos' => []];
        }
        $_SESSION['article']['content_photos'][] = [
            'original_name' => $originalName,
            'title_at_upload' => $title,
            'date_at_upload' => $date
        ];

        return ["success" => true, "location" => '/' . $uploadFile . '/' . $originalName];
    } else {
        throw new RuntimeException("Erreur lors de la sauvegarde sur le serveur.", 500);
    }
}
