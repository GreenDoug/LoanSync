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

// Fetch members for the select dropdown
$members_sql = "SELECT member_id, first_name, last_name FROM Members";
$members_result = $conn->query($members_sql);

// Fetch status ID for "Pending"
$status_sql = "SELECT status_id FROM Loan_Status WHERE status_name = 'Pending'";
$status_result = $conn->query($status_sql);
$status_id = $status_result->fetch_assoc()['status_id']; // Get the status ID for "Pending"

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $member_id = $_POST['member_id'];
    $loan_kind = $_POST['loan_kind'];
    $loan_amount = $_POST['loan_amount'];
    $interest_rate = $_POST['interest_rate'];
    $loan_date = $_POST['loan_date'];
    $due_date = $_POST['due_date'];

    // Insert loan data into the database with status_id set to "Pending"
    $sql = "INSERT INTO Loans (member_id, loan_kind, loan_amount, interest_rate, loan_date, due_date, status_id)
            VALUES ('$member_id', '$loan_kind', '$loan_amount', '$interest_rate', '$loan_date', '$due_date', '$status_id')";

    if ($conn->query($sql) === TRUE) {
        echo "New loan added successfully!";
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
    <title>Add New Loan</title>
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
    <h2>Add New Loan Application</h2>
    <form action="add_loan.php" method="POST">
        <label for="member_id">Select Member</label>
        <select name="member_id" id="member_id" required>
            <option value="">-- Select Member --</option>
            <?php if ($members_result->num_rows > 0): ?>
                <?php while($member = $members_result->fetch_assoc()): ?>
                    <option value="<?= $member['member_id']; ?>">
                        <?= $member['first_name'] . " " . $member['last_name']; ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>

        <br>
        <label for="loan_kind">Loan Type</label>
        <select name="loan_kind" id="loan_kind" required>
            <option value="Regular">Regular</option>
            <option value="Emergency">Emergency</option>
            <option value="Educational">Educational</option>
            <option value="Summer Loan">Summer Loan</option>
        </select>

        <br>
        <label for="loan_amount">Loan Amount</label>
        <input type="number" name="loan_amount" id="loan_amount" step="0.01" required>

        <label for="interest_rate">Interest Rate (%)</label>
        <input type="number" name="interest_rate" id="interest_rate" step="0.01" required>

        <label for="loan_date">Loan Date</label>
        <input type="date" name="loan_date" id="loan_date" required>

        <label for="due_date">Due Date</label>
        <input type="date" name="due_date" id="due_date" required>

        <button type="submit">Submit Loan Application</button>
    </form>
</div>

</body>
</html>
