<?php

$GLOBALS["ERROR_CODES"] = array(
    600 => "SID not found",
    601 => "SID is outdated",
    602 => "SID not provided",
    603 => "Username or password incorrect",         // Session connection only
    604 => "Every field must be filled",
    605 => "Incorrect email",
    606 => "Account already exists, please connect",
    607 => "No account corresponding to sent data",  // Account deletion only
);

/**
 * Send an error message to the client and exits.
 * 
 * @param int $code The error code to send. List of codes is in the $GLOBALS["ERROR_CODES"] array.
 */
function sendError($code){
    header("HTTP/1.0 $code Error");
    echo json_encode(array("error" => $code, "message" => $GLOBALS["ERROR_CODES"][$code]));
    exit;
}

/**
 * Begin the endpoint.
 * Should be called by every endpoint except for account creation and session connection.
 * 
 * @return bool True if the endpoint can continue.
 * @throws Exception Just exits the script if the SID is not provided.
 */
function beginEndpoint(): bool {
    // Slow down bruteforce attacks
    usleep(100_000); // 100ms = 0.1s

    // Get the database connection details from the environment variable
    $mysqlHost = getenv('MYSQL_CONTAINER');
    $mysqlDatabase = getenv('MYSQL_DATABASE');
    $mysqlUsername = getenv('MYSQL_USER');
    $mysqlPassword = getenv('MYSQL_PASSWORD');

    // Check if the SID is provided
    if (!isset($_POST['sid'])) {
        header("Content-Type: application/json");
        sendError(602);
    }

    $SID = $_POST['sid'];

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
        sendError(600);
    }

    $lastSeen = $result[0]['last_seen'];
    $currentTime = time();

    if ($currentTime - $lastSeen > 3600) {
        header("Content-Type: application/json");
        sendError(601);
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
