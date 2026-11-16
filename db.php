<?php
$host = "localhost";
$port = "5432";
$dbname = "db_pradip";        // the database you created
$user = "postgres";
$password = "Pradip713129";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    //echo "Connected successfully";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    }
?>