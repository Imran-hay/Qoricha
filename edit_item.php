<?php
session_start();
require 'config.php'; // Include your database configuration

// Check if the ID is provided in the URL
if (!isset($_GET['id'])) {
    echo "No item ID provided.";
    exit;
}

$item_id = $_GET['id'];

// Fetch the item data from the database
try {
    $stmt = $pdo->prepare("SELECT * FROM items WHERE id = ?");
    $stmt->execute([$item_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the item exists
    if (!$item) {
        echo "Item not found.";
        exit;
    }
} catch (PDOException $e) {
    echo "Error fetching item: " . htmlspecialchars($e->getMessage());
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve and sanitize form data
    $hs_code = trim($_POST['hs_code']);
    $spec_code = trim($_POST['spec_code']);
    $description = trim($_POST['description']);
    $common_name = trim($_POST['common_name']);
    $quantity = trim($_POST['quantity']);
    $quantity_code = trim($_POST['quantity_code']);
    $packages_type_code = trim($_POST['packages_type_code']);
    $net_weight = trim($_POST['net_weight']);
    $weight_unit_code = trim($_POST['weight_unit_code']);
    $unit_price = trim($_POST['unit_price']);
    $invoice_amount = trim($_POST['invoice_amount']);
    $invoice_amount_birr = trim($_POST['invoice_amount_birr']);
    $country_of_origin = trim($_POST['country_of_origin']);
    $expire_date = trim($_POST['expire_date']);

    // Prevent past dates in expiration date
    if ($expire_date < date('Y-m-d')) {
        echo "<script>alert('Expiration date cannot be in the past.'); window.history.back();</script>";
        exit;
    }

    // Update the item in the database
    try {
        $stmt = $pdo->prepare("UPDATE items SET 
            hs_code = ?, 
            spec_code = ?, 
            description = ?, 
            common_name = ?, 
            quantity = ?, 
            quantity_code = ?, 
            packages_type_code = ?, 
            net_weight = ?, 
            weight_unit_code = ?, 
            unit_price = ?, 
            invoice_amount = ?, 
            invoice_amount_birr = ?, 
            country_of_origin = ?, 
            expire_date = ? 
            WHERE id = ?");

        $stmt->execute([
            $hs_code, $spec_code, $description, $common_name, $quantity, 
            $quantity_code, $packages_type_code, $net_weight, 
            $weight_unit_code, $unit_price, $invoice_amount, 
            $invoice_amount_birr, $country_of_origin, $expire_date, $item_id
        ]);

        echo "<script>alert('Item updated successfully!'); window.location.href='view_profile_item.php';</script>";
        exit;
    } catch (PDOException $e) {
        echo "Error updating item: " . htmlspecialchars($e->getMessage());
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Item</title>
    <link rel="stylesheet" href="create_item.css">
</head>
<body>
    <h2>Edit Item</h2>
    <form action="" method="POST">
        <label for="hs_code">HS Code:</label>
        <input type="text" id="hs_code" name="hs_code" value="<?php echo htmlspecialchars($item['hs_code']); ?>" required>

        <label for="spec_code">Specification Code:</label>
        <input type="text" id="spec_code" name="spec_code" value="<?php echo htmlspecialchars($item['spec_code']); ?>" required>

        <label for="description">Description of Goods:</label>
        <input type="text" id="description" name="description" value="<?php echo htmlspecialchars($item['description']); ?>" required>

        <label for="common_name">Common Name:</label>
        <input type="text" id="common_name" name="common_name" value="<?php echo htmlspecialchars($item['common_name']); ?>" required>

        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>

        <label for="quantity_code">Quantity Code:</label>
        <input type="text" id="quantity_code" name="quantity_code" value="<?php echo htmlspecialchars($item['quantity_code']); ?>" required>

        <label for="packages_type_code">Package Type Code:</label>
        <input type="text" id="packages_type_code" name="packages_type_code" value="<?php echo htmlspecialchars($item['packages_type_code']); ?>" required>

        <label for="net_weight">Net Weight:</label>
        <input type="number" id="net_weight" name="net_weight" value="<?php echo htmlspecialchars($item['net_weight']); ?>" required>

        <label for="weight_unit_code">Weight Unit Code:</label>
        <input type="text" id="weight_unit_code" name="weight_unit_code" value="<?php echo htmlspecialchars($item['weight_unit_code']); ?>" required>

        <label for="unit_price">Unit Price:</label>
        <input type="number" step="0.01" id="unit_price" name="unit_price" value="<?php echo htmlspecialchars($item['unit_price']); ?>" required>

        <label for="invoice_amount">Invoice Amount:</label>
        <input type="number" step="0.01" id="invoice_amount" name="invoice_amount" value="<?php echo htmlspecialchars($item['invoice_amount']); ?>" required>

        <label for="invoice_amount_birr">Invoice Amount (Birr):</label>
        <input type="number" step="0.01" id="invoice_amount_birr" name="invoice_amount_birr" value="<?php echo htmlspecialchars($item['invoice_amount_birr']); ?>" required>

        <label for="country_of_origin">Country of Origin:</label>
        <input type="text" id="country_of_origin" name="country_of_origin" value="<?php echo htmlspecialchars($item['country_of_origin']); ?>" required>

        <label for="expire_date">Expire Date:</label>
        <input type="date" id="expire_date" name="expire_date" value="<?php echo htmlspecialchars($item['expire_date']); ?>" required min="<?php echo date('Y-m-d'); ?>">

        <input type="submit" value="Update Item">
    </form>
</body>
</html>
