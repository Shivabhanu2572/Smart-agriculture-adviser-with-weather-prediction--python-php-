<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "smart_agri");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = $conn->real_escape_string($_POST['fullname']);
    $email = $conn->real_escape_string($_POST['email']);
    $location = $conn->real_escape_string($_POST['location']);
    $age = (int)$_POST['age'];
    $gender = $conn->real_escape_string($_POST['gender']);
    $contact = $conn->real_escape_string($_POST['contact']);

    $update_photo = "";
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === 0) {
        $filename = basename($_FILES['profile_photo']['name']);
        $target = 'uploads/' . $filename;
        move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target);
        $update_photo = ", profile_photo = '$filename'";
    }

    $sql = "UPDATE users SET fullname='$fullname', email='$email', location='$location', age=$age, gender='$gender', contact='$contact' $update_photo WHERE id=$user_id";
    if ($conn->query($sql)) {
        // After successful profile update (e.g., after update query and before redirect)
        $_SESSION['last_profile_update'] = date('Y-m-d H:i:s');
        header("Location: profile_dashboard.php");
        exit();
    } else {
        $error = "Update failed: " . $conn->error;
    }
}

// Fetch current user details
$result = $conn->query("SELECT * FROM users WHERE id=$user_id");
$user = $result->fetch_assoc();
$photo = !empty($user['profile_photo']) ? 'uploads/' . $user['profile_photo'] : 'default-user.png';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile | Smart Agriculture</title>
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
            max-width: 700px;
            margin: 30px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 25px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            font-weight: bold;
            margin-bottom: 6px;
            color: #555;
        }
        input[type="text"], input[type="email"], input[type="number"], select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 15px;
        }
        input[type="file"] {
            font-size: 14px;
        }
        .photo-preview {
            text-align: center;
            margin-bottom: 20px;
        }
        .photo-preview img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .actions {
            text-align: center;
            margin-top: 25px;
        }
        .actions input[type="submit"], .actions a {
            padding: 10px 20px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            font-size: 15px;
            margin: 0 10px;
        }
        .actions input[type="submit"]:hover, .actions a:hover {
            background-color: #1b5e20;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header">✏️ Edit Profile - Smart Agriculture</div>
<div class="container">
    <h2>Update Your Details</h2>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <div class="photo-preview">
        <img src="<?= $photo ?>" alt="Profile Photo">
    </div>

    <form method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Full Name:</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        </div>

        <div class="form-group">
            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="form-group">
            <label>Location:</label>
            <input type="text" name="location" value="<?= htmlspecialchars($user['location']) ?>">
        </div>

        <div class="form-group">
            <label>Age:</label>
            <input type="number" name="age" value="<?= htmlspecialchars($user['age']) ?>" min="1" max="100">
        </div>

        <div class="form-group">
            <label>Gender:</label>
            <select name="gender">
                <option value="Male" <?= $user['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
                <option value="Female" <?= $user['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
                <option value="Other" <?= $user['gender'] === 'Other' ? 'selected' : '' ?>>Other</option>
            </select>
        </div>

        <div class="form-group">
            <label>Contact No.:</label>
            <input type="text" name="contact" value="<?= htmlspecialchars($user['contact']) ?>">
        </div>

        <div class="form-group">
            <label>Change Profile Photo:</label>
            <input type="file" name="profile_photo" accept="image/*">
        </div>

        <div class="actions">
            <input type="submit" value="Update">
            <a href="profile_dashboard.php">Cancel</a>
        </div>
    </form>
</div>

</body>
</html>
