<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

// Always include the AI analyzer class before any use
require_once 'ai_image_analyzer.php';

// Create enhanced crop monitoring tables
$create_crop_table = "CREATE TABLE IF NOT EXISTS ai_crop_monitoring (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    variety VARCHAR(100),
    sow_date DATE NOT NULL,
    expected_harvest_date DATE,
    current_stage VARCHAR(50) DEFAULT 'Seedling',
    growth_percentage INT DEFAULT 0,
    health_status ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Critical') DEFAULT 'Good',
    disease_detected VARCHAR(200),
    fertilizer_applied TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_crop_table);

$create_disease_table = "CREATE TABLE IF NOT EXISTS crop_disease_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_id INT NOT NULL,
    user_id INT NOT NULL,
    image_path VARCHAR(255),
    detected_disease VARCHAR(200),
    confidence_score DECIMAL(5,2),
    symptoms TEXT,
    treatment_plan TEXT,
    fertilizer_recommendation TEXT,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Low',
    status ENUM('Detected', 'Treatment Started', 'Recovering', 'Resolved') DEFAULT 'Detected',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($conn, $create_disease_table);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_crop':
                $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name']);
                $variety = mysqli_real_escape_string($conn, $_POST['variety']);
                $sow_date = $_POST['sow_date'];
                
                // Calculate expected harvest date based on crop type
                $harvest_days = getCropHarvestDays($crop_name);
                $expected_harvest = date('Y-m-d', strtotime($sow_date . " + $harvest_days days"));
                
                $sql = "INSERT INTO ai_crop_monitoring (user_id, crop_name, variety, sow_date, expected_harvest_date) 
                        VALUES ($user_id, '$crop_name', '$variety', '$sow_date', '$expected_harvest')";
                mysqli_query($conn, $sql);
                $message = "Crop added successfully! Expected harvest: " . date('M d, Y', strtotime($expected_harvest));
                break;
                
            case 'analyze_image':
                $crop_id = $_POST['crop_id'];
                
                // Handle image upload
                if (isset($_FILES['crop_image']) && $_FILES['crop_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/crop_analysis/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = pathinfo($_FILES['crop_image']['name'], PATHINFO_EXTENSION);
                    $file_name = 'analysis_' . time() . '_' . $user_id . '.' . $file_extension;
                    $image_path = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($_FILES['crop_image']['tmp_name'], $image_path)) {
                        // AI Analysis (Simulated for now - replace with actual AI API)
                        $analysis_result = performAIAnalysis($image_path, $_POST['crop_name']);
                        
                        $sql = "INSERT INTO crop_disease_reports (crop_id, user_id, image_path, detected_disease, confidence_score, symptoms, treatment_plan, fertilizer_recommendation, severity) 
                                VALUES ($crop_id, $user_id, '$image_path', '{$analysis_result['disease']}', {$analysis_result['confidence']}, '{$analysis_result['symptoms']}', '{$analysis_result['treatment']}', '{$analysis_result['fertilizer']}', '{$analysis_result['severity']}')";
                        mysqli_query($conn, $sql);
                        
                        $message = "AI Analysis Complete! Disease: {$analysis_result['disease']} (Confidence: {$analysis_result['confidence']}%)";
                    }
                }
                break;
        }
    }
    
    // Handle delete crop analysis
    if (isset($_POST['delete_analysis'])) {
        $analysis_id = (int)$_POST['analysis_id'];
        $user_id = $_SESSION['user_id'];
        
        // Get image path before deleting
        $get_image = mysqli_query($conn, "SELECT image_path FROM crop_disease_reports WHERE id = $analysis_id AND user_id = $user_id");
        if ($get_image && $row = mysqli_fetch_assoc($get_image)) {
            $image_path = $row['image_path'];
            // Delete image file if exists
            if ($image_path && file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        // Delete from database
        mysqli_query($conn, "DELETE FROM crop_disease_reports WHERE id = $analysis_id AND user_id = $user_id");
        $message = "Crop analysis deleted successfully!";
    }
    
    // Handle delete crop
    if (isset($_POST['delete_crop'])) {
        $crop_id = (int)$_POST['crop_id'];
        $user_id = $_SESSION['user_id'];
        
        // Delete associated disease reports first
        mysqli_query($conn, "DELETE FROM crop_disease_reports WHERE crop_id = $crop_id AND user_id = $user_id");
        
        // Delete the crop
        mysqli_query($conn, "DELETE FROM ai_crop_monitoring WHERE id = $crop_id AND user_id = $user_id");
        $message = "Crop and all associated analyses deleted successfully!";
    }
}

// Get crop harvest days based on crop type
function getCropHarvestDays($crop_name) {
    $crop_data = [
        'Tomato' => 90, 'Potato' => 120, 'Onion' => 100, 'Carrot' => 80,
        'Cabbage' => 85, 'Cauliflower' => 80, 'Peas' => 70, 'Beans' => 60,
        'Cucumber' => 55, 'Pumpkin' => 100, 'Brinjal' => 85, 'Chilli' => 90,
        'Rice' => 120, 'Wheat' => 150, 'Maize' => 100, 'Sugarcane' => 365,
        'Cotton' => 180, 'Groundnut' => 120, 'Soybean' => 100, 'Sunflower' => 110
    ];
    
    foreach ($crop_data as $crop => $days) {
        if (stripos($crop_name, $crop) !== false) {
            return $days;
        }
    }
    return 90; // Default
}

// Calculate growth stage and percentage
function calculateGrowthStage($sow_date, $crop_name) {
    $days_since_sowing = (time() - strtotime($sow_date)) / (24 * 60 * 60);
    $total_days = getCropHarvestDays($crop_name);
    $percentage = min(100, max(0, ($days_since_sowing / $total_days) * 100));
    
    if ($percentage < 15) return ['stage' => 'Seedling', 'percentage' => $percentage];
    elseif ($percentage < 35) return ['stage' => 'Vegetative Growth', 'percentage' => $percentage];
    elseif ($percentage < 60) return ['stage' => 'Flowering', 'percentage' => $percentage];
    elseif ($percentage < 85) return ['stage' => 'Fruiting', 'percentage' => $percentage];
    else return ['stage' => 'Mature/Harvest Ready', 'percentage' => $percentage];
}

// AI Analysis Function using AI Image Analyzer
function performAIAnalysis($image_path, $crop_name) {
    global $conn;
    
    $analyzer = new AIImageAnalyzer($conn);
    $result = $analyzer->analyzeCropImage($image_path, $crop_name);
    
    return [
        'disease' => $result['disease_detected'],
        'confidence' => $result['confidence_score'],
        'severity' => $result['severity'],
        'symptoms' => $result['symptoms'],
        'treatment' => $result['treatment_plan'],
        'fertilizer' => $result['fertilizer_recommendation']
    ];
}

function getDiseaseSymptoms($disease, $crop_name) {
    $symptoms = [
        'Early Blight' => 'Brown spots with concentric rings on leaves, yellowing of lower leaves',
        'Powdery Mildew' => 'White powdery patches on leaves and stems',
        'Root Rot' => 'Wilting plants, brown/black roots, stunted growth',
        'Bacterial Spot' => 'Small dark spots with yellow halos on leaves',
        'Healthy Plant' => 'Green leaves, normal growth, no visible damage'
    ];
    return $symptoms[$disease] ?? 'Symptoms not identified';
}

function getTreatmentPlan($disease, $crop_name) {
    $treatments = [
        'Early Blight' => '1. Remove infected leaves immediately. 2. Apply copper fungicide (2g/liter). 3. Improve air circulation. 4. Avoid overhead watering.',
        'Powdery Mildew' => '1. Apply baking soda solution (1 tbsp + 1 liter water). 2. Use neem oil (5ml/liter). 3. Apply sulfur-based fungicide. 4. Improve air circulation.',
        'Root Rot' => '1. Improve soil drainage immediately. 2. Reduce watering frequency. 3. Apply cinnamon powder to soil. 4. Use Trichoderma biofungicide.',
        'Bacterial Spot' => '1. Remove infected plant parts. 2. Apply copper-based bactericide. 3. Avoid overhead irrigation. 4. Use disease-resistant varieties.',
        'Healthy Plant' => 'Continue current care routine. Monitor regularly for any changes.'
    ];
    return $treatments[$disease] ?? 'Treatment plan not available';
}

function getFertilizerRecommendation($disease, $crop_name) {
    $fertilizers = [
        'Early Blight' => 'NPK 20-20-20 (balanced) + Micronutrients (Fe, Zn, Mn)',
        'Powdery Mildew' => 'NPK 15-15-15 + Calcium nitrate for leaf strength',
        'Root Rot' => 'Phosphorus-rich fertilizer (NPK 10-26-26) + Organic compost',
        'Bacterial Spot' => 'NPK 20-20-20 + Potassium sulfate for disease resistance',
        'Healthy Plant' => 'NPK 20-20-20 (balanced) + Organic matter'
    ];
    return $fertilizers[$disease] ?? 'Standard NPK 20-20-20 fertilizer';
}

// Get user's crops
$crops_query = "SELECT * FROM ai_crop_monitoring WHERE user_id = $user_id ORDER BY sow_date DESC";
$crops_result = mysqli_query($conn, $crops_query);

// Get disease reports
$disease_query = "SELECT * FROM crop_disease_reports WHERE user_id = $user_id ORDER BY created_at DESC";
$disease_result = mysqli_query($conn, $disease_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Crop Monitor | Smart Agriculture</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
            color: #fff;
            background: linear-gradient(90deg, #43a047 70%, #388e3c 100%);
            text-decoration: none;
            padding: 12px 28px;
            margin: 0 10px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.08rem;
            box-shadow: 0 2px 8px rgba(67,160,71,0.10);
            transition: background 0.2s, color 0.2s, transform 0.18s;
            border: none;
            display: inline-block;
        }
        .nav-bar a:hover, .nav-bar a.active {
            background: linear-gradient(90deg, #388e3c 80%, #2e7d32 100%);
            color: #fff;
            transform: scale(1.06);
            box-shadow: 0 4px 16px rgba(56,142,60,0.13);
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
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h3 {
            color: #33691e;
            margin-bottom: 15px;
            font-size: 1.2rem;
            font-weight: 700;
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
        .btn-danger:hover {
            background: #c82333;
        }
        .action-buttons {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .crop-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
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
        .crop-item {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #28a745;
        }
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
        .disease-report {
            background: #fff3cd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #ffc107;
        }
        .disease-report.critical {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        .ai-analysis {
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
    </style>
</head>
<body>
    <div class="header">
        <h1>🤖 AI Crop Monitor</h1>
        <p>Real-time crop analysis with AI-powered disease detection</p>
    </div>
    
    <div class="nav-bar">
        <a href="dashboard.php">🏠 Back to Dashboard</a>
    </div>

    <div class="container">
        <?php if (isset($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>

        <!-- Add New Crop -->
        <div class="card add-crop-card" style="max-width: 480px; margin: 0 auto 32px auto; box-shadow: 0 6px 24px rgba(51,105,30,0.13); border-radius: 18px; background: #f9fff0; border: 1.5px solid #dcedc8;">
            <h3 style="text-align:center; color:#33691e; margin-top:18px; margin-bottom:10px; font-size:1.35rem; font-weight:700;"><i class="fas fa-plus-circle" style="color:#43a047;"></i> Add New Crop for AI Monitoring</h3>
            <form method="POST" style="padding: 0 28px 24px 28px;" autocomplete="off">
                <input type="hidden" name="action" value="add_crop">
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight:600; color:#2e7d32; margin-bottom:6px; display:block;"><i class="fas fa-seedling" style="margin-right:7px; color:#43a047;"></i>Crop Name</label>
                    <div style="position:relative;">
                        <select name="crop_name" class="form-control" required style="width:100%; padding:12px 40px 12px 16px; border-radius:9px; border:1.5px solid #c5e1a5; font-size:1.05rem; background:#fff; appearance:none; outline:none; transition: border 0.2s;">
                            <option value="">Select Crop</option>
                            <optgroup label="Vegetables">
                                <option value="Tomato">🍅 Tomato</option>
                                <option value="Potato">🥔 Potato</option>
                                <option value="Onion">🧅 Onion</option>
                                <option value="Carrot">🥕 Carrot</option>
                                <option value="Cabbage">🥬 Cabbage</option>
                                <option value="Cauliflower">🥦 Cauliflower</option>
                                <option value="Peas">🫛 Peas</option>
                                <option value="Beans">🫘 Beans</option>
                                <option value="Cucumber">🥒 Cucumber</option>
                                <option value="Pumpkin">🎃 Pumpkin</option>
                                <option value="Brinjal">🍆 Brinjal</option>
                                <option value="Chilli">🌶️ Chilli</option>
                                <option value="Spinach">🥬 Spinach</option>
                                <option value="Radish">Radish</option>
                                <option value="Beetroot">Beetroot</option>
                                <option value="Okra">Okra (Lady's Finger)</option>
                                <option value="Bottle Gourd">Bottle Gourd</option>
                                <option value="Bitter Gourd">Bitter Gourd</option>
                                <option value="Capsicum">Capsicum</option>
                                <option value="Sweet Corn">Sweet Corn</option>
                                <option value="Turnip">Turnip</option>
                                <option value="Lettuce">Lettuce</option>
                                <option value="Mushroom">Mushroom</option>
                                <option value="Yam">Yam</option>
                                <option value="Sweet Potato">Sweet Potato</option>
                                <option value="Broccoli">🥦 Broccoli</option>
                                <option value="Asparagus">Asparagus</option>
                                <option value="Artichoke">Artichoke</option>
                                <option value="Celery">Celery</option>
                                <option value="Parsley">Parsley</option>
                                <option value="Mint">Mint</option>
                                <option value="Basil">Basil</option>
                                <option value="Thyme">Thyme</option>
                                <option value="Rosemary">Rosemary</option>
                                <option value="Oregano">Oregano</option>
                                <option value="Sage">Sage</option>
                                <option value="Bay Leaf">Bay Leaf</option>
                                <option value="Curry Leaves">Curry Leaves</option>
                                <option value="Drumstick">Drumstick (Moringa)</option>
                                <option value="Taro">Taro (Arbi)</option>
                                <option value="Colocasia">Colocasia (Arbi)</option>
                                <option value="Elephant Foot Yam">Elephant Foot Yam</option>
                                <option value="Chinese Potato">Chinese Potato</option>
                                <option value="Arrowroot">Arrowroot</option>
                                <option value="Cassava">Cassava (Tapioca)</option>
                                <option value="Kohlrabi">Kohlrabi</option>
                                <option value="Rutabaga">Rutabaga</option>
                                <option value="Parsnip">Parsnip</option>
                                <option value="Salsify">Salsify</option>
                                <option value="Scorzonera">Scorzonera</option>
                                <option value="Jerusalem Artichoke">Jerusalem Artichoke</option>
                                <option value="Crosne">Crosne</option>
                                <option value="Sunchoke">Sunchoke</option>
                                <option value="Jicama">Jicama</option>
                                <option value="Daikon">Daikon</option>
                                <option value="Water Chestnut">Water Chestnut</option>
                                <option value="Lotus Root">Lotus Root</option>
                                <option value="Bamboo Shoots">Bamboo Shoots</option>
                                <option value="Fiddlehead Ferns">Fiddlehead Ferns</option>
                                <option value="Nettle">Nettle</option>
                                <option value="Dandelion">Dandelion</option>
                                <option value="Purslane">Purslane</option>
                                <option value="Amaranth">Amaranth</option>
                                <option value="Quinoa">Quinoa</option>
                                <option value="Buckwheat">Buckwheat</option>
                                <option value="Chia">Chia</option>
                                <option value="Flax">Flax</option>
                                <option value="Hemp">Hemp</option>
                                <option value="Teff">Teff</option>
                                <option value="Millet">Millet</option>
                                <option value="Fonio">Fonio</option>
                                <option value="Job's Tears">Job's Tears</option>
                                <option value="Canary Grass">Canary Grass</option>
                                <option value="Proso Millet">Proso Millet</option>
                                <option value="Foxtail Millet">Foxtail Millet</option>
                                <option value="Little Millet">Little Millet</option>
                                <option value="Kodo Millet">Kodo Millet</option>
                                <option value="Barnyard Millet">Barnyard Millet</option>
                                <option value="Browntop Millet">Browntop Millet</option>
                                <option value="Japanese Millet">Japanese Millet</option>
                                <option value="White Millet">White Millet</option>
                                <option value="Red Millet">Red Millet</option>
                                <option value="Yellow Millet">Yellow Millet</option>
                                <option value="Black Millet">Black Millet</option>
                                <option value="Grey Millet">Grey Millet</option>
                                <option value="Brown Millet">Brown Millet</option>
                                <option value="Mixed Millet">Mixed Millet</option>
                            </optgroup>
                            <optgroup label="Fruits">
                                <option value="Mango">🥭 Mango</option>
                                <option value="Banana">🍌 Banana</option>
                                <option value="Papaya">Papaya</option>
                                <option value="Guava">Guava</option>
                                <option value="Orange">🍊 Orange</option>
                                <option value="Lemon">🍋 Lemon</option>
                                <option value="Watermelon">🍉 Watermelon</option>
                                <option value="Muskmelon">Muskmelon</option>
                                <option value="Grapes">🍇 Grapes</option>
                                <option value="Pomegranate">Pomegranate</option>
                                <option value="Apple">🍏 Apple</option>
                                <option value="Sapota">Sapota (Chikoo)</option>
                                <option value="Pineapple">Pineapple</option>
                                <option value="Jackfruit">Jackfruit</option>
                                <option value="Custard Apple">Custard Apple</option>
                                <option value="Litchi">Litchi</option>
                                <option value="Strawberry">🍓 Strawberry</option>
                                <option value="Pear">Pear</option>
                                <option value="Plum">Plum</option>
                                <option value="Peach">Peach</option>
                                <option value="Cherry">Cherry</option>
                                <option value="Fig">Fig</option>
                                <option value="Avocado">Avocado</option>
                                <option value="Kiwi">🥝 Kiwi</option>
                                <option value="Dragon Fruit">Dragon Fruit</option>
                                <option value="Passion Fruit">Passion Fruit</option>
                                <option value="Lychee">Lychee</option>
                                <option value="Rambutan">Rambutan</option>
                                <option value="Longan">Longan</option>
                                <option value="Mangosteen">Mangosteen</option>
                                <option value="Durian">Durian</option>
                                <option value="Breadfruit">Breadfruit</option>
                                <option value="Soursop">Soursop</option>
                                <option value="Sugar Apple">Sugar Apple</option>
                                <option value="Cherimoya">Cherimoya</option>
                                <option value="Atemoya">Atemoya</option>
                                <option value="Rollinia">Rollinia</option>
                                <option value="Annona">Annona</option>
                                <option value="Custard Apple">Custard Apple</option>
                                <option value="Bullock's Heart">Bullock's Heart</option>
                                <option value="Pond Apple">Pond Apple</option>
                                <option value="Mountain Soursop">Mountain Soursop</option>
                                <option value="Ilama">Ilama</option>
                                <option value="Soncoya">Soncoya</option>
                                <option value="Biriba">Biriba</option>
                                <option value="Wild Custard Apple">Wild Custard Apple</option>
                                <option value="Red Custard Apple">Red Custard Apple</option>
                                <option value="Yellow Custard Apple">Yellow Custard Apple</option>
                                <option value="Green Custard Apple">Green Custard Apple</option>
                                <option value="Purple Custard Apple">Purple Custard Apple</option>
                                <option value="White Custard Apple">White Custard Apple</option>
                                <option value="Black Custard Apple">Black Custard Apple</option>
                                <option value="Brown Custard Apple">Brown Custard Apple</option>
                                <option value="Grey Custard Apple">Grey Custard Apple</option>
                                <option value="Mixed Custard Apple">Mixed Custard Apple</option>
                                <option value="Apricot">Apricot</option>
                                <option value="Nectarine">Nectarine</option>
                                <option value="Persimmon">Persimmon</option>
                                <option value="Quince">Quince</option>
                                <option value="Medlar">Medlar</option>
                                <option value="Loquat">Loquat</option>
                                <option value="Mulberry">Mulberry</option>
                                <option value="Blackberry">Blackberry</option>
                                <option value="Raspberry">Raspberry</option>
                                <option value="Blueberry">Blueberry</option>
                                <option value="Cranberry">Cranberry</option>
                                <option value="Gooseberry">Gooseberry</option>
                                <option value="Currant">Currant</option>
                                <option value="Elderberry">Elderberry</option>
                                <option value="Huckleberry">Huckleberry</option>
                                <option value="Bilberry">Bilberry</option>
                                <option value="Lingonberry">Lingonberry</option>
                                <option value="Cloudberry">Cloudberry</option>
                                <option value="Salmonberry">Salmonberry</option>
                                <option value="Thimbleberry">Thimbleberry</option>
                                <option value="Wineberry">Wineberry</option>
                                <option value="Boysenberry">Boysenberry</option>
                                <option value="Loganberry">Loganberry</option>
                                <option value="Tayberry">Tayberry</option>
                                <option value="Marionberry">Marionberry</option>
                                <option value="Olallieberry">Olallieberry</option>
                                <option value="Chesterberry">Chesterberry</option>
                                <option value="Triple Crown">Triple Crown</option>
                                <option value="Black Diamond">Black Diamond</option>
                                <option value="Columbia Star">Columbia Star</option>
                                <option value="Metolius">Metolius</option>
                                <option value="Obsidian">Obsidian</option>
                                <option value="Kotata">Kotata</option>
                                <option value="Siskiyou">Siskiyou</option>
                                <option value="Newberry">Newberry</option>
                                <option value="Waldo">Waldo</option>
                                <option value="Cascade">Cascade</option>
                                <option value="Willamette">Willamette</option>
                                <option value="Meeker">Meeker</option>
                                <option value="Tulameen">Tulameen</option>
                                <option value="Cascade Delight">Cascade Delight</option>
                                <option value="Cascade Bounty">Cascade Bounty</option>
                                <option value="Cascade Harvest">Cascade Harvest</option>
                                <option value="Cascade Gold">Cascade Gold</option>
                                <option value="Cascade Red">Cascade Red</option>
                                <option value="Cascade Black">Cascade Black</option>
                                <option value="Cascade Purple">Cascade Purple</option>
                                <option value="Cascade White">Cascade White</option>
                                <option value="Cascade Pink">Cascade Pink</option>
                                <option value="Cascade Orange">Cascade Orange</option>
                                <option value="Cascade Yellow">Cascade Yellow</option>
                                <option value="Cascade Green">Cascade Green</option>
                                <option value="Cascade Blue">Cascade Blue</option>
                                <option value="Cascade Brown">Cascade Brown</option>
                                <option value="Cascade Grey">Cascade Grey</option>
                                <option value="Cascade Mixed">Cascade Mixed</option>
                            </optgroup>
                            <optgroup label="Cereals">
                                <option value="Rice">🌾 Rice</option>
                                <option value="Wheat">🌾 Wheat</option>
                                <option value="Maize">🌽 Maize</option>
                                <option value="Barley">Barley</option>
                                <option value="Oats">Oats</option>
                                <option value="Sorghum">Sorghum (Jowar)</option>
                                <option value="Pearl Millet">Pearl Millet (Bajra)</option>
                                <option value="Finger Millet">Finger Millet (Ragi)</option>
                                <option value="Rye">Rye</option>
                                <option value="Triticale">Triticale</option>
                                <option value="Spelt">Spelt</option>
                                <option value="Einkorn">Einkorn</option>
                                <option value="Emmer">Emmer</option>
                                <option value="Khorasan">Khorasan</option>
                                <option value="Teff">Teff</option>
                                <option value="Fonio">Fonio</option>
                                <option value="Job's Tears">Job's Tears</option>
                                <option value="Canary Grass">Canary Grass</option>
                                <option value="Proso Millet">Proso Millet</option>
                                <option value="Foxtail Millet">Foxtail Millet</option>
                                <option value="Little Millet">Little Millet</option>
                                <option value="Kodo Millet">Kodo Millet</option>
                                <option value="Barnyard Millet">Barnyard Millet</option>
                                <option value="Browntop Millet">Browntop Millet</option>
                                <option value="Japanese Millet">Japanese Millet</option>
                                <option value="White Millet">White Millet</option>
                                <option value="Red Millet">Red Millet</option>
                                <option value="Yellow Millet">Yellow Millet</option>
                                <option value="Black Millet">Black Millet</option>
                                <option value="Grey Millet">Grey Millet</option>
                                <option value="Brown Millet">Brown Millet</option>
                                <option value="Mixed Millet">Mixed Millet</option>
                                <option value="Amaranth">Amaranth</option>
                                <option value="Quinoa">Quinoa</option>
                                <option value="Buckwheat">Buckwheat</option>
                                <option value="Chia">Chia</option>
                                <option value="Flax">Flax</option>
                                <option value="Hemp">Hemp</option>
                                <option value="Canary Seed">Canary Seed</option>
                                <option value="Niger Seed">Niger Seed</option>
                                <option value="Perilla">Perilla</option>
                                <option value="Sesame">Sesame</option>
                                <option value="Sunflower">Sunflower</option>
                                <option value="Pumpkin Seed">Pumpkin Seed</option>
                                <option value="Watermelon Seed">Watermelon Seed</option>
                                <option value="Cucumber Seed">Cucumber Seed</option>
                                <option value="Melon Seed">Melon Seed</option>
                                <option value="Squash Seed">Squash Seed</option>
                                <option value="Gourd Seed">Gourd Seed</option>
                                <option value="Bottle Gourd Seed">Bottle Gourd Seed</option>
                                <option value="Bitter Gourd Seed">Bitter Gourd Seed</option>
                                <option value="Ridge Gourd Seed">Ridge Gourd Seed</option>
                                <option value="Sponge Gourd Seed">Sponge Gourd Seed</option>
                                <option value="Snake Gourd Seed">Snake Gourd Seed</option>
                                <option value="Ash Gourd Seed">Ash Gourd Seed</option>
                                <option value="Wax Gourd Seed">Wax Gourd Seed</option>
                                <option value="Winter Melon Seed">Winter Melon Seed</option>
                                <option value="Summer Squash Seed">Summer Squash Seed</option>
                                <option value="Winter Squash Seed">Winter Squash Seed</option>
                                <option value="Acorn Squash Seed">Acorn Squash Seed</option>
                                <option value="Butternut Squash Seed">Butternut Squash Seed</option>
                                <option value="Spaghetti Squash Seed">Spaghetti Squash Seed</option>
                                <option value="Delicata Squash Seed">Delicata Squash Seed</option>
                                <option value="Kabocha Squash Seed">Kabocha Squash Seed</option>
                                <option value="Hubbard Squash Seed">Hubbard Squash Seed</option>
                                <option value="Turban Squash Seed">Turban Squash Seed</option>
                                <option value="Cushaw Squash Seed">Cushaw Squash Seed</option>
                                <option value="Banana Squash Seed">Banana Squash Seed</option>
                                <option value="Carnival Squash Seed">Carnival Squash Seed</option>
                                <option value="Sweet Dumpling Squash Seed">Sweet Dumpling Squash Seed</option>
                                <option value="Red Kuri Squash Seed">Red Kuri Squash Seed</option>
                                <option value="Blue Hubbard Squash Seed">Blue Hubbard Squash Seed</option>
                                <option value="Green Hubbard Squash Seed">Green Hubbard Squash Seed</option>
                                <option value="Golden Hubbard Squash Seed">Golden Hubbard Squash Seed</option>
                                <option value="White Hubbard Squash Seed">White Hubbard Squash Seed</option>
                                <option value="Grey Hubbard Squash Seed">Grey Hubbard Squash Seed</option>
                                <option value="Mixed Hubbard Squash Seed">Mixed Hubbard Squash Seed</option>
                            </optgroup>
                            <optgroup label="Pulses">
                                <option value="Chickpea">Chickpea (Gram)</option>
                                <option value="Pigeon Pea">Pigeon Pea (Tur/Arhar)</option>
                                <option value="Green Gram">Green Gram (Moong)</option>
                                <option value="Black Gram">Black Gram (Urad)</option>
                                <option value="Lentil">Lentil (Masoor)</option>
                                <option value="Peas">Peas</option>
                                <option value="Cowpea">Cowpea</option>
                                <option value="Horse Gram">Horse Gram</option>
                                <option value="Moth Bean">Moth Bean</option>
                                <option value="Kidney Bean">Kidney Bean</option>
                                <option value="Navy Bean">Navy Bean</option>
                                <option value="Pinto Bean">Pinto Bean</option>
                                <option value="Black Bean">Black Bean</option>
                                <option value="Red Bean">Red Bean</option>
                                <option value="White Bean">White Bean</option>
                                <option value="Cannellini Bean">Cannellini Bean</option>
                                <option value="Great Northern Bean">Great Northern Bean</option>
                                <option value="Lima Bean">Lima Bean</option>
                                <option value="Butter Bean">Butter Bean</option>
                                <option value="Fava Bean">Fava Bean</option>
                                <option value="Broad Bean">Broad Bean</option>
                                <option value="Runner Bean">Runner Bean</option>
                                <option value="Scarlet Runner Bean">Scarlet Runner Bean</option>
                                <option value="Tepary Bean">Tepary Bean</option>
                                <option value="Mung Bean">Mung Bean</option>
                                <option value="Adzuki Bean">Adzuki Bean</option>
                                <option value="Moth Bean">Moth Bean</option>
                                <option value="Rice Bean">Rice Bean</option>
                                <option value="Winged Bean">Winged Bean</option>
                                <option value="Yardlong Bean">Yardlong Bean</option>
                                <option value="Asparagus Bean">Asparagus Bean</option>
                                <option value="Snake Bean">Snake Bean</option>
                                <option value="Chinese Long Bean">Chinese Long Bean</option>
                                <option value="Bora Bean">Bora Bean</option>
                                <option value="Lablab Bean">Lablab Bean</option>
                                <option value="Hyacinth Bean">Hyacinth Bean</option>
                                <option value="Egyptian Bean">Egyptian Bean</option>
                                <option value="Indian Bean">Indian Bean</option>
                                <option value="Australian Bean">Australian Bean</option>
                                <option value="African Bean">African Bean</option>
                                <option value="American Bean">American Bean</option>
                                <option value="European Bean">European Bean</option>
                                <option value="Asian Bean">Asian Bean</option>
                                <option value="South American Bean">South American Bean</option>
                                <option value="Central American Bean">Central American Bean</option>
                                <option value="North American Bean">North American Bean</option>
                                <option value="Caribbean Bean">Caribbean Bean</option>
                                <option value="Pacific Bean">Pacific Bean</option>
                                <option value="Atlantic Bean">Atlantic Bean</option>
                                <option value="Indian Ocean Bean">Indian Ocean Bean</option>
                                <option value="Arctic Bean">Arctic Bean</option>
                                <option value="Antarctic Bean">Antarctic Bean</option>
                                <option value="Tropical Bean">Tropical Bean</option>
                                <option value="Subtropical Bean">Subtropical Bean</option>
                                <option value="Temperate Bean">Temperate Bean</option>
                                <option value="Boreal Bean">Boreal Bean</option>
                                <option value="Alpine Bean">Alpine Bean</option>
                                <option value="Desert Bean">Desert Bean</option>
                                <option value="Mediterranean Bean">Mediterranean Bean</option>
                                <option value="Continental Bean">Continental Bean</option>
                                <option value="Maritime Bean">Maritime Bean</option>
                                <option value="Highland Bean">Highland Bean</option>
                                <option value="Lowland Bean">Lowland Bean</option>
                                <option value="Upland Bean">Upland Bean</option>
                                <option value="Wetland Bean">Wetland Bean</option>
                                <option value="Dryland Bean">Dryland Bean</option>
                                <option value="Irrigated Bean">Irrigated Bean</option>
                                <option value="Rainfed Bean">Rainfed Bean</option>
                                <option value="Organic Bean">Organic Bean</option>
                                <option value="Conventional Bean">Conventional Bean</option>
                                <option value="Hybrid Bean">Hybrid Bean</option>
                                <option value="Heirloom Bean">Heirloom Bean</option>
                                <option value="GMO Bean">GMO Bean</option>
                                <option value="Non-GMO Bean">Non-GMO Bean</option>
                                <option value="Biodynamic Bean">Biodynamic Bean</option>
                                <option value="Permaculture Bean">Permaculture Bean</option>
                                <option value="Agroforestry Bean">Agroforestry Bean</option>
                                <option value="Intercropping Bean">Intercropping Bean</option>
                                <option value="Monoculture Bean">Monoculture Bean</option>
                                <option value="Polyculture Bean">Polyculture Bean</option>
                                <option value="Mixed Bean">Mixed Bean</option>
                            </optgroup>
                            <optgroup label="Oilseeds">
                                <option value="Groundnut">🥜 Groundnut</option>
                                <option value="Soybean">🫘 Soybean</option>
                                <option value="Sunflower">🌻 Sunflower</option>
                                <option value="Mustard">Mustard</option>
                                <option value="Sesame">Sesame (Til)</option>
                                <option value="Safflower">Safflower</option>
                                <option value="Linseed">Linseed (Flaxseed)</option>
                                <option value="Castor">Castor</option>
                                <option value="Rapeseed">Rapeseed</option>
                                <option value="Canola">Canola</option>
                                <option value="Olive">Olive</option>
                                <option value="Palm">Palm</option>
                                <option value="Coconut">Coconut</option>
                                <option value="Almond">Almond</option>
                                <option value="Walnut">Walnut</option>
                                <option value="Pecan">Pecan</option>
                                <option value="Hazelnut">Hazelnut</option>
                                <option value="Pistachio">Pistachio</option>
                                <option value="Cashew">Cashew</option>
                                <option value="Macadamia">Macadamia</option>
                                <option value="Brazil Nut">Brazil Nut</option>
                                <option value="Pine Nut">Pine Nut</option>
                                <option value="Pumpkin Seed">Pumpkin Seed</option>
                                <option value="Watermelon Seed">Watermelon Seed</option>
                                <option value="Cucumber Seed">Cucumber Seed</option>
                                <option value="Melon Seed">Melon Seed</option>
                                <option value="Squash Seed">Squash Seed</option>
                                <option value="Gourd Seed">Gourd Seed</option>
                                <option value="Bottle Gourd Seed">Bottle Gourd Seed</option>
                                <option value="Bitter Gourd Seed">Bitter Gourd Seed</option>
                                <option value="Ridge Gourd Seed">Ridge Gourd Seed</option>
                                <option value="Sponge Gourd Seed">Sponge Gourd Seed</option>
                                <option value="Snake Gourd Seed">Snake Gourd Seed</option>
                                <option value="Ash Gourd Seed">Ash Gourd Seed</option>
                                <option value="Wax Gourd Seed">Wax Gourd Seed</option>
                                <option value="Winter Melon Seed">Winter Melon Seed</option>
                                <option value="Summer Squash Seed">Summer Squash Seed</option>
                                <option value="Winter Squash Seed">Winter Squash Seed</option>
                                <option value="Acorn Squash Seed">Acorn Squash Seed</option>
                                <option value="Butternut Squash Seed">Butternut Squash Seed</option>
                                <option value="Spaghetti Squash Seed">Spaghetti Squash Seed</option>
                                <option value="Delicata Squash Seed">Delicata Squash Seed</option>
                                <option value="Kabocha Squash Seed">Kabocha Squash Seed</option>
                                <option value="Hubbard Squash Seed">Hubbard Squash Seed</option>
                                <option value="Turban Squash Seed">Turban Squash Seed</option>
                                <option value="Cushaw Squash Seed">Cushaw Squash Seed</option>
                                <option value="Banana Squash Seed">Banana Squash Seed</option>
                                <option value="Carnival Squash Seed">Carnival Squash Seed</option>
                                <option value="Sweet Dumpling Squash Seed">Sweet Dumpling Squash Seed</option>
                                <option value="Red Kuri Squash Seed">Red Kuri Squash Seed</option>
                                <option value="Blue Hubbard Squash Seed">Blue Hubbard Squash Seed</option>
                                <option value="Green Hubbard Squash Seed">Green Hubbard Squash Seed</option>
                                <option value="Golden Hubbard Squash Seed">Golden Hubbard Squash Seed</option>
                                <option value="White Hubbard Squash Seed">White Hubbard Squash Seed</option>
                                <option value="Grey Hubbard Squash Seed">Grey Hubbard Squash Seed</option>
                                <option value="Mixed Hubbard Squash Seed">Mixed Hubbard Squash Seed</option>
                                <option value="Jojoba">Jojoba</option>
                                <option value="Meadowfoam">Meadowfoam</option>
                                <option value="Camelina">Camelina</option>
                                <option value="Crambe">Crambe</option>
                                <option value="Lesquerella">Lesquerella</option>
                                <option value="Stokes Aster">Stokes Aster</option>
                                <option value="Vernonia">Vernonia</option>
                                <option value="Euphorbia">Euphorbia</option>
                                <option value="Calendula">Calendula</option>
                                <option value="Borage">Borage</option>
                                <option value="Evening Primrose">Evening Primrose</option>
                                <option value="Black Currant">Black Currant</option>
                                <option value="Red Currant">Red Currant</option>
                                <option value="White Currant">White Currant</option>
                                <option value="Pink Currant">Pink Currant</option>
                                <option value="Yellow Currant">Yellow Currant</option>
                                <option value="Purple Currant">Purple Currant</option>
                                <option value="Green Currant">Green Currant</option>
                                <option value="Blue Currant">Blue Currant</option>
                                <option value="Orange Currant">Orange Currant</option>
                                <option value="Brown Currant">Brown Currant</option>
                                <option value="Grey Currant">Grey Currant</option>
                                <option value="Mixed Currant">Mixed Currant</option>
                            </optgroup>
                            <optgroup label="Spices & Condiments">
                                <option value="Turmeric">Turmeric</option>
                                <option value="Ginger">Ginger</option>
                                <option value="Garlic">Garlic</option>
                                <option value="Coriander">Coriander</option>
                                <option value="Cumin">Cumin</option>
                                <option value="Fenugreek">Fenugreek</option>
                                <option value="Cardamom">Cardamom</option>
                                <option value="Clove">Clove</option>
                                <option value="Pepper">Pepper</option>
                                <option value="Fennel">Fennel</option>
                                <option value="Ajwain">Ajwain</option>
                                <option value="Mustard">Mustard</option>
                                <option value="Cinnamon">Cinnamon</option>
                                <option value="Nutmeg">Nutmeg</option>
                                <option value="Mace">Mace</option>
                                <option value="Allspice">Allspice</option>
                                <option value="Star Anise">Star Anise</option>
                                <option value="Saffron">Saffron</option>
                                <option value="Vanilla">Vanilla</option>
                                <option value="Cocoa">Cocoa</option>
                                <option value="Coffee">Coffee</option>
                                <option value="Tea">Tea</option>
                                <option value="Mint">Mint</option>
                                <option value="Basil">Basil</option>
                                <option value="Thyme">Thyme</option>
                                <option value="Rosemary">Rosemary</option>
                                <option value="Oregano">Oregano</option>
                                <option value="Sage">Sage</option>
                                <option value="Bay Leaf">Bay Leaf</option>
                                <option value="Curry Leaves">Curry Leaves</option>
                                <option value="Kaffir Lime">Kaffir Lime</option>
                                <option value="Lemongrass">Lemongrass</option>
                                <option value="Galangal">Galangal</option>
                                <option value="Shallot">Shallot</option>
                                <option value="Leek">Leek</option>
                                <option value="Chive">Chive</option>
                                <option value="Spring Onion">Spring Onion</option>
                                <option value="Red Onion">Red Onion</option>
                                <option value="White Onion">White Onion</option>
                                <option value="Yellow Onion">Yellow Onion</option>
                                <option value="Purple Onion">Purple Onion</option>
                                <option value="Green Onion">Green Onion</option>
                                <option value="Brown Onion">Brown Onion</option>
                                <option value="Grey Onion">Grey Onion</option>
                                <option value="Mixed Onion">Mixed Onion</option>
                                <option value="Black Pepper">Black Pepper</option>
                                <option value="White Pepper">White Pepper</option>
                                <option value="Green Pepper">Green Pepper</option>
                                <option value="Red Pepper">Red Pepper</option>
                                <option value="Pink Pepper">Pink Pepper</option>
                                <option value="Yellow Pepper">Yellow Pepper</option>
                                <option value="Purple Pepper">Purple Pepper</option>
                                <option value="Orange Pepper">Orange Pepper</option>
                                <option value="Brown Pepper">Brown Pepper</option>
                                <option value="Grey Pepper">Grey Pepper</option>
                                <option value="Mixed Pepper">Mixed Pepper</option>
                                <option value="Cayenne Pepper">Cayenne Pepper</option>
                                <option value="Paprika">Paprika</option>
                                <option value="Chili Powder">Chili Powder</option>
                                <option value="Red Chili">Red Chili</option>
                                <option value="Green Chili">Green Chili</option>
                                <option value="Yellow Chili">Yellow Chili</option>
                                <option value="Purple Chili">Purple Chili</option>
                                <option value="Orange Chili">Orange Chili</option>
                                <option value="Brown Chili">Brown Chili</option>
                                <option value="Grey Chili">Grey Chili</option>
                                <option value="Mixed Chili">Mixed Chili</option>
                                <option value="Jalapeno">Jalapeno</option>
                                <option value="Habanero">Habanero</option>
                                <option value="Serrano">Serrano</option>
                                <option value="Poblano">Poblano</option>
                                <option value="Anaheim">Anaheim</option>
                                <option value="Chipotle">Chipotle</option>
                                <option value="Ancho">Ancho</option>
                                <option value="Pasilla">Pasilla</option>
                                <option value="Guajillo">Guajillo</option>
                                <option value="Arbol">Arbol</option>
                                <option value="Cascabel">Cascabel</option>
                                <option value="Mulato">Mulato</option>
                                <option value="Negro">Negro</option>
                                <option value="Blanco">Blanco</option>
                                <option value="Rojo">Rojo</option>
                                <option value="Verde">Verde</option>
                                <option value="Amarillo">Amarillo</option>
                                <option value="Morado">Morado</option>
                                <option value="Naranja">Naranja</option>
                                <option value="Marron">Marron</option>
                                <option value="Gris">Gris</option>
                                <option value="Mixto">Mixto</option>
                            </optgroup>
                            <optgroup label="Cash Crops & Others">
                                <option value="Cotton">🧶 Cotton</option>
                                <option value="Sugarcane">Sugarcane</option>
                                <option value="Tobacco">Tobacco</option>
                                <option value="Tea">Tea</option>
                                <option value="Coffee">Coffee</option>
                                <option value="Cocoa">Cocoa</option>
                                <option value="Rubber">Rubber</option>
                                <option value="Jute">Jute</option>
                                <option value="Arecanut">Arecanut</option>
                                <option value="Betel Leaf">Betel Leaf</option>
                                <option value="Hemp">Hemp</option>
                                <option value="Flax">Flax</option>
                                <option value="Kenaf">Kenaf</option>
                                <option value="Ramie">Ramie</option>
                                <option value="Sisal">Sisal</option>
                                <option value="Abaca">Abaca</option>
                                <option value="Coir">Coir</option>
                                <option value="Kapok">Kapok</option>
                                <option value="Bamboo">Bamboo</option>
                                <option value="Rattan">Rattan</option>
                                <option value="Cane">Cane</option>
                                <option value="Reed">Reed</option>
                                <option value="Rush">Rush</option>
                                <option value="Sedge">Sedge</option>
                                <option value="Grass">Grass</option>
                                <option value="Hay">Hay</option>
                                <option value="Straw">Straw</option>
                                <option value="Silage">Silage</option>
                                <option value="Fodder">Fodder</option>
                                <option value="Forage">Forage</option>
                                <option value="Pasture">Pasture</option>
                                <option value="Meadow">Meadow</option>
                                <option value="Prairie">Prairie</option>
                                <option value="Savanna">Savanna</option>
                                <option value="Steppe">Steppe</option>
                                <option value="Pampas">Pampas</option>
                                <option value="Veld">Veld</option>
                                <option value="Outback">Outback</option>
                                <option value="Bush">Bush</option>
                                <option value="Scrub">Scrub</option>
                                <option value="Heath">Heath</option>
                                <option value="Moor">Moor</option>
                                <option value="Bog">Bog</option>
                                <option value="Fen">Fen</option>
                                <option value="Marsh">Marsh</option>
                                <option value="Swamp">Swamp</option>
                                <option value="Wetland">Wetland</option>
                                <option value="Riparian">Riparian</option>
                                <option value="Floodplain">Floodplain</option>
                                <option value="Delta">Delta</option>
                                <option value="Estuary">Estuary</option>
                                <option value="Lagoon">Lagoon</option>
                                <option value="Bayou">Bayou</option>
                                <option value="Slough">Slough</option>
                                <option value="Oxbow">Oxbow</option>
                                <option value="Meander">Meander</option>
                                <option value="Channel">Channel</option>
                                <option value="Creek">Creek</option>
                                <option value="Stream">Stream</option>
                                <option value="River">River</option>
                                <option value="Lake">Lake</option>
                                <option value="Pond">Pond</option>
                                <option value="Pool">Pool</option>
                                <option value="Reservoir">Reservoir</option>
                                <option value="Dam">Dam</option>
                                <option value="Canal">Canal</option>
                                <option value="Ditch">Ditch</option>
                                <option value="Aqueduct">Aqueduct</option>
                                <option value="Pipeline">Pipeline</option>
                                <option value="Well">Well</option>
                                <option value="Spring">Spring</option>
                                <option value="Geyser">Geyser</option>
                                <option value="Hot Spring">Hot Spring</option>
                                <option value="Mineral Spring">Mineral Spring</option>
                                <option value="Artesian Well">Artesian Well</option>
                                <option value="Groundwater">Groundwater</option>
                                <option value="Aquifer">Aquifer</option>
                                <option value="Water Table">Water Table</option>
                                <option value="Water Level">Water Level</option>
                                <option value="Water Quality">Water Quality</option>
                                <option value="Water Quantity">Water Quantity</option>
                                <option value="Water Supply">Water Supply</option>
                                <option value="Water Demand">Water Demand</option>
                                <option value="Water Use">Water Use</option>
                                <option value="Water Conservation">Water Conservation</option>
                                <option value="Water Management">Water Management</option>
                                <option value="Water Policy">Water Policy</option>
                                <option value="Water Law">Water Law</option>
                                <option value="Water Rights">Water Rights</option>
                                <option value="Water Allocation">Water Allocation</option>
                                <option value="Water Distribution">Water Distribution</option>
                                <option value="Water Treatment">Water Treatment</option>
                                <option value="Water Purification">Water Purification</option>
                                <option value="Water Filtration">Water Filtration</option>
                                <option value="Water Disinfection">Water Disinfection</option>
                                <option value="Water Chlorination">Water Chlorination</option>
                                <option value="Water Fluoridation">Water Fluoridation</option>
                                <option value="Water Softening">Water Softening</option>
                                <option value="Water Hardening">Water Hardening</option>
                                <option value="Water pH">Water pH</option>
                                <option value="Water Alkalinity">Water Alkalinity</option>
                                <option value="Water Acidity">Water Acidity</option>
                                <option value="Water Salinity">Water Salinity</option>
                                <option value="Water Conductivity">Water Conductivity</option>
                                <option value="Water Turbidity">Water Turbidity</option>
                                <option value="Water Clarity">Water Clarity</option>
                                <option value="Water Color">Water Color</option>
                                <option value="Water Odor">Water Odor</option>
                                <option value="Water Taste">Water Taste</option>
                                <option value="Water Temperature">Water Temperature</option>
                                <option value="Water Pressure">Water Pressure</option>
                                <option value="Water Flow">Water Flow</option>
                                <option value="Water Velocity">Water Velocity</option>
                                <option value="Water Volume">Water Volume</option>
                                <option value="Water Mass">Water Mass</option>
                                <option value="Water Weight">Water Weight</option>
                                <option value="Water Density">Water Density</option>
                                <option value="Water Viscosity">Water Viscosity</option>
                                <option value="Water Surface Tension">Water Surface Tension</option>
                                <option value="Water Capillary Action">Water Capillary Action</option>
                                <option value="Water Adhesion">Water Adhesion</option>
                                <option value="Water Cohesion">Water Cohesion</option>
                                <option value="Water Solubility">Water Solubility</option>
                                <option value="Water Dissolution">Water Dissolution</option>
                                <option value="Water Precipitation">Water Precipitation</option>
                                <option value="Water Condensation">Water Condensation</option>
                                <option value="Water Evaporation">Water Evaporation</option>
                                <option value="Water Transpiration">Water Transpiration</option>
                                <option value="Water Respiration">Water Respiration</option>
                                <option value="Water Photosynthesis">Water Photosynthesis</option>
                                <option value="Water Respiration">Water Respiration</option>
                                <option value="Water Metabolism">Water Metabolism</option>
                                <option value="Water Digestion">Water Digestion</option>
                                <option value="Water Absorption">Water Absorption</option>
                                <option value="Water Assimilation">Water Assimilation</option>
                                <option value="Water Excretion">Water Excretion</option>
                                <option value="Water Secretion">Water Secretion</option>
                                <option value="Water Reabsorption">Water Reabsorption</option>
                                <option value="Water Filtration">Water Filtration</option>
                                <option value="Water Reabsorption">Water Reabsorption</option>
                                <option value="Water Secretion">Water Secretion</option>
                                <option value="Water Excretion">Water Excretion</option>
                                <option value="Water Metabolism">Water Metabolism</option>
                                <option value="Water Respiration">Water Respiration</option>
                                <option value="Water Photosynthesis">Water Photosynthesis</option>
                                <option value="Water Transpiration">Water Transpiration</option>
                                <option value="Water Evaporation">Water Evaporation</option>
                                <option value="Water Condensation">Water Condensation</option>
                                <option value="Water Precipitation">Water Precipitation</option>
                                <option value="Water Dissolution">Water Dissolution</option>
                                <option value="Water Solubility">Water Solubility</option>
                                <option value="Water Cohesion">Water Cohesion</option>
                                <option value="Water Adhesion">Water Adhesion</option>
                                <option value="Water Capillary Action">Water Capillary Action</option>
                                <option value="Water Surface Tension">Water Surface Tension</option>
                                <option value="Water Viscosity">Water Viscosity</option>
                                <option value="Water Density">Water Density</option>
                                <option value="Water Weight">Water Weight</option>
                                <option value="Water Mass">Water Mass</option>
                                <option value="Water Volume">Water Volume</option>
                                <option value="Water Velocity">Water Velocity</option>
                                <option value="Water Flow">Water Flow</option>
                                <option value="Water Pressure">Water Pressure</option>
                                <option value="Water Temperature">Water Temperature</option>
                                <option value="Water Taste">Water Taste</option>
                                <option value="Water Odor">Water Odor</option>
                                <option value="Water Color">Water Color</option>
                                <option value="Water Clarity">Water Clarity</option>
                                <option value="Water Turbidity">Water Turbidity</option>
                                <option value="Water Conductivity">Water Conductivity</option>
                                <option value="Water Salinity">Water Salinity</option>
                                <option value="Water Acidity">Water Acidity</option>
                                <option value="Water Alkalinity">Water Alkalinity</option>
                                <option value="Water pH">Water pH</option>
                                <option value="Water Hardening">Water Hardening</option>
                                <option value="Water Softening">Water Softening</option>
                                <option value="Water Fluoridation">Water Fluoridation</option>
                                <option value="Water Chlorination">Water Chlorination</option>
                                <option value="Water Disinfection">Water Disinfection</option>
                                <option value="Water Filtration">Water Filtration</option>
                                <option value="Water Purification">Water Purification</option>
                                <option value="Water Treatment">Water Treatment</option>
                                <option value="Water Distribution">Water Distribution</option>
                                <option value="Water Allocation">Water Allocation</option>
                                <option value="Water Rights">Water Rights</option>
                                <option value="Water Law">Water Law</option>
                                <option value="Water Policy">Water Policy</option>
                                <option value="Water Management">Water Management</option>
                                <option value="Water Conservation">Water Conservation</option>
                                <option value="Water Use">Water Use</option>
                                <option value="Water Demand">Water Demand</option>
                                <option value="Water Supply">Water Supply</option>
                                <option value="Water Quantity">Water Quantity</option>
                                <option value="Water Quality">Water Quality</option>
                                <option value="Water Level">Water Level</option>
                                <option value="Water Table">Water Table</option>
                                <option value="Aquifer">Aquifer</option>
                                <option value="Groundwater">Groundwater</option>
                                <option value="Artesian Well">Artesian Well</option>
                                <option value="Mineral Spring">Mineral Spring</option>
                                <option value="Hot Spring">Hot Spring</option>
                                <option value="Geyser">Geyser</option>
                                <option value="Spring">Spring</option>
                                <option value="Well">Well</option>
                                <option value="Pipeline">Pipeline</option>
                                <option value="Aqueduct">Aqueduct</option>
                                <option value="Ditch">Ditch</option>
                                <option value="Canal">Canal</option>
                                <option value="Dam">Dam</option>
                                <option value="Reservoir">Reservoir</option>
                                <option value="Pool">Pool</option>
                                <option value="Pond">Pond</option>
                                <option value="Lake">Lake</option>
                                <option value="River">River</option>
                                <option value="Stream">Stream</option>
                                <option value="Creek">Creek</option>
                                <option value="Channel">Channel</option>
                                <option value="Meander">Meander</option>
                                <option value="Oxbow">Oxbow</option>
                                <option value="Slough">Slough</option>
                                <option value="Bayou">Bayou</option>
                                <option value="Lagoon">Lagoon</option>
                                <option value="Estuary">Estuary</option>
                                <option value="Delta">Delta</option>
                                <option value="Floodplain">Floodplain</option>
                                <option value="Riparian">Riparian</option>
                                <option value="Wetland">Wetland</option>
                                <option value="Swamp">Swamp</option>
                                <option value="Marsh">Marsh</option>
                                <option value="Fen">Fen</option>
                                <option value="Bog">Bog</option>
                                <option value="Moor">Moor</option>
                                <option value="Heath">Heath</option>
                                <option value="Scrub">Scrub</option>
                                <option value="Bush">Bush</option>
                                <option value="Outback">Outback</option>
                                <option value="Veld">Veld</option>
                                <option value="Pampas">Pampas</option>
                                <option value="Steppe">Steppe</option>
                                <option value="Savanna">Savanna</option>
                                <option value="Prairie">Prairie</option>
                                <option value="Meadow">Meadow</option>
                                <option value="Pasture">Pasture</option>
                                <option value="Forage">Forage</option>
                                <option value="Fodder">Fodder</option>
                                <option value="Silage">Silage</option>
                                <option value="Straw">Straw</option>
                                <option value="Hay">Hay</option>
                                <option value="Grass">Grass</option>
                                <option value="Sedge">Sedge</option>
                                <option value="Rush">Rush</option>
                                <option value="Reed">Reed</option>
                                <option value="Cane">Cane</option>
                                <option value="Rattan">Rattan</option>
                                <option value="Bamboo">Bamboo</option>
                                <option value="Kapok">Kapok</option>
                                <option value="Coir">Coir</option>
                                <option value="Abaca">Abaca</option>
                                <option value="Sisal">Sisal</option>
                                <option value="Ramie">Ramie</option>
                                <option value="Kenaf">Kenaf</option>
                                <option value="Flax">Flax</option>
                                <option value="Hemp">Hemp</option>
                                <option value="Betel Leaf">Betel Leaf</option>
                                <option value="Arecanut">Arecanut</option>
                                <option value="Jute">Jute</option>
                                <option value="Rubber">Rubber</option>
                                <option value="Cocoa">Cocoa</option>
                                <option value="Coffee">Coffee</option>
                                <option value="Tea">Tea</option>
                                <option value="Tobacco">Tobacco</option>
                                <option value="Sugarcane">Sugarcane</option>
                                <option value="Cotton">🧶 Cotton</option>
                            </optgroup>
                        </select>
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#43a047; font-size:1.2rem;"><i class="fas fa-seedling"></i></span>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 18px;">
                    <label style="font-weight:600; color:#2e7d32; margin-bottom:6px; display:block;"><i class="fas fa-leaf" style="margin-right:7px; color:#8bc34a;"></i>Crop Variety <span style="font-weight:400; color:#888;">(Optional)</span></label>
                    <input type="text" name="variety" class="form-control" placeholder="e.g., Hybrid, Local, Organic" style="width:100%; padding:12px 16px; border-radius:9px; border:1.5px solid #c5e1a5; font-size:1.05rem; background:#fff; outline:none; transition: border 0.2s;">
                </div>
                <div class="form-group" style="margin-bottom: 22px;">
                    <label style="font-weight:600; color:#2e7d32; margin-bottom:6px; display:block;"><i class="fas fa-calendar-alt" style="margin-right:7px; color:#43a047;"></i>Sowing Date</label>
                    <div style="position:relative;">
                        <input type="date" name="sow_date" class="form-control" required style="width:100%; padding:12px 40px 12px 16px; border-radius:9px; border:1.5px solid #c5e1a5; font-size:1.05rem; background:#fff; outline:none; transition: border 0.2s;">
                        <span style="position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#43a047; font-size:1.2rem;"><i class="fas fa-calendar-alt"></i></span>
                    </div>
                </div>
                <button type="submit" class="btn btn-success add-crop-btn" style="width:100%; padding:14px 0; font-size:1.13rem; font-weight:700; border-radius:9px; background:linear-gradient(90deg,#43a047 70%,#388e3c 100%); box-shadow:0 2px 8px rgba(67,160,71,0.10); margin-top:8px;">
                    <i class="fas fa-seedling"></i> Add Crop
                </button>
            </form>
        </div>

        <!-- My Crops -->
        <div class="card">
            <h3><i class="fas fa-seedling"></i> My Crops - AI Monitoring</h3>
            <?php if (mysqli_num_rows($crops_result) > 0): ?>
                <?php 
                // Fetch all disease reports and group by crop_id
                $disease_reports_by_crop = [];
                $disease_query = mysqli_query($conn, "SELECT * FROM crop_disease_reports WHERE user_id = $user_id ORDER BY created_at DESC");
                if ($disease_query) {
                    while ($report = mysqli_fetch_assoc($disease_query)) {
                        $disease_reports_by_crop[$report['crop_id']][] = $report;
                    }
                }
                ?>
                <?php while ($crop = mysqli_fetch_assoc($crops_result)): 
                    $growth_data = calculateGrowthStage($crop['sow_date'], $crop['crop_name']);
                ?>
                    <div class="crop-item">
                        <h4><?php echo htmlspecialchars($crop['crop_name']); ?> 
                            <?php if ($crop['variety']): ?>
                                <small>(<?php echo htmlspecialchars($crop['variety']); ?>)</small>
                            <?php endif; ?>
                        </h4>
                        <p><strong>Sowed:</strong> <?php echo date('M d, Y', strtotime($crop['sow_date'])); ?></p>
                        <p><strong>Expected Harvest:</strong> <?php echo date('M d, Y', strtotime($crop['expected_harvest_date'])); ?></p>
                        <p><strong>Current Stage:</strong> <?php echo $growth_data['stage']; ?></p>
                        <p><strong>Growth Progress:</strong> <?php echo round($growth_data['percentage']); ?>%</p>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $growth_data['percentage']; ?>%"></div>
                        </div>
                        
                        <div class="crop-actions">
                            <button class="btn btn-primary" onclick="openAnalysisModal(<?php echo $crop['id']; ?>, '<?php echo htmlspecialchars($crop['crop_name']); ?>')">
                                <i class="fas fa-camera"></i> AI Analysis
                            </button>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this crop and all its analyses? This action cannot be undone.');">
                                <input type="hidden" name="delete_crop" value="1">
                                <input type="hidden" name="crop_id" value="<?php echo $crop['id']; ?>">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash"></i> Delete Crop
                                </button>
                            </form>
                        </div>

                        <!-- Disease Reports for this crop -->
                        <?php if (!empty($disease_reports_by_crop[$crop['id']])): ?>
                            <div class="disease-reports-group">
                                <h5 style="margin-top:18px; color:#2e7d32;">AI Disease Detection Reports</h5>
                                <?php foreach ($disease_reports_by_crop[$crop['id']] as $report): ?>
                                    <div class="disease-report <?php echo strtolower($report['severity']); ?>">
                                        <h4>🔍 AI Analysis Result</h4>
                                        <p><strong>Detected Disease:</strong> <?php echo htmlspecialchars($report['detected_disease']); ?></p>
                                        <p><strong>Confidence Score:</strong> <?php echo $report['confidence_score']; ?>%</p>
                                        <p><strong>Severity:</strong> <?php echo $report['severity']; ?></p>
                                        <p><strong>Date:</strong> <?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?></p>
                                        <?php if ($report['image_path']): ?>
                                            <p><strong>Image:</strong> <a href="<?php echo htmlspecialchars($report['image_path']); ?>" target="_blank">View Analysis Image</a></p>
                                        <?php endif; ?>
                                        <div class="ai-analysis">
                                            <strong>🔬 Symptoms Detected:</strong><br>
                                            <?php echo htmlspecialchars($report['symptoms']); ?>
                                        </div>
                                        <div class="treatment-plan">
                                            <strong>💊 Treatment Plan:</strong><br>
                                            <?php echo htmlspecialchars($report['treatment_plan']); ?>
                                        </div>
                                        <div class="ai-analysis">
                                            <strong>🌱 Fertilizer Recommendation:</strong><br>
                                            <?php echo htmlspecialchars($report['fertilizer_recommendation']); ?>
                                        </div>
                                        <div class="action-buttons">
                                            <a href="disease_certificate.php?id=<?php echo $report['id']; ?>" class="btn btn-success" target="_blank">
                                                <i class="fas fa-certificate"></i> View Certificate
                                            </a>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this analysis? This action cannot be undone.');">
                                                <input type="hidden" name="delete_analysis" value="1">
                                                <input type="hidden" name="analysis_id" value="<?php echo $report['id']; ?>">
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Delete Analysis
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No crops added yet. Add your first crop above to start AI monitoring!</p>
            <?php endif; ?>
        </div>

        <!-- Disease Reports -->
        <!-- (This section is now removed, as reports are shown under each crop) -->
    </div>

    <!-- AI Analysis Modal -->
    <div id="analysisModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>🤖 AI Crop Analysis</h3>
                <span class="close" onclick="closeModal('analysisModal')">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="analyze_image">
                <input type="hidden" name="crop_id" id="analysis_crop_id">
                <input type="hidden" name="crop_name" id="analysis_crop_name">
                
                <div class="form-group">
                    <label>📸 Upload Crop Image for AI Analysis:</label>
                    <input type="file" name="crop_image" class="form-control" accept="image/*" required>
                    <small>Take a clear photo of the plant/leaves for disease detection</small>
                </div>
                
                <div class="ai-analysis">
                    <strong>🤖 What AI will analyze:</strong>
                    <ul>
                        <li>🌿 Plant health status</li>
                        <li>🦠 Disease identification</li>
                        <li>📊 Growth stage assessment</li>
                        <li>💊 Treatment recommendations</li>
                        <li>🌱 Fertilizer suggestions</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-brain"></i> Start AI Analysis
                </button>
            </form>
        </div>
    </div>

    <script>
        function openAnalysisModal(cropId, cropName) {
            document.getElementById('analysis_crop_id').value = cropId;
            document.getElementById('analysis_crop_name').value = cropName;
            document.getElementById('analysisModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        function viewCropDetails(cropId) {
            // Implement detailed crop view
            alert('Detailed crop view coming soon!');
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
    </script>
</body>
</html> 