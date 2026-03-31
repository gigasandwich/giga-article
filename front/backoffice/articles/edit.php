<?php
require_once __DIR__ . '/../../../back/auth/check_auth.php';
require_once __DIR__ . "/../../../back/model/Article.php";
require_once __DIR__ . "/../../../back/db/Connection.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: ../index.php");
    exit();
}

// Simple fetch (assuming static getById exists or similar, if not we use PDO)
$stmt = $pdo->prepare("SELECT * FROM article WHERE id = ?");
$stmt->execute([$id]);
$articleData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$articleData) {
    header("Location: ../index.php");
    exit();
}

$article = new Article($articleData['id'], $articleData['title'], $articleData['url'], $articleData['cover'], $articleData['content'], $articleData['created_at']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>

    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/create-article.css">

    <script src="https://cdn.tiny.cloud/1/o9hrg0a9nx5b8gypfnqerbmac9utp40qhb4ttvgueyf1revd/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "../../component/header.php"; ?>
    <main style="padding: 20px; max-width: 1200px; margin: 0 auto;">
        <h1>Modifier l'article : <?= htmlspecialchars($article->getTitle()) ?></h1>

        <div id="message-container"></div>
        <form action="/back/controller/ArticleUpdateController.php" method="POST" enctype="multipart/form-data" id="article-form">
            <input type="hidden" name="id" value="<?= $article->getId() ?>">
            <div class="article-header">
                <div id="cover" onclick="document.getElementById('cover-file').click()" style="background-image: url('/<?= htmlspecialchars($article->getCover()) ?>'); background-size: cover; background-position: center;">
                    <input type="file" name="cover" id="cover-file" accept="image/*" style="display: none;">
                    <div class="placeholder" style="<?= $article->getCover() ? 'display:none' : '' ?>">
                        <span class="plus-icon">+</span>
                        <span>Modifier la photo de couverture</span>
                    </div>
                </div>

                <div class="header-inputs">
                    <div>
                        <label for="title">Titre</label>
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($article->getTitle()) ?>" placeholder="Ex: Intensification de la guerre en Iran">
                    </div>

                    <div>
                        <label for="date">Date de creation</label>
                        <input type="date" name="date" id="date" value="<?= date('Y-m-d', strtotime($article->getCreatedAt())) ?>">
                    </div>
                </div>
            </div>

            <div>
                <label for="content">Contenu</label>
                <textarea name="content" id="content"><?= htmlspecialchars($article->getContent()) ?></textarea>
            </div>

            <button type="submit" id="submit-button">Enregistrer les modifications</button>
            <a href="../index.php" style="margin-left: 10px; color: #666; text-decoration: none;">Annuler</a>
        </form>
    </main>

    <script>
        const backUploadUrl = "/back/controller/PhotoUploadController.php";

        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: '#content',
                height: 450,
                plugins: [
                    'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount', 'image',
                ],
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough image | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                automatic_uploads: true,
                images_upload_url: backUploadUrl,
                file_picker_types: 'image',
                file_picker_callback: (callback, value, meta) => {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';
                    input.onchange = function () {
                        const file = this.files[0];
                        const formData = new FormData();
                        formData.append('file', file);
                        formData.append('title', document.getElementById('title').value);
                        formData.append('date', document.getElementById('date').value);

                        fetch(backUploadUrl, {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.location) {
                                callback(result.location, { alt: file.name });
                            }
                        });
                    };
                    input.click();
                }
            });

            // Preview cover image
            document.getElementById('cover-file').onchange = function(evt) {
                const [file] = this.files;
                if (file) {
                    const coverDiv = document.getElementById('cover');
                    coverDiv.style.backgroundImage = `url(${URL.createObjectURL(file)})`;
                    coverDiv.style.backgroundSize = 'cover';
                    coverDiv.querySelector('.placeholder').style.display = 'none';
                }
            };
        });
    </script>
</body>
</html>
