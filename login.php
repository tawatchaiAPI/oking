<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log');

require_once __DIR__ . '/datascss/db_connection.php';

session_start();

function sendJsonResponse($data) {
    ob_end_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    error_log("Login attempt: Username = $username");

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result === false) {
        error_log("SQL Error: " . $conn->error);
        sendJsonResponse(["error" => "SQL Error: " . $conn->error]);
    } elseif ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        error_log("User found: ID = {$user['id']}, Role = {$user['role']}");

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            error_log("Password verified, Session set: user_id = {$user['id']}, role = {$user['role']}");

            if (isset($_POST['api']) && $_POST['api'] == 'true') {
                error_log("API mode: Sending JSON response");
                sendJsonResponse([
                    "message" => "Login successful",
                    "user" => [
                        "id" => $user['id'],
                        "username" => $user['username'],
                        "role" => $user['role']
                    ],
                    "token" => bin2hex(random_bytes(16))
                ]);
            } else {
                ob_end_clean();
                error_log("Non-API mode: Checking role for redirect");
                if ($user['role'] === 'admin') {
                    error_log("Role is admin, redirecting to index.php");
                    header("Location: index.php");
                } else {
                    error_log("Role is user, redirecting to dashboard.php");
                    header("Location: dashboard.php");
                }
                exit;
            }
        } else {
            $error = "Invalid username or password";
            error_log("Password verification failed");
            if (isset($_POST['api']) && $_POST['api'] == 'true') {
                sendJsonResponse(["error" => $error]);
            }
        }
    } else {
        $error = "Invalid username or password";
        error_log("No user found with username: $username");
        if (isset($_POST['api']) && $_POST['api'] == 'true') {
            sendJsonResponse(["error" => $error]);
        }
    }
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
</head>
<body>
   <!-- <div class="container">
        <h2>Login</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" id="loginForm">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div> --->
    <div class="wrapper">
         <div class="title">
            Login
         </div>
         <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST" id="loginForm">
            <div class="field">
               <input type="text" name="username"  required>
               <label>Username</label>
            </div>
            <div class="field">
               <input type="password" name="password" required>
               <label>Password</label>
            </div>
            <div class="field">
               <input type="submit" value="Login">
            </div>
         </form>
      </div>
    <script src="../assets/js/roleconfig.js"></script>
</body>
</html>