<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>MarketSA - Report a Listing</title>

  <style>
    * {
      box-sizing: border-box;
      font-family: Arial, sans-serif;
    }

    body {
      margin: 0;
      background: linear-gradient(135deg, #f4f6f9, #eef2f7);
      color: #333;
    }

    header {
      background: #111;
      color: #fff;
      padding: 18px;
      text-align: center;
      font-size: 22px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    .container {
      max-width: 750px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    h2 {
      text-align: center;
      margin-bottom: 10px;
      color: #222;
    }

    .info {
      font-size: 14px;
      margin-bottom: 25px;
      padding: 12px;
      background: #f7f7f7;
      border-left: 4px solid #111;
      border-radius: 6px;
      color: #555;
      line-height: 1.5;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
      font-size: 14px;
    }

    input, textarea {
      width: 100%;
      padding: 12px;
      margin-top: 6px;
      border: 1px solid #ddd;
      border-radius: 8px;
      font-size: 14px;
      transition: 0.2s ease;
    }

    input:focus, textarea:focus {
      border-color: #111;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0,0,0,0.08);
    }

    textarea {
      min-height: 120px;
      resize: vertical;
    }

    .submit-btn {
      margin-top: 20px;
      width: 100%;
      background: #111;
      color: #fff;
      border: none;
      padding: 14px;
      font-size: 16px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s ease;
      font-weight: bold;
    }

    .submit-btn:hover {
      background: #333;
      transform: translateY(-1px);
    }

    .submit-btn:active {
      transform: scale(0.99);
    }

    .note {
      margin-top: 20px;
      font-size: 13px;
      color: #666;
      padding: 12px;
      background: #fafafa;
      border-left: 4px solid #ccc;
      border-radius: 6px;
    }
  </style>
</head>

<body>

<header>MarketSA- Contact US</header>

<div class="container">
  <h2>Contact Us</h2>

  <p class="info">
    Contact us For any enquieries or email us at
    <strong>admin@marketsa.co.za</strong>. 
  </p>

  <form action="https://formsubmit.co/3b0e56f2bb9741c0f1cb7c82d6883bbc"
      method="POST"
      enctype="multipart/form-data">


  <input type="hidden" name="_captcha" value="false">
  <input type="hidden" name="_template" value="table">

  <label for="reason">What do you want to enquire </label>
  <textarea id="reason" name="reason" required></textarea>

  <label for="name">Name</label>
  <input type="text" id="name" name="name" required>
<label for="name">Email Adress </label>
  <input type="text" id="email" name="email" required>
  
  

  <button type="submit">Submit</button>
</form>

  <p class="note">
    Our admin team will review your enquiry 1-4 business days.
    Thank you for Contacting MarketSA .
  </p>
</div>

</body>
</html>