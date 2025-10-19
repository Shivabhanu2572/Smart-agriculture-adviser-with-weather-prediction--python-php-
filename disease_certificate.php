<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

// Include AI Image Analyzer
require_once 'ai_image_analyzer.php';

$analyzer = new AIImageAnalyzer($conn);

// Get disease report ID from URL
$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($report_id > 0) {
    // Get disease report details
    $sql = "SELECT dr.*, cm.crop_name, cm.variety, cm.sow_date 
            FROM crop_disease_reports dr 
            JOIN ai_crop_monitoring cm ON dr.crop_id = cm.id 
            WHERE dr.id = $report_id AND dr.user_id = $user_id";
    $result = mysqli_query($conn, $sql);
    
    if (mysqli_num_rows($result) > 0) {
        $report = mysqli_fetch_assoc($result);
        
        // Get crop-specific information
        $crop_diseases = $analyzer->getCropDiseases($report['crop_name']);
        $fertilizer_info = $analyzer->getFertilizerRecommendation($report['crop_name'], 'Current Stage');
        $growth_percentage = $analyzer->calculateGrowthPercentage($report['sow_date'], $report['crop_name']);
    } else {
        header("Location: ai_crop_monitor.php");
        exit();
    }
} else {
    header("Location: ai_crop_monitor.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disease Certificate - <?php echo htmlspecialchars($report['crop_name']); ?> | Smart Agriculture</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(120deg, #e8f5e9 0%, #f1f8e9 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #33691e 0%, #558b2f 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 20px;
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
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
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
        .certificate-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .certificate {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .certificate-header {
            text-align: center;
            border-bottom: 3px solid #33691e;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .certificate-header h2 {
            color: #33691e;
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .certificate-header .subtitle {
            color: #666;
            font-size: 1.1rem;
        }
        .section {
            margin-bottom: 30px;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #33691e;
            background: #f8f9fa;
        }
        .section h3 {
            color: #33691e;
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .section h3 i {
            margin-right: 10px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }
        .info-item strong {
            color: #33691e;
            display: block;
            margin-bottom: 5px;
        }
        .severity-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        .severity-low { background: #d4edda; color: #155724; }
        .severity-medium { background: #fff3cd; color: #856404; }
        .severity-high { background: #f8d7da; color: #721c24; }
        .severity-critical { background: #f5c6cb; color: #721c24; }
        .confidence-bar {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin-top: 5px;
        }
        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
            transition: width 0.3s ease;
        }
        .treatment-plan {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #2196f3;
        }
        .treatment-plan h4 {
            color: #1976d2;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .treatment-steps {
            list-style: none;
            padding: 0;
        }
        .treatment-steps li {
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            position: relative;
            padding-left: 25px;
        }
        .treatment-steps li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }
        .fertilizer-info {
            background: #f3e5f5;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #9c27b0;
        }
        .fertilizer-info h4 {
            color: #7b1fa2;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .monitoring-schedule {
            background: #fff3e0;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            border-left: 4px solid #ff9800;
        }
        .monitoring-schedule h4 {
            color: #f57c00;
            margin-bottom: 10px;
            font-weight: 600;
        }
        .crop-image {
            text-align: center;
            margin: 20px 0;
        }
        .crop-image img {
            max-width: 100%;
            max-height: 400px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .btn {
            padding: 12px 24px;
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
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        @media print {
            .nav-bar, .print-btn { display: none; }
            body { background: white; }
            .certificate { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="nav-bar">
        <a href="dashboard.php">🏠 Back to Dashboard</a>
    </div>

    <button class="btn btn-primary print-btn" onclick="window.print()">
        <i class="fas fa-print"></i> Print Certificate
    </button>

    <div class="certificate-container">
        <div class="header">
            <h1>🏥 Crop Disease Analysis Certificate</h1>
            <p>Comprehensive AI-powered crop health assessment and treatment plan</p>
        </div>

        <div class="certificate">
            <div class="certificate-header">
                <h2>🌾 <?php echo htmlspecialchars($report['crop_name']); ?> Disease Analysis Report</h2>
                <div class="subtitle">
                    Certificate ID: #<?php echo str_pad($report_id, 6, '0', STR_PAD_LEFT); ?> | 
                    Date: <?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?>
                </div>
            </div>

            <!-- Crop Image -->
            <?php if ($report['image_path']): ?>
            <div class="crop-image">
                <img src="<?php echo htmlspecialchars($report['image_path']); ?>" alt="Crop Analysis Image">
                <p><em>AI Analysis Image - <?php echo htmlspecialchars($report['crop_name']); ?></em></p>
            </div>
            <?php endif; ?>

            <!-- Basic Information -->
            <div class="section">
                <h3><i class="fas fa-info-circle"></i> Basic Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Crop Name:</strong>
                        <?php echo htmlspecialchars($report['crop_name']); ?>
                        <?php if ($report['variety']): ?>
                            (<?php echo htmlspecialchars($report['variety']); ?>)
                        <?php endif; ?>
                    </div>
                    <div class="info-item">
                        <strong>Sowing Date:</strong>
                        <?php echo date('M d, Y', strtotime($report['sow_date'])); ?>
                    </div>
                    <div class="info-item">
                        <strong>Growth Progress:</strong>
                        <?php echo $growth_percentage; ?>% Complete
                    </div>
                    <div class="info-item">
                        <strong>Analysis Date:</strong>
                        <?php echo date('M d, Y H:i', strtotime($report['created_at'])); ?>
                    </div>
                </div>
            </div>

            <!-- Disease Analysis -->
            <div class="section">
                <h3><i class="fas fa-bug"></i> Disease Analysis Results</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <strong>Detected Disease:</strong>
                        <?php echo htmlspecialchars($report['detected_disease']); ?>
                    </div>
                    <div class="info-item">
                        <strong>Severity Level:</strong>
                        <span class="severity-badge severity-<?php echo strtolower($report['severity']); ?>">
                            <?php echo $report['severity']; ?>
                        </span>
                    </div>
                    <div class="info-item">
                        <strong>AI Confidence Score:</strong>
                        <?php echo $report['confidence_score']; ?>%
                        <div class="confidence-bar">
                            <div class="confidence-fill" style="width: <?php echo $report['confidence_score']; ?>%"></div>
                        </div>
                    </div>
                    <div class="info-item">
                        <strong>Current Status:</strong>
                        <?php echo ucfirst($report['status']); ?>
                    </div>
                </div>

                <div class="treatment-plan">
                    <h4>🔍 Symptoms Detected:</h4>
                    <p><?php echo htmlspecialchars($report['symptoms']); ?></p>
                </div>
            </div>

            <!-- Treatment Plan -->
            <div class="section">
                <h3><i class="fas fa-pills"></i> Treatment Plan</h3>
                <div class="treatment-plan">
                    <h4>💊 Recommended Treatment:</h4>
                    <ol class="treatment-steps">
                        <?php 
                        $steps = explode('.', $report['treatment_plan']);
                        foreach ($steps as $step) {
                            $step = trim($step);
                            if (!empty($step)) {
                                echo "<li>" . htmlspecialchars($step) . "</li>";
                            }
                        }
                        ?>
                    </ol>
                </div>

                <div class="fertilizer-info">
                    <h4>🌱 Fertilizer Recommendation:</h4>
                    <p><strong>Recommended:</strong> <?php echo htmlspecialchars($report['fertilizer_recommendation']); ?></p>
                    <?php if ($fertilizer_info): ?>
                    <p><strong>Application Method:</strong> <?php echo htmlspecialchars($fertilizer_info['application_method']); ?></p>
                    <p><strong>Timing:</strong> <?php echo htmlspecialchars($fertilizer_info['timing']); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Monitoring Schedule -->
            <div class="section">
                <h3><i class="fas fa-chart-line"></i> Monitoring & Care Schedule</h3>
                <div class="monitoring-schedule">
                    <h4>📅 Recommended Monitoring Schedule:</h4>
                    <?php
                    $schedule = '';
                    if ($report['severity'] == 'Critical') {
                        $schedule = 'Daily monitoring for first week, then every 3 days';
                    } elseif ($report['severity'] == 'High') {
                        $schedule = 'Every 3 days for first week, then weekly';
                    } else {
                        $schedule = 'Weekly monitoring with detailed assessment every 2 weeks';
                    }
                    ?>
                    <p><strong>Frequency:</strong> <?php echo $schedule; ?></p>
                    <p><strong>Key Monitoring Points:</strong></p>
                    <ul>
                        <li>Check for disease spread to other plants</li>
                        <li>Monitor treatment effectiveness</li>
                        <li>Observe new growth patterns</li>
                        <li>Assess overall plant health</li>
                    </ul>
                </div>
            </div>

            <!-- Preventive Measures -->
            <div class="section">
                <h3><i class="fas fa-shield-alt"></i> Preventive Measures</h3>
                <div class="treatment-plan">
                    <h4>🛡️ Long-term Prevention:</h4>
                    <ul class="treatment-steps">
                        <li>Crop rotation to prevent disease buildup</li>
                        <li>Proper spacing for air circulation</li>
                        <li>Balanced fertilization</li>
                        <li>Regular monitoring and early detection</li>
                        <li>Use of disease-resistant varieties</li>
                        <li>Clean farming practices</li>
                    </ul>
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="text-align: center; margin-top: 30px;">
                <a href="ai_crop_monitor.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Back to AI Monitor
                </a>
                <a href="update_status.php?id=<?php echo $report_id; ?>" class="btn btn-success">
                    <i class="fas fa-edit"></i> Update Status
                </a>
                <button class="btn btn-warning" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Certificate
                </button>
            </div>
        </div>
    </div>

    <script>
        // Auto-refresh confidence bar animation
        document.addEventListener('DOMContentLoaded', function() {
            const confidenceFill = document.querySelector('.confidence-fill');
            if (confidenceFill) {
                setTimeout(() => {
                    confidenceFill.style.width = confidenceFill.style.width;
                }, 100);
            }
        });
    </script>
</body>
</html> 