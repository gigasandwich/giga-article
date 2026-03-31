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

$articles = Article::getAll($pdo, $dateStart, $dateEnd);

// Group articles by date (Y-m-d) and sort by date descending
$groupedByDate = [];
foreach ($articles as $a) {
    $dateKey = (new DateTime($a->getCreatedAt()))->format('Y-m-d');
    if (!isset($groupedByDate[$dateKey])) {
        $groupedByDate[$dateKey] = [];
    }
    $groupedByDate[$dateKey][] = $a;
}
krsort($groupedByDate);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backoffice - Articles</title>
    
    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/backoffice-list.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <?php include "../component/header.php"; ?>
    <main class="backoffice-container">
        <div class="backoffice-header">
            <h1>Journal Admin</h1>
            <a href="/backoffice/articles/create" class="btn-new-article">Nouvel Article</a>
        </div>

        <?php if (empty($groupedByDate)): ?>
            <div class="empty-state">
                <img src="/public/assets/img/empty.svg" alt="Aucun article" style="max-width: 300px; display: block; margin: 2rem auto;">
                <p style="text-align: center; font-size: 1.2rem; color: #666;">Aucun article trouvé selon vos criteres</p>
            </div>
        <?php else: ?>
            <div class="scroll-container">
            <?php foreach ($groupedByDate as $date => $items): ?>
                <section class="date-section">
                    <div class="date-header">
                        <?= date('d F Y', strtotime($date)) ?>
                    </div>
                    
                    <ul class="admin-article-list">
                        <?php foreach ($items as $a): ?>
                            <li class="admin-article-item <?= $a->isDeleted() ? 'deleted' : '' ?>">
                                <?php if ($a->isDeleted()): ?>
                                    <span class="deleted-badge">Supprimé</span>
                                <?php endif; ?>
                                
                                <?php if ($a->getCover()): ?>
                                    <div class="thumb">
                                        <img src="/<?= htmlspecialchars($a->getCover(), ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
                                    </div>
                                <?php endif; ?>
                                
                                <div class="news-content">
                                    <span class="news-date"><?= date('H:i', strtotime($a->getCreatedAt())) ?></span>
                                    <h2 class="news-title"><?= htmlspecialchars($a->getTitle(), ENT_QUOTES, 'UTF-8') ?></h2>
                                    <div class="news-body"><?= parse_excerpt_html($a->getContent()) ?></div>
                                    
                                    <div class="admin-actions">
                                        <?php if ($a->isDeleted()): ?>
                                            <form action="/backoffice/articles/restore" method="POST" style="flex:1">
                                                <input type="hidden" name="id" value="<?= $a->getId() ?>">
                                                <button type="submit" class="btn btn-restore" title="Restaurer">
                                                    <i class="fa-solid fa-rotate-left"></i> RESTAURER
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <a href="/backoffice/articles/edit/<?= $a->getId() ?>" class="btn btn-edit" title="Modifier">
                                                <i class="fa-solid fa-pen-to-square"></i> MODIFIER
                                            </a>
                                            <form action="/backoffice/articles/delete" method="POST" onsubmit="return confirm('Supprimer cet article ?')" style="flex:1">
                                                <input type="hidden" name="id" value="<?= $a->getId() ?>">
                                                <button type="submit" class="btn btn-delete" title="Supprimer">
                                                    <i class="fa-solid fa-trash"></i> SUPPRIMER
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
