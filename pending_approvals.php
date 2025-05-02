<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'agent') {
    header("Location: login.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve'])) {
        $sale_id = $_POST['sale_id'];
        $stmt = $pdo->prepare("UPDATE sales SET status = 'approved' WHERE sale_id = ? AND user_id = ?");
        $stmt->execute([$sale_id, $_SESSION['user_id']]);
        $_SESSION['success'] = "Sale #$sale_id approved successfully!";
        header("Location: pending_approvals.php");
        exit();
    }
    elseif (isset($_POST['reject'])) {
        $sale_id = $_POST['sale_id'];
        $stmt = $pdo->prepare("UPDATE sales SET status = 'rejected' WHERE sale_id = ? AND user_id = ?");
        $stmt->execute([$sale_id, $_SESSION['user_id']]);
        $_SESSION['success'] = "Sale #$sale_id rejected successfully!";
        header("Location: pending_approvals.php");
        exit();
    }
}

require 'config.php';
require 'agent_sidebar.php';

// Handle approval/rejection if form submitted


// Fetch pending sales
$stmt = $pdo->prepare("
    SELECT s.sale_id, s.customer_name, s.quantity, i.item_name, 
           s.total_amount, s.due_date, s.payment_type, s.status,
           i.unit_price, i.hs_code
    FROM sales s
    JOIN items i ON s.item_id = i.item_id
    WHERE s.user_id = ? AND s.status = 'pending'
    ORDER BY s.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approvals | Qoricha</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #e6f0ff;
            --success: #28a745;
            --success-light: #d1fae5;
            --warning: #fd7e14;
            --warning-light: #fef3c7;
            --danger: #dc3545;
            --danger-light: #fee2e2;
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
            margin: 0;
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

        /* Horizontal Cards Container */
        .cards-container {
            display: flex;
            gap: 24px;
            overflow-x: auto;
            padding: 20px 0;
            scrollbar-width: thin;
            scrollbar-color: var(--primary) #f1f1f1;
        }

        .cards-container::-webkit-scrollbar {
            height: 8px;
        }

        .cards-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .cards-container::-webkit-scrollbar-thumb {
            background-color: var(--primary);
            border-radius: 10px;
        }

        /* Sale Card */
        .sale-card {
            min-width: 320px;
            background: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: var(--transition);
            border-top: 4px solid var(--warning);
            flex-shrink: 0;
        }

        .sale-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(253, 126, 20, 0.15);
        }

        .sale-header {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: rgba(253, 126, 20, 0.05);
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
            background-color: var(--warning-light);
            color: var(--warning);
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
            font-weight: 500;
        }

        .detail-value {
            font-weight: 500;
            color: var(--dark);
            text-align: right;
        }

        .detail-value.amount {
            font-weight: 600;
            color: var(--primary);
        }

        .detail-value.highlight {
            color: var(--warning);
            font-weight: 600;
        }

        /* Action Buttons */
        .sale-actions {
            padding: 20px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            gap: 12px;
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
            flex: 1;
            justify-content: center;
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #218838;
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #c82333;
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
        }

        .empty-state i {
            font-size: 3rem;
            color: #e2e8f0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: #64748b;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #94a3b8;
            font-size: 1rem;
            max-width: 500px;
            margin: 0 auto;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 0;
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .sale-card {
                min-width: 280px;
            }
            
            .sale-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>Pending Approvals</h1>
                <p>Review and manage your pending sales approvals</p>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($sales)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>No Pending Approvals</h3>
                <p>You currently don't have any sales waiting for approval. All your submitted sales have been processed.</p>
            </div>
        <?php else: ?>
            <div class="cards-container">
                <?php foreach ($sales as $sale): ?>
                    <div class="sale-card">
                        <div class="sale-header">
                            <span class="sale-id">Sale #<?php echo htmlspecialchars($sale['sale_id']); ?></span>
                            <span class="sale-status">Pending Approval</span>
                        </div>
                        
                        <div class="sale-details">
                            <div class="detail-row">
                                <span class="detail-label">Customer</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['customer_name']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Product</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['item_name']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">HS Code</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['hs_code']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Unit Price</span>
                                <span class="detail-value">ETB <?php echo number_format($sale['unit_price'], 2); ?></span>
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
                                <span class="detail-label">Payment Type</span>
                                <span class="detail-value"><?php echo htmlspecialchars($sale['payment_type']); ?></span>
                            </div>
                            
                            <div class="detail-row">
                                <span class="detail-label">Due Date</span>
                                <span class="detail-value highlight">
                                    <i class="far fa-calendar-alt"></i> 
                                    <?php echo date('M d, Y', strtotime($sale['due_date'])); ?>
                                </span>
                            </div>
                        </div>
                     
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>