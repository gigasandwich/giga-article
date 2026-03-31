<?php
session_start();
if (!isset($_SESSION['article'])) {
    $_SESSION['article'] = [
        'content_photos' => []
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/article-dashboard.css">

    <script src="https://cdn.tiny.cloud/1/o9hrg0a9nx5b8gypfnqerbmac9utp40qhb4ttvgueyf1revd/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

</head>
<body>
    <?php include "../../component/header.php"; ?>
    <main class="article-dashboard">
        <aside class="dashboard-sidebar">
            <h2>Informations</h2>
            <div class="sidebar-content">
                <p style="font-size: 0.9rem; line-height: 1.6; color: #444;">
                    Créez un nouvel article pour votre journal. <br><br>
                    N'oubliez pas d'ajouter une <strong>image de couverture</strong> percutante pour attirer vos lecteurs.
                </p>
                <div style="margin-top: 30px; border-top: 2px solid var(--neo-black); padding-top: 20px;">
                    <span style="display: block; font-weight: 900; text-transform: uppercase; font-size: 0.75rem; margin-bottom: 10px;">Conseils SEO</span>
                    <ul style="padding-left: 20px; font-size: 0.8rem; display: flex; flex-direction: column; gap: 8px;">
                        <li>Utilisez des mots-clés dans le titre.</li>
                        <li>Structurez avec des titres (H2, H3).</li>
                        <li>Ajoutez des descriptions aux images.</li>
                    </ul>
                </div>
            </div>
        </aside>

        <section class="dashboard-form-section">
            <h1>Création d'article</h1>

            <form action="/back/controller/ArticleCreationController.php" method="POST" enctype="multipart/form-data" id="article-form" class="dashboard-form">
                <div id="message-container"></div>
                
                <div class="article-header">
                    <div id="cover" onclick="document.getElementById('cover-file').click()">
                        <input type="file" name="cover" id="cover-file" accept="image/*" style="display: none;">
                        <div class="placeholder">
                            <span class="plus-icon">+</span>
                            <span>Photo de couverture</span>
                        </div>
                    </div>

                    <div class="header-inputs">
                        <div>
                            <label for="title">Titre</label>
                            <input type="text" name="title" id="title" placeholder="Ex: Intensification de la guerre en Iran">
                        </div>

                        <div>
                            <label for="date">Date de création</label>
                            <input type="date" name="date" id="date">
                        </div>
                    </div>
                </div>

                <div class="content-wrapper">
                    <label for="content">Contenu</label>
                    <textarea name="content" id="content"></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" id="submit-button" class="btn-primary">Créer l'article</button>
                    <a href="../index.php" class="btn-cancel">Annuler</a>
                </div>
            </form>
        </section>
    </main>

    <script>
        const backUploadUrl = "/back/controller/PhotoUploadController.php";

        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: '#content',
                height: 450,
                language: 'fr_FR',
                plugins: [
                    // Core editing features
                    'anchor', 'autolink', 'charmap', 'codesample', 'emoticons', 'link', 'lists', 'media', 'searchreplace', 'table', 'visualblocks', 'wordcount',
                    'image',
                    // Your account includes a free trial of TinyMCE premium features
                    // Try the most popular premium features until Apr 11, 2026:
                    // 'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown','importword', 'exportword', 'exportpdf'
                ],
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough image | media link table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                // tinycomments_mode: 'embedded',
                // tinycomments_author: 'Author name',
                // mergetags_list: [
                //     { value: 'First.Name', title: 'First Name' },
                //     { value: 'Email', title: 'Email' },
                // ],
                // ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
                // uploadcare_public_key: 'f3720f25f8e5b4607db2',

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
                            method: 'POST',
                            body: formData
                        })
                        .then(res => res.json())
                        .then(data => {
                            callback(data.location);
                        })
                        .catch(err => console.error(err));
                    };

                    input.click();
                }

            });

            // Default date: now
            const dateInput = document.getElementById('date');
            if (dateInput && !dateInput.value) {
                const today = new Date();
                const yyyy = today.getFullYear();
                const mm = String(today.getMonth() + 1).padStart(2, '0');
                const dd = String(today.getDate()).padStart(2, '0');
                dateInput.value = `${yyyy}-${mm}-${dd}`;
            }

            const coverInput = document.getElementById('cover-file');
            const coverDiv = document.getElementById('cover');

            coverInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const maxSize = 5 * 1024 * 1024; // 5 Mo
                    if (file.size > maxSize) {
                        messageContainer.innerHTML = `<div class="alert alert-error">Le fichier est trop volumineux (max 5 Mo).</div>`;
                        this.value = ''; // Reset input
                        return;
                    }
                    messageContainer.innerHTML = '';

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        coverDiv.style.backgroundImage = `url(${e.target.result})`;
                    }
                    reader.readAsDataURL(file);
                }
            });

            const form = document.getElementById("article-form");
            const messageContainer = document.getElementById("message-container");

            form.addEventListener("submit", (event) => {
                event.preventDefault();
                
                // Sync TinyMCE to textarea
                if (tinymce.get('content')) {
                    tinymce.get('content').save();
                }

                messageContainer.innerHTML = '';
                const submitBtn = document.getElementById('submit-button');
                submitBtn.disabled = true;
                submitBtn.innerText = 'Création...';

                const formData = new FormData(form);
                
                fetch("/back/controller/ArticleCreationController.php", {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageContainer.innerHTML = `<div class="alert alert-success">Article créé avec succès !</div>`;
                        
                        // Reset form
                        form.reset();
                        
                        // Clear TinyMCE
                        tinymce.get("content").setContent('');
                        
                        // Reset cover preview
                        coverDiv.style.backgroundImage = 'none';

                        // Restore default date
                        const today = new Date();
                        const yyyy = today.getFullYear();
                        const mm = String(today.getMonth() + 1).padStart(2, '0');
                        const dd = String(today.getDate()).padStart(2, '0');
                        dateInput.value = `${yyyy}-${mm}-${dd}`;
                    } else {
                        messageContainer.innerHTML = `<div class="alert alert-error">${data.error || 'Une erreur est survenue.'}</div>`;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    messageContainer.innerHTML = `<div class="alert alert-error">Erreur reseau ou serveur</div>`;
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerText = 'Créer l\'article';
                });
            });
        });
    </script>
</body>
</html>