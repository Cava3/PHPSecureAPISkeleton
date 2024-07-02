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

// Generate a new SID (64 chars long)
$SIDresult = false;
$NEW_SID = "";

do {
    $seed = rand();
    $SID = hash('sha256', $seed);

    $query = "SELECT * FROM user WHERE sid = :sid";
    $statement = $connection->prepare($query);
    $statement->bindParam(':sid', $SID);
    $statement->execute();
    $SIDresult = $statement->fetch(PDO::FETCH_ASSOC);
} while ($SIDresult || strlen($SID) != 64);

// Update the user's SID in the database
$query = "UPDATE user SET sid = :sid, last_seen = NOW() WHERE sid = :old_sid";
$statement = $connection->prepare($query);
$statement->bindParam(':sid', $NEW_SID);
$statement->bindParam(':old_sid', $SID);
$statement->execute();




// Close the database connection
$connection = null;

// Return the success message
header("Content-Type: application/json");
echo json_encode([
    "success" => true
]);
