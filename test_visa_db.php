<?php
// Test script to debug database connection

try {
    $host = '127.0.0.1';
    $port = '3306';
    $database = 'riyaoeiu_subvisadom';
    $username = 'riyaoeiu_subvisadom';
    $password = '4h[#Uln@G{ZS';
    
    $dsn = "mysql:host=$host;port=$port;dbname=$database;charset=utf8mb4";
    
    echo "Attempting to connect to: $dsn\n";
    echo "Username: $username\n";
    echo "Password: " . str_repeat('*', strlen($password)) . "\n\n";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✓ Connection successful!\n";
    
    // Test query
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    
    echo "\nTables in database:\n";
    foreach ($tables as $table) {
        echo "- " . array_values($table)[0] . "\n";
    }
    
    // Check if visa_application_summary table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'visa_application_summary'");
    if ($stmt->rowCount() > 0) {
        echo "\n✓ visa_application_summary table exists!\n";
        
        // Get table structure
        $stmt = $pdo->query("DESCRIBE visa_application_summary");
        $columns = $stmt->fetchAll();
        
        echo "\nTable structure:\n";
        foreach ($columns as $column) {
            echo "- {$column['Field']}: {$column['Type']}\n";
        }
    } else {
        echo "\n✗ visa_application_summary table not found!\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    
    if ($e->getCode() == 1045) {
        echo "\nAccess denied error (1045) solutions:\n";
        echo "1. Check if user exists in MySQL with: SELECT User, Host FROM mysql.user WHERE User = 'riyaoeiu_subvisadom';\n";
        echo "2. Create user for 127.0.0.1: CREATE USER 'riyaoeiu_subvisadom'@'127.0.0.1' IDENTIFIED BY '4h[#Uln@G{ZS';\n";
        echo "3. Grant privileges: GRANT ALL ON riyaoeiu_subvisadom.* TO 'riyaoeiu_subvisadom'@'127.0.0.1';\n";
        echo "4. Try connecting as root to verify credentials\n";
    }
}
