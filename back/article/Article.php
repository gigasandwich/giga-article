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
        if(!is_string($url) || empty($url)) {
            throw new InvalidArgumentException("URL must be a non-empty string.");
        }
        $this->url = $url;
    }
    
    public function setCover($cover) {
        if(!is_string($cover)) {
            throw new InvalidArgumentException("Cover must be a string.");
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

    // Fonction
    public function saveArticle(PDO $pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO article (title, url, cover, content, created_at) VALUES (:title, :url, :cover, :content, :created_at)");
            $stmt->bindValue(':title', $this->getTitle());
            $stmt->bindValue(':url', $this->getUrl());
            $stmt->bindValue(':cover', $this->getCover());
            $stmt->bindValue(':content', $this->getContent());
            $stmt->bindValue(':created_at', $this->getCreatedAt());
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error saving article: " . $e->getMessage();
        }
    }
}