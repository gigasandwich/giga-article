<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../db/Connection.php';
require_once __DIR__ . '/../model/Article.php';

use Faker\Factory;

/**
 * Seeder to generate articles for at least 265 days.
 * Each day will have between 1 and 10 articles.
 */

// 1. Initialize Faker in French
$faker = Factory::create('fr_FR');

// 2. Database connection
$pdo = connection();
if (!$pdo) {
    die("Database connection failed\n");
}

// 3. Configuration
$daysToSeed = 100;
$maxArticlesPerDay = 10;
$withCoverProbability = 0.4; // 40% chance of having a cover

echo "Starting seeding of articles for $daysToSeed days...\n";

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

// 4. Generate articles with random day intervals
$currentDate = new DateTime();
// Go back ~300 days to allow for spacing
$currentDate->modify("-300 days");

for ($d = 0; $d < $daysToSeed; $d++) {
    // Randomize the interval (1 to 3 days between seeding days)
    $interval = rand(1, 3);
    $currentDate->modify("+$interval days");
    
    $numArticlesToday = rand(1, $maxArticlesPerDay);
    $dateStr = $currentDate->format('Y-m-d');
    
    echo "Day " . ($d + 1) . "/$daysToSeed: $dateStr - Creating $numArticlesToday articles...\n";
    
    for ($i = 0; $i < $numArticlesToday; $i++) {
        // Random time during the day
        $hour = rand(8, 20);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        $createdAt = $dateStr . " " . sprintf('%02d:%02d:%02d', $hour, $minute, $second);
        
        $title = $faker->realText(rand(40, 70));
        $title = rtrim($title, ".");
        
        $coverPath = null;
        if (rand(1, 100) <= ($withCoverProbability * 100)) {
            $coverName = "seeded_cover_" . uniqid() . ".jpg";
            $coverPath = "uploads/" . $coverName;
        }
        
        $content = generateFrenchContent($faker);
        
        try {
            // Using ID 1 as placeholder, saveArticle will update it
            $article = new Article(1, $title, '', $coverPath, $content, $createdAt);
            
            // Save to DB
            $article->saveArticle($pdo);
            
            // Generate and save URL
            $article->createUrl();
            $article->saveUrl($pdo);
            
            // Note: savePictures parses content to find img tags and saves to 'picture' table
            // We pass '../..' as level because the script is in back/seeders/
            $article->savePictures($pdo, '../../');
        } catch (Exception $e) {
            echo "  Error creating article: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nSeeding completed successfully!\n";
echo "Note: Image files were not physically created in /uploads, only referenced in the database.\n";
