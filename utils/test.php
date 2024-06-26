<?php

// TODO: Rate limiting

if(!isset($_GET["api_test_key"]) || $_GET["api_test_key"] !== getenv("API_TEST_KEY")){
    header("HTTP/1.0 403 Forbidden");
    echo "You are not allowed to access this page.";
    exit;
}

$root = realpath($_SERVER["DOCUMENT_ROOT"]);
$api = realpath("$root/" . getenv('API_PATH'));

$API_list = array(
    "account/create.php" => array( // Create a dummy account
        "username" => "test",
        "password" => "test",
        "email" => "test@test.test",
    ),
    "session/connect.php" => array( // Connect to the dummy account
        "username" => "test",
        "password" => "test",
    ),
    // Add more tests here
    "account/delete.php" => array(  // Delete the dummy account
        "username" => "test",
        "password" => "test",
        "email" => "test@test.test"
    )
);
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <title>API Endpoint Test</title>
        <meta charset="UTF-8">
    </head>
    <body>
        <h1>API Endpoint Test</h1>
        <tests>
            <?php
            $currentSID = "";

            foreach ($API_list as $endpoint => $data) {
                $url = "$api/$endpoint";
                $data["sid"] = $currentSID;
                $data = http_build_query($data);
                $options = array(
                    'http' => array(
                        'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                        'method' => 'POST',
                        'content' => $data
                    )
                );
                $context = stream_context_create($options);
                $result = file_get_contents($url, false, $context);
                $result = json_decode($result, true);

                if (isset($result["sid"])) {
                    $currentSID = $result["sid"];
                }

                echo "<test class='" . ($result["error"] ? "failed" : "validated") . "'>";
                echo "<h2>$endpoint</h2>";
                echo "<p>" . ($result["error"] ? "Failed" : "Validated") . "</p>";
                echo "</test>";
            }
            ?>
        </tests>


        <style>
            tests {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            test {
                border: 2px solid black;
                border-radius: 5px;

                text-align: center;
            }

            test.validated {
                background-color: green;
                text-color: white;
            }

            test.failed {
                background-color: red;
                text-color: white;
            }
        </style>
    </body>
</html>