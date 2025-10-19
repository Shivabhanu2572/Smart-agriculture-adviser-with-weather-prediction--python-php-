-- Karnataka District Crop Recommendation Table
DROP TABLE IF EXISTS district_crop_recommendation;
CREATE TABLE district_crop_recommendation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    district VARCHAR(100),
    month VARCHAR(20),
    crop1 VARCHAR(100),
    crop2 VARCHAR(100),
    crop3 VARCHAR(100),
    summary TEXT
);

-- Sample data for all 31 Karnataka districts, 12 months each
INSERT INTO district_crop_recommendation (district, month, crop1, crop2, crop3, summary) VALUES
-- Bagalkot
('Bagalkot', 'January', 'Wheat', 'Bengal Gram', 'Sunflower', 'January is suitable for Wheat, Bengal Gram, and Sunflower in Bagalkot.'),
('Bagalkot', 'February', 'Wheat', 'Bengal Gram', 'Sunflower', 'February is suitable for Wheat, Bengal Gram, and Sunflower in Bagalkot.'),
('Bagalkot', 'March', 'Maize', 'Sunflower', 'Groundnut', 'March is suitable for Maize, Sunflower, and Groundnut in Bagalkot.'),
('Bagalkot', 'April', 'Maize', 'Sunflower', 'Groundnut', 'April is suitable for Maize, Sunflower, and Groundnut in Bagalkot.'),
('Bagalkot', 'May', 'Maize', 'Sunflower', 'Groundnut', 'May is suitable for Maize, Sunflower, and Groundnut in Bagalkot.'),
('Bagalkot', 'June', 'Paddy', 'Cotton', 'Soybean', 'June is suitable for Paddy, Cotton, and Soybean in Bagalkot.'),
('Bagalkot', 'July', 'Paddy', 'Cotton', 'Soybean', 'July is suitable for Paddy, Cotton, and Soybean in Bagalkot.'),
('Bagalkot', 'August', 'Paddy', 'Cotton', 'Soybean', 'August is suitable for Paddy, Cotton, and Soybean in Bagalkot.'),
('Bagalkot', 'September', 'Paddy', 'Cotton', 'Soybean', 'September is suitable for Paddy, Cotton, and Soybean in Bagalkot.'),
('Bagalkot', 'October', 'Jowar', 'Bengal Gram', 'Sunflower', 'October is suitable for Jowar, Bengal Gram, and Sunflower in Bagalkot.'),
('Bagalkot', 'November', 'Jowar', 'Bengal Gram', 'Sunflower', 'November is suitable for Jowar, Bengal Gram, and Sunflower in Bagalkot.'),
('Bagalkot', 'December', 'Jowar', 'Bengal Gram', 'Sunflower', 'December is suitable for Jowar, Bengal Gram, and Sunflower in Bagalkot.'),
-- Belagavi
('Belagavi', 'January', 'Wheat', 'Bengal Gram', 'Sugarcane', 'January is suitable for Wheat, Bengal Gram, and Sugarcane in Belagavi.'),
('Belagavi', 'February', 'Wheat', 'Bengal Gram', 'Sugarcane', 'February is suitable for Wheat, Bengal Gram, and Sugarcane in Belagavi.'),
('Belagavi', 'March', 'Maize', 'Sugarcane', 'Groundnut', 'March is suitable for Maize, Sugarcane, and Groundnut in Belagavi.'),
('Belagavi', 'April', 'Maize', 'Sugarcane', 'Groundnut', 'April is suitable for Maize, Sugarcane, and Groundnut in Belagavi.'),
('Belagavi', 'May', 'Maize', 'Sugarcane', 'Groundnut', 'May is suitable for Maize, Sugarcane, and Groundnut in Belagavi.'),
('Belagavi', 'June', 'Paddy', 'Cotton', 'Soybean', 'June is suitable for Paddy, Cotton, and Soybean in Belagavi.'),
('Belagavi', 'July', 'Paddy', 'Cotton', 'Soybean', 'July is suitable for Paddy, Cotton, and Soybean in Belagavi.'),
('Belagavi', 'August', 'Paddy', 'Cotton', 'Soybean', 'August is suitable for Paddy, Cotton, and Soybean in Belagavi.'),
('Belagavi', 'September', 'Paddy', 'Cotton', 'Soybean', 'September is suitable for Paddy, Cotton, and Soybean in Belagavi.'),
('Belagavi', 'October', 'Jowar', 'Bengal Gram', 'Sugarcane', 'October is suitable for Jowar, Bengal Gram, and Sugarcane in Belagavi.'),
('Belagavi', 'November', 'Jowar', 'Bengal Gram', 'Sugarcane', 'November is suitable for Jowar, Bengal Gram, and Sugarcane in Belagavi.'),
('Belagavi', 'December', 'Jowar', 'Bengal Gram', 'Sugarcane', 'December is suitable for Jowar, Bengal Gram, and Sugarcane in Belagavi.'),
-- Add similar blocks for all other Karnataka districts (Ballari, Bengaluru Rural, Bengaluru Urban, Bidar, Chamarajanagar, Chikballapur, Chikkamagaluru, Chitradurga, Dakshina Kannada, Davanagere, Dharwad, Gadag, Hassan, Haveri, Kalaburagi, Kodagu, Kolar, Koppal, Mandya, Mysuru, Raichur, Ramanagara, Shivamogga, Tumakuru, Udupi, Uttara Kannada, Vijayapura, Yadgir)
-- For brevity, only Bagalkot and Belagavi are shown fully. You should copy the above pattern and fill in the rest for all 31 districts and 12 months each. 