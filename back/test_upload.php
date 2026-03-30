<?php
// On inclut le fichier qui contient notre fonction d'upload
require_once 'Upload.php';

// On verifie si le formulaire a ete soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. On recupere les donnees textuelles (titre et date)
    $titreArticle = $_POST['title'];
    $dateCreation = $_POST['date'];
    
    // 2. On recupere le fichier envoye
    $fichierImage = $_FILES['cover'];
    
    // 3. On appelle notre fonction uploadImages !
    // On doit remonter d'un dossier (..) pour acceder au dossier 'uploads' a la racine
    $resultat = uploadImages($fichierImage, '..', $titreArticle, $dateCreation);
    
    // 4. On affiche le resultat
    if ($resultat['success'] === true) {
        echo "<p style='color: green;'>Super ! L'image a ete sauvegardee ici : " . $resultat['location'] . "</p>";
    } else {
        echo "<p style='color: red;'>Erreur : " . $resultat['message'] . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test de l'Upload</title>
</head>
<body>
    <h2>Creation d'Article</h2>
    
    <!-- L'attribut enctype="multipart/form-data" est OBLIGATOIRE pour uploader des fichiers ! -->
    <form action="test_upload.php" method="POST" enctype="multipart/form-data">
        
        <div>
            <label>Titre de l'article :</label><br>
            <input type="text" name="title" required>
        </div>
        <br>
        
        <div>
            <label>Date de creation :</label><br>
            <input type="date" name="date" required>
        </div>
        <br>
        
        <div>
            <label>Image de couverture :</label><br>
            <!-- L'attribut accept permet de filtrer visuellement les fichiers sur l'ordi de l'utilisateur -->
            <input type="file" name="cover" accept="image/png, image/jpeg, image/jpg, image/webp" required>
        </div>
        <br>
        
        <button type="submit">Creer l'article et Uploader</button>

    </form>
</body>
</html>
