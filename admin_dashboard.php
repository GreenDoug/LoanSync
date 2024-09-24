<?php
session_start();
$host = 'localhost'; // or your database host
$db = 'loansync_db'; // database name
$user = 'root'; // your database username
$pass = ''; // your database password

// Create connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query to get all active loans, their status, upcoming payments, and member info
$sql = "SELECT m.first_name, m.last_name, l.loan_kind, l.loan_amount, l.loan_date, l.due_date, ls.status_name, 
        (SELECT SUM(payment_amount) FROM Payments WHERE loan_id = l.loan_id) AS total_paid,
        (l.loan_amount - (SELECT IFNULL(SUM(payment_amount), 0) FROM Payments WHERE loan_id = l.loan_id)) AS remaining_balance
        FROM Loans l
        JOIN Members m ON l.member_id = m.member_id
        JOIN Loan_Status ls ON l.status_id = ls.status_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
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
        <h2>Admin Dashboard</h2>

        <!-- Table for displaying loans and payments -->
        <h3>Active Loans</h3>
        <table>
            <tr>
                <th>Member Name</th>
                <th>Loan Type</th>
                <th>Loan Amount</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Total Paid</th>
                <th>Remaining Balance</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
                        <td><?= $row['loan_kind']; ?></td>
                        <td><?= number_format($row['loan_amount'], 2); ?></td>
                        <td><?= $row['loan_date']; ?></td>
                        <td><?= $row['due_date']; ?></td>
                        <td><?= $row['status_name']; ?></td>
                        <td><?= number_format($row['total_paid'], 2); ?></td>
                        <td><?= number_format($row['remaining_balance'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No active loans found.</td>
                </tr>
            <?php endif; ?>
        </table>

        <!-- Placeholder for charts and graphs -->
        <div class="chart-container">
            <h3>Loan Progress Charts</h3>
            <!-- Future code for visual indicators like charts/graphs will go here -->
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
