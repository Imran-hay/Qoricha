<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

require 'config.php';
require 'sidebar.php'; // Assuming you have an admin sidebar

$message = "";

// Check if there's a message in the session
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Remove the message from the session
}

// Handle form submission for adding a new category
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_category'])) {
        $category_name = $_POST['category_name'];
        $description = $_POST['description'];

        try {
            $stmt = $pdo->prepare("INSERT INTO expense_categories (category_name, description) VALUES (?, ?)");
            $stmt->execute([$category_name, $description]);
            $message = "Category added successfully!";
        } catch (PDOException $e) {
            $message = "Error adding category: " . $e->getMessage();
        }
    }

    // Handle edit category
    if (isset($_POST['edit_category'])) {
        $category_id = $_POST['category_id'];
        $category_name = $_POST['category_name'];
        $description = $_POST['description'];

        try {
            $stmt = $pdo->prepare("UPDATE expense_categories SET category_name = ?, description = ? WHERE category_id = ?");
            $stmt->execute([$category_name, $description, $category_id]);
            $message = "Category updated successfully!";
        } catch (PDOException $e) {
            $message = "Error updating category: " . $e->getMessage();
        }
    }

    // Handle delete category
    if (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];

        try {
            $stmt = $pdo->prepare("DELETE FROM expense_categories WHERE category_id = ?");
            $stmt->execute([$category_id]);
            $message = "Category deleted successfully!";
        } catch (PDOException $e) {
            $message = "Error deleting category: " . $e->getMessage();
        }
    }
}

// Fetch all expense categories
try {
    $stmt = $pdo->prepare("SELECT * FROM expense_categories ORDER BY category_name");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Error fetching categories: " . $e->getMessage();
    $categories = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Expense Categories</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" integrity="sha512-9usAa10IRO0HhonpyAIVpjrylPvoDwiPUiKdWk5t3PyolY1cOd4DSE0Ga+ri4AuTroPR5aQvXU9xC6qOPnzFeg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .content {
            margin-left: 280px;
            padding: 30px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }
        .container {
            
            margin-left: 120px; /* Adjust for sidebar width */
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        h1 {
            margin-bottom: 20px;
            color: #007bff;
            text-align: center;
        }

        .message {
            text-align: center;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            color: #fff;
        }

        .success {
            background-color: #27ae60;
        }

        .error {
            background-color: #e74c3c;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        /* Add Category Form (Initially Hidden) */
        .add-category-form {
            display: none; /* Initially hidden */
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .edit-category-form {
            display: none;
            margin-bottom: 15px;
            padding: 20px;
            border-radius: 8px;
            background-color: #ecf0f1;
        }

        .add-category-form h2, .edit-category-form h2 {
            color: #34495e;
            margin-bottom: 20px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #34495e;
            font-weight: bold;
        }

        .form-group input[type="text"],
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            font-size: 16px;
            color: #34495e;
            box-sizing: border-box;
        }

        .form-group textarea {
            height: 100px;
            resize: vertical;
        }

        .add-category-form button[type="submit"], .edit-category-form button[type="submit"] {
            background-color: #3498db;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .add-category-form button[type="submit"]:hover, .edit-category-form button[type="submit"]:hover {
            background-color: #2980b9;
        }

        /* Category List Table */
        .category-list {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .category-list th,
        .category-list td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .category-list th {
            background-color: #3498db;
            color: #fff;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-list tbody tr:hover {
            background-color: #f0f0f0;
        }

        .category-list .actions {
            text-align: center;
        }

        .category-list .actions a {
            display: inline-block;
            margin: 0 5px;
            color: #fff;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .category-list .actions .edit {
            background-color: #f39c12;
        }

        .category-list .actions .delete {
            background-color: #e74c3c;
        }

        .category-list .actions a:hover {
            opacity: 0.8;
        }

        /* Add Category Button */
        .add-category-button {
            background-color: #4361ee;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
            display: inline-block;
            text-decoration: none;
        }

        .add-category-button:hover {
            background-color: #219653;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 20px;
            }

            .category-list {
                overflow-x: auto;
            }

            .category-list th,
            .category-list td {
                white-space: nowrap;
            }
        }
    </style>
    <script>
        function toggleAddCategoryForm() {
            var form = document.querySelector('.add-category-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
            // Hide the edit form if it's open
            var editForm = document.querySelector('.edit-category-form');
            if (editForm) {
                editForm.style.display = 'none';
            }
        }

        function toggleEditCategoryForm(categoryId, categoryName, categoryDescription) {
            var form = document.querySelector('.edit-category-form');
            // If the form doesn't exist, create it
            if (!form) {
                form = document.createElement('div');
                form.classList.add('edit-category-form');
                form.innerHTML = `
                    <h2>Edit Category</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="category_id" value="${categoryId}">
                        <div class="form-group">
                            <label for="category_name">Category Name:</label>
                            <input type="text" id="category_name" name="category_name" value="${categoryName}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description:</label>
                            <textarea id="description" name="description">${categoryDescription}</textarea>
                        </div>
                        <button type="submit" name="edit_category">Update Category</button>
                    </form>
                `;
                document.querySelector('.container').appendChild(form);
            } else {
                // If the form exists, update the values
                form.querySelector('input[name="category_id"]').value = categoryId;
                form.querySelector('input[name="category_name"]').value = categoryName;
                form.querySelector('textarea[name="description"]').value = categoryDescription;
            }
            form.style.display = 'block';

            // Hide the add form if it's open
            var addForm = document.querySelector('.add-category-form');
            if (addForm) {
                addForm.style.display = 'none';
            }
        }

        function confirmDelete(categoryId) {
            if (confirm("Are you sure you want to delete this category?")) {
                // Create a form dynamically and submit it
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = 'view_categories.php'; // Submit to the same page

                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'delete_category';
                input.value = 'true';
                form.appendChild(input);

                var idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'category_id';
                idInput.value = categoryId;
                form.appendChild(idInput);

                document.body.appendChild(form); // Add to the document
                form.submit(); // Submit the form
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Manage Expense Categories</h1>

        <?php if ($message): ?>
            <div class="message <?php echo strpos($message, 'Error') === false ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <a href="#" class="add-category-button" onclick="toggleAddCategoryForm()">Add New Category</a>

        <div class="add-category-form">
            <h2>Add New Category</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="category_name">Category Name:</label>
                    <input type="text" id="category_name" name="category_name" required>
                </div>
                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description"></textarea>
                </div>
                <button type="submit" name="add_category">Add Category</button>
            </form>
        </div>

        <h2>Existing Categories</h2>
        <table class="category-list">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th class="actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="3">No categories found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                            <td><?php echo htmlspecialchars($category['description']); ?></td>
                            <td class="actions">
                                <a href="#" class="edit" onclick="toggleEditCategoryForm('<?php echo $category['category_id']; ?>', '<?php echo htmlspecialchars($category['category_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($category['description'], ENT_QUOTES); ?>')"><i class="fas fa-edit"></i> Edit</a>
                                <a href="#" class="delete" onclick="confirmDelete('<?php echo $category['category_id']; ?>')"><i class="fas fa-trash-alt"></i> Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>