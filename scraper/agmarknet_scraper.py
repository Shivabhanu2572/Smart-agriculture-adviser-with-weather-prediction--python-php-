from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait, Select
from selenium.webdriver.support import expected_conditions as EC
from bs4 import BeautifulSoup
import pandas as pd
import time

search_date = "06-Jul-2025"

driver = webdriver.Chrome()
driver.get("https://agmarknet.gov.in/SearchCmmMkt.aspx")
print("🔍 Agmarknet page opened...")

# ✅ Scroll down to trigger full page load
driver.execute_script("window.scrollTo(0, document.body.scrollHeight);")
time.sleep(5)  # Allow JavaScript to run

# ✅ Wait up to 60 seconds for the dropdown
try:
    WebDriverWait(driver, 60).until(
        EC.presence_of_element_located((By.ID, "cphBody_ddlState"))
    )
    print("✅ State dropdown found.")
except:
    print("❌ State dropdown not found — could be inside iframe or page didn't load.")
    driver.save_screenshot("form_load_error.png")
    driver.quit()
    exit()

# ✅ Now interact with form
try:
    state_dropdown = Select(driver.find_element(By.ID, "cphBody_ddlState"))
    state_dropdown.select_by_value("0")

    commodity_dropdown = Select(driver.find_element(By.ID, "cphBody_ddlCommodity"))
    commodity_dropdown.select_by_value("0")

    driver.find_element(By.ID, "cphBody_txtDate").clear()
    driver.find_element(By.ID, "cphBody_txtDate").send_keys(search_date)

    driver.find_element(By.ID, "cphBody_txtToDate").clear()
    driver.find_element(By.ID, "cphBody_txtToDate").send_keys(search_date)

    driver.find_element(By.ID, "cphBody_rdbType_1").click()
    driver.find_element(By.ID, "cphBody_btnGetData").click()

except Exception as e:
    print(f"❌ Error filling the form: {e}")
    driver.save_screenshot("form_fill_error.png")
    driver.quit()
    exit()

# ✅ Wait for crop price table
try:
    WebDriverWait(driver, 40).until(
        EC.presence_of_element_located((By.ID, "cphBody_GridPriceData"))
    )
    time.sleep(3)
    print("✅ Table loaded successfully.")
except:
    print("❌ Table not found — either no data or failed to render.")
    driver.save_screenshot("table_not_found.png")
    driver.quit()
    exit()

# ✅ Parse data using BeautifulSoup
soup = BeautifulSoup(driver.page_source, "html.parser")
driver.quit()

table = soup.find("table", {"id": "cphBody_GridPriceData"})
if not table:
    print("❌ No data table found in HTML.")
    exit()

rows = table.find_all("tr")
data = []

for row in rows[1:]:
    cols = row.find_all("td")
    if len(cols) >= 9:
        data.append({
            "state": cols[0].text.strip(),
            "district": cols[1].text.strip(),
            "market": cols[2].text.strip(),
            "commodity": cols[3].text.strip(),
            "variety": cols[4].text.strip(),
            "arrival_date": cols[5].text.strip(),
            "min_price": cols[6].text.strip(),
            "max_price": cols[7].text.strip(),
            "modal_price": cols[8].text.strip()
        })

# ✅ Save to CSV
df = pd.DataFrame(data)
df.to_csv("agmarknet_prices.csv", index=False)
print(f"✅ {len(df)} crop prices saved to agmarknet_prices.csv")
