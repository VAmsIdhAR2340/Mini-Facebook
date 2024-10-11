<?php

// Function to toggle user status (active or blocked)
function toggleUserStatus($conn, $userId, $status) {
    try {
        // Prepare SQL statement
        $stmt = $conn->prepare("UPDATE users SET status = :status WHERE id = :userId");
        
        // Bind parameters
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':userId', $userId);
        
        // Execute the statement
        $stmt->execute();
        
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            // Return true if successful
            return true;
        } else {
            // Return false if no rows were affected
            return false;
        }
    } catch(PDOException $e) {
        // Return false if there's an error
        echo "Error: " . $e->getMessage();
        return false;
    }
}


?>


