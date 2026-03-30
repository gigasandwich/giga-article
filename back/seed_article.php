<?php
require_once 'db/Connection.php';
require_once 'model/Article.php';

$pdo = connection();

// 1. Creation du repertoire uploads s'il n'existe pas
$uploadsDir = __DIR__ . '/../uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// 2. Definition des meta-donnees de notre article réaliste
$title = "La révolution de l'intelligence artificielle et du code en 2026";
$date = "2026-03-30 10:00:00";

// Formatage comme le ferait l'application pour les noms de fichiers
$titrePropre = strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $title));
// On recupere justeYYYYMMDD pour la date
$datePropre = str_replace('-', '', substr($date, 0, 10)); 

// 3. Recuperation des images existantes pour s'en servir de base
$existingFiles = array_diff(scandir($uploadsDir), ['.', '..']);
$realImages = [];
foreach ($existingFiles as $file) {
    if (pathinfo($file, PATHINFO_EXTENSION) === 'png' || pathinfo($file, PATHINFO_EXTENSION) === 'jpg') {
        $realImages[] = $uploadsDir . '/' . $file;
    }
}
if (empty($realImages)) {
    die("❌ Aucune image existante trouvée dans le dossier uploads pour générer des données de test réalistes.\n");
}

// On duplique des vraies images aléatoirement pour avoir du vrai contenu
$coverSource = $realImages[array_rand($realImages)];
$img1Source = $realImages[array_rand($realImages)];
$img2Source = $realImages[array_rand($realImages)];

// Generation des noms de fichiers des images pour l'article courant
$coverName = "cover-" . $titrePropre . "_" . $datePropre . "_" . uniqid() . ".png";
$img1Name = $titrePropre . "_" . $datePropre . "_" . uniqid() . ".png";
$img2Name = $titrePropre . "_" . $datePropre . "_" . uniqid() . ".png";

// On copie physiquement ces vraies images pour cet article
copy($coverSource, $uploadsDir . '/' . $coverName);
copy($img1Source, $uploadsDir . '/' . $img1Name);
copy($img2Source, $uploadsDir . '/' . $img2Name);

$coverUrl = 'uploads/' . $coverName;

// 4. Creation du contenu HTML simulant un editeur WYSIWYG
$content = <<<HTML
<h1>La révolution de l'intelligence artificielle et du code en 2026</h1>
<h3><img src="../../uploads/{$coverName}" alt="Un robot qui tape sur un clavier d'ordinateur holographique" width="600"></h3>
<h3>L'année 2026 marque un tournant décisif dans l'adoption mondiale des technologies d'Intelligence Artificielle. Le métier de développeur connait un bouleversement total grâce à l'émergence d'agents autonomes.</h3>
<p>Depuis les premières annonces tonitruantes du début de la décennie, le monde s'est préparé à l'inévitable. Aujourd'hui, l'IA ne se contente plus de simplement compléter des lignes de code, elle orchestre des pans entiers de l'architecture logicielle.</p>
<p>De nombreuses entreprises de la Tech ont vu leur productivité de développement multipliée par dix. Les développeurs se transforment désormais en "directeurs d'IA" (AI orchestrators), guidant des armées d'agents spécialisés.</p>
<p><img src="../../uploads/{$img1Name}" alt="Graphique illustrant la croissance fulgurante de la productivite logicielle" width="400"></p>
<p>Cependant, cette transition phénoménale n'est pas sans heurts. La société doit faire face à des défis de fond, incluant la sécurité des algorithmes, la maintenance du code auto-généré et la compréhension des gigantesques "bases de code noires" écrites par les machines.</p>
<p><img src="../../uploads/{$img2Name}" alt="Datacenter massif alimenté par des energies renouvelables" width="400"></p>
<p>En conclusion, l'humanité a franchi un nouveau cap. Le code informatique n'est plus seulement l'apanage des humains, c'est désormais une langue universelle co-écrite avec la machine.</p>
HTML;

try {
    // 5. Instanciation et Sauvegarde en Base de Donnees
    // Note: on utilise "2026-03-30" car c'est le format que ta fonction createUrl attend (YYYY-MM-DD)
    $article = new Article(1, $title, '', $coverUrl, $content, "2026-03-30");
    $article->saveArticle($pdo);
    $article->createUrl();
    $article->saveUrl($pdo);

    // 6. Sauvegarde des images dans la table 'picture'
    // Le "level" passé est '..' car on lance ce script depuis le dossier /back/
    $debug = $article->savePictures($pdo, '..');

    echo "✅ Article réaliste créé avec l'ID: " . $article->getId() . ".\n";
    echo "🔗 URL générée: " . $article->getUrl() . "\n";
    echo "📸 Images trouvées et insérées: " . $debug['inserted'] . " (Couverture exclue: " . $debug['skipped_cover'] . ")\n";

} catch (Exception $e) {
    echo "❌ Erreur durant la création: " . $e->getMessage() . "\n";
}
