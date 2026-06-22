


<!DOCTYPE html>
<html>
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="icon" type="image/jpg" href="MarketSA.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@24,400,0,0"/>
    <title>MarketSA Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="loginpage">

<div class="container">
    <div class="form-box active">

        <form action="admin_login_process.php" method="POST">

            <h2>MarketSA Admin Login</h2>

            <input
                type="email"
                name="email"
                placeholder="Admin Email"
                required
            >

            <input
                type="password"
                name="password"
                placeholder="Password"
                required
            >

            <button type="submit" name="admin_login">
                Login
            </button>

        </form>

    </div>
</div>

</body>
</html>