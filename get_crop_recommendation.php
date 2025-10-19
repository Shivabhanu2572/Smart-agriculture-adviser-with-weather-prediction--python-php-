<?php
header('Content-Type: application/json');

$conn = mysqli_connect("localhost", "root", "", "smart_agri");
if (!$conn) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$soil_type = trim(strtolower($_POST['soil_type'] ?? ''));
$ph = isset($_POST['ph']) ? floatval($_POST['ph']) : null;
$moisture = isset($_POST['moisture']) ? floatval($_POST['moisture']) : null;

$exact_matches = [];
$near_matches = [];

// Debug log
file_put_contents('debug_crop.txt', print_r([
  'soil_type' => $soil_type,
  'ph' => $ph,
  'moisture' => $moisture
], true), FILE_APPEND);

if ($soil_type && $ph !== null && $moisture !== null) {
    // Exact match query
    $sql = "SELECT * FROM crop_recommendation_master
            WHERE LOWER(TRIM(soil_type)) = '$soil_type'
            AND ph_min <= $ph AND ph_max >= $ph
            AND moisture_min <= $moisture AND moisture_max >= $moisture";
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $exact_matches[] = $row;
    }

    // Near match query (±1.0 pH or ±20% moisture, but not already in exact)
    $sql2 = "SELECT * FROM crop_recommendation_master
             WHERE LOWER(TRIM(soil_type)) = '$soil_type'
             AND (
                 (ph_min <= ($ph + 1.0) AND ph_max >= ($ph - 1.0))
                 OR (moisture_min <= ($moisture + 20) AND moisture_max >= ($moisture - 20))
             )";
    $res2 = mysqli_query($conn, $sql2);
    while ($row = mysqli_fetch_assoc($res2)) {
        // Avoid duplicates
        $found = false;
        foreach ($exact_matches as $ex) {
            if ($ex['id'] == $row['id']) { $found = true; break; }
        }
        if (!$found) $near_matches[] = $row;
    }

    // If less than 3 exact matches, fill up with near matches
    if (count($exact_matches) < 3) {
        $needed = 3 - count($exact_matches);
        $to_add = array_slice($near_matches, 0, $needed);
        $exact_matches = array_merge($exact_matches, $to_add);
        $near_matches = array_slice($near_matches, $needed); // Remove those added to exact
    }

    // Only return relevant fields
    $exact_matches = array_map(function($row) {
        return [
            'crop_name' => $row['crop_name'],
            'variety_name' => $row['variety_name'],
            'scientific_note' => $row['scientific_note'],
            'ph_min' => $row['ph_min'],
            'ph_max' => $row['ph_max'],
            'moisture_min' => $row['moisture_min'],
            'moisture_max' => $row['moisture_max']
        ];
    }, $exact_matches);
    $near_matches = array_map(function($row) {
        return [
            'crop_name' => $row['crop_name'],
            'variety_name' => $row['variety_name'],
            'scientific_note' => $row['scientific_note'],
            'ph_min' => $row['ph_min'],
            'ph_max' => $row['ph_max'],
            'moisture_min' => $row['moisture_min'],
            'moisture_max' => $row['moisture_max']
        ];
    }, $near_matches);
}

// If no pH/moisture provided, show all crops for the soil type
$all_soil_type_crops = [];
if ($soil_type && ($ph === null || $moisture === null)) {
    $sql = "SELECT * FROM crop_recommendation_master WHERE LOWER(TRIM(soil_type)) = '$soil_type'";
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $all_soil_type_crops[] = [
            'crop_name' => $row['crop_name'],
            'variety_name' => $row['variety_name'],
            'scientific_note' => $row['scientific_note'],
            'ph_min' => $row['ph_min'],
            'ph_max' => $row['ph_max'],
            'moisture_min' => $row['moisture_min'],
            'moisture_max' => $row['moisture_max']
        ];
    }
}

echo json_encode([
    'exact_matches' => $exact_matches,
    'near_matches' => $near_matches,
    'all_soil_type_crops' => $all_soil_type_crops
]);
exit; 