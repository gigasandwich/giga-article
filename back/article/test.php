<?php
require_once 'Article.php';

try {
    $article = new Article(1, "Sample Title", "http://example.com", "cover.jpg", "This is the content of the article.", "2024-06-01");
    echo "Article created successfully:\n";
    echo "ID: " . $article->getId() . "\n";
    echo "Title: " . $article->getTitle() . "\n";
    echo "URL: " . $article->getUrl() . "\n";
    echo "Cover: " . $article->getCover() . "\n";
    echo "Content: " . $article->getContent() . "\n";
    echo "Created At: " . $article->getCreatedAt() . "\n";
} catch (InvalidArgumentException $e) {
    echo "Error creating article: " . $e->getMessage();
}