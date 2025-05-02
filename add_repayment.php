<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'agent_sidebar.php';

$message = "";

// Handle form submission for adding a new repayment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_repayment'])) {
    $sale_id = $_POST['sale_id'];
    $amount = $_POST['amount'];
    $repayment_date = $_POST['repayment_date'];

    try {
        $stmt = $pdo->prepare("INSERT INTO repayments (sale_id, amount, repayment_date, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$sale_id, $amount, $repayment_date]);
        $message = "Repayment added successfully!";
    } catch (PDOException $e) {
        $message = "Error adding repayment: " . $e->getMessage();
    }
}

// Fetch all sales with payment method "Credit" and status "Approved"
try {
    $stmt = $pdo->prepare("
        SELECT sales.*, items.item_name
        FROM sales
        JOIN items ON sales.item_id = items.item_id
        WHERE sales.payment_type = 'Credit' AND sales.status = 'Approved'
        ORDER BY sales.due_date DESC
    ");
    $stmt->execute();
    $credit_sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching credit sales: " . $e->getMessage();
    $credit_sales = [];
}

// Function to fetch repayments for a specific sale
function getRepaymentsForSale($pdo, $sale_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM repayments WHERE sale_id = ? ORDER BY repayment_date DESC");
        $stmt->execute([$sale_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching repayments: " . $e->getMessage());
        return [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Sales Management | Qoricha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6f0ff;
            --primary-dark: #3a56d4;
            --success: #28a745;
            --success-light: #d1fae5;
            --danger: #dc3545;
            --danger-light: #fee2e2;
            --warning: #fd7e14;
            --warning-light: #fef3c7;
            --info: #17a2b8;
            --info-light: #e0f2fe;
            --dark: #2b2d42;
            --light: #f8f9fa;
            --white: #ffffff;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fb;
            color: var(--dark);
            margin-left: 260px;
            padding: 0;
        }

        .main-content {
            margin-left: 280px;
            padding: 40px;
            transition: var(--transition);
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title h1 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 0;
            background: linear-gradient(90deg, var(--primary), #5e72e4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-title p {
            color: #64748b;
            margin: 8px 0 0;
            font-size: 1rem;
        }

        .alert {
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 30px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: var(--box-shadow);
        }

        .alert-success {
            background-color: var(--success-light);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: var(--danger-light);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        .alert i {
            font-size: 1.2rem;
        }

        /* Sales Card */
        .sales-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 24px;
            margin-top: 30px;
        }

        .sale-card {
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border-top: 4px solid var(--primary);
        }

        .sale-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(67, 97, 238, 0.15);
        }

        .sale-header {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sale-id {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }

        .sale-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            background-color: var(--success-light);
            color: var(--success);
        }

        .sale-details {
            padding: 20px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .detail-label {
            color: #64748b;
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 500;
            color: var(--dark);
        }

        .detail-value.amount {
            font-weight: 600;
            color: var(--primary);
        }

        /* Repayments Section */
        .repayments-section {
            margin-top: 20px;
            border-top: 1px solid #f1f5f9;
            padding: 20px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .repayments-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .repayments-list th {
            text-align: left;
            padding: 10px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            border-bottom: 2px solid #f1f5f9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .repayments-list td {
            padding: 12px 10px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .repayments-list tr:last-child td {
            border-bottom: none;
        }

        .repayment-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: var(--warning-light);
            color: var(--warning);
        }

        .status-completed {
            background-color: var(--success-light);
            color: var(--success);
        }

        /* Add Repayment Form */
        .add-repayment-form {
            margin-top: 20px;
            padding: 20px;
            border-radius: var(--border-radius);
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
        }

        .form-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 16px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 0.85rem;
            font-weight: 500;
            color: #64748b;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: var(--transition);
            background-color: var(--white);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 20px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 3rem;
            color: #cbd5e1;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 0.9rem;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 0;
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            .sales-grid {
                grid-template-columns: 1fr;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Credit Sales Management</h1>
                <p>Track and manage customer credit sales and repayments</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-error'; ?>">
                <i class="fas <?php echo strpos($message, 'Error') === false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($credit_sales)): ?>
            <div class="empty-state">
                <i class="fas fa-credit-card"></i>
                <h3>No Credit Sales Found</h3>
                <p>There are currently no approved credit sales to display.</p>
            </div>
        <?php else: ?>
            <div class="sales-grid">
                <?php foreach ($credit_sales as $sale): ?>
                    <div class="sale-card">
                        <div class="sale-header">
                            <span class="sale-id">Sale #<?php echo htmlspecialchars($sale['sale_id']); ?></span>
                            <span class="sale-status">Approved</span>
                        </div>
                        
                        <div class="sale-details">
                            <div class="detail-row">
                                <span class="detail-label">Customer</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['customer_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Item</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['item_name']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Quantity</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['quantity']); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Total Amount</span>
                                <span class="detail-value amount">ETB <?php echo number_format($sale['total_amount'], 2); ?></span>
                            </div>
                            <div class="detail-row">
                                <span class="detail-label">Due Date</span>
                                <span class="detail-value"><?php echo date('M d, Y', strtotime($sale['due_date'])); ?></span>
                            </div>
                        </div>
                        
                        <div class="repayments-section">
                            <h3 class="section-title">
                                <i class="fas fa-money-bill-wave"></i>
                                Repayment History
                            </h3>
                            
                            <?php $repayments = getRepaymentsForSale($pdo, $sale['sale_id']); ?>
                            
                            <?php if (empty($repayments)): ?>
                                <p style="color: #94a3b8; font-size: 0.9rem;">No repayments recorded yet</p>
                            <?php else: ?>
                                <table class="repayments-list">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($repayments as $repayment): ?>
                                            <tr>
                                                <td><?php echo date('M d, Y', strtotime($repayment['repayment_date'])); ?></td>
                                                <td>ETB <?php echo number_format($repayment['amount'], 2); ?></td>
                                                <td>
                                                    <span class="repayment-status status-<?php echo strtolower($repayment['status']); ?>">
                                                        <?php echo htmlspecialchars($repayment['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                            
                            <div class="add-repayment-form">
                                <h4 class="form-title">
                                    <i class="fas fa-plus-circle"></i>
                                    Add New Repayment
                                </h4>
                                <form method="POST" action="">
                                    <input type="hidden" name="sale_id" value="<?php echo htmlspecialchars($sale['sale_id']); ?>">
                                    
                                    <div class="form-grid">
                                        <div class="form-group">
                                            <label for="amount_<?php echo $sale['sale_id']; ?>">Amount (ETB)</label>
                                            <input type="number" step="0.01" class="form-control" id="amount_<?php echo $sale['sale_id']; ?>" name="amount" required>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="repayment_date_<?php echo $sale['sale_id']; ?>">Date</label>
                                            <input type="date" class="form-control" id="repayment_date_<?php echo $sale['sale_id']; ?>" name="repayment_date" required>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" name="add_repayment" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Record Repayment
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Set default date to today for all date inputs
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            const dateInputs = document.querySelectorAll('input[type="date"]');
            
            dateInputs.forEach(input => {
                if (!input.value) {
                    input.value = today;
                }
            });
        });
    </script>
</body>
</html>