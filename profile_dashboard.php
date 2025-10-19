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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary: #2d6a4f;
            --secondary: #38b000;
            --accent: #f9f9f9;
            --bg: #f5fdf7;
            --card-bg: #fff;
            --border-radius: 18px;
            --shadow: 0 4px 24px rgba(44, 62, 80, 0.08);
            --text: #222;
            --muted: #666;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(120deg, var(--bg) 60%, #e0ffe6 100%);
            min-height: 100vh;
            color: var(--text);
        }
        .container {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 16px;
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 8px;
            letter-spacing: -1px;
        }
        .header-desc {
            color: var(--muted);
            font-size: 1.1rem;
            margin-bottom: 0;
        }
        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 32px 28px 28px 28px;
            margin-bottom: 32px;
        }
        .profile-photo {
            display: flex;
            justify-content: center;
            margin-bottom: 24px;
        }
        .profile-photo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            border: 4px solid var(--secondary);
        }
        .info-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px 24px;
            margin-bottom: 0;
        }
        .info-list p {
            margin: 0;
            font-size: 1rem;
            padding: 8px 0;
        }
        .info-list strong {
            color: var(--secondary);
            font-weight: 600;
        }
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        .actions a {
            text-decoration: none;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            color: #fff;
            padding: 10px 22px;
            border-radius: 8px;
            margin: 0 12px;
            display: inline-block;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(44, 62, 80, 0.08);
            transition: background 0.2s, box-shadow 0.2s;
        }
        .actions a:hover {
            background: linear-gradient(90deg, var(--secondary), var(--primary));
            box-shadow: 0 4px 16px rgba(44, 62, 80, 0.12);
        }
        .card-box {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }
        .feature-card {
            background: #e9fbe5;
            border-left: 5px solid var(--secondary);
            padding: 20px;
            border-radius: 10px;
            width: 48%;
            min-width: 250px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .feature-card h4 {
            margin: 0 0 10px;
            color: var(--primary);
            font-size: 1.1rem;
        }
        .feature-card p {
            margin: 0;
            font-size: 15px;
            color: #444;
        }
        @media (max-width: 700px) {
            .container {
                padding: 0 4vw;
            }
            .card {
                padding: 18px 6vw 18px 6vw;
            }
            .info-list {
                flex-direction: column;
                gap: 10px;
            }
            .feature-card {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-title"><i class="fas fa-user-circle"></i> My Profile</div>
        <div class="header-desc">View and manage your account information</div>
    </div>
    <div class="container">
        <div class="card">
            <h2 style="text-align:center;margin-bottom:25px;color:var(--primary);font-weight:700;"><i class="fas fa-id-badge"></i> Account Information</h2>
            <div class="profile-photo">
                <img src="<?= $photo ?>" alt="Profile Photo">
            </div>
            <?php if ($user): ?>
                <div class="info-list" style="margin-bottom:18px;">
                    <p><strong>Full Name:</strong> <?= htmlspecialchars($user['fullname']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                    <p><strong>Location:</strong> <?= htmlspecialchars($user['location']) ?></p>
                    <p><strong>Age:</strong> <?= htmlspecialchars($user['age']) ?></p>
                    <p><strong>Gender:</strong> <?= htmlspecialchars($user['gender']) ?></p>
                    <p><strong>Contact No.:</strong> <?= htmlspecialchars($user['contact']) ?></p>
                </div>
            <?php else: ?>
                <p style="color: red; text-align: center;">User details not found.</p>
            <?php endif; ?>
            <!-- Profile Completion Progress -->
            <?php
            // Simple profile completion calculation (real logic)
            $fields = ['fullname', 'email', 'location', 'age', 'gender', 'contact', 'profile_photo'];
            $filled = 0;
            foreach ($fields as $f) {
                if (!empty($user[$f])) $filled++;
            }
            $completion = round(($filled / count($fields)) * 100);
            ?>
            <div style="margin-bottom:18px;">
                <strong>Profile Completion:</strong> <?= $completion ?>%
                <div class="progress-bar" style="background:#e0e0e0;border-radius:8px;height:12px;width:100%;overflow:hidden;margin-top:6px;">
                    <div class="progress-fill" style="background:linear-gradient(90deg,var(--primary),var(--secondary));height:100%;border-radius:8px;width:<?= $completion ?>%;transition:width 0.5s;"></div>
                </div>
                <?php if ($completion < 100): ?>
                    <div style="color:var(--muted);font-size:0.98rem;margin-top:4px;">Complete your profile for a better experience!</div>
                <?php endif; ?>
            </div>
            <!-- Recent Activity / Notifications (real data if available) -->
            <?php
            // These session variables are set in login.php, edit_profile.php, and irrigation_tips.php
            $last_login = isset($_SESSION['last_login']) && $_SESSION['last_login'] !== '' ? date('d M Y, h:i A', strtotime($_SESSION['last_login'])) : '<span style="color:#888">No login record</span>';
            $last_profile_update = isset($_SESSION['last_profile_update']) && $_SESSION['last_profile_update'] !== '' ? date('d M Y, h:i A', strtotime($_SESSION['last_profile_update'])) : '<span style="color:#888">No update record</span>';
            $last_advice = isset($_SESSION['last_advice']) && $_SESSION['last_advice'] !== '' ? htmlspecialchars($_SESSION['last_advice']) : '<span style="color:#888">No advice viewed yet</span>';
            ?>
            <div class="advice-box" style="background:#e3f0ff;border-left:5px solid #2196f3;border-radius:10px;padding:14px 18px;margin-bottom:18px;color:#1a3a5d;font-size:1.05rem;">
                <div style="font-weight:600;margin-bottom:4px;"><i class="fas fa-bell"></i> Recent Activity</div>
                <ul style="margin:0 0 0 18px;padding:0;font-size:0.98rem;">
                    <li>Last login: <?= $last_login ?></li>
                    <li>Last advice viewed: <?= $last_advice ?></li>
                    <li>Profile updated: <?= $last_profile_update ?></li>
                </ul>
            </div>
            <!-- Personalized Recommendations (real data if available) -->
            <?php
            $recommended_crop = isset($_SESSION['recommended_crop']) && $_SESSION['recommended_crop'] !== '' ? htmlspecialchars($_SESSION['recommended_crop']) : null;
            $recommendation = $recommended_crop ? "It’s time to irrigate your <strong>$recommended_crop</strong> crop! Check today’s weather before watering." : "<span style='color:#888'>No personalized recommendation available yet.</span>";
            ?>
            <div class="advice-box" style="background:#e9fbe5;border-left:5px solid var(--secondary);border-radius:10px;padding:14px 18px;margin-bottom:18px;color:#205c2c;font-size:1.05rem;">
                <div style="font-weight:600;margin-bottom:4px;"><i class="fas fa-lightbulb"></i> Personalized Recommendation</div>
                <div><?= $recommendation ?></div>
            </div>
            <div class="actions">
                <a href="edit_profile.php"><i class="fas fa-edit"></i> Edit Profile</a>
                <a href="dashboard.php"><i class="fas fa-home"></i> Back to Dashboard</a>
                <a href="#" style="background:linear-gradient(90deg,#2196f3,#38b000);margin-top:10px;"><i class="fas fa-cog"></i> Profile Settings</a>
            </div>
        </div>
    </div>
</body>
</html>
