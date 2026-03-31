<?php 
require_once __DIR__ . '/../../back/auth/check_auth.php'; 
require_once __DIR__ . "/../../back/model/Article.php";
require_once __DIR__ . "/../../back/db/Connection.php";

/**
 * HTML to raw string for the article body (img becomes alt)
 */
function parse_excerpt_html(string $html): string {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $wrapped = '<!doctype html><html><body>' . $html . '</body></html>';
    $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $images = $doc->getElementsByTagName('img');
    for ($i = $images->length - 1; $i >= 0; $i--) {
        $img = $images->item($i);
        $alt = trim($img->getAttribute('alt')) ?: 'image';
        $text = "(image of {$alt})";
        $textNode = $doc->createTextNode($text);
        $img->parentNode->replaceChild($textNode, $img);
    }

    $body = $doc->getElementsByTagName('body')->item(0);
    // Use textContent to get plain text (this collapses tags)
    $text = $body ? $body->textContent : '';
    // Normalize whitespace and trim
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text);
    // Escape for safe output in HTML
    return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

$articles = Article::getAll($pdo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Articles</title>
    
    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/list-article.css">
    <link rel="stylesheet" href="/public/assets/styles/backoffice-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include "../component/header.php"; ?>
    <main class="articles-list">
        <div class="backoffice-header">
            <h1>Gestion des articles</h1>
            <a href="/backoffice/articles/create" class="btn btn-primary">Nouveau Article</a>
        </div>

        <ul class="admin-article-list">
            <?php foreach ($articles as $a): ?>
                <li class="admin-article-item <?= $a->isDeleted() ? 'deleted' : '' ?>">
                    <div class="news-article">
                        <?php if ($a->isDeleted()): ?>
                            <span class="deleted-badge">Supprimé</span>
                        <?php endif; ?>
                        <div class="thumb">
                            <img src="/<?= htmlspecialchars($a->getCover() ?: 'public/assets/images/placeholder.jpg', ENT_QUOTES, 'UTF-8') ?>" alt="Thumbnail for <?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="news-content">
                            <h2 class="news-title"><?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>
                            <time class="news-date" datetime="<?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></time>
                            <div class="news-body"><?= parse_excerpt_html($a->getContent()) ?></div>
                        </div>
                        <div class="admin-actions">
                            <?php if ($a->isDeleted()): ?>
                                <form action="/backoffice/articles/restore" method="POST">
                                    <input type="hidden" name="id" value="<?= $a->getId() ?>">
                                    <button type="submit" class="btn btn-restore" title="Restaurer">
                                        <i class="fa-solid fa-rotate-left"></i>
                                    </button>
                                </form>
                            <?php else: ?>
                                <a href="/backoffice/articles/edit/<?= $a->getId() ?>" class="btn btn-edit" title="Modifier">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="/backoffice/articles/delete" method="POST" onsubmit="return confirm('Supprimer cet article ?')">
                                    <input type="hidden" name="id" value="<?= $a->getId() ?>">
                                    <button type="submit" class="btn btn-delete" title="Supprimer">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>
