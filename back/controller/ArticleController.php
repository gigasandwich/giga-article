<?php
require_once '../Connection.php';
require_once '../article/Article.php';

function postArticle() {
    try {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo "Method not allowed";
            return;
        }

        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $date = isset($_POST['date']) ? trim($_POST['date']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $cover = isset($_POST['cover']) ? trim($_POST['cover']) : null;

        if ($title === '' || $date === '' || $content === '') {
            http_response_code(422);
            echo "Missing required fields";
            return;
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

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'id' => $article->getId(),
            'url' => $article->getUrl(),
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