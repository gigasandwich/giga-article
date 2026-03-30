<?php
require_once "../../back/model/Article.php";
require_once "../../back/db/Connection.php";

$article_id = $_GET["article_id"];
$article = Article::getById($pdo, $article_id);

print_r($article);