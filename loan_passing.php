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

// Fetch loan data including loan statuses and sort by custom status order
$approved_types_sql = "SELECT l.loan_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name, 
                    l.loan_kind, l.loan_amount, l.interest_rate, l.loan_date, l.due_date, ls.status_name, ls.status_id
                    FROM Loans l
                    JOIN Members m ON l.member_id = m.member_id
                    LEFT JOIN Loan_Status ls ON l.status_id = ls.status_id
                    WHERE ls.status_name = 'Approved'
                    ORDER BY l.loan_id";
$approved_types_result = $conn->query($approved_types_sql);

$pending_types_sql = "SELECT l.loan_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name, 
                    l.loan_kind, l.loan_amount, l.interest_rate, l.loan_date, l.due_date, ls.status_name, ls.status_id
                    FROM Loans l
                    JOIN Members m ON l.member_id = m.member_id
                    LEFT JOIN Loan_Status ls ON l.status_id = ls.status_id
                    WHERE ls.status_name = 'Pending'
                    ORDER BY l.loan_id";
$pending_types_result = $conn->query($pending_types_sql);

$rejected_types_sql = "SELECT l.loan_id, CONCAT(m.first_name, ' ', m.last_name) AS member_name, 
                    l.loan_kind, l.loan_amount, l.interest_rate, l.loan_date, l.due_date, ls.status_name, ls.status_id
                    FROM Loans l
                    JOIN Members m ON l.member_id = m.member_id
                    LEFT JOIN Loan_Status ls ON l.status_id = ls.status_id
                    WHERE ls.status_name = 'Rejected'
                    ORDER BY l.loan_id";
$rejected_types_result = $conn->query($rejected_types_sql);

// Fetch all available statuses for the dropdown
$statuses_sql = "SELECT * FROM Loan_Status";
$statuses_result = $conn->query($statuses_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Passing</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to global styles -->
</head>
<body>

<div class="navbar">
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="loan_passing.php">Passing of Loan</a>
    <a href="add_loan.php">Add New Loan</a>
    <a href="add_member.php">Add New Member</a>
    <a href="payments.php">Payments</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h2>Loan Passing</h2>

    <table>
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Loan Type</th>
                <th>Loan Amount</th>
                <th>Interest Rate</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($approved_types_result->num_rows > 0): ?>
                <?php while($loan = $approved_types_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $loan['member_name']; ?></td>
                        <td><?= $loan['loan_kind']; ?></td>
                        <td><?= number_format($loan['loan_amount'], 2); ?></td>
                        <td><?= number_format($loan['interest_rate'], 2); ?>%</td>
                        <td><?= date('Y-m-d', strtotime($loan['loan_date'])); ?></td>
                        <td><?= date('Y-m-d', strtotime($loan['due_date'])); ?></td>
                        <td>
                            <form action="update_status.php" method="POST">
                                <input type="hidden" name="loan_id" value="<?= $loan['loan_id']; ?>">
                                <select name="status_id" onchange="this.form.submit()">
                                    <?php
                                    // Loop through all possible statuses and mark the current one as selected
                                    foreach ($statuses_result as $status) {
                                        $selected = $status['status_id'] == $loan['status_id'] ? 'selected' : '';
                                        echo "<option value='" . $status['status_id'] . "' $selected>" . $status['status_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No loan records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Loan Type</th>
                <th>Loan Amount</th>
                <th>Interest Rate</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($pending_types_result->num_rows > 0): ?>
                <?php while($loan = $pending_types_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $loan['member_name']; ?></td>
                        <td><?= $loan['loan_kind']; ?></td>
                        <td><?= number_format($loan['loan_amount'], 2); ?></td>
                        <td><?= number_format($loan['interest_rate'], 2); ?>%</td>
                        <td><?= date('Y-m-d', strtotime($loan['loan_date'])); ?></td>
                        <td><?= date('Y-m-d', strtotime($loan['due_date'])); ?></td>
                        <td>
                            <form action="update_status.php" method="POST">
                                <input type="hidden" name="loan_id" value="<?= $loan['loan_id']; ?>">
                                <select name="status_id" onchange="this.form.submit()">
                                    <?php
                                    // Loop through all possible statuses and mark the current one as selected
                                    foreach ($statuses_result as $status) {
                                        $selected = $status['status_id'] == $loan['status_id'] ? 'selected' : '';
                                        echo "<option value='" . $status['status_id'] . "' $selected>" . $status['status_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No loan records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    <table>
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Loan Type</th>
                <th>Loan Amount</th>
                <th>Interest Rate</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($rejected_types_result->num_rows > 0): ?>
                <?php while($loan = $rejected_types_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $loan['member_name']; ?></td>
                        <td><?= $loan['loan_kind']; ?></td>
                        <td><?= number_format($loan['loan_amount'], 2); ?></td>
                        <td><?= number_format($loan['interest_rate'], 2); ?>%</td>
                        <td><?= date('Y-m-d', strtotime($loan['loan_date'])); ?></td>
                        <td><?= date('Y-m-d', strtotime($loan['due_date'])); ?></td>
                        <td>
                            <form action="update_status.php" method="POST">
                                <input type="hidden" name="loan_id" value="<?= $loan['loan_id']; ?>">
                                <select name="status_id" onchange="this.form.submit()">
                                    <?php
                                    // Loop through all possible statuses and mark the current one as selected
                                    foreach ($statuses_result as $status) {
                                        $selected = $status['status_id'] == $loan['status_id'] ? 'selected' : '';
                                        echo "<option value='" . $status['status_id'] . "' $selected>" . $status['status_name'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No loan records found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
