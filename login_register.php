<?php
session_start();
require_once 'config.php';
require_once 'send_verification.php';


if (isset($_POST['register'])) {
    $name     = trim($_POST['name']     ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $role     = $_POST['role'] ?? '';

    
    if (!$name || !$email || !$password || !$role) {
        $_SESSION['register_error'] = 'Please fill in all fields.';
        $_SESSION['active_form']    = 'register';
        header("Location: login.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = 'Please enter a valid email address.';
        $_SESSION['active_form']    = 'register';
        header("Location: login.php");
        exit();
    }

    
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['register_error'] = 'This email is already registered.';
        $_SESSION['active_form']    = 'register';
        header("Location: login.php");
        exit();
    }
    $stmt->close();

    
    $banCheck = $conn->prepare("SELECT id FROM banned_users WHERE email = ?");
    $banCheck->bind_param("s", $email);
    $banCheck->execute();
    $banCheck->store_result();

    if ($banCheck->num_rows > 0) {
        $_SESSION['register_error'] = 'This account has been permanently suspended.';
        $_SESSION['active_form']    = 'register';
        header("Location: login.php");
        exit();
    }
    $banCheck->close();

    
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $token  = bin2hex(random_bytes(32)); 

    
    $insert = $conn->prepare("
        INSERT INTO users (name, email, password, role, email_verified, verification_token)
        VALUES (?, ?, ?, ?, 0, ?)
    ");
    $insert->bind_param("sssss", $name, $email, $hashed, $role, $token);

   if ($insert->execute()) {
    sendVerificationEmail($email, $name, $token);
    $_SESSION['register_success'] = 'Account created! Please check your email to verify your account.';
} else {
    $_SESSION['register_error'] = 'Something went wrong. Please try again.';
    $_SESSION['active_form']    = 'register';
}
    $insert->close();

    header("Location: login.php");
    exit();
}


if (isset($_POST['login'])) {
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {

            
            if (!$user['email_verified']) {
                $_SESSION['login_error'] = 'Please verify your email before logging in. Check your inbox.';
                $_SESSION['active_form'] = 'login';
                header("Location: login.php");
                exit();
            }

            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            if ($user['role'] === 'seller') {
                header("Location: seller_page.php");
            } else {
                header("Location: buyer_page.php");
            }
            exit();
        }
    }

    $_SESSION['login_error'] = 'Incorrect email or password.';
    $_SESSION['active_form'] = 'login';
    header("Location: login.php");
    exit();
}


