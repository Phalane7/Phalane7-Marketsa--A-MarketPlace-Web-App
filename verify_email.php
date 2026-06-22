<?php
session_start();
require_once 'config.php';

$token = trim($_GET['token'] ?? '');

if (!$token) {
    $message = 'Invalid verification link.';
    $success = false;
} else {
    $stmt = $conn->prepare("
        SELECT id, name, email_verified 
        FROM users 
        WHERE verification_token = ? 
        LIMIT 1
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $message = 'This verification link is invalid or has already been used.';
        $success = false;
    } else {
        $user = $result->fetch_assoc();

        if ($user['email_verified']) {
            $message = 'Your email is already verified. You can log in.';
            $success = true;
        } else {
            // Mark as verified and clear token
            $update = $conn->prepare("
                UPDATE users 
                SET email_verified = 1, verification_token = NULL 
                WHERE id = ?
            ");
            $update->bind_param("i", $user['id']);
            $update->execute();
            $update->close();

            $message = 'Your email has been verified! You can now log in.';
            $success = true;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Email Verification — MarketSA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family:'Poppins',sans-serif; display:flex; align-items:center;
               justify-content:center; min-height:100vh; background:#f6f6f9; margin:0; }
        .box { text-align:center; background:#fff; border-radius:20px;
               padding:3rem 2.5rem; box-shadow:0 10px 40px rgba(0,0,0,.08);
               max-width:440px; width:90%; }
        .icon { font-size:3.5rem; margin-bottom:1rem; }
        h2 { font-size:1.4rem; font-weight:800; margin-bottom:.5rem;
             color: <?= $success ? '#1a7a4a' : '#e03131' ?>; }
        p  { color:#666; font-size:.9rem; margin-bottom:1.5rem; }
        a  { display:inline-block; background:#1a7a4a; color:#fff;
             text-decoration:none; padding:11px 28px; border-radius:50px;
             font-weight:700; font-size:.9rem; transition:.2s; }
        a:hover { background:#25a865; }
    </style>
</head>
<body>
<div class="box">
    <div class="icon"><?= $success ? '' : '' ?></div>
    <h2><?= $success ? 'Email Verified!' : 'Verification Failed' ?></h2>
    <p><?= htmlspecialchars($message) ?></p>
    <a href="login.php">Go to Login</a>
</div>
</body>
</html>