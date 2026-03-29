<?php
class Article {
    private $id = null;
    private $title = null;
    private $url = null;
    private $content = null;
    private $date = null;

    public function __construct($id, $title, $url, $content, $date) {
        try {
            $this->setId($id);
            $this->setTitle($title);
            $this->setUrl($url);
            $this->setContent($content);
            $this->setDate($date);
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

    public function getDate() {
        return $this->date;
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

    public function setContent($content) {
        if(!is_string($content)) {
            throw new InvalidArgumentException("Content must be a string.");
        }
        $this->content = $content;
    }

    public function setDate($date) {
        if(!is_string($date)) {
            throw new InvalidArgumentException("Date must be a string.");
        }
        $this->date = $date;
    }

}