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

function parse_filter_datetime(?string $value): ?string {
    if ($value === null || trim($value) === '') {
        return null;
    }

    $date = DateTime::createFromFormat('Y-m-d\\TH:i', $value);
    if ($date === false) {
        return null;
    }

    return $date->format('Y-m-d H:i:s');
}

$range = $_GET['range'] ?? 'all';
$dateStart = null;
$dateEnd = null;
$now = new DateTime();

if ($range === 'today') {
    $start = (clone $now)->setTime(0, 0, 0);
    $end = (clone $now)->setTime(23, 59, 59);
    $dateStart = $start->format('Y-m-d H:i:s');
    $dateEnd = $end->format('Y-m-d H:i:s');
} elseif ($range === 'manual') {
    $dateStart = parse_filter_datetime($_GET['date_start'] ?? null);
    $dateEnd = parse_filter_datetime($_GET['date_end'] ?? null);

    if ($dateStart !== null && $dateEnd !== null && $dateStart > $dateEnd) {
        $tmp = $dateStart;
        $dateStart = $dateEnd;
        $dateEnd = $tmp;
    }
} else {
    // "Jusqu'a aujourd'hui": toutes les dates <= maintenant
    $dateEnd = $now->format('Y-m-d H:i:s');
}

$all_articles = Article::getAll($pdo, $dateStart, $dateEnd);
$articles = array_filter($all_articles, function($a) {
    return !$a->isDeleted();
});

// Separate article types
$articlesWithCover = array_filter($articles, function($a) {
    return !empty($a->getCover());
});
$articlesWithoutCover = array_filter($articles, function($a) {
    return empty($a->getCover());
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/list-article.css">
    <style>
        .section-title {
            font-family: Georgia, serif;
            font-size: 2rem;
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid #000;
            text-transform: uppercase;
        }
        .articles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        .no-cover-list {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        @media (max-width: 768px) {
            .no-cover-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include "../component/header.php"; ?>
    <main class="articles-list">
        <?php if (empty($articles)): ?>
            <div class="empty-state">
                <img src="/public/assets/img/empty.svg" alt="Aucun article" style="max-width: 300px; display: block; margin: 2rem auto;">
                <p style="text-align: center; font-size: 1.2rem; color: #666;">Aucun article n'a encore été publié selon vos criteres</p>
            </div>
        <?php else: ?>
            
            <?php if (!empty($articlesWithCover)): ?>
                <h2 class="section-title">À la une</h2>
                <div class="articles-grid">
                    <?php foreach ($articlesWithCover as $a): ?>
                        <a href="<?= htmlspecialchars($a->getUrl(), ENT_QUOTES, 'UTF-8') ?>" class="news-link">
                            <article class="news-article">
                                <div class="thumb">
                                    <img src="/<?= htmlspecialchars($a->getCover(), ENT_QUOTES, 'UTF-8') ?>" alt="Couverture de <?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                                </div>
                                <div class="news-content">
                                    <h3 class="news-title"><?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <time class="news-date" datetime="<?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></time>
                                    <div class="news-body"><?= parse_excerpt_html($a->getContent()) ?></div>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($articlesWithoutCover)): ?>
                <h2 class="section-title">Brèves & Analyses</h2>
                <div class="no-cover-list">
                    <?php foreach ($articlesWithoutCover as $a): ?>
                        <a href="<?= htmlspecialchars($a->getUrl(), ENT_QUOTES, 'UTF-8') ?>" class="news-link">
                            <article class="news-article no-thumb" style="border-left: 4px solid #000; padding-left: 1rem;">
                                <div class="news-content">
                                    <h3 class="news-title" style="font-size: 1.4rem;"><?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?></h3>
                                    <time class="news-date" datetime="<?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a->getCreatedAt(), ENT_QUOTES, 'UTF-8') ?></time>
                                    <div class="news-body"><?= parse_excerpt_html($a->getContent()) ?></div>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </main>
</body>
</html>