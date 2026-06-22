<?php
require_once 'db.php';
require_once 'configg.php';
bootSession();
$user    = mustBuyer('login.php');
$pdo     = db();
$orderId = (int)($_GET['order_id'] ?? 0);

if ($orderId) {
    // Restore stock and cancel order
    $pdo->prepare("
        UPDATE products p JOIN orders o ON o.product_id = p.id
        SET p.stock_qty = p.stock_qty + o.quantity
        WHERE o.id = ? AND o.buyer_id = ? AND o.status = 'pending'
    ")->execute([$orderId, $user['id']]);

    $pdo->prepare("
        UPDATE orders SET status='cancelled'
        WHERE id=? AND buyer_id=? AND status='pending'
    ")->execute([$orderId, $user['id']]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Payment Cancelled — MarketSA</title>
    <meta http-equiv="refresh" content="3;url=buyer_page.php">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Poppins',sans-serif;display:flex;align-items:center;
             justify-content:center;min-height:100vh;background:#f6f6f9;}
        .box{text-align:center;background:#fff;border-radius:20px;padding:3rem 2.5rem;
             box-shadow:0 10px 40px rgba(0,0,0,.08);max-width:420px;}
        .icon{font-size:3.5rem;margin-bottom:1rem;}
        h2{font-size:1.4rem;font-weight:800;color:#e03131;margin-bottom:.5rem;}
        p{color:#666;font-size:.9rem;}
    </style>
</head>
<body>
<div class="box">
   
    <h2>Payment Cancelled</h2>
    <p>No payment was taken. Your cart items are still available.</p>
    <p style="margin-top:1rem;font-size:.8rem;color:#aaa;">Taking you back to MarketSA…</p>
</div>
</body>
</html>