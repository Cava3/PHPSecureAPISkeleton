<?php

// ==== Begin of the endpoint ====
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$api = realpath("$root/" . getenv('API_PATH'));
require_once "$api/endpoint.php";

beginEndpoint();

// Check if username and password are provided
if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['email']) || !isset($_POST['sid'])) {
    sendError(604);
}

$username = $_POST['username'];
$password = $_POST['password'];
$sid = $_POST['sid'];
$email = $_POST['email'];

// Get the database connection details from the environment variable
$mysqlHost = getenv('MYSQL_CONTAINER');
$mysqlDatabase = getenv('MYSQL_DATABASE');
$mysqlUsername = getenv('MYSQL_USER');
$mysqlPassword = getenv('MYSQL_PASSWORD');

// Create a new PDO instance
$fullURI = "mysql:host=$mysqlHost;dbname=$mysqlDatabase";
$connection = new PDO($fullURI, $mysqlUsername, $mysqlPassword);

// Get the user's salt
$query = "SELECT salt FROM user WHERE sid = :sid";
$statement = $connection->prepare($query);
$statement->bindParam(':sid', $sid);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    sendError(600);
}

$salt = $result[0]['salt'];

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Retrieve the user's ID
$query = "SELECT id FROM user WHERE sid = :sid AND username = :username AND email = :email AND salt = :salt AND passwd = :passwd";
$statement = $connection->prepare($query);
$statement->bindParam(':sid', $sid);
$statement->bindParam(':username', $username);
$statement->bindParam(':email', $email);
$statement->bindParam(':salt', $salt);
$statement->bindParam(':passwd', $hashedPassword);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    sendError(607);
}

$userId = $result[0]['id'];

// Delete the user
$query = "DELETE FROM user WHERE userid = :id";
$statement = $connection->prepare($query);
$statement->bindParam(':id', $userId);
$statement->execute();
$result = $statement->fetch(PDO::FETCH_ASSOC);

// Close the database connection
$connection = null;

// Send the success response
header("Content-Type: application/json");
echo json_encode([
    'success' => true
]);
exit;
