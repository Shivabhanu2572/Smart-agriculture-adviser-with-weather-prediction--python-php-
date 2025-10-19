<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$fullname = $_SESSION['fullname'];

// Database connection
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

// Fetch user's location (district)
$user_location = null;
if ($conn && isset($_SESSION['user_id'])) {
    $uid = intval($_SESSION['user_id']);
    $res = mysqli_query($conn, "SELECT location FROM users WHERE id = $uid LIMIT 1");
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $user_location = trim($row['location']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Smart Agriculture Advisor</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;}
body{margin:0;font-family:'Inter','Segoe UI',Arial,sans-serif;display:flex;background:url('uploads/equipment_images/dashboard.jpg') center center/cover no-repeat fixed;min-height:100vh;overflow:hidden;}
.sidebar{width:260px;background:linear-gradient(135deg,#33691e 80%,#558b2f 100%);color:white;padding:14px 18px 16px;display:flex;flex-direction:column;box-shadow:2px 0 16px rgba(51,105,30,0.08);z-index:2;height:100vh;justify-content:space-between;}
.sidebar h1{font-size:1.3rem;font-weight:700;margin-bottom:6px;letter-spacing:0.3px;line-height:1.2;}
.sidebar .welcome{font-size:0.95rem;margin-bottom:20px;color:#e6ee9c;font-weight:500;}
.nav-link{background:rgba(86,139,47,0.13);padding:11px 16px;border-radius:10px;color:white;text-decoration:none;margin-bottom:10px;display:flex;align-items:center;font-weight:500;font-size:1rem;transition:background 0.2s,transform 0.2s;box-shadow:0 1px 4px rgba(51,105,30,0.04);}
.nav-link:hover{background:linear-gradient(90deg,#689f38 60%,#558b2f 100%);transform:translateX(4px) scale(1.03);}
.logout-btn{margin-top:auto;margin-bottom:20px;padding:8px 12px;font-size:0.97rem;background:linear-gradient(90deg,#d32f2f 70%,#b71c1c 100%);color:#fff;font-weight:600;box-shadow:0 2px 8px rgba(211,47,47,0.10);}
.logout-btn:hover{background:#b71c1c;color:#fff;}
.main-content{flex:1;padding:25px;display:flex;flex-direction:column;overflow-y:auto;background:rgba(255,255,255,0.3);max-width:calc(100vw - 260px);}
.top-section{display:flex;justify-content:space-between;flex-wrap:wrap;gap:20px;align-items:flex-start;max-width:100%;margin-bottom:20px;}
.weather-section{display:flex;flex-direction:column;align-items:center;min-width:250px;max-width:280px;flex-shrink:0;}
.weather-card{background:linear-gradient(120deg,rgba(255,255,255,0.8) 90%,rgba(232,245,233,0.8) 100%);border-radius:18px;box-shadow:0 8px 32px rgba(51,105,30,0.15),0 2px 8px rgba(0,0,0,0.1);padding:20px 16px;width:100%;max-width:250px;text-align:center;margin-bottom:16px;transition:box-shadow 0.2s;backdrop-filter:blur(5px);}
.weather-card:hover{box-shadow:0 12px 40px rgba(51,105,30,0.25),0 4px 12px rgba(0,0,0,0.15);}
.weather-card .icon{font-size:2.8rem;margin-bottom:6px;}
.weather-card .temp{font-size:1.5rem;font-weight:700;color:#33691e;}
.weather-card .status{color:#616161;font-size:1.02rem;margin-top:7px;}
.weather-card a{display:inline-block;margin-top:14px;background:linear-gradient(90deg,#43a047 70%,#388e3c 100%);color:white;padding:9px 18px;border-radius:8px;text-decoration:none;font-size:1.01rem;font-weight:500;transition:background 0.2s,box-shadow 0.2s;box-shadow:0 1px 4px rgba(67,160,71,0.08);}
.weather-card a:hover{background:#2e7d32;box-shadow:0 2px 8px rgba(67,160,71,0.13);}
.chart-container{background:rgba(255,255,255,0.8);border-radius:15px;padding:16px 14px 8px;box-shadow:0 8px 32px rgba(51,105,30,0.15),0 2px 8px rgba(0,0,0,0.1);width:100%;max-width:250px;margin-top:8px;backdrop-filter:blur(5px);}
.chart-container h4{color:#2e7d32;font-size:1.08rem;font-weight:600;}
.top-prices{background:rgba(255,255,255,0.8);border-radius:15px;padding:20px 18px;box-shadow:0 8px 32px rgba(51,105,30,0.15),0 2px 8px rgba(0,0,0,0.1);flex:1;min-width:300px;max-width:calc(100% - 300px);margin-bottom:20px;backdrop-filter:blur(5px);}
.top-prices h3{font-size:1.1rem;font-weight:700;color:#33691e;margin-bottom:8px;}
.top-prices table{width:100%;border-collapse:separate;border-spacing:0;margin-top:10px;background:#f9fff0;border-radius:8px;overflow:hidden;font-size:0.95rem;}
.top-prices th,.top-prices td{border:none;padding:8px 6px;text-align:center;}
.top-prices th{background:#dcedc8;color:#33691e;font-weight:600;}
.top-prices tr:nth-child(even) td{background:#f1f8e9;}
.top-prices tr:hover td{background:#e6ee9c;}
.crop-suggestions{background:rgba(249,255,240,0.8);border-radius:12px;padding:18px 15px;box-shadow:0 2px 8px rgba(51,105,30,0.07);font-size:0.95rem;margin-top:20px;}
.crop-suggestions h3{font-size:1rem;font-weight:600;margin-bottom:10px;color:#558b2f;}
.crop-box{margin-bottom:12px;padding:12px;background:rgba(255,255,255,0.9);border-left:4px solid #558b2f;border-radius:6px;box-shadow:0 1px 4px rgba(51,105,30,0.04);}
.crop-box h4{margin:0 0 4px;font-size:0.95rem;font-weight:600;color:#33691e;}
.crop-box p{font-size:0.9rem;margin:0;color:#616161;}
.sidebar-nav{flex:1 1 auto;display:flex;flex-direction:column;margin-bottom:12px;border-radius:10px;border:1px solid rgba(255,255,255,0.13);box-shadow:0 1px 6px rgba(51,105,30,0.06);}
@media (max-width:1200px){.main-content{padding:24px 16px 18px;}.weather-section{max-width:260px;}.weather-card{max-width:240px;}.chart-container{max-width:240px;}.top-prices{max-width:calc(100% - 280px);}}
@media (max-width:1100px){.main-content{padding:20px 12px 16px;}.top-section{flex-direction:column;gap:20px;}.weather-section,.chart-container,.top-prices{width:100%!important;max-width:100%!important;}.weather-card,.chart-container{max-width:300px;}}
@media (max-width:700px){.sidebar{display:none;}.main-content{padding:16px 8px;}.weather-card,.chart-container{max-width:280px;}}
@media (max-width:500px){.main-content{padding:12px 6px;}.top-section{gap:12px;}.weather-card,.chart-container{max-width:260px;}.top-prices{padding:16px 12px;}}
</style>
<script type="text/javascript">
function googleTranslateElementInit(){
    new google.translate.TranslateElement({
        pageLanguage:'en',
        includedLanguages:'en,hi,kn,ta,te,ml,bn,gu,mr,pa,ur',
        layout:google.translate.TranslateElement.InlineLayout.SIMPLE
    },'google_translate_element');
}
</script>
<script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
</head>
<body>
<div class="sidebar">
    <h1>Smart Agriculture</h1>
    <div class="welcome">Hello <?php echo htmlspecialchars($fullname); ?> 👋</div>
    <div class="sidebar-nav">
        <a href="profile_dashboard.php" class="nav-link">🌱 My Profile</a>
        <a href="weather_dashboard.php" class="nav-link">🌦️ Weather forecast</a>
        <a href="soil.php" class="nav-link">🩺 Soil Analysis</a>
        <a href="crop_advice.php" class="nav-link">💰 Crop Advice</a>
        <a href="irrigation_tips.php" class="nav-link">📈 Irrigation Tips</a>
        <a href="ai_crop_monitor.php" class="nav-link">🤖 AI Crop Monitor</a>
        <a href="marketing_price.php" class="nav-link"> Market Prices</a>
        <a href="equipment_rental.php" class="nav-link">🚜 Agri Equipment</a>
    </div>
    <a href="logout.php" class="nav-link logout-btn">🔓 Logout</a>
</div>

<div class="main-content">
    <h2 style="margin-top:0;color:#2e7d32;">Welcome to Smart Agriculture Advisor Dashboard</h2>
    <div id="google_translate_element" style="position:absolute;top:20px;right:30px;"></div>

    <div class="top-section">
        <div class="top-prices">
            <h3> Top 3 Highest Crop Prices</h3>
          
                <?php
// Default crop data
$topCrops = [
    [
        'Commodity' => 'Grapes',
        'Place' => 'Bhuna, Fatehabad, Haryana',
        'Max_Price' => 3500
    ],
    [
        'Commodity' => 'Wheat',
        'Place' => 'Hata, Damoh, Madhya Pradesh',
        'Max_Price' => 1111
    ],
    [
        'Commodity' => 'Wheat',
        'Place' => 'Banapura, Hoshangabad, Madhya Pradesh',
        'Max_Price' => 1050
    ]
];

// Display table
echo "<table border='1' cellpadding='8' cellspacing='0'>
        <tr>
            <th>Crop</th>
            <th>Place</th>
            <th>Max Price (₹)</th>
        </tr>";

foreach ($topCrops as $crop) {
    echo "<tr>
            <td>" . htmlspecialchars($crop['Commodity']) . "</td>
            <td>" . htmlspecialchars($crop['Place']) . "</td>
            <td>" . htmlspecialchars($crop['Max_Price']) . "</td>
          </tr>";
}

echo "</table>";
?>
           

            <div class="crop-suggestions" id="crop-suggestions">
                <h3>🌾 Best Crops to Sow Now in Your Region</h3>
                <?php
                $districts=[];
                $district_query=mysqli_query($conn,"SELECT DISTINCT district FROM district_crop_recommendation ORDER BY district ASC");
                if($district_query){
                    while($row=mysqli_fetch_assoc($district_query)){
                        $districts[]=$row['district'];
                    }
                }
                $selected_district=null;
                if(isset($_GET['district']) && in_array($_GET['district'],$districts)){
                    $selected_district=$_GET['district'];
                }elseif($user_location && in_array($user_location,$districts)){
                    $selected_district=$user_location;
                }else{
                    $locationJson=@file_get_contents("https://ipapi.co/json/");
                    $location=$locationJson!==false?json_decode($locationJson,true):[];
                    $selected_district=$location['city']??($districts[0]??'Mandya');
                }
                ?>
                <form method="get" style="margin-bottom:12px;">
                    <label for="district" style="font-size:0.98rem;color:#33691e;font-weight:500;">Select District:</label>
                    <select name="district" id="district" onchange="this.form.submit()" style="margin-left:8px;padding:3px 8px;border-radius:6px;border:1px solid #dcedc8;font-size:0.98rem;">
                        <?php foreach($districts as $d): ?>
                        <option value="<?php echo htmlspecialchars($d); ?>" <?php if($d==$selected_district)echo 'selected'; ?>>
                            <?php echo htmlspecialchars($d); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </form>
                <?php
                $month=date('F');
                $selected_district=trim($selected_district);
                $month=trim($month);
                $sql="SELECT * FROM district_crop_recommendation WHERE LOWER(TRIM(district))=LOWER('$selected_district') AND LOWER(TRIM(month))=LOWER('$month')";
                $result=mysqli_query($conn,$sql);
                if($result && mysqli_num_rows($result)>0){
                    while($row=mysqli_fetch_assoc($result)){
                        echo "<div class='crop-box'>";
                        echo "<h4>🌱 ".htmlspecialchars($row['crop1']).", ".htmlspecialchars($row['crop2']).", ".htmlspecialchars($row['crop3'])."</h4>";
                        echo "<p>".htmlspecialchars($row['summary'])."</p>";
                        echo "</div>";
                    }
                }else{
                    echo "<p style='color:#b71c1c;'>No crop recommendation found for <strong>".htmlspecialchars($selected_district)."</strong> in <strong>".htmlspecialchars($month)."</strong>.</p>";
                }
                ?>
            </div>
        </div>

        <div class="weather-section">
            <div class="weather-card" id="weather">
                <div class="icon">⏳</div>
                <div class="temp">Loading...</div>
                <div class="status">Fetching weather</div>
                <a href="weather_dashboard.php">View More</a>
            </div>
            <div class="chart-container">
                <h4 style="margin:10px 0 5px;color:#2e7d32;">🌧️ Rainfall (mm)</h4>
                <canvas id="rainChart" height="220"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", async ()=>{
    const weatherCard=document.getElementById("weather");
    const apiKey="7b46741e515713880330945106c0d3d8";
    try{
        const locationRes=await fetch("https://ipapi.co/json/");
        const location=await locationRes.json();
        const city=location.city||"Bangalore";
        const weatherRes=await fetch(`https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`);
        const weather=await weatherRes.json();
        const temp=weather.main.temp+"°C";
        const description=weather.weather[0].description;
        const icon=weather.weather[0].main;
        let iconEmoji="🌦️";
        if(icon.includes("Rain")) iconEmoji="🌧️";
        else if(icon.includes("Clear")) iconEmoji="☀️";
        else if(icon.includes("Cloud")) iconEmoji="☁️";
        else if(icon.includes("Thunder")) iconEmoji="⛈️";
        weatherCard.querySelector(".icon").textContent=iconEmoji;
        weatherCard.querySelector(".temp").textContent=temp;
        weatherCard.querySelector(".status").textContent=description.charAt(0).toUpperCase()+description.slice(1);
    }catch(err){
        weatherCard.querySelector(".icon").textContent="⚠️";
        weatherCard.querySelector(".temp").textContent="N/A";
        weatherCard.querySelector(".status").textContent="Weather unavailable";
        console.error("Weather load failed:",err);
    }
});

const ctx=document.getElementById("rainChart").getContext("2d");
new Chart(ctx,{
    type:"bar",
    data:{
        labels:["2025","2024","2023","2022","2021"],
        datasets:[{
            label:"Rainfall (mm)",
            data:[1040,1025,980,890,1125],
            backgroundColor:"#66bb6a"
        }]
    },
    options:{
        responsive:true,
        plugins:{legend:{display:false}},
        scales:{
            y:{beginAtZero:true,title:{display:true,text:"Rainfall (mm)" }},
            x:{title:{display:true,text:"Year"}}
        }
    }
});
</script>
</body>
</html>
