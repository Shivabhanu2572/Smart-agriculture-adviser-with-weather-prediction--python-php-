<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$conn = mysqli_connect("localhost", "root", "", "smart_agri");

require_once 'ai_image_analyzer.php';
$analyzer = new AIImageAnalyzer($conn);

$report_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($report_id <= 0) {
    header("Location: ai_crop_monitor.php");
    exit();
}

// Fetch the report
$sql = "SELECT dr.*, cm.crop_name FROM crop_disease_reports dr JOIN ai_crop_monitoring cm ON dr.crop_id = cm.id WHERE dr.id = $report_id AND dr.user_id = $user_id";
$result = mysqli_query($conn, $sql);
if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: ai_crop_monitor.php");
    exit();
}
$report = mysqli_fetch_assoc($result);

$feedback = '';
$show_form = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actions = mysqli_real_escape_string($conn, $_POST['actions']);
    $improvement = $_POST['improvement'];
    $details = mysqli_real_escape_string($conn, $_POST['details']);
    $status = $improvement === 'yes' ? 'Recovering' : 'Treatment Started';
    $notes = "Actions: $actions\n" . ($improvement === 'yes' ? "Improvements: $details" : "New/Worsening Symptoms: $details");

    // Optional: handle new image upload
    if (isset($_FILES['new_image']) && $_FILES['new_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/crop_analysis/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['new_image']['name'], PATHINFO_EXTENSION);
        $file_name = 'analysis_update_' . time() . '_' . $user_id . '.' . $file_extension;
        $image_path = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['new_image']['tmp_name'], $image_path)) {
            // Optionally re-run AI analysis
            $analysis_result = $analyzer->analyzeCropImage($image_path, $report['crop_name']);
            // Update report with new analysis
            $update_sql = "UPDATE crop_disease_reports SET image_path='$image_path', detected_disease='{$analysis_result['disease_detected']}', confidence_score='{$analysis_result['confidence_score']}', symptoms='{$analysis_result['symptoms']}', treatment_plan='{$analysis_result['treatment_plan']}', fertilizer_recommendation='{$analysis_result['fertilizer_recommendation']}', severity='{$analysis_result['severity']}', status='$status', notes='$notes' WHERE id=$report_id AND user_id=$user_id";
            mysqli_query($conn, $update_sql);
            $report = array_merge($report, $analysis_result, ['image_path' => $image_path, 'status' => $status, 'notes' => $notes]);
        }
    } else {
        // Just update status and notes
        $update_sql = "UPDATE crop_disease_reports SET status='$status', notes='$notes' WHERE id=$report_id AND user_id=$user_id";
        mysqli_query($conn, $update_sql);
        $report['status'] = $status;
        $report['notes'] = $notes;
    }
    $show_form = false;
    // Dynamic, science-based feedback
    if ($improvement === 'yes') {
        $feedback = "<strong>Great! Your crop is showing improvement.</strong><br>Continue monitoring for any new symptoms. Maintain good cultural practices: proper irrigation, balanced fertilization, and regular field scouting. If symptoms reappear, repeat recommended treatment or consult an expert.";
    } else {
        // Show advanced/next-level scientific advice based on disease
        $disease = $report['detected_disease'];
        $crop = $report['crop_name'];
        $treatment = $report['treatment_plan'];
        $fertilizer = $report['fertilizer_recommendation'];
        $feedback = "<strong>Your crop has not improved yet.</strong><br>";
        $feedback .= "<b>Please repeat the following scientifically recommended steps:</b><ul>";
        $steps = explode('.', $treatment);
        foreach ($steps as $step) {
            $step = trim($step);
            if (!empty($step)) {
                $feedback .= "<li>" . htmlspecialchars($step) . ".</li>";
            }
        }
        $feedback .= "</ul>";
        $feedback .= "<b>Fertilizer Recommendation:</b> " . htmlspecialchars($fertilizer) . "<br>";
        $feedback .= "<b>Tip:</b> If there is still no improvement after following these steps, consider consulting a local agricultural extension officer or expert for further guidance.";
    }
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Status - Crop Disease Report</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f1f8e9; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 20px rgba(51,105,30,0.10); padding: 32px; }
        h2 { color: #33691e; margin-bottom: 18px; }
        .form-group { margin-bottom: 18px; }
        label { font-weight: 600; color: #33691e; display: block; margin-bottom: 7px; }
        input[type="text"], textarea, select { width: 100%; padding: 10px; border: 1.5px solid #c5e1a5; border-radius: 7px; font-size: 1rem; background: #f9fff0; }
        textarea { min-height: 60px; }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-size: 15px; font-weight: 600; cursor: pointer; background: linear-gradient(90deg,#43a047 70%,#388e3c 100%); color: #fff; margin-top: 10px; }
        .btn:hover { background: #2e7d32; }
        .feedback { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 18px; border-radius: 8px; margin-top: 24px; color: #1976d2; font-size: 1.08rem; }
        .back-link { display: inline-block; margin-top: 18px; color: #33691e; text-decoration: none; font-weight: 600; }
        .back-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fas fa-edit"></i> Update Crop Disease Status</h2>
        <p><strong>Crop:</strong> <?php echo htmlspecialchars($report['crop_name']); ?><br>
        <strong>Disease:</strong> <?php echo htmlspecialchars($report['detected_disease']); ?><br>
        <strong>Current Status:</strong> <?php echo htmlspecialchars($report['status']); ?></p>
        <?php if ($show_form): ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="actions">What actions/treatments did you apply?</label>
                <textarea name="actions" id="actions" required placeholder="Describe the treatment, fertilizer, or other actions you took..."></textarea>
            </div>
            <div class="form-group">
                <label>Did you observe any improvement?</label>
                <select name="improvement" id="improvement" required onchange="toggleDetails()">
                    <option value="">Select</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                </select>
            </div>
            <div class="form-group" id="details-group" style="display:none;">
                <label id="details-label">Please provide details:</label>
                <textarea name="details" id="details" required placeholder="Describe improvements or new/worsening symptoms..."></textarea>
            </div>
            <div class="form-group">
                <label for="new_image">Upload a new image (optional, for re-analysis):</label>
                <input type="file" name="new_image" id="new_image" accept="image/*">
            </div>
            <button type="submit" class="btn"><i class="fas fa-save"></i> Submit Update</button>
        </form>
        <script>
        function toggleDetails() {
            var imp = document.getElementById('improvement').value;
            var group = document.getElementById('details-group');
            var label = document.getElementById('details-label');
            if (imp === 'yes') {
                group.style.display = '';
                label.textContent = 'What improvements did you notice?';
            } else if (imp === 'no') {
                group.style.display = '';
                label.textContent = 'What new or worsening symptoms did you see?';
            } else {
                group.style.display = 'none';
            }
        }
        </script>
        <?php else: ?>
            <div class="feedback"><?php echo $feedback; ?></div>
            <a href="disease_certificate.php?id=<?php echo $report_id; ?>" class="back-link"><i class="fas fa-arrow-left"></i> Back to Certificate</a>
        <?php endif; ?>
    </div>
</body>
</html>
<?php
// --- Helper for advanced advice ---
function getAdvancedTreatmentAdvice($disease, $crop, $details) {
    // Example: add more scientific escalation here
    $advice = "Please ensure you are following all recommended steps. If symptoms persist, consider the following advanced actions:<ul>";
    if (stripos($disease, 'blight') !== false) {
        $advice .= "<li>Apply a systemic fungicide (e.g., azoxystrobin or mancozeb) as per label instructions.</li>";
        $advice .= "<li>Remove and destroy all infected plant material.</li>";
        $advice .= "<li>Improve field drainage and avoid overhead irrigation.</li>";
    } elseif (stripos($disease, 'mildew') !== false) {
        $advice .= "<li>Use sulfur-based or potassium bicarbonate fungicides.</li>";
        $advice .= "<li>Increase air circulation and reduce humidity.</li>";
    } elseif (stripos($disease, 'rot') !== false) {
        $advice .= "<li>Apply Trichoderma-based biofungicides.</li>";
        $advice .= "<li>Remove affected roots and improve soil aeration.</li>";
    } elseif (stripos($disease, 'bacterial') !== false) {
        $advice .= "<li>Apply copper-based bactericides.</li>";
        $advice .= "<li>Disinfect tools and avoid working in wet fields.</li>";
    } else {
        $advice .= "<li>Consult a local agricultural extension officer for expert advice.</li>";
    }
    $advice .= "</ul>Monitor closely and document any further changes.";
    return $advice;
}
?> 