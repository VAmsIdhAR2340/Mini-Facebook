<?php
session_start();
include 'database.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page or display an error message
    header("Location: index.php");
    exit();
}

// Check if the logout button is clicked
if (isset($_POST['logout'])) {
    // Unset all session variables
    $_SESSION = array();
    // Destroy the session
    session_destroy();
    // Redirect to the login page or any other page
    header("Location: index.php");
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

// Check if form is submitted for adding/editing post
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add'])) {
        // Validate and sanitize form data
        $message = htmlspecialchars($_POST['message']);

        // Get the user ID from the session
        $userId = $_SESSION['user_id'];

        // Prepare SQL statement as a prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO posts (userId, message, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->bindParam(2, $message, PDO::PARAM_STR);
        
        // Execute SQL statement
        try {
            $stmt->execute();
            // Redirect to prevent form resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['edit'])) {
        // Handle editing of posts
        $postId = $_POST['postId'];
        $newMessage = htmlspecialchars($_POST['newMessage']);

        // Prepare SQL statement to update the post
        $stmt = $conn->prepare("UPDATE posts SET message = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bindParam(1, $newMessage, PDO::PARAM_STR);
        $stmt->bindParam(2, $postId, PDO::PARAM_INT);

        // Execute SQL statement
        try {
            $stmt->execute();
            // Redirect to prevent form resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete'])) {
        // Handle deleting of posts
        $postId = $_POST['postId'];

        // Prepare SQL statement to delete the post
        $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->bindParam(1, $postId, PDO::PARAM_INT);

        // Execute SQL statement
        try {
            $stmt->execute();
            // Redirect to prevent form resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    } elseif (isset($_POST['add_comment'])) {
        // Handle adding comments
        $postId = $_POST['postId'];
        $comment = htmlspecialchars($_POST['comment']);
        $userId = $_SESSION['user_id'];

        // Prepare SQL statement to insert comment
        $stmt = $conn->prepare("INSERT INTO comments (postId, userId, comment, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bindParam(1, $postId, PDO::PARAM_INT);
        $stmt->bindParam(2, $userId, PDO::PARAM_INT);
        $stmt->bindParam(3, $comment, PDO::PARAM_STR);

        // Execute SQL statement
        try {
            $stmt->execute();
            // Redirect to prevent form resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
    if (isset($_POST['delete_account'])) {
        $userId = $_SESSION['user_id'];
        
        // Prepare SQL statement to delete the user account
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);

        // Execute SQL statement
        try {
            $stmt->execute();
            echo '<script>alert("Account Deleted Scuccessfully..");</script>';
            header("Location: logout.php");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

}

// Fetch posts with user information from the database
try {
    $stmt = $conn->prepare("SELECT posts.id, posts.message, posts.created_at, users.username, posts.userId FROM posts INNER JOIN users ON posts.userId = users.id ORDER BY posts.created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch comments for each post
foreach ($posts as &$post) {
    try {
        $stmt = $conn->prepare("SELECT comments.comment, comments.created_at, users.username FROM comments INNER JOIN users ON comments.userId = users.id WHERE comments.postId = ? ORDER BY comments.created_at ASC");
        $stmt->bindParam(1, $post['id'], PDO::PARAM_INT);
        $stmt->execute();
        $post['comments'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Post</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .post {
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
        }

        .post h3 {
            margin-top: 0;
        }

        .post p {
            margin-bottom: 10px;
        }

        .comment {
            margin-left: 20px;
            font-style: italic;
        }

        .comment p {
            margin: 5px 0;
        }

        .comment .author {
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 10px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
        }

        .form-group textarea {
            width: 100%;
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 3px;
        }

        .form-group input[type="submit"] {
            padding: 5px 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .comment {
    margin-bottom: 10px;
}

.comment .author {
    font-weight: bold;
}

.comment p {
    margin: 5px 0;
}
    </style>
</head>
<body>
    <h2>Upload Post</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <textarea name="message" rows="4" cols="50" placeholder="Enter your message" required></textarea><br>
        <input type="submit" name="add" value="Upload">
    </form>
    
    <h2>Recent Posts</h2>
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <strong><?php echo htmlspecialchars($post['username']); ?>:</strong>
            <p><?php echo htmlspecialchars($post['message']); ?> - <?php echo htmlspecialchars($post['created_at']); ?></p>
            <ul>
                <?php foreach ($post['comments'] as $comment): ?>
                     <div class="comment">
        <span class="author"><?php echo htmlspecialchars($comment['username']); ?>:</span>
        <p><?php echo htmlspecialchars($comment['comment']); ?> - <?php echo htmlspecialchars($comment['created_at']); ?></p>
    </div>
                <?php endforeach; ?>
            </ul>
            <form class="form-group" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <input type="hidden" name="postId" value="<?php echo $post['id']; ?>">
                <textarea name="comment" rows="2" cols="30" placeholder="Add a comment" required></textarea>
                <input type="submit" name="add_comment" value="Add Comment">
            </form>
            <?php if ($_SESSION['user_id'] == $post['userId']): ?>
                <form class="form-group" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <input type="hidden" name="postId" value="<?php echo $post['id']; ?>">
                    <input type="text" name="newMessage" value="<?php echo htmlspecialchars($post['message']); ?>">
                    <button type="submit" name="edit">Edit</button>
                    <button type="submit" name="delete">Delete</button>
                </form>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
    
    <a href="logout.php">Logout</a>
    <a href = "admin_toggle.php">Admin</a>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <button type="submit" name="delete_account">Delete Account</button>
    </form>
</html>

