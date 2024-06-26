<?php

// ==== Begin of the endpoint ====
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$api = realpath("$root/" . getenv('API_PATH'));
require_once "$api/endpoint.php";

// Check if username and password are provided
if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['email'])) {
    sendError(604);
}

$username = $_POST['username'];
$password = $_POST['password'];
$email = $_POST['email'];

// Get the database connection details from the environment variable
$mysqlHost = getenv('MYSQL_CONTAINER');
$mysqlDatabase = getenv('MYSQL_DATABASE');
$mysqlUsername = getenv('MYSQL_USER');
$mysqlPassword = getenv('MYSQL_PASSWORD');

// Create a new PDO instance
$fullURI = "mysql:host=$mysqlHost;dbname=$mysqlDatabase";
$connection = new PDO($fullURI, $mysqlUsername, $mysqlPassword);

// Derivate a salt from the username
$salt = substr(hash('sha256', $username), 0, 10);

// Hash the password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Check if the username and password combination already exists
$query = "SELECT * FROM user WHERE username = :username AND passwd = :passwd";
$statement = $connection->prepare($query);
$statement->bindParam(':username', $username);
$statement->bindParam(':passwd', $hashedPassword);
$statement->execute();

$uniqueCheckResult = $statement->fetch(PDO::FETCH_ASSOC);
if ($uniqueCheckResult) {
    sendError(606);
}

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

// Insert the new user into the database
$query = "INSERT INTO `user` (`username`, `passwd`, `sid`, `last_seen`, `salt`, `email`) VALUES (:username, :passwd, :sid, NOW(), :salt, :email)";
$statement = $connection->prepare($query);
$statement->bindParam(':username', $username);
$statement->bindParam(':passwd', $hashedPassword);
$statement->bindParam(':sid', $SID);
$statement->bindParam(':salt', $salt);
$statement->bindParam(':email', $email);
$statement->execute();

// Return the SID
header("Content-Type: application/json");
echo json_encode([
    "sid" => $SID
]);
exit;
