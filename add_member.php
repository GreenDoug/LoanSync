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
    // Retrieve form data
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_number = $_POST['contact_number'];

    // Insert member data into the database
    $sql = "INSERT INTO Members (first_name, last_name, email, contact_number)
            VALUES ('$first_name', '$last_name', '$email', '$contact_number')";

    if ($conn->query($sql) === TRUE) {
        echo "New member added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Member</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to global styles -->
</head>
<body>
<div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="loan_passing.php">Passing of Loan</a>
    <a href="add_loan.php">Add New Loan</a>
    <a href="add_member.php">Add New Member</a>
    <a href="payments.php">Payments</a> <!-- Link to payments page -->
    <a href="logout.php">Logout</a>
</div>
<div class="container">
    <h2>Add New Member</h2>
    <form action="add_member.php" method="POST">
        <label for="first_name">First Name</label>
        <input type="text" name="first_name" id="first_name" required>

        <label for="last_name">Last Name</label>
        <input type="text" name="last_name" id="last_name" required>

        <label for="email">Email</label>
        <input type="text" name="email" id="email" required>

        <label for="contact_number">Contact Number</label>
        <input type="text" name="contact_number" id="contact_number" required>

        <button type="submit">Add Member</button>
    </form>
</div>

</body>
</html>
