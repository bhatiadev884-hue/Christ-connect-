<?php

//Your Mysql Config
$servername = "127.0.0.1:3306";
$username = "root";
$password = "";
$dbname = "placement_portal";

//Create New Database Connection
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli($servername, $username, $password, $dbname);

//Check Connection - Fallback to SQLite if MySQL service is not running
if ($conn->connect_error) {
    require_once __DIR__ . "/sqlite_bridge.php";
    $conn = new BridgeMysqli(__DIR__ . "/database/placement_portal.sqlite", __DIR__ . "/database/db1.sql");
}

