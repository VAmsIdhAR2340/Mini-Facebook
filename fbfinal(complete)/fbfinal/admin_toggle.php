<?php
session_start();
include 'admin_functions.php'; // Include the admin functions
include 'database.php'

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page or display an error message
    header("Location: index.php");
    exit();
}

// Check if the user is an admin
$isAdmin = ($_SESSION['username'] === 'vamsiv@gmail.com');

// If not an admin, redirect to home page
if (!$isAdmin) {
    echo "<script>alert('You are not an admin. Redirecting you to your dashboard.....')</script>"; 
    header("refresh:1; url=welcome.php");
    exit();
}



// Create connection using PDO with error handling
try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if form is submitted for toggling user status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_user'])) {
    $userId = $_POST['userId'];
    $status = $_POST['status']; // 'active' or 'blocked'

    // Call the toggleUserStatus function from admin_functions.php
    $result = toggleUserStatus($conn, $userId, $status);
    
    // Display message based on the result
    //if ($result) {
        //$message = ($status === 'active') ? 'User activated successfully.' : 'User blocked successfully.';
    //} else {
      //  $message = 'Error toggling user status.';
    //}
    //echo "<script>alert('$message')</script>";

    // Redirect to prevent form resubmission
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch list of users from the database
//$users = [];
try {
    $stmt = $conn->prepare("SELECT id, username, status, role FROM users WHERE role = 0");

    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Debugging: Print the fetched users
var_dump($users);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Toggle</title>
    <style>
        /* CSS styles */
    </style>
</head>
<body>
    <h2>Admin</h2>
    <table>
        <tr>
            <th>Username</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?php echo htmlspecialchars($user['username']); ?></td>
            <td><?php echo htmlspecialchars($user['status']); ?></td>
            <td>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="userId" value="<?php echo $user['id']; ?>">
                    <input type="hidden" name="status" value="<?php echo ($user['status'] === 'active') ? 'blocked' : 'active'; ?>">
                    <button type="submit" name="toggle_user"><?php echo ($user['status'] === 'active') ? 'Block' : 'Activate'; ?></button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <a href="welcome.php">Go back to home page</a>
</body>
</html>

