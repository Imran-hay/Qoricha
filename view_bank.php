<?php
session_start();

// Include database configuration
require 'config.php';
require 'agent_sidebar.php';

// Fetch banks from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM banks ORDER BY bank_name ASC"); // Order by bank name
    $stmt->execute();
    $banks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching banks: " . $e->getMessage();
    $banks = []; // Ensure $banks is an empty array to avoid errors later
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Banks</title>
    <link rel="stylesheet" href="view_banks.css">
</head>
<body>
    <header>
        <!-- Header content here (optional) -->
    </header>
    <main>
        <div class="container">
            <h2>View Bank Accounts</h2>

            <?php if (count($banks) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Bank Name</th>
                            <th>Bank ID</th>
                            <th>Holder Name</th>
                            <th>Account Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($banks as $bank): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($bank['bank_name']); ?></td>
                                <td><?php echo htmlspecialchars($bank['bank_id']); ?></td>
                                <td><?php echo htmlspecialchars($bank['holder_name']); ?></td>
                                <td><?php echo htmlspecialchars($bank['account_number']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-banks">No banks found in the database.</p>
            <?php endif; ?>
        </div>
    </main>
    <script src="js/script.js"></script>
</body>
</html>