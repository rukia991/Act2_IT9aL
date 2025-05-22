<?php
include "../dbconnect.php";
session_start();

$error = '';
$success = '';

// Handle registration
if (isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if username exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $error = "Username already exists.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("ss", $username, $hashed);
        if ($stmt->execute()) {
            $success = "Registration successful. You can now log in.";
        } else {
            $error = "Registration failed. Try again.";
        }
    }
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();

    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header("Location: ../index.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Login</title>
    <link rel="stylesheet" href="../mycss.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    <div class="form-wrapper">
        <div class="page-container">
            <div class="login-box" id="loginBox">
                <h2 id="formTitle">Login</h2>

                <?php if ($error): ?>
                    <p style="color:red;"><?= $error ?></p>
                <?php elseif ($success): ?>
                    <p style="color:limegreen;"><?= $success ?></p>
                <?php endif; ?>

                <!-- Form slider container -->
                <div class="form-slider-container" id="formSlider">
                    <!-- Login Form -->
                    <form method="POST" id="loginForm" class="form-slide active">
                        <label>Username</label>
                        <input type="text" name="username" required>
                        <label>Password</label>
                        <input type="password" name="password" required>
                        <button type="submit" name="login"><i class="fas fa-sign-in-alt"></i> Login</button>
                    </form>

                    <!-- Register Form -->
                    <form method="POST" id="registerForm" class="form-slide">
                        <label>Username</label>
                        <input type="text" name="username" required>
                        <label>Password</label>
                        <input type="password" name="password" required>
                        <button type="submit" name="register"><i class="fas fa-user-plus"></i> Register</button>
                    </form>
                </div>
                <span class="link-button" onclick="toggleForm()">Don't have an account? Register</span>
            </div>
        </div>
    </div>

    <script>
        const toggle = document.querySelector('.link-button');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const formTitle = document.getElementById('formTitle');

        let showingLogin = true;

        function toggleForm() {
            if (showingLogin) {
                loginForm.classList.remove('active');
                loginForm.classList.add('exit');
                registerForm.classList.add('active');
                registerForm.classList.remove('exit');
                formTitle.textContent = 'Register';
                toggle.textContent = 'Already have an account? Login';
            } else {
                registerForm.classList.remove('active');
                registerForm.classList.add('exit');
                loginForm.classList.add('active');
                loginForm.classList.remove('exit');
                formTitle.textContent = 'Login';
                toggle.textContent = "Don't have an account? Register";
            }
            showingLogin = !showingLogin;
        }

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (e) {
                const username = form.querySelector('input[name="username"]').value.trim();
                const password = form.querySelector('input[name="password"]').value.trim();

                if (username.length < 3 || password.length < 6) {
                    e.preventDefault();
                    alert("Username must be at least 3 characters, password at least 6.");
                }
            });
        });

    </script>
</body>

</html>