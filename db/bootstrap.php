<?php
// Bootstrap script to create the database schema
// Run with: php db/bootstrap.php

$host = '127.0.0.1';
$port = 33060;
$user = 'root';
$pass = '';

try {
    // Try connecting without database first
    $mysqli = new mysqli($host, $user, $pass, '', $port);

    if ($mysqli->connect_error) {
        die('Connection failed: ' . $mysqli->connect_error);
    }

    // Read and execute the schema
    $schema = file_get_contents(__DIR__ . '/schema.sql');

    // Split by semicolons and execute each statement
    $statements = explode(';', $schema);

    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            if (!$mysqli->query($statement)) {
                echo "Error executing: " . substr($statement, 0, 100) . "...\n";
                echo "MySQL Error: " . $mysqli->error . "\n";
            } else {
                echo "Executed: " . substr($statement, 0, 80) . "...\n";
            }
        }
    }

    // Verify tables
    $result = $mysqli->query("USE ci3_ecomm; SHOW TABLES;");

    echo "\n\nTables created:\n";
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        echo "  - " . $row['Tables_in_ci3_ecomm'] . "\n";
        $count++;
    }

    echo "\nTotal tables: " . $count . "\n";

    $mysqli->close();

} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
?>
