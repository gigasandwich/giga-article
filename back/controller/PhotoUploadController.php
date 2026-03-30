<?php
session_start();

function uploadImages($temporaryFile, $level, $title, $date) {
    // 1. Verifier s'il y a eu une erreur lors de l'upload
    if ($temporaryFile['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Erreur lors de l'envoi du fichier.", 400);
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titreArticle = $_POST['title'];
        $dateCreation = $_POST['date'];
        $fichierImage = $_FILES['file'];

        $result = uploadImages($fichierImage, '../..', $titreArticle, $dateCreation);
        header('Content-Type: application/json');
        echo json_encode($result);
    } catch (RuntimeException $e) {
        $status = $e->getCode() !== 0 ? $e->getCode() : 500;
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}