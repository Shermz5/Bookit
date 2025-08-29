<?php
include 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch admin info (assume password_hash column)
    $stmt = $conn->prepare("SELECT id, username, password_hash FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($admin = $result->fetch_assoc()) {
        if (password_verify($password, $admin['password_hash'])) {
            // Set admin session
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            $stmt->close();
            $conn->close();

            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Wrong password
            $stmt->close();
            $conn->close();
            header("Location: admin_login.php?error=invalid");
            exit();
        }
    } else {
        // User not found
        $stmt->close();
        $conn->close();
        header("Location: admin_login.php?error=invalid");
        exit();
    }
}
?>

<!-- Your HTML form goes here -->


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: rgb(250, 250, 250);
      color: rgb(29, 36, 43);
      margin: 0;
      padding: 20px;
    }

    .login-container {
      max-width: 600px;
      margin: auto;
      padding: 20px;
      background-color: white;
      box-shadow: 0 0 10px rgba(0, 119, 192, 0.2);
      border-radius: 10px;
    }

    h2 {
      text-align: center;
      color: rgb(0, 119, 192);
    }

    .error-message {
      color: red;
      text-align: center;
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin: 10px 0 5px;
    }

    input[type="text"], input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    .checkbox-container {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }

    .checkbox-container input {
      margin-right: 10px;
    }

    .actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.9em;
    }

    button {
      background-color: rgb(0, 119, 192);
      color: white;
      padding: 10px;
      width: 100%;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 15px;
    }

    .social-login {
      text-align: center;
      margin-top: 20px;
    }

    .social-icons {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin-top: 10px;
    }

    .social-icons button {
      background: transparent;
      border: none;
      cursor: pointer;
    }

    .social-icons svg {
      width: 24px;
      height: 24px;
      fill: #000;
    }

    a {
      color: rgb(0, 119, 192);
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>

    <div id="error-message" class="error-message"></div>

    <form action="admin_login.php" method="POST" onsubmit="return validateForm()">
      <label for="username">Admin Username</label>
      <input type="text" id="username" name="username" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <div class="actions">
        <div class="checkbox-container">
          <input type="checkbox" id="remember" name="remember" />
          <label for="remember">Remember Me</label>
        </div>
        <a href="#">Forgot Password?</a>
      </div>

      <button type="submit">Login</button>
    </form>

    

    
      <p>I'm a Client, <a href="login.html">Login</a></p>
    </div>
  </div>
   <script>
    // Optional: Show error from PHP via query string
    const params = new URLSearchParams(window.location.search);
    if (params.get("error") === "invalid") {
      document.getElementById("error-message").innerText = "Invalid username or password.";
    }

    function validateForm() {
      const username = document.getElementById("username").value.trim();
      const password = document.getElementById("password").value.trim();
      if (username === "" || password === "") {
        document.getElementById("error-message").innerText = "Please fill in all fields.";
        return false;
      }
      return true;
    }
  </script>

</body>
</html>

