<!DOCTYPE html>
<html>
<head>
    <title>Market Price | Smart Agriculture Advisor</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
            background-color: #f4f8f5;
        }
        h1 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        form {
            margin-bottom: 20px;
        }
        input[type="text"], select {
            padding: 10px;
            width: 250px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
            margin-top: 10px;
        }
        button {
            padding: 10px 16px;
            background-color: #2e7d32;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        th, td {
            border: 1px solid #e0e0e0;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #388e3c;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .no-results {
            margin-top: 20px;
            color: #c62828;
            font-weight: bold;
        }
        .top5 {
            background-color: #fff3e0;
        }
        .section-title {
            margin-top: 40px;
            font-size: 20px;
            color: #1b5e20;
        }
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
        }
        .header-actions .btn-link {
            text-decoration: none;
            background-color: #2e7d32;
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
        }
    </style>
    <script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,hi,kn,ta,te,ml,bn,gu,mr,pa,ur',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE
        }, 'google_translate_element');
    }
    </script>
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</head>
<body>

<div class="header-actions">
    <h1>Live Market Prices of Crops</h1>
    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
        <a href="dashboard.php" class="btn-link">Dashboard</a>
        <div id="google_translate_element"></div>
    </div>
</div>

<form method="get">
    <input type="text" name="search" placeholder="Search crop, market, state..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <select name="state">
        <option value="">All States</option>
        <?php
        $apiKey = "579b464db66ec23bdd0000011ec418109033452d47392bb62daee529";
        $url = "https://api.data.gov.in/resource/9ef84268-d588-465a-a308-a864a43d0070?api-key=$apiKey&format=json&limit=1000";
        $response = @file_get_contents($url);
        $data = json_decode($response, true);

        $states = [];
        if ($data && !empty($data['records'])) {
            foreach ($data['records'] as $item) {
                if (!in_array($item['state'], $states)) {
                    $states[] = $item['state'];
                }
            }
        }
        sort($states);
        foreach ($states as $state) {
            $selected = ($_GET['state'] ?? '') === $state ? 'selected' : '';
            echo "<option value='$state' $selected>$state</option>";
        }
        ?>
    </select>
    <button type="submit">Search</button>
</form>

<?php
if (!$data || empty($data['records'])) {
    echo "<p class='no-results'>No crop price data found. Please check your API key or try again later.</p>";
    exit;
}

$search = strtolower(trim($_GET['search'] ?? ''));
$stateFilter = $_GET['state'] ?? '';

$dates = array_unique(array_column($data['records'], 'arrival_date'));
rsort($dates);
$latestDate = $dates[0];

$filtered = array_filter($data['records'], function($item) use ($search, $latestDate, $stateFilter) {
    if ($item['arrival_date'] !== $latestDate) return false;
    if ($stateFilter && $item['state'] !== $stateFilter) return false;
    if ($search === '') return true;
    $fields = ['commodity', 'variety', 'market', 'district', 'state'];
    foreach ($fields as $field) {
        if (isset($item[$field]) && strpos(strtolower($item[$field]), $search) !== false) {
            return true;
        }
    }
    return false;
});

usort($filtered, function($a, $b) {
    return (int)$b['modal_price'] <=> (int)$a['modal_price'];
});

if (count($filtered) > 0) {
    $top5 = array_slice($filtered, 0, 5);
    echo "<div class='section-title'>Top 5 Highest Priced Crops on $latestDate</div>";
    echo "<table><thead><tr>
        <th>State</th>
        <th>District</th>
        <th>Market</th>
        <th>Commodity</th>
        <th>Variety</th>
        <th>Arrival Date</th>
        <th>Min Price</th>
        <th>Max Price</th>
        <th>Modal Price</th>
    </tr></thead><tbody>";
    foreach ($top5 as $row) {
        echo "<tr class='top5'>
            <td>{$row['state']}</td>
            <td>{$row['district']}</td>
            <td>{$row['market']}</td>
            <td>{$row['commodity']}</td>
            <td>{$row['variety']}</td>
            <td>{$row['arrival_date']}</td>
            <td>{$row['min_price']}</td>
            <td>{$row['max_price']}</td>
            <td><strong>{$row['modal_price']}</strong></td>
        </tr>";
    }
    echo "</tbody></table>";

    echo "<div class='section-title'>All Crop Prices on $latestDate</div>";
    echo "<table><thead><tr>
        <th>State</th>
        <th>District</th>
        <th>Market</th>
        <th>Commodity</th>
        <th>Variety</th>
        <th>Arrival Date</th>
        <th>Min Price</th>
        <th>Max Price</th>
        <th>Modal Price</th>
    </tr></thead><tbody>";
    foreach ($filtered as $row) {
        echo "<tr>
            <td>{$row['state']}</td>
            <td>{$row['district']}</td>
            <td>{$row['market']}</td>
            <td>{$row['commodity']}</td>
            <td>{$row['variety']}</td>
            <td>{$row['arrival_date']}</td>
            <td>{$row['min_price']}</td>
            <td>{$row['max_price']}</td>
            <td><strong>{$row['modal_price']}</strong></td>
        </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p class='no-results'>No crop prices available for the latest available date ($latestDate) or no match found.</p>";
}
?>

</body>
</html>
