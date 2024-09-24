<?php
session_start();
$host = 'localhost'; // Database host
$db = 'loansync_db'; // Database name
$user = 'root'; // Database username
$pass = ''; // Database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $loan_id = $_POST['loan_id'];
    $status_id = $_POST['status_id'];

    // Update the status for the specific loan
    $sql = "UPDATE Loans SET status_id = '$status_id' WHERE loan_id = '$loan_id'";

    if ($conn->query($sql) === TRUE) {
        echo "Status updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();

// Redirect back to the loan passing page
header("Location: loan_passing.php");
exit();
?>
