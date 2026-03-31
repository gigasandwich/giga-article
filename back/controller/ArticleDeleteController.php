<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once '../db/Connection.php';
require_once '../model/Article.php';

function handleDelete() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new RuntimeException("Méthode non autorisée", 405);
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : 'delete';

        if ($id <= 0) {
            throw new RuntimeException("ID d'article invalide", 422);
        }

        $pdo = connection();
        if (!$pdo) {
            throw new RuntimeException("Connexion à la base de données échouée", 500);
        }

        $article = Article::getById($pdo, $id);
        if (!$article) {
            throw new RuntimeException("Article non trouvé", 404);
        }

        if ($action === 'restore') {
            $article->restore($pdo);
            $message = "Article restauré avec succès";
        } else {
            $article->delete($pdo);
            $message = "Article supprimé avec succès";
        }

        // Check if it's an AJAX request
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $message,
                'id' => $id,
                'deleted' => ($action !== 'restore')
            ]);
        } else {
            // Standard form submission
            header("Location: /front/backoffice/index.php?success=" . urlencode($message));
        }
    } catch (Exception $e) {
        $status = $e->getCode() !== 0 && is_int($e->getCode()) ? $e->getCode() : 500;
        http_response_code($status);
        
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        } else {
            header("Location: /front/backoffice/index.php?error=" . urlencode($e->getMessage()));
        }
    }
}

handleDelete();
