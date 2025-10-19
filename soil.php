<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Soil Analysis | Smart Agriculture</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', 'Segoe UI', sans-serif;
      background: url('uploads/equipment_images/soil.jpg') center center/cover no-repeat fixed;
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
      background: linear-gradient(135deg, rgba(0, 0, 0, 0.3) 0%, rgba(0, 0, 0, 0.5) 100%);
      backdrop-filter: blur(10px);
      color: white;
      padding: 20px 0;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
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
    .form-container {
      display: flex;
      justify-content: center;
      align-items: center;
      flex: 1;
      min-height: 60vh;
      margin-top: 32px;
      margin-bottom: 32px;
      padding: 10px;
    }
    .form-box {
      background: rgba(0, 0, 0, 0.4);
      backdrop-filter: blur(15px);
      color: white;
      padding: 30px 25px;
      border-radius: 18px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      border: 1px solid rgba(255, 255, 255, 0.3);
      width: 100%;
      max-width: 600px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }
    .form-box h2 {
      text-align: center;
      color: #fff;
      margin-bottom: 20px;
      font-size: 1.8rem;
      font-weight: 700;
      letter-spacing: 0.5px;
    }
    .form-box form {
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .form-group {
      margin-bottom: 15px;
      position: relative;
    }
    .form-group label {
      display: block;
      margin-bottom: 7px;
      font-weight: 600;
      color: #ffffff;
      font-size: 1rem;
    }
    .input-icon {
      position: absolute;
      left: 10px;
      top: 38px;
      color: rgba(255, 255, 255, 0.8);
      font-size: 1.1rem;
      pointer-events: none;
    }
    .form-box input[type="number"], 
    .form-box select {
      width: 100%;
      padding: 12px 12px 12px 36px;
      border: 1px solid rgba(255, 255, 255, 0.4);
      border-radius: 8px;
      font-size: 1rem;
      margin-bottom: 2px;
      background: rgba(0, 0, 0, 0.3);
      color: #ffffff;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      font-weight: 700;
      transition: all 0.3s ease;
      box-sizing: border-box;
    }
    .form-box input[type="number"]:focus, 
    .form-box select:focus {
      border: 1px solid rgba(255, 255, 255, 0.6);
      outline: none;
      background: rgba(0, 0, 0, 0.5);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .form-box select option {
      background: rgba(0, 0, 0, 0.95);
      color: #ffffff;
      font-weight: 700;
    }
    .helper-text {
      font-size: 0.85rem;
      color: rgba(255, 255, 255, 0.95);
      margin-top: 2px;
      margin-left: 2px;
      font-weight: 600;
    }
    .submit-btn {
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
    .submit-btn:hover {
      background: rgba(0, 0, 0, 0.6);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
    }
    .dashboard-btn {
      display: block;
      width: fit-content;
      margin: 12px auto 0 auto;
      background: rgba(0, 0, 0, 0.4);
      color: #ffffff;
      border: 1px solid rgba(255, 255, 255, 0.4);
      padding: 10px 22px;
      border-radius: 8px;
      text-align: center;
      text-decoration: none;
      font-size: 0.9rem;
      font-weight: 600;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
      transition: all 0.3s ease;
    }
    .dashboard-btn:hover {
      background: rgba(0, 0, 0, 0.6);
      box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
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
      .form-box {
        padding: 20px 15px;
        max-width: 95vw;
      }
      .form-box h2 {
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
  <h1>Soil Analysis Module</h1>
  <p>Unlock your farm's potential by understanding your soil. Enter your soil details below to get tailored advice!</p>
</div>

<div class="form-container">
  <div class="form-box">
    <h2><i class="fa-solid fa-magnifying-glass-chart"></i> Analyze Your Soil</h2>
    <form action="soil_result.php" method="POST">
      <div class="form-group">
        <label for="ph">Enter pH Value:</label>
        <span class="input-icon"><i class="fa-solid fa-flask-vial"></i></span>
        <input type="number" step="0.1" name="ph" id="ph" min="0" max="14" required>
        <div class="helper-text">Typical range: 5.5 - 7.5 for most crops.</div>
      </div>
      <div class="form-group">
        <label for="moisture">Moisture Level (%):</label>
        <span class="input-icon"><i class="fa-solid fa-tint"></i></span>
        <input type="number" name="moisture" id="moisture" min="0" max="100" required>
        <div class="helper-text">Ideal: 20% - 60% depending on crop and soil type.</div>
      </div>
      <div class="form-group">
        <label for="soil_type">Select Soil Type:</label>
        <span class="input-icon"><i class="fa-solid fa-mountain-sun"></i></span>
        <select name="soil_type" id="soil_type" required>
          <option value="">-- Choose Soil Type --</option>
          <option value="Alluvial">Alluvial Soil</option>
          <option value="Black">Black Soil</option>
          <option value="Red">Red Soil</option>
          <option value="Laterite">Laterite Soil</option>
          <option value="Mountain">Mountain Soil</option>
          <option value="Desert">Desert Soil</option>
          <option value="Peaty">Peaty and Marshy Soil</option>
          <option value="Saline">Saline Soil</option>
          <option value="Loamy">Loamy Soil</option>
          <option value="Sandy">Sandy Soil</option>
          <option value="Clayey">Clayey Soil</option>
          <!-- Karnataka-based soil types -->
          <option value="Forest">Forest Soil</option>
          <option value="Mixed Red and Black">Mixed Red and Black Soil</option>
          <option value="Coastal Alluvial">Coastal Alluvial Soil</option>
          <option value="Lateritic Gravelly">Lateritic Gravelly Soil</option>
          <option value="Red Sandy Loam">Red Sandy Loam Soil</option>
          <option value="Deep Black">Deep Black Soil</option>
          <option value="Medium Black">Medium Black Soil</option>
          <option value="Shallow Black">Shallow Black Soil</option>
        </select>
        <div class="helper-text">Choose the soil type that best matches your field.</div>
      </div>
      <button type="submit" class="submit-btn"><i class="fa-solid fa-microscope"></i> Analyze</button>
    </form>
    <a href="dashboard.php" class="dashboard-btn"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
  </div>
</div>

<div class="footer">
  &copy; <?php echo date('Y'); ?> Smart Agriculture | Empowering Farmers for a Greener Tomorrow
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

</body>
</html>
