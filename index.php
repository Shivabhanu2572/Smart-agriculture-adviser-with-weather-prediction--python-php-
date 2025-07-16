<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome | Smart Agriculture Advisor</title>
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: pink;
        }

        .hero-section {
            background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), 
                url('https://images.unsplash.com/photo-1591608516485-3fd1f259d440?auto=format&fit=crop&w=1500&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 20px;
            text-align: center;
        }

        h1 {
            font-size: 3rem;
            color:#1565c0;
            margin-bottom: 10px;
        }

        p {
            font-size: 1rem;
            max-width: 600px;
            margin-bottom: 30px;
            
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .action-buttons a {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: bold;
            transition: 0.3s ease;
        }

        .action-buttons a:hover {
            background-color: #218838;
        }

        .footer {
            position: absolute;
            bottom: 15px;
            width: 100%;
            text-align: center;
            color: #eee;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="hero-section">
    <h2>Smart Agriculture Advisor With Weather Prediction</h1>
    <p>
        Welcome to a new way of farming  </p>

    <div class="action-buttons">
        <a href="register.php">📝 new user Register</a>
        <a href="login.php">🔐 Login</a>
    </div>
</div>

<div class="footer">
    &copy; 2025 Smart Agriculture Advisor. Made by Shivabhanu A R.
</div>

</body>
</html>