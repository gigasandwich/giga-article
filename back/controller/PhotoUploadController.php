<?php
require_once '../util/upload.php';

if (!isset($_SESSION)) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $titreArticle = $_POST['title'];
        $dateCreation = $_POST['date'];
        $fichierImage = isset($_FILES['file']) ? $_FILES['file'] : null;

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