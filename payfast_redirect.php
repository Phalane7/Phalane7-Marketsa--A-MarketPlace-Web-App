<?php
require_once 'db.php';
require_once 'configg.php';
bootSession();
$user = mustBuyer('login.php');
$pdo  = db();

$orderId = (int)($_GET['order_id'] ?? 0);
if (!$orderId) die('Invalid order.');

$stmt = $pdo->prepare("
    SELECT o.*, p.title AS product_title
    FROM orders o
    JOIN products p ON p.id = o.product_id
    WHERE o.id = ? AND o.buyer_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $user['id']]);
$order = $stmt->fetch();
if (!$order) die('Order not found.');

$buyerStmt = $pdo->prepare("SELECT name, email FROM users WHERE id = ?");
$buyerStmt->execute([$user['id']]);
$buyer = $buyerStmt->fetch();

$nameParts = explode(' ', trim($buyer['name']), 2);
$firstName = $nameParts[0];
$lastName  = $nameParts[1] ?? '';

$data = [
    'merchant_id'   => PF_MERCHANT_ID,
    'merchant_key'  => PF_MERCHANT_KEY,
    'return_url'    => SITE_URL . '/payfast_return.php',
    'cancel_url'    => SITE_URL . '/payfast_cancel.php?order_id=' . $orderId,
    'notify_url'    => SITE_URL . '/payfast_notify.php',
    'name_first'    => $firstName,
    'name_last'     => $lastName,
    'email_address' => $buyer['email'],
    'm_payment_id'  => $order['order_ref'],
    'amount'        => number_format((float)$order['total_amount'], 2, '.', ''),
    'item_name'     => substr($order['product_title'], 0, 100),
];

// Generate signature
$pfString = '';
foreach ($data as $key => $val) {
    if ($val !== '') {
        $pfString .= $key . '=' . urlencode(trim($val)) . '&';
    }
}
$pfString .= 'passphrase=' . urlencode(trim(PF_PASSPHRASE));
$data['signature'] = md5($pfString);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayFast…</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body{font-family:'Poppins',sans-serif;display:flex;align-items:center;
             justify-content:center;min-height:100vh;background:#f6f6f9;}
        .box{text-align:center;}
        .spinner{width:44px;height:44px;border:4px solid #ddd;border-top-color:#1a7a4a;
                 border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 16px;}
        @keyframes spin{to{transform:rotate(360deg)}}
        p{color:#666;font-size:14px;}
    </style>
</head>
<body>
<div class="box">
    <div class="spinner"></div>
    <p>Redirecting you to PayFast to complete payment…</p>
    <form id="pf" action="<?= PF_URL ?>" method="POST">
        <?php foreach ($data as $k => $v): ?>
            <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($v) ?>">
        <?php endforeach; ?>
    </form>
</div>
<script>setTimeout(() => document.getElementById('pf').submit(), 800);</script>
</body>
</html>