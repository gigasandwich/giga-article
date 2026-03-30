<?php
require_once "../../back/model/Article.php";
require_once "../../back/db/Connection.php";

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
    <title>Document</title>
    
    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/list-article.css">
</head>
<body>
    <main class="articles-list">
        <h1>Liste des articles</h1>
        <ul>
            <?php foreach ($articles as $a): ?>
                <li>
                    <a href="<?= htmlspecialchars($a->getUrl(), ENT_QUOTES, 'UTF-8') ?>" class="news-link">
                        <article class="news-article">
                            <div class="thumb">
                                <img src="/<?= htmlspecialchars($a->getCover(), ENT_QUOTES, 'UTF-8') ?>" alt="Thumbnail for <?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="news-content">
                                <h2 class="news-title"><?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>
                                <time class="news-date" datetime="<?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></time>
                                <div class="news-body"><?= parse_excerpt_html($a->getContent()) ?></div>
                            </div>
                        </article>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>