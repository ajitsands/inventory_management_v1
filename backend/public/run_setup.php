<?php
// Secure temporary run_setup.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../core/Model.php';

try {
    $pdo = Model::getDB();
    echo "Database connected successfully.<br>";
    
    $setupSqlPath = __DIR__ . '/../database/database_setup.sql';
    if (!file_exists($setupSqlPath)) {
        die("Error: database_setup.sql not found at $setupSqlPath");
    }
    
    $sql = file_get_contents($setupSqlPath);
    $queries = preg_split("/;[\r\n]+/", $sql);
    
    echo "Running " . count($queries) . " queries...<br>";
    
    $successCount = 0;
    foreach ($queries as $index => $query) {
        $query = trim($query);
        if (!empty($query)) {
            try {
                $pdo->exec($query);
                $successCount++;
            } catch (\Exception $e) {
                echo "<strong style='color:red;'>Query #$index failed:</strong><br>";
                echo "<pre>" . htmlspecialchars($query) . "</pre><br>";
                echo "<strong>Error message:</strong> " . htmlspecialchars($e->getMessage()) . "<br><hr>";
                // Keep executing other queries or halt? Let's halt so they can see the first blocker
                die("Setup halted due to query failure.");
            }
        }
    }
    
    echo "<h2 style='color:green;'>Setup completed successfully! Run $successCount queries.</h2>";
} catch (\Exception $e) {
    die("Database Connection Error: " . $e->getMessage());
}
