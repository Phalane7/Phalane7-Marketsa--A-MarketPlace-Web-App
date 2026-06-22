
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About MarketSA</title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Segoe UI',sans-serif;
}

body{
    background:#f4f7fa;
    color:#333;
    line-height:1.7;
}


.hero{
    min-height:100vh;
    background:linear-gradient(rgba(0,0,0,0.6),rgba(0,0,0,0.6)),
    url('southa.jpeg') center/cover;
    display:flex;
    justify-content:center;
    align-items:center;
    text-align:center;
    color:white;
    padding:20px;
}

.hero-content h1{
    font-size:4rem;
    margin-bottom:20px;
}

.hero-content p{
    max-width:700px;
    margin:auto;
    font-size:1.2rem;
}

.btn{
    display:inline-block;
    margin-top:25px;
    padding:14px 35px;
    background:#00b894;
    color:white;
    text-decoration:none;
    border-radius:50px;
    font-weight:bold;
    transition:0.3s;
}

.btn:hover{
    background:#009879;
    transform:translateY(-3px);
}


.about{
    padding:80px 10%;
    background:white;
}

.container{
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:40px;
}

.about-text{
    flex:1;
}

.about-text h2{
    color:#00695c;
    font-size:2.5rem;
    margin-bottom:20px;
}

.about-image{
    flex:1;
}

.about-image img{
    width:100%;
    border-radius:20px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}


.features{
    padding:80px 10%;
    text-align:center;
}

.features h2{
    font-size:2.5rem;
    color:#00695c;
    margin-bottom:50px;
}

.feature-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:25px;
}

.card{
    background:white;
    padding:35px;
    border-radius:20px;
    box-shadow:0 5px 20px rgba(0,0,0,0.08);
    transition:0.3s;
}

.card:hover{
    transform:translateY(-10px);
}

.card i{
    font-size:3rem;
    color:#00b894;
    margin-bottom:20px;
}

.card h3{
    margin-bottom:15px;
}


.mission{
    background:#00695c;
    padding:80px 10%;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
    gap:30px;
}

.mission-box{
    background:white;
    padding:40px;
    border-radius:20px;
}

.mission-box h2{
    color:#00695c;
    margin-bottom:15px;
}


.cta{
    background:linear-gradient(135deg,#00b894,#00695c);
    color:white;
    text-align:center;
    padding:100px 10%;
}

.cta h2{
    font-size:2.5rem;
    margin-bottom:15px;
}

.cta .btn{
    background:white;
    color:#00695c;
}

.cta .btn:hover{
    background:#f1f1f1;
}


footer{
    background:#222;
    color:white;
    text-align:center;
    padding:20px;
}


@media(max-width:768px){

    .hero-content h1{
        font-size:2.8rem;
    }

    .container{
        flex-direction:column;
    }
}
</style>
</head>

<body>

<section class="hero">
    <div class="hero-content">
        <h1>Welcome to MarketSA</h1>
        <p>
            South Africa's trusted online marketplace connecting buyers and sellers
            across the country.
        </p>
        <a href="#" class="btn">Explore Marketplace</a>
    </div>
</section>

<section class="about">
    <div class="container">

        <div class="about-text">
            <h2>Who We Are</h2>

            <p>
                MarketSA is a proudly South African online marketplace that enables
                people to buy, sell and discover products with ease.
            </p>

            <br>

            <p>
                Whether you're searching for electronics, fashion, vehicles,
                property, or services, MarketSA provides a secure platform
                that helps communities connect and grow.
            </p>
        </div>

        <div class="about-image">
            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=800" alt="MarketSA">
        </div>

    </div>
</section>

<section class="features">

    <h2>Why Choose MarketSA?</h2>

    <div class="feature-grid">

        <div class="card">
            <i class="fas fa-shield-alt"></i>
            <h3>Secure Platform</h3>
            <p>Safe buying and selling experience for all users.</p>
        </div>

        <div class="card">
            <i class="fas fa-users"></i>
            <h3>Community Driven</h3>
            <p>Supporting local businesses and individuals nationwide.</p>
        </div>

        <div class="card">
            <i class="fas fa-bolt"></i>
            <h3>Fast & Easy</h3>
            <p>Quick listings and simple navigation for everyone.</p>
        </div>

        <div class="card">
            <i class="fas fa-mobile-alt"></i>
            <h3>Mobile Friendly</h3>
            <p>Access MarketSA anytime from any device.</p>
        </div>

    </div>

</section>

<section class="mission">

    <div class="mission-box">
        <h2>Our Mission</h2>
        <p>
            To provide a trusted digital marketplace that empowers South Africans
            to buy, sell and connect with confidence.
        </p>
    </div>

    <div class="mission-box">
        <h2>Our Vision</h2>
        <p>
            To become South Africa's leading online marketplace and create
            opportunities for everyone.
        </p>
    </div>

</section>

<section class="cta">
    <h2>Join the MarketSA Community</h2>
    <p>Start buying, selling and connecting today.</p>
    <a href="#" class="btn">Get Started</a>
</section>

<footer>
    <p>&copy; 2026 MarketSA. All Rights Reserved.</p>
</footer>

</body>
</html>

