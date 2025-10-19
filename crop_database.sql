-- Comprehensive Crop Database for AI Monitoring System
-- This database contains detailed information for all crop varieties

-- Crop varieties and their characteristics
CREATE TABLE IF NOT EXISTS crop_varieties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(100) NOT NULL,
    variety_name VARCHAR(100) NOT NULL,
    crop_type ENUM('Vegetable', 'Grain', 'Fruit', 'Pulse', 'Oilseed', 'Spice', 'Other') NOT NULL,
    growth_duration_days INT NOT NULL,
    water_requirement ENUM('Low', 'Medium', 'High') NOT NULL,
    climate_zone ENUM('Tropical', 'Subtropical', 'Temperate', 'All') NOT NULL,
    soil_type ENUM('Sandy', 'Loamy', 'Clay', 'All') NOT NULL,
    ph_range VARCHAR(20) NOT NULL,
    spacing_cm VARCHAR(50) NOT NULL,
    yield_per_hectare VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Disease information for each crop
CREATE TABLE IF NOT EXISTS crop_diseases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(100) NOT NULL,
    disease_name VARCHAR(200) NOT NULL,
    disease_type ENUM('Fungal', 'Bacterial', 'Viral', 'Nematode', 'Physiological') NOT NULL,
    symptoms TEXT NOT NULL,
    causes TEXT NOT NULL,
    treatment_plan TEXT NOT NULL,
    prevention_methods TEXT NOT NULL,
    severity_level ENUM('Low', 'Medium', 'High', 'Critical') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fertilizer recommendations for each crop
CREATE TABLE IF NOT EXISTS crop_fertilizers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(100) NOT NULL,
    growth_stage VARCHAR(50) NOT NULL,
    npk_ratio VARCHAR(20) NOT NULL,
    application_rate VARCHAR(100) NOT NULL,
    application_method TEXT NOT NULL,
    timing VARCHAR(200) NOT NULL,
    organic_alternatives TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Growth stages for each crop
CREATE TABLE IF NOT EXISTS crop_growth_stages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    crop_name VARCHAR(100) NOT NULL,
    stage_name VARCHAR(100) NOT NULL,
    stage_order INT NOT NULL,
    duration_days INT NOT NULL,
    description TEXT NOT NULL,
    care_requirements TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Crop Recommendation Master Table for Dynamic, Scientific Matching
DROP TABLE IF EXISTS crop_recommendation_master;
CREATE TABLE crop_recommendation_master (
    id INT AUTO_INCREMENT PRIMARY KEY,
    soil_type VARCHAR(100) NOT NULL,
    crop_name VARCHAR(100) NOT NULL,
    variety_name VARCHAR(100),
    ph_min FLOAT NOT NULL,
    ph_max FLOAT NOT NULL,
    moisture_min FLOAT NOT NULL,
    moisture_max FLOAT NOT NULL,
    scientific_note TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample data for all major soil types (expand as needed)
INSERT INTO crop_recommendation_master (soil_type, crop_name, variety_name, ph_min, ph_max, moisture_min, moisture_max, scientific_note) VALUES
('Alluvial', 'Wheat', 'HD2967', 6.0, 7.5, 40, 60, 'Wheat grows best in alluvial soils with neutral pH and moderate moisture.'),
('Alluvial', 'Rice', 'Basmati', 6.0, 7.0, 60, 80, 'Rice prefers alluvial soils with high moisture and slightly acidic to neutral pH.'),
('Black', 'Cotton', 'Bt Cotton', 6.0, 8.0, 40, 60, 'Cotton thrives in black soils with good moisture retention and slightly alkaline pH.'),
('Black', 'Soybean', 'JS-335', 6.0, 7.5, 40, 70, 'Soybean prefers black soils with moderate moisture and neutral pH.'),
('Red', 'Groundnut', 'JL-24', 5.5, 7.0, 30, 60, 'Groundnut is suitable for red soils with slightly acidic to neutral pH and moderate moisture.'),
('Red', 'Millets', 'Bajra', 5.5, 7.0, 20, 50, 'Millets are drought-tolerant and grow well in red soils with low to moderate moisture.'),
('Laterite', 'Cashew', 'Vengurla-4', 5.5, 6.5, 30, 60, 'Cashew adapts to laterite soils with acidic pH and moderate moisture.'),
('Laterite', 'Pineapple', 'Queen', 5.0, 6.5, 40, 70, 'Pineapple prefers acidic laterite soils with good drainage.'),
('Mountain', 'Tea', 'Assam', 4.5, 5.5, 60, 80, 'Tea grows best in mountain soils with acidic pH and high moisture.'),
('Mountain', 'Potato', 'Kufri Jyoti', 5.0, 6.5, 40, 70, 'Potato is suitable for mountain soils with slightly acidic pH and moderate moisture.'),
('Desert', 'Pearl Millet', 'ICMV-221', 6.0, 8.0, 10, 30, 'Pearl millet is drought-tolerant and grows in desert soils with low moisture.'),
('Desert', 'Guar', 'Pusa Navbahar', 7.0, 8.5, 10, 30, 'Guar is suitable for arid, sandy desert soils with alkaline pH.'),
('Peaty', 'Rice', 'IR64', 5.0, 6.5, 60, 80, 'Rice can be grown in peaty soils with high organic matter and moisture.'),
('Saline', 'Barley', 'RD-2503', 7.5, 8.5, 30, 60, 'Barley tolerates saline soils with alkaline pH.'),
('Loamy', 'Tomato', 'Hybrid F1', 6.0, 7.0, 40, 70, 'Tomato grows best in loamy soils with neutral pH and moderate moisture.'),
('Loamy', 'Maize', 'Hybrid', 6.0, 7.0, 40, 70, 'Maize prefers loamy soils with good drainage and neutral pH.'),
('Sandy', 'Watermelon', 'Sugar Baby', 5.5, 7.0, 20, 50, 'Watermelon is suitable for sandy soils with low moisture.'),
('Sandy', 'Groundnut', 'JL-24', 5.5, 7.0, 20, 50, 'Groundnut grows well in sandy soils with good drainage.'),
('Clayey', 'Rice', 'Sona Masuri', 6.0, 7.0, 60, 80, 'Rice is ideal for clayey soils with high moisture.'),
('Clayey', 'Sugarcane', 'Co-86032', 6.0, 7.5, 50, 80, 'Sugarcane prefers clayey soils with high moisture and neutral pH.'),
('Forest', 'Cardamom', 'Malabar', 5.0, 6.5, 60, 80, 'Cardamom grows in forest soils with acidic pH and high organic matter.'),
('Mixed Red and Black', 'Cotton', 'Bt Cotton', 6.0, 8.0, 40, 60, 'Cotton is suitable for mixed red and black soils.'),
('Coastal Alluvial', 'Coconut', 'West Coast Tall', 5.5, 7.5, 50, 80, 'Coconut thrives in coastal alluvial soils with high moisture.'),
('Lateritic Gravelly', 'Cashew', 'Vengurla-4', 5.5, 6.5, 30, 60, 'Cashew adapts to lateritic gravelly soils.'),
('Red Sandy Loam', 'Groundnut', 'JL-24', 5.5, 7.0, 30, 60, 'Groundnut is suitable for red sandy loam soils.'),
('Deep Black', 'Cotton', 'Bt Cotton', 6.0, 8.0, 40, 60, 'Deep black soils are ideal for cotton.'),
('Medium Black', 'Soybean', 'JS-335', 6.0, 7.5, 40, 70, 'Medium black soils are suitable for soybean.'),
('Shallow Black', 'Jowar', 'M35-1', 6.0, 7.5, 30, 60, 'Jowar grows in shallow black soils.');

-- Insert comprehensive crop data

-- TOMATO VARIETIES
INSERT INTO crop_varieties (crop_name, variety_name, crop_type, growth_duration_days, water_requirement, climate_zone, soil_type, ph_range, spacing_cm, yield_per_hectare) VALUES
('Tomato', 'Hybrid F1', 'Vegetable', 90, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '60x45', '25-30 tons'),
('Tomato', 'Local Desi', 'Vegetable', 85, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '60x45', '20-25 tons'),
('Tomato', 'Cherry', 'Vegetable', 70, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '45x30', '15-20 tons'),
('Tomato', 'Roma', 'Vegetable', 95, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '60x45', '30-35 tons');

-- POTATO VARIETIES
INSERT INTO crop_varieties (crop_name, variety_name, crop_type, growth_duration_days, water_requirement, climate_zone, soil_type, ph_range, spacing_cm, yield_per_hectare) VALUES
('Potato', 'Kufri Jyoti', 'Vegetable', 120, 'High', 'Temperate', 'Loamy', '5.5-6.5', '60x20', '25-30 tons'),
('Potato', 'Kufri Chandramukhi', 'Vegetable', 110, 'High', 'Temperate', 'Loamy', '5.5-6.5', '60x20', '20-25 tons'),
('Potato', 'Local Red', 'Vegetable', 115, 'High', 'Temperate', 'Loamy', '5.5-6.5', '60x20', '18-22 tons');

-- RICE VARIETIES
INSERT INTO crop_varieties (crop_name, variety_name, crop_type, growth_duration_days, water_requirement, climate_zone, soil_type, ph_range, spacing_cm, yield_per_hectare) VALUES
('Rice', 'Basmati', 'Grain', 120, 'High', 'Tropical', 'Clay', '6.0-7.0', '20x15', '4-5 tons'),
('Rice', 'Sona Masuri', 'Grain', 115, 'High', 'Tropical', 'Clay', '6.0-7.0', '20x15', '5-6 tons'),
('Rice', 'IR64', 'Grain', 110, 'High', 'Tropical', 'Clay', '6.0-7.0', '20x15', '6-7 tons');

-- WHEAT VARIETIES
INSERT INTO crop_varieties (crop_name, variety_name, crop_type, growth_duration_days, water_requirement, climate_zone, soil_type, ph_range, spacing_cm, yield_per_hectare) VALUES
('Wheat', 'HD2967', 'Grain', 150, 'Medium', 'Temperate', 'Loamy', '6.5-7.5', '22x5', '4-5 tons'),
('Wheat', 'PBW343', 'Grain', 145, 'Medium', 'Temperate', 'Loamy', '6.5-7.5', '22x5', '4.5-5.5 tons'),
('Wheat', 'Local', 'Grain', 140, 'Medium', 'Temperate', 'Loamy', '6.5-7.5', '22x5', '3-4 tons');

-- MAIZE VARIETIES
INSERT INTO crop_varieties (crop_name, variety_name, crop_type, growth_duration_days, water_requirement, climate_zone, soil_type, ph_range, spacing_cm, yield_per_hectare) VALUES
('Maize', 'Hybrid', 'Grain', 100, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '75x25', '8-10 tons'),
('Maize', 'Sweet Corn', 'Grain', 85, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '75x25', '6-8 tons'),
('Maize', 'Local', 'Grain', 95, 'Medium', 'Tropical', 'Loamy', '6.0-7.0', '75x25', '5-7 tons');

-- Insert disease information

-- TOMATO DISEASES
INSERT INTO crop_diseases (crop_name, disease_name, disease_type, symptoms, causes, treatment_plan, prevention_methods, severity_level) VALUES
('Tomato', 'Early Blight', 'Fungal', 'Brown spots with concentric rings on lower leaves, yellowing and defoliation', 'Alternaria solani fungus, high humidity, poor air circulation', 'Remove infected leaves, apply copper fungicide (2g/liter), improve air circulation, avoid overhead watering', 'Crop rotation, proper spacing, balanced fertilization, resistant varieties', 'Medium'),
('Tomato', 'Late Blight', 'Fungal', 'Dark water-soaked lesions on leaves and stems, white fungal growth in humid conditions', 'Phytophthora infestans, cool wet weather, poor drainage', 'Remove infected plants, apply copper fungicide, improve drainage, avoid overhead irrigation', 'Resistant varieties, proper spacing, good drainage, fungicide sprays', 'High'),
('Tomato', 'Bacterial Wilt', 'Bacterial', 'Sudden wilting of plants, brown discoloration of vascular tissue', 'Ralstonia solanacearum, contaminated soil, infected transplants', 'Remove infected plants, solarize soil, use disease-free transplants', 'Crop rotation, resistant varieties, clean tools, healthy transplants', 'Critical'),
('Tomato', 'Powdery Mildew', 'Fungal', 'White powdery patches on leaves and stems', 'Oidium lycopersici, high humidity, poor air circulation', 'Apply neem oil (5ml/liter), baking soda solution, improve air circulation', 'Resistant varieties, proper spacing, avoid overhead watering', 'Medium'),
('Tomato', 'Tomato Mosaic Virus', 'Viral', 'Mottled leaves, stunted growth, distorted fruit', 'Tobacco mosaic virus, contaminated tools, infected seeds', 'Remove infected plants, disinfect tools, use virus-free seeds', 'Resistant varieties, clean tools, healthy seeds, control vectors', 'High');

-- POTATO DISEASES
INSERT INTO crop_diseases (crop_name, disease_name, disease_type, symptoms, causes, treatment_plan, prevention_methods, severity_level) VALUES
('Potato', 'Late Blight', 'Fungal', 'Dark lesions on leaves and stems, white fungal growth', 'Phytophthora infestans, cool wet weather', 'Apply copper fungicide, remove infected parts, improve drainage', 'Resistant varieties, proper spacing, fungicide sprays', 'High'),
('Potato', 'Early Blight', 'Fungal', 'Brown spots with concentric rings, defoliation', 'Alternaria solani, high humidity', 'Remove infected leaves, apply fungicide, improve air circulation', 'Crop rotation, balanced fertilization, resistant varieties', 'Medium'),
('Potato', 'Blackleg', 'Bacterial', 'Black lesions on stems, wilting, soft rot', 'Pectobacterium atrosepticum, wet conditions', 'Remove infected plants, improve drainage, use healthy seed', 'Healthy seed potatoes, good drainage, crop rotation', 'High');

-- RICE DISEASES
INSERT INTO crop_diseases (crop_name, disease_name, disease_type, symptoms, causes, treatment_plan, prevention_methods, severity_level) VALUES
('Rice', 'Bacterial Blight', 'Bacterial', 'Yellow lesions on leaves, wilting', 'Xanthomonas oryzae, high humidity', 'Remove infected plants, apply copper bactericide', 'Resistant varieties, balanced fertilization, proper spacing', 'High'),
('Rice', 'Rice Blast', 'Fungal', 'Diamond-shaped lesions on leaves, neck rot', 'Magnaporthe oryzae, high humidity', 'Apply fungicide, remove infected parts', 'Resistant varieties, balanced fertilization, proper timing', 'High'),
('Rice', 'Sheath Blight', 'Fungal', 'Oval lesions on sheaths, grain sterility', 'Rhizoctonia solani, high humidity', 'Apply fungicide, improve air circulation', 'Resistant varieties, proper spacing, balanced fertilization', 'Medium');

-- Insert fertilizer recommendations

-- TOMATO FERTILIZERS
INSERT INTO crop_fertilizers (crop_name, growth_stage, npk_ratio, application_rate, application_method, timing, organic_alternatives) VALUES
('Tomato', 'Seedling', '10-10-10', '50 kg/ha', 'Broadcast and incorporate', 'At transplanting', 'Vermicompost 5 tons/ha, neem cake 250 kg/ha'),
('Tomato', 'Vegetative', '20-20-20', '100 kg/ha', 'Side dressing', '30 days after transplanting', 'Fish emulsion, seaweed extract'),
('Tomato', 'Flowering', '15-15-30', '75 kg/ha', 'Side dressing', 'At flowering stage', 'Banana peel tea, eggshell powder'),
('Tomato', 'Fruiting', '10-10-20', '50 kg/ha', 'Foliar spray', 'During fruit development', 'Molasses solution, bone meal');

-- POTATO FERTILIZERS
INSERT INTO crop_fertilizers (crop_name, growth_stage, npk_ratio, application_rate, application_method, timing, organic_alternatives) VALUES
('Potato', 'Planting', '15-15-15', '200 kg/ha', 'Broadcast and incorporate', 'At planting', 'Farmyard manure 10 tons/ha, bone meal'),
('Potato', 'Vegetative', '20-20-20', '150 kg/ha', 'Side dressing', '30 days after planting', 'Fish emulsion, seaweed extract'),
('Potato', 'Tuber Formation', '10-20-20', '100 kg/ha', 'Side dressing', 'At tuber initiation', 'Wood ash, banana peel tea');

-- RICE FERTILIZERS
INSERT INTO crop_fertilizers (crop_name, growth_stage, npk_ratio, application_rate, application_method, timing, organic_alternatives) VALUES
('Rice', 'Basal', '20-20-20', '150 kg/ha', 'Broadcast', 'At transplanting', 'Green manure, farmyard manure'),
('Rice', 'Tillering', '20-20-20', '100 kg/ha', 'Top dressing', '30 days after transplanting', 'Azolla, duckweed'),
('Rice', 'Panicle Initiation', '10-20-20', '75 kg/ha', 'Top dressing', 'At panicle initiation', 'Fish emulsion, seaweed extract');

-- Insert growth stages

-- TOMATO GROWTH STAGES
INSERT INTO crop_growth_stages (crop_name, stage_name, stage_order, duration_days, description, care_requirements) VALUES
('Tomato', 'Seedling', 1, 15, 'Young plants with 4-6 true leaves', 'Maintain soil moisture, protect from direct sun, apply light fertilizer'),
('Tomato', 'Vegetative Growth', 2, 30, 'Rapid stem and leaf growth', 'Regular watering, balanced fertilizer, support with stakes'),
('Tomato', 'Flowering', 3, 20, 'Flower buds and blossoms appear', 'Reduce nitrogen, increase phosphorus, maintain moisture'),
('Tomato', 'Fruiting', 4, 25, 'Fruit development and ripening', 'Calcium supplement, regular watering, pest monitoring');

-- POTATO GROWTH STAGES
INSERT INTO crop_growth_stages (crop_name, stage_name, stage_order, duration_days, description, care_requirements) VALUES
('Potato', 'Sprouting', 1, 20, 'Emergence of sprouts from seed tubers', 'Maintain soil moisture, protect from frost'),
('Potato', 'Vegetative Growth', 2, 40, 'Stem and leaf development', 'Regular watering, hilling, pest control'),
('Potato', 'Tuber Initiation', 3, 30, 'Tuber formation begins', 'Reduce nitrogen, maintain moisture, hilling'),
('Potato', 'Tuber Development', 4, 30, 'Tuber growth and maturation', 'Reduce watering, monitor for diseases');

-- RICE GROWTH STAGES
INSERT INTO crop_growth_stages (crop_name, stage_name, stage_order, duration_days, description, care_requirements) VALUES
('Rice', 'Seedling', 1, 25, 'Young plants in nursery', 'Maintain water level, protect from pests'),
('Rice', 'Vegetative', 2, 35, 'Tillering and stem elongation', 'Maintain water level, apply nitrogen'),
('Rice', 'Reproductive', 3, 30, 'Panicle initiation and flowering', 'Maintain water level, apply phosphorus'),
('Rice', 'Ripening', 4, 30, 'Grain filling and maturation', 'Gradual water reduction, pest monitoring');

-- Create indexes for better performance
CREATE INDEX idx_crop_varieties_crop_name ON crop_varieties(crop_name);
CREATE INDEX idx_crop_diseases_crop_name ON crop_diseases(crop_name);
CREATE INDEX idx_crop_fertilizers_crop_name ON crop_fertilizers(crop_name);
CREATE INDEX idx_crop_growth_stages_crop_name ON crop_growth_stages(crop_name);

-- Display success message
SELECT 'Crop database setup completed successfully!' as status; 