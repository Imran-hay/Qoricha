<?php
session_start();

// Include database configuration
require 'config.php'; 
require 'sidebar.php'; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $hs_code = $_POST['hs_code'];
    $spec_code = $_POST['spec_code'];
    $description = $_POST['description'];
    $common_name = $_POST['common_name'];
    $quantity = $_POST['quantity'];
    $quantity_code = $_POST['quantity_code'];
    $packages = $_POST['packages'];
    $packages_type_code = $_POST['packages_type_code'];
    $net_weight = $_POST['net_weight'];
    $gross_weight = $_POST['gross_weight'];
    $weight_unit_code = $_POST['weight_unit_code'];
    $unit_price = $_POST['unit_price'];
    $invoice_amount = $_POST['invoice_amount'];
    $invoice_amount_birr = $_POST['invoice_amount_birr'];
    $country_of_origin = $_POST['country_of_origin'];
    $expire_date = $_POST[expire_date];

    // Prepare the insert statement
    $stmt = $pdo->prepare("
    INSERT INTO items 
    (hs_code, spec_code, description, common_name, quantity, quantity_code, packages, packages_type_code, net_weight, gross_weight, weight_unit_code, unit_price, invoice_amount, invoice_amount_birr, country_of_origin, expire_date) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");


    // Execute the statement with parameters
    if ($stmt->execute([$hs_code, $spec_code, $description, $common_name, $quantity, $quantity_code, $packages, $packages_type_code, $net_weight, $gross_weight, $weight_unit_code, $unit_price, $invoice_amount, $invoice_amount_birr, $country_of_origin, $expire_date])) {
        echo "<script>alert('Item created successfully!'); window.location.href='create_item.php';</script>";
    } else {
        echo "<script>alert('Error creating item. Please try again.'); window.location.href='create_item.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="create_item.css">
</head>
<body>
    <header>
    </header>
    <main>
        <div class="form-container">
            <h2>Create Item Profile</h2>
            <form action="" method="POST">
                <!-- Left Column -->
                <div class="form-group">
                    <label for="hs_code">HS Code:</label>
                    <input type="text" id="hs_code" name="hs_code" required>
                </div>
                <div class="form-group">
                    <label for="spec_code">Specification Code:</label>
                    <input type="text" id="spec_code" name="spec_code" required>
                </div>
                <div class="form-group">
                    <label for="description">Description of Goods:</label>
                    <input type="text" id="description" name="description" required>
                </div>
                <div class="form-group">
                    <label for="common_name">Common Name:</label>
                    <input type="text" id="common_name" name="common_name" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
                <div class="form-group">
                    <label for="quantity_code">Quantity Code:</label>
                    <input type="text" id="quantity_code" name="quantity_code" required>
                </div>
                <div class="form-group">
                    <label for="packages">Number of Packages:</label>
                    <input type="number" id="packages" name="packages" required>
                </div>
                <div class="form-group">
                    <label for="packages_type_code">Packages Type Code:</label>
                    <input type="text" id="packages_type_code" name="packages_type_code" required>
                </div>

                <!-- Right Column -->
                <div class="form-group">
                    <label for="net_weight">Net Weight:</label>
                    <input type="number" id="net_weight" name="net_weight" required>
                </div>
                <div class="form-group">
                    <label for="gross_weight">Gross Weight:</label>
                    <input type="number" id="gross_weight" name="gross_weight" required>
                </div>
                <div class="form-group">
                    <label for="weight_unit_code">Weight Unit Code:</label>
                    <input type="text" id="weight_unit_code" name="weight_unit_code" required>
                </div>
                <div class="form-group">
                    <label for="unit_price">Unit Price:</label>
                    <input type="number" step="0.01" id="unit_price" name="unit_price" required>
                </div>
                <div class="form-group">
                    <label for="invoice_amount">Invoice Amount:</label>
                    <input type="number" step="0.01" id="invoice_amount" name="invoice_amount" required>
                </div>
                <div class="form-group">
                    <label for="invoice_amount_birr">Invoice Amount (Birr):</label>
                    <input type="number" step="0.01" id="invoice_amount_birr" name="invoice_amount_birr" required>
                </div>
                <div class="form-group">
                    <label for="country_of_origin">Country of Origin:</label>
                    <input type="text" id="country_of_origin" name="country_of_origin" required>
                </div>
                <div class="form-group">
    <label for="expire_date">Expire Date</label>
    <input type="date" id="expire_date" name="expire_date" required>
</div>


                <input type="submit" name="create_item" value="Create Item">
            </form>
        </div>
    </main>
    <script src="js/script.js"></script>
</body>
</html>