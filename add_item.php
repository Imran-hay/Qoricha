<?php
session_start();

// Include database configuration
require 'config.php'; 
require 'storeman_sidebar.php'; 


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $hs_code = $_POST['hs_code'];
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $stock = $_POST['stock'];

    $unit_price = $_POST['unit_price'];
    $expire_date = $_POST['expire_date'];

    // Prepare the insert statement
    $stmt = $pdo->prepare("
    INSERT INTO items 
    (hs_code, item_id, item_name, stock, unit_price, expire_date) 
    VALUES (?, ?, ?, ?, ?, ?)
");


    // Execute the statement with parameters
    if ($stmt->execute([$hs_code, $item_id, $item_name, $stock, $unit_price, $expire_date])) {
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
                    <label for="item_id">Item Id:</label>
                    <input type="text" id="item_id" name="item_id" required>
                </div>
         
                <div class="form-group">
                    <label for="item_name">Item Name:</label>
                    <input type="text" id="item_name" name="item_name" required>
                </div>
             
             
                <!-- Right Column -->
              
              
            
              
                <div class="form-group">
                    <label for="Stock">Stock:</label>
                    <input type="number" id="stock" name="stock" required>
                </div>

                <div class="form-group">
                    <label for="unit_price">Unit Price:</label>
                    <input type="number" id="unit_price" name="unit_price" required>
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