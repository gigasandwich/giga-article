<?php
class Article {
    private $id = null;
    private $title = null;
    private $url = null;
    private $cover = null;
    private $content = null;
    private $createdAt = null;

    public function __construct($id, $title, $url, $cover, $content, $date) {
        try {
            $this->setId($id);
            $this->setTitle($title);
            $this->setUrl($url);
            $this->setCover($cover);
            $this->setContent($content);
            $this->setCreatedAt($date);
        } catch (InvalidArgumentException $e) {
            // Handle the exception as needed, e.g., log it or rethrow
            throw $e;
        }
    }

    // getters
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getUrl() {
        return $this->url;
    }

    public function getContent() {
        return $this->content;
    }

    public function getCover() {
        return $this->cover;
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }

    // setters
    public function setId($id) {
        if(!is_int($id) || $id <= 0) {
            throw new InvalidArgumentException("ID must be a positive integer.");
        }
        $this->id = $id;
    }

    public function setTitle($title) {
        if(!is_string($title) || empty($title)) {
            throw new InvalidArgumentException("Title must be a non-empty string.");
        }
        $this->title = $title;
    }

    public function setUrl($url) {
        if(!is_string($url)) {
            throw new InvalidArgumentException("URL must be a non-empty string.");
        }
        $this->url = $url;
    }
    
    public function setCover($cover) {
        if(!is_string($cover)) {
            if ($cover !== null) { // Allow null for cover
                throw new InvalidArgumentException("Cover must be a string or null.");
            }
        }
        $this->cover = $cover;
    }

    public function setContent($content) {
        if(!is_string($content)) {
            throw new InvalidArgumentException("Content must be a string.");
        }
        $this->content = $content;
    }

    public function setCreatedAt($createdAt) {
        if(!is_string($createdAt)) {
            throw new InvalidArgumentException("Created At must be a string.");
        }
        $this->createdAt = $createdAt;
    }

    // Fonctions
    public static function getAll(PDO $pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM article");
            $articles = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $articles[] = new Article($row['id'], $row['title'], $row['url'], $row['cover'], $row['content'], $row['created_at']);
            }
            return $articles;
        } catch (PDOException $e) {
            echo "Error fetching articles: " . $e->getMessage();
            return [];
        }
    }

    public function saveArticle(PDO $pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO article (title, url, cover, content, created_at) VALUES (:title, :url, :cover, :content, :created_at)");
            $stmt->bindValue(':title', $this->getTitle());
            $stmt->bindValue(':url', $this->getUrl());
            $stmt->bindValue(':cover', $this->getCover());
            $stmt->bindValue(':content', $this->getContent());
            $stmt->bindValue(':created_at', $this->getCreatedAt());
            $stmt->execute();
            $this->setId((int)$pdo->lastInsertId());
        } catch (PDOException $e) {
            echo "Error saving article: " . $e->getMessage();
        }
    }

    public function createUrl() {
        $date = str_replace('-', '/', $this->getCreatedAt());
        $title = str_replace(' ', '-', $this->getTitle());
        $url = '/article/'. $date . '/' . strtolower($title) . '_' . $this->getId() . '.html';
        $this->setUrl($url);
        return $url;
    }

    public function saveUrl(PDO $pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE article SET url = :url WHERE id = :id");
            $stmt->bindValue(':url', $this->getUrl());
            $stmt->bindValue(':id', $this->getId(), PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error saving URL: " . $e->getMessage();
        }
    } 

    public function getArticleImages($level) {
        // 1. Recreer la base du nom propre (titrePropre_datePropre)
        $titrePropre = preg_replace('/[^a-zA-Z0-9]/', '-', $this->getTitle());
        $datePropre = str_replace('-', '', $this->getCreatedAt());
        $nomDeBase = strtolower($titrePropre . '_' . $datePropre);

        // 2. Determiner le chemin du dossier uploads
        $dossierUploads = $level . '/uploads';

        // Tableau pour stocker les images trouvees
        $imagesTrouvees = [];

        // 3. Verifier si le dossier existe
        if (is_dir($dossierUploads)) {
            // Scanner tous les fichiers du dossier
            $fichiers = scandir($dossierUploads);

            foreach ($fichiers as $fichier) {
                // Ignorer les dossiers speciaux . et ..
                if ($fichier === '.' || $fichier === '..') {
                    continue;
                }

                // 4. Verifier si le nom du fichier COMMENCE PAR notre nom de base
                // ex: si nomDeBase est "Mon-Titre_20260330", ca matchera "Mon-Titre_20260330_64f1.jpg"
                if (strpos($fichier, $nomDeBase) === 0) {
                    // Ajouter le chemin relatif (propre pour le frontend) au tableau
                    $imagesTrouvees[] = 'uploads/' . $fichier;
                }
            }
        }

        return $imagesTrouvees;
    }

    public function savePictures(PDO $pdo, $level) {
        try {
            // 1. On recupere toutes les images liees a cet article
            $images = $this->getArticleImages($level);
            $inserted = 0;
            $skippedCover = 0;
            
            // 2. On prepare la requete d'insertion dans la table picture
            $stmt = $pdo->prepare("INSERT INTO picture (url, article_id) VALUES (:url, :article_id) ON CONFLICT (url) DO NOTHING");
            
            // 3. On boucle sur chaque image trouvee
            foreach ($images as $imageUrl) {
                // On s'assure qu'on n'insere pas l'image de couverture si elle y est deja
                if ($imageUrl !== $this->getCover()) {
                    $stmt->bindValue(':url', $imageUrl);
                    $stmt->bindValue(':article_id', $this->getId(), PDO::PARAM_INT);
                    $stmt->execute();
                    $inserted += $stmt->rowCount();
                } else {
                    $skippedCover++;
                }
            }

            return [
                'images_found' => $images,
                'inserted' => $inserted,
                'skipped_cover' => $skippedCover
            ];
        } catch (PDOException $e) {
            return [
                'images_found' => [],
                'inserted' => 0,
                'skipped_cover' => 0,
                'error' => $e->getMessage()
            ];
        }
    }
}