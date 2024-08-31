<?php
session_start();

// Check if the user is an admin
$isAdmin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

$data = json_decode(file_get_contents('php://input'), true);

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ganesh";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Assume columns 1-31 are locked for non-admins
$lockedColumns = 31;

$updateColumns = [];
for ($i = 0; $i < count($data); $i++) {
    if ($isAdmin || $i >= $lockedColumns) {
        $updateColumns[] = "col$i = '" . mysqli_real_escape_string($conn, $data[$i]) . "'";
    }
}

if (!empty($updateColumns)) {
    $sql = "UPDATE csv SET " . implode(', ', $updateColumns) . " WHERE id = '" . mysqli_real_escape_string($conn, $data[0]) . "'";
    if ($conn->query($sql) === TRUE) {
        echo "Record updated successfully";
    } else {
        echo "Error updating record: " . $conn->error;
    }
} else {
    echo "No columns to update";
}

$conn->close();
?>
