<?php
session_start();
require_once '../db/Connection.php';
require_once '../model/Article.php';

function postArticle() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new RuntimeException("Method not allowed", 405);
        }

        $sessionArticle = isset($_SESSION['article']) ? $_SESSION['article'] : ['content_photos' => []];
        
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $date = isset($_POST['date']) ? trim($_POST['date']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $cover = isset($_POST['cover']) ? trim($_POST['cover']) : null;

        if ($title === '' || $date === '' || $content === '') {
            throw new RuntimeException("Missing required fields", 422);
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
                    // Update content with new path, but only inside img src
                    $escapedOldName = preg_quote($oldName, '/');
                    $content = preg_replace(
                        '/(<img\b[^>]*\bsrc=["\"][^"\"]*)' . $escapedOldName . '([^"\"]*["\"][^>]*>)/i',
                        '$1' . $newName . '$2',
                        $content
                    );
                    $finalPhotoPaths[] = 'uploads/' . $newName;
                }
            }
        }

        $pdo = connection();
        if (!$pdo) {
            throw new RuntimeException("Database connection failed", 500);
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
    } catch (RuntimeException $e) {
        $status = $e->getCode() !== 0 ? $e->getCode() : 500;
        http_response_code($status);
        header(header: 'Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } catch (InvalidArgumentException $e) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => "Error creating article: " . $e->getMessage()
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => "Database error: " . $e->getMessage()
        ]);
    }
}

postArticle();