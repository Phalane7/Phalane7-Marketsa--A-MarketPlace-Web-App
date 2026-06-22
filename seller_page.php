<?php
require_once 'db.php';
bootSession();
$user = mustSeller('login.php');
$pdo  = db();


$sp = $pdo->prepare("
    SELECT u.id, u.name, u.email,
           COALESCE(sp.shop_name, u.name) AS shop_name,
           COALESCE(sp.province,'') AS province,
           COALESCE(sp.city,'') AS city,
           COALESCE(sp.phone,'') AS phone,
           COALESCE(sp.shop_bio,'') AS shop_bio,
           COALESCE(sp.rating,0) AS rating,
           COALESCE(sp.total_reviews,0) AS total_reviews
    FROM users u
    LEFT JOIN seller_profiles sp ON sp.user_id = u.id
    WHERE u.id = ?
");
$sp->execute([$user['id']]);
$profile = $sp->fetch();

$shopName = $profile['shop_name'] ?? $user['name'];
$initial  = strtoupper(substr($shopName, 0, 1));


$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
$unreadStmt->execute([$user['id']]);
$unreadCount = (int)$unreadStmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketSA - Seller Dashboard</title>
    <link rel="icon" type="image/jpg" href="MarketSA.jpeg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@24,400,0,0"/>
<style>
:root{
    --primary:#2546a8;--danger:#e03131;--success:#1a7a4a;--warning:#f5a623;
    --white:#fff;--bg:#f6f6f9;--light:rgba(132,139,200,.18);
    --dark:#363949;--dark-v:#677483;--info-dark:#7d8da1;
    --shadow:0 2rem 3rem var(--light);
    --card-radius:2rem;--br1:.4rem;--br2:.8rem;--br3:1.2rem;--cp:1.8rem;
}
.dark-theme{--bg:#181a1e;--white:#202528;--dark:#edeffd;--dark-v:#a3bdcc;--light:rgba(0,0,0,.4);}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{width:100vw;min-height:100vh;font-family:'Poppins',sans-serif;font-size:14px;
    background:var(--bg);overflow-x:hidden;}
a{color:var(--dark);text-decoration:none;}
img{display:block;width:100%;}
small{font-size:.75rem;}
.text-muted{color:var(--info-dark);}
.success{color:var(--success);} .danger{color:var(--danger);} .warning{color:var(--warning);}

/* ── LAYOUT ── */
.dash-wrap{display:grid;grid-template-columns:14rem 1fr;width:98%;margin:0 auto;gap:1.8rem;}

/* ── SIDEBAR ── */
aside{height:100vh;background:var(--white);box-shadow:var(--shadow);position:sticky;top:0;}
aside .top{display:flex;align-items:center;justify-content:space-between;padding:1.4rem;}
.logo{font-size:28px;font-weight:700;color:var(--dark);}
.logo span{color:var(--primary);}
aside .sidebar{display:flex;flex-direction:column;height:86vh;position:relative;top:1rem;}
aside .sidebar a{display:flex;color:var(--info-dark);margin-left:2rem;gap:1rem;
    align-items:center;position:relative;height:3.7rem;transition:.3s;}
aside .sidebar a span.material-symbols-sharp{font-size:1.6rem;transition:.3s;}
aside .sidebar a:last-child{position:absolute;bottom:2rem;width:100%;}
aside .sidebar a.active{background:var(--light);color:var(--primary);margin-left:0;}
aside .sidebar a.active::before{content:'';width:6px;height:100%;background:var(--primary);}
aside .sidebar a.active span.material-symbols-sharp{color:var(--primary);margin-left:calc(1rem - 3px);}
aside .sidebar a:hover{color:var(--primary);}
aside .sidebar a:hover span.material-symbols-sharp{margin-left:1rem;}
.msg-badge{background:var(--danger);color:#fff;padding:2px 8px;font-size:11px;border-radius:var(--br1);}

/* ── MAIN ── */
main{margin-top:1.4rem;min-height:100vh;}

/* section pages */
.sec { display: none; }
.sec.active { display: block; }

.sec-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;}
.sec-header h1{font-size:1.4rem;font-weight:800;}
.back-btn{background:none;border:1px solid #ddd;border-radius:8px;padding:7px 14px;
    cursor:pointer;font-family:'Poppins',sans-serif;font-size:13px;color:#555;
    display:flex;align-items:center;gap:5px;transition:.2s;}
.back-btn:hover{border-color:var(--primary);color:var(--primary);}

/* insights */
.date{display:inline-block;background:var(--light);border-radius:var(--br1);
    margin-top:1rem;padding:.2rem 1rem;}
.date input{background:transparent;border:none;outline:none;height:28px;font-size:13px;}
.insights{display:grid;grid-template-columns:repeat(4,1fr);gap:1.6rem;margin-top:1rem;}
.insight-card{background:var(--white);padding:var(--cp);border-radius:var(--card-radius);
    box-shadow:var(--shadow);transition:.3s;position:relative;}
.insight-card:hover{box-shadow:none;}
.insight-card > span.material-symbols-sharp{background:var(--primary);padding:.5rem;
    border-radius:50%;color:white;font-size:1.5rem;}
.insight-card.listing > span.material-symbols-sharp{background:var(--danger);}
.insight-card.msgs > span.material-symbols-sharp{background:var(--success);}
.insight-card.rating > span.material-symbols-sharp{background:var(--warning);}
.insight-card h3{font-size:.87rem;font-weight:500;margin-top:.8rem;}
.insight-card h1{font-size:1.8rem;font-weight:800;margin:.3rem 0;}
.trend{position:absolute;top:14px;right:14px;padding:5px 11px;border-radius:20px;
    font-size:12px;font-weight:600;}
.t-up{background:rgba(26,122,74,.1);color:green;}
.t-new{background:rgba(245,166,35,.15);color:orange;}

/* orders table */
.orders-box{background:var(--white);border-radius:var(--card-radius);
    padding:var(--cp);box-shadow:var(--shadow);margin-top:1.4rem;}
.orders-box h2{margin-bottom:.8rem;}
.orders-box table{width:100%;border-collapse:collapse;text-align:center;}
.orders-box table th{padding:.6rem .5rem;font-size:.78rem;color:var(--info-dark);
    text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--light);}
.orders-box table td{height:2.8rem;border-bottom:1px solid var(--light);color:var(--dark-v);
    font-size:.82rem;}
.orders-box table tr:last-child td{border:none;}
.orders-box > a{display:block;text-align:center;margin:1rem auto;color:var(--primary);}
.ord-badge{font-size:.7rem;font-weight:700;padding:3px 10px;border-radius:50px;}
.s-pending{background:rgba(224,49,49,.1);color:#e03131;}
.s-confirmed{background:rgba(37,70,168,.1);color:#2546a8;}
.s-ready{background:rgba(245,166,35,.15);color:#d4891a;}
.s-delivered{background:rgba(26,122,74,.12);color:#1a7a4a;}
.s-cancelled{background:rgba(0,0,0,.07);color:#666;}
.act-btn{padding:4px 11px;border:1px solid #ddd;border-radius:7px;cursor:pointer;
    font-size:.72rem;font-family:'Poppins',sans-serif;background:none;transition:.2s;}
.act-btn:hover{border-color:var(--primary);color:var(--primary);}

/* ── LISTINGS ── */
.toolbar{display:flex;gap:1rem;margin-bottom:1.2rem;flex-wrap:wrap;align-items:center;}
.srch{flex:1;min-width:200px;display:flex;align-items:center;gap:8px;background:var(--white);
    border:1px solid #eee;border-radius:10px;padding:8px 12px;
    box-shadow:0 1px 4px rgba(0,0,0,.05);}
.srch input{border:none;outline:none;background:none;font-family:'Poppins',sans-serif;
    font-size:.85rem;width:100%;}
.ftab{background:var(--white);border:1px solid #eee;border-radius:10px;padding:8px 14px;
    cursor:pointer;font-size:.83rem;font-family:'Poppins',sans-serif;color:#666;transition:.2s;}
.ftab:hover,.ftab.active{border-color:var(--primary);color:var(--primary);}

.listings-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:1.2rem;}
.lcard{background:var(--white);border-radius:1.2rem;box-shadow:var(--shadow);overflow:hidden;transition:.2s;}
.lcard:hover{transform:translateY(-4px);box-shadow:0 12px 30px rgba(0,0,0,.1);}
.lcard-img{height:150px;background:#f5f5f5;position:relative;overflow:hidden;
    display:flex;align-items:center;justify-content:center;}
.lcard-img img{width:100%;height:100%;object-fit:cover;}
.lcard-img .no-img{font-size:2.5rem;opacity:.25;}
.lcard-badge{position:absolute;top:8px;right:8px;font-size:.63rem;font-weight:700;
    padding:3px 9px;border-radius:50px;}
.b-active{background:rgba(26,122,74,.15);color:#1a7a4a;}
.b-pending{background:rgba(245,166,35,.15);color:#d4891a;}
.b-sold{background:rgba(0,0,0,.07);color:#666;}
.lcard-body{padding:12px;}
.lcard-title{font-size:.87rem;font-weight:700;margin-bottom:2px;}
.lcard-cat{font-size:.72rem;color:#888;margin-bottom:6px;}
.lcard-foot{display:flex;align-items:center;justify-content:space-between;}
.lcard-price{font-weight:800;color:var(--success);font-size:.97rem;}
.lcard-views{font-size:.7rem;color:#aaa;}
.lcard-actions{display:flex;gap:5px;padding:9px 12px;border-top:1px solid #f0f0f0;}
.la{flex:1;padding:6px;border:1px solid #eee;border-radius:7px;cursor:pointer;
    font-size:.7rem;font-family:'Poppins',sans-serif;font-weight:600;background:none;
    color:#444;transition:.2s;}
.la:hover{border-color:var(--primary);color:var(--primary);}
.la.del:hover{border-color:var(--danger);color:var(--danger);}
.add-card{background:transparent;border:2px dashed #ddd;border-radius:1.2rem;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    min-height:240px;cursor:pointer;transition:.2s;color:#aaa;gap:6px;}
.add-card:hover{border-color:var(--primary);color:var(--primary);}

/* ── INBOX ── */
.inbox-wrap{display:grid;grid-template-columns:290px 1fr;background:var(--white);
    border-radius:1.5rem;box-shadow:var(--shadow);overflow:hidden;
    height:calc(100vh - 130px);}
.convo-list{border-right:1px solid var(--light);display:flex;flex-direction:column;}
.convo-srch{padding:1rem;border-bottom:1px solid var(--light);}
.convo-srch input{width:100%;background:#f5f5f5;border:1px solid #eee;border-radius:10px;
    padding:8px 12px;font-family:'Poppins',sans-serif;font-size:13px;outline:none;}
.convo-srch input:focus{border-color:var(--primary);}
.convos{flex:1;overflow-y:auto;}
.ci{display:flex;align-items:flex-start;gap:10px;padding:13px 14px;
    border-bottom:1px solid var(--light);cursor:pointer;transition:.15s;}
.ci:hover{background:#f9f9f9;}
.ci.active{background:rgba(37,70,168,.06);border-right:3px solid var(--primary);}
.ci-av{width:42px;height:42px;border-radius:50%;display:flex;align-items:center;
    justify-content:center;font-weight:700;color:#fff;font-size:.9rem;flex-shrink:0;}
.ci-body{flex:1;min-width:0;}
.ci-name{font-size:.84rem;font-weight:700;color:#222;}
.ci-prod{font-size:.69rem;color:var(--primary);font-weight:600;margin-top:1px;}
.ci-prev{font-size:.73rem;color:#888;margin-top:2px;white-space:nowrap;
    overflow:hidden;text-overflow:ellipsis;}
.ci-meta{text-align:right;flex-shrink:0;}
.ci-time{font-size:.66rem;color:#bbb;}
.ci-unread{display:inline-block;background:var(--danger);color:#fff;font-size:.58rem;
    font-weight:700;padding:1px 6px;border-radius:50px;margin-top:2px;}

.chat-pane{display:flex;flex-direction:column;}
.chat-hdr{display:flex;align-items:center;gap:10px;padding:12px 18px;
    border-bottom:1px solid var(--light);flex-shrink:0;}
.chat-hdr .ch-av{width:40px;height:40px;border-radius:50%;display:flex;
    align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:.9rem;}
.ch-name{font-size:.88rem;font-weight:700;}
.ch-prod{font-size:.72rem;color:var(--primary);font-weight:600;}
.chat-msgs{flex:1;overflow-y:auto;padding:16px 18px;
    display:flex;flex-direction:column;gap:10px;}
.bw{display:flex;flex-direction:column;}
.bw.buyer{align-items:flex-start;} .bw.seller{align-items:flex-end;}
.blbl{font-size:.66rem;color:#aaa;margin-bottom:2px;padding:0 4px;}
.bbl{max-width:68%;padding:9px 13px;border-radius:15px;font-size:.83rem;line-height:1.55;}
.buyer .bbl{background:#f0f0f0;color:#333;border-bottom-left-radius:3px;}
.seller .bbl{background:var(--primary);color:#fff;border-bottom-right-radius:3px;}
.btime{font-size:.65rem;color:#bbb;padding:0 4px;margin-top:1px;}
.dsep{text-align:center;font-size:.69rem;color:#bbb;margin:4px 0;}
.chat-inp{padding:12px 18px;border-top:1px solid var(--light);
    display:flex;align-items:flex-end;gap:8px;flex-shrink:0;}
.chat-inp textarea{flex:1;resize:none;border:1.5px solid #ddd;border-radius:12px;
    padding:9px 13px;font-family:'Poppins',sans-serif;font-size:.84rem;outline:none;
    min-height:40px;max-height:100px;transition:.2s;}
.chat-inp textarea:focus{border-color:var(--primary);}
.send-btn{width:40px;height:40px;border-radius:11px;background:var(--primary);
    color:#fff;border:none;cursor:pointer;display:flex;align-items:center;
    justify-content:center;font-size:1.1rem;transition:.2s;}
.send-btn:hover{background:#1a36a0;}
.empty-pane{flex:1;display:flex;flex-direction:column;align-items:center;
    justify-content:center;color:#ccc;gap:6px;font-size:.87rem;}

/* ── PROFILE ── */
.profile-grid{display:grid;grid-template-columns:280px 1fr;gap:1.4rem;}
.pcard{background:var(--white);border-radius:1.5rem;box-shadow:var(--shadow);overflow:hidden;}
.pbanner{height:80px;background:linear-gradient(135deg,#9f25a8,#c2255c);}
.pbody{padding:0 1.4rem 1.4rem;}
.pav{width:68px;height:68px;border-radius:50%;background:linear-gradient(135deg,#9f25a8,#f5a623);
    border:4px solid #fff;margin-top:-34px;display:flex;align-items:center;
    justify-content:center;font-size:1.7rem;font-weight:700;color:#fff;}
.pname{font-size:1.1rem;font-weight:800;margin:.6rem 0 .2rem;}
.pmeta{font-size:.77rem;color:#888;}
.pstats{display:grid;grid-template-columns:1fr 1fr 1fr;gap:7px;margin-top:1rem;}
.pstat{text-align:center;padding:9px 4px;background:#f9f9f9;border-radius:9px;}
.pstat .n{font-size:1.2rem;font-weight:900;color:var(--primary);}
.pstat .l{font-size:.67rem;color:#888;}
.scard{background:var(--white);border-radius:1.5rem;box-shadow:var(--shadow);padding:1.4rem;}
.scard h3{font-size:.95rem;font-weight:700;margin-bottom:1.2rem;
    padding-bottom:9px;border-bottom:1px solid #f0f0f0;}
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:13px;}
.fg{display:flex;flex-direction:column;gap:4px;}
.fg.full{grid-column:1/-1;}
.fg label{font-size:.74rem;font-weight:700;color:#555;letter-spacing:.04em;}
.fg input,.fg select,.fg textarea{padding:10px 12px;border:1.5px solid #eee;
    border-radius:9px;font-family:'Poppins',sans-serif;font-size:.84rem;outline:none;transition:.2s;}
.fg input:focus,.fg select:focus,.fg textarea:focus{border-color:var(--primary);}
.fg textarea{resize:vertical;min-height:75px;}
.save-btn{background:var(--primary);color:#fff;border:none;border-radius:9px;
    padding:10px 24px;cursor:pointer;font-family:'Poppins',sans-serif;
    font-size:.84rem;font-weight:700;transition:.2s;margin-top:.8rem;float:right;}
.save-btn:hover{background:#1a36a0;}

/* ── MODAL ── */
.modal-ov{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;
    align-items:center;justify-content:center;z-index:1000;padding:1rem;
    backdrop-filter:blur(4px);}
.modal-ov.open{display:flex;}
.modal-box{background:#fff;border-radius:1.5rem;width:100%;max-width:560px;
    max-height:90vh;overflow-y:auto;padding:1.8rem;
    box-shadow:0 30px 80px rgba(0,0,0,.2);animation:slideUp .3s ease;}
@keyframes slideUp{from{transform:translateY(30px);opacity:0}to{transform:translateY(0);opacity:1}}
.modal-box h2{font-size:1.15rem;font-weight:800;margin-bottom:1.4rem;}
.modal-close{float:right;background:none;border:1px solid #ddd;border-radius:7px;
    width:32px;height:32px;cursor:pointer;font-size:.95rem;margin-top:-4px;}
.upload-zone{border:2px dashed #ddd;border-radius:11px;padding:1.4rem;
    text-align:center;cursor:pointer;transition:.2s;background:#f9f9f9;margin-bottom:1rem;}
.upload-zone:hover{border-color:var(--primary);}
.upload-zone p{font-size:.8rem;color:#aaa;margin-top:5px;}
.img-prev-row{display:flex;gap:7px;flex-wrap:wrap;margin-top:7px;}
.img-prev-row img{width:60px;height:60px;object-fit:cover;border-radius:7px;
    border:2px solid var(--primary);}
.mfooter{display:flex;gap:9px;justify-content:flex-end;margin-top:.8rem;}
.btn-cancel{background:none;border:1.5px solid #eee;border-radius:9px;padding:9px 20px;
    cursor:pointer;font-family:'Poppins',sans-serif;font-size:.84rem;font-weight:600;}
.btn-submit{background:var(--primary);color:#fff;border:none;border-radius:9px;
    padding:9px 22px;cursor:pointer;font-family:'Poppins',sans-serif;
    font-size:.84rem;font-weight:700;transition:.2s;}
.btn-submit:hover{background:#1a36a0;}
.btn-submit:disabled{opacity:.6;cursor:not-allowed;}

/* ── ANALYTICS ── */
.an-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin-bottom:1.2rem;}
.an-card{background:var(--white);border-radius:1.1rem;box-shadow:var(--shadow);padding:1.3rem;}
.an-label{font-size:.74rem;color:#888;margin-bottom:3px;}
.an-num{font-size:1.6rem;font-weight:900;}
.an-trend{font-size:.73rem;font-weight:600;margin-top:3px;}
.chart-card{background:var(--white);border-radius:1.4rem;box-shadow:var(--shadow);
    padding:1.4rem;margin-bottom:1.1rem;}
.chart-card h3{font-size:.94rem;font-weight:700;margin-bottom:1rem;}
.bar-chart{display:flex;align-items:flex-end;gap:8px;height:110px;}
.bar-col{display:flex;flex-direction:column;align-items:center;gap:3px;flex:1;}
.bar{border-radius:5px 5px 0 0;width:100%;}
.bar-lbl{font-size:.65rem;color:#aaa;}
.bar-val{font-size:.65rem;font-weight:700;color:#555;}

/* ── TOAST ── */
.toast{position:fixed;bottom:20px;right:20px;background:#222;color:#fff;
    padding:10px 16px;border-radius:11px;font-size:.84rem;font-weight:500;
    transform:translateY(70px);opacity:0;transition:.3s;z-index:9999;}
.toast.show{transform:translateY(0);opacity:1;}

/* ── PAYMENTS ── */
.pay-grid{display:grid;grid-template-columns:1fr 1fr;gap:13px;}

/* responsive */
@media(max-width:1100px){
    .dash-wrap{grid-template-columns:7rem 1fr;gap:1rem;}
    aside .sidebar a{width:5.5rem;justify-content:center;}
    aside .sidebar a h3{display:none;}
    aside .sidebar a:last-child{position:relative;margin-top:1.5rem;}
    .insights{grid-template-columns:1fr 1fr;}
}
</style>
</head>
<body>

<div class="dash-wrap">


<aside>
    <div class="top">
        <a href="#" class="logo" onclick="showSec('sec-dashboard',this);return false;">
            Market<span>SA</span>
        </a>
    </div>
    <nav class="sidebar" id="sidebar">
        <a href="#" class="active" id="nav-dashboard"
           onclick="showSec('sec-dashboard',this);return false;">
            <span class="material-symbols-sharp">grid_view</span><h3>Dashboard</h3>
        </a>
        <a href="#" id="nav-listings"
           onclick="showSec('sec-listings',this);return false;">
            <span class="material-symbols-sharp">inventory</span><h3>My Listings</h3>
        </a>
        <a href="#" id="nav-orders"
           onclick="showSec('sec-orders',this);return false;">
            <span class="material-symbols-sharp">receipt_long</span><h3>Orders</h3>
        </a>
        <a href="#" id="nav-inbox"
           onclick="showSec('sec-inbox',this);return false;">
            <span class="material-symbols-sharp">mail_outline</span><h3>Messages</h3>
            <span class="msg-badge" id="unread-badge"
                  style="<?= $unreadCount > 0 ? '' : 'display:none' ?>">
                <?= $unreadCount ?>
            </span>
        </a>
        <a href="#" id="nav-analytics"
           onclick="showSec('sec-analytics',this);return false;">
            <span class="material-symbols-sharp">insights</span><h3>Analytics</h3>
        </a>
        <a href="#" id="nav-profile"
           onclick="showSec('sec-profile',this);return false;">
            <span class="material-symbols-sharp">person</span><h3>My Profile</h3>
        </a>
        <a href="#" id="nav-payments"
           onclick="showSec('sec-payments',this);return false;">
            <span class="material-symbols-sharp">payments</span><h3>Payments</h3>
        </a>
        <a href="#" onclick="openAddModal();return false;">
            <span class="material-symbols-sharp">add</span><h3>Add Product</h3>
        </a>
        <a href="logout.php">
            <span class="material-symbols-sharp">logout</span><h3>Log Out</h3>
        </a>
    </nav>
</aside>

<!--main-->
<main>

<div style="display:flex;align-items:center;justify-content:space-between;
    padding:1rem 0;margin-bottom:.5rem;">
    <div>
        <h1 id="page-title" style="font-size:1.4rem;font-weight:800;">Dashboard</h1>
    </div>
    <div style="display:flex;align-items:center;gap:1rem;">
        <div id="theme-tog" style="background:var(--light);display:flex;align-items:center;
            height:1.6rem;width:4.2rem;cursor:pointer;border-radius:var(--br1);">
            <span class="material-symbols-sharp active" id="tog-light"
                  style="font-size:1.1rem;width:50%;height:100%;display:flex;
                  align-items:center;justify-content:center;
                  background:var(--primary);color:#fff;border-radius:var(--br1);"
                  onclick="setTheme('light')">light_mode</span>
            <span class="material-symbols-sharp" id="tog-dark"
                  style="font-size:1.1rem;width:50%;height:100%;display:flex;
                  align-items:center;justify-content:center;"
                  onclick="setTheme('dark')">dark_mode</span>
        </div>
        <div style="display:flex;align-items:center;gap:.8rem;">
            <div style="text-align:right;">
                <div style="font-size:.84rem;font-weight:600;"><?= xss($shopName) ?></div>
                <small class="text-muted">Seller</small>
            </div>
            <div style="width:46px;height:46px;border-radius:50%;background:#9f25a8;
                color:#fff;display:flex;align-items:center;justify-content:center;
                font-size:1.2rem;font-weight:700;">
                <?= $initial ?>
            </div>
        </div>
    </div>
</div>


<section class="sec " id="sec-dashboard">
    <div class="date"><input type="date" id="today-date"></div>

    <div class="insights" id="insights-row">
        <!-- populated by JS -->
        <div class="insight-card">
            <span class="material-symbols-sharp">analytics</span>
            <h3>Total Sales</h3>
            <h1 id="stat-earnings">—</h1>
            <span class="trend t-up" id="stat-orders-lbl">Loading…</span>
        </div>
        <div class="insight-card listing">
            <span class="material-symbols-sharp">list</span>
            <h3>Active Listings</h3>
            <h1 id="stat-listings">—</h1>
        </div>
        <div class="insight-card msgs">
            <span class="material-symbols-sharp">chat_bubble</span>
            <h3>Unread Messages</h3>
            <h1 id="stat-unread">—</h1>
            <span class="trend t-new" id="stat-unread-lbl"></span>
        </div>
       
    </div>

    <div class="orders-box" style="margin-top:1.4rem;">
        <h2>Recent Orders</h2>
        <table>
            <thead>
                <tr>
                    <th>Order No.</th><th>Buyer</th><th>Product</th>
                    <th>Amount</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody id="recent-orders-tbody">
                <tr><td colspan="6" style="padding:1rem;color:#aaa;">Loading…</td></tr>
            </tbody>
        </table>
        <a href="#" onclick="showSec('sec-orders',document.getElementById('nav-orders'));return false;">
            Show All
        </a>
    </div>
</section>


<section class="sec" id="sec-listings">
    <div class="sec-header">
      
        <div style="display:flex;gap:8px;">
            <button class="btn-submit" onclick="openAddModal()"
                    style="padding:8px 16px;font-size:.82rem;">+ Add Product</button>
            <button class="back-btn" onclick="showSec('sec-dashboard',document.getElementById('nav-dashboard'))">
                <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
            </button>
        </div>
    </div>
    <div class="toolbar">
        <div class="srch">
            <span class="material-symbols-sharp" style="color:#aaa;font-size:1rem;">search</span>
            <input type="text" id="listings-q" placeholder="Search listings…"
                   oninput="loadListings()">
        </div>
        <button class="ftab active" onclick="setListingFilter('',this)">All</button>
        <button class="ftab" onclick="setListingFilter('active',this)">Active</button>
        <button class="ftab" onclick="setListingFilter('pending',this)">Pending</button>
      
    </div>
    <div class="listings-grid" id="listings-grid">
        <div style="color:#aaa;padding:1rem;">Loading…</div>
    </div>
</section>


<section class="sec" id="sec-orders">
    <div class="sec-header">
     
        <button class="back-btn" onclick="showSec('sec-dashboard',document.getElementById('nav-dashboard'))">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
    </div>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:1.2rem;"
         id="orders-summary"></div>
    <div class="orders-box" style="margin-top:0;">
        <table>
            <thead>
                <tr>
                    <th>Order No.</th><th>Buyer</th><th>Product</th>
                    <th>Amount</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody id="orders-tbody">
                <tr><td colspan="6" style="padding:1rem;color:#aaa;">Loading…</td></tr>
            </tbody>
        </table>
    </div>
</section>


<section class="sec" id="sec-inbox">
    <div class="sec-header">
       
        <button class="back-btn" onclick="showSec('sec-dashboard',document.getElementById('nav-dashboard'))">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
    </div>
    <div class="inbox-wrap">
        <div class="convo-list">
            <div class="convo-srch">
                <input type="text" placeholder="Search conversations…"
                       oninput="filterConvos(this.value)">
            </div>
            <div class="convos" id="convo-list">
                <div style="padding:1rem;color:#aaa;font-size:.83rem;">Loading…</div>
            </div>
        </div>
        <div class="chat-pane" id="chat-pane">
            <div class="empty-pane">
                <span class="material-symbols-sharp" style="font-size:3rem;opacity:.3;">chat_bubble_outline</span>
                <p>Select a conversation</p>
            </div>
        </div>
    </div>
</section>


<section class="sec" id="sec-analytics">
    <div class="sec-header">
        
        <button class="back-btn" onclick="showSec('sec-dashboard',document.getElementById('nav-dashboard'))">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
    </div>
    <div class="an-grid" id="an-grid">
        <div style="color:#aaa;padding:1rem;grid-column:1/-1;">Loading…</div>
    </div>
    <div class="chart-card">
        <h3>Monthly Revenue (last 6 months)</h3>
        <div class="bar-chart" id="bar-chart">
            <div style="color:#aaa;font-size:.83rem;">No data yet</div>
        </div>
    </div>
</section>


<section class="sec" id="sec-profile">
    <div class="sec-header">
      
        <button class="back-btn" onclick="showSec('sec-dashboard',document.getElementById('nav-dashboard'))">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
    </div>
    <div class="profile-grid">
        <div>
            <div class="pcard">
                <div class="pbanner"></div>
                <div class="pbody">
                    <div class="pav" id="prof-initial"><?= $initial ?></div>
                    <div class="pname" id="prof-shopname"><?= xss($shopName) ?></div>
                    <div class="pmeta" id="prof-meta">
                        <?= xss($profile['city'] ?? '') ?><?= $profile['city'] && $profile['province'] ? ', ' : '' ?><?= xss($profile['province'] ?? '') ?>
                    </div>
                    <div class="pstats">
                        <div class="pstat"><div class="n" id="ps-listings">—</div><div class="l">Listings</div></div>
                        <div class="pstat"><div class="n" id="ps-rating">—</div><div class="l">Rating</div></div>
                        <div class="pstat"><div class="n" id="ps-reviews">—</div><div class="l">Reviews</div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="scard">
            <h3>Edit Profile</h3>
            <div class="fgrid">
                <div class="fg"><label>FULL NAME</label>
                    <input type="text" id="pf-name" value="<?= xss($profile['name'] ?? '') ?>"></div>
                <div class="fg"><label>SHOP NAME</label>
                    <input type="text" id="pf-shop" value="<?= xss($profile['shop_name'] ?? '') ?>"></div>
                <div class="fg"><label>PROVINCE</label>
                    <select id="pf-province">
                        <option value="">Select…</option>
                        <?php foreach(['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape',
                            'Limpopo','Mpumalanga','North West','Free State','Northern Cape'] as $p):?>
                        <option <?= ($profile['province']??'') === $p ? 'selected':'' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="fg"><label>CITY</label>
                    <input type="text" id="pf-city" value="<?= xss($profile['city'] ?? '') ?>"></div>
                <div class="fg"><label>PHONE</label>
                    <input type="text" id="pf-phone" value="<?= xss($profile['phone'] ?? '') ?>"></div>
                <div class="fg"><label>EMAIL</label>
                    <input type="email" id="pf-email" value="<?= xss($profile['email'] ?? '') ?>" disabled></div>
                <div class="fg full"><label>SHOP DESCRIPTION</label>
                    <textarea id="pf-bio"><?= xss($profile['shop_bio'] ?? '') ?></textarea></div>
            </div>
            <button class="save-btn" onclick="saveProfile()">Save Changes</button>
        </div>
    </div>
    
    <div class="scard" style="margin-top:20px;">
        <h3>Customer Reviews</h3>

        <div id="seller-reviews">
            <div style="color:#777;padding:20px;text-align:center;">
                Loading reviews...
            </div>
        </div>
    </div>

</section>


<section class="sec" id="sec-payments">
    <div class="sec-header">
      

        <button class="back-btn" onclick="showSec('sec-dashboard',document.getElementById('nav-dashboard'))">
            <span class="material-symbols-sharp" style="font-size:1rem;">arrow_back</span> Back
        </button>
    </div>

    <div class="scard">
        <h3>Bank / EFT Details</h3>

        <p style="font-size:.85rem;color:#888;margin-bottom:1rem;">
            Add your banking details so buyers can pay you via EFT.
        </p>

        <div class="pay-grid">
            <div class="fg">
                <label>BANK NAME</label>
                <input type="text" id="pay-bank" placeholder="e.g. Standard Bank">
            </div>

            <div class="fg">
                <label>ACCOUNT HOLDER NAME</label>
                <input type="text" id="pay-holder" placeholder="Name on account">
            </div>

            <div class="fg">
                <label>ACCOUNT NUMBER</label>
                <input type="text" id="pay-account" placeholder="Account number">
            </div>

            <div class="fg">
                <label>BRANCH CODE</label>
                <input type="text" id="pay-branch" placeholder="Branch code">
            </div>
        </div>

        <button class="save-btn" onclick="savePayments()">Save Payment Settings</button>
    </div>

    <div class="scard" style="margin-top:20px;">
    <h3>Saved Payment Details</h3>

    <p><strong>Bank:</strong> <span id="view-bank">-</span></p>
    <p><strong>Account Holder:</strong> <span id="view-holder">-</span></p>
    <p><strong>Account Number:</strong> <span id="view-account">-</span></p>
    <p><strong>Branch Code:</strong> <span id="view-branch">-</span></p>
</div>
</section>

</main>
</div><!-- /dash-wrap -->


<div class="modal-ov" id="modal-ov" onclick="closeModalOutside(event)">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal()">✕</button>
        <h2 id="modal-title">Add New Product</h2>
        <input type="hidden" id="edit-id" value="">

        <label class="upload-zone" for="prod-img">
            <span class="material-symbols-sharp" style="font-size:2rem;opacity:.3;">photo_camera</span>
            <p><strong>Click to upload photo</strong> — JPG, PNG, WebP · Max 6MB</p>
            <div class="img-prev-row" id="img-prev"></div>
        </label>
        <input type="file" id="prod-img" accept="image/*" style="display:none"
               onchange="previewImg(this)">

        <div class="fgrid" style="margin-top:1rem;">
            <div class="fg full"><label>PRODUCT TITLE *</label>
                <input type="text" id="p-title" placeholder="e.g. 28″ Straight Lace Front Wig"></div>
            <div class="fg"><label>CATEGORY *</label>
                <select id="p-cat">
                    <option value="">Select…</option>
                    <!-- populated from DB via JS -->
                </select>
            </div>
            <div class="fg"><label>PRICE (R) *</label>
                <input type="number" id="p-price" placeholder="0.00" min="0" step="0.01"></div>
            <div class="fg"><label>CONDITION</label>
                <select id="p-cond">
                    <option>Brand New</option><option>Like New</option>
                    <option>Good Condition</option><option>Fair Condition</option>
                </select>
            </div>
            <div class="fg"><label>STOCK QTY</label>
                <input type="number" id="p-stock" value="1" min="1"></div>
            <div class="fg"><label>DELIVERY</label>
                <select id="p-delivery">
                    <option>Both</option>
                    <option>Delivery Available</option>
                    <option>Collection Only</option>
                </select>
            </div>
            <div class="fg"><label>PROVINCE</label>
                <select id="p-province">
                    <option value="">Select…</option>
                    <?php foreach(['Gauteng','Western Cape','KwaZulu-Natal','Eastern Cape',
                        'Limpopo','Mpumalanga','North West','Free State','Northern Cape'] as $pv):?>
                    <option><?= $pv ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="fg"><label>CITY</label>
                <input type="text" id="p-city" placeholder="e.g. Johannesburg"></div>
            <div class="fg"><label>STATUS</label>
                <select id="p-status">
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                </select>
            </div>
            <div class="fg full"><label>DESCRIPTION *</label>
                <textarea id="p-desc" placeholder="Describe your product…"></textarea></div>
        </div>
        <div class="mfooter">
            <button class="btn-cancel" onclick="closeModal()">Cancel</button>
            <button class="btn-submit" id="submit-btn" onclick="submitProduct()">
                Publish Listing
            </button>
        </div>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const ME_ID   = <?= (int)$user['id'] ?>;
const ME_NAME = <?= json_encode($user['name']) ?>;

const titles = {
    'sec-dashboard':'Dashboard','sec-listings':'My Listings',
    'sec-orders':'Orders','sec-inbox':'Messages',
    'sec-analytics':'Analytics','sec-profile':'My Profile','sec-payments':'Payments'
};
function showSec(id, navEl) {
    // Hide ALL sections first
    document.querySelectorAll('.sec').forEach(s => {
        s.style.display = 'none';
        s.classList.remove('active');
    });

    
    document.querySelectorAll('aside .sidebar a').forEach(a => a.classList.remove('active'));

  
    const target = document.getElementById(id);
    if (target) {
        target.style.display = 'block';
        target.classList.add('active');
    }

    if (navEl) navEl.classList.add('active');
    document.getElementById('page-title').textContent = titles[id] || '';

    if (id === 'sec-listings')  loadListings();
    if (id === 'sec-orders')    loadOrders();
    if (id === 'sec-inbox')     loadConversations();
    if (id === 'sec-analytics') loadAnalytics();
    if (id === 'sec-profile')   loadProfileStats();
}


function setTheme(t) {
    document.body.classList.toggle('dark-theme', t === 'dark');
    document.getElementById('tog-light').style.background = t==='light' ? 'var(--primary)' : '';
    document.getElementById('tog-light').style.color      = t==='light' ? '#fff' : '';
    document.getElementById('tog-dark').style.background  = t==='dark'  ? 'var(--primary)' : '';
    document.getElementById('tog-dark').style.color       = t==='dark'  ? '#fff' : '';
}


async function api(action, params = {}, method = 'GET', body = null) {
    const url = 'api.php?action=' + action + (method === 'GET'
        ? '&' + new URLSearchParams(params).toString() : '');
    const opts = { method, credentials: 'include' };
    if (method === 'POST' && body) opts.body = body;
    else if (method === 'POST') {
        const fd = new FormData();
        Object.entries(params).forEach(([k,v]) => fd.append(k,v));
        opts.body = fd;
    }
    const r = await fetch(url, opts);
    return r.json().catch(() => ({ success: false, message: 'Server error' }));
}


async function loadDashboardStats() {
    const d = await api('seller_stats');
    if (!d.success) return;
    document.getElementById('stat-earnings').textContent = 'R ' + Number(d.earnings).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2});
    document.getElementById('stat-orders-lbl').textContent = d.total_orders + ' total orders';
    document.getElementById('stat-listings').textContent  = d.active_listings;
    document.getElementById('stat-unread').textContent    = d.unread_messages;
    document.getElementById('stat-unread-lbl').textContent = d.unread_messages > 0 ? 'New' : '';
    document.getElementById('stat-rating').textContent    = d.rating || '—';
    document.getElementById('stat-reviews-lbl').textContent = d.total_reviews + ' reviews';
    // update badge
    const b = document.getElementById('unread-badge');
    b.textContent = d.unread_messages;
    b.style.display = d.unread_messages > 0 ? '' : 'none';
}


async function loadRecentOrders() {
    const d = await api('seller_orders', { status: '' });
    if (!d.success) return;
    const tbody = document.getElementById('recent-orders-tbody');
    const rows  = d.orders.slice(0, 5);
    if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="padding:1rem;color:#aaa;">No orders yet.</td></tr>';
        return;
    }
    tbody.innerHTML = rows.map(o => orderRow(o)).join('');
}


async function loadOrders() {
    const d = await api('seller_orders');
    if (!d.success) return;

    // summary cards
    const counts = { pending:0, confirmed:0, ready:0, delivered:0, cancelled:0 };
    let revenue  = 0;
    d.orders.forEach(o => {
        counts[o.status] = (counts[o.status] || 0) + 1;
        if (o.status === 'delivered') revenue += parseFloat(o.total_amount);
    });
    document.getElementById('orders-summary').innerHTML = [
        ['Total Orders', d.orders.length, '#222'],
        ['Pending',      counts.pending,  '#d4891a'],
        ['Delivered',    counts.delivered,'#1a7a4a'],
     ['Revenue', 'R ' + revenue.toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2}), '#1a7a4a'],
    ].map(([l,v,c]) => `
        <div style="background:var(--white);border-radius:1rem;box-shadow:var(--shadow);padding:1.1rem;">
            <div style="font-size:.73rem;color:#888;margin-bottom:4px;">${l}</div>
            <div style="font-size:1.5rem;font-weight:900;color:${c};">${v}</div>
        </div>`).join('');

    const tbody = document.getElementById('orders-tbody');
    if (!d.orders.length) {
        tbody.innerHTML = '<tr><td colspan="6" style="padding:1rem;color:#aaa;">No orders yet.</td></tr>';
        return;
    }
    tbody.innerHTML = d.orders.map(o => orderRow(o)).join('');
}

function orderRow(o) {
    const statusMap = {
        pending:   ['s-pending',   'Pending'],
        confirmed: ['s-confirmed', 'Confirmed'],
        ready:     ['s-ready',     'Ready for Pickup'],
        delivered: ['s-delivered', 'Delivered '],
        cancelled: ['s-cancelled', 'Cancelled'],
    };
    const [cls, lbl] = statusMap[o.status] || ['', o.status];
    const nextActions = {
        pending:   [['confirmed','Confirm'],['cancelled','Cancel']],
        confirmed: [['ready','Mark Ready']],
        ready:     [['delivered','Mark Delivered']],
        delivered: [], cancelled: [],
    };
    const btns = (nextActions[o.status]||[])
        .map(([s,t]) => `<button class="act-btn" onclick="updateOrderStatus(${o.id},'${s}')">${t}</button>`)
        .join(' ');
    return `<tr id="orow-${o.id}">
        <td style="font-weight:700;">${esc(o.order_ref)}</td>
        <td>${esc(o.buyer_name)}</td>
        <td>${esc(o.product_title)}</td>
        <td style="font-weight:700;color:var(--success);">R ${Number(o.total_amount).toLocaleString('en-ZA')}</td>
        <td><span class="ord-badge ${cls}">${lbl}</span></td>
        <td>${btns || '—'}</td>
    </tr>`;
}

async function updateOrderStatus(orderId, status) {
    const d = await api('update_order_status', { order_id: orderId, status }, 'POST');
    toast(d.success ? 'Order updated' : '' + d.message);
    if (d.success) { loadOrders(); loadRecentOrders(); loadDashboardStats(); }
}


let listingFilterStatus = '';

function setListingFilter(status, btn) {
    listingFilterStatus = status;
    document.querySelectorAll('.ftab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadListings();
}

async function loadListings() {
    const q    = document.getElementById('listings-q')?.value || '';
    const d    = await api('my_listings', { q, status: listingFilterStatus });
    const grid = document.getElementById('listings-grid');
    if (!d.success) { grid.innerHTML = '<div style="color:#e03131">Error loading listings.</div>'; return; }

    if (!d.products.length) {
        grid.innerHTML = `
            <div class="add-card" onclick="openAddModal()">
                <span class="material-symbols-sharp" style="font-size:2.5rem;opacity:.5;">add_circle</span>
                <p style="font-size:.83rem;font-weight:600;">No listings yet – Add your first product</p>
            </div>`;
        return;
    }

    const bMap = { active:'b-active', pending:'b-pending', sold:'b-sold' };
    const bLbl = { active:'Active', pending:'Pending', sold:'Sold' };
    grid.innerHTML = d.products.map(p => `
    <div class="lcard" id="lc-${p.id}">
        <div class="lcard-img">
            ${p.image_url
                ? `<img src="${esc(p.image_url)}" alt="${esc(p.title)}">`
                : `<span class="material-symbols-sharp no-img">image</span>`}
            <span class="lcard-badge ${bMap[p.status]||'b-active'}">${bLbl[p.status]||p.status}</span>
        </div>
        <div class="lcard-body">
            <div class="lcard-title">${esc(p.title)}</div>
            <div class="lcard-cat">${esc(p.category_name)}</div>
            <div class="lcard-foot">
                <span class="lcard-price">R ${Number(p.price).toLocaleString('en-ZA')}</span>
                <span class="lcard-views">👁 ${p.views}</span>
            </div>
        </div>
        <div class="lcard-actions">
            <button class="la" onclick='editListing(${JSON.stringify(p)})'> Edit</button>
            
            <button class="la del" onclick="deleteListing(${p.id})">Delete</button>
        </div>
    </div>`).join('') + `
    <div class="add-card" onclick="openAddModal()">
        <span class="material-symbols-sharp" style="font-size:2.5rem;opacity:.5;">add_circle</span>
        <p style="font-size:.83rem;font-weight:600;">Add New Product</p>
    </div>`;
}


async function deleteListing(id) {
    if (!confirm('Delete this listing? This cannot be undone.')) return;
    const d = await api('delete_product', { product_id: id }, 'POST');
    toast(d.success ? 'Deleted' : '✘ ' + d.message);
    if (d.success) { document.getElementById('lc-'+id)?.remove(); }
}


async function loadCategories() {
    const d = await api('categories');
    if (!d.success) return;
    const sel = document.getElementById('p-cat');
    sel.innerHTML = '<option value="">Select…</option>' +
        d.categories.map(c => `<option value="${c.id}">${esc(c.name)}</option>`).join('');
}

function openAddModal() {
    document.getElementById('edit-id').value = '';
    document.getElementById('modal-title').textContent = 'Add New Product';
    document.getElementById('submit-btn').textContent  = ' Publish Listing';
    ['p-title','p-price','p-city','p-desc'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    document.getElementById('p-stock').value = '1';
    document.getElementById('img-prev').innerHTML = '';
    document.getElementById('prod-img').value = '';
    document.getElementById('modal-ov').classList.add('open');
}

function editListing(p) {
    document.getElementById('edit-id').value    = p.id;
    document.getElementById('modal-title').textContent = 'Edit Listing';
    document.getElementById('submit-btn').textContent  = ' Save Changes';
    document.getElementById('p-title').value    = p.title;
    document.getElementById('p-price').value    = p.price;
    document.getElementById('p-stock').value    = p.stock_qty;
    document.getElementById('p-city').value     = p.city || '';
    document.getElementById('p-desc').value     = p.description || '';
    document.getElementById('p-cond').value     = p.condition_type;
    document.getElementById('p-delivery').value = p.delivery_option;
    document.getElementById('p-status').value   = p.status;
    // set province
    const provSel = document.getElementById('p-province');
    for (let o of provSel.options) if (o.text === p.province) { o.selected = true; break; }
    // set category
    const catSel = document.getElementById('p-cat');
    for (let o of catSel.options) if (o.value == p.category_id) { o.selected = true; break; }
    document.getElementById('modal-ov').classList.add('open');
}

function closeModal() { document.getElementById('modal-ov').classList.remove('open'); }
function closeModalOutside(e) { if (e.target === document.getElementById('modal-ov')) closeModal(); }

function previewImg(input) {
    const prev = document.getElementById('img-prev');
    prev.innerHTML = '';
    if (!input.files[0]) return;
    const img = document.createElement('img');
    img.src = URL.createObjectURL(input.files[0]);
    prev.appendChild(img);
}

async function submitProduct() {
    const btn   = document.getElementById('submit-btn');
    const editId = document.getElementById('edit-id').value;
    const title  = document.getElementById('p-title').value.trim();
    const cat    = document.getElementById('p-cat').value;
    const price  = document.getElementById('p-price').value;
    const desc   = document.getElementById('p-desc').value.trim();

    if (!title || !cat || !price || !desc) { toast('! Please fill in all required fields'); return; }

    btn.disabled = true;
    btn.textContent = editId ? 'Saving…' : 'Publishing…';

    const fd = new FormData();
    const action = editId ? 'update_product' : 'add_product';
    if (editId) fd.append('product_id', editId);
    fd.append('title',           title);
    fd.append('category_id',     cat);
    fd.append('price',           price);
    fd.append('description',     desc);
    fd.append('condition_type',  document.getElementById('p-cond').value);
    fd.append('stock_qty',       document.getElementById('p-stock').value);
    fd.append('delivery_option', document.getElementById('p-delivery').value);
    fd.append('province',        document.getElementById('p-province').value);
    fd.append('city',            document.getElementById('p-city').value);
    fd.append('status',          document.getElementById('p-status').value);
    const imgFile = document.getElementById('prod-img').files[0];
    if (imgFile) fd.append('image', imgFile);

    const r = await fetch(`api.php?action=${action}`, {
        method: 'POST', body: fd, credentials: 'include'
    });
    const d = await r.json().catch(() => ({ success: false, message: 'Server error' }));

    btn.disabled = false;
    btn.textContent = editId ? 'Save Changes' : 'Publish Listing';

    toast(d.success ? '✔ ' + d.message : '✘ ' + d.message);
    if (d.success) {
        closeModal();
        loadListings();
        loadDashboardStats();
    }
}


let allConvos = [], currentConvo = null;

async function loadConversations() {
    const d = await api('conversations');
    if (!d.success) return;
    allConvos = d.conversations;
    renderConvoList(allConvos);
    if (allConvos.length && !currentConvo) openConvo(allConvos[0]);
}

function renderConvoList(list) {
    const el = document.getElementById('convo-list');
    if (!list.length) {
        el.innerHTML = '<div style="padding:1rem;color:#aaa;font-size:.83rem;">No conversations yet.</div>';
        return;
    }
    el.innerHTML = list.map(c => `
    <div class="ci ${currentConvo && currentConvo.convo_id === c.convo_id ? 'active' : ''}"
         onclick='openConvo(${JSON.stringify(c).replace(/'/g,"&#39;")})'>
        <div class="ci-av" style="background:${avatarColor(c.other_name)};">
            ${c.other_name[0].toUpperCase()}
        </div>
        <div class="ci-body">
            <div class="ci-name">${esc(c.other_shop || c.other_name)}</div>
            <div class="ci-prev">${esc(c.last_message || '—')}</div>
        </div>
        <div class="ci-meta">
            <div class="ci-time">${timeAgo(c.last_at)}</div>
            ${c.unread_count > 0 ? `<span class="ci-unread">${c.unread_count}</span>` : ''}
        </div>
    </div>`).join('');
}

async function openConvo(c) {
    if (typeof c === 'string') c = JSON.parse(c);
    currentConvo = c;
    renderConvoList(allConvos);

    const pane = document.getElementById('chat-pane');
    pane.innerHTML = `
    <div class="chat-hdr">
        <div class="ch-av" style="background:${avatarColor(c.other_name)};">
            ${c.other_name[0].toUpperCase()}
        </div>
        <div>
            <div class="ch-name">${esc(c.other_shop || c.other_name)}</div>
        </div>
    </div>
    <div class="chat-msgs" id="chat-msgs">
        <div style="color:#aaa;font-size:.83rem;">Loading…</div>
    </div>
    <div class="chat-inp">
        <textarea id="msg-input" placeholder="Type your reply…" rows="1"
            onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}">
        </textarea>
        <button class="send-btn" onclick="sendMsg()">
            <span class="material-symbols-sharp" style="font-size:1.1rem;">send</span>
        </button>
    </div>`;

    const d = await api('thread', { other_id: c.other_id });
    if (!d.success) return;

    const msgs = document.getElementById('chat-msgs');
    if (!d.messages.length) {
        msgs.innerHTML = '<div style="color:#aaa;font-size:.83rem;">No messages yet.</div>';
    } else {
        msgs.innerHTML = d.messages.map(m => {
            const isMine = parseInt(m.sender_id) === ME_ID;
            return `<div class="bw ${isMine ? 'seller' : 'buyer'}">
                <div class="blbl">${isMine ? 'You' : esc(m.sender_name)}</div>
                <div class="bbl">${esc(m.message_text)}</div>
                <div class="btime">${formatTime(m.created_at)}</div>
            </div>`;
        }).join('');
        msgs.scrollTop = msgs.scrollHeight;
    }

    loadDashboardStats();
    const cv = allConvos.find(x => x.convo_id === c.convo_id);
    if (cv) { cv.unread_count = 0; renderConvoList(allConvos); }
}

async function sendMsg() {
    if (!currentConvo) return;
    const input = document.getElementById('msg-input');
    const text  = input.value.trim();
    if (!text) return;
    input.value = '';

    const d = await api('send_message', {
        receiver_id:  currentConvo.other_id,
        message_text: text,
    }, 'POST');

    if (!d.success) { toast('' + d.message); return; }

    const msgs = document.getElementById('chat-msgs');
    msgs.innerHTML += `<div class="bw seller">
        <div class="blbl">You</div>
        <div class="bbl">${esc(text)}</div>
        <div class="btime">Just now</div>
    </div>`;
    msgs.scrollTop = msgs.scrollHeight;

    const cv = allConvos.find(c => c.convo_id === currentConvo.convo_id);
    if (cv) { cv.last_message = text; cv.last_at = new Date().toISOString(); renderConvoList(allConvos); }
}

function filterConvos(q) {
    const filtered = allConvos.filter(c =>
        (c.other_name || '').toLowerCase().includes(q.toLowerCase()) ||
        (c.other_shop || '').toLowerCase().includes(q.toLowerCase())
    );
    renderConvoList(filtered);
}

setInterval(async () => {
    if (!currentConvo) return;
    const d = await api('thread', { other_id: currentConvo.other_id });
    if (!d.success) return;
    const msgs = document.getElementById('chat-msgs');
    if (!msgs) return;
    msgs.innerHTML = d.messages.map(m => {
        const isMine = parseInt(m.sender_id) === ME_ID;
        return `<div class="bw ${isMine ? 'seller' : 'buyer'}">
            <div class="blbl">${isMine ? 'You' : esc(m.sender_name)}</div>
            <div class="bbl">${esc(m.message_text)}</div>
            <div class="btime">${formatTime(m.created_at)}</div>
        </div>`;
    }).join('');
    msgs.scrollTop = msgs.scrollHeight;
}, 5000);

async function loadAnalytics() {
    const d = await api('seller_stats');
    if (!d.success) return;

    document.getElementById('an-grid').innerHTML = [
       ['Total Revenue', 'R ' + Number(d.earnings).toLocaleString('en-ZA', {minimumFractionDigits:2, maximumFractionDigits:2}), '#1a7a4a'],
        ['Total Orders',  d.total_orders, 'var(--primary)'],
        ['Active Listings', d.active_listings, '#d4891a'],
        ['Unread Messages', d.unread_messages, 'var(--danger)'],
        
    ].map(([l,v,c]) => `
        <div class="an-card">
            <div class="an-label">${l}</div>
            <div class="an-num" style="color:${c};">${v}</div>
        </div>`).join('');

    
    const chart = document.getElementById('bar-chart');
    if (!d.monthly_revenue || !d.monthly_revenue.length) {
        chart.innerHTML = '<div style="color:#aaa;font-size:.83rem;align-self:center;">No revenue data yet</div>';
        return;
    }
    const maxR = Math.max(...d.monthly_revenue.map(r => parseFloat(r.revenue)));
    const colors = ['#2546a8','#2546a8','#2546a8','#9f25a8','#c2255c','#1a7a4a'];
    chart.innerHTML = d.monthly_revenue.map((r, i) => `
    <div class="bar-col">
        <div class="bar-val">R${(parseFloat(r.revenue)/1000).toFixed(1)}k</div>
        <div class="bar" style="height:${Math.max(5,Math.round((parseFloat(r.revenue)/maxR)*100))}%;
             background:${colors[i%colors.length]};"></div>
        <div class="bar-lbl">${r.month}</div>
    </div>`).join('');
}


async function loadProfileStats() {
    const d = await api('seller_stats');
    if (!d.success) return;

    document.getElementById('ps-listings').textContent = d.active_listings;
    document.getElementById('ps-rating').textContent   = (d.rating||0).toFixed(1);
    document.getElementById('ps-reviews').textContent  = d.total_reviews;

    loadSellerReviews();
}

async function saveProfile() {
    const d = await api('save_seller_profile', {
        name:      document.getElementById('pf-name').value,
        shop_name: document.getElementById('pf-shop').value,
        province:  document.getElementById('pf-province').value,
        city:      document.getElementById('pf-city').value,
        phone:     document.getElementById('pf-phone').value,
        shop_bio:  document.getElementById('pf-bio').value,
    }, 'POST');
    toast(d.success ? ' Profile saved!' : 'X ' + d.message);
    if (d.success) {
        document.getElementById('prof-shopname').textContent =
            document.getElementById('pf-shop').value || document.getElementById('pf-name').value;
    }
}

let paymentLoaded = false;


async function loadPayments() {
    const res = await fetch('get_payment.php');
    const data = await res.json();

    if (data.success && data.payment) {

        const p = data.payment;

        
        document.getElementById('pay-bank').value = p.bank_name || '';
        document.getElementById('pay-holder').value = p.account_holder || '';
        document.getElementById('pay-account').value = p.account_number || '';
        document.getElementById('pay-branch').value = p.branch_code || '';

       
        document.getElementById('view-bank').innerText = p.bank_name || '-';
        document.getElementById('view-holder').innerText = p.account_holder || '-';
        document.getElementById('view-account').innerText = p.account_number || '-';
        document.getElementById('view-branch').innerText = p.branch_code || '-';
    }
}

async function savePayments() {

    const payload = {
        bank_name: document.getElementById('pay-bank').value,
        account_holder: document.getElementById('pay-holder').value,
        account_number: document.getElementById('pay-account').value,
        branch_code: document.getElementById('pay-branch').value
    };

    const res = await fetch('save_payment.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (data.success) {
        toast('Payment details saved successfully!');
    } else {
        toast('Failed to save payment details');
    }
}
 window.addEventListener('DOMContentLoaded', loadPayments);

const COLOURS = ['#1a7a4a','#d72b2b','#f5a623','#555','#862e9c','#1971c2','#e07400','#2f9e44'];
function avatarColor(name) {
    let h = 0; for (const c of (name||'?')) h = (h*31 + c.charCodeAt(0)) & 0x7fffffff;
    return COLOURS[h % COLOURS.length];
}
function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function timeAgo(dt) {
    if (!dt) return '';
    const diff = Math.floor((Date.now() - new Date(dt)) / 1000);
    if (diff < 60)    return 'Just now';
    if (diff < 3600)  return Math.floor(diff/60)   + 'm ago';
    if (diff < 86400) return Math.floor(diff/3600)  + 'h ago';
    return new Date(dt).toLocaleDateString('en-ZA');
}
function formatTime(dt) {
    if (!dt) return '';
    return new Date(dt).toLocaleTimeString('en-ZA', {hour:'2-digit', minute:'2-digit'});
}
function toast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    clearTimeout(t._t); t._t = setTimeout(() => t.classList.remove('show'), 3000);
}

async function loadSellerReviews() {

    const d = await api('seller_reviews');

    if (!d.success) return;

    const wrap = document.getElementById('seller-reviews');

    if (!d.reviews.length) {
        wrap.innerHTML = `
            <div style="padding:20px;text-align:center;color:#888;">
                No reviews yet.
            </div>
        `;
        return;
    }

    wrap.innerHTML = d.reviews.map(r => `
        <div style="
            border:1px solid #eee;
            border-radius:12px;
            padding:14px;
            margin-bottom:12px;
        ">
            <div style="
                display:flex;
                justify-content:space-between;
                align-items:center;
                margin-bottom:8px;
            ">
                <strong>${escapeHtml(r.buyer_name)}</strong>

                <span style="color:#f4b400;font-size:1rem;">
                    ${'<span class="material-symbols-sharp">star</span>'.repeat(r.rating)}
                </span>
            </div>

            ${
                r.comment
                ? `<div style="color:#555;margin-bottom:8px;">
                     ${escapeHtml(r.comment)}
                   </div>`
                : ''
            }

            <small style="color:#999;">
                ${new Date(r.created_at).toLocaleDateString('en-ZA')}
            </small>
        </div>
    `).join('');
}



function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}




document.getElementById('today-date').value = new Date().toISOString().split('T')[0];


showSec('sec-dashboard', document.getElementById('nav-dashboard'));

loadDashboardStats();
loadRecentOrders();
loadCategories();
</script>
</body>
</html>