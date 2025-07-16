<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "smart_agri");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT fullname, email, location, age, gender, contact, profile_photo FROM users WHERE id = $user_id";
$result = $conn->query($sql);
$user = $result->fetch_assoc();
$photo = !empty($user['profile_photo']) ? 'uploads/' . $user['profile_photo'] : 'default-user.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Smart Agriculture</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f8e9;
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
        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 25px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 200px 1fr;
            gap: 12px 30px;
            font-size: 16px;
            align-items: center;
        }
        .label {
            font-weight: bold;
            color: #555;
            text-align: right;
        }
        .value {
            color: #222;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .actions a {
            text-decoration: none;
            background: #2e7d32;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            margin: 0 12px;
            display: inline-block;
        }
        .actions a:hover {
            background: #1b5e20;
        }
        .card-box {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .card {
            background: #e8f5e9;
            border-left: 5px solid #43a047;
            padding: 20px;
            border-radius: 10px;
            width: 48%;
            min-width: 250px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .card h4 {
            margin: 0 0 10px;
            color: #2e7d32;
        }
        .card p {
            margin: 0;
            font-size: 15px;
            color: #444;
        }
        .photo {
            text-align: center;
            margin-bottom: 30px;
        }
        .photo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="header">🌿 Smart Agriculture - My Profile</div>
    <div class="container">
        <h2>👤 Account Information</h2>

        <div class="photo">
            <img src="<?= $photo ?>" alt="Profile Photo">
        </div>

        <?php if ($user): ?>
            <div class="info-grid">
                <div class="label">Full Name:</div>
                <div class="value"><?= htmlspecialchars($user['fullname']) ?></div>

                <div class="label">Email:</div>
                <div class="value"><?= htmlspecialchars($user['email']) ?></div>

                <div class="label">Location:</div>
                <div class="value"><?= htmlspecialchars($user['location']) ?></div>

                <div class="label">Age:</div>
                <div class="value"><?= htmlspecialchars($user['age']) ?></div>

                <div class="label">Gender:</div>
                <div class="value"><?= htmlspecialchars($user['gender']) ?></div>

                <div class="label">Contact No.:</div>
                <div class="value"><?= htmlspecialchars($user['contact']) ?></div>
            </div>
        <?php else: ?>
            <p style="color: red; text-align: center;">User details not found.</p>
        <?php endif; ?>

        <div class="actions">
            <a href="edit_profile.php">✏️ Edit Profile</a>
            <a href="dashboard.php">🏠 Back to Dashboard</a>
        </div>

        <div class="card-box">
            <div class="card">
                <h4>🌦️ Weather Access</h4>
                <p>Track daily and weekly forecasts for your region.</p>
            </div>
            <div class="card">
                <h4>🩺 Soil Health</h4>
                <p>Monitor and maintain optimal soil conditions.</p>
            </div>
            <div class="card">
                <h4>📈 Irrigation Plans</h4>
                <p>Get smart irrigation tips based on weather data.</p>
            </div>
            <div class="card">
                <h4>💰 Crop Market</h4>
                <p>View latest market prices and trends.</p>
            </div>
        </div>
    </div>
</body>
</html>
