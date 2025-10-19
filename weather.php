<?php
header("Content-Type: application/json");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: 0");

$apiKey = "7b46741e515713880330945106c0d3d8";

// Bangalore default
$lat = $_GET['lat'] ?? 12.9716;
$lon = $_GET['lon'] ?? 77.5946;

// 1. Current Weather API
$currentUrl = "https://api.openweathermap.org/data/2.5/weather?lat=$lat&lon=$lon&units=metric&appid=$apiKey";
$currentData = json_decode(file_get_contents($currentUrl), true);

// 2. 5 Day / 3 Hour Forecast
$forecastUrl = "https://api.openweathermap.org/data/2.5/forecast?lat=$lat&lon=$lon&units=metric&appid=$apiKey";
$forecastData = json_decode(file_get_contents($forecastUrl), true);

// 3. One Call API for alerts (if available)
$oneCallUrl = "https://api.openweathermap.org/data/2.5/onecall?lat=$lat&lon=$lon&appid=$apiKey&units=metric";
$oneCallData = json_decode(@file_get_contents($oneCallUrl), true);

// Default alert message
$alertMsg = "No weather alerts from free API";
if (isset($oneCallData['alerts']) && count($oneCallData['alerts']) > 0) {
    $firstAlert = $oneCallData['alerts'][0];
    $alertMsg = "<strong>" . htmlspecialchars($firstAlert['event']) . ":</strong> " . htmlspecialchars($firstAlert['description']);
}

// Parse current
$current = [
    "temp" => round($currentData['main']['temp']),
    "feels_like" => round($currentData['main']['feels_like']),
    "humidity" => $currentData['main']['humidity'],
    "weather" => $currentData['weather'][0]['main'],
    "icon" => $currentData['weather'][0]['icon'],
    "updated" => date("g:i A"),
    // Real-time rainfall (mm in last 1h)
    "precip" => isset($currentData['rain']['1h']) ? $currentData['rain']['1h'] : 0,
    // Real-time rain chance (from first forecast entry, as %)
    "chance" => isset($forecastData['list'][0]['pop']) ? round($forecastData['list'][0]['pop'] * 100) : 0
];

// Parse next 5 days (first 5 unique days only)
$forecast = [];
$dates = [];

foreach ($forecastData['list'] as $entry) {
    $date = date("Y-m-d", strtotime($entry['dt_txt']));
    if (!in_array($date, $dates)) {
        $dates[] = $date;
        $forecast[] = [
            "date" => $entry['dt_txt'],
            "temp" => round($entry['main']['temp']),
            "weather" => $entry['weather'][0]['main'],
            "icon" => $entry['weather'][0]['icon']
        ];
    }
    if (count($forecast) === 5) break;
}

// Hourly fallback
$hourly = [];
for ($i = 0; $i < 5; $i++) {
    $hour = $forecastData['list'][$i];
    $hourly[] = [
        "time" => date("g A", strtotime($hour['dt_txt'])),
        "temp" => round($hour['main']['temp']),
        "rain" => $hour['pop'] ?? 0,
        "wind" => round($hour['wind']['speed'], 1)
    ];
}

echo json_encode([
    "current" => $current,
    "forecast" => $forecast,
    "hourly" => $hourly,
    "alert" => $alertMsg
]);
?>
