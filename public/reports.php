<?php
// Include config file
require_once "../includes/config.php";
require_once "../includes/template_helper.php";

// Check if user is logged in and has appropriate role
require_role([ROLE_ACCOUNTANT, ROLE_DEVELOPER]);

$user_role = $_SESSION['user_role'];
$user_name = $_SESSION['full_name'];

// Get report data
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-01');
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');

// Get transaction summary
$sql = "SELECT 
            COUNT(*) as total_transactions,
            COALESCE(SUM(amount), 0) as total_revenue,
            payment_method,
            COUNT(*) as count
        FROM transactions
        WHERE DATE(payment_date) BETWEEN ? AND ?
        GROUP BY payment_method";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$transactions = $stmt->get_result();
$stmt->close();

// Get total revenue
$sql = "SELECT COALESCE(SUM(amount), 0) as total FROM transactions WHERE DATE(payment_date) BETWEEN ? AND ?";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$result = $stmt->get_result();
$total_revenue = $result->fetch_assoc()['total'];
$stmt->close();

// Get billing summary
$sql = "SELECT 
            COUNT(*) as total_bills,
            COALESCE(SUM(total_amount), 0) as total_billed,
            COALESCE(SUM(paid_amount), 0) as total_paid,
            COALESCE(SUM(balance), 0) as total_balance,
            payment_status,
            COUNT(*) as count
        FROM billing
        WHERE DATE(billing_date) BETWEEN ? AND ?
        GROUP BY payment_status";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss", $date_from, $date_to);
$stmt->execute();
$billing = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Patient DBMS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <?php render_common_styles(); ?>
</head>
<body>
    <?php render_navbar($user_name, $user_role); ?>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <?php render_sidebar('reports', $user_role); ?>
            </div>

            <div class="col-md-10">
                <div class="container-fluid py-4">
                    <h2>Financial Reports</h2>
                    <p class="text-muted">View financial reports and analytics</p>

                    <!-- Date Filter -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" class="form-inline">
                                <label class="mr-2">From:</label>
                                <input type="date" name="date_from" class="form-control mr-3" value="<?php echo htmlspecialchars($date_from); ?>">
                                
                                <label class="mr-2">To:</label>
                                <input type="date" name="date_to" class="form-control mr-3" value="<?php echo htmlspecialchars($date_to); ?>">
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-filter"></i> Filter
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Revenue Summary -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5>Total Revenue</h5>
                                    <h2 class="text-success">$<?php echo number_format($total_revenue, 2); ?></h2>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions by Payment Method -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-money"></i> Transactions by Payment Method</h5>
                                </div>
                                <div class="card-body">
                                    <?php if($transactions->num_rows > 0): ?>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Payment Method</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($row = $transactions->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted">No transactions found for the selected period.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Billing Status -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fa fa-file-text"></i> Billing Status</h5>
                                </div>
                                <div class="card-body">
                                    <?php if($billing->num_rows > 0): ?>
                                        <table class="table">
                                            <thead>
                                                <tr>
                                                    <th>Status</th>
                                                    <th>Count</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while($row = $billing->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($row['payment_status']); ?></td>
                                                    <td><?php echo $row['count']; ?></td>
                                                </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    <?php else: ?>
                                        <p class="text-muted">No billing records found for the selected period.</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
