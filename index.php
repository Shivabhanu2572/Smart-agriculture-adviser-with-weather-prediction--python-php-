<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart Agriculture Advisor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --main-green: #217a2b;
            --main-yellow: #ffe066;
            --main-white: #fff;
            --icon-green: #217a2b;
            --icon-yellow: #ffe066;
        }
        html, body {
            width: 100vw;
            height: 100vh;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: url('uploads/equipment_images/index.jpg') center center/cover no-repeat fixed;
            min-height: 100vh;
            color: var(--main-white);
            overflow: hidden;
        }
        .hero-section {
            width: 100vw;
            height: 100vh;
            background: url('uploads/equipment_images/index.jpg') center center/cover no-repeat;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .overlay {
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            width: 100vw;
            height: 100vh;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.5) 100%);
            backdrop-filter: blur(2px);
            z-index: 1;
        }
        .content {
            position: relative;
            z-index: 2;
            width: 100vw;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        h1 {
            color: var(--main-yellow);
            font-size: 2.7rem;
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 1.5px;
            text-shadow: 0 4px 24px rgba(0, 0, 0, 0.8), 0 1.5px 8px rgba(0, 0, 0, 0.6);
        }
        .subtitle {
            color: var(--main-white);
            font-size: 1.13rem;
            font-weight: 500;
            margin-bottom: 32px;
            max-width: 480px;
            line-height: 1.6;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.8);
        }
        .icon-row {
            display: flex;
            gap: 22px;
            justify-content: center;
            margin-bottom: 32px;
        }
        .icon-circle {
            background: rgba(33, 122, 43, 0.8);
            border-radius: 50%;
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(5px);
        }
        .icon-circle i {
            color: var(--icon-yellow);
            font-size: 1.7rem;
        }
        .action-buttons {
            display: flex;
            gap: 18px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 18px;
        }
        .action-buttons a {
            background: rgba(33, 122, 43, 0.8);
            color: var(--icon-yellow);
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.08rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(5px);
        }
        .action-buttons a:hover {
            background: rgba(33, 122, 43, 0.9);
            color: #ffe066;
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 4px 16px rgba(255, 224, 102, 0.4);
        }
        .footer {
            margin-top: 24px;
            color: var(--main-yellow);
            text-align: center;
            font-size: 1rem;
            letter-spacing: 0.5px;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
        }
        @media (max-width: 700px) {
            h1 { font-size: 1.4rem; }
            .subtitle { font-size: 1rem; }
            .icon-row { gap: 10px; }
            .icon-circle { width: 38px; height: 38px; }
            .icon-circle i { font-size: 1.1rem; }
            .footer { font-size: 0.95rem; }
        }
    </style>
</head>
<body>
<div class="hero-section">
    <div class="overlay"></div>
    <div class="content">
        <h1>SMART AGRICULTURE ADVISOR</h1>
        <div class="subtitle">
            Welcome to the future of farming! Get personalized crop advice, weather predictions, and smart tools to boost your agricultural success.
        </div>
        <div class="icon-row">
            <span class="icon-circle"><i class="fa-solid fa-tractor"></i></span>
            <span class="icon-circle"><i class="fa-solid fa-seedling"></i></span>
            <span class="icon-circle"><i class="fa-solid fa-cloud-sun-rain"></i></span>
            <span class="icon-circle"><i class="fa-solid fa-tint"></i></span>
            <span class="icon-circle"><i class="fa-solid fa-wheat-awn"></i></span>
        </div>
        <div class="action-buttons">
            <a href="register.php"><i class="fa-solid fa-user-plus"></i> Register</a>
            <a href="login.php"><i class="fa-solid fa-lock"></i> Login</a>
        </div>
        <div class="footer">
            &copy; 2025 Smart Agriculture Advisor, Made by Shivabhanu A R
        </div>
    </div>
</div>
</body>
</html>
