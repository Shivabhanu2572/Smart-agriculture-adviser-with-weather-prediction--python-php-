<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

// Create tables if they don't exist
$create_crop_table = "CREATE TABLE IF NOT EXISTS crop_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    crop_type VARCHAR(50) NOT NULL,
    planting_date DATE NOT NULL,
    expected_harvest_date DATE,
    current_stage VARCHAR(50) DEFAULT 'Seedling',
    health_status VARCHAR(50) DEFAULT 'Good',
    growth_percentage INT DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_crop_table);

$create_pest_reports_table = "CREATE TABLE IF NOT EXISTS pest_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_id INT NOT NULL,
    problem_type VARCHAR(100) NOT NULL,
    description TEXT,
    severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
    photo_path VARCHAR(255),
    reported_date DATE NOT NULL,
    status ENUM('detected', 'treatment_started', 'recovering', 'resolved') DEFAULT 'detected',
    treatment_applied TEXT,
    recovery_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_pest_reports_table);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'add_crop':
            $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name']);
            $crop_type = mysqli_real_escape_string($conn, $_POST['crop_type']);
            $planting_date = $_POST['planting_date'];
            $expected_harvest = $_POST['expected_harvest_date'];
            
            $sql = "INSERT INTO crop_monitoring (user_id, crop_name, crop_type, planting_date, expected_harvest_date) 
                    VALUES ($user_id, '$crop_name', '$crop_type', '$planting_date', '$expected_harvest')";
            mysqli_query($conn, $sql);
            $message = "Crop added successfully!";
            break;
            
        case 'update_crop':
            $crop_id = $_POST['crop_id'];
            $current_stage = mysqli_real_escape_string($conn, $_POST['current_stage']);
            $health_status = mysqli_real_escape_string($conn, $_POST['health_status']);
            $growth_percentage = $_POST['growth_percentage'];
            $notes = mysqli_real_escape_string($conn, $_POST['notes']);
            
            $sql = "UPDATE crop_monitoring SET 
                    current_stage = '$current_stage',
                    health_status = '$health_status',
                    growth_percentage = $growth_percentage,
                    notes = '$notes'
                    WHERE id = $crop_id AND user_id = $user_id";
            mysqli_query($conn, $sql);
            $message = "Crop updated successfully!";
            break;
            
        case 'report_problem':
            $crop_id = $_POST['crop_id'];
            $problem_description = mysqli_real_escape_string($conn, $_POST['problem_description']);
            $problem_date = $_POST['problem_date'];
            $severity = $_POST['severity'];
            $notes = $_POST['notes'] ?? '';
            $photo_path = '';
            
            // Handle file upload
            if (isset($_FILES['problem_photo']) && $_FILES['problem_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = 'uploads/crop_problems/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES['problem_photo']['name'], PATHINFO_EXTENSION);
                $file_name = 'problem_' . time() . '.' . $file_extension;
                $photo_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['problem_photo']['tmp_name'], $photo_path)) {
                    // File uploaded successfully
                } else {
                    $error = "Failed to upload photo.";
                }
            }
            
            // Analyze problem and provide scientific information
            $problem_analysis = analyzeCropProblem($problem_description, $conn);
            $problem_type = $problem_analysis['problem_type'];
            $scientific_info = $problem_analysis['scientific_info'];
            $treatment_plan = $problem_analysis['treatment_plan'];
            
            $description = "Problem: $problem_description | Analysis: $problem_type | Scientific Info: $scientific_info | Treatment: $treatment_plan" . ($notes ? " | Notes: $notes" : "");
            
            if (empty($error)) {
                $sql = "INSERT INTO pest_reports (user_id, crop_id, problem_type, description, severity, photo_path, reported_date, status) 
                        VALUES ($user_id, $crop_id, '$problem_type', '$description', '$severity', '$photo_path', '$problem_date', 'detected')";
                
                if (mysqli_query($conn, $sql)) {
                    $message = "Crop problem reported successfully! Problem identified: $problem_type. Scientific analysis and treatment plan provided.";
                } else {
                    $error = "Failed to submit report: " . mysqli_error($conn);
                }
            }
            break;
            
        case 'update_status':
            $report_id = $_POST['report_id'];
            $status = $_POST['status'];
            $treatment_applied = mysqli_real_escape_string($conn, $_POST['treatment_applied']);
            $recovery_notes = mysqli_real_escape_string($conn, $_POST['recovery_notes']);
            
            $sql = "UPDATE pest_reports SET 
                    status = '$status',
                    treatment_applied = '$treatment_applied',
                    recovery_notes = '$recovery_notes'
                    WHERE id = $report_id AND user_id = $user_id";
            mysqli_query($conn, $sql);
            $message = "Status updated successfully!";
            break;
    }
}

// Function to analyze crop problems and provide scientific information
function analyzeCropProblem($description, $conn) {
    $description = strtolower($description);
    $problem_type = 'Unknown Problem';
    $scientific_info = '';
    $treatment_plan = '';
    
    // Comprehensive problem analysis based on keywords
    if (strpos($description, 'yellow') !== false && strpos($description, 'leaf') !== false) {
        if (strpos($description, 'brown') !== false || strpos($description, 'spot') !== false) {
            $problem_type = 'Leaf Blight Disease';
            $scientific_info = 'Caused by fungal pathogens (Alternaria, Septoria, or Cercospora). Affects photosynthesis and nutrient transport.';
            $treatment_plan = '1. Remove infected leaves immediately. 2. Apply copper-based fungicide (2g/liter water). 3. Improve air circulation. 4. Avoid overhead watering. 5. Apply neem oil (5ml/liter) every 7 days.';
        } else {
            $problem_type = 'Chlorosis (Yellowing)';
            $scientific_info = 'Caused by nutrient deficiency (nitrogen, iron, magnesium) or poor soil pH. Affects chlorophyll production.';
            $treatment_plan = '1. Test soil pH (should be 6.0-7.0). 2. Apply balanced NPK fertilizer (20-20-20). 3. For iron deficiency: apply chelated iron (2g/liter). 4. Improve soil drainage.';
        }
    } elseif (strpos($description, 'white') !== false && strpos($description, 'powder') !== false) {
        $problem_type = 'Powdery Mildew';
        $scientific_info = 'Fungal disease caused by Erysiphe, Sphaerotheca, or Podosphaera. Thrives in high humidity and moderate temperatures.';
        $treatment_plan = '1. Apply baking soda solution (1 tbsp + 1 liter water + 1 drop dish soap). 2. Use neem oil (5ml/liter water). 3. Apply sulfur-based fungicide. 4. Improve air circulation. 5. Remove infected parts.';
    } elseif (strpos($description, 'hole') !== false && strpos($description, 'leaf') !== false) {
        $problem_type = 'Insect Infestation';
        $scientific_info = 'Caused by chewing insects like caterpillars, beetles, or grasshoppers. Direct feeding damage to plant tissue.';
        $treatment_plan = '1. Hand-pick visible insects. 2. Apply neem oil (5ml/liter water). 3. Use Bacillus thuringiensis (Bt) for caterpillars. 4. Apply soap solution (2 tbsp/liter). 5. Use physical barriers (netting).';
    } elseif (strpos($description, 'black') !== false && strpos($description, 'spot') !== false) {
        $problem_type = 'Black Spot Disease';
        $scientific_info = 'Fungal disease caused by Diplocarpon rosae. Common in humid conditions with poor air circulation.';
        $treatment_plan = '1. Remove all infected leaves and stems. 2. Apply copper fungicide (2g/liter). 3. Use mancozeb (2g/liter). 4. Improve air circulation. 5. Avoid overhead watering.';
    } elseif (strpos($description, 'wilt') !== false || strpos($description, 'droop') !== false) {
        if (strpos($description, 'root') !== false) {
            $problem_type = 'Root Rot Disease';
            $scientific_info = 'Caused by soil-borne fungi (Phytophthora, Pythium, or Fusarium). Affects root system and water uptake.';
            $treatment_plan = '1. Improve soil drainage immediately. 2. Reduce watering frequency. 3. Apply cinnamon powder to soil (natural fungicide). 4. Use Trichoderma-based biofungicide. 5. Remove severely affected plants.';
        } else {
            $problem_type = 'Water Stress';
            $scientific_info = 'Caused by insufficient water, excessive heat, or poor root development. Affects turgor pressure in cells.';
            $treatment_plan = '1. Water deeply and regularly. 2. Apply mulch to retain moisture. 3. Provide shade during peak heat. 4. Check soil moisture with finger test. 5. Improve soil structure.';
        }
    } elseif (strpos($description, 'mold') !== false || strpos($description, 'fungus') !== false) {
        $problem_type = 'Fungal Infection';
        $scientific_info = 'Various fungal pathogens affecting different plant parts. Common in humid, poorly ventilated conditions.';
        $treatment_plan = '1. Remove all infected plant parts. 2. Apply neem oil (5ml/liter water). 3. Use copper fungicide (2g/liter). 4. Improve air circulation. 5. Reduce humidity around plants.';
    } elseif (strpos($description, 'small') !== false || strpos($description, 'stunt') !== false) {
        $problem_type = 'Nutrient Deficiency';
        $scientific_info = 'Lack of essential nutrients (N, P, K, Ca, Mg, S, Fe, Zn, Mn, Cu, B, Mo). Affects plant growth and development.';
        $treatment_plan = '1. Test soil for nutrient levels. 2. Apply balanced fertilizer (NPK 20-20-20). 3. Add organic compost. 4. Use foliar spray for micronutrients. 5. Adjust soil pH to 6.0-7.0.';
    } elseif (strpos($description, 'insect') !== false || strpos($description, 'bug') !== false) {
        $problem_type = 'Pest Infestation';
        $scientific_info = 'Various insects feeding on plant sap, leaves, or roots. Can transmit viral diseases.';
        $treatment_plan = '1. Identify specific pest type. 2. Apply neem oil (5ml/liter water). 3. Use insecticidal soap (2 tbsp/liter). 4. Apply pyrethrin-based insecticide. 5. Use beneficial insects (ladybugs, lacewings).';
    } else {
        $problem_type = 'General Plant Stress';
        $scientific_info = 'Multiple factors affecting plant health including environmental stress, poor growing conditions, or unknown pathogens.';
        $treatment_plan = '1. Assess growing conditions (light, water, soil). 2. Apply balanced fertilizer. 3. Improve air circulation. 4. Monitor for specific symptoms. 5. Consider soil testing.';
    }
    
    return [
        'problem_type' => $problem_type,
        'scientific_info' => $scientific_info,
        'treatment_plan' => $treatment_plan
    ];
}

// Get user's crops
$crops_query = "SELECT * FROM crop_monitoring WHERE user_id = $user_id ORDER BY planting_date DESC";
$crops_result = mysqli_query($conn, $crops_query);

// Get pest reports
$pest_reports_query = "SELECT pr.*, cm.crop_name FROM pest_reports pr 
                       JOIN crop_monitoring cm ON pr.crop_id = cm.id 
                       WHERE pr.user_id = $user_id 
                       ORDER BY pr.reported_date DESC";
$pest_reports_result = mysqli_query($conn, $pest_reports_query);

// Get weather data
$weather_data = null;
try {
    $locationRes = @file_get_contents('https://ipapi.co/json');
    if ($locationRes !== false) {
        $location = json_decode($locationRes, true);
        $city = $location['city'] ?? 'Bangalore';
        $apiKey = "7b46741e515713880330945106c0d3d8";
        $weatherRes = @file_get_contents("https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$apiKey&units=metric");
        if ($weatherRes !== false) {
            $weather_data = json_decode($weatherRes, true);
        }
    }
} catch (Exception $e) {
    // Weather data unavailable
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Crop Monitoring System | Smart Agriculture</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(120deg, #e8f5e9 0%, #f1f8e9 100%);
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #33691e 0%, #558b2f 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 4px 20px rgba(51,105,30,0.1);
        }
        .header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .nav-bar {
            background: white;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .nav-bar a {
            color: #33691e;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .nav-bar a:hover, .nav-bar a.active {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .card h3 {
            color: #33691e;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .weather-card {
            background: linear-gradient(135deg, #4caf50 0%, #66bb6a 100%);
            color: white;
            text-align: center;
        }
        .weather-card .temp {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 10px 0;
        }
        .weather-card .description {
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .crop-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
        .crop-item.critical {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        .crop-item.warning {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        .crop-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .crop-name {
            font-weight: 700;
            color: #333;
            font-size: 1.1rem;
        }
        .health-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .health-excellent { background: #d4edda; color: #155724; }
        .health-good { background: #d1ecf1; color: #0c5460; }
        .health-fair { background: #fff3cd; color: #856404; }
        .health-poor { background: #f8d7da; color: #721c24; }
        .health-critical { background: #f5c6cb; color: #721c24; }
        .progress-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s ease;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
            margin: 5px;
        }
        .btn-primary {
            background: #33691e;
            color: white;
        }
        .btn-primary:hover {
            background: #2e7d32;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 20px;
            border-radius: 12px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-control:focus {
            outline: none;
            border-color: #33691e;
            box-shadow: 0 0 0 3px rgba(51,105,30,0.1);
        }
        .problem-item {
            background: #fff3cd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }
        .problem-item.critical {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .problem-item.resolved {
            background: #d4edda;
            border-left-color: #28a745;
        }
        .scientific-info {
            background: #e3f2fd;
            border-radius: 6px;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #2196f3;
        }
        .treatment-plan {
            background: #f3e5f5;
            border-radius: 6px;
            padding: 10px;
            margin: 10px 0;
            border-left: 3px solid #9c27b0;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .status-detected { background: #fff3cd; color: #856404; }
        .status-treatment_started { background: #d1ecf1; color: #0c5460; }
        .status-recovering { background: #d4edda; color: #155724; }
        .status-resolved { background: #c3e6cb; color: #155724; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🌾 Complete Crop Monitoring System</h1>
        <p>Real-time crop health monitoring with scientific analysis</p>
    </div>
    
    <div class="nav-bar">
        <a href="dashboard.php">🏠 Dashboard</a>
        <a href="crop_monitoring_complete.php" class="active">🌱 Crop Monitoring</a>
        <a href="weather_dashboard.php">🌦️ Weather</a>
        <a href="marketing_price.php">📊 Market Prices</a>
        <a href="logout.php">🔓 Logout</a>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="dashboard-grid">
            <!-- Weather Card -->
            <div class="card weather-card">
                <h3><i class="fas fa-cloud-sun"></i> Current Weather</h3>
                <?php if ($weather_data): ?>
                    <div class="temp"><?php echo round($weather_data['main']['temp']); ?>°C</div>
                    <div class="description"><?php echo ucfirst($weather_data['weather'][0]['description']); ?></div>
                    <p>Humidity: <?php echo $weather_data['main']['humidity']; ?>%</p>
                    <p>Wind: <?php echo $weather_data['wind']['speed']; ?> m/s</p>
                <?php else: ?>
                    <div class="temp">N/A</div>
                    <div class="description">Weather unavailable</div>
                <?php endif; ?>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h3><i class="fas fa-tools"></i> Quick Actions</h3>
                <button class="btn btn-success" onclick="openModal('addCropModal')">
                    <i class="fas fa-plus"></i> Add New Crop
                </button>
                <button class="btn btn-warning" onclick="openModal('reportProblemModal')">
                    <i class="fas fa-bug"></i> Report Problem
                </button>
                <button class="btn btn-primary" onclick="refreshData()">
                    <i class="fas fa-sync"></i> Refresh Data
                </button>
            </div>
        </div>

        <!-- My Crops -->
        <div class="card">
            <h3><i class="fas fa-seedling"></i> My Crops</h3>
            <?php if (mysqli_num_rows($crops_result) > 0): ?>
                <?php while ($crop = mysqli_fetch_assoc($crops_result)): ?>
                    <div class="crop-item <?php echo strtolower($crop['health_status']); ?>">
                        <div class="crop-header">
                            <div class="crop-name"><?php echo htmlspecialchars($crop['crop_name']); ?> (<?php echo htmlspecialchars($crop['crop_type']); ?>)</div>
                            <span class="health-status health-<?php echo strtolower($crop['health_status']); ?>">
                                <?php echo $crop['health_status']; ?>
                            </span>
                        </div>
                        <p><strong>Planted:</strong> <?php echo date('M d, Y', strtotime($crop['planting_date'])); ?></p>
                        <p><strong>Stage:</strong> <?php echo $crop['current_stage']; ?></p>
                        <p><strong>Growth:</strong> <?php echo $crop['growth_percentage']; ?>%</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $crop['growth_percentage']; ?>%"></div>
                        </div>
                        <?php if ($crop['notes']): ?>
                            <p><strong>Notes:</strong> <?php echo htmlspecialchars($crop['notes']); ?></p>
                        <?php endif; ?>
                        <button class="btn btn-primary" onclick="editCrop(<?php echo htmlspecialchars(json_encode($crop)); ?>)">
                            <i class="fas fa-edit"></i> Update
                        </button>
                        <button class="btn btn-warning" onclick="reportProblem(<?php echo $crop['id']; ?>)">
                            <i class="fas fa-bug"></i> Report Problem
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No crops added yet. Click "Add New Crop" to get started!</p>
            <?php endif; ?>
        </div>

        <!-- Problem Reports -->
        <div class="card">
            <h3><i class="fas fa-exclamation-triangle"></i> Problem Reports</h3>
            <?php if (mysqli_num_rows($pest_reports_result) > 0): ?>
                <?php while ($report = mysqli_fetch_assoc($pest_reports_result)): ?>
                    <div class="problem-item <?php echo $report['status']; ?>">
                        <div class="crop-header">
                            <div class="crop-name"><?php echo htmlspecialchars($report['crop_name']); ?></div>
                            <span class="status-badge status-<?php echo $report['status']; ?>">
                                <?php echo ucfirst($report['status']); ?>
                            </span>
                        </div>
                        <p><strong>Problem:</strong> <?php echo htmlspecialchars($report['problem_type']); ?></p>
                        <p><strong>Severity:</strong> <?php echo ucfirst($report['severity']); ?></p>
                        <p><strong>Reported:</strong> <?php echo date('M d, Y', strtotime($report['reported_date'])); ?></p>
                        
                        <?php if ($report['photo_path']): ?>
                            <p><strong>Photo:</strong> <a href="<?php echo htmlspecialchars($report['photo_path']); ?>" target="_blank">View Photo</a></p>
                        <?php endif; ?>
                        
                        <div class="scientific-info">
                            <strong>🔬 Scientific Analysis:</strong><br>
                            <?php 
                            $desc_parts = explode(' | ', $report['description']);
                            foreach ($desc_parts as $part) {
                                if (strpos($part, 'Scientific Info:') !== false) {
                                    echo htmlspecialchars(str_replace('Scientific Info: ', '', $part));
                                    break;
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="treatment-plan">
                            <strong>💊 Treatment Plan:</strong><br>
                            <?php 
                            foreach ($desc_parts as $part) {
                                if (strpos($part, 'Treatment:') !== false) {
                                    echo htmlspecialchars(str_replace('Treatment: ', '', $part));
                                    break;
                                }
                            }
                            ?>
                        </div>
                        
                        <?php if ($report['treatment_applied']): ?>
                            <p><strong>Applied Treatment:</strong> <?php echo htmlspecialchars($report['treatment_applied']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($report['recovery_notes']): ?>
                            <p><strong>Recovery Notes:</strong> <?php echo htmlspecialchars($report['recovery_notes']); ?></p>
                        <?php endif; ?>
                        
                        <button class="btn btn-primary" onclick="updateStatus(<?php echo $report['id']; ?>)">
                            <i class="fas fa-edit"></i> Update Status
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No problem reports yet. Your crops are healthy!</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Crop Modal -->
    <div id="addCropModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Crop</h3>
                <span class="close" onclick="closeModal('addCropModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add_crop">
                
                <div class="form-group">
                    <label>Crop Name</label>
                    <input type="text" name="crop_name" class="form-control" required placeholder="e.g., Tomatoes, Corn, Wheat">
                </div>
                
                <div class="form-group">
                    <label>Crop Type</label>
                    <select name="crop_type" class="form-control" required>
                        <option value="">Select Crop Type</option>
                        <option value="Vegetables">Vegetables</option>
                        <option value="Grains">Grains</option>
                        <option value="Fruits">Fruits</option>
                        <option value="Pulses">Pulses</option>
                        <option value="Oilseeds">Oilseeds</option>
                        <option value="Spices">Spices</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Planting Date</label>
                    <input type="date" name="planting_date" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Expected Harvest Date</label>
                    <input type="date" name="expected_harvest_date" class="form-control">
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Add Crop
                </button>
            </form>
        </div>
    </div>

    <!-- Report Problem Modal -->
    <div id="reportProblemModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🌿 Report Crop Problem</h3>
                <span class="close" onclick="closeModal('reportProblemModal')">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="report_problem">
                
                <div class="form-group">
                    <label>Select Affected Crop:</label>
                    <select name="crop_id" class="form-control" required>
                        <option value="">Choose your crop...</option>
                        <?php mysqli_data_seek($crops_result, 0); ?>
                        <?php while ($crop = mysqli_fetch_assoc($crops_result)): ?>
                            <option value="<?php echo $crop['id']; ?>"><?php echo htmlspecialchars($crop['crop_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>📸 Upload Photo of Problem:</label>
                    <input type="file" name="problem_photo" class="form-control" accept="image/*" required>
                    <small>Take a clear photo of the affected plant/leaves</small>
                </div>

                <div class="form-group">
                    <label>🔍 Describe the Problem:</label>
                    <textarea name="problem_description" class="form-control" rows="4" required 
                              placeholder="Describe what you see in simple words. For example: 'Yellow leaves with brown spots' or 'White powder on leaves' or 'Holes in leaves with insects visible'"></textarea>
                    <small>Be as detailed as possible. Our system will analyze and provide scientific information.</small>
                </div>

                <div class="form-group">
                    <label>📅 When did you first notice this problem?</label>
                    <input type="date" name="problem_date" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>⚠️ How serious is it?</label>
                    <select name="severity" class="form-control" required>
                        <option value="">Choose severity...</option>
                        <option value="low">🟢 Low - Just a few plants affected</option>
                        <option value="medium">🟡 Medium - Some plants affected</option>
                        <option value="high">🟠 High - Many plants affected</option>
                        <option value="critical">🔴 Critical - Most/all plants affected</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>💬 Additional Notes (Optional):</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Any additional observations or notes..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary">📤 Submit Report</button>
            </form>
        </div>
    </div>

    <!-- Edit Crop Modal -->
    <div id="editCropModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Crop</h3>
                <span class="close" onclick="closeModal('editCropModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_crop">
                <input type="hidden" name="crop_id" id="edit_crop_id">
                
                <div class="form-group">
                    <label>Current Growth Stage</label>
                    <select name="current_stage" class="form-control" required>
                        <option value="Seedling">Seedling</option>
                        <option value="Vegetative">Vegetative</option>
                        <option value="Flowering">Flowering</option>
                        <option value="Fruiting">Fruiting</option>
                        <option value="Mature">Mature</option>
                        <option value="Harvest Ready">Harvest Ready</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Health Status</label>
                    <select name="health_status" class="form-control" required>
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Growth Percentage (0-100)</label>
                    <input type="number" name="growth_percentage" class="form-control" min="0" max="100" required>
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Any observations or notes..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Crop
                </button>
            </form>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div id="updateStatusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Update Problem Status</h3>
                <span class="close" onclick="closeModal('updateStatusModal')">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="report_id" id="update_report_id">
                
                <div class="form-group">
                    <label>Current Status</label>
                    <select name="status" class="form-control" required>
                        <option value="detected">🟡 Detected</option>
                        <option value="treatment_started">🟠 Treatment Started</option>
                        <option value="recovering">🟢 Recovering</option>
                        <option value="resolved">✅ Resolved</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Treatment Applied</label>
                    <textarea name="treatment_applied" class="form-control" rows="3" 
                              placeholder="What treatment did you apply?"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Recovery Notes</label>
                    <textarea name="recovery_notes" class="form-control" rows="3" 
                              placeholder="How is the crop responding to treatment?"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Status
                </button>
            </form>
        </div>
    </div>

    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function editCrop(cropData) {
            document.getElementById('edit_crop_id').value = cropData.id;
            document.querySelector('#editCropModal select[name="current_stage"]').value = cropData.current_stage;
            document.querySelector('#editCropModal select[name="health_status"]').value = cropData.health_status;
            document.querySelector('#editCropModal input[name="growth_percentage"]').value = cropData.growth_percentage;
            document.querySelector('#editCropModal textarea[name="notes"]').value = cropData.notes || '';
            openModal('editCropModal');
        }
        
        function reportProblem(cropId) {
            document.querySelector('#reportProblemModal select[name="crop_id"]').value = cropId;
            openModal('reportProblemModal');
        }
        
        function updateStatus(reportId) {
            document.getElementById('update_report_id').value = reportId;
            openModal('updateStatusModal');
        }
        
        function refreshData() {
            location.reload();
        }
        
        // Auto-refresh data every 5 minutes
        setInterval(function() {
            // Only refresh if no modals are open
            if (document.querySelectorAll('.modal[style*="block"]').length === 0) {
                refreshData();
            }
        }, 300000); // 5 minutes
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html> 