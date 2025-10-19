<?php
// Show available districts in the database
header('Content-Type: text/html; charset=utf-8');

echo "<h1>Available Districts in Database</h1>";

// Database connection
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

if (!$conn) {
    echo "<p style='color: red;'>❌ Database connection failed</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connected successfully</p>";

// Get all unique districts
$result = mysqli_query($conn, "SELECT DISTINCT district FROM district_crop_recommendation ORDER BY district");

if (!$result) {
    echo "<p style='color: red;'>❌ Error querying districts</p>";
    exit;
}

$districts = [];
while ($row = mysqli_fetch_assoc($result)) {
    $districts[] = $row['district'];
}

echo "<h3>Districts with Crop Recommendations Available:</h3>";
echo "<div style='display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; margin: 20px 0;'>";

foreach ($districts as $district) {
    // Count recommendations for this district
    $countResult = mysqli_query($conn, "SELECT COUNT(*) as count FROM district_crop_recommendation WHERE district = '$district'");
    $count = mysqli_fetch_assoc($countResult)['count'];
    
    echo "<div style='border: 1px solid #ddd; padding: 10px; border-radius: 5px; background: #f9f9f9;'>";
    echo "<strong>{$district}</strong><br>";
    echo "<small style='color: #666;'>{$count} month-wise recommendations</small>";
    echo "</div>";
}

echo "</div>";

echo "<h3>Current Location Test:</h3>";
$locationJson = @file_get_contents("https://ipapi.co/json/");
if ($locationJson !== false) {
    $location = json_decode($locationJson, true);
    $detectedCity = $location['city'] ?? 'Unknown';
    
    echo "<p><strong>Your detected location:</strong> $detectedCity</p>";
    
    if (in_array($detectedCity, $districts)) {
        echo "<p style='color: green;'>✅ $detectedCity is available in our database!</p>";
        
        // Show sample recommendations for this district
        $sampleResult = mysqli_query($conn, "SELECT * FROM district_crop_recommendation WHERE district = '$detectedCity' LIMIT 3");
        echo "<h4>Sample recommendations for $detectedCity:</h4>";
        echo "<ul>";
        while ($row = mysqli_fetch_assoc($sampleResult)) {
            echo "<li><strong>{$row['month']}:</strong> {$row['crop1']}, {$row['crop2']}, {$row['crop3']}</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ $detectedCity is not in our database. The system will show general seasonal recommendations.</p>";
        echo "<p>Available districts for your region: " . implode(', ', array_slice($districts, 0, 5)) . "...</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Could not detect your location</p>";
}

mysqli_close($conn);
?> 