<?php
if (!isset($_SESSION)) {
    session_start();
}

require_once '../db/Connection.php';
require_once '../model/Article.php';
require_once '../util/upload.php';

function updateArticle() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new RuntimeException("Method not allowed", 405);
        }

        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $date = isset($_POST['date']) ? trim($_POST['date']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($id <= 0 || $title === '' || $date === '' || $content === '') {
            throw new RuntimeException("Missing required fields", 422);
        }

        $pdo = connection();
        if (!$pdo) {
            throw new RuntimeException("Database connection failed", 500);
        }

        $article = Article::getById($pdo, $id);
        if (!$article) {
            throw new RuntimeException("Article not found", 404);
        }

        // Process files
        $coverPath = $article->getCover();
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadImages($_FILES['cover'], '../..', $title, $date);
            if (isset($uploadResult['success']) && $uploadResult['success']) {
                $oldPath = '../../' . $uploadResult['location'];
                $extension = strtolower(pathinfo($oldPath, PATHINFO_EXTENSION));
                $titlePropre = preg_replace('/[^a-zA-Z0-9]/', '-', $title);
                $datePropre = str_replace('-', '', $date);
                $newCoverName = 'cover-' . $titlePropre . '_' . $datePropre . '_' . uniqid() . '.' . $extension;
                $newCoverPathFull = '../../uploads/' . $newCoverName;
                
                if (rename($oldPath, $newCoverPathFull)) {
                    $coverPath = 'uploads/' . $newCoverName;
                }
            }
        }

        // Update properties
        $article->setTitle($title);
        $article->setCreatedAt($date);
        $article->setContent($content);
        $article->setCover($coverPath);
        
        // Regenerate URL (if title/date changed)
        $article->createUrl();

        // Perform the update (Article::update handles archival)
        $article->update($pdo);

        // Sync pictures from content
        $article->savePictures($pdo, '../..');

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Article updated successfully',
            'id' => $article->getId(),
            'url' => $article->getUrl()
        ]);

    } catch (Exception $e) {
        $status = $e->getCode() !== 0 && is_int($e->getCode()) ? $e->getCode() : 500;
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}

updateArticle();
