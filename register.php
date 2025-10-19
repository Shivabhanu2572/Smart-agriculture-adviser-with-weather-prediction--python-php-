<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $conn = new mysqli("localhost", "root", "", "smart_agri");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $location = $_POST['location'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $contact = $_POST['contact'];

    $sql = "INSERT INTO users (fullname, email, password, location, age, gender, contact)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiss", $fullname, $email, $password, $location, $age, $gender, $contact);

    if ($stmt->execute()) {
        echo "<script>alert('Registration successful!'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register - Smart Agriculture</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', 'Segoe UI', sans-serif;
      background: url('uploads/equipment_images/dashboard.jpg') center center/cover no-repeat fixed;
      min-height: 100vh;
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
    .register-container {
      display: flex;
      justify-content: center;
      align-items: center;
      flex: 1;
      min-height: 60vh;
      margin-top: 32px;
      margin-bottom: 32px;
      padding: 10px;
    }
    .register-box {
      background: rgba(255, 255, 255, 0.15);
      backdrop-filter: blur(15px);
      color: white;
      padding: 30px 25px;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
      border: 1px solid rgba(255, 255, 255, 0.2);
      width: 100%;
      max-width: 450px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .register-box h2 {
      text-align: center;
      color: #fff;
      margin-bottom: 20px;
      font-size: 1.8rem;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .register-box form {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .register-box input,
    .register-box select {
      width: 100%;
      padding: 12px;
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 8px;
      font-size: 1rem;
      margin-bottom: 2px;
      background: rgba(0, 0, 0, 0.3);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      font-weight: 600;
      transition: all 0.3s ease;
    }
    .register-box input:focus,
    .register-box select:focus {
      border: 1px solid rgba(255, 255, 255, 0.6);
      outline: none;
      background: rgba(0, 0, 0, 0.4);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .register-box input::placeholder {
      color: rgba(255, 255, 255, 0.9);
      opacity: 1;
      font-weight: 600;
    }
    .register-box select option {
      background: rgba(0, 0, 0, 0.9);
      color: #ffffff;
      font-weight: 600;
    }
    .register-box button[type="submit"] {
      width: 100%;
      background: rgba(0, 0, 0, 0.4);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.4);
      padding: 12px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      margin-top: 8px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }
    .register-box button[type="submit"]:hover {
      background: rgba(0, 0, 0, 0.6);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
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
      .register-box {
        padding: 20px 15px;
        max-width: 95vw;
      }
      .register-box h2 {
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
  <span class="icon"><i class="fa-solid fa-seedling"></i></span>
  <h1>Smart Agriculture Registration</h1>
  <p>Join the community and unlock smart farming features!</p>
</div>
<div class="register-container">
  <div class="register-box">
    <h2>Create Your Account <span aria-label="seedling" role="img">🌱</span></h2>
    <form method="POST">
      <input type="text" name="fullname" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="text" name="location" placeholder="Location" required />
      <input type="number" name="age" placeholder="Age" required />
      <select name="gender" required>
        <option value="">Select Gender</option>
        <option value="Male">Male</option>
        <option value="Female">Female</option>
        <option value="Other">Other</option>
      </select>
      <input type="text" name="contact" placeholder="Contact Number" required />
      <button type="submit">Register</button>
    </form>
    <div class="footer-link">
      Already have an account? <a href="login.php">Login here</a>
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
