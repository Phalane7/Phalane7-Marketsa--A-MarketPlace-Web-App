<?php

session_start();

$errors = [
    'login'    => $_SESSION['login_error']    ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$activeForm      = $_SESSION['active_form']      ?? 'login';
$registerSuccess = $_SESSION['register_success'] ?? '';

session_unset();

function showError($error) {
    return !empty($error) ? "<p class='error-message'>$error</p>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $formName === $activeForm ? 'active' : '';
}


?>




<!DOCTYPE html>
<html lang="en">
   <head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="M">
    <title>MarketSA</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">

</head>
<body class="loginpage">
  <div class="container">
    <div class="form-box <?= isActiveForm('login', $activeForm); ?>" id="login-form">
          
    <?php if ($registerSuccess): ?>
        <div style="background:#eaf6ef;border:1px solid #b2dfca;border-radius:10px;
                    padding:12px 16px;margin-bottom:16px;color:#1a7a4a;
                    font-size:14px;text-align:center;">
             <?= htmlspecialchars($registerSuccess) ?>
        </div>
    <?php endif; ?>
        <form action="login_register.php" method="post">
            <h2 style="font-size: 34px; text-align: center; margin-bottom: 20px;">Users Login</h2>
            <?= showError($errors['login']); ?>
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <button type="sumbit" name="login">Login</button>
            <p style="font-size: 14.5px; text-align: center; margin-bottom: 10px;">Don't have an account? <a href="#" onclick="showForm('register-form')" style="color: #7494ec; text-decoration: none; "> Register</a></p>
        </form>
    </div>

 <div class="form-box <?= isActiveForm('register', $activeForm); ?>" id="register-form">
        <form action="login_register.php" method="post">
            <h2 style="font-size: 34px; text-align: center; margin-bottom: 20px;">Register</h2>
            <?= showError($errors['register']); ?>
            <input type="text" name="name" placeholder="Name">
            <input type="email" name="email" placeholder="Email">
            <input type="password" name="password" placeholder="Password">
            <select name="role" required>
                <option value="">--Select Role--</option>
                <option value="seller">Seller</option>
                <option value="buyer">Buyer</option>
            </select>
            <button type="sumbit" name="register">Register</button>
            <p style="font-size: 14.5px; text-align: center; margin-bottom: 10px;">Already have an account? <a href="#" onclick="showForm('login-form')"       style="color: #7494ec; text-decoration: none; "> Login</a></p>
        </form>
    </div>

  </div>

    <script src="script.js"></script>
</body>
</html>