<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Market Price | Smart Agriculture Advisor</title>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Montserrat', 'Segoe UI', sans-serif;
      background: linear-gradient(120deg, #e0f7fa 0%, #f0f9f4 100%);
      min-height: 100vh;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
    }
    #google_translate_element { position: fixed; top: 10px; right: 20px; z-index: 999; }
    .hero {
      background: linear-gradient(90deg, #256029 60%, #43a047 100%);
      color: white;
      padding: 44px 0 24px 0;
      text-align: center;
      box-shadow: 0 4px 20px rgba(67, 160, 71, 0.08);
    }
    .hero .icon { font-size: 48px; margin-bottom: 10px; display: block; }
    .hero h1 { margin: 0; font-size: 2.5rem; font-weight: 700; letter-spacing: 1px; }
    .hero p { margin: 10px 0 0 0; font-size: 1.1rem; font-weight: 400; color: #e3f2fd; }
    .main-container { max-width: 1200px; margin: 0 auto; width: 100%; padding: 32px 20px 20px 20px; display: flex; flex-direction: column; gap: 32px; }
    .card { background: linear-gradient(135deg, #b2f2c9 60%, #e8f5e9 100%); border-radius: 18px; box-shadow: 0 6px 24px rgba(56, 142, 60, 0.10); padding: 32px 24px 24px 24px; margin-bottom: 24px; width: 100%; }
    form { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: flex-start; margin-bottom: 0; }
    input[type="text"], select {
      padding: 12px; width: 220px; border: 1.5px solid #a5d6a7; border-radius: 8px;
      font-size: 1rem; background: #f8fff8; color: #256029; font-weight: 500; transition: border 0.2s;
    }
    input[type="text"]:focus, select:focus { border: 1.5px solid #43a047; outline: none; background: #fff; }
    input[type="text"]::placeholder { color: #388e3c; opacity: 0.95; font-weight: 500; }
    button {
      padding: 12px 28px; background: linear-gradient(90deg, #43a047 60%, #388e3c 100%);
      color: white; border: none; border-radius: 8px; font-size: 1.1rem; font-weight: 700; cursor: pointer;
      box-shadow: 0 2px 8px rgba(67, 160, 71, 0.10); transition: background 0.2s, box-shadow 0.2s;
    }
    button:hover { background: linear-gradient(90deg, #256029 60%, #43a047 100%); box-shadow: 0 4px 16px rgba(56, 142, 60, 0.18); }
    .section-title { margin-top: 40px; font-size: 1.3rem; color: #256029; font-weight: 700; letter-spacing: 0.5px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    th, td { border: 1px solid #e0e0e0; padding: 12px 8px; text-align: center; font-size: 1rem; }
    th { background-color: #256029; color: white; font-size: 1.05rem; font-weight: 700; }
    tr:nth-child(even) { background-color: #f2f2f2; }
    .no-results { margin-top: 20px; color: #c62828; font-weight: bold; text-align: center; }
    .top5 { background-color: #fff3e0; }
    .header-actions { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 0; }
    .header-actions .btn-link { text-decoration: none; background: linear-gradient(90deg, #43a047 60%, #388e3c 100%); color: white; padding: 10px 22px; border-radius: 8px; font-weight: 700; font-size: 1rem; box-shadow: 0 2px 8px rgba(67, 160, 71, 0.10); transition: background 0.2s, box-shadow 0.2s; margin-bottom: 8px; }
    .header-actions .btn-link:hover { background: linear-gradient(90deg, #256029 60%, #43a047 100%); box-shadow: 0 4px 16px rgba(56, 142, 60, 0.18); }
    .footer { margin-top: auto; background: #256029; color: #e3f2fd; text-align: center; padding: 18px 0 10px 0; font-size: 1rem; letter-spacing: 0.5px; box-shadow: 0 -2px 10px rgba(56, 142, 60, 0.08); }
    @media (max-width: 900px) { .main-container { padding: 18px 4vw 18px 4vw; } .card { padding: 18px 8px 18px 8px; } table th, table td { font-size: 0.95rem; padding: 8px 4px; } }
    @media (max-width: 600px) { .hero h1 { font-size: 1.5rem; } .main-container { padding: 10px 2vw 10px 2vw; } .card { padding: 10px 2vw 10px 2vw; } table th, table td { font-size: 0.9rem; padding: 6px 2px; } }
    .goog-te-banner-frame.skiptranslate, .goog-logo-link, .goog-te-gadget img { display: none !important; }
    body { top: 0 !important; }
  </style>
  <script type="text/javascript">
    function googleTranslateElementInit() {
      new google.translate.TranslateElement({pageLanguage: 'en', includedLanguages: 'en,hi,kn,ta,te,ml,bn,gu,mr,pa,ur', layout: google.translate.TranslateElement.InlineLayout.SIMPLE}, 'google_translate_element');
    }
  </script>
  <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</head>
<body>
<div class="hero">
  <span class="icon"><i class="fa-solid fa-wheat-awn"></i></span>
  <h1>Live Market Prices of Crops</h1>
  <p>Stay updated with real-time crop prices across India and make informed selling decisions!</p>
</div>

<div class="main-container">
  <div class="header-actions">
    <a href="dashboard.php" class="btn-link"><i class="fa-solid fa-arrow-left"></i> Dashboard</a>
    <div id="google_translate_element"></div>
  </div>

  <div class="card">
    <form method="get">
      <input type="text" name="search" placeholder="Search crop, market, state..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
      <select name="state">
        <option value="">All States</option>
        <?php
        $apiKey = "579b464db66ec23bdd00000110cee3bff6d64baf6fa3fb0bc5bc1a99";
        $baseUrl = "https://api.data.gov.in/resource/9ef84268-d588-465a-a308-a864a43d0070?api-key=$apiKey&format=json&limit=500";
        $cacheFile = __DIR__ . '/market_price_cache.json';

        $context = stream_context_create(['http'=>['timeout'=>10,'method'=>'GET']]);
        $response = @file_get_contents($baseUrl,false,$context);
        $data = json_decode($response ?? '',true);
        if($data && !empty($data['records'])){ file_put_contents($cacheFile,$response); }
        else if(file_exists($cacheFile)){ $cached = file_get_contents($cacheFile); $data = json_decode($cached,true); }
        $states=[]; if($data && !empty($data['records'])){ foreach($data['records'] as $item){ 
          $sKey = $item['State']??$item['state']??''; 
          if($sKey && !in_array($sKey,$states,true)) $states[]=$sKey; } } sort($states);
        foreach($states as $state){ $selected = ($_GET['state']??'')===$state?'selected':''; echo "<option value='".htmlspecialchars($state,ENT_QUOTES)."' $selected>".htmlspecialchars($state)."</option>"; }
        ?>
      </select>
      <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
    </form>
  </div>

  <?php
  if(!$data || empty($data['records'])){ echo "<p class='no-results'>No crop price data found. Please check API or try later.</p>"; }
  else{
    $search=strtolower(trim($_GET['search']??''));
    $stateFilter=$_GET['state']??'';

    // Filter data
    $filtered=array_filter($data['records'],function($item)use($search,$stateFilter){
      $stateKey=$item['State']??$item['state']??null;
      if($stateFilter && (!$stateKey || $item[$stateKey]!==$stateFilter)) return false;
      if($search==='') return true;
      $fields=['Commodity','Variety','Market','District','State','state'];
      foreach($fields as $f){ if(isset($item[$f]) && strpos(strtolower((string)$item[$f]),$search)!==false) return true; }
      return false;
    });

    // Remove duplicates
    $unique=[]; $filteredUnique=[];
    foreach($filtered as $row){
      $key = ($row['State']??$row['state']??'').'|'.($row['District']??$row['district']??'').'|'.($row['Market']??'').'|'.($row['Commodity']??'').'|'.($row['Variety']??$row['variety']??'');
      if(!isset($unique[$key])){ $unique[$key]=true; $filteredUnique[]=$row; }
    }

    // Sort by Modal Price desc
    usort($filteredUnique,function($a,$b){ return (int)($b['Modal_Price']??$b['modal_price']??0) <=> (int)($a['Modal_Price']??$a['modal_price']??0); });

    // Top 5
    echo "<div class='section-title'>Top 5 Highest Priced Crops in </div>";
    echo "<table><thead><tr>
      <th>State</th><th>District</th><th>Market</th><th>Commodity</th><th>Variety</th><th>Min Price</th><th>Max Price</th><th>Modal Price</th>
    </tr></thead><tbody>";
    foreach(array_slice($filteredUnique,0,5) as $row){
      echo "<tr class='top5'>
        <td>".($row['State']??$row['state']??'')."</td>
        <td>".($row['District']??$row['district']??'')."</td>
        <td>".($row['Market']??'')."</td>
        <td>".($row['Commodity']??'')."</td>
        <td>".($row['Variety']??$row['variety']??'')."</td>
        <td>".($row['Min_Price']??$row['min_price']??'')."</td>
        <td>".($row['Max_Price']??$row['max_price']??'')."</td>
        <td><strong>".($row['Modal_Price']??$row['modal_price']??'')."</strong></td>
      </tr>";
    }
    echo "</tbody></table>";

    // Remaining crops
    echo "<div class='section-title'>All Crop Prices </div>";
    echo "<table><thead><tr>
      <th>State</th><th>District</th><th>Market</th><th>Commodity</th><th>Variety</th><th>Min Price</th><th>Max Price</th><th>Modal Price</th>
    </tr></thead><tbody>";
    foreach(array_slice($filteredUnique,5) as $row){
      echo "<tr>
        <td>".($row['State']??$row['state']??'')."</td>
        <td>".($row['District']??$row['district']??'')."</td>
        <td>".($row['Market']??'')."</td>
        <td>".($row['Commodity']??'')."</td>
        <td>".($row['Variety']??$row['variety']??'')."</td>
        <td>".($row['Min_Price']??$row['min_price']??'')."</td>
        <td>".($row['Max_Price']??$row['max_price']??'')."</td>
        <td><strong>".($row['Modal_Price']??$row['modal_price']??'')."</strong></td>
      </tr>";
    }
    echo "</tbody></table>";
  }
  ?>
</div>

<div class="footer">
  &copy; <?php echo date('Y'); ?> Smart Agriculture | Market Insights for Smarter Farming
</div>
</body>
</html>
