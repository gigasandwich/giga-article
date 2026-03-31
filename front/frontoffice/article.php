<?php
require_once "../../back/model/Article.php";
require_once "../../back/db/Connection.php";

// 1. Verification de l'ID
$articleId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($articleId <= 0) {
    die("<h1>404 - Article introuvable</h1>");
}

// 2. Recuperation de l'article depuis la base de donnees
$article = Article::getById($pdo, $articleId);
if (!$article || $article->isDeleted()) {
    die("<h1>404 - Article introuvable</h1>");
}

// 3. Notre fonction de "nettoyage" et d'extraction
// On a ajoute $coverUrl en parametre pour savoir quelle image chercher
function getCleanArticleParts(string $html, string $coverUrl) {
    if (empty($html)) return ['chapeau' => '', 'body' => '', 'coverAlt' => ''];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    // On wrap le HTML pour que DOMDocument ne soit pas perdu (le utf-8 est crucial pour les accents)
    $wrapped = '<!doctype html><html><head><meta charset="utf-8"></head><body>' . $html . '</body></html>';
    $doc->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // A. On trouve et on SUPPRIME tous les H1 (Le titre est deja dans $article->getTitle())
    $h1s = $doc->getElementsByTagName('h1');
    while ($h1s->length > 0) {
        $h1 = $h1s->item(0);
        $h1->parentNode->removeChild($h1);
    }

    // B.1 Extraire l'attribut alt de l'image de couverture (et la supprimer du HTML)
    $images = $doc->getElementsByTagName('img');
    $coverAlt = '';
    
    // On va chercher le nom du fichier de couverture (en enlevant les dossiers devant)
    $coverFileName = basename($coverUrl); 
    
    // On doit parcourir le NodeList a l'envers quand on veut supprimer des elements, 
    // sinon les index de la liste changent et on en "rate" la moitie
    for ($i = $images->length - 1; $i >= 0; $i--) {
        $img = $images->item($i);
        $src = $img->getAttribute('src');
        
        // Si le src de l'image dans le texte contient le nom de notre cover...
        if (strpos($src, $coverFileName) !== false && $coverFileName !== '') {
            $coverAlt = $img->getAttribute('alt');
            $img->parentNode->removeChild($img);
        }
    }

    // B.2 Extraire le "Chapeau" (courte description)
    // On suppose que la description est dans le tout premier <h3> ou le tout premier paragraphe important
    $chapeau = '';
    $h3s = $doc->getElementsByTagName('h3');
    
    // On va parcourir les H3. S'il contient une image, on le skip ou on enleve l'image.
    // L'editeur WYSIWYG met parfois l'image DANS un h3.
    while ($h3s->length > 0) {
        $h3 = $h3s->item(0);
        // Si le h3 contient juste du texte, c'est forcement notre chapeau !
        if (trim(strip_tags($h3->textContent)) !== '') {
            if ($chapeau === '') { // On ne prend que le premier texte qu'on trouve
                $chapeau = $h3->textContent;
            }
        }
        // Quoi qu'il arrive, on supprime les h3 du debut du document pour nettoyer
        $h3->parentNode->removeChild($h3);
    }

    // C. On recupere le HTML propre restant (le "vrai" corps de l'article)
    $bodyHtml = '';
    $bodyNode = $doc->getElementsByTagName('body')->item(0);
    if ($bodyNode) {
        foreach ($bodyNode->childNodes as $child) {
            $bodyHtml .= $doc->saveHTML($child);
        }
    }

    return [
        'chapeau' => trim($chapeau),
        'body' => trim($bodyHtml),
        'coverAlt' => trim($coverAlt)
    ];
}

// On passe maintenant l'URL de la cover en 2eme parametre !
$parts = getCleanArticleParts($article->getContent(), $article->getCover()); 

// Formatage de la date à la francaise
$dateObj = new DateTime($article->getCreatedAt());
$dateFormatee = $dateObj->format('d/m/Y à H:i');

// Preparation de la description SEO (meta description)
// On prend le chapeau s'il existe, sinon on prend un bout du texte brut (max 150 caracteres)
$seoDescription = '';
if (!empty($parts['chapeau'])) {
    $seoDescription = strip_tags($parts['chapeau']);
} else {
    $seoDescription = strip_tags($parts['body']);
}
// Tronquer proprement à 150 caractères environ, pour la lisibilité de Google
if (mb_strlen($seoDescription) > 150) {
    $seoDescription = mb_substr($seoDescription, 0, 147) . '...';
}

// Preparation du chemin complet de l'image pour l'Open Graph
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$fullCoverUrl = $article->getCover() ? $protocol . "://" . $domain . "/" . ltrim($article->getCover(), '/') : '';
$fullArticleUrl = $protocol . "://" . $domain . $_SERVER['REQUEST_URI'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 1. Balises Meta basiques dynamiques -->
    <title><?= htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8') ?> - GigaArticle</title>
    <meta name="description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta name="author" content="La Rédaction GigaArticle">
    <meta name="robots" content="index, follow"> <!-- Autorise explicitement l'indexation -->

    <!-- 2. Balises Open Graph (Facebook, LinkedIn, Discord...) -->
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($fullArticleUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:site_name" content="GigaArticle">
    <meta property="article:published_time" content="<?= htmlspecialchars($dateObj->format('c'), ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($fullCoverUrl): ?>
    <meta property="og:image" content="<?= htmlspecialchars($fullCoverUrl, ENT_QUOTES, 'UTF-8') ?>">
    <meta property="og:image:alt" content="<?= htmlspecialchars(!empty($parts['coverAlt']) ? $parts['coverAlt'] : $article->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>

    <!-- 3. Balises Twitter Cards -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($article->getTitle(), ENT_QUOTES, 'UTF-8') ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($seoDescription, ENT_QUOTES, 'UTF-8') ?>">
    <?php if ($fullCoverUrl): ?>
    <meta name="twitter:image" content="<?= htmlspecialchars($fullCoverUrl, ENT_QUOTES, 'UTF-8') ?>">
    <?php endif; ?>
    
    <link rel="stylesheet" href="/public/assets/styles/style.css">
    
    <!-- On met un peu de CSS ici pour cibler precisement le style type "Le Monde" 
         Tu pourras le deplacer dans un article-detail.css plus tard -->
    <style>
        /* La structure de la page */
        .article-container {
            max-width: 800px; /* Le texte d'article n'est jamais trop large pour faciliter la lecture */
            margin: 0 auto;
            padding: 20px;
            font-family: Georgia, serif; /* Le style classique des journaux */
            color: #333;
        }

        /* L'en-tete de l'article (Titre, Date, Chapeau) */
        .article-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
        }

        .article-header h1 {
            font-size: 2.8rem;
            line-height: 1.2;
            margin-bottom: 15px;
            font-weight: bold;
            color: #000;
        }

        .article-meta {
            font-family: Arial, sans-serif;
            font-size: 0.9rem;
            color: #666;
            margin-bottom: 20px;
            text-transform: uppercase;
        }

        .article-chapeau {
            font-size: 1.3rem;
            font-weight: bold;
            line-height: 1.5;
            color: #444;
            max-width: 90%;
            margin: 0 auto;
            text-align: left;
        }

        /* L'image de couverture */
        .article-cover {
            width: 100%;
            margin-bottom: 30px;
            position: relative;
            margin-left: auto;
        }

        .article-cover img {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: cover; /* Assure que l'image ne se deforme pas */
            display: block;
        }

        .article-cover figcaption {
            font-family: Arial, sans-serif;
            font-size: 0.8rem;
            color: #777;
            text-align: right;
            padding-top: 5px;
        }

        /* Le corps du texte (paragraphes) */
        .article-body {
            font-size: 1.15rem;
            line-height: 1.6;
            text-align: justify; /* Typique des journaux */
        }

        .article-body p {
            margin-bottom: 20px;
        }

        /* Optionnel : Lettrine (la grosse premiere lettre) comme dans la presse papier */
        .article-body > p:first-of-type::first-letter {
            font-size: 3.5rem;
            float: left;
            line-height: 1;
            margin-right: 8px;
            margin-top: -5px;
            font-family: "Times New Roman", Times, serif;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Le header global du site pourrait aller ici -->

    <main class="article-container">
        
        <!-- EN-TETE DE L'ARTICLE -->
        <header class="article-header">
            <!-- 1. Le Titre -->
            <h1><?= htmlspecialchars($article->getTitle()) ?></h1>
            
            <!-- 2. Les Metadonnees (Date, Auteur) -->
            <div class="article-meta">
                Publié le <time datetime="<?= htmlspecialchars($article->getCreatedAt()) ?>"><?= $dateFormatee ?></time>
                <br> Par <strong>La Rédaction</strong>
            </div>

            <!-- 3. Le Chapeau (Extrait de notre fonction de nettoyage) -->
            <?php if (!empty($parts['chapeau'])): ?>
                <p class="article-chapeau">
                    <?= htmlspecialchars($parts['chapeau']) ?>
                </p>
            <?php endif; ?>
        </header>

        <!-- L'IMAGE DE COUVERTURE -->
        <?php if ($article->getCover()): ?>
        <figure class="article-cover">
            <!-- On s'assure d'ajouter le / devant uploads/ pour partir de la racine du site -->
            <img src="/<?= htmlspecialchars($article->getCover(), ENT_QUOTES, 'UTF-8') ?>" 
                 alt="<?= htmlspecialchars(!empty($parts['coverAlt']) ? $parts['coverAlt'] : "Couverture de l'article") ?>">
            <figcaption>
                <?php if (!empty($parts['coverAlt'])): ?>
                    <?= htmlspecialchars($parts['coverAlt']) ?>. © Giga News
                <?php else: ?>
                    Illustration liée à l'article. © Giga News
                <?php endif; ?>
            </figcaption>
        </figure>
        <?php endif; ?>

        <!-- LE CORPS DE L'ARTICLE -->
        <div class="article-body">
            <!-- Attention, ici on NE FAIT PAS de htmlspecialchars !
                 On veut afficher les balises <p>, <strong> que l'utilisateur a ecrites. 
                 On utilise notre $parts['body'] purifié de ses H1 -->
            <?= $parts['body'] ?>
        </div>

    </main>

</body>
</html>