<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../db/Connection.php';
require_once __DIR__ . '/../model/Article.php';

use Faker\Factory;

/**
 * Seeder to generate 50 articles (20 with cover, 30 without)
 * All articles include French content with images referencing 'uploads/'
 */

// 1. Initialize Faker in French
$faker = Factory::create('fr_FR');

// 2. Database connection
$pdo = connection();
if (!$pdo) {
    die("Database connection failed\n");
}

// 3. Configuration
$totalArticles = 50;
$withCoverCount = 20;
$withoutCoverCount = 30;

echo "Starting seeding of $totalArticles articles...\n";

// Function to generate a realistic HTML article body in French
function generateFrenchContent($faker) {
    $html = "";
    
    // Number of blocks (paragraphs/elements)
    $numBlocks = rand(4, 7);
    
    for ($index = 0; $index < $numBlocks; $index++) {
        // Randomly add a heading
        if ($index > 0 && rand(0, 3) === 0) {
            $html .= "<h3>" . $faker->realText(50) . "</h3>";
        }

        // Generate a paragraph using realText for better flow
        $html .= "<p>" . $faker->realText(rand(300, 800)) . "</p>";
        
        // Add an image every now and then
        if (rand(0, 2) === 1) {
            $imgName = "seeded_image_" . uniqid() . ".jpg";
            $alt = $faker->realText(40);
            $html .= "<figure><img src=\"uploads/$imgName\" alt=\"$alt\"><figcaption>$alt</figcaption></figure>";
        }
    }
    
    return $html;
}

// 4. Generate articles
for ($i = 0; $i < $totalArticles; $i++) {
    $hasCover = ($i < $withCoverCount);
    
    // Use realText for the title to make it sound like a real news headline
    $title = $faker->realText(rand(40, 70));
    // Remove the trailing period that realText often adds for titles
    $title = rtrim($title, ".");
    
    // Use a random date within the last year
    $createdAt = $faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d H:i:s');
    
    $coverPath = null;
    if ($hasCover) {
        $coverName = "seeded_cover_" . uniqid() . ".jpg";
        $coverPath = "uploads/" . $coverName;
    }
    
    $content = generateFrenchContent($faker);
    
    try {
        // We use ID 1 as a placeholder as per Article class requirement, 
        // saveArticle will update it with lastInsertId
        $article = new Article(1, $title, '', $coverPath, $content, $createdAt);
        
        // Save to DB (this also handles archiving via the model)
        $article->saveArticle($pdo);
        
        // Generate and save URL
        $article->createUrl();
        $article->saveUrl($pdo);
        
        // Note: savePictures parses content to find img tags and saves to 'picture' table
        // We pass '../..' as level because the script is in back/seeders/
        $article->savePictures($pdo, '../../');
        
        echo "Article " . ($i + 1) . "/" . $totalArticles . " created: " . $title . "\n";
    } catch (Exception $e) {
        echo "Error creating article " . ($i + 1) . ": " . $e->getMessage() . "\n";
    }
}

echo "\nSeeding completed successfully!\n";
echo "Note: Image files were not physically created in /uploads, only referenced in the database.\n";
