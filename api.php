<?php

require_once 'db.php';
bootSession();
header('Content-Type: application/json');

$action = trim($_GET['action'] ?? $_POST['action'] ?? '');
$pdo    = db();

switch ($action) {


case 'categories':
  $rows = $pdo->query("SELECT id, name FROM categories ORDER BY id")->fetchAll();
    json_out(true, '', ['categories' => $rows]);


case 'browse_products':
    $q      = '%' . trim($_GET['q'] ?? '') . '%';
    $cat    = (int)($_GET['category_id'] ?? 0);
    $prov   = trim($_GET['province'] ?? '');
    $min    = (float)($_GET['min'] ?? 0);
    $max    = (float)($_GET['max'] ?? 9999999);
    $limit  = min((int)($_GET['limit'] ?? 40), 80);
    $offset = (int)($_GET['offset'] ?? 0);

    $sql  = "
        SELECT p.id, p.title, p.price, p.condition_type, p.image_url,
               p.delivery_option, p.province, p.city, p.views, p.stock_qty,
               c.name AS category_name,
               u.name AS seller_name,
               COALESCE(sp.shop_name, u.name) AS shop_name,
               sp.rating AS seller_rating
        FROM products p
        JOIN categories c      ON c.id = p.category_id
        JOIN users u           ON u.id = p.seller_id
        LEFT JOIN seller_profiles sp ON sp.user_id = p.seller_id
        WHERE p.status = 'active'
          AND p.stock_qty > 0
          AND (p.title LIKE ? OR c.name LIKE ?)
          AND p.price BETWEEN ? AND ?
    ";
    $params = [$q, $q, $min, $max];

    if ($cat)  { $sql .= " AND p.category_id = ?"; $params[] = $cat; }
    if ($prov) { $sql .= " AND p.province = ?";    $params[] = $prov; }

    $sql .= " ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_out(true, '', ['products' => $stmt->fetchAll()]);


case 'product_detail':
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) json_out(false, 'ID required', [], 400);

    $pdo->prepare("UPDATE products SET views = views + 1 WHERE id = ?")->execute([$id]);

    $stmt = $pdo->prepare("
        SELECT p.*,
               c.name AS category_name,
               u.name AS seller_name,
               COALESCE(sp.shop_name, u.name) AS shop_name,
               COALESCE(sp.province, p.province) AS seller_province,
               sp.rating AS seller_rating,
               sp.total_reviews
        FROM products p
        JOIN categories c      ON c.id = p.category_id
        JOIN users u           ON u.id = p.seller_id
        LEFT JOIN seller_profiles sp ON sp.user_id = p.seller_id
        WHERE p.id = ? AND p.status != 'deleted'
        LIMIT 1
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) json_out(false, 'Not found', [], 404);
    json_out(true, '', ['product' => $product]);


case 'my_listings':
    $u = mustSeller();
    $status = trim($_GET['status'] ?? '');
    $q      = '%' . trim($_GET['q'] ?? '') . '%';

    $sql    = "
        SELECT p.*, c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.seller_id = ? AND p.status != 'deleted' AND p.title LIKE ?
    ";
    $params = [$u['id'], $q];
    if (in_array($status, ['active','pending','sold'])) {
        $sql .= " AND p.status = ?"; $params[] = $status;
    }
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    json_out(true, '', ['products' => $stmt->fetchAll()]);


case 'add_product':
    $u = mustSeller();
    $title    = xss($_POST['title']          ?? '');
    $desc     = xss($_POST['description']    ?? '');
    $price    = (float)($_POST['price']      ?? 0);
    $catId    = (int)($_POST['category_id']  ?? 0);
    $cond     = xss($_POST['condition_type'] ?? 'Brand New');
    $stock    = max(1,(int)($_POST['stock_qty'] ?? 1));
    $delivery = xss($_POST['delivery_option'] ?? 'Both');
    $province = xss($_POST['province']       ?? '');
    $city     = xss($_POST['city']           ?? '');
    $status   = xss($_POST['status']         ?? 'active');

    if (!$title || !$desc || $price <= 0 || !$catId)
        json_out(false, 'Please fill in all required fields.', [], 400);

    $imgUrl = null;
    if (!empty($_FILES['image']['tmp_name'])) {
        $imgUrl = saveImage($_FILES['image']);
        if (!$imgUrl) json_out(false, 'Image upload failed.', [], 400);
    }

    $stmt = $pdo->prepare("
        INSERT INTO products
          (seller_id,category_id,title,description,price,condition_type,
           stock_qty,delivery_option,province,city,image_url,status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->execute([$u['id'],$catId,$title,$desc,$price,$cond,
                    $stock,$delivery,$province,$city,$imgUrl,$status]);
    json_out(true, "\"$title\" listed!", ['product_id'=>(int)$pdo->lastInsertId()], 201);


case 'update_product':
    $u  = mustSeller();
    $id = (int)($_POST['product_id'] ?? 0);
    if (!$id) json_out(false, 'product_id required', [], 400);

   
    $own = $pdo->prepare("SELECT id FROM products WHERE id=? AND seller_id=? AND status!='deleted'");
    $own->execute([$id, $u['id']]);
    if (!$own->fetch()) json_out(false, 'Not found or access denied.', [], 403);

    $allowed = ['title','description','price','category_id','condition_type',
                'stock_qty','delivery_option','province','city','status'];
    $fields=[]; $params=[];
    foreach ($allowed as $f) {
        if (!isset($_POST[$f])) continue;
        $v = in_array($f,['price']) ? (float)$_POST[$f]
           : (in_array($f,['category_id','stock_qty']) ? (int)$_POST[$f] : xss($_POST[$f]));
        $fields[] = "$f=?"; $params[] = $v;
    }
    if (!empty($_FILES['image']['tmp_name'])) {
        $url = saveImage($_FILES['image']);
        if ($url) { $fields[] = "image_url=?"; $params[] = $url; }
    }
    if (empty($fields)) json_out(false, 'Nothing to update.');
    $params[] = $id; $params[] = $u['id'];
    $pdo->prepare("UPDATE products SET ".implode(',',$fields)." WHERE id=? AND seller_id=?")->execute($params);
    json_out(true, 'Listing updated.');


case 'delete_product':
    $u = mustSeller();
    $id = (int)($_POST['product_id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE products SET status='deleted' WHERE id=? AND seller_id=?");
    $stmt->execute([$id, $u['id']]);
    if (!$stmt->rowCount()) json_out(false, 'Not found.', [], 403);
    json_out(true, 'Listing deleted.');


case 'mark_sold':
    $u = mustSeller();
    $id = (int)($_POST['product_id'] ?? 0);
    $stmt = $pdo->prepare("UPDATE products SET status='sold',stock_qty=0 WHERE id=? AND seller_id=?");
    $stmt->execute([$id, $u['id']]);
    if (!$stmt->rowCount()) json_out(false, 'Not found.', [], 403);
    json_out(true, 'Marked as sold.');

case 'seller_stats':
    $u = mustSeller();
    $sid = $u['id'];

    $active = $pdo->prepare("SELECT COUNT(*) FROM products WHERE seller_id=? AND status='active'");
    $active->execute([$sid]); $activeCount = (int)$active->fetchColumn();

    $orders = $pdo->prepare("
        SELECT COUNT(*) AS total,
               COALESCE(SUM(CASE WHEN status='delivered' THEN total_amount ELSE 0 END),0) AS earnings
        FROM orders WHERE seller_id=?
    ");
    $orders->execute([$sid]); $oRow = $orders->fetch();

    $unread = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
    $unread->execute([$sid]); $unreadCount = (int)$unread->fetchColumn();

    $rating = $pdo->prepare("SELECT COALESCE(rating,0), COALESCE(total_reviews,0) FROM seller_profiles WHERE user_id=?");
    $rating->execute([$sid]); $rRow = $rating->fetch();

    
    $monthly = $pdo->prepare("
        SELECT DATE_FORMAT(created_at,'%b') AS month,
               DATE_FORMAT(created_at,'%Y-%m') AS ym,
               COALESCE(SUM(total_amount),0) AS revenue
        FROM orders
        WHERE seller_id=? AND status='delivered'
          AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY ym, month
        ORDER BY ym ASC
    ");
    $monthly->execute([$sid]);

    json_out(true, '', [
        'active_listings' => $activeCount,
        'total_orders'    => (int)$oRow['total'],
        'earnings'        => (float)$oRow['earnings'], 
        'unread_messages' => $unreadCount,
        'rating'          => $rRow ? (float)$rRow[0] : 0,
        'total_reviews'   => $rRow ? (int)$rRow[1] : 0,
        'monthly_revenue' => $monthly->fetchAll(),
    ]);


case 'seller_orders':
    $u      = mustSeller();
    $status = trim($_GET['status'] ?? '');
    $sql    = "
        SELECT o.*, p.title AS product_title, p.image_url AS product_image,
               u.name AS buyer_name
        FROM orders o
        JOIN products p ON p.id = o.product_id
        JOIN users    u ON u.id = o.buyer_id
        WHERE o.seller_id = ?
    ";
    $params = [$u['id']];
    if (in_array($status,['pending','confirmed','ready','delivered','cancelled'])) {
        $sql .= " AND o.status=?"; $params[] = $status;
    }
    $sql .= " ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql); $stmt->execute($params);
    json_out(true, '', ['orders' => $stmt->fetchAll()]);


case 'update_order_status':
    $u = mustSeller();
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $newStatus = xss($_POST['status'] ?? '');
    $allowed   = ['confirmed','ready','delivered','cancelled'];
    if (!in_array($newStatus, $allowed)) json_out(false, 'Invalid status.', [], 400);

    $stmt = $pdo->prepare("UPDATE orders SET status=? WHERE id=? AND seller_id=?");
    $stmt->execute([$newStatus, $orderId, $u['id']]);
    if (!$stmt->rowCount()) json_out(false, 'Order not found.', [], 403);
    json_out(true, "Order marked as $newStatus.");

case 'place_order':
    $u  = mustBuyer();
    $pid = (int)($_POST['product_id'] ?? 0);
    $qty = max(1,(int)($_POST['quantity'] ?? 1));

  
    $ps = $pdo->prepare("SELECT * FROM products WHERE id=? AND status='active' AND stock_qty>=? LIMIT 1");
    $ps->execute([$pid, $qty]); $product = $ps->fetch();
    if (!$product) json_out(false, 'Product not available or insufficient stock.', [], 400);
    if ($product['seller_id'] == $u['id']) json_out(false, "You can't buy your own product.", [], 400);

    $total   = round($product['price'] * $qty, 2);
    $ref     = makeRef();
    $method  = xss($_POST['payment_method'] ?? 'card');
    $last4   = $method === 'card' ? substr(preg_replace('/\D/','',$_POST['card_number']??''), -4) : null;

    $stmt = $pdo->prepare("
        INSERT INTO orders
          (order_ref,buyer_id,seller_id,product_id,quantity,unit_price,total_amount,
           ship_name,ship_street,ship_city,ship_province,ship_postal,ship_phone,
           bill_same,bill_name,bill_street,bill_city,bill_province,bill_postal,
           payment_method,card_last4,status)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,'pending')
    ");
    $stmt->execute([
        $ref, $u['id'], $product['seller_id'], $pid, $qty,
        $product['price'], $total,
        xss($_POST['ship_name']   ?? ''), xss($_POST['ship_street'] ?? ''),
        xss($_POST['ship_city']   ?? ''), xss($_POST['ship_province'] ?? ''),
        xss($_POST['ship_postal'] ?? ''), xss($_POST['ship_phone']   ?? ''),
        (int)($_POST['bill_same'] ?? 1),
        xss($_POST['bill_name']   ?? ''), xss($_POST['bill_street'] ?? ''),
        xss($_POST['bill_city']   ?? ''), xss($_POST['bill_province'] ?? ''),
        xss($_POST['bill_postal'] ?? ''),
        $method, $last4,
    ]);
    $orderId = (int)$pdo->lastInsertId();

   
    $pdo->prepare("UPDATE products SET stock_qty = stock_qty - ? WHERE id=?")->execute([$qty, $pid]);

   
    $shopStmt = $pdo->prepare("SELECT COALESCE(sp.shop_name, u.name) AS name
        FROM users u LEFT JOIN seller_profiles sp ON sp.user_id=u.id WHERE u.id=?");
    $shopStmt->execute([$product['seller_id']]); $shop = $shopStmt->fetchColumn();

    $autoMsg = "Hi! I've placed order #{$ref} for \"{$product['title']}\". Looking forward to hearing from you!";
    $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,product_id,message_text) VALUES (?,?,?,?)")
        ->execute([$u['id'], $product['seller_id'], $pid, $autoMsg]);

   
   $u1 = min($u['id'], $product['seller_id']);
$u2 = max($u['id'], $product['seller_id']);
$pdo->prepare("INSERT INTO conversations (user_one_id,user_two_id,last_message)
        VALUES (?,?,?) ON DUPLICATE KEY UPDATE last_message=VALUES(last_message),last_at=NOW()")
        ->execute([$u1,$u2,$autoMsg]);

    json_out(true, 'Order placed!', ['order_ref'=>$ref,'order_id'=>$orderId,'total'=>$total], 201);


case 'my_orders':
    $u = mustBuyer();
    $stmt = $pdo->prepare("
        SELECT o.*, p.title AS product_title, p.image_url AS product_image,
               COALESCE(sp.shop_name, u.name) AS shop_name
        FROM orders o
        JOIN products p         ON p.id = o.product_id
        JOIN users u            ON u.id = o.seller_id
        LEFT JOIN seller_profiles sp ON sp.user_id = o.seller_id
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$u['id']]);
    json_out(true, '', ['orders' => $stmt->fetchAll()]);


case 'conversations':
    $u = mustLogin();
    $me = (int)$u['id'];
    $stmt = $pdo->prepare("
        SELECT 
            c.id AS convo_id,
            c.last_message,
            c.last_at,
            IF(c.user_one_id=?, c.user_two_id, c.user_one_id) AS other_id,
            IF(c.user_one_id=?, u2.name, u1.name) AS other_name,
            IF(c.user_one_id=?,
               COALESCE(sp2.shop_name, u2.name),
               COALESCE(sp1.shop_name, u1.name)) AS other_shop,
            (SELECT COUNT(*) FROM messages m
             WHERE m.sender_id = IF(c.user_one_id=?, c.user_two_id, c.user_one_id)
               AND m.receiver_id = ?
               AND m.is_read = 0) AS unread_count
        FROM conversations c
        JOIN users u1 ON u1.id = c.user_one_id
        JOIN users u2 ON u2.id = c.user_two_id
        LEFT JOIN seller_profiles sp1 ON sp1.user_id = c.user_one_id
        LEFT JOIN seller_profiles sp2 ON sp2.user_id = c.user_two_id
        WHERE c.user_one_id=? OR c.user_two_id=?
        ORDER BY c.last_at DESC
    ");
    $stmt->execute([$me,$me,$me,$me,$me,$me,$me]);
    json_out(true, '', ['conversations' => $stmt->fetchAll()]);

case 'thread':
    $u       = mustLogin();
    $otherId = (int)($_GET['other_id'] ?? 0);
    if (!$otherId) json_out(false, 'other_id required', [], 400);

    $stmt = $pdo->prepare("
        SELECT m.id, m.sender_id, m.receiver_id, m.message_text, m.is_read, m.created_at,
               u.name AS sender_name
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE (m.sender_id=? AND m.receiver_id=?)
           OR (m.sender_id=? AND m.receiver_id=?)
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$u['id'], $otherId, $otherId, $u['id']]);
    $msgs = $stmt->fetchAll();

    $pdo->prepare("UPDATE messages SET is_read=1 WHERE sender_id=? AND receiver_id=? AND is_read=0")
        ->execute([$otherId, $u['id']]);

    $os = $pdo->prepare("SELECT u.name, COALESCE(sp.shop_name, u.name) AS shop_name
        FROM users u LEFT JOIN seller_profiles sp ON sp.user_id=u.id WHERE u.id=?");
    $os->execute([$otherId]);
    $other = $os->fetch();

    json_out(true, '', ['messages' => $msgs, 'other' => $other]);

case 'send_message':
    $u    = mustLogin();
    $to   = (int)($_POST['receiver_id'] ?? 0);
    $text = trim($_POST['message_text'] ?? '');

    if (!$to || !$text) json_out(false, 'receiver_id and message_text required', [], 400);
    if ($to === (int)$u['id']) json_out(false, "You can't message yourself.", [], 400);

    $rx = $pdo->prepare("SELECT id FROM users WHERE id=?");
    $rx->execute([$to]);
    if (!$rx->fetch()) json_out(false, 'Recipient not found.', [], 404);

    $pdo->prepare("INSERT INTO messages (sender_id,receiver_id,product_id,message_text) VALUES (?,?,NULL,?)")
        ->execute([$u['id'], $to, $text]);
    $mid = (int)$pdo->lastInsertId();

    $u1 = min((int)$u['id'], $to);
    $u2 = max((int)$u['id'], $to);
    $pdo->prepare("
        INSERT INTO conversations (user_one_id, user_two_id, last_message)
        VALUES (?,?,?)
        ON DUPLICATE KEY UPDATE last_message=VALUES(last_message), last_at=NOW()
    ")->execute([$u1, $u2, $text]);

    json_out(true, 'Sent.', ['message_id' => $mid, 'time' => date('H:i')], 201);

case 'unread_count':
    $u = mustLogin();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
    $stmt->execute([$u['id']]);
    json_out(true, '', ['unread' => (int)$stmt->fetchColumn()]);

case 'toggle_fav':
    $u  = mustBuyer();
    $pid = (int)($_POST['product_id'] ?? 0);
    if (!$pid) json_out(false, 'product_id required', [], 400);

    $check = $pdo->prepare("SELECT id FROM favourites WHERE user_id=? AND product_id=?");
    $check->execute([$u['id'], $pid]);
    if ($check->fetch()) {
        $pdo->prepare("DELETE FROM favourites WHERE user_id=? AND product_id=?")->execute([$u['id'],$pid]);
        json_out(true, 'Removed from favourites.', ['action'=>'removed']);
    } else {
        $pdo->prepare("INSERT INTO favourites (user_id,product_id) VALUES (?,?)")->execute([$u['id'],$pid]);
        json_out(true, 'Added to favourites!', ['action'=>'added']);
    }


case 'my_favourites':
    $u = mustBuyer();
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.condition_type, p.image_url,
               p.delivery_option, p.status,
               c.name AS category_name,
               COALESCE(sp.shop_name, u.name) AS shop_name
        FROM favourites f
        JOIN products p         ON p.id = f.product_id
        JOIN categories c       ON c.id = p.category_id
        JOIN users u            ON u.id = p.seller_id
        LEFT JOIN seller_profiles sp ON sp.user_id = p.seller_id
        WHERE f.user_id = ?
        ORDER BY f.created_at DESC
    ");
    $stmt->execute([$u['id']]);
    json_out(true, '', ['favourites' => $stmt->fetchAll()]);


case 'buyer_profile':
    $u = mustBuyer();
    $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id=?");
    $stmt->execute([$u['id']]); $user = $stmt->fetch();
    json_out(true, '', ['profile' => $user]);


case 'seller_profile':
    $u = mustSeller();
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.email,
               sp.shop_name, sp.shop_bio, sp.province, sp.city, sp.phone,
               sp.rating, sp.total_reviews
        FROM users u
        LEFT JOIN seller_profiles sp ON sp.user_id = u.id
        WHERE u.id = ?
    ");
    $stmt->execute([$u['id']]); $profile = $stmt->fetch();
    json_out(true, '', ['profile' => $profile]);


case 'save_seller_profile':
    $u = mustSeller();
    $shopName = xss($_POST['shop_name'] ?? '');
    $shopBio  = xss($_POST['shop_bio']  ?? '');
    $province = xss($_POST['province']  ?? '');
    $city     = xss($_POST['city']      ?? '');
    $phone    = xss($_POST['phone']     ?? '');
    $name     = xss($_POST['name']      ?? '');

   
    if ($name) $pdo->prepare("UPDATE users SET name=? WHERE id=?")->execute([$name, $u['id']]);

   
    $pdo->prepare("
        INSERT INTO seller_profiles (user_id,shop_name,shop_bio,province,city,phone)
        VALUES (?,?,?,?,?,?)
        ON DUPLICATE KEY UPDATE
          shop_name=VALUES(shop_name), shop_bio=VALUES(shop_bio),
          province=VALUES(province),   city=VALUES(city), phone=VALUES(phone)
    ")->execute([$u['id'],$shopName,$shopBio,$province,$city,$phone]);

   
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $url = saveImage($_FILES['avatar']);
        if ($url) $pdo->prepare("UPDATE seller_profiles SET avatar_url=? WHERE user_id=?")->execute([$url,$u['id']]);
    }
    json_out(true, 'Profile saved!');

case 'sellers_list':
    $stmt = $pdo->query("
        SELECT u.id, u.name,
               COALESCE(sp.shop_name, u.name) AS shop_name,
               sp.shop_bio, sp.province, sp.city, sp.rating, sp.total_reviews,
               (SELECT COUNT(*) FROM products p WHERE p.seller_id=u.id AND p.status='active') AS product_count
        FROM users u
        LEFT JOIN seller_profiles sp ON sp.user_id = u.id
        WHERE u.role = 'seller'
        ORDER BY u.name ASC
    ");
    json_out(true, '', ['sellers' => $stmt->fetchAll()]);

case 'seller_products':
    $sid = (int)($_GET['seller_id'] ?? 0);
    if (!$sid) json_out(false, 'seller_id required', [], 400);
    $stmt = $pdo->prepare("
        SELECT p.id, p.title, p.price, p.condition_type, p.image_url,
               p.delivery_option, p.stock_qty, p.views,
               c.name AS category_name
        FROM products p
        JOIN categories c ON c.id = p.category_id
        WHERE p.seller_id=? AND p.status='active' AND p.stock_qty>0
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$sid]);
    json_out(true, '', ['products' => $stmt->fetchAll()]);


case 'save_payment_settings':
    $u = mustSeller();
    
    json_out(true, 'Payment settings saved!');



case 'submit_review':
    $u        = mustBuyer();
    $orderId  = (int)($_POST['order_id']  ?? 0);
    $rating   = (int)($_POST['rating']    ?? 0);
    $comment  = xss($_POST['comment']     ?? '');

    if ($rating < 1 || $rating > 5) json_out(false, 'Rating must be between 1 and 5.', [], 400);
    if (!$orderId) json_out(false, 'order_id required', [], 400);

    
    $ord = $pdo->prepare("SELECT seller_id FROM orders WHERE id=? AND buyer_id=? AND status='delivered'");
    $ord->execute([$orderId, $u['id']]);
    $order = $ord->fetch();
    if (!$order) json_out(false, 'Order not found or not yet delivered.', [], 403);

  
    $existing = $pdo->prepare("SELECT id FROM reviews WHERE order_id=? AND reviewer_id=?");
    $existing->execute([$orderId, $u['id']]);
    if ($existing->fetch()) json_out(false, 'You have already reviewed this order.', [], 409);

   
    $pdo->prepare("INSERT INTO reviews (order_id, reviewer_id, seller_id, rating, comment)
                   VALUES (?,?,?,?,?)")
        ->execute([$orderId, $u['id'], $order['seller_id'], $rating, $comment]);

    
    $avg = $pdo->prepare("SELECT AVG(rating) AS avg_r, COUNT(*) AS cnt FROM reviews WHERE seller_id=?");
    $avg->execute([$order['seller_id']]); $avgRow = $avg->fetch();
    $pdo->prepare("UPDATE seller_profiles SET rating=?, total_reviews=? WHERE user_id=?")
        ->execute([round((float)$avgRow['avg_r'], 2), (int)$avgRow['cnt'], $order['seller_id']]);

    json_out(true, 'Review submitted! Thank you.');


case 'check_review':
    $u       = mustBuyer();
    $orderId = (int)($_GET['order_id'] ?? 0);
    $exists  = $pdo->prepare("SELECT id, rating, comment FROM reviews WHERE order_id=? AND reviewer_id=?");
    $exists->execute([$orderId, $u['id']]);
    $rev = $exists->fetch();
    json_out(true, '', ['reviewed' => (bool)$rev, 'review' => $rev ?: null]);


case 'seller_reviews':
    $u = mustSeller();

    $stmt = $pdo->prepare("
        SELECT
            r.id,
            r.rating,
            r.comment,
            r.created_at,
            o.id AS order_id,
            COALESCE(b.name, b.email, 'Buyer') AS buyer_name
        FROM reviews r
        LEFT JOIN users b ON b.id = r.reviewer_id
        LEFT JOIN orders o ON o.id = r.order_id
        WHERE r.seller_id = ?
        ORDER BY r.created_at DESC
    ");

    $stmt->execute([$u['id']]);

    json_out(true, '', [
        'reviews' => $stmt->fetchAll(PDO::FETCH_ASSOC)
    ]);

case 'public_seller_reviews':
    $sellerId = (int)($_GET['seller_id'] ?? 0);
    if (!$sellerId) json_out(false, 'seller_id required', [], 400);

    $stmt = $pdo->prepare("
        SELECT
            r.rating,
            r.comment,
            r.created_at,
            COALESCE(b.name, 'Anonymous') AS buyer_name
        FROM reviews r
        LEFT JOIN users b ON b.id = r.reviewer_id
        WHERE r.seller_id = ?
        ORDER BY r.created_at DESC
    ");
    $stmt->execute([$sellerId]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $avg = count($rows) ? round(array_sum(array_column($rows, 'rating')) / count($rows), 1) : 0;

    json_out(true, '', ['reviews' => $rows, 'average' => $avg, 'total' => count($rows)]);
default:
    json_out(false, 'Unknown action: ' . htmlspecialchars($action), [], 400);
}