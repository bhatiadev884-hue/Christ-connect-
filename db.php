<?php

// Your Mysql Config (support environment variables for cloud deployment)
$servername = getenv('DB_HOST') ?: "127.0.0.1:3306";
$username   = getenv('DB_USER') ?: "root";
$password   = getenv('DB_PASS') ?: "";
$dbname     = getenv('DB_NAME') ?: "placement_portal";

// Create New Database Connection
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($servername, $username, $password, $dbname);

// Check Connection - Fallback to SQLite if MySQL service is not running
if ($conn->connect_error) {
    require_once __DIR__ . "/sqlite_bridge.php";

    $sqlitePath = __DIR__ . "/database/placement_portal.sqlite";

    // Serverless platforms like Vercel have a read-only filesystem except /tmp
    $dbDir = __DIR__ . "/database";
    if (!is_writable($dbDir) && is_dir('/tmp')) {
        $tmpSqlite = '/tmp/placement_portal.sqlite';
        if (!file_exists($tmpSqlite) && file_exists($sqlitePath)) {
            @copy($sqlitePath, $tmpSqlite);
        }
        if (file_exists($tmpSqlite)) {
            $sqlitePath = $tmpSqlite;
        }
    }

    $conn = new BridgeMysqli($sqlitePath, __DIR__ . "/database/db1.sql");
}


