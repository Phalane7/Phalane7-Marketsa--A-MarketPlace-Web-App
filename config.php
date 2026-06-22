<?php

$host = "sql308.infinityfree.com";
$buyer = "if0_42157238";
$password = "S3RGy5gibmGquK7";
$database = "if0_42157238_markets_db";

$conn = new mysqli($host, $buyer, $password, $database);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>