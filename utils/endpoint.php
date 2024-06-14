<?php

function writeToDatabase($value) {
    // Get the database connection details from the environment variable
    $mysqlHost = getenv('MYSQL_CONTAINER');
    $mysqlDatabase = getenv('MYSQL_DATABASE');
    $mysqlUsername = getenv('MYSQL_USER');
    $mysqlPassword = getenv('MYSQL_PASSWORD');

    // Create a new PDO instance
    $fullURI = "mysql:host=$mysqlHost;dbname=$mysqlDatabase";
    $connection = new PDO($fullURI, $mysqlUsername, $mysqlPassword);

    // // Prepare and execute the SQL query
    // $request = $connection->prepare("INSERT INTO your_table_name (column_name) VALUES (:value)");
    // $request->bindParam(':value', $value);
    // $request->execute();

    // Close the database connection
    $connection = null;
}

// Usage example
$value = "Hello, world!";
writeToDatabase($value);