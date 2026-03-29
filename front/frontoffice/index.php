<?php
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

$articles = [];
for ($i = 1; $i <= 5; $i++) {
    $imageUrl = "https://picsum.photos/seed/article{$i}/440/264";
    $inlineImage = "https://picsum.photos/seed/inline{$i}/120/80";
    $htmlExcerpt = "
        <p>Lorem ipsum <strong>dolor sit amet</strong>, consectetur adipisicing elit. <img src='{$inlineImage}' alt='This is from alt tag' /></p>
        <p>Quisquam, quidem. Voluptas, voluptate.</p>
        <p>Ratataatatatataaaaaaaaaaaaaaaaaaaaaaaaaa Ratataatatatataaaaaaaaaaaaaaaaaaaaaaaaaa Ratataatatatataaaaaaaaaaaaaaaaaaaaaaaaaa Ratataatatatataaaaaaaaaaaaaaaaaaaaaaaaaa</p>
    ";

    $articles[] = [
        'title' => "Article $i",
        'date' => date('Y-m-d', strtotime("-{$i} days")),
        'image' => $imageUrl,
        'excerpt' => $htmlExcerpt
    ];
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
    <main class="articles-list">
        <h1>Liste des articles</h1>
        <ul>
            <?php foreach ($articles as $a): ?>
                <li>
                    <article class="news-article">
                        <div class="thumb">
                            <img src="<?= htmlspecialchars($a['image'], ENT_QUOTES, 'UTF-8') ?>" alt="Thumbnail for <?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="news-content">
                            <h2 class="news-title"><?= htmlspecialchars($a['title'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <time class="news-date" datetime="<?= htmlspecialchars($a['date'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($a['date'], ENT_QUOTES, 'UTF-8') ?></time>
                            <div class="news-body"><?= parse_excerpt_html($a['excerpt']) ?></div>
                        </div>
                    </article>
                </li>
            <?php endforeach; ?>
        </ul>
    </main>
</body>
</html>