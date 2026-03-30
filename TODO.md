# Scenario-1: Creation arcticles: (cree BO et Read FO)
## Affichage *Yvan*
- Creation formulaires:
    - date flexible
    - Titre arcticles
    - Body articles textarea
        - Afaka miselect text ho formatena (TinyDocs)
        - Insertion photo (tiny docs)
            - Upload: overidena ny an'ny tiny Docs (avadika input type file ilay type txt)
- Read articles
    - Listes des articles
## Metier *Steeve*
- Creation article
    - Creation classe article
        - id
        - date
        - titre
        - photoCouverture
        - contenu
        - url (a generer dans la classe)
        - Fonctions:
            1. [ok]Article creationArcticle()
                - [ok]Controle donnee
            2. [ok]Article persistenceArcticle()
                - persistePhoto()
            3. [ok]String genererUrl()
                - url ex: https://www.lemonde.fr/international/article/2026/03/29/sur-l-immigration-l-espagne-avance-a-contre-courant-de-ses-voisins-europeens_6675201_3210.html
            4. [ok]Article persisterUrl()
    - [ok]Upload photos

- Read articles
    - listAll()
## integration
- Miupload
- confirmation de creation article dans fo

# Scenario-2: Mijery details articles (FO)
## Affichage *Steeve*
- Mihezaka mirepresente an'ilay dessin d'ecran
## Metier Yvan
- extraireId avy amina url article()
- static Article getArticleById()
## integration
- Affichage des contenus

# Scenario-3: Filtrage des articles par dates default: now()
## Affichage *Yvan*
- Ajout input date
## Metier *Steeve*
- na listAll() na hafa no miasa
## Integration 
- affichage resultat

# Scenario-4: Login (geren'ny laravel) *Steeve/Yvan*
## Affichage
## Metier
## Integration

