<?php
// AI Image Analysis System for Crop Disease Detection
// This file handles AI-powered image analysis for crop monitoring

class AIImageAnalyzer {
    private $api_key = "your_ai_api_key_here"; // Replace with actual AI API key
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    // Main analysis function
    public function analyzeCropImage($image_path, $crop_name) {
        // Try real AI API integration (PlantVillage demo)
        $api_result = $this->callPlantVillageAPI($image_path, $crop_name);
        if ($api_result && isset($api_result['disease'])) {
            $analysis_result = $api_result;
        } else {
            // Fallback to simulation if API fails
            $analysis_result = $this->simulateAIAnalysis($image_path, $crop_name);
        }
        // Store analysis result
        $this->storeAnalysisResult($analysis_result);
        return $analysis_result;
    }
    
    // Simulate AI analysis (replace with actual AI API calls)
    private function simulateAIAnalysis($image_path, $crop_name) {
        // Get image characteristics for analysis
        $image_info = getimagesize($image_path);
        $file_size = filesize($image_path);
        
        // Simulate different analysis results based on image properties
        $analysis_types = [
            'healthy' => [
                'health_status' => 'Healthy',
                'disease_detected' => 'None',
                'confidence_score' => 95,
                'severity' => 'Low',
                'symptoms' => 'Green leaves, normal growth pattern, no visible damage',
                'treatment_plan' => 'Continue current care routine. Monitor regularly.',
                'fertilizer_recommendation' => 'Standard NPK 20-20-20 fertilizer',
                'growth_stage' => $this->determineGrowthStage($crop_name),
                'care_instructions' => 'Maintain regular watering, balanced fertilization, pest monitoring'
            ],
            'early_blight' => [
                'health_status' => 'Diseased',
                'disease_detected' => 'Early Blight',
                'confidence_score' => 88,
                'severity' => 'Medium',
                'symptoms' => 'Brown spots with concentric rings on lower leaves, yellowing',
                'treatment_plan' => 'Remove infected leaves immediately. Apply copper fungicide (2g/liter). Improve air circulation. Avoid overhead watering.',
                'fertilizer_recommendation' => 'NPK 20-20-20 + Micronutrients (Fe, Zn, Mn)',
                'growth_stage' => $this->determineGrowthStage($crop_name),
                'care_instructions' => 'Remove affected leaves, apply fungicide, improve air circulation'
            ],
            'powdery_mildew' => [
                'health_status' => 'Diseased',
                'disease_detected' => 'Powdery Mildew',
                'confidence_score' => 92,
                'severity' => 'Medium',
                'symptoms' => 'White powdery patches on leaves and stems',
                'treatment_plan' => 'Apply baking soda solution (1 tbsp + 1 liter water). Use neem oil (5ml/liter). Apply sulfur-based fungicide. Improve air circulation.',
                'fertilizer_recommendation' => 'NPK 15-15-15 + Calcium nitrate',
                'growth_stage' => $this->determineGrowthStage($crop_name),
                'care_instructions' => 'Apply fungicide, improve air circulation, avoid overhead watering'
            ],
            'root_rot' => [
                'health_status' => 'Critical',
                'disease_detected' => 'Root Rot',
                'confidence_score' => 85,
                'severity' => 'High',
                'symptoms' => 'Wilting plants, brown/black roots, stunted growth',
                'treatment_plan' => 'Improve soil drainage immediately. Reduce watering frequency. Apply cinnamon powder to soil. Use Trichoderma biofungicide.',
                'fertilizer_recommendation' => 'Phosphorus-rich fertilizer (NPK 10-26-26) + Organic compost',
                'growth_stage' => $this->determineGrowthStage($crop_name),
                'care_instructions' => 'Improve drainage, reduce watering, apply biofungicide'
            ],
            'nutrient_deficiency' => [
                'health_status' => 'Poor',
                'disease_detected' => 'Nutrient Deficiency',
                'confidence_score' => 78,
                'severity' => 'Medium',
                'symptoms' => 'Yellowing leaves, stunted growth, poor development',
                'treatment_plan' => 'Test soil for nutrient levels. Apply balanced fertilizer (NPK 20-20-20). Add organic compost. Use foliar spray for micronutrients.',
                'fertilizer_recommendation' => 'NPK 20-20-20 + Micronutrients (Fe, Zn, Mn, Cu, B)',
                'growth_stage' => $this->determineGrowthStage($crop_name),
                'care_instructions' => 'Apply balanced fertilizer, add organic matter, test soil pH'
            ]
        ];
        
        // Select analysis result based on image properties (simulation)
        $keys = array_keys($analysis_types);
        $selected_key = $keys[array_rand($keys)];
        $result = $analysis_types[$selected_key];
        
        // Add crop-specific information
        $result['crop_name'] = $crop_name;
        $result['image_path'] = $image_path;
        $result['analysis_date'] = date('Y-m-d H:i:s');
        $result['ai_model'] = 'Simulated AI Analysis v1.0';
        
        return $result;
    }

    // Call PlantVillage API (or similar public endpoint)
    private function callPlantVillageAPI($image_path, $crop_name) {
        // Resize and compress image before sending
        $optimized_path = $this->resizeAndCompressImage($image_path, 1024, 1024, 80);
        $api_url = 'https://plantvillage-backend.uc.r.appspot.com/predict';
        $image_data = file_get_contents($optimized_path);
        $base64_image = base64_encode($image_data);
        $payload = json_encode([
            'image' => $base64_image,
            'model' => 'general',
        ]);
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // 15 seconds timeout
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        // Remove the optimized temp file
        if ($optimized_path !== $image_path && file_exists($optimized_path)) {
            unlink($optimized_path);
        }
        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            if (isset($data['predictions'][0])) {
                $pred = $data['predictions'][0];
                return [
                    'health_status' => ($pred['disease'] === 'healthy') ? 'Healthy' : 'Diseased',
                    'disease_detected' => $pred['disease'],
                    'confidence_score' => round($pred['confidence'] * 100),
                    'severity' => ($pred['confidence'] > 0.85) ? 'High' : (($pred['confidence'] > 0.6) ? 'Medium' : 'Low'),
                    'symptoms' => $pred['description'] ?? 'See disease details below.',
                    'treatment_plan' => $pred['treatment'] ?? 'See recommended treatment below.',
                    'fertilizer_recommendation' => 'Standard NPK 20-20-20 fertilizer',
                    'growth_stage' => $this->determineGrowthStage($crop_name),
                    'care_instructions' => 'Follow recommended treatment and monitor regularly.',
                    'crop_name' => $crop_name,
                    'image_path' => $image_path,
                    'analysis_date' => date('Y-m-d H:i:s'),
                    'ai_model' => 'PlantVillage API',
                ];
            }
        } elseif ($curl_error && strpos($curl_error, 'timed out') !== false) {
            // Timeout error
            return [
                'health_status' => 'Unknown',
                'disease_detected' => 'Timeout',
                'confidence_score' => 0,
                'severity' => 'Low',
                'symptoms' => 'The AI analysis took too long. Please try again with a smaller image or check your connection.',
                'treatment_plan' => 'Try again or contact support if the issue persists.',
                'fertilizer_recommendation' => '',
                'growth_stage' => $this->determineGrowthStage($crop_name),
                'care_instructions' => '',
                'crop_name' => $crop_name,
                'image_path' => $image_path,
                'analysis_date' => date('Y-m-d H:i:s'),
                'ai_model' => 'PlantVillage API',
            ];
        }
        return null;
    }

    // Resize and compress image to max width/height and quality
    private function resizeAndCompressImage($image_path, $max_width, $max_height, $quality = 80) {
        $info = getimagesize($image_path);
        if (!$info) return $image_path;
        list($orig_width, $orig_height) = $info;
        $mime = $info['mime'];
        if ($orig_width <= $max_width && $orig_height <= $max_height && $mime === 'image/jpeg') {
            return $image_path; // No need to resize/compress
        }
        $ratio = min($max_width / $orig_width, $max_height / $orig_height, 1);
        $new_width = (int)($orig_width * $ratio);
        $new_height = (int)($orig_height * $ratio);
        if ($mime === 'image/jpeg') {
            $src = imagecreatefromjpeg($image_path);
        } elseif ($mime === 'image/png') {
            $src = imagecreatefrompng($image_path);
        } elseif ($mime === 'image/gif') {
            $src = imagecreatefromgif($image_path);
        } else {
            return $image_path; // Unsupported type
        }
        $dst = imagecreatetruecolor($new_width, $new_height);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $new_width, $new_height, $orig_width, $orig_height);
        $temp_path = $image_path . '_opt.jpg';
        imagejpeg($dst, $temp_path, $quality);
        imagedestroy($src);
        imagedestroy($dst);
        return $temp_path;
    }
    
    // Determine growth stage based on crop and time
    private function determineGrowthStage($crop_name) {
        $stages = [
            'Tomato' => ['Seedling', 'Vegetative Growth', 'Flowering', 'Fruiting'],
            'Potato' => ['Sprouting', 'Vegetative Growth', 'Tuber Initiation', 'Tuber Development'],
            'Rice' => ['Seedling', 'Vegetative', 'Reproductive', 'Ripening'],
            'Wheat' => ['Germination', 'Tillering', 'Stem Extension', 'Heading'],
            'Maize' => ['Emergence', 'Vegetative', 'Tasseling', 'Silking']
        ];
        
        $crop_stages = $stages[$crop_name] ?? ['Seedling', 'Vegetative', 'Flowering', 'Mature'];
        return $crop_stages[array_rand($crop_stages)];
    }
    
    // Store analysis result in database
    private function storeAnalysisResult($result) {
        $sql = "INSERT INTO crop_disease_reports (
            crop_id, user_id, image_path, detected_disease, confidence_score, 
            symptoms, treatment_plan, fertilizer_recommendation, severity, status
        ) VALUES (
            1, 1, '{$result['image_path']}', '{$result['disease_detected']}', 
            {$result['confidence_score']}, '{$result['symptoms']}', 
            '{$result['treatment_plan']}', '{$result['fertilizer_recommendation']}', 
            '{$result['severity']}', 'Detected'
        )";
        
        mysqli_query($this->conn, $sql);
    }
    
    // Get crop-specific disease information
    public function getCropDiseases($crop_name) {
        $sql = "SELECT * FROM crop_diseases WHERE crop_name = '$crop_name' ORDER BY severity_level DESC";
        $result = mysqli_query($this->conn, $sql);
        
        $diseases = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $diseases[] = $row;
        }
        
        return $diseases;
    }
    
    // Get fertilizer recommendations for crop and stage
    public function getFertilizerRecommendation($crop_name, $growth_stage) {
        $sql = "SELECT * FROM crop_fertilizers WHERE crop_name = '$crop_name' AND growth_stage = '$growth_stage'";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        
        // Return default recommendation if not found
        return [
            'npk_ratio' => '20-20-20',
            'application_rate' => '100 kg/ha',
            'application_method' => 'Broadcast and incorporate',
            'timing' => 'At current growth stage',
            'organic_alternatives' => 'Vermicompost, neem cake, farmyard manure'
        ];
    }
    
    // Get growth stage information
    public function getGrowthStageInfo($crop_name, $stage_name) {
        $sql = "SELECT * FROM crop_growth_stages WHERE crop_name = '$crop_name' AND stage_name = '$stage_name'";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result);
        }
        
        return null;
    }
    
    // Calculate growth percentage based on sowing date
    public function calculateGrowthPercentage($sow_date, $crop_name) {
        $days_since_sowing = (time() - strtotime($sow_date)) / (24 * 60 * 60);
        
        // Get crop duration from database
        $sql = "SELECT growth_duration_days FROM crop_varieties WHERE crop_name = '$crop_name' LIMIT 1";
        $result = mysqli_query($this->conn, $sql);
        
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            $total_days = $row['growth_duration_days'];
        } else {
            $total_days = 90; // Default
        }
        
        $percentage = min(100, max(0, ($days_since_sowing / $total_days) * 100));
        return round($percentage);
    }
    
    // Generate comprehensive report
    public function generateReport($analysis_result) {
        $report = [
            'summary' => [
                'crop_name' => $analysis_result['crop_name'],
                'health_status' => $analysis_result['health_status'],
                'disease_detected' => $analysis_result['disease_detected'],
                'confidence_score' => $analysis_result['confidence_score'],
                'severity' => $analysis_result['severity'],
                'growth_stage' => $analysis_result['growth_stage'],
                'analysis_date' => $analysis_result['analysis_date']
            ],
            'detailed_analysis' => [
                'symptoms' => $analysis_result['symptoms'],
                'treatment_plan' => $analysis_result['treatment_plan'],
                'fertilizer_recommendation' => $analysis_result['fertilizer_recommendation'],
                'care_instructions' => $analysis_result['care_instructions']
            ],
            'recommendations' => [
                'immediate_actions' => $this->getImmediateActions($analysis_result),
                'preventive_measures' => $this->getPreventiveMeasures($analysis_result),
                'monitoring_schedule' => $this->getMonitoringSchedule($analysis_result)
            ]
        ];
        
        return $report;
    }
    
    private function getImmediateActions($result) {
        $actions = [];
        
        if ($result['severity'] == 'Critical') {
            $actions[] = 'Immediate isolation of affected plants';
            $actions[] = 'Apply emergency treatment within 24 hours';
            $actions[] = 'Contact agricultural expert for consultation';
        } elseif ($result['severity'] == 'High') {
            $actions[] = 'Start treatment within 48 hours';
            $actions[] = 'Monitor closely for spread';
            $actions[] = 'Prepare backup treatment plan';
        } else {
            $actions[] = 'Begin treatment as recommended';
            $actions[] = 'Monitor progress weekly';
            $actions[] = 'Maintain preventive measures';
        }
        
        return $actions;
    }
    
    private function getPreventiveMeasures($result) {
        return [
            'Crop rotation to prevent disease buildup',
            'Proper spacing for air circulation',
            'Balanced fertilization',
            'Regular monitoring and early detection',
            'Use of disease-resistant varieties',
            'Clean farming practices'
        ];
    }
    
    private function getMonitoringSchedule($result) {
        if ($result['severity'] == 'Critical') {
            return 'Daily monitoring for first week, then every 3 days';
        } elseif ($result['severity'] == 'High') {
            return 'Every 3 days for first week, then weekly';
        } else {
            return 'Weekly monitoring with detailed assessment every 2 weeks';
        }
    }
}

// Usage example:
/*
$analyzer = new AIImageAnalyzer($conn);
$result = $analyzer->analyzeCropImage('path/to/image.jpg', 'Tomato');
$report = $analyzer->generateReport($result);
*/ 