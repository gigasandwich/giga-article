<?php
session_start();
require_once '../Connection.php';
require_once '../article/Article.php';

function postArticle() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }

        $sessionArticle = isset($_SESSION['article']) ? $_SESSION['article'] : ['content_photos' => []];
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $date = isset($_POST['date']) ? trim($_POST['date']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $cover = isset($_POST['cover']) ? trim($_POST['cover']) : null;

        if ($title === '' || $date === '' || $content === '') {
            http_response_code(422);
            echo "Missing required fields";
            return;
        }

        // Rename photos from session
        $uploadsDir = '../../uploads';
        $finalPhotoPaths = [];

        foreach ($sessionArticle['content_photos'] as $photo) {
            $oldName = $photo['original_name'];
            $oldPath = $uploadsDir . '/' . $oldName;

            if (file_exists($oldPath)) {
                $extension = strtolower(pathinfo($oldName, PATHINFO_EXTENSION));
                $titlePropre = preg_replace('/[^a-zA-Z0-9]/', '-', $title);
                $datePropre = str_replace('-', '', $date);
                $newName = $titlePropre . '_' . $datePropre . '_' . uniqid() . '.' . $extension;
                $newPath = $uploadsDir . '/' . $newName;

                if (rename($oldPath, $newPath)) {
                    // TODO: only change img.src=
                    // Update content with new path
                    $content = str_replace($oldName, $newName, $content);
                    $finalPhotoPaths[] = 'uploads/' . $newName;
                }
            }
        }

        $pdo = connection();
        if (!$pdo) {
            http_response_code(500);
            echo "Database connection failed";
            return;
        }

        $article = new Article(1, $title, '', $cover, $content, $date);
        $article->saveArticle($pdo);
        $article->createUrl();
        $article->saveUrl($pdo);

        // Clear session after successful creation
        unset($_SESSION['article']);

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $article->getId(),
            'url' => $article->getUrl(),
            'photos' => $finalPhotoPaths
        ]);
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        echo "Error creating article: " . $e->getMessage();
    } catch (PDOException $e) {
        http_response_code(500);
        echo "Database error: " . $e->getMessage();
    }
}

postArticle();