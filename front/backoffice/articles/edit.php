<?php
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

        <section class="edit-form-section">
            <h1>Modifier l'article : <?= htmlspecialchars($article->getTitle()) ?></h1>

            <form action="/back/controller/ArticleUpdateController.php" method="POST" enctype="multipart/form-data" id="article-form">
                <div id="message-container"></div>
                <input type="hidden" name="id" value="<?= $article->getId() ?>">
                <div class="article-header">
                    <div id="cover" onclick="document.getElementById('cover-file').click()" style="background-image: url('/<?= htmlspecialchars($article->getCover()) ?>'); background-size: cover; background-position: center;">
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
                    <button type="submit" id="submit-button">Enregistrer les modifications</button>
                    <a href="/backoffice" class="btn-cancel">Annuler</a>
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
            height: calc(100vh - 100px); /* Fill screen minus header */
            box-sizing: border-box;
            overflow: hidden; /* Prevent master scroll */
            font-family: 'Georgia', serif;
        }

        .version-sidebar {
            flex: 0 0 300px;
            border: var(--neo-border-width) solid var(--neo-black);
            background: var(--neo-white);
            box-shadow: var(--neo-shadow);
            display: flex;
            flex-direction: column;
            padding: 20px;
            height: 100%;
            box-sizing: border-box;
        }

        .version-sidebar h2 {
            text-transform: uppercase;
            font-weight: 900;
            font-size: 1.2rem;
            margin-top: 0;
            border-bottom: 3px solid var(--neo-black);
            padding-bottom: 10px;
            letter-spacing: 1px;
        }

        .version-timeline {
            flex: 1;
            overflow-y: auto;
            margin-top: 20px;
            padding-right: 10px;
            padding-left: 20px;
            position: relative;
        }

        .version-timeline::-webkit-scrollbar { width: 8px; }
        .version-timeline::-webkit-scrollbar-track { background: #eee; }
        .version-timeline::-webkit-scrollbar-thumb { background: var(--neo-black); }

        .version-timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            bottom: 5px;
            width: 2px;
            background: var(--neo-black);
        }

        .version-node {
            position: relative;
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid transparent;
            transition: var(--neo-transition);
        }

        .version-node:hover {
            background: #f0f0f0;
            border: 1px solid var(--neo-black);
        }

        .node-marker {
            position: absolute;
            left: -24px;
            top: 15px;
            width: 10px;
            height: 10px;
            background: var(--neo-blue);
            border: 2px solid var(--neo-white);
            box-shadow: 0 0 0 2px var(--neo-black);
        }

        .v-label {
            font-weight: 900;
            color: var(--neo-black);
            font-size: 0.85rem;
            text-transform: uppercase;
        }

        .v-status {
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 900;
            padding: 1px 6px;
            background: var(--neo-black);
            color: var(--neo-white);
            display: inline-block;
            margin-bottom: 4px;
        }

        .version-node.is-deleted .v-status {
            background: var(--neo-red);
        }

        .version-node.is-deleted .node-marker {
            background: var(--neo-red);
        }

        .edit-form-section {
            flex: 1;
            min-width: 0;
            background: var(--neo-white);
            border: var(--neo-border-width) solid var(--neo-black);
            box-shadow: var(--neo-shadow);
            padding: 30px;
            display: flex;
            flex-direction: column;
            height: 100%;
            box-sizing: border-box;
        }

        .edit-form-section h1 {
            font-size: 2rem;
            text-transform: uppercase;
            font-weight: 950;
            margin: 0 0 25px 0;
            text-align: left;
            border-bottom: 4px solid var(--neo-black);
            padding-bottom: 10px;
        }

        #article-form {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            gap: 20px;
        }

        .article-header {
            display: flex;
            gap: 20px;
            flex-shrink: 0;
        }

        #cover {
            width: 240px;
            height: 160px;
            background-color: #eee;
            border: var(--neo-border-width) solid var(--neo-black);
            box-shadow: 4px 4px 0px var(--neo-black);
            cursor: pointer;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            flex-shrink: 0;
            transition: var(--neo-transition);
        }

        #cover:hover {
            transform: translate(-2px, -2px);
            box-shadow: 6px 6px 0px var(--neo-black);
        }

        .placeholder {
            font-weight: 950;
            text-transform: uppercase;
            font-size: 0.8rem;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: rgba(0, 0, 0, 0.4); /* Dark overlay */
            color: var(--neo-white); /* High contrast text */
            text-shadow: 2px 2px 0px var(--neo-black); /* Brutalist text shadow */
            width: 100%;
            height: 100%;
            justify-content: center;
            align-items: center;
            box-sizing: border-box;
            line-height: 1.2;
            text-align: center;
            transition: var(--neo-transition);
        }

        #cover:hover .placeholder {
            background: rgba(0, 0, 0, 0.6);
        }

        .plus-icon { 
            font-size: 2rem;
            margin-bottom: 2px;
        }

        .header-inputs {
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .header-inputs label, .content-wrapper label {
            display: block;
            text-transform: uppercase;
            font-weight: 900;
            font-size: 0.8rem;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }

        .header-inputs input {
            width: 100%;
            padding: 10px;
            border: var(--neo-border-width) solid var(--neo-black);
            font-family: inherit;
            font-size: 1rem;
            box-sizing: border-box;
        }

        .content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 0; /* Important for flex child with overflow */
        }

        .tox-tinymce {
            border: var(--neo-border-width) solid var(--neo-black) !important;
            flex: 1 !important;
        }

        .form-actions {
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-shrink: 0;
        }

        #submit-button {
            padding: 12px 30px;
            background: var(--neo-green);
            color: var(--neo-black);
            border: var(--neo-border-width-thick) solid var(--neo-black);
            font-weight: 950;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: var(--neo-shadow);
            transition: var(--neo-transition);
        }

        #submit-button:hover {
            transform: translate(-2px, -2px);
            box-shadow: var(--neo-shadow-hover);
        }

        #submit-button:active {
            transform: translate(2px, 2px);
            box-shadow: 0px 0px 0px var(--neo-black);
        }

        .btn-cancel {
            color: var(--neo-black);
            text-decoration: none;
            text-transform: uppercase;
            font-weight: 900;
            font-size: 0.9rem;
            border-bottom: 2px solid transparent;
        }

        .btn-cancel:hover {
            border-bottom: 2px solid var(--neo-black);
        }

        @media (max-width: 1000px) {
            .edit-container {
                flex-direction: column;
                height: auto;
                overflow: visible;
            }
            .version-sidebar {
                flex: none;
                height: 300px;
            }
        }
    </style>

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
