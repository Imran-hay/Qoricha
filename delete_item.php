<?php
session_start();
require 'config.php'; // Include your database configuration

// Check if the ID is provided
if (isset($_GET['id'])) {
    $item_id = intval($_GET['id']); // Sanitize the input

    // Prepare the delete statement
    $stmt = $pdo->prepare("DELETE FROM items WHERE id = :id");
    $stmt->bindParam(':id', $item_id, PDO::PARAM_INT);

    // Execute the delete statement
    if ($stmt->execute()) {
        // Redirect back to the item list with a success message
        $_SESSION['message'] = "Item deleted successfully.";
    } else {
        // Redirect back with an error message
        $_SESSION['message'] = "Error deleting item.";
    }
} else {
    $_SESSION['message'] = "No item ID provided.";
}

// Redirect to the view profile items page
header("Location: view_profile_item.php");
exit;
?>