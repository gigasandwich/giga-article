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
    <style>
        .news-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            font-family: 'Georgia', serif;
        }

        .date-section {
            border: 2px solid #000;
            margin-bottom: 4rem;
            background: #fff;
            box-shadow: 8px 8px 0px #000;
            padding: 20px;
            width: 100%;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Layout wrappers with scrolling */
        .covers-scroll-container {
            display: flex;
            overflow-x: auto;
            overflow-y: hidden;
            gap: 2rem;
            padding-bottom: 15px;
            scroll-snap-type: x mandatory;
            scrollbar-width: thin;
            scrollbar-color: #000 #eee;
        }
        .covers-scroll-container::-webkit-scrollbar { height: 8px; }
        .covers-scroll-container::-webkit-scrollbar-track { background: #eee; }
        .covers-scroll-container::-webkit-scrollbar-thumb { background: #000; border-radius: 4px; }

        .covers-scroll-container .article-card {
            flex: 0 0 400px; /* Fixed width for horizontal scrolling */
            scroll-snap-align: start;
        }

        /* Hero Feature for the very first article with cover */
        .featured-hero {
            display: grid !important;
            grid-template-columns: 1.5fr 1fr;
            gap: 30px;
            margin-bottom: 2rem;
            border-bottom: 3px solid #000;
            padding-bottom: 2rem;
            align-items: center;
        }
        .featured-hero .card-img-wrapper {
            height: 500px !important;
            margin-bottom: 0 !important;
        }
        .featured-hero .card-title {
            font-size: 3rem !important;
            margin-bottom: 20px;
        }
        .featured-hero .card-excerpt {
            font-size: 1.25rem !important;
            -webkit-line-clamp: 8 !important;
        }

        .briefs-vertical-container {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 15px;
            scrollbar-width: thin;
            scrollbar-color: #000 #eee;
        }
        .briefs-vertical-container::-webkit-scrollbar { width: 6px; }
        .briefs-vertical-container::-webkit-scrollbar-track { background: #eee; }
        .briefs-vertical-container::-webkit-scrollbar-thumb { background: #000; }

        .brief-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .date-header {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 5px 20px;
            font-family: Arial, sans-serif;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 25px;
            margin-top: -40px;
            margin-left: -20px;
        }

        /* Layout Combinations */
        .layout-grid {
            display: grid;
            gap: 2.5rem;
            margin-bottom: 2rem;
        }

        /* Adaptive Grid behavior */
        .adaptive-grid {
            display: grid;
            gap: 2.5rem;
        }

        /* 1 item: Full width */
        .count-1 { grid-template-columns: 1fr; }
        .count-1 .card-img-wrapper { height: 450px; }
        .count-1 .card-title { font-size: 2.5rem; }

        /* 2 items: 2 columns */
        .count-2 { grid-template-columns: 1fr 1fr; }
        .count-2 .card-img-wrapper { height: 350px; }

        /* 3 items: Large first, two smaller next to it */
        .count-3 { 
            grid-template-columns: 1.5fr 1fr;
            grid-template-rows: auto auto;
        }
        .count-3 .article-card:first-child { 
            grid-row: span 2; 
        }
        .count-3 .article-card:first-child .card-img-wrapper { height: 450px; }

        /* 4 items: 2x2 grid */
        .count-4 { grid-template-columns: 1fr 1fr; }

        /* More than 4: 3 columns */
        .count-default { grid-template-columns: repeat(3, 1fr); }

        /* Full width featured */
        .featured-row {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1.5fr 1fr;
            gap: 2rem;
            border-bottom: 1px solid #ddd;
            padding-bottom: 2rem;
            align-items: start;
        }

        /* Article Styles */
        .article-card {
            text-decoration: none;
            color: inherit;
            display: block;
            transition: opacity 0.2s;
        }
        .article-card:hover {
            opacity: 0.8;
        }

        .card-img-wrapper {
            width: 100%;
            height: 250px;
            overflow: hidden;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }
        .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.6rem;
            line-height: 1.2;
            margin-bottom: 10px;
            font-weight: 800;
        }

        .card-excerpt {
            font-size: 1.05rem;
            line-height: 1.5;
            color: #444;
            display: -webkit-box;
            -webkit-line-clamp: 4;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-align: justify;
        }

        /* Brief (Without cover) */
        .brief-card {
            border-left: 3px solid #000;
            padding-left: 15px;
            margin-bottom: 10px;
        }
        .brief-title {
            font-size: 1.2rem;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .brief-excerpt {
            font-size: 0.95rem;
            -webkit-line-clamp: 3;
        }

        @media (max-width: 900px) {
            .featured-row, .grid-3, .layout-mix {
                grid-template-columns: 1fr;
            }
        }
    </style>
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