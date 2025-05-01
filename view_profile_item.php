<?php
session_start();
require 'config.php'; // Include your database configuration
 require 'sidebar.php';
// Fetch items from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM items");
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching items: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['edit_item'])) {
        if (!empty($_POST['selected_items'])) {
            $item_id = $_POST['selected_items'][0]; 
            header("Location: edit_item.php?id=" . $item_id);
            exit;
        } else {
            echo "<script>alert('No item selected for editing.');</script>";
        }
    }

    if (isset($_POST['delete_item'])) {
        if (!empty($_POST['selected_items'])) {
            $ids = implode(',', array_map('intval', $_POST['selected_items']));
            $stmt = $pdo->prepare("DELETE FROM items WHERE id IN ($ids)");

            if ($stmt->execute()) {
                echo "<script>alert('Items deleted successfully.');</script>";
                header("Refresh:0");
                exit;
            } else {
                echo "<script>alert('Error deleting items.');</script>";
            }
        } else {
            echo "<script>alert('No item selected for deletion.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock List</title>
    <link rel="stylesheet" href="view_profile_item.css">
    
</head>
<body>
    <h2>Item Profile List</h2>

    <form action="" method="POST">
        <div class="button-container">
            <a href="create_item.php" class="button">Add Item</a>
            <input type="submit" name="edit_item" value="Edit Item" class="button" <?php echo empty($items) ? 'disabled' : ''; ?>>
            <input type="submit" name="delete_item" value="Delete Item" class="button" <?php echo empty($items) ? 'disabled' : ''; ?>>
        </div>

        <table>
            <tr>
                <th>Select</th>
                <th>Id</th>
                <th>HS Code</th>
                <th>Specification Code</th>
                <th>Description of Goods</th>
                <th>Common Name</th>
                <th>Quantity</th>
                <th>Quantity Code</th>
                <th>Package Type Code</th>
                <th>Net Weight</th>
                <th>Weight Unit Code</th>
                <th>Unit Price</th>
                <th>Invoice Amount</th>
                <th>Invoice Amount (Birr)</th>
                <th>Country of Origin</th>
                <th> Expire Date</th>
            </tr>
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><input type="checkbox" name="selected_items[]" value="<?php echo htmlspecialchars($item['id']); ?>"></td>
                        <td><?php echo htmlspecialchars($item['id']); ?></td>
                        <td><?php echo htmlspecialchars($item['hs_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['spec_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['description']); ?></td>
                        <td><?php echo htmlspecialchars($item['common_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['packages_type_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['net_weight']); ?></td>
                        <td><?php echo htmlspecialchars($item['weight_unit_code']); ?></td>
                        <td><?php echo htmlspecialchars($item['unit_price']); ?></td>
                        <td><?php echo htmlspecialchars($item['invoice_amount']); ?></td>
                        <td><?php echo htmlspecialchars($item['invoice_amount_birr']); ?></td>
                        <td><?php echo htmlspecialchars($item['country_of_origin']); ?></td>
                        <td><?php echo htmlspecialchars($item['expire_date']); ?></td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="15" style="text-align: center;">No items found.</td>
                </tr>
            <?php endif; ?>
        </table>
    </form>
</body>
</html>
