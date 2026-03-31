<?php
// require_once __DIR__ . '/../../../back/util/minify.php';
require_once __DIR__ . '/../../../back/auth/check_auth.php';
require_once __DIR__ . "/../../../back/model/Article.php";
require_once __DIR__ . "/../../../back/db/Connection.php";

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: /backoffice");
    exit();
}

$article = Article::getById($pdo, (int)$id);

if (!$article) {
    header("Location: /backoffice");
    exit();
}

$history = Article::getHistory($pdo, $id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'article</title>

    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/article-dashboard.css">

    <script src="https://cdn.tiny.cloud/1/o9hrg0a9nx5b8gypfnqerbmac9utp40qhb4ttvgueyf1revd/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "../../component/header.php"; ?>
    <main class="article-dashboard">
        <aside class="dashboard-sidebar">
            <h2>Historique</h2>
            <div class="version-timeline">
                <?php if (!empty($history)): ?>
                    <?php foreach ($history as $v): ?>
                        <div class="version-node <?= isset($v['status']) && $v['status'] === 'Supprimé' ? 'is-deleted' : '' ?>" 
                             onclick="loadVersion(<?= $v['id'] ?>)" 
                             style="cursor: pointer;" 
                             title="Cliquer pour voir cette version">
                            <div class="node-marker"></div>
                            <div class="node-content">
                                <span class="v-label">v<?= $v['version'] ?></span>
                                <?php if (isset($v['status']) && $v['status'] !== 'Mis à jour'): ?>
                                    <span class="v-status"><?= htmlspecialchars($v['status']) ?></span>
                                <?php endif; ?>
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

        <section class="dashboard-form-section">
            <h1>Modifier l'article : <?= htmlspecialchars($article->getTitle()) ?></h1>

            <form action="/back/controller/ArticleUpdateController.php" method="POST" enctype="multipart/form-data" id="article-form" class="dashboard-form">
                <div id="message-container"></div>
                <input type="hidden" name="id" value="<?= $article->getId() ?>">
                <div class="article-header">
                    <div id="cover" onclick="document.getElementById('cover-file').click()" style="background-image: url('/<?= htmlspecialchars($article->getCover()) ?>');">
                        <input type="file" name="cover" id="cover-file" accept="image/*" style="display: none;">
                        <div class="placeholder">
                            <span class="plus-icon">+</span>
                        </div>
                    </div>

                    <div class="header-inputs">
                        <div>
                            <label for="title">Titre</label>
                            <input type="text" name="title" id="title" value="<?= htmlspecialchars($article->getTitle()) ?>" placeholder="Ex: Intensification de la guerre en Iran">
                        </div>

                        <div>
                            <label for="date">Date de création</label>
                            <input type="date" name="date" id="date" value="<?= date('Y-m-d', strtotime($article->getCreatedAt())) ?>">
                        </div>
                    </div>
                </div>

                <div class="content-wrapper">
                    <label for="content">Contenu</label>
                    <textarea name="content" id="content"><?= htmlspecialchars($article->getContent()) ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submit-button" class="btn-primary">Enregistrer les modifications</button>
                    <a href="/backoffice" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        const backUploadUrl = "/back/controller/PhotoUploadController.php";

        document.addEventListener("DOMContentLoaded", function() {
            const form = document.getElementById('article-form');
            const messageContainer = document.getElementById('message-container');

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Trigger TinyMCE save to textarea
                if (tinymce.get('content')) {
                    tinymce.get('content').save();
                }

                const formData = new FormData(this);
                const submitBtn = document.getElementById('submit-button');
                submitBtn.disabled = true;
                submitBtn.innerText = 'Enregistrement...';

                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        messageContainer.innerHTML = `<div style="padding: 15px; background: #d4edda; color: #155724; border-radius: 4px; margin-bottom: 20px; border: 1px solid #c3e6cb;">${result.message || 'Article mis à jour avec succès'}</div>`;
                        // Optional: reload to update history list
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        messageContainer.innerHTML = `<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">${result.error || 'Une erreur est survenue'}</div>`;
                    }
                })
                .catch(error => {
                    messageContainer.innerHTML = `<div style="padding: 15px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 20px; border: 1px solid #f5c6cb;">Erreur de connexion au serveur</div>`;
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Enregistrer les modifications';
                });
            });

            tinymce.init({
                selector: '#content',
                height: 450,
                language: 'fr_FR',
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
                }
            };
        });

        function loadVersion(versionId) {
            if (!confirm('Voulez-vous charger le contenu de cette version dans le formulaire ? (Les modifications non enregistrées seront perdues)')) {
                return;
            }

            fetch(`/back/controller/GetVersionDetailController.php?id=${versionId}`)
                .then(res => res.json())
                .then(result => {
                    if (result.success) {
                        const data = result.data;
                        
                        // Remplir les champs
                        document.getElementById('title').value = data.title;
                        document.getElementById('date').value = data.date;
                        
                        // TinyMCE
                        if (tinymce.get('content')) {
                            tinymce.get('content').setContent(data.content);
                        }
                        
                        // Image de couverture
                        const coverDiv = document.getElementById('cover');
                        if (data.cover) {
                            coverDiv.style.backgroundImage = `url('/${data.cover}')`;
                            coverDiv.style.backgroundSize = 'cover';
                        } else {
                            coverDiv.style.backgroundImage = 'none';
                        }
                        
                        // Message informatif
                        const messageContainer = document.getElementById('message-container');
                        messageContainer.innerHTML = `<div style="padding: 15px; background: #e7f5ff; color: #1971c2; border-radius: 4px; margin-bottom: 20px; border: 1px solid #a5d8ff;">Version chargée. N'oubliez pas d'enregistrer pour valider ces changements.</div>`;
                        
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    } else {
                        alert('Erreur lors du chargement : ' + result.error);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Erreur de connexion');
                });
        }
    </script>
</body>
</html>
