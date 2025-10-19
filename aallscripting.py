#!/usr/bin/env python3
"""
smart_agri_ai.py
Integrated Python backend for the PHP project
'Smart Agriculture Adviser with Weather Prediction'

Features:
- Soil analysis & crop recommendation
- Crop disease image detection (AI simulation)
- Weather forecast via API
- Market-price trend analysis
Outputs JSON for PHP integration.
"""

import sys, os, json, argparse, random, datetime, base64
import requests
from statistics import mean
from PIL import Image

# --- Database (optional) ---
import mysql.connector

DB = dict(
    host="localhost",
    user="root",
    password="",
    database="smart_agri"
)

# ---------------------------------------------------------------------
#  SOIL ANALYSIS MODULE
# ---------------------------------------------------------------------
def soil_analysis(ph: float, moisture: float, soil_type: str):
    """Suggest crops based on soil pH, moisture, and soil type."""
    crops = {
        "Loamy": ["Wheat", "Sugarcane", "Rice"],
        "Sandy": ["Groundnut", "Watermelon", "Coconut"],
        "Clayey": ["Rice", "Jute", "Paddy"],
        "Red": ["Millet", "Cotton", "Tobacco"],
        "Black": ["Cotton", "Soybean", "Sunflower"],
        "Alluvial": ["Paddy", "Sugarcane", "Jute"]
    }
    ideal = crops.get(soil_type, ["Maize", "Wheat"])
    result = {
        "soil_type": soil_type,
        "pH": ph,
        "moisture": moisture,
        "recommended_crops": ideal,
        "fertility_status": "Fertile" if 5.5 <= ph <= 7.5 else "Needs Treatment"
    }
    print(json.dumps(result))
    return result

# ---------------------------------------------------------------------
#  WEATHER PREDICTION MODULE
# ---------------------------------------------------------------------
def weather_forecast(city="Bangalore"):
    """Fetch 3-day forecast from OpenWeather API (requires key)."""
    api_key = os.getenv("OPENWEATHER_KEY", "YOUR_API_KEY")
    try:
        res = requests.get(
            f"https://api.openweathermap.org/data/2.5/forecast",
            params={"q": city, "appid": api_key, "units": "metric", "cnt": 24},
            timeout=10
        )
        data = res.json()
        temps = [x["main"]["temp"] for x in data["list"]]
        avg = round(mean(temps), 1)
        report = {
            "city": city,
            "average_temp": avg,
            "condition": data["list"][0]["weather"][0]["description"],
            "humidity": data["list"][0]["main"]["humidity"]
        }
        print(json.dumps(report))
        return report
    except Exception as e:
        print(json.dumps({"error": str(e)}))

# ---------------------------------------------------------------------
#  AI CROP MONITOR MODULE
# ---------------------------------------------------------------------
def analyze_crop_image(image_path: str, crop: str):
    """
    Simulated AI analysis — reads image, produces random disease prediction.
    In a real system, replace this with TensorFlow/PyTorch model inference.
    """
    if not os.path.exists(image_path):
        print(json.dumps({"error": "Image not found"}))
        return

    img = Image.open(image_path)
    size = img.size
    diseases = [
        "Leaf Spot", "Blight", "Rust", "Healthy", "Yellow Mosaic", "Wilt"
    ]
    disease = random.choice(diseases)
    confidence = round(random.uniform(80, 99), 2)
    severity = random.choice(["Mild", "Moderate", "Severe"])
    result = {
        "crop": crop,
        "image_size": size,
        "disease": disease,
        "confidence": confidence,
        "severity": severity,
        "analysis_time": datetime.datetime.now().isoformat()
    }
    print(json.dumps(result))
    return result

# ---------------------------------------------------------------------
#  MARKET PRICE TREND MODULE
# ---------------------------------------------------------------------
def analyze_market_prices():
    """Reads live price data and computes top crops by modal price."""
    api_url = (
        "https://api.data.gov.in/resource/"
        "9ef84268-d588-465a-a308-a864a43d0070?"
        "api-key=579b464db66ec23bdd0000011ec418109033452d47392bb62daee529&"
        "format=json&limit=1000"
    )
    data = requests.get(api_url).json()
    records = data.get("records", [])
    top = sorted(records, key=lambda x: int(x.get("modal_price", 0)), reverse=True)[:5]
    summary = [{"crop": t["commodity"], "price": t["modal_price"], "state": t["state"]} for t in top]
    print(json.dumps({"top_prices": summary}))
    return summary

# ---------------------------------------------------------------------
#  MAIN ENTRYPOINT
# ---------------------------------------------------------------------
def main():
    parser = argparse.ArgumentParser(description="Smart Agriculture Adviser Python backend")
    sub = parser.add_subparsers(dest="cmd", required=True)

    s1 = sub.add_parser("soil", help="Run soil analysis")
    s1.add_argument("--ph", type=float, required=True)
    s1.add_argument("--moisture", type=float, required=True)
    s1.add_argument("--soil", required=True)

    s2 = sub.add_parser("weather", help="Weather forecast")
    s2.add_argument("--city", default="Bangalore")

    s3 = sub.add_parser("crop_ai", help="Analyze crop image")
    s3.add_argument("--image", required=True)
    s3.add_argument("--crop", default="Tomato")

    sub.add_parser("market", help="Market price analysis")

    args = parser.parse_args()

    if args.cmd == "soil":
        soil_analysis(args.ph, args.moisture, args.soil)
    elif args.cmd == "weather":
        weather_forecast(args.city)
    elif args.cmd == "crop_ai":
        analyze_crop_image(args.image, args.crop)
    elif args.cmd == "market":
        analyze_market_prices()

if __name__ == "__main__":
    main()
