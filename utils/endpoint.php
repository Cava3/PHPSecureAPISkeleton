<?php

$GLOBALS["ERROR_CODES"] = array(
    600 => "SID not found",
    601 => "SID is outdated",
    602 => "SID not provided",
    603 => "Username or password incorrect",
    604 => "Username already exists",
    605 => "Every field must be filled",
    606 => "Incorrect email"
);

function getError($code): array {
    return array("error" => $code, "message" => $GLOBALS["ERROR_CODES"][$code]);
}

function beginEndpoint(): bool {
    // Slow down bruteforce attacks
    usleep(100_000); // 100ms = 0.1s

    // Get the database connection details from the environment variable
    $mysqlHost = getenv('MYSQL_CONTAINER');
    $mysqlDatabase = getenv('MYSQL_DATABASE');
    $mysqlUsername = getenv('MYSQL_USER');
    $mysqlPassword = getenv('MYSQL_PASSWORD');

    // Check if the SID is provided
    if (!isset($_GET['sid'])) {
        header("Content-Type: application/json");
        echo json_encode(getError(602));
        return false;
    }

    $SID = $_GET['sid'];

    // Create a new PDO instance
    $fullURI = "mysql:host=$mysqlHost;dbname=$mysqlDatabase";
    $connection = new PDO($fullURI, $mysqlUsername, $mysqlPassword);

    // Check if SID is correct and not outdated
    $query = "SELECT last_seen FROM user WHERE sid = :sid";
    $statement = $connection->prepare($query);
    $statement->bindParam(':sid', $SID);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_ASSOC);
    if (!$result) {
        header("Content-Type: application/json");
        echo json_encode(getError(600));
        return false;
    }

    $lastSeen = $result[0]['last_seen'];
    $currentTime = time();

    if ($currentTime - $lastSeen > 3600) {
        header("Content-Type: application/json");
        echo json_encode(getError(601));
        return false;
    }

    // Update the last seen time
    $query = "UPDATE user SET last_seen = :last_seen WHERE sid = :sid";
    $statement = $connection->prepare($query);
    $statement->bindParam(':last_seen', $currentTime);
    $statement->bindParam(':sid', $SID);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_ASSOC);

    // Close the database connection
    $connection = null;
    return true;
}

// TODO: Clean
beginEndpoint();