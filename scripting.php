#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py#!/usr/bin/env python3
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))

equipment_rental.py
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py
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
crop_advice.py
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
import mysql.connector

def get_crop_advice(soil_type, ph, moisture, season):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)

    query = """SELECT * FROM crops
               WHERE soil_type=%s AND ph_min<=%s AND ph_max>=%s
               AND moisture_min<=%s AND moisture_max>=%s
               AND season=%s"""
    cursor.execute(query, (soil_type, ph, ph, moisture, moisture, season))
    crops = cursor.fetchall()
    conn.close()
    return crops

if __name__ == "__main__":
    result = get_crop_advice("Loamy", 6.5, 45, "Monsoon")
    print("Recommended crops:", result)
 weather.py
python
Copy code
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))
 admin_irrigation.py
python
Copy code
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)
 admin_prices.py
python
Copy code
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))
 admin_analytics.py
python
Copy code
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))
 equipment_rental.py
python
Copy code
import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())
 market_price_cache.py
python
Copy code
import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())

 weather.py
import requests

def get_weather(city):
    api_key = "YOUR_API_KEY"  # Replace with your OpenWeatherMap API key
    url = f"http://api.openweathermap.org/data/2.5/weather?q={city}&appid={api_key}&units=metric"
    response = requests.get(url)
    data = response.json()
    if data.get("main"):
        return {
            "temperature": data["main"]["temp"],
            "humidity": data["main"]["humidity"],
            "wind_speed": data["wind"]["speed"],
            "description": data["weather"][0]["description"]
        }
    else:
        return {"error": "City not found"}

if __name__ == "__main__":
    print(get_weather("Bangalore"))

 admin_irrigation.py
import mysql.connector

def get_irrigation_schedule(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM irrigation WHERE crop_name=%s", (crop_name,))
    schedule = cursor.fetchall()
    conn.close()
    return schedule

if __name__ == "__main__":
    schedule = get_irrigation_schedule("Tomato")
    print(schedule)

 admin_prices.py
import mysql.connector

def get_market_prices(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT * FROM market_prices WHERE crop_name=%s ORDER BY date DESC LIMIT 5", (crop_name,))
    prices = cursor.fetchall()
    conn.close()
    return prices

if __name__ == "__main__":
    print(get_market_prices("Tomato"))

 admin_analytics.py
import mysql.connector

def get_crop_analytics(crop_name):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    cursor.execute("SELECT AVG(price) as avg_price, MAX(price) as max_price, MIN(price) as min_price FROM market_prices WHERE crop_name=%s", (crop_name,))
    analytics = cursor.fetchone()
    conn.close()
    return analytics

if __name__ == "__main__":
    print(get_crop_analytics("Tomato"))


import mysql.connector

def get_equipment_rentals(equipment_name=None):
    conn = mysql.connector.connect(
        host="localhost",
        user="root",
        password="",
        database="smart_agri"
    )
    cursor = conn.cursor(dictionary=True)
    if equipment_name:
        cursor.execute("SELECT * FROM equipment_rentals WHERE equipment_name=%s", (equipment_name,))
    else:
        cursor.execute("SELECT * FROM equipment_rentals")
    rentals = cursor.fetchall()
    conn.close()
    return rentals

if __name__ == "__main__":
    print(get_equipment_rentals())

import json
import os

CACHE_FILE = "market_price_cache.json"

def save_cache(data):
    with open(CACHE_FILE, "w") as f:
        json.dump(data, f)

def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, "r") as f:
            return json.load(f)
    return {}

if __name__ == "__main__":
    sample_data = {"Tomato": 25, "Onion": 30}
    save_cache(sample_data)
    print(load_cache())


Create a folder in your project:

C:/xampp/htdocs/smrt/py_modules/


Save the 7 .py files inside py_modules/.

Run individually using Git Bash or Python:

cd C:/xampp/htdocs/smrt/py_modules
python crop_advice.py
python weather.py