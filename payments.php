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
$members_sql = "SELECT member_id, CONCAT(first_name, ' ', last_name) AS member_name FROM Members";
$members_result = $conn->query($members_sql);

// Fetch payments for display, sorted by payment_id descending
$payments_sql = "SELECT p.payment_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name, p.payment_amount, p.payment_date
                 FROM Payments p
                 JOIN Loans l ON p.loan_id = l.loan_id
                 JOIN Members m ON l.member_id = m.member_id
                 ORDER BY p.payment_id DESC"; // Sort by payment_id in descending order
$payments_result = $conn->query($payments_sql);

// Fetch loans for a selected member if any
$loans_sql = "";
if (isset($_POST['member_id']) && !empty($_POST['member_id'])) {
    $member_id = $_POST['member_id'];
    $loans_sql = "SELECT loan_id, loan_kind FROM Loans WHERE member_id = '$member_id'";
    $loans_result = $conn->query($loans_sql);
} else {
    $loans_result = [];
}

// Insert payment data into the database
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['payment_amount'])) {
    $loan_id = $_POST['loan_id'];
    $payment_amount = $_POST['payment_amount'];
    $payment_date = $_POST['payment_date'];

    $sql = "INSERT INTO Payments (loan_id, payment_amount, payment_date)
            VALUES ('$loan_id', '$payment_amount', '$payment_date')";

    if ($conn->query($sql) === TRUE) {
        echo "Payment added successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Calculate total payments, total loans, and remaining balances by member
$total_balances_sql = "SELECT m.member_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name,
                       COALESCE(SUM(l.loan_amount), 0) AS total_loans,
                       COALESCE(SUM(p.payment_amount), 0) AS total_payments,
                       COALESCE(SUM(l.loan_amount), 0) - COALESCE(SUM(p.payment_amount), 0) AS remaining_balance
                       FROM Members m
                       LEFT JOIN Loans l ON m.member_id = l.member_id
                       LEFT JOIN Payments p ON l.loan_id = p.loan_id
                       GROUP BY m.member_id, member_name";
$total_balances_result = $conn->query($total_balances_sql);

// Calculate total loans and payments by loan type for each member, including remaining balances
$loan_types_sql = "SELECT m.member_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name, 
                          l.loan_kind,
                          COALESCE(SUM(l.loan_amount), 0) AS total_loan_amount,
                          COALESCE(SUM(p.payment_amount), 0) AS total_payments,
                          COALESCE(SUM(l.loan_amount), 0) - COALESCE(SUM(p.payment_amount), 0) AS remaining_balance
                   FROM Members m
                   LEFT JOIN Loans l ON m.member_id = l.member_id
                   LEFT JOIN Payments p ON l.loan_id = p.loan_id
                   GROUP BY m.member_id, member_name, l.loan_kind
                   ORDER BY m.member_id, l.loan_kind";
$loan_types_result = $conn->query($loan_types_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments</title>
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
    <h2>Add Payment</h2>
    <form action="payments.php" method="POST">
        <label for="member_id">Select Member</label>
        <select name="member_id" id="member_id" required onchange="this.form.submit()">
            <option value="">-- Select Member --</option>
            <?php if ($members_result->num_rows > 0): ?>
                <?php while($member = $members_result->fetch_assoc()): ?>
                    <option value="<?= $member['member_id']; ?>" <?= isset($_POST['member_id']) && $_POST['member_id'] == $member['member_id'] ? 'selected' : ''; ?>>
                        <?= $member['member_name']; ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </form>

    <?php if (isset($_POST['member_id']) && !empty($_POST['member_id'])): ?>
        <form action="payments.php" method="POST">
            <label for="loan_id">Select Loan</label>
            <select name="loan_id" id="loan_id" required>
                <option value="">-- Select Loan --</option>
                <?php if ($loans_result->num_rows > 0): ?>
                    <?php while($loan = $loans_result->fetch_assoc()): ?>
                        <option value="<?= $loan['loan_id']; ?>">
                            Loan Type: <?= $loan['loan_kind']; ?>
                        </option>
                    <?php endwhile; ?>
                <?php endif; ?>
            </select>

            <label for="payment_amount">Payment Amount</label>
            <input type="number" name="payment_amount" id="payment_amount" step="0.01" required>

            <label for="payment_date">Payment Date</label>
            <input type="date" name="payment_date" id="payment_date" required>

            <button type="submit">Add Payment</button>
        </form>
    <?php endif; ?>

    <h2>All Payments</h2>
    <table>
        <thead>
            <tr>
                <th>Payment ID</th>
                <th>Member Name</th>
                <th>Payment Amount</th>
                <th>Payment Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($payments_result->num_rows > 0): ?>
                <?php while($payment = $payments_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $payment['payment_id']; ?></td>
                        <td><?= $payment['member_name']; ?></td>
                        <td><?= $payment['payment_amount']; ?></td>
                        <td><?= $payment['payment_date']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <h2>Loan Types by Member</h2>
    <table>
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Loan Type</th>
                <th>Total Loan Amount</th>
                <th>Total Payments</th>
                <th>Remaining Balance</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($loan_types_result->num_rows > 0): ?>
                <?php while($loan_type = $loan_types_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $loan_type['member_name']; ?></td>
                        <td><?= $loan_type['loan_kind']; ?></td>
                        <td><?= number_format($loan_type['total_loan_amount'], 2); ?></td>
                        <td><?= number_format($loan_type['total_payments'], 2); ?></td>
                        <td><?= number_format($loan_type['remaining_balance'], 2); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
