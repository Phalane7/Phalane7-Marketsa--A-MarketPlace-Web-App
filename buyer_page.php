<?php
require_once 'db.php';
bootSession();
$user = mustBuyer('login.php');
$pdo  = db();


$buyerStmt = $pdo->prepare("SELECT id, name, email FROM users WHERE id = ?");
$buyerStmt->execute([$user['id']]);
$buyer = $buyerStmt->fetch();
$buyerName    = $buyer['name'] ?? 'there';
$buyerInitial = strtoupper(substr($buyerName, 0, 1));


$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0");
$unreadStmt->execute([$user['id']]);
$unreadCount = (int)$unreadStmt->fetchColumn();

$favStmt = $pdo->prepare("SELECT product_id FROM favourites WHERE user_id = ?");
$favStmt->execute([$user['id']]);
$favIds = array_column($favStmt->fetchAll(), 'product_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketSA - Shop</title>
    <link rel="icon" type="image/jpg" href="MarketSA.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@24,400,0,0"/>
<style>

*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Poppins',sans-serif;font-size:14px;background:#fff;color:#222;overflow-x:hidden;}
a{text-decoration:none;color:inherit;}
img{display:block;width:100%;}


.buyer-topbar{width:100%;background:#fff;padding:16px 5%;box-shadow:0 2px 12px rgba(0,0,0,.08);
    position:sticky;top:0;z-index:200;}
.buyer-wrapper{display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;}
.buyer-brand-img{width:140px;object-fit:contain;cursor:pointer;}
.buyer-nav-links{display:flex;align-items:center;gap:22px;list-style:none;}
.buyer-link{color:#333;font-size:15px;font-weight:500;transition:.3s;cursor:pointer;
    position:relative;background:none;border:none;font-family:'Poppins',sans-serif;}
.buyer-link:hover{color:#1a7a4a;}
.buyer-link-active{color:#1a7a4a;}
.buyer-link-active::after{content:'';position:absolute;bottom:-6px;left:0;
    width:100%;height:2px;background:#1a7a4a;}
.buyer-search-box{display:flex;align-items:center;background:#f3f3f3;border-radius:12px;
    padding:9px 13px;min-width:240px;gap:8px;}
.buyer-search-input{border:none;outline:none;background:transparent;width:100%;font-size:14px;}
.buyer-search-icon{color:#777;}
.buyer-icons-area{display:flex;align-items:center;gap:12px;}
.buyer-icon-circle{width:44px;height:44px;border-radius:50%;background:#f3f3f3;
    display:flex;align-items:center;justify-content:center;cursor:pointer;
    transition:.3s;position:relative;}
.buyer-icon-circle:hover{background:#1a7a4a;color:white;transform:translateY(-2px);}
.icon-badge{position:absolute;top:-3px;right:-3px;background:#e03131;color:#fff;
    font-size:.58rem;font-weight:700;padding:1px 5px;border-radius:50px;min-width:16px;text-align:center;}


.page-overlay{display:none;position:fixed;inset:0;background:#fff;z-index:300;
    overflow-y:auto;animation:fadeUp .25s ease;}
.page-overlay.active{display:block;}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}

.ov-header{display:flex;align-items:center;gap:14px;padding:15px 5%;
    background:#fff;border-bottom:1px solid #eee;
    position:sticky;top:0;z-index:10;}
.ov-back{background:none;border:1px solid #ddd;border-radius:10px;padding:7px 14px;
    cursor:pointer;font-family:'Poppins',sans-serif;font-size:13px;font-weight:500;
    color:#333;display:flex;align-items:center;gap:5px;transition:.2s;}
.ov-back:hover{border-color:#1a7a4a;color:#1a7a4a;}
.ov-title{font-size:18px;font-weight:700;}


.buyer-hero-section{width:100%;padding:70px 8%;background:#fff;}
.buyer-hero-wrapper{display:flex;align-items:center;justify-content:space-between;
    gap:50px;flex-wrap:wrap;}
.buyer-hero-text{flex:1;min-width:280px;}
.buyer-hero-title{font-size:50px;line-height:1.1;font-weight:700;color:#111;
    margin-bottom:18px;max-width:580px;}
.buyer-hero-description{font-size:17px;color:#666;margin-bottom:26px;line-height:1.7;}
.btn{display:inline-block;background:#1a7a4a;color:#fff;padding:12px 28px;
    border-radius:50px;font-weight:600;font-size:15px;transition:.3s;}
.btn:hover{background:#25a865;transform:translateY(-2px);}
.buyer-hero-imagebox{flex:1;display:flex;justify-content:flex-end;min-width:260px;}
.buyer-hero-image{width:100%;max-width:380px;border-radius:22px;object-fit:cover;}


.buyer-products-section{width:100%;padding:60px 6%;background:#fff;}
.buyer-products-header{display:flex;align-items:center;justify-content:space-between;
    margin-bottom:30px;flex-wrap:wrap;gap:12px;}
.buyer-products-title{font-size:30px;font-weight:700;color:#111;}


.filter-bar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;}
.filter-bar select{padding:8px 13px;border:1px solid #ddd;border-radius:10px;
    font-family:'Poppins',sans-serif;font-size:13px;outline:none;background:#fff;cursor:pointer;}
.filter-bar select:focus{border-color:#1a7a4a;}
.clear-btn{background:none;border:1px solid #ddd;border-radius:10px;padding:8px 14px;
    font-family:'Poppins',sans-serif;font-size:13px;cursor:pointer;transition:.2s;color:#666;}
.clear-btn:hover{border-color:#1a7a4a;color:#1a7a4a;}


.buyer-products-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:20px;}
.buyer-product-card{background:#fff;border-radius:18px;overflow:hidden;
    border:1px solid #eee;transition:.3s;}
.buyer-product-card:hover{transform:translateY(-5px);
    box-shadow:0 14px 34px rgba(0,0,0,.08);}
.buyer-product-imagebox{position:relative;overflow:hidden;}
.buyer-product-image{width:100%;height:200px;object-fit:cover;display:block;}
.no-product-img{width:100%;height:200px;background:#f5f5f5;display:flex;
    align-items:center;justify-content:center;font-size:3rem;color:#ddd;}
.buyer-product-badge{position:absolute;top:10px;left:10px;padding:4px 11px;
    border-radius:30px;font-size:12px;font-weight:600;}
.new-badge{background:#d4f8e8;color:#118c4f;}
.used-badge{background:#ffe0e0;color:#cc2f2f;}
.out-badge{background:#f0f0f0;color:#888;}
.buyer-product-icons{position:absolute;top:10px;right:10px;
    display:flex;flex-direction:column;gap:8px;}
.buyer-product-icon{width:36px;height:36px;border-radius:50%;background:white;
    display:flex;align-items:center;justify-content:center;
    color:#333;transition:.3s;cursor:pointer;border:none;}
.buyer-product-icon:hover{background:#1a7a4a;color:white;}
.buyer-product-icon.fav-on{background:#e03131;color:white;}
.buyer-product-details{padding:15px;}
.buyer-product-category{font-size:12px;color:#888;}
.buyer-product-name{font-size:16px;font-weight:600;margin:6px 0;color:#111;}
.buyer-product-seller{font-size:13px;color:#666;font-weight:500;}
.buyer-product-bottom{display:flex;align-items:center;justify-content:space-between;
    margin-top:14px;}
.buyer-product-price{font-size:17px;font-weight:700;color:#1a7a4a;}
.buyer-cart-btn{width:40px;height:40px;border-radius:50%;background:#f3f3f3;
    display:flex;align-items:center;justify-content:center;
    color:#333;transition:.3s;cursor:pointer;border:none;}
.buyer-cart-btn:hover{background:#1a7a4a;color:white;}

.empty-state{grid-column:1/-1;text-align:center;padding:60px 20px;color:#aaa;}
.empty-state span{font-size:3rem;display:block;margin-bottom:10px;opacity:.3;}


.vendors-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));
    gap:20px;padding:30px 5%;}
.vendor-card{background:#fff;border:1px solid #eee;border-radius:18px;padding:24px 18px;
    text-align:center;cursor:pointer;transition:.25s;
    box-shadow:0 2px 10px rgba(0,0,0,.05);}
.vendor-card:hover{transform:translateY(-5px);box-shadow:0 14px 34px rgba(0,0,0,.1);
    border-color:#1a7a4a;}
.vendor-avatar{width:72px;height:72px;border-radius:50%;margin:0 auto 12px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.8rem;font-weight:700;color:#fff;}
.vendor-name{font-size:15px;font-weight:700;color:#111;margin-bottom:3px;}
.vendor-shop{font-size:13px;color:#1a7a4a;font-weight:600;margin-bottom:5px;}
.vendor-cat{font-size:12px;color:#888;margin-bottom:10px;}
.vendor-count{font-size:12px;color:#555;}
.vendor-view-btn{margin-top:12px;background:#1a7a4a;color:#fff;border:none;
    border-radius:10px;padding:8px 18px;font-family:'Poppins',sans-serif;
    font-size:13px;font-weight:600;cursor:pointer;transition:.2s;}
.vendor-view-btn:hover{background:#25a865;}


.vendor-banner{height:150px;display:flex;align-items:flex-end;padding:0 5% 20px;
    background:linear-gradient(135deg,#1a7a4a,#0d5c34);}
.vendor-banner-av{width:78px;height:78px;border-radius:50%;border:4px solid #fff;
    display:flex;align-items:center;justify-content:center;
    font-size:2rem;font-weight:700;color:#fff;margin-right:16px;}
.vendor-banner-info h2{font-size:20px;font-weight:800;color:#fff;}
.vendor-banner-info p{font-size:13px;color:rgba(255,255,255,.75);}
.vendor-prods-wrap{padding:24px 5%;}
.vendor-prods-wrap h3{font-size:18px;font-weight:700;margin-bottom:18px;}


.vendor-msg-box{margin:10px 5% 50px;background:#f9f9f9;border-radius:16px;
    padding:24px;border:1px solid #eee;}
.vendor-msg-box h3{font-size:16px;font-weight:700;margin-bottom:12px;}
.msg-bubbles{display:flex;flex-direction:column;gap:8px;margin-bottom:12px;}
.mb-sent{align-self:flex-end;background:#1a7a4a;color:#fff;padding:9px 13px;
    border-radius:14px 14px 3px 14px;font-size:13px;max-width:70%;line-height:1.5;}
.mb-recv{align-self:flex-start;background:#efefef;color:#333;padding:9px 13px;
    border-radius:14px 14px 14px 3px;font-size:13px;max-width:70%;line-height:1.5;}
.mb-time{font-size:11px;opacity:.6;margin-top:3px;}
.vendor-msg-box textarea{width:100%;min-height:90px;resize:vertical;border:1.5px solid #ddd;
    border-radius:12px;padding:11px;font-family:'Poppins',sans-serif;font-size:14px;outline:none;}
.vendor-msg-box textarea:focus{border-color:#1a7a4a;}
.vendor-msg-box button{margin-top:10px;background:#1a7a4a;color:#fff;border:none;
    border-radius:12px;padding:11px 26px;font-family:'Poppins',sans-serif;
    font-size:14px;font-weight:600;cursor:pointer;transition:.2s;}
.vendor-msg-box button:hover{background:#25a865;}


.prod-detail-wrap{display:grid;grid-template-columns:1fr 1fr;gap:46px;
    padding:36px 6%;align-items:start;}
.prod-detail-img{width:100%;border-radius:20px;object-fit:cover;max-height:440px;}
.pd-no-img{width:100%;height:350px;background:#f5f5f5;border-radius:20px;
    display:flex;align-items:center;justify-content:center;font-size:4rem;color:#ddd;}
.pd-cat{font-size:13px;color:#888;margin-bottom:6px;}
.pd-name{font-size:28px;font-weight:800;color:#111;margin-bottom:5px;}
.pd-seller{font-size:14px;color:#555;margin-bottom:10px;}
.pd-badge{display:inline-block;padding:4px 13px;border-radius:30px;
    font-size:13px;font-weight:600;margin-bottom:16px;}
.pd-price{font-size:26px;font-weight:800;color:#1a7a4a;margin-bottom:14px;}
.pd-desc{font-size:14px;color:#666;line-height:1.75;border-top:1px solid #eee;
    padding-top:16px;margin-bottom:24px;}
.pd-delivery{font-size:13px;color:#555;margin-bottom:6px;}
.pd-actions{display:flex;gap:11px;flex-wrap:wrap;margin-top:6px;}
.pd-cart-btn{background:#1a7a4a;color:#fff;border:none;border-radius:13px;
    padding:13px 28px;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;
    cursor:pointer;display:flex;align-items:center;gap:7px;transition:.2s;}
.pd-cart-btn:hover{background:#25a865;transform:translateY(-2px);}
.pd-fav-btn{background:#f3f3f3;color:#333;border:none;border-radius:13px;
    padding:13px 20px;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;
    cursor:pointer;display:flex;align-items:center;gap:7px;transition:.2s;}
.pd-fav-btn:hover,.pd-fav-btn.on{background:#ffe0e0;color:#e03131;}


.cart-wrap{display:grid;grid-template-columns:1fr 320px;gap:26px;padding:26px 5%;}
.cart-items{background:#fff;border:1px solid #eee;border-radius:16px;overflow:hidden;}
.cart-row{display:flex;align-items:center;gap:13px;padding:14px 17px;
    border-bottom:1px solid #f0f0f0;}
.cart-row:last-child{border-bottom:none;}
.cart-img{width:64px;height:64px;border-radius:11px;object-fit:cover;
    flex-shrink:0;background:#f5f5f5;}
.cart-name{font-size:14px;font-weight:700;margin-bottom:2px;}
.cart-shop{font-size:12px;color:#888;}
.cart-price{margin-left:auto;font-size:15px;font-weight:800;color:#1a7a4a;}
.cart-rm{background:none;border:none;cursor:pointer;color:#ccc;margin-left:10px;
    font-size:1.1rem;transition:.2s;}
.cart-rm:hover{color:#e03131;}
.cart-summary{background:#fff;border:1px solid #eee;border-radius:16px;
    padding:20px;height:fit-content;}
.cart-summary h3{font-size:16px;font-weight:800;margin-bottom:16px;}
.cs-row{display:flex;justify-content:space-between;margin-bottom:9px;
    font-size:14px;color:#555;}
.cs-row.total{font-size:16px;font-weight:800;color:#111;border-top:1px solid #eee;
    padding-top:11px;margin-top:4px;}
.checkout-btn{width:100%;margin-top:14px;background:#1a7a4a;color:#fff;border:none;
    border-radius:13px;padding:14px;font-family:'Poppins',sans-serif;
    font-size:14px;font-weight:700;cursor:pointer;transition:.2s;}
.checkout-btn:hover{background:#25a865;}
.cart-empty{text-align:center;padding:60px 20px;color:#aaa;}


.co-wrap{max-width:580px;margin:0 auto;padding:34px 5%;}
.co-wrap h3{font-size:20px;font-weight:800;margin-bottom:5px;}
.co-sub{font-size:14px;color:#888;margin-bottom:24px;}
.co-steps{display:flex;align-items:center;margin-bottom:28px;}
.co-step{display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;}
.co-circle{width:30px;height:30px;border-radius:50%;display:flex;align-items:center;
    justify-content:center;font-size:13px;font-weight:700;
    background:#eee;color:#aaa;transition:.3s;}
.co-circle.done{background:#1a7a4a;color:#fff;}
.co-circle.cur{background:#111;color:#fff;}
.co-lbl{font-size:11px;color:#aaa;font-weight:600;}
.co-lbl.done{color:#1a7a4a;} .co-lbl.cur{color:#111;}
.co-line{flex:1;height:2px;background:#eee;margin-bottom:16px;transition:.3s;}
.co-line.done{background:#1a7a4a;}

.co-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.co-group{display:flex;flex-direction:column;gap:5px;}
.co-group.full{grid-column:1/-1;}
.co-group label{font-size:11px;font-weight:700;color:#555;letter-spacing:.04em;}
.co-group input,.co-group select{padding:11px 13px;border:1.5px solid #ddd;
    border-radius:10px;font-family:'Poppins',sans-serif;font-size:14px;outline:none;
    transition:.2s;background:#fff;}
.co-group input:focus,.co-group select:focus{border-color:#1a7a4a;}
.co-next{width:100%;margin-top:22px;background:#1a7a4a;color:#fff;border:none;
    border-radius:13px;padding:14px;font-family:'Poppins',sans-serif;
    font-size:15px;font-weight:700;cursor:pointer;transition:.2s;}
.co-next:hover{background:#25a865;}


.pay-section{margin-top:28px;padding-top:26px;border-top:2px solid #eee;}
.pay-title{font-size:16px;font-weight:700;color:#333;
    display:flex;align-items:center;gap:8px;margin-bottom:14px;}
.pay-cards-row{display:flex;align-items:center;gap:9px;margin-bottom:18px;flex-wrap:wrap;}
.pay-card-lbl{font-size:13px;color:#777;font-weight:500;}
.pay-card-icon{padding:5px 9px;border:1.5px solid #ddd;border-radius:7px;
    font-size:11px;font-weight:800;background:#fff;}
.pay-card-icon.visa{color:#1a1f71;} .pay-card-icon.mc{color:#eb001b;} .pay-card-icon.amex{color:#007bc1;}
.card-wrap{position:relative;}
.card-wrap input{width:100%;padding-right:42px;}
.card-brand{position:absolute;right:11px;top:50%;transform:translateY(-50%);
    font-size:11px;font-weight:900;color:#aaa;pointer-events:none;}
.secure-note{display:flex;align-items:center;gap:7px;margin-top:12px;padding:9px 13px;
    background:#f0faf4;border:1px solid #c3e6cb;border-radius:10px;
    font-size:12px;color:#1a7a4a;font-weight:600;}


.rev-addr{background:#f9f9f9;border-radius:11px;padding:13px 15px;
    font-size:13px;color:#555;line-height:1.75;margin-bottom:16px;}
.rev-addr strong{color:#111;}
.rev-item{display:flex;align-items:center;gap:12px;padding:11px 0;
    border-bottom:1px solid #f0f0f0;}
.rev-item:last-child{border-bottom:none;}
.rev-img{width:54px;height:54px;border-radius:10px;object-fit:cover;
    flex-shrink:0;background:#f5f5f5;}
.rev-name{font-size:13px;font-weight:700;}
.rev-price{margin-left:auto;font-size:14px;font-weight:800;color:#1a7a4a;}
.place-btn{width:100%;background:#1a7a4a;color:#fff;border:none;border-radius:13px;
    padding:15px;font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;
    cursor:pointer;transition:.2s;margin-top:20px;}
.place-btn:hover{background:#25a865;}


.order-success{text-align:center;padding:80px 5%;}
.order-success .tick{font-size:5rem;margin-bottom:14px;}
.order-success h2{font-size:26px;font-weight:800;margin-bottom:10px;}
.order-success p{font-size:15px;color:#666;max-width:420px;margin:0 auto 26px;line-height:1.7;}
.order-success button{background:#1a7a4a;color:#fff;border:none;border-radius:13px;
    padding:13px 32px;font-family:'Poppins',sans-serif;font-size:15px;font-weight:700;cursor:pointer;}


.acc-wrap{
display:grid;
grid-template-columns:240px 1fr;
gap:26px;
padding:26px 5%;
min-height:calc(100vh - 70px);
}

.acc-sidebar{
display:flex;
flex-direction:column;
gap:6px;
}

.acc-tab{
display:flex;
align-items:center;
gap:11px;
padding:11px 15px;
border-radius:12px;
 border:none;
 background:none;
 font-family:'Poppins',sans-serif;
 font-size:14px;
font-weight:500;
color:#555;
cursor:pointer;
text-align:left;
transition:.2s;
}
.acc-tab:hover{
    background:#f3f3f3;
    color:#1a7a4a;
}
.acc-tab.active{
    background:#eaf6ef;
    color:#1a7a4a;font-weight:700;
}
.acc-panel{
    display:none;
}
.acc-panel.active{
    display:block;
    animation:fadeUp .2s ease;
}
.acc-panel h3{
    font-size:18px;
    font-weight:700;
    margin-bottom:18px;
}
.acc-grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:13px;
}
.ag{
    display:flex;
    flex-direction:column;
    gap:4px;
}
.ag.full{
    grid-column:1/-1;
}
.ag label{
    font-size:11px;
font-weight:700;
color:#555;
letter-spacing:.04em;}

.ag input,.ag select{
    padding:10px 12px;
    border:1.5px solid #eee;
    border-radius:10px;
    font-family:'Poppins',sans-serif;
    font-size:14px;
    outline:none;transition:.2s;
}
.ag input:focus,.ag select:focus{
    border-color:#1a7a4a;
}
.acc-save{
    background:#1a7a4a;
    color:#fff;
    border:none;
    border-radius:10px;
    padding:11px 26px;
    font-family:'Poppins',sans-serif;
    font-size:14px;font-weight:700;
    cursor:pointer;margin-top:14px;
}


.pur-table{width:100%;border-collapse:collapse;font-size:13px;}
.pur-table th{background:#f5f5f5;padding:10px 13px;text-align:left;font-size:11px;
    font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.05em;}
.pur-table td{padding:12px 13px;border-bottom:1px solid #f0f0f0;color:#444;}
.pur-table tr:last-child td{border:none;}
.pur-badge{font-size:11px;font-weight:700;padding:2px 9px;border-radius:50px;}
.pur-pending{background:rgba(245,166,35,.15);color:#d4891a;}
.pur-delivered{background:rgba(26,122,74,.12);color:#1a7a4a;}
.pur-confirmed{background:rgba(37,70,168,.1);color:#2546a8;}
.pur-ready{background:rgba(245,166,35,.15);color:#d4891a;}
.pur-cancelled{background:rgba(224,49,49,.1);color:#e03131;}


.msg-thread{margin-bottom:16px;border:1px solid #eee;border-radius:13px;overflow:hidden;}
.mt-hdr{display:flex;align-items:center;gap:11px;padding:12px 15px;
    background:#f9f9f9;border-bottom:1px solid #eee;}
.mt-av{width:38px;height:38px;border-radius:50%;display:flex;align-items:center;
    justify-content:center;font-weight:700;color:#fff;font-size:.88rem;}
.mt-name{font-weight:700;font-size:13px;}
.mt-shop{font-size:11px;color:#1a7a4a;font-weight:600;}
.mt-body{padding:13px 15px;display:flex;flex-direction:column;gap:7px;}


.fav-empty{text-align:center;padding:70px 20px;color:#aaa;}
.fav-empty span{font-size:3.5rem;display:block;margin-bottom:10px;opacity:.25;}


footer{background:#111;color:rgba(255,255,255,.6);padding:3rem 5% 2rem;}
.footer-inner{max-width:1200px;margin:0 auto;}
.footer-top{display:flex;justify-content:space-between;flex-wrap:wrap;gap:2rem;margin-bottom:2rem;}
.footer-logo{font-size:1.4rem;font-weight:700;color:#fff;margin-bottom:.3rem;}
.footer-logo span{color:#1a7a4a;}
.footer-tagline{font-size:.8rem;opacity:.6;}
.footer-links{display:flex;gap:3rem;flex-wrap:wrap;}
.footer-col h4{color:#fff;font-size:.85rem;font-weight:600;margin-bottom:.8rem;}
.footer-col a{display:block;color:rgba(255,255,255,.5);font-size:.83rem;margin-bottom:.45rem;transition:.2s;}
.footer-col a:hover{color:#1a7a4a;}
.footer-bottom{border-top:1px solid rgba(255,255,255,.08);padding-top:1.3rem;
    font-size:.8rem;display:flex;justify-content:space-between;flex-wrap:wrap;gap:1rem;}


.toast{position:fixed;bottom:20px;right:20px;background:#111;color:#fff;
    padding:10px 16px;border-radius:11px;font-size:14px;font-weight:500;
    transform:translateY(70px);opacity:0;transition:.3s;z-index:9999;
    display:flex;align-items:center;gap:7px;}
.toast.show{transform:translateY(0);opacity:1;}


@media(max-width:1300px){.buyer-products-grid{grid-template-columns:repeat(4,1fr);}}
@media(max-width:1050px){.buyer-products-grid{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){.buyer-products-grid{grid-template-columns:repeat(2,1fr);}
    .prod-detail-wrap,.cart-wrap,.acc-wrap,.co-wrap{grid-template-columns:1fr;padding:20px 4%;}
    .buyer-hero-title{font-size:34px;} .buyer-wrapper{flex-direction:column;}}
@media(max-width:480px){.buyer-products-grid{grid-template-columns:1fr;}}
</style>
</head>
<body>


<header class="buyer-topbar">
    <div class="buyer-wrapper">
        <img src="lo33.png" alt="MarketSA" class="buyer-brand-img"
             onclick="showHome()" style="width:140px;">

        <ul class="buyer-nav-links">
            <li><button class="buyer-link buyer-link-active" onclick="showHome()">Home</button></li>
           
            <li><button class="buyer-link" onclick="openOv('ov-vendors')">Vendors</button></li>
            <li><button class="buyer-link" onclick="openOv('ov-account')">My Account</button></li>
            <li><a href="logout.php" class="buyer-link">Sign Out</a></li>
        </ul>

        <div class="buyer-search-box">
            <span class="material-symbols-sharp buyer-search-icon">search</span>
            <input type="text" placeholder="Search products…" class="buyer-search-input"
                   oninput="searchProducts(this.value)">
        </div>

        <div class="buyer-icons-area">
            <div class="buyer-icon-circle" onclick="openOv('ov-favs')" title="Favourites">
                <span class="material-symbols-sharp">favorite</span>
                <span class="icon-badge" id="fav-badge" style="display:none">0</span>
            </div>
            <div class="buyer-icon-circle" onclick="openOv('ov-cart')" title="Cart">
                <span class="material-symbols-sharp">local_mall</span>
                <span class="icon-badge" id="cart-badge" style="display:none">0</span>
            </div>
        </div>
    </div>
</header>


<div id="home-page">
    <h1 style="padding:18px 8% 0;font-size:19px;font-weight:600;">
        Hi, <span style="color:#1a7a4a;"><?= xss($buyerName) ?></span> 
    </h1>

    <section class="buyer-hero-section">
        <div class="buyer-hero-wrapper">
            <div class="buyer-hero-text">
                <h1 class="buyer-hero-title">Best South African Trading Platform</h1>
                <p class="buyer-hero-description">Check what our verified sellers have in stock</p>
                <a href="#products" class="btn" onclick="document.getElementById('products-sec').scrollIntoView({behavior:'smooth'});return false;">
                    Shop Now
                </a>
            </div>
          
        </div>
    </section>

    <section class="buyer-products-section" id="products-sec">
        <div class="buyer-products-header">
            <h2 class="buyer-products-title">Products</h2>
            <div class="filter-bar">
                <select id="filter-category" onchange="applyFilters()">
                    <option value="">All Categories</option>
                   
                </select>
                <select id="filter-province" onchange="applyFilters()">
                    <option value="">All Provinces</option>
                    <option>Gauteng</option><option>Western Cape</option>
                    <option>KwaZulu-Natal</option><option>Eastern Cape</option>
                    <option>Limpopo</option><option>Mpumalanga</option>
                    <option>North West</option><option>Free State</option>
                    <option>Northern Cape</option>
                </select>
                <select id="filter-sort" onchange="applyFilters()">
                    <option value="">Sort: Newest</option>
                    <option value="price_asc">Price: Low to High</option>
                    <option value="price_desc">Price: High to Low</option>
                </select>
                <button class="clear-btn" onclick="clearFilters()">Clear</button>
            </div>
        </div>
        <div class="buyer-products-grid" id="products-grid">
            <div class="empty-state"><span class="material-symbols-sharp">inventory_2</span>Loading products…</div>
        </div>
    </section>
</div>




<div class="page-overlay" id="ov-vendors">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-vendors')">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">Our Vendors</h2>
    </div>
    <div class="vendors-grid" id="vendors-grid">
        <div class="empty-state"><span class="material-symbols-sharp">storefront</span>Loading vendors…</div>
    </div>
</div>


<div class="page-overlay" id="ov-vendor-detail">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-vendor-detail');openOv('ov-vendors');">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> All Vendors
        </button>
        <h2 class="ov-title" id="vd-title">Vendor</h2>
    </div>
    <div id="vendor-detail-content"></div>
</div>


<div class="page-overlay" id="ov-product">
    <div class="ov-header">
        <button class="ov-back" id="prod-back-btn" onclick="closeOv('ov-product')">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">Product Details</h2>
    </div>
    <div id="product-detail-content"></div>
</div>


<div class="page-overlay" id="ov-favs">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-favs')">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">My Favourites </h2>
    </div>
    <div id="fav-content" style="padding:24px 5%;"></div>
</div>


<div class="page-overlay" id="ov-cart">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-cart')">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">My Cart </h2>
    </div>
    <div id="cart-content"></div>
</div>


<div class="page-overlay" id="ov-shipping">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-shipping');openOv('ov-cart');">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back to Cart
        </button>
        <h2 class="ov-title">Checkout</h2>
    </div>
    <div class="co-wrap">
        <div class="co-steps">
            <div class="co-step"><div class="co-circle cur">1</div><div class="co-lbl cur">Shipping</div></div>
            <div class="co-line"></div>
            <div class="co-step"><div class="co-circle">2</div><div class="co-lbl">Billing & Payment</div></div>
            <div class="co-line"></div>
            <div class="co-step"><div class="co-circle">3</div><div class="co-lbl">Review</div></div>
        </div>
        <h3>Shipping Details</h3>
        <p class="co-sub">Where should we deliver your order?</p>
        <div class="co-grid">
            <div class="co-group"><label>FULL NAME *</label><input type="text" id="sh-name" placeholder="e.g. Nomvula Dlamini"></div>
            <div class="co-group"><label>PHONE *</label><input type="text" id="sh-phone" placeholder="07x xxx xxxx"></div>
            <div class="co-group full"><label>STREET ADDRESS *</label><input type="text" id="sh-street" placeholder="123 Main Street"></div>
            <div class="co-group"><label>CITY *</label><input type="text" id="sh-city" placeholder="e.g. Soweto"></div>
            <div class="co-group"><label>PROVINCE *</label>
                <select id="sh-province">
                    <option value="">Select…</option>
                    <option>Gauteng</option><option>Western Cape</option><option>KwaZulu-Natal</option>
                    <option>Eastern Cape</option><option>Limpopo</option><option>Mpumalanga</option>
                    <option>North West</option><option>Free State</option><option>Northern Cape</option>
                </select>
            </div>
            <div class="co-group"><label>POSTAL CODE</label><input type="text" id="sh-postal" placeholder="e.g. 1800"></div>
        </div>
        <button class="co-next" onclick="goToBilling()">Continue to Billing →</button>
    </div>
</div>

<!-- this is for payment and billing -->
<div class="page-overlay" id="ov-billing">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-billing');openOv('ov-shipping');">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">Billing & Payment</h2>
    </div>
    <div class="co-wrap">
        <div class="co-steps">
            <div class="co-step"><div class="co-circle done">✓</div><div class="co-lbl done">Shipping</div></div>
            <div class="co-line done"></div>
            <div class="co-step"><div class="co-circle cur">2</div><div class="co-lbl cur">Billing & Payment</div></div>
            <div class="co-line"></div>
            <div class="co-step"><div class="co-circle">3</div><div class="co-lbl">Review</div></div>
        </div>
        <h3>Billing Address</h3>
        <p class="co-sub">Where should we send the invoice?</p>
        <label style="display:flex;align-items:center;gap:10px;font-size:14px;margin-bottom:16px;cursor:pointer;">
            <input type="checkbox" id="bill-same" onchange="toggleBillSame(this)"
                   style="width:17px;height:17px;accent-color:#1a7a4a;"> Same as shipping address
        </label>
        <div id="billing-fields">
            <div class="co-grid">
                <div class="co-group full"><label>FULL NAME *</label><input type="text" id="bi-name" placeholder="e.g. Nomvula Dlamini"></div>
                <div class="co-group full"><label>STREET ADDRESS *</label><input type="text" id="bi-street" placeholder="123 Main Street"></div>
                <div class="co-group"><label>CITY *</label><input type="text" id="bi-city" placeholder="e.g. Soweto"></div>
                <div class="co-group"><label>PROVINCE *</label>
                    <select id="bi-province">
                        <option value="">Select…</option>
                        <option>Gauteng</option><option>Western Cape</option><option>KwaZulu-Natal</option>
                        <option>Eastern Cape</option><option>Limpopo</option><option>Mpumalanga</option>
                        <option>North West</option><option>Free State</option><option>Northern Cape</option>
                    </select>
                </div>
                <div class="co-group"><label>POSTAL CODE</label><input type="text" id="bi-postal" placeholder="e.g. 1800"></div>
            </div>
        </div>

       
       
 <!-- this is for payment  -->
        <div class="pay-section">
            <div class="pay-title">
                <span class="material-symbols-sharp">credit_card</span> Payment Details
            </div>
            <div class="co-group full" style="margin-bottom:14px;">
                <label>PAYMENT METHOD</label>
                <select id="pay-method" onchange="togglePaymentFields(this.value)">
                    <option value="card">Credit / Debit Card</option>
                    
                </select>
            </div>

            <!-- this is forr card fields -->
            <div id="card-fields">
                <div class="pay-cards-row">
                    <span class="pay-card-lbl">Accepted:</span>
                    <div class="pay-card-icon visa">
                        <img src="card_img.png" alt="">
                    </div>
                    
                </div>
                <div class="co-grid">
                    <div class="co-group full"><label>NAME ON CARD *</label>
                        <input type="text" id="pay-name" placeholder="e.g. Nomvula Dlamini"></div>
                    <div class="co-group full"><label>CARD NUMBER *</label>
                        <div class="card-wrap">
                            <input type="text" id="pay-cardnum" placeholder="1234 5678 9012 3456"
                                   maxlength="19" oninput="fmtCard(this)">
                            <span class="card-brand" id="card-brand"></span>
                        </div>
                    </div>
                    <div class="co-group"><label>EXPIRY MONTH *</label>
                        <select id="pay-month">
                            <option value="">Month…</option>
                            <option>January</option><option>February</option><option>March</option>
                            <option>April</option><option>May</option><option>June</option>
                            <option>July</option><option>August</option><option>September</option>
                            <option>October</option><option>November</option><option>December</option>
                        </select>
                    </div>
                    <div class="co-group"><label>EXPIRY YEAR *</label>
                        <input type="number" id="pay-year" placeholder="2025" min="2024" max="2040"></div>
                    <div class="co-group"><label>CVV *</label>
                        <input type="password" id="pay-cvv" placeholder="•••" maxlength="4"></div>
                    <div class="co-group" style="display:flex;align-items:flex-end;">
                        <small style="color:#aaa;font-size:11px;line-height:1.5;">
                            3 or 4 digit code<br>on your card.
                        </small>
                    </div>
                </div>
            </div>



            

            <div class="secure-note">
                <span class="material-symbols-sharp" style="font-size:1rem;">lock</span>
                Your payment details are encrypted. We never store full card numbers.
            </div>
        </div>
        <button class="co-next" style="margin-top:24px;" onclick="goToReview()">Review Order →</button>
    </div>
</div>


<div class="page-overlay" id="ov-review">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-review');openOv('ov-billing');">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">Review Your Order</h2>
    </div>
    <div class="co-wrap">
        <div class="co-steps">
            <div class="co-step"><div class="co-circle done">✓</div><div class="co-lbl done">Shipping</div></div>
            <div class="co-line done"></div>
            <div class="co-step"><div class="co-circle done">✓</div><div class="co-lbl done">Billing</div></div>
            <div class="co-line done"></div>
            <div class="co-step"><div class="co-circle cur">3</div><div class="co-lbl cur">Review</div></div>
        </div>
        <h3>Order Summary</h3>
        <div id="review-addr"></div>
        <div id="review-items"></div>
        <div id="review-total" style="font-size:18px;font-weight:800;color:#1a7a4a;margin-top:14px;"></div>
        <button class="place-btn" id="place-btn" onclick="placeOrder()"> Place Order</button>
    </div>
</div>


<div class="page-overlay" id="ov-success">
    <div class="order-success">
        <div class="tick">Yay</div>
        <h2>Order Placed Successfully!</h2>
        <p>Thank you for shopping on MarketSA! Your order has been received and the seller will be in touch shortly.</p>
        <div id="success-ref" style="font-size:15px;font-weight:700;color:#1a7a4a;margin-bottom:20px;"></div>
        <button onclick="closeAll();loadProducts();">Continue Shopping</button>
    </div>
</div>


<div class="page-overlay" id="ov-account">
    <div class="ov-header">
        <button class="ov-back" onclick="closeOv('ov-account')">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
        <h2 class="ov-title">My Account</h2>
    </div>
    <div class="acc-wrap">
        <div class="acc-sidebar">
            <button class="acc-tab active" onclick="accTab('tab-details',this)">
                <span class="material-symbols-sharp">person</span> My Details
            </button>
            <button class="acc-tab" onclick="accTab('tab-orders',this)">
                <span class="material-symbols-sharp">receipt_long</span> My Orders
            </button>
            <button class="acc-tab" onclick="accTab('tab-messages',this)">
                <span class="material-symbols-sharp">mail_outline</span> Messages
                <span id="acc-msg-badge" style="background:#e03131;color:#fff;font-size:.6rem;
                    padding:1px 6px;border-radius:50px;margin-left:4px;
                    <?= $unreadCount > 0 ? '' : 'display:none' ?>"><?= $unreadCount ?></span>
            </button>
        </div>
        <div>
            <!-- details -->
            <div class="acc-panel active" id="tab-details">
                <h3>My Details</h3>
                <div class="acc-grid">
                    <div class="ag full"><label>FULL NAME</label>
                        <input type="text" id="acc-name" value="<?= xss($buyerName) ?>"></div>
                    <div class="ag full"><label>EMAIL</label>
                        <input type="email" id="acc-email" value="<?= xss($buyer['email'] ?? '') ?>" disabled></div>
                    <div class="ag full"><label>NEW PASSWORD (blank = no change)</label>
                        <input type="password" id="acc-pw" placeholder="••••••••"></div>
                </div>
                <button class="acc-save" onclick="toast('Details saved!')">Save Changes</button>
            </div>
            <!-- orders -->
            <div class="acc-panel" id="tab-orders">
                <h3>My Orders</h3>
                <table class="pur-table">
                    <thead><tr>
    <th>Order Ref</th><th>Product</th><th>Shop</th>
    <th>Amount</th><th>Status</th><th>Date</th><th>Review</th>
</tr></thead>
                    <tbody id="acc-orders-tbody">
                        <tr><td colspan="6" style="padding:1rem;color:#aaa;">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
            <!-- messages -->
            <div class="acc-panel" id="tab-messages">
                <h3>Messages with Vendors</h3>
                <div id="acc-msgs-list"><p style="color:#aaa;">Loading…</p></div>
            </div>
        </div>
    </div>
</div>


<footer>
    <div class="footer-inner">
        <div class="footer-top">
            <div>
                <div class="footer-logo">Market<span>SA</span></div>
                <div class="footer-tagline">South Africa's Free Marketplace 🇿🇦</div>
            </div>
            <div class="footer-links">
                <div class="footer-col">
                     <h4>Platform</h4>
          <a href="MarkeSA User Manual.pdf">How It Works</a>
          
        <a href="index.php?form=register">Start Selling</a>
         <a href="help.php">Help Centre</a>
        </div>

        <div class="footer-col">
          <h4>Support</h4>
         
          <a href="saftey.php">Safety Tips</a>
          <a href="report.php">Report a Listing</a>
          <a href="contact.php">Contact Us</a>
        </div>

        <div class="footer-col">
          <h4>Company</h4>
          <a href="about.php">About MarketSA</a>
          <a href="terms.php">Terms of Service</a>
          <a href="policy.php">Privacy Policy</a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <span>© MarketSA. All rights reserved.</span>
            <span>Made in South Africa 🇿🇦</span>
        </div>
    </div>
</footer>



<div id="rate-modal-ov" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);
    z-index:2000;align-items:center;justify-content:center;backdrop-filter:blur(4px);">
    <div style="background:#fff;border-radius:20px;padding:2rem;max-width:420px;width:90%;
        box-shadow:0 30px 80px rgba(0,0,0,.2);animation:fadeUp .25s ease;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.2rem;">
            <h3 style="font-size:1.1rem;font-weight:800;" id="rate-modal-title">Rate Seller</h3>
            <button onclick="closeRateModal()" style="background:none;border:1px solid #ddd;
                border-radius:7px;width:32px;height:32px;cursor:pointer;font-size:.95rem;">✕</button>
        </div>
        <div id="rate-already-done" style="display:none;text-align:center;padding:1rem 0;">
            <div style="font-size:2rem;margin-bottom:.5rem;" id="rate-done-stars"></div>
            <p style="color:#555;font-size:.9rem;">You've already reviewed this order.</p>
            <p id="rate-done-comment" style="color:#888;font-size:.83rem;font-style:italic;margin-top:.5rem;"></p>
        </div>
        <div id="rate-form">
            <p style="font-size:.87rem;color:#666;margin-bottom:1.2rem;">
                How was your experience with <strong id="rate-shop-name"></strong>?
            </p>
            <div style="display:flex;gap:8px;justify-content:center;margin-bottom:1.2rem;" id="star-row">
                <span class="star-btn" data-v="1" onclick="selectStar(1)"
                      style="font-size:2rem;cursor:pointer;opacity:.3;transition:.2s;"><span class="material-symbols-sharp">
star
</span></span>
                <span class="star-btn" data-v="2" onclick="selectStar(2)"
                      style="font-size:2rem;cursor:pointer;opacity:.3;transition:.2s;"><span class="material-symbols-sharp">
star
</span></span>
                <span class="star-btn" data-v="3" onclick="selectStar(3)"
                      style="font-size:2rem;cursor:pointer;opacity:.3;transition:.2s;"><span class="material-symbols-sharp">
star
</span></span>
                <span class="star-btn" data-v="4" onclick="selectStar(4)"
                      style="font-size:2rem;cursor:pointer;opacity:.3;transition:.2s;"><span class="material-symbols-sharp">
star
</span></span>
                <span class="star-btn" data-v="5" onclick="selectStar(5)"
                      style="font-size:2rem;cursor:pointer;opacity:.3;transition:.2s;"><span class="material-symbols-sharp">
star
</span></span>
            </div>
            <p id="star-label" style="text-align:center;font-size:.8rem;color:#888;margin-bottom:1rem;min-height:18px;"></p>
            <textarea id="rate-comment" placeholder="Leave a comment (optional)…"
                style="width:100%;min-height:80px;resize:vertical;border:1.5px solid #ddd;
                border-radius:10px;padding:10px 12px;font-family:'Poppins',sans-serif;
                font-size:.87rem;outline:none;"></textarea>
            <button onclick="submitRating()"
                style="width:100%;margin-top:12px;background:#1a7a4a;color:#fff;border:none;
                border-radius:12px;padding:12px;font-family:'Poppins',sans-serif;
                font-size:.9rem;font-weight:700;cursor:pointer;">Submit Review</button>
        </div>
    </div>
</div>




<div class="toast" id="toast"></div>
<script>

const ME_ID   = <?= (int)$user['id'] ?>;
const FAV_IDS = new Set(<?= json_encode($favIds) ?>);
let CART = JSON.parse(
    localStorage.getItem('msa_cart_' + ME_ID) || '[]'
);

CART.forEach(item => {
    if (!item.qty) item.qty = 1;
}); 
let   shipData = {};


async function api(action, params = {}, method = 'GET', fd = null) {
    const qs  = method === 'GET' ? '&' + new URLSearchParams(params) : '';
    const url = 'api.php?action=' + action + qs;
    const opts = { method, credentials: 'include' };
    if (method === 'POST') {
        if (fd) { opts.body = fd; }
        else {
            const f = new FormData();
            Object.entries(params).forEach(([k,v]) => f.append(k,v));
            opts.body = f;
        }
    }
    const r = await fetch(url, opts);
    return r.json().catch(() => ({ success: false, message: 'Server error' }));
}


function openOv(id) {
    document.getElementById(id).classList.add('active');
    if (id === 'ov-vendors')  loadVendors();
    if (id === 'ov-favs')     loadFavs();
    if (id === 'ov-cart')     renderCart();
    if (id === 'ov-account')  { loadMyOrders(); loadMyMessages(); }
}
function closeOv(id)  { document.getElementById(id).classList.remove('active'); }
function closeAll()   { document.querySelectorAll('.page-overlay').forEach(o => o.classList.remove('active')); }
function showHome()   { closeAll(); }


async function loadCategories() {
    const d = await api('categories');
    if (!d.success) return;
    const sel = document.getElementById('filter-category');
    sel.innerHTML = '<option value="">All Categories</option>' +
        d.categories.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
}


let allProducts = [];
async function loadProducts(extraParams = {}) {
    const params = {
        q:           document.querySelector('.buyer-search-input')?.value || '',
        category_id: document.getElementById('filter-category')?.value || '',
        province:    document.getElementById('filter-province')?.value || '',
        ...extraParams
    };
    const d = await api('browse_products', params);
    const grid = document.getElementById('products-grid');
    if (!d.success || !d.products.length) {
        grid.innerHTML = `<div class="empty-state">
            <span class="material-symbols-sharp">inventory_2</span>
            No products found. Try a different search or filter.
        </div>`;
        allProducts = [];
        return;
    }
    allProducts = d.products;
    let sorted = [...allProducts];
    const sort = document.getElementById('filter-sort')?.value;
    if (sort === 'price_asc')  sorted.sort((a,b) => a.price - b.price);
    if (sort === 'price_desc') sorted.sort((a,b) => b.price - a.price);
    grid.innerHTML = sorted.map(p => productCard(p)).join('');
}

function productCard(p) {
    const isFav   = FAV_IDS.has(parseInt(p.id));
    const imgHtml = p.image_url
        ? `<img src="${esc(p.image_url)}" alt="${esc(p.title)}" class="buyer-product-image"
               onerror="this.parentElement.innerHTML='<div class=no-product-img></div>'">`
        : `<div class="no-product-img"></div>`;
    const badge = parseInt(p.stock_qty) <= 0
        ? `<div class="buyer-product-badge out-badge">Out of Stock</div>`
        : p.condition_type === 'Brand New'
            ? `<div class="buyer-product-badge new-badge">Brand New</div>`
            : `<div class="buyer-product-badge used-badge">${esc(p.condition_type)}</div>`;
    return `
    <div class="buyer-product-card">
        <div class="buyer-product-imagebox">
            ${imgHtml}
            ${badge}
            <div class="buyer-product-icons">
                <button class="buyer-product-icon" title="View details"
                        onclick="viewProduct(${p.id})">
                    <span class="material-symbols-sharp">visibility</span>
                </button>
                <button class="buyer-product-icon ${isFav?'fav-on':''}"
                        id="fav-${p.id}" title="Favourite"
                        onclick="toggleFav(${p.id},this)">
                    <span class="material-symbols-sharp">favorite</span>
                </button>
            </div>
        </div>
        <div class="buyer-product-details">
            <span class="buyer-product-category">${esc(p.category_name)}</span>
            <h3 class="buyer-product-name">${esc(p.title)}</h3>
            <h4 class="buyer-product-seller">${esc(p.shop_name)}</h4>
            <div class="buyer-product-bottom">
                <span class="buyer-product-price">R${Number(p.price).toLocaleString('en-ZA')}</span>
                ${parseInt(p.stock_qty) > 0
                    ? `<button class="buyer-cart-btn" title="Add to cart" onclick="addToCart(${JSON.stringify(p).replace(/"/g,'&quot;')})">
                            <span class="material-symbols-sharp">shopping_cart</span>
                       </button>`
                    : `<span style="font-size:11px;color:#aaa;">Available</span>`}
            </div>
        </div>
    </div>`;
}

function searchProducts(q) { loadProducts({ q }); }
function applyFilters()    { loadProducts(); }
function clearFilters() {
    document.getElementById('filter-category').value = '';
    document.getElementById('filter-province').value = '';
    document.getElementById('filter-sort').value     = '';
    document.querySelector('.buyer-search-input').value = '';
    loadProducts();
}


async function viewProduct(id) {
    const d = await api('product_detail', { id });
    if (!d.success) { toast(' Could not load product'); return; }
    const p     = d.product;
    const isFav = FAV_IDS.has(parseInt(p.id));
    const imgHtml = p.image_url
        ? `<img src="${esc(p.image_url)}" class="prod-detail-img" alt="${esc(p.title)}">`
        : `<div class="pd-no-img"></div>`;
    const badge = parseInt(p.stock_qty) <= 0
        ? `<span class="pd-badge" style="background:#f0f0f0;color:#888;">Out of Stock</span>`
        : p.condition_type === 'Brand New'
            ? `<span class="pd-badge" style="background:#d4f8e8;color:#118c4f;">Brand New</span>`
            : `<span class="pd-badge" style="background:#ffe0e0;color:#cc2f2f;">${esc(p.condition_type)}</span>`;

    document.getElementById('product-detail-content').innerHTML = `
    <div class="prod-detail-wrap">
        ${imgHtml}
        <div>
            <div class="pd-cat">${esc(p.category_name)}</div>
            <h2 class="pd-name">${esc(p.title)}</h2>
            <div class="pd-seller">by <strong>${esc(p.seller_name)}</strong> · ${esc(p.shop_name)}</div>
            ${badge}
            <div class="pd-price">R${Number(p.price).toLocaleString('en-ZA')}</div>
            <div class="pd-delivery">
                <strong>Delivery:</strong> ${esc(p.delivery_option)}
                ${p.province ? ` ·  ${esc(p.province)}` : ''}
            </div>
            <div class="pd-desc">${esc(p.description)}</div>
            <div class="pd-actions">
                ${parseInt(p.stock_qty) > 0
                    ? `<button class="pd-cart-btn" onclick='addToCart(${JSON.stringify(p).replace(/"/g,"'")})'>
                            <span class="material-symbols-sharp">shopping_cart</span> Add to Cart
                       </button>`
                    : `<button class="pd-cart-btn" disabled style="opacity:.5;cursor:not-allowed;">Out of Stock</button>`}
                <button class="pd-fav-btn ${isFav?'on':''}" id="pdfav-${p.id}"
                        onclick="pdToggleFav(${p.id})">
                    <span class="material-symbols-sharp">favorite</span>
                    ${isFav ? 'Saved' : 'Save'}
                </button>
            </div>
        </div>
    </div>`;
    openOv('ov-product');
}


async function toggleFav(id, btn) {
    const d = await api('toggle_fav', { product_id: id }, 'POST');
    if (!d.success) { toast(' ' + d.message); return; }
    if (d.action === 'added') {
        FAV_IDS.add(parseInt(id)); btn.classList.add('fav-on');
        toast('Added to favourites!');
    } else {
        FAV_IDS.delete(parseInt(id)); btn.classList.remove('fav-on');
        toast('Removed from favourites');
    }
    updateFavBadge();
}

async function pdToggleFav(id) {
    const btn = document.getElementById('pdfav-' + id);
    const d   = await api('toggle_fav', { product_id: id }, 'POST');
    if (!d.success) return;
    if (d.action === 'added') {
        FAV_IDS.add(parseInt(id));
        btn.classList.add('on');
        btn.innerHTML = '<span class="material-symbols-sharp">favorite</span> Saved';
        toast(' Added to favourites!');
    } else {
        FAV_IDS.delete(parseInt(id));
        btn.classList.remove('on');
        btn.innerHTML = '<span class="material-symbols-sharp">favorite</span> Save';
        toast('Removed from favourites');
    }
    const gridBtn = document.getElementById('fav-' + id);
    if (gridBtn) gridBtn.classList.toggle('fav-on', FAV_IDS.has(parseInt(id)));
    updateFavBadge();
}

async function loadFavs() {
    const d = await api('my_favourites');
    const el = document.getElementById('fav-content');
    if (!d.success || !d.favourites.length) {
        el.innerHTML = `<div class="fav-empty">
            <span class="material-symbols-sharp">favorite_border</span>
            <p>No favourites yet. Click  on any product to save it here.</p>
        </div>`; return;
    }
    el.innerHTML = `<div class="buyer-products-grid">${d.favourites.map(p => productCard(p)).join('')}</div>`;
}

function updateFavBadge() {
    const b = document.getElementById('fav-badge');
    b.textContent = FAV_IDS.size;
    b.style.display = FAV_IDS.size > 0 ? '' : 'none';
}





function saveCart() {
    localStorage.setItem(
        'msa_cart_' + ME_ID,
        JSON.stringify(CART)
    );
}

function addToCart(product) {

    if (typeof product === 'string') {
        product = JSON.parse(product);
    }

    const existing = CART.find(
        item => item.id == product.id
    );

    if (existing) {

        existing.qty += 1;

    } else {

        CART.push({
            id: product.id,
            title: product.title,
            price: parseFloat(product.price),
            shop: product.shop_name,
            img: product.image_url || '',
            qty: 1
        });

    }

    saveCart();
    updateCartBadge();
    renderCart();

    toast('Added to cart!');
}

function increaseQty(id) {

    const item = CART.find(
        item => item.id == id
    );

    if (!item) return;

    item.qty++;

    saveCart();
    updateCartBadge();
    renderCart();
}

function decreaseQty(id) {

    const item = CART.find(
        item => item.id == id
    );

    if (!item) return;

    item.qty--;

    if (item.qty <= 0) {

        CART = CART.filter(
            item => item.id != id
        );

    }

    saveCart();
    updateCartBadge();
    renderCart();
}

function removeFromCart(id) {

    CART = CART.filter(
        item => item.id != id
    );

    saveCart();
    updateCartBadge();
    renderCart();
}

function updateCartBadge() {

    const badge =
        document.getElementById('cart-badge');

    const totalItems = CART.reduce(
        (sum, item) => sum + item.qty,
        0
    );

    badge.textContent = totalItems;

    badge.style.display =
        totalItems > 0 ? '' : 'none';
}

function renderCart() {

    const container =
        document.getElementById('cart-content');

    if (!CART.length) {

        container.innerHTML = `
        <div class="cart-empty">
            <span class="material-symbols-sharp"
                style="font-size:3rem;opacity:.25;display:block;margin-bottom:10px;">
                shopping_cart
            </span>
            <p>Your cart is empty.</p>
        </div>
        `;

        return;
    }

    const total = CART.reduce(
        (sum, item) =>
            sum + (item.price * item.qty),
        0
    );

    container.innerHTML = `
    <div class="cart-wrap">

        <div class="cart-items">

            ${CART.map(item => `
            <div class="cart-row">

                ${item.img
                    ? `<img src="${esc(item.img)}"
                        class="cart-img"
                        onerror="this.style.display='none'">`
                    : `<div class="cart-img"
                        style="
                            display:flex;
                            align-items:center;
                            justify-content:center;
                            font-size:1.5rem;">
                        
                    </div>`
                }

                <div>
                    <div class="cart-name">
                        ${esc(item.title)}
                    </div>

                    <div class="cart-shop">
                        ${esc(item.shop)}
                    </div>
                </div>

                <div class="cart-qty">

                    <button
                        onclick="decreaseQty(${item.id})">
                        -
                    </button>

                    <span>${item.qty}</span>

                    <button
                        onclick="increaseQty(${item.id})">
                        +
                    </button>

                </div>

                <div class="cart-price">
                    R${(
                        item.price * item.qty
                    ).toLocaleString('en-ZA')}
                </div>

                <button
                    class="cart-rm"
                    onclick="removeFromCart(${item.id})">

                    <span
                        class="material-symbols-sharp"
                        style="font-size:1rem;">
                        close
                    </span>

                </button>

            </div>
            `).join('')}

        </div>

        <div class="cart-summary">

            <h3>Order Summary</h3>

            ${CART.map(item => `
            <div class="cs-row">

                <span>
                    ${esc(item.title)}
                    × ${item.qty}
                </span>

                <span>
                    R${(
                        item.price * item.qty
                    ).toLocaleString('en-ZA')}
                </span>

            </div>
            `).join('')}

            <div class="cs-row">
                <span>Delivery</span>
                <span>Courier Guy</span>
            </div>

            <div class="cs-row total">
                <span>Total</span>

                <span>
                    R${total.toLocaleString('en-ZA')}
                </span>
            </div>

            <button
                class="checkout-btn"
                onclick="startCheckout()">

                Proceed to Checkout →

            </button>

        </div>

    </div>`;
}




function startCheckout() { closeOv('ov-cart'); openOv('ov-shipping'); }

function goToBilling() {
    const name  = v('sh-name'), phone = v('sh-phone'), street = v('sh-street');
    const city  = v('sh-city'), prov  = document.getElementById('sh-province').value;
    if (!name||!phone||!street||!city||!prov) { toast(' Please fill in all required fields'); return; }
    shipData = { name, phone, street, city, province: prov, postal: v('sh-postal') };
    closeOv('ov-shipping'); openOv('ov-billing');
}

function toggleBillSame(cb) {
    const fields = document.getElementById('billing-fields');
    fields.style.opacity       = cb.checked ? '.4' : '1';
    fields.style.pointerEvents = cb.checked ? 'none' : '';
}

function togglePaymentFields(val) {
    document.getElementById('card-fields').style.display = val === 'card' ? '' : 'none';
    document.getElementById('eft-fields').style.display  = val === 'eft'  ? '' : 'none';
    document.getElementById('cash-fields').style.display = val === 'cash' ? '' : 'none';
}

function fmtCard(input) {
    let n = input.value.replace(/\D/g,'').substring(0,16);
    input.value = n.replace(/(.{4})/g,'$1 ').trim();
    const brand = document.getElementById('card-brand');
    const raw   = n;
    brand.textContent = /^4/.test(raw) ? 'VISA' : /^5[1-5]/.test(raw) ? ' MC'
                      : /^3[47]/.test(raw) ? 'AMEX' : '';
}

function goToReview() {
    const same   = document.getElementById('bill-same').checked;
    const method = document.getElementById('pay-method').value;

    if (!same) {
        if (!v('bi-name')||!v('bi-street')||!v('bi-city')||
            !document.getElementById('bi-province').value) {
            toast('⚠️ Please fill in all billing fields'); return;
        }
    }
    if (method === 'card') {
        if (!v('pay-name')||!v('pay-cardnum')||
            !document.getElementById('pay-month').value||
            !v('pay-year')||!v('pay-cvv')) {
            toast('Please fill in all card details'); return;
        }
    }

    const billAddr = same ? shipData : {
        name:     v('bi-name'), street: v('bi-street'),
        city:     v('bi-city'), province: document.getElementById('bi-province').value,
        postal:   v('bi-postal'),
    };
    const payLabel = { card:'Credit/Debit Card', eft:'EFT / Bank Transfer', cash:'Cash on Collection' }[method];
    const last4    = method === 'card' ? v('pay-cardnum').replace(/\s/g,'').slice(-4) : '';

    document.getElementById('review-addr').innerHTML = `
    <div class="rev-addr">
        <strong>Ship to:</strong> ${esc(shipData.name)}, ${esc(shipData.street)}, ${esc(shipData.city)}, ${esc(shipData.province)}<br>
        <strong>Bill to:</strong> ${esc(billAddr.name||shipData.name)}, ${esc(billAddr.street||shipData.street)}, ${esc(billAddr.city||shipData.city)}, ${esc(billAddr.province||shipData.province)}<br>
        <strong>Payment:</strong> ${payLabel}${last4 ? ` · ending in <strong>${last4}</strong>` : ''}
    </div>`;

    document.getElementById('review-items').innerHTML = CART.map(c => `
    <div class="rev-item">
        ${c.img ? `<img src="${esc(c.img)}" class="rev-img" onerror="this.style.display='none'">` : `<div class="rev-img" style="display:flex;align-items:center;justify-content:center;font-size:1.5rem;">🛍️</div>`}
        <div><div class="rev-name">${esc(c.title)}</div><div style="font-size:12px;color:#888;">${esc(c.shop)}</div></div>
        <div class="rev-price">R${Number(c.price).toLocaleString('en-ZA')}</div>
    </div>`).join('');

    const total = CART.reduce((s,c) => s + c.price, 0);
    document.getElementById('review-total').textContent = `Total: R${total.toLocaleString('en-ZA')}`;

    closeOv('ov-billing'); openOv('ov-review');
}

async function placeOrder() {
    const btn = document.getElementById('place-btn');
    btn.disabled = true; btn.textContent = 'Placing order…';

    const method = document.getElementById('pay-method').value;
    const same   = document.getElementById('bill-same').checked;
    const billAddr = same ? shipData : {
        name:     v('bi-name'), street: v('bi-street'),
        city:     v('bi-city'), province: document.getElementById('bi-province').value,
        postal:   v('bi-postal'),
    };

   
    
    let allOrderIds = [];
    let allRefs = [];
    for (const item of CART) {
        const fd = new FormData();
        fd.append('product_id',    item.id);
        fd.append('quantity', item.qty);
        fd.append('ship_name',     shipData.name);
        fd.append('ship_street',   shipData.street);
        fd.append('ship_city',     shipData.city);
        fd.append('ship_province', shipData.province);
        fd.append('ship_postal',   shipData.postal || '');
        fd.append('ship_phone',    shipData.phone  || '');
        fd.append('bill_same',     same ? 1 : 0);
        fd.append('bill_name',     billAddr.name   || '');
        fd.append('bill_street',   billAddr.street || '');
        fd.append('bill_city',     billAddr.city   || '');
        fd.append('bill_province', billAddr.province || '');
        fd.append('bill_postal',   billAddr.postal || '');
        fd.append('payment_method', method);
        if (method === 'card') fd.append('card_number', v('pay-cardnum'));

        const d = await api('place_order', {}, 'POST', fd);
       if (d.success) { allRefs.push(d.order_ref); allOrderIds.push(d.order_id); }
        else { toast(' ' + (d.message || 'Order failed for ' + item.title)); }
    }

    btn.disabled = false; btn.textContent = ' Place Order';

   if (allOrderIds.length) {
    CART = []; saveCart(); updateCartBadge();
   
    window.location.href = 'payfast_redirect.php?order_id=' + allOrderIds[0];
}
}


async function loadVendors() {
    const d = await api('sellers_list');
    const el = document.getElementById('vendors-grid');
    if (!d.success || !d.sellers.length) {
        el.innerHTML = `<div class="empty-state" style="grid-column:1/-1;">
            <span class="material-symbols-sharp">storefront</span>No vendors registered yet.
        </div>`; return;
    }
    el.innerHTML = d.sellers.map(s => `
    <div class="vendor-card" onclick="openVendor(${s.id})">
        <div class="vendor-avatar" style="background:${avatarColor(s.shop_name)};">
            ${s.shop_name.charAt(0).toUpperCase()}
        </div>
        <div class="vendor-name">${esc(s.name)}</div>
        <div class="vendor-shop">${esc(s.shop_name)}</div>
        ${s.province ? `<div class="vendor-cat"> ${esc(s.province)}</div>` : ''}
        <div class="vendor-count">${s.product_count} product${s.product_count != 1 ? 's' : ''}</div>
        ${s.rating > 0 ? `<div style="font-size:12px;color:#f5a623;margin-top:4px;">
             ${parseFloat(s.rating).toFixed(1)} (${s.total_reviews} reviews)</div>` : ''}
        <button class="vendor-view-btn">View Shop →</button>
    </div>`).join('');
}

async function openVendor(sellerId) {
    const [sellD, prodD] = await Promise.all([
        api('sellers_list'),
        api('seller_products', { seller_id: sellerId })
    ]);
    const seller  = (sellD.sellers || []).find(s => s.id == sellerId);
    const prods   = prodD.products || [];
    if (!seller) { toast('Vendor not found'); return; }

    const convD = await api('conversations');
    const thread = (convD.conversations || []).find(c => c.other_id == sellerId);

    document.getElementById('vd-title').textContent = seller.shop_name;

    const color   = avatarColor(seller.shop_name);
    const initial = seller.shop_name.charAt(0).toUpperCase();

    let msgBubbles = '';
    if (thread) {
        const td = await api('thread', { other_id: sellerId, product_id: thread.product_id || '' });
        if (td.success && td.messages.length) {
            msgBubbles = td.messages.map(m => {
                const mine = parseInt(m.sender_id) === ME_ID;
                return `<div class="${mine?'mb-sent':'mb-recv'}">${esc(m.message_text)}<div class="mb-time">${formatTime(m.created_at)}</div></div>`;
            }).join('');
        }
    }

    
    const revD = await api('public_seller_reviews', { seller_id: sellerId });
    const reviews = revD.reviews || [];
    const avgRating = revD.average || 0;
    const totalRevs = revD.total || 0;

    const starsHtml = (rating) =>
        [1,2,3,4,5].map(i =>
            `<span style="color:${i <= rating ? '#f4b400' : '#ddd'};font-size:1rem;">★</span>`
        ).join('');

    const reviewsHtml = reviews.length ? reviews.map(r => `
        <div style="border:1px solid #eee;border-radius:12px;padding:14px;margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:34px;height:34px;border-radius:50%;
                        background:${avatarColor(r.buyer_name)};color:#fff;
                        display:flex;align-items:center;justify-content:center;
                        font-size:.85rem;font-weight:700;">
                        ${r.buyer_name.charAt(0).toUpperCase()}
                    </div>
                    <strong style="font-size:.88rem;">${esc(r.buyer_name)}</strong>
                </div>
                <span>${starsHtml(r.rating)}</span>
            </div>
            ${r.comment ? `<p style="font-size:.84rem;color:#555;margin:6px 0 4px;">${esc(r.comment)}</p>` : ''}
            <small style="color:#bbb;">${new Date(r.created_at).toLocaleDateString('en-ZA')}</small>
        </div>
    `).join('') : `<p style="color:#aaa;font-size:.85rem;">No reviews yet for this shop.</p>`;

    const ratingBadge = totalRevs > 0
        ? `<span style="display:inline-flex;align-items:center;gap:5px;background:#fffbe6;
               border:1px solid #f4b400;border-radius:20px;padding:3px 10px;font-size:.82rem;font-weight:700;color:#d4891a;">
               ★ ${avgRating} <span style="font-weight:400;color:#888;">(${totalRevs} review${totalRevs !== 1 ? 's' : ''})</span>
           </span>`
        : '';

    document.getElementById('vendor-detail-content').innerHTML = `
    <div class="vendor-banner">
        <div class="vendor-banner-av" style="background:${color};">${initial}</div>
        <div class="vendor-banner-info">
            <h2>${esc(seller.shop_name)}</h2>
            <p>${esc(seller.name)}${seller.province ? ' · ' + esc(seller.province) : ''}</p>
            ${ratingBadge}
        </div>
    </div>
    <div class="vendor-prods-wrap">
        <h3>Products by ${esc(seller.shop_name)}</h3>
        ${prods.length
            ? `<div class="buyer-products-grid">${prods.map(p => productCard(p)).join('')}</div>`
            : `<p style="color:#aaa;">No active products listed.</p>`}
    </div>

    
    <div style="padding:24px 5%;border-top:1px solid #f0f0f0;margin-top:10px;">
        <h3 style="font-size:1rem;font-weight:800;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
            Customer Reviews ${ratingBadge}
        </h3>
        <div id="vendor-reviews-list">
            ${reviewsHtml}
        </div>
    </div>

    <div class="vendor-msg-box">
        <h3>Message ${esc(seller.shop_name)}</h3>
        ${msgBubbles ? `<div class="msg-bubbles">${msgBubbles}</div>` : ''}
        <textarea id="vendor-msg-input" placeholder="Type your message… e.g. Is this item available?"></textarea>
        <button onclick="sendVendorMsg(${sellerId},'${esc(seller.shop_name)}')">Send Message</button>
    </div>`;

    closeOv('ov-vendors');
    openOv('ov-vendor-detail');
}

async function sendVendorMsg(sellerId, shopName) {
    const input = document.getElementById('vendor-msg-input');
    const text  = input.value.trim();
    if (!text) { toast('Please type a message first'); return; }

    const d = await api('send_message', { receiver_id: sellerId, message_text: text }, 'POST');
    if (!d.success) { toast(' ' + d.message); return; }

    toast(`Message sent to ${shopName}!`);
    input.value = '';

   
    const bubbles = document.querySelector('.msg-bubbles');
    const newBubble = `<div class="mb-sent">${esc(text)}<div class="mb-time">Just now</div></div>`;
    if (bubbles) {
        bubbles.insertAdjacentHTML('beforeend', newBubble);
        bubbles.scrollTop = bubbles.scrollHeight;
    } else {
     
        const msgBox = document.querySelector('.vendor-msg-box');
        const textarea = document.getElementById('vendor-msg-input');
        const div = document.createElement('div');
        div.className = 'msg-bubbles';
        div.innerHTML = newBubble;
        msgBox.insertBefore(div, textarea);
    }
}

function accTab(id, btn) {
    document.querySelectorAll('.acc-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.acc-tab').forEach(b => b.classList.remove('active'));
    document.getElementById(id).classList.add('active');
    btn.classList.add('active');
    if (id === 'tab-orders')   loadMyOrders();
    if (id === 'tab-messages') loadMyMessages();
}

async function loadMyOrders() {
    const d = await api('my_orders');
    const tbody = document.getElementById('acc-orders-tbody');
    if (!d.success || !d.orders.length) {
        tbody.innerHTML = '<tr><td colspan="7" style="padding:1rem;color:#aaa;text-align:center;">No orders yet.</td></tr>';
        return;
    }
    const statusCls = {
        pending:'pur-pending', confirmed:'pur-confirmed', ready:'pur-ready',
        delivered:'pur-delivered', cancelled:'pur-cancelled'
    };
    const statusLbl = {
        pending:'Pending', confirmed:'Confirmed', ready:'Ready for Pickup',
        delivered:'Delivered ', cancelled:'Cancelled'
    };
    tbody.innerHTML = d.orders.map(o => `
    <tr>
        <td style="font-weight:700;">${esc(o.order_ref)}</td>
        <td>${esc(o.product_title)}</td>
        <td>${esc(o.shop_name)}</td>
        <td style="font-weight:700;color:#1a7a4a;">R${Number(o.total_amount).toLocaleString('en-ZA')}</td>
        <td><span class="pur-badge ${statusCls[o.status]||'pur-pending'}">${statusLbl[o.status]||o.status}</span></td>
        <td>${new Date(o.created_at).toLocaleDateString('en-ZA')}</td>
        <td>${o.status === 'delivered'
            ? `<button onclick="openRateModal(${o.id},'${esc(o.shop_name)}')"
                style="background:#1a7a4a;color:#fff;border:none;border-radius:8px;
                padding:5px 12px;font-family:'Poppins',sans-serif;font-size:12px;
                font-weight:600;cursor:pointer;">Rate</button>`
            : '—'}</td>
    </tr>`).join('');
}
async function loadMyMessages() {
    const d = await api('conversations');
    const el = document.getElementById('acc-msgs-list');
    if (!d.success || !d.conversations.length) {
        el.innerHTML = '<p style="color:#aaa;font-size:14px;">No messages yet.</p>';
        return;
    }

    const threads = await Promise.all(d.conversations.map(c =>
        api('thread', { other_id: c.other_id })
            .then(td => ({ convo: c, messages: td.messages || [] }))
    ));

    el.innerHTML = threads.map(({ convo: c, messages: msgs }) => `
    <div class="msg-thread" id="thread-${c.other_id}">
        <div class="mt-hdr">
            <div class="mt-av" style="background:${avatarColor(c.other_shop || c.other_name)};">
                ${(c.other_shop || c.other_name).charAt(0).toUpperCase()}
            </div>
            <div>
                <div class="mt-name">${esc(c.other_shop || c.other_name)}</div>
            </div>
        </div>
        <div class="mt-body" id="msgs-${c.other_id}">
            ${msgs.map(m => {
                const mine = parseInt(m.sender_id) === ME_ID;
                return `<div>
                    <div class="${mine ? 'mb-sent' : 'mb-recv'}">${esc(m.message_text)}</div>
                    <div style="font-size:11px;color:#bbb;text-align:${mine?'right':'left'};margin-top:2px;">
                        ${formatTime(m.created_at)}
                    </div>
                </div>`;
            }).join('')}
        </div>
        <div style="display:flex;gap:8px;margin-top:10px;">
            <textarea id="reply-${c.other_id}" placeholder="Type a reply…"
                style="flex:1;border:1.5px solid #e0e0e0;border-radius:10px;
                padding:10px;font-family:'Poppins',sans-serif;font-size:.83rem;
                resize:none;outline:none;height:42px;"
                onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();buyerReply(${c.other_id});}">
            </textarea>
            <button onclick="buyerReply(${c.other_id})"
                style="background:#1a7a4a;color:#fff;border:none;border-radius:10px;
                padding:0 16px;cursor:pointer;font-family:'Poppins',sans-serif;font-size:.83rem;font-weight:600;">
                Send
            </button>
        </div>
    </div>`).join('');

    document.getElementById('acc-msg-badge').style.display = 'none';
}

async function buyerReply(otherId) {
    const input = document.getElementById('reply-' + otherId);
    const text  = input.value.trim();
    if (!text) return;

    const d = await api('send_message', {
        receiver_id:  otherId,
        message_text: text,
    }, 'POST');

    if (!d.success) { toast(' ' + d.message); return; }

    input.value = '';

    const body = document.getElementById('msgs-' + otherId);
    if (body) {
        body.insertAdjacentHTML('beforeend', `
            <div>
                <div class="mb-sent">${esc(text)}</div>
                <div style="font-size:11px;color:#bbb;text-align:right;margin-top:2px;">Just now</div>
            </div>
        `);
        body.scrollTop = body.scrollHeight;
    }

    toast('Message sent!');
}


async function pollBuyerMessages() {
    const el = document.getElementById('acc-msgs-list');
    if (!el || !el.querySelector('.msg-thread')) return;

    const d = await api('conversations');
    if (!d.success) return;

    for (const c of d.conversations) {
        const body = document.getElementById('msgs-' + c.other_id);
        if (!body) continue;

        const td = await api('thread', { other_id: c.other_id });
        if (!td.success) continue;

       
        body.innerHTML = td.messages.map(m => {
            const mine = parseInt(m.sender_id) === ME_ID;
            return `<div>
                <div class="${mine ? 'mb-sent' : 'mb-recv'}">${esc(m.message_text)}</div>
                <div style="font-size:11px;color:#bbb;text-align:${mine?'right':'left'};margin-top:2px;">
                    ${formatTime(m.created_at)}
                </div>
            </div>`;
        }).join('');
    }
}

setInterval(pollBuyerMessages, 5000);


const COLOURS = ['#1a7a4a','#d72b2b','#f5a623','#555','#862e9c','#1971c2','#e07400','#2f9e44'];
function avatarColor(name) {
    let h = 0; for (const c of (name||'?')) h = (h*31+c.charCodeAt(0)) & 0x7fffffff;
    return COLOURS[h % COLOURS.length];
}
function v(id)  { return (document.getElementById(id)?.value||'').trim(); }
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
                        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function formatTime(dt) {
    if (!dt) return '';
    return new Date(dt).toLocaleTimeString('en-ZA',{hour:'2-digit',minute:'2-digit'});
}
function toast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._t); t._t = setTimeout(()=>t.classList.remove('show'),3000);
}


loadCategories();
loadProducts();
updateCartBadge();
updateFavBadge();


let rateOrderId   = null;
let selectedStars = 0;
const starLabels  = ['','Poor','Fair','Good','Very Good','Excellent'];

async function openRateModal(orderId, shopName) {
    rateOrderId   = orderId;
    selectedStars = 0;
    document.getElementById('rate-modal-title').textContent = 'Rate ' + shopName;
    document.getElementById('rate-shop-name').textContent   = shopName;
    document.getElementById('rate-comment').value           = '';
    document.getElementById('star-label').textContent       = '';
    document.querySelectorAll('.star-btn').forEach(s => s.style.opacity = '.3');

 
    const d = await api('check_review', { order_id: orderId });
    const ov = document.getElementById('rate-modal-ov');
    ov.style.display = 'flex';

    if (d.reviewed && d.review) {
        document.getElementById('rate-form').style.display       = 'none';
        document.getElementById('rate-already-done').style.display = 'block';
        document.getElementById('rate-done-stars').textContent   = '<span class="material-symbols-outlined">star</span>'.repeat(d.review.rating);
        document.getElementById('rate-done-comment').textContent = d.review.comment || 'No comment left.';
    } else {
        document.getElementById('rate-form').style.display       = 'block';
        document.getElementById('rate-already-done').style.display = 'none';
    }
}

function closeRateModal() {
    document.getElementById('rate-modal-ov').style.display = 'none';
    rateOrderId = null; selectedStars = 0;
}

function selectStar(n) {
    selectedStars = n;
    document.querySelectorAll('.star-btn').forEach(s => {
        s.style.opacity = parseInt(s.dataset.v) <= n ? '1' : '.25';
    });
    document.getElementById('star-label').textContent = starLabels[n] || '';
}

async function submitRating() {
    if (!selectedStars) { toast('Please select a star rating first'); return; }
    const comment = document.getElementById('rate-comment').value.trim();
    const d = await api('submit_review', {
        order_id: rateOrderId,
        rating:   selectedStars,
        comment:  comment,
    }, 'POST');
    toast(d.success ? ' Review submitted!' : ' ' + d.message);
    if (d.success) { closeRateModal(); loadMyOrders(); }
}

document.getElementById('rate-modal-ov').addEventListener('click', function(e) {
    if (e.target === this) closeRateModal();
});




</script>
</body>
</html>
