<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MarketSA</title>
  <link rel="icon" type="image/png" href="lo33.png">
  <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --green:   #1a7a4a;
      --lime:    #b8f04a;
      --sand:    #f5f0e8;
      --ink:     #0d1a10;
      --muted:   #6b7c6f;
      --white:   #ffffff;
      --radius:  18px;
    }

    html { scroll-behavior: smooth; }

    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--white);
      color: var(--ink);
      overflow-x: hidden;
    }

    
    nav {
      position: fixed; top: 0; left: 0; right: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem 5%;
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(0,0,0,0.06);
    }

    .nav-logo {
      font-family: 'Syne', sans-serif;
      font-size: 1.5rem; font-weight: 800;
      color: var(--ink); text-decoration: none;
    }
    .nav-logo span { color: var(--green); }

    .nav-links { display: flex; align-items: center; gap: 1.5rem; }

    .nav-links a {
      font-size: .88rem; font-weight: 500;
      color: var(--muted); text-decoration: none;
      transition: color .2s;
    }
    .nav-links a:hover { color: var(--green); }

    .nav-cta {
      background: var(--green); color: var(--white) !important;
      padding: .55rem 1.3rem; border-radius: 50px;
      font-weight: 600 !important;
      transition: background .2s !important;
    }
    .nav-cta:hover { background: #145e39 !important; }

   
    .hero {
      min-height: 100vh;
      padding: 8rem 5% 5rem;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 4rem;
      align-items: center;
      background: var(--sand);
      position: relative;
      overflow: hidden;
    }

   
    .hero::before {
      content: '';
      position: absolute;
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(26,122,74,.15) 0%, transparent 70%);
      top: -100px; right: -100px;
      border-radius: 50%;
    }

    .hero-text { position: relative; z-index: 1; }

    .hero-eyebrow {
      display: inline-flex; align-items: center; gap: .5rem;
      background: rgba(26,122,74,.1);
      border: 1px solid rgba(26,122,74,.25);
      color: var(--green);
      font-size: .78rem; font-weight: 600;
      letter-spacing: .08em; text-transform: uppercase;
      padding: .35rem .9rem; border-radius: 50px;
      margin-bottom: 1.5rem;
    }
    .hero-eyebrow::before {
      content: '';
      width: 7px; height: 7px;
      background: var(--green);
      border-radius: 50%;
      animation: pulse 1.8s infinite;
    }
    @keyframes pulse {
      0%,100% { opacity: 1; transform: scale(1); }
      50%      { opacity: .4; transform: scale(1.4); }
    }

    .hero h1 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(2.6rem, 5vw, 4rem);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -.02em;
      margin-bottom: 1.2rem;
    }

    .hero h1 .accent {
      color: var(--green);
      position: relative;
    }
    .hero h1 .accent::after {
      content: '';
      position: absolute;
      left: 0; bottom: 2px; right: 0;
      height: 6px;
      background: var(--lime);
      border-radius: 3px;
      z-index: -1;
      opacity: .7;
    }

    .hero-sub {
      font-size: 1.05rem;
      color: var(--muted);
      line-height: 1.7;
      max-width: 460px;
      margin-bottom: 2.2rem;
    }

    .hero-btns { display: flex; gap: 1rem; flex-wrap: wrap; }

    .btn-primary {
      background: var(--green); color: var(--white);
      padding: .85rem 2rem; border-radius: 50px;
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem; font-weight: 600;
      text-decoration: none;
      transition: background .2s, transform .15s;
      display: inline-flex; align-items: center; gap: .5rem;
    }
    .btn-primary:hover { background: #145e39; transform: translateY(-2px); }

    .btn-secondary {
      background: transparent; color: var(--ink);
      padding: .85rem 2rem; border-radius: 50px;
      border: 1.5px solid rgba(0,0,0,.18);
      font-family: 'DM Sans', sans-serif;
      font-size: .95rem; font-weight: 600;
      text-decoration: none;
      transition: border-color .2s, transform .15s;
      display: inline-flex; align-items: center; gap: .5rem;
    }
    .btn-secondary:hover { border-color: var(--green); color: var(--green); transform: translateY(-2px); }

    
    .hero-visual {
      position: relative; z-index: 1;
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-template-rows: auto auto auto;
      gap: 1rem;
    }

    .mock-card {
      background: var(--white);
      border-radius: var(--radius);
      padding: 1.1rem;
      box-shadow: 0 4px 24px rgba(0,0,0,.08);
      transition: transform .3s;
    }
    .mock-card:hover { transform: translateY(-4px); }

    .mock-card.tall { grid-row: span 2; }
    .mock-card.wide { grid-column: span 2; }

    .mock-img {
      border-radius: 10px;
      width: 100%; aspect-ratio: 1;
      display: flex; align-items: center; justify-content: center;
      font-size: 2.8rem;
      margin-bottom: .8rem;
    }

    .mock-cat {
      font-size: .68rem; font-weight: 600; letter-spacing: .06em;
      text-transform: uppercase; color: var(--green);
      margin-bottom: .25rem;
    }
    .mock-name { font-size: .85rem; font-weight: 600; margin-bottom: .3rem; }
    .mock-price { font-size: 1rem; font-weight: 700; color: var(--green); }
    .mock-shop { font-size: .72rem; color: var(--muted); margin-top: .15rem; }

    .mock-badge {
      display: inline-block;
      background: #d4f8e8; color: #0f6b38;
      font-size: .65rem; font-weight: 700;
      padding: .2rem .55rem; border-radius: 20px;
      margin-bottom: .5rem;
    }

    .mock-card.wide {
      display: flex; align-items: center; gap: 1rem;
      padding: .9rem 1.1rem;
    }
    .mock-card.wide .mock-img {
      width: 54px; height: 54px; aspect-ratio: unset;
      font-size: 1.6rem; margin: 0; flex-shrink: 0;
    }
    .mock-card.wide .mock-name { font-size: .78rem; }
    .mock-card.wide .mock-price { font-size: .88rem; }

    
    .ticker {
      background: var(--green);
      padding: .7rem 0;
      overflow: hidden;
      white-space: nowrap;
    }

    .ticker-track {
      display: inline-flex; gap: 3rem;
      animation: ticker 22s linear infinite;
    }

    .ticker-item {
      font-size: .82rem; font-weight: 600;
      color: rgba(255,255,255,.85);
      display: inline-flex; align-items: center; gap: .6rem;
      flex-shrink: 0;
    }
    .ticker-dot { color: var(--lime); font-size: 1rem; }

    @keyframes ticker {
      from { transform: translateX(0); }
      to   { transform: translateX(-50%); }
    }

  
    .stats {
      background: var(--ink);
      padding: 4rem 5%;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 2rem;
      text-align: center;
    }

    .stat-item {}
    .stat-num {
      font-family: 'Syne', sans-serif;
      font-size: 2.6rem; font-weight: 800;
      color: var(--lime);
      display: block;
    }
    .stat-label {
      font-size: .85rem;
      color: rgba(255,255,255,.5);
      margin-top: .3rem;
    }

   
    .how {
      padding: 6rem 5%;
      background: var(--white);
    }

    .section-label {
      display: inline-block;
      font-size: .75rem; font-weight: 700;
      letter-spacing: .12em; text-transform: uppercase;
      color: var(--green);
      margin-bottom: .7rem;
    }

    .section-h {
      font-family: 'Syne', sans-serif;
      font-size: clamp(1.8rem, 3.5vw, 2.6rem);
      font-weight: 800; line-height: 1.15;
      margin-bottom: .8rem;
    }

    .section-p {
      font-size: .95rem; color: var(--muted);
      line-height: 1.7; max-width: 500px;
      margin-bottom: 3rem;
    }

    .steps {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1.5rem;
    }

    .step {
      background: var(--sand);
      border-radius: var(--radius);
      padding: 2rem;
      position: relative;
      overflow: hidden;
      transition: box-shadow .25s;
    }
    .step:hover { box-shadow: 0 12px 40px rgba(0,0,0,.1); }

    .step-icon {
      width: 52px; height: 52px;
      background: var(--green);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.4rem;
      margin-bottom: 1.2rem;
    }

    .step h3 {
      font-family: 'Syne', sans-serif;
      font-size: 1.05rem; font-weight: 700;
      margin-bottom: .5rem;
    }

    .step p { font-size: .88rem; color: var(--muted); line-height: 1.6; }

    .step-n {
      position: absolute; top: 1rem; right: 1.2rem;
      font-family: 'Syne', sans-serif;
      font-size: 3.5rem; font-weight: 800;
      color: rgba(0,0,0,.06);
      line-height: 1;
    }

    .features {
      background: var(--ink);
      padding: 6rem 5%;
    }

    .features .section-label { color: var(--lime); }
    .features .section-h { color: var(--white); }
    .features .section-p { color: rgba(255,255,255,.5); }

    .feat-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1px;
      background: rgba(255,255,255,.07);
      border-radius: var(--radius);
      overflow: hidden;
    }

    .feat {
      background: var(--ink);
      padding: 2rem;
      transition: background .25s;
    }
    .feat:hover { background: rgba(184,240,74,.05); }

    .feat-icon {
      font-size: 1.8rem; margin-bottom: 1rem;
    }

    .feat h3 {
      font-family: 'Syne', sans-serif;
      font-size: 1rem; font-weight: 700;
      color: var(--white);
      margin-bottom: .45rem;
    }
    .feat p { font-size: .85rem; color: rgba(255,255,255,.45); line-height: 1.6; }

  
    .audience {
      padding: 6rem 5%;
      background: var(--white);
    }

    .aud-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1.5rem;
      margin-top: 3rem;
    }

    .aud-card {
      border-radius: 24px;
      padding: 3rem 2.5rem;
      display: flex; flex-direction: column;
    }

    .aud-card.sellers {
      background: linear-gradient(135deg, #0d4a2a 0%, #1a7a4a 100%);
    }
    .aud-card.buyers {
      background: linear-gradient(135deg, #0d1a10 0%, #1a3a22 60%, #0d4a2a 100%);
      border: 1px solid rgba(184,240,74,.15);
    }

    .aud-tag {
      display: inline-block;
      font-size: .72rem; font-weight: 700;
      letter-spacing: .1em; text-transform: uppercase;
      padding: .3rem .8rem; border-radius: 50px;
      margin-bottom: 1.5rem; width: fit-content;
    }
    .sellers .aud-tag { background: rgba(184,240,74,.2); color: var(--lime); }
    .buyers  .aud-tag { background: rgba(184,240,74,.15); color: var(--lime); }

    .aud-card h2 {
      font-family: 'Syne', sans-serif;
      font-size: 1.8rem; font-weight: 800;
      color: var(--white); margin-bottom: .8rem;
    }

    .aud-card p {
      font-size: .9rem; color: rgba(255,255,255,.65);
      line-height: 1.7; margin-bottom: 1.5rem;
    }

    .aud-list {
      list-style: none; margin-bottom: 2rem; flex: 1;
    }
    .aud-list li {
      font-size: .87rem; color: rgba(255,255,255,.75);
      padding: .4rem 0;
      display: flex; align-items: center; gap: .6rem;
      border-bottom: 1px solid rgba(255,255,255,.08);
    }
    .aud-list li:last-child { border-bottom: none; }
    .aud-list li::before { content: '→'; color: var(--lime); font-weight: 700; }

    .aud-btn {
      display: inline-flex; align-items: center; gap: .4rem;
      background: var(--lime); color: var(--ink);
      padding: .75rem 1.6rem; border-radius: 50px;
      font-weight: 700; font-size: .88rem;
      text-decoration: none; width: fit-content;
      transition: opacity .2s, transform .15s;
    }
    .aud-btn:hover { opacity: .85; transform: translateY(-2px); }

    /* ── CTA BAND ── */
    .cta-band {
      background: var(--lime);
      padding: 4.5rem 5%;
      display: flex; align-items: center;
      justify-content: space-between; gap: 2rem;
      flex-wrap: wrap;
    }

    .cta-band h2 {
      font-family: 'Syne', sans-serif;
      font-size: clamp(1.6rem, 3vw, 2.4rem);
      font-weight: 800; color: var(--ink);
      max-width: 500px; line-height: 1.15;
    }

    .cta-band a {
      background: var(--ink); color: var(--lime);
      padding: 1rem 2.2rem; border-radius: 50px;
      font-weight: 700; font-size: 1rem;
      text-decoration: none; white-space: nowrap;
      transition: background .2s;
    }
    .cta-band a:hover { background: var(--green); color: var(--white); }

   
    footer {
      background: var(--ink);
      padding: 4rem 5% 2rem;
      color: rgba(255,255,255,.5);
    }

    .foot-top {
      display: flex; justify-content: space-between;
      align-items: flex-start; gap: 3rem; flex-wrap: wrap;
      padding-bottom: 3rem;
      border-bottom: 1px solid rgba(255,255,255,.08);
      margin-bottom: 2rem;
    }

    .foot-logo {
      font-family: 'Syne', sans-serif;
      font-size: 1.5rem; font-weight: 800; color: var(--white);
      margin-bottom: .4rem;
    }
    .foot-logo span { color: var(--lime); }
    .foot-tagline { font-size: .82rem; }

    .foot-cols { display: flex; gap: 4rem; flex-wrap: wrap; }

    .foot-col h4 {
      font-size: .82rem; font-weight: 700;
      color: var(--white); letter-spacing: .06em;
      text-transform: uppercase; margin-bottom: 1rem;
    }

    .foot-col a {
      display: block; color: rgba(255,255,255,.45);
      text-decoration: none; font-size: .83rem;
      margin-bottom: .55rem; transition: color .2s;
    }
    .foot-col a:hover { color: var(--lime); }

    .foot-bottom {
      display: flex; justify-content: space-between;
      align-items: center; flex-wrap: wrap; gap: 1rem;
      font-size: .8rem;
    }

  
    @media (max-width: 900px) {
      .hero { grid-template-columns: 1fr; padding-top: 7rem; }
      .hero-visual { display: none; }
      .stats { grid-template-columns: repeat(2,1fr); }
      .steps { grid-template-columns: 1fr; }
      .feat-grid { grid-template-columns: 1fr; }
      .aud-grid { grid-template-columns: 1fr; }
      .cta-band { flex-direction: column; text-align: center; }
      nav .nav-links { display: none; }
    }
  </style>
</head>
<body>


<nav>
  <a href="#" class="nav-logo">Market<span>SA</span></a>
  <div class="nav-links">
    <a href="#how">How it works</a>
    <a href="#features">Features</a>
    <a href="#sellers">For sellers</a>
    <a href="admin_login.php">Admin</a>
    <a href="login.php?form=register" class="nav-cta">Get started free </a>
  </div>
</nav>


<section class="hero">
  <div class="hero-text">
    <div class="hero-eyebrow">South Africa's free marketplace</div>
    <h1>Buy & sell anything,<br><span class="accent">anywhere</span> in SA.</h1>
    <p class="hero-sub">
      From Joburg to Cape Town — list your products in minutes, reach buyers in all 9 provinces, and trade with confidence. Zero fees, forever.
    </p>
    <div class="hero-btns">
      <a href="login.php?form=register" class="btn-primary">Start selling free </a>
      <a href="login.php" class="btn-secondary">Browse listings</a>
    </div>
  </div>

  
</section>


<div class="ticker">
  <div class="ticker-track">
    <span class="ticker-item"><span class="ticker-dot">✦</span> Free to list, free to buy</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> All 9 provinces covered</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Verified seller profiles</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Secure in-app messaging</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Fashion · Electronics · Food · Crafts · More</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Support local businesses</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Free to list, free to buy</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> All 9 provinces covered</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Verified seller profiles</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Secure in-app messaging</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Fashion · Electronics · Food · Crafts · More</span>
    <span class="ticker-item"><span class="ticker-dot">✦</span> Support local businesses</span>
  </div>
</div>


<div class="stats">
  <div class="stat-item">
    <span class="stat-num">100%</span>
    <div class="stat-label">Free — no hidden fees ever</div>
  </div>
  <div class="stat-item">
    <span class="stat-num">9</span>
    <div class="stat-label">Provinces connected</div>
  </div>
  <div class="stat-item">
    <span class="stat-num">∞</span>
    <div class="stat-label">Products you can list</div>
  </div>
  <div class="stat-item">
    <span class="stat-num">🇿🇦</span>
    <div class="stat-label">Built for South Africa</div>
  </div>
</div>


<section class="how" id="how">
  <span class="section-label">How it works</span>
  <h2 class="section-h">Trading made simple</h2>
  <p class="section-p">Three steps to start — no card needed, no complicated setup, no cost.</p>

  <div class="steps">
    <div class="step">
      
      <div class="step-n">1</div>
      <h3>Create your free account</h3>
      <p>Sign up in under 2 minutes with just your name and email. Choose seller or buyer — or both.</p>
    </div>
    <div class="step">
     
      <div class="step-n">2</div>
      <h3>List or browse products</h3>
      <p>Sellers post with photos and prices instantly. Buyers browse by category, province, or price.</p>
    </div>
    <div class="step">
     
      <div class="step-n">3</div>
      <h3>Connect & trade safely</h3>
      <p>Message sellers directly, agree on deals, leave reviews, and build your trading reputation.</p>
    </div>
  </div>
</section>


<section class="features" id="features">
  <span class="section-label">Platform features</span>
  <h2 class="section-h">Everything you need.<br>Nothing you don't.</h2>
  <p class="section-p">Built specifically for South Africans, with features that actually matter for local trade.</p>

  <div class="feat-grid">
    <div class="feat">
      
      <h3>Completely free</h3>
      <p>No listing fees, no commission, no subscription. MarketSA will always be free for South Africans.</p>
    </div>
    <div class="feat">
      
      <h3>Browse by province</h3>
      <p>Filter listings by your area — from Gauteng to the Western Cape. Support local traders near you.</p>
    </div>
    <div class="feat">
     
      <h3>Verified seller profiles</h3>
      <p>Seller ratings, reviews, and badges help you trade with confidence every time.</p>
    </div>
    <div class="feat">
     
      <h3>In-app messaging</h3>
      <p>Chat directly with buyers or sellers without sharing your personal number.</p>
    </div>
    <div class="feat">
      
      <h3>Works on any device</h3>
      <p>Fully optimised for mobile, tablet, and desktop. Trade anywhere, anytime across SA.</p>
    </div>
    <div class="feat">
    
      <h3>All categories</h3>
      <p>Electronics, fashion, food, crafts, vehicles, services — all in one place.</p>
    </div>
  </div>
</section>


<section class="audience" id="sellers">
  <span class="section-label">Who is MarketSA for?</span>
  <h2 class="section-h">Whether you sell or shop</h2>

  <div class="aud-grid">
    <div class="aud-card sellers">
      <span class="aud-tag">For sellers</span>
      <h2>Start your online business today</h2>
      <p>Zero upfront cost. Reach thousands of buyers across all 9 provinces and build your brand from day one.</p>
      <ul class="aud-list">
        <li>List unlimited products for free</li>
        <li>Reach buyers in all 9 provinces</li>
        <li>Manage orders from your dashboard</li>
        <li>Build your brand & reputation</li>
      </ul>
      <a href="login.php?form=register" class="aud-btn">Start selling free </a>
    </div>

    <div class="aud-card buyers">
      <span class="aud-tag">For buyers</span>
      <h2>Discover local deals near you</h2>
      <p>Browse unique products, support South African businesses, and find the best prices — all on one platform.</p>
      <ul class="aud-list">
        <li>Browse thousands of local listings</li>
        <li>Buy direct from verified sellers</li>
        <li>Filter by location & category</li>
        <li>Secure messaging with sellers</li>
      </ul>
      <a href="login.php" class="aud-btn">Start shopping </a>
    </div>
  </div>
</section>


<div class="cta-band">
  <h2>Ready to trade? It takes less than 2 minutes to get started.</h2>
  <a href="index.php?form=register">Create your free account </a>
</div>


<footer>
  <div class="foot-top">
    <div>
      <div class="foot-logo">Market<span>SA</span></div>
      <div class="foot-tagline">South Africa's free marketplace 🇿🇦</div>
    </div>
    <div class="foot-cols">
      <div class="foot-col">
        <h4>Platform</h4>
        <a href="MarkeSA User Manual.pdf">How It Works</a>
        <a href="login.php?form=register">Start Selling</a>
        <a href="help.php">Help Centre</a>
      </div>
      <div class="foot-col">
        <h4>Support</h4>
        <a href="saftey.php">Safety Tips</a>
        <a href="report.php">Report a Listing</a>
        <a href="contact.php">Contact Us</a>
      </div>
      <div class="foot-col">
        <h4>Company</h4>
        <a href="about.php">About MarketSA</a>
        <a href="terms.php">Terms of Service</a>
        <a href="policy.php">Privacy Policy</a>
       
      </div>
    </div>
  </div>
  <div class="foot-bottom">
    <span>© 2026 MarketSA. All rights reserved.</span>
    <span>Made in South Africa 🇿🇦</span>
  </div>
</footer>

</body>
</html>