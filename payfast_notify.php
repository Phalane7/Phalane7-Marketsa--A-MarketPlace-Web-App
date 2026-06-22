<?php
require_once 'db.php';
require_once 'configg.php';
$pdo = db();

$pfData = $_POST;

// Verify signature
$pfString = '';
foreach ($pfData as $key => $val) {
    if ($key !== 'signature' && $val !== '') {
        $pfString .= $key . '=' . urlencode(trim($val)) . '&';
    }
}
$pfString .= 'passphrase=' . urlencode(trim(PF_PASSPHRASE));
$signature = md5($pfString);

if ($signature !== ($pfData['signature'] ?? '')) {
    http_response_code(400);
    exit('Invalid signature');
}

// Update order if payment complete
if (($pfData['payment_status'] ?? '') === 'COMPLETE') {
    $orderRef = $pfData['m_payment_id'] ?? '';
    $pdo->prepare("
        UPDATE orders SET status='confirmed', updated_at=NOW()
        WHERE order_ref=? AND status='pending'
    ")->execute([$orderRef]);
}

http_response_code(200);
exit();