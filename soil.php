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
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #f0f9f4;
      margin: 0;
      padding: 0;
    }
    .header {
      background-color: #33691e;
      color: white;
      padding: 20px;
      text-align: center;
      font-size: 24px;
    }
    .form-container {
      max-width: 600px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    h2 {
      text-align: center;
      color: #2e7d32;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin: 15px 0 5px;
      font-weight: bold;
      color: #444;
    }
    input[type="number"], select {
      width: 100%;
      padding: 10px;
      font-size: 16px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }
    .submit-btn {
      display: block;
      margin: 25px auto 10px;
      background-color: #2e7d32;
      color: white;
      padding: 12px 25px;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      cursor: pointer;
    }
    .submit-btn:hover {
      background-color: #1b5e20;
    }
    .dashboard-btn {
      display: block;
      width: fit-content;
      margin: 10px auto 0;
      background-color: #33691e;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      text-align: center;
      text-decoration: none;
      font-size: 16px;
    }
    .dashboard-btn:hover {
      background-color: #2a5314;
    }
    #google_translate_element {
      position: fixed;
      top: 10px;
      right: 20px;
      z-index: 999;
    }
  </style>
</head>
<body>

<div id="google_translate_element"></div>

<div class="header">🧪 Soil Analysis Module</div>

<div class="form-container">
  <h2>Analyze Your Soil</h2>
  <form action="soil_result.php" method="POST">
    <label for="ph">Enter pH Value:</label>
    <input type="number" step="0.1" name="ph" id="ph" min="0" max="14" required>

    <label for="moisture">Moisture Level (%):</label>
    <input type="number" name="moisture" id="moisture" min="0" max="100" required>

    <label for="soil_type">Select Soil Type:</label>
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
    </select>

    <button type="submit" class="submit-btn">🔍 Analyze</button>
  </form>

  <!-- Dashboard Button -->
  <a href="dashboard.php" class="dashboard-btn">🏠 Back to Dashboard</a>
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
