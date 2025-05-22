<?php
session_start();
session_unset(); // Clear all session variables
session_destroy(); // Destroy the session

// Redirect to login page or homepage
header("Location: login.php"); // or "index.php" if no login
exit();
