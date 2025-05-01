<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Register</title>
    <style>
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .form-row {
            margin-bottom: 15px;
        }
        .column {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .role-selection {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .role-selection label {
            flex: 1;
            text-align: center;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #0a888f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: rgb(4, 70, 73);
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Register</h2>
        <form action="register_employee.php" method="post">
            <div class="form-row">
                <div class="column">
                    <input type="text" name="fullname" placeholder="Full Name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <input type="text" name="phone" placeholder="Phone Number" required>
                </div>
                <div class="column">
                    <input type="text" name="tin" placeholder="TIN Number" required>
                    <input type="text" name="region" placeholder="Region" required>
                    <input type="text" name="address" placeholder="Address" required>
                </div>
            </div>
            <div class="form-row">
                <input type="text" name="joining_date" placeholder="Joining Date" value="<?php echo date('Y-m-d'); ?>" readonly>
            </div>
            <div class="form-row role-selection">
                <label><input type="radio" name="role" value="agent" required> Agent</label>
                <label><input type="radio" name="role" value="cashier" required> Cashier</label>
                <label><input type="radio" name="role" value="storeman" required> Storeman</label>
            </div>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>