<?php

// ==== Begin of the endpoint ====
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$api = realpath("$root/" . getenv('API_PATH'));
require_once "$api/endpoint.php";

// Check if username and password are provided
if (!isset($_POST['username']) || !isset($_POST['password'])) {
    sendError(604);
}

$username = $_POST['username'];
$password = $_POST['password'];

// Get the database connection details from the environment variable
$mysqlHost = getenv('MYSQL_CONTAINER');
$mysqlDatabase = getenv('MYSQL_DATABASE');
$mysqlUsername = getenv('MYSQL_USER');
$mysqlPassword = getenv('MYSQL_PASSWORD');

// Create a new PDO instance
$fullURI = "mysql:host=$mysqlHost;dbname=$mysqlDatabase";
$connection = new PDO($fullURI, $mysqlUsername, $mysqlPassword);

// Get the user's salt
$query = "SELECT salt FROM user WHERE username = :username";
$statement = $connection->prepare($query);
$statement->bindParam(':username', $username);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    sendError(600);
}

$salt = $result[0]['salt'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Get the user's SID
$query = "SELECT id FROM user WHERE username = :username AND passwd = :passwd";
$statement = $connection->prepare($query);
$statement->bindParam(':username', $username);
$statement->bindParam(':passwd', $hashedPassword);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    sendError(603);
}

$userId = $result[0]['id'];

// Generate a new SID (64 chars long)
$SIDresult = false;
$SID = "";

do {
    $seed = rand();
    $SID = hash('sha256', $seed);

    $query = "SELECT * FROM user WHERE sid = :sid";
    $statement = $connection->prepare($query);
    $statement->bindParam(':sid', $SID);
    $statement->execute();
    $SIDresult = $statement->fetch(PDO::FETCH_ASSOC);
} while ($SIDresult || strlen($SID) != 64);

// Update the user's SID
$query = "UPDATE user SET sid = :sid, last_seen = NOW() WHERE id = :id";
$statement = $connection->prepare($query);
$statement->bindParam(':sid', $SID);
$statement->bindParam(':id', $userId);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

// Close the database connection
$connection = null;

// Send the SID back to the user
header("Content-Type: application/json");
echo json_encode(["
    sid" => $SID
]);