<?php
require_once __DIR__ . '/../../back/auth/check_auth.php';
require_once __DIR__ . "/../../back/model/Article.php";
require_once __DIR__ . "/../../back/db/Connection.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['success' => false, 'error' => 'ID manquant']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT * FROM article_historic WHERE id = :id");
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $version = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($version) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => [
                'title' => $version['title'],
                'content' => $version['content'],
                'date' => date('Y-m-d', strtotime($version['created_at'])),
                'cover' => $version['cover']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Version non trouvée']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
