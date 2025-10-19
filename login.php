<?php
session_start();
include("db_connection.php");

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

   
    if (empty($email) || empty($password)) {
        $message = "Please enter both your email and password to continue.";
    } else {
        // Check if user exists
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($conn, $query);

        if ($result && mysqli_num_rows($result) === 1) {
            $user = mysqli_fetch_assoc($result);

           
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                
                // After successful authentication and before redirect/exit
                $_SESSION['last_login'] = date('Y-m-d H:i:s');
                // Redirect to dashboard
                header("Location: dashboard.php");
                exit();
            } else {
                $message = "Oops! The password you entered is incorrect.";
            }
        } else {
            $message = "We couldn't find an account with that email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Login | Smart Agriculture Advisor</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', 'Segoe UI', sans-serif;
      background: url('uploads/equipment_images/login.jpg') center center/cover no-repeat fixed;
      min-height: 100vh;
      height: 100vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
    }
    #google_translate_element {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 999;
    }
    .hero {
      background: linear-gradient(135deg, rgba(0, 0, 0, 0.4) 0%, rgba(0, 0, 0, 0.7) 100%);
      backdrop-filter: blur(10px);
      color: white;
      padding: 20px 0;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      position: relative;
    }
    .hero .icon {
      font-size: 32px;
      margin-bottom: 8px;
      display: block;
    }
    .hero h1 {
      margin: 0;
      font-size: 1.8rem;
      font-weight: 700;
      letter-spacing: 1px;
    }
    .hero p {
      margin: 5px 0 0 0;
      font-size: 0.9rem;
      font-weight: 400;
      color: #ffffff;
    }
    .login-container {
      display: flex;
      justify-content: center;
      align-items: center;
      flex: 1;
      height: calc(100vh - 120px);
    }
    .login-box {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      color: white;
      padding: 30px 25px;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      width: 100%;
      max-width: 380px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .login-box h2 {
      text-align: center;
      color: #fff;
      margin-bottom: 20px;
      font-size: 1.8rem;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .login-box form {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .login-box input[type="email"],
    .login-box input[type="password"] {
      width: 100%;
      padding: 12px;
      border: 1px solid rgba(255, 255, 255, 0.3);
      border-radius: 8px;
      font-size: 1rem;
      margin-bottom: 2px;
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }
    .login-box input[type="email"]::placeholder,
    .login-box input[type="password"]::placeholder {
      color: rgba(255, 255, 255, 0.8);
      opacity: 1;
    }
    .login-box input[type="submit"] {
      width: 100%;
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.3);
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 5px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
    }
    .login-box input[type="submit"]:hover {
      background: rgba(255, 255, 255, 0.3);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    }
    .message {
      text-align: center;
      margin-top: 15px;
      color: #ffd600;
      font-weight: 600;
      font-size: 0.9rem;
      background: rgba(255, 255, 255, 0.1);
      padding: 8px 0;
      border-radius: 8px;
      width: 100%;
    }
    .footer-link {
      text-align: center;
      margin-top: 15px;
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.9);
    }
    .footer-link a {
      color: #fff;
      text-decoration: underline;
      font-weight: 600;
    }
    .footer {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(10px);
      color: #ffffff;
      text-align: center;
      padding: 15px 0;
      font-size: 0.8rem;
      letter-spacing: 0.5px;
      box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
    }
    @media (max-width: 600px) {
      .hero h1 {
        font-size: 1.3rem;
      }
      .login-box {
        padding: 20px 15px;
        max-width: 95vw;
      }
      .login-box h2 {
        font-size: 1.5rem;
      }
    }
    .goog-te-banner-frame.skiptranslate, .goog-logo-link, .goog-te-gadget img {
      display: none !important;
    }
    body { top: 0 !important; }
  </style>
</head>
<body>
<div id="google_translate_element"></div>
<div class="hero">
  <span class="icon"><i class="fa-solid fa-leaf"></i></span>
  <h1>Smart Agriculture Login</h1>
  <p>Access your personalized dashboard and smart farming tools.</p>
</div>
<div class="login-container">
  <div class="login-box">
    <h2>Welcome Back <span aria-label="wave" role="img">👋</span></h2>
    <form method="POST">
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Your Password" required>
      <input type="submit" value="Login">
    </form>
    <?php if (!empty($message)): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>
    <div class="footer-link">
      New here? <a href="register.php">Create your free account</a>
    </div>
  </div>
</div>
<div class="footer">
  &copy; <?php echo date('Y'); ?> Smart Agriculture | Empowering Farmers
</div>
<script type="text/javascript">
  function googleTranslateElementInit() {
    new google.translate.TranslateElement({
      pageLanguage: 'en',
      includedLanguages: 'en,kn,hi,ta,te',
      layout: google.translate.TranslateElement.InlineLayout.SIMPLE
    }, 'google_translate_element');
  }
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
