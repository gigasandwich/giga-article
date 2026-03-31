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
$history = Article::getHistory($pdo, $id);
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
    <main class="edit-container">
        <aside class="version-sidebar">
            <h2>Historique</h2>
            <div class="version-timeline">
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $v): ?>
                        <div class="version-node">
                            <div class="node-marker"></div>
                            <div class="node-content">
                                <span class="v-label">v<?= $v['version'] ?></span>
                                <time class="v-date"><?= date('d/m/Y H:i', strtotime($v['modified_at'] ?? $v['created_at'])) ?></time>
                                <div class="v-title"><?= htmlspecialchars($v['title']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-history">Aucun historique disponible.</p>
                <?php endif; ?>
            </div>
        </aside>

        <section class="edit-form-section">
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

                <div class="form-actions">
                    <button type="submit" id="submit-button">Enregistrer les modifications</button>
                    <a href="../index.php" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </section>
    </main>

    <style>
        .edit-container {
            display: flex;
            gap: 40px;
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .version-sidebar {
            flex: 0 0 280px;
            border-right: 1px solid #eee;
            padding-right: 20px;
        }

        .edit-form-section {
            flex: 1;
            min-width: 0;
        }

        .version-timeline {
            position: relative;
            margin-top: 20px;
            padding-left: 20px;
        }

        .version-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background: #e0e0e0;
        }

        .version-node {
            position: relative;
            margin-bottom: 25px;
        }

        .node-marker {
            position: absolute;
            left: -24px;
            top: 6px;
            width: 10px;
            height: 10px;
            background: #007bff;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: 0 0 0 2px #007bff;
        }

        .node-content {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .v-label {
            font-weight: bold;
            color: #007bff;
            font-size: 0.85rem;
        }

        .v-date {
            color: #666;
            font-size: 0.8rem;
        }

        .v-title {
            font-size: 0.9rem;
            color: #333;
            line-height: 1.3;
            word-wrap: break-word;
        }

        .form-actions {
            margin-top: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-cancel {
            color: #666;
            text-decoration: none;
        }

        .btn-cancel:hover {
            text-decoration: underline;
        }

        .no-history {
            color: #999;
            font-style: italic;
        }

        @media (max-width: 900px) {
            .edit-container {
                flex-direction: column-reverse;
            }
            .version-sidebar {
                border-right: none;
                border-top: 1px solid #eee;
                padding-top: 20px;
                flex: none;
            }
        }
    </style>

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
