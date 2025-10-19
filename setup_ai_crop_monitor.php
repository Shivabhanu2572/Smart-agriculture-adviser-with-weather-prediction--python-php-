<?php
// Setup script for AI Crop Monitoring System
// Run this file once to initialize the database

$conn = mysqli_connect("localhost", "root", "", "smart_agri");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "<h2>🤖 Setting up AI Crop Monitoring System...</h2>";

// Create AI crop monitoring table
$create_ai_crop_table = "CREATE TABLE IF NOT EXISTS ai_crop_monitoring (
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

if (mysqli_query($conn, $create_ai_crop_table)) {
    echo "✅ AI crop monitoring table created successfully<br>";
} else {
    echo "❌ Error creating AI crop monitoring table: " . mysqli_error($conn) . "<br>";
}

// Create disease reports table
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

if (mysqli_query($conn, $create_disease_table)) {
    echo "✅ Disease reports table created successfully<br>";
} else {
    echo "❌ Error creating disease reports table: " . mysqli_error($conn) . "<br>";
}

// Import crop database
echo "<h3>📊 Importing crop database...</h3>";

// Read and execute the crop database SQL
$sql_file = 'crop_database.sql';
if (file_exists($sql_file)) {
    $sql_content = file_get_contents($sql_file);
    $queries = explode(';', $sql_content);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            if (mysqli_query($conn, $query)) {
                echo "✅ Executed: " . substr($query, 0, 50) . "...<br>";
            } else {
                echo "❌ Error: " . mysqli_error($conn) . "<br>";
            }
        }
    }
} else {
    echo "❌ Crop database file not found<br>";
}

// Create upload directories
$directories = [
    'uploads/crop_analysis',
    'uploads/crop_images',
    'uploads/crop_problems'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0777, true)) {
            echo "✅ Created directory: $dir<br>";
        } else {
            echo "❌ Failed to create directory: $dir<br>";
        }
    } else {
        echo "✅ Directory exists: $dir<br>";
    }
}

echo "<h3>🎉 AI Crop Monitoring System Setup Complete!</h3>";
echo "<p>You can now:</p>";
echo "<ul>";
echo "<li>🌱 Add crops for AI monitoring</li>";
echo "<li>📸 Upload images for disease detection</li>";
echo "<li>🔍 Get AI-powered analysis reports</li>";
echo "<li>💊 Receive treatment recommendations</li>";
echo "<li>🌱 Get fertilizer suggestions</li>";
echo "</ul>";

echo "<p><a href='ai_crop_monitor.php' style='background: #33691e; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Start Using AI Monitor</a></p>";

mysqli_close($conn);
?> 