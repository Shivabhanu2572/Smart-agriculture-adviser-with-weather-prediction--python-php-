<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['district'])) {
    http_response_code(400);
    echo json_encode(['error' => 'District parameter is required']);
    exit;
}

$district = trim($input['district']);
$month = isset($input['month']) ? trim($input['month']) : date('F');

// Database connection
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Clean the district name for better matching
$district = mysqli_real_escape_string($conn, $district);

// Get current month and determine season
$currentMonth = date('n'); // 1-12
$currentMonthName = date('F'); // January, February, etc.

// Define seasons
$season = '';
if ($currentMonth >= 3 && $currentMonth <= 5) {
    $season = 'Summer';
} elseif ($currentMonth >= 6 && $currentMonth <= 9) {
    $season = 'Monsoon';
} elseif ($currentMonth >= 10 && $currentMonth <= 11) {
    $season = 'Post-Monsoon';
} else {
    $season = 'Winter';
}

$recommendations = [];

// Strategy 1: Try to find exact district and current month match
$sql = "SELECT * FROM district_crop_recommendation WHERE month = '$currentMonthName'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed']);
    exit;
}

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        if (strcasecmp(trim($row['district']), $district) === 0) {
            $recommendations[] = [
                'crop1' => $row['crop1'],
                'crop2' => $row['crop2'],
                'crop3' => $row['crop3'],
                'summary' => $row['summary'],
                'district' => $row['district'],
                'month' => $row['month']
            ];
        }
    }
} else {
    // Strategy 2: Try to find district and current season match
    $sql = "SELECT * FROM district_crop_recommendation WHERE month LIKE '%$season%'";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if (strcasecmp(trim($row['district']), $district) === 0) {
                $recommendations[] = [
                    'crop1' => $row['crop1'],
                    'crop2' => $row['crop2'],
                    'crop3' => $row['crop3'],
                    'summary' => $row['summary'] . ' (Seasonal recommendation for ' . $season . ')',
                    'district' => $row['district'],
                    'month' => $row['month']
                ];
            }
        }
    } else {
            // Strategy 3: Try to find any recommendations for this district (any month)
    $sql = "SELECT * FROM district_crop_recommendation WHERE district = '$district' LIMIT 3";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                if (strcasecmp(trim($row['district']), $district) === 0) {
                    $recommendations[] = [
                        'crop1' => $row['crop1'],
                        'crop2' => $row['crop2'],
                        'crop3' => $row['crop3'],
                        'summary' => $row['summary'] . ' (General recommendation for ' . $row['district'] . ')',
                        'district' => $row['district'],
                        'month' => $row['month']
                    ];
                }
            }
        } else {
            // Strategy 4: Try to find recommendations for similar districts or regions
            // Remove common suffixes and try partial matching
            $districtClean = preg_replace('/\b(district|city|town|village)\b/i', '', $district);
            $districtClean = trim($districtClean);
            
            if (!empty($districtClean) && $districtClean !== $district) {
                $sql = "SELECT * FROM district_crop_recommendation WHERE district LIKE '%$districtClean%' LIMIT 3";
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $recommendations[] = [
                            'crop1' => $row['crop1'],
                            'crop2' => $row['crop2'],
                            'crop3' => $row['crop3'],
                            'summary' => $row['summary'] . ' (Similar region recommendation)',
                            'district' => $row['district'],
                            'month' => $row['month']
                        ];
                    }
                }
            }
            
            // Strategy 5: Try to find any recommendations for current month from any district
            if (empty($recommendations)) {
                $sql = "SELECT * FROM district_crop_recommendation WHERE month = '$currentMonthName' LIMIT 3";
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $recommendations[] = [
                            'crop1' => $row['crop1'],
                            'crop2' => $row['crop2'],
                            'crop3' => $row['crop3'],
                            'summary' => $row['summary'] . ' (General recommendation for ' . $currentMonthName . ' from ' . $row['district'] . ' region)',
                            'district' => $row['district'],
                            'month' => $row['month']
                        ];
                    }
                }
            }
            
            // Strategy 6: If still no results, provide season-based fallback recommendations
            if (empty($recommendations)) {
                $fallbackRecommendations = [
                    'Summer' => [
                        'crop1' => 'Rice',
                        'crop2' => 'Maize',
                        'crop3' => 'Pulses',
                        'summary' => 'Summer crops suitable for warm weather. Consider rice, maize, and pulses for this season in ' . $district . ' region.'
                    ],
                    'Monsoon' => [
                        'crop1' => 'Rice',
                        'crop2' => 'Cotton',
                        'crop3' => 'Sugarcane',
                        'summary' => 'Monsoon season is ideal for rice, cotton, and sugarcane cultivation in ' . $district . ' region.'
                    ],
                    'Post-Monsoon' => [
                        'crop1' => 'Wheat',
                        'crop2' => 'Mustard',
                        'crop3' => 'Peas',
                        'summary' => 'Post-monsoon is perfect for wheat, mustard, and peas cultivation in ' . $district . ' region.'
                    ],
                    'Winter' => [
                        'crop1' => 'Wheat',
                        'crop2' => 'Barley',
                        'crop3' => 'Potatoes',
                        'summary' => 'Winter season is suitable for wheat, barley, and potato cultivation in ' . $district . ' region.'
                    ]
                ];
                
                if (isset($fallbackRecommendations[$season])) {
                    $recommendations[] = array_merge($fallbackRecommendations[$season], [
                        'district' => $district,
                        'month' => $season
                    ]);
                }
            }
        }
    }
}

mysqli_close($conn);

// Return the recommendations
echo json_encode([
    'success' => true,
    'district' => $district,
    'current_month' => $currentMonthName,
    'current_season' => $season,
    'recommendations' => $recommendations,
    'count' => count($recommendations),
    'query_info' => [
        'original_district' => $district,
        'search_month' => $currentMonthName,
        'search_season' => $season
    ]
]);
?> 