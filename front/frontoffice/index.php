<?php
// require_once __DIR__ . "/../../back/util/minify.php";

require_once "../../back/model/Article.php";
require_once "../../back/db/Connection.php";

/**
 * HTML to raw string for the article body (img becomes alt)
 */
function parse_excerpt_html(string $html): string {
    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    $wrapped = '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
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

// Group articles by date (Y-m-d)
$groupedByDate = [];
foreach ($articles as $a) {
    $dateKey = (new DateTime($a->getCreatedAt()))->format('Y-m-d');
    if (!isset($groupedByDate[$dateKey])) {
        $groupedByDate[$dateKey] = [
            'withCover' => [],
            'withoutCover' => []
        ];
    }
    if (!empty($a->getCover())) {
        $groupedByDate[$dateKey]['withCover'][] = $a;
    } else {
        $groupedByDate[$dateKey]['withoutCover'][] = $a;
    }
}

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
    <?php include "../component/header.php"; ?>
    <main class="news-container">
        <?php if (empty($groupedByDate)): ?>
            <div class="empty-state">
                <img src="/public/assets/img/empty.svg" alt="Aucun article" style="max-width: 300px; display: block; margin: 2rem auto;">
                <p style="text-align: center; font-size: 1.2rem; color: #666;">Aucun article n'a encore été publié selon vos critères</p>
            </div>
        <?php else: ?>
            
            <?php 
            $isFirstGlobal = true; // Flag for the very first article of the whole page
            foreach ($groupedByDate as $date => $data): 
                $withCover = $data['withCover'];
                $withoutCover = $data['withoutCover'];
            ?>
                <section class="date-section">
                    <div class="date-header">
                        <?= date('d F Y', strtotime($date)) ?>
                    </div>

                    <?php if (!empty($withCover)): ?>
                        <?php if ($isFirstGlobal): 
                            $featured = array_shift($withCover);
                            $isFirstGlobal = false;
                        ?>
                            <a href="<?= htmlspecialchars($featured->getUrl(), ENT_QUOTES, 'UTF-8') ?>" class="article-card featured-hero">
                                <div class="card-img-wrapper">
                                    <img src="/<?= htmlspecialchars($featured->getCover()) ?>" alt="<?= htmlspecialchars($featured->getTitle()) ?>">
                                </div>
                                <div class="hero-text">
                                    <h2 class="card-title"><?= htmlspecialchars($featured->getTitle()) ?></h2>
                                    <p class="card-excerpt"><?= parse_excerpt_html($featured->getContent()) ?></p>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($withCover)): ?>
                            <div class="covers-scroll-container">
                                <?php foreach ($withCover as $a): ?>
                                    <a href="<?= htmlspecialchars($a->getUrl(), ENT_QUOTES, 'UTF-8') ?>" class="article-card">
                                        <div class="card-img-wrapper">
                                            <img src="/<?= htmlspecialchars($a->getCover()) ?>" alt="<?= htmlspecialchars($a->getTitle()) ?>">
                                        </div>
                                        <h3 class="card-title"><?= htmlspecialchars($a->getTitle()) ?></h3>
                                        <p class="card-excerpt"><?= parse_excerpt_html($a->getContent()) ?></p>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($withoutCover)): ?>
                        <div style="border-top: 2px solid #000; padding-top: 25px; margin-top: 10px;">
                            <h4 style="text-transform: uppercase; font-family: sans-serif; font-size: 0.9rem; letter-spacing: 2px; margin-bottom: 20px; color: #000; font-weight: 900;">Dépêches et analyses</h4>
                            <div class="briefs-vertical-container">
                                <div class="brief-grid">
                                    <?php foreach ($withoutCover as $a): ?>
                                        <a href="<?= htmlspecialchars($a->getUrl(), ENT_QUOTES, 'UTF-8') ?>" class="article-card brief-card">
                                            <h4 class="brief-title"><?= htmlspecialchars($a->getTitle()) ?></h4>
                                            <p class="card-excerpt brief-excerpt"><?= parse_excerpt_html($a->getContent()) ?></p>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>

        <?php endif; ?>
    </main>
</body>
</html>