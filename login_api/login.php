<?php
header("Content-Type: application/json");

// Assuming you have a database connection
$host = "localhost:3308 ";
$user = "root";
$pass = "";
$db   = "api";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->username) && isset($data->password)) {
        $username = $data->username;
        $password = $data->password;

        $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $response = array('success' => true, 'message' => 'Login successful');
        } else {
            $response = array('success' => false, 'message' => 'Invalid username or password');
        }
    } else {
        $response = array('success' => false, 'message' => 'Username and password are required');
    }

    echo json_encode($response);
}

// $conn->close();




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->username) && isset($data->password)) {
        $username = $data->username;
        $password = $data->password;

        // Check if the username already exists
        $checkQuery = "SELECT * FROM users WHERE username = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $response = array('success' => false, 'message' => 'Username already exists');
        } else {
            // Prepare and bind the statement to prevent SQL injection
            $insertQuery = "INSERT INTO users (username,password) VALUES (?, ?)";
            $insertStmt = $conn->prepare($insertQuery);
            $insertStmt->bind_param("ss", $username, $password );

            // Execute the statement
            if ($insertStmt->execute()) {
                $response = array('success' => true, 'message' => 'User added successfully');
            } else {
                $response = array('success' => false, 'message' => 'Error adding user');
            }

            // Close the statement
            $insertStmt->close();
        }

        // Close the check statement
        $checkStmt->close();
    } else {
        $response = array('success' => false, 'message' => 'Username and password are required');
    }

    echo json_encode($response);
}

// Close the database connection
$conn->close();
?>

?>