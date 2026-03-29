<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    
    <script src="https://cdn.tiny.cloud/1/o9hrg0a9nx5b8gypfnqerbmac9utp40qhb4ttvgueyf1revd/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>

</head>
<body>
    <h1>Creation d'article</h1>

    <div>
        <form action="">
            <div>
                <label for="title">Titre</label>
                <input type="text" name="title" id="title">
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
                    // Your account includes a free trial of TinyMCE premium features
                    // Try the most popular premium features until Apr 11, 2026:
                    // 'checklist', 'mediaembed', 'casechange', 'formatpainter', 'pageembed', 'a11ychecker', 'tinymcespellchecker', 'permanentpen', 'powerpaste', 'advtable', 'advcode', 'advtemplate', 'ai', 'uploadcare', 'mentions', 'tinycomments', 'tableofcontents', 'footnotes', 'mergetags', 'autocorrect', 'typography', 'inlinecss', 'markdown','importword', 'exportword', 'exportpdf'
                ],
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck typography uploadcare | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat',
                // tinycomments_mode: 'embedded',
                // tinycomments_author: 'Author name',
                // mergetags_list: [
                //     { value: 'First.Name', title: 'First Name' },
                //     { value: 'Email', title: 'Email' },
                // ],
                // ai_request: (request, respondWith) => respondWith.string(() => Promise.reject('See docs to implement AI Assistant')),
                // uploadcare_public_key: 'f3720f25f8e5b4607db2',
            });

            const submitButton = document.getElementById("submit-button");
            submitButton.addEventListener("click", function(event) {
                event.preventDefault();

                const content = tinymce.get("content").getContent();
                console.log(content);
            });
        });
    </script>
</body>
</html>