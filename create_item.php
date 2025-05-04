<?php
session_start();

// Include database configuration
require 'config.php';
require 'sidebar.php';


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
    <title>Create Item Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
        integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <style>
        /* General body and content styles */
        body {
            font-family: 'Nunito', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #e9ecef;
            color: #212529;
            display: flex;
            min-height: 100vh;
        }

        main {
            flex-grow: 1;
            padding: 20px;
            margin-left: 240px;
            /* Sidebar width */
            transition: margin-left 0.3s ease;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .form-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 100%;
            max-width: 600px;
        }

        h2 {
            margin-bottom: 20px;
            color: #343a40;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-sizing: border-box;
            color: #495057;
            font-size: 16px;
            transition: border-color 0.2s;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        input[type="date"]:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        input[type="submit"] {
            background-color: #0d6efd;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0b5ed7;
        }

        @media (max-width: 768px) {
            main {
                margin-left: 0;
                padding: 10px;
            }

            .form-container {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <main>
        <div class="form-container">
            <h2>Create Item Profile</h2>
            <form action="" method="POST">
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