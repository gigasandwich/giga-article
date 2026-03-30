<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <link rel="stylesheet" href="/public/assets/styles/style.css">
    <link rel="stylesheet" href="/public/assets/styles/create-article.css">

    <script src="https://cdn.tiny.cloud/1/o9hrg0a9nx5b8gypfnqerbmac9utp40qhb4ttvgueyf1revd/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

</head>
<body>
    <h1>Creation d'article</h1>

    <div>
        <form action="/back/controller/ArticleController.php" method="POST" enctype="multipart/form-data" id="article-form">
            <div id="cover">
                
            </div>
            <div>
                <label for="title">Titre</label>
                <input type="text" name="title" id="title" placeholder="Ex: Intensification de la guerre en Iran">
            </div>

            <div>
                <label for="date">Date de creation</label>
                <input type="date" name="date" id="date">
            </div>

            <div>
                <label for="content">Contenu</label>
                <textarea name="content" id="content"></textarea>
            </div>

            <button type="submit" id="submit-button">Creer l'article</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            tinymce.init({
                selector: '#content',
                height: 300,
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
                images_upload_url: '/back/dummy-upload.php',

                file_picker_types: 'image',

                file_picker_callback: (callback, value, meta) => {
                    const input = document.createElement('input');
                    input.type = 'file';
                    input.accept = 'image/*';

                    input.onchange = function () {
                        const file = this.files[0];

                        const formData = new FormData();
                        formData.append('file', file);

                        fetch('/back/dummy-upload.php', {
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

            const submitButton = document.getElementById("submit-button");

            const form = document.getElementById("article-form");
            form.addEventListener("submit", (event) => {
                event.preventDefault();

                const content = tinymce.get("content").getContent();
                console.log(content);

                const formData = new FormData(form);
                
                fetch("/back/controller/ArticleController.php", {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            });
        });
    </script>
</body>
</html>