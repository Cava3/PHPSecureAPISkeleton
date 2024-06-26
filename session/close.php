<?php

// ==== Begin of the endpoint ====
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$api = realpath("$root/" . getenv('API_PATH'));
require_once "$api/endpoint.php";

beginEndpoint();

$SID = $_POST['sid'];

// Get the database connection details from the environment variable
$mysqlHost = getenv('MYSQL_CONTAINER');
$mysqlDatabase = getenv('MYSQL_DATABASE');
$mysqlUsername = getenv('MYSQL_USER');
$mysqlPassword = getenv('MYSQL_PASSWORD');

// Create a new PDO instance
$fullURI = "mysql:host=$mysqlHost;dbname=$mysqlDatabase";
$connection = new PDO($fullURI, $mysqlUsername, $mysqlPassword);

// Delete the user from the database
$query = "UPDATE user SET sid = NULL, last_seen = '1970-01-01 00:00:00' WHERE sid = :sid";
$statement = $connection->prepare($query);
$statement->bindParam(':sid', $SID);
$statement->execute();

// Close the database connection
$connection = null;

// Return the success message
header("Content-Type: application/json");
echo json_encode([
    "success" => true
]);
