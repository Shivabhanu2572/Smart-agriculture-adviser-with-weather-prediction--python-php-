<?php
session_start();
// Database connection (reuse from other modules)
$conn = mysqli_connect("localhost", "root", "", "smart_agri");
$equipment = [];
if ($conn) {
    $res = mysqli_query($conn, "SELECT * FROM equipment ORDER BY id DESC");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $equipment[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Agricultural Equipment Rental</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Montserrat', Arial, sans-serif;
            background: #f4f8f3;
            margin: 0;
            padding: 0;
            color: #222;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 18px 6px 8px 6px;
        }
        h1 {
            text-align: center;
            color: #217a2b;
            margin-bottom: 8px;
            font-size: 1.4rem;
        }
        .subtitle {
            text-align: center;
            color: #555;
            margin-bottom: 32px;
            font-size: 0.98rem;
        }
        .equipment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(270px, 1fr));
            gap: 32px;
        }
        .equipment-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px #217a2b18;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.18s, box-shadow 0.18s;
            margin: 8px 0;
        }
        .equipment-card:hover {
            transform: translateY(-4px) scale(1.03);
            box-shadow: 0 8px 32px #217a2b33;
        }
        .equipment-image {
            width: 100%;
            height: 110px;
            object-fit: cover;
        }
        .equipment-content {
            padding: 10px 8px 8px 8px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .equipment-name {
            font-size: 1rem;
            font-weight: 700;
            color: #217a2b;
            margin-bottom: 6px;
        }
        .equipment-desc {
            font-size: 0.92rem;
            color: #444;
            margin-bottom: 12px;
        }
        .equipment-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .price {
            color: #14532d;
            font-weight: 700;
            font-size: 0.98rem;
        }
        .availability {
            font-size: 0.92rem;
            font-weight: 600;
            color: #fff;
            background: #217a2b;
            border-radius: 6px;
            padding: 2px 10px;
            display: inline-block;
        }
        .not-available {
            background: #b91c1c;
        }
        .dealer-link {
            margin-top: auto;
            display: block;
            background: #217a2b;
            color: #ffe066;
            text-align: center;
            padding: 7px 0;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            font-size: 0.98rem;
            transition: background 0.18s, color 0.18s;
        }
        .dealer-link:hover {
            background: #14532d;
            color: #fffbe6;
        }
        @media (max-width: 700px) {
            .container { padding: 18px 4px; }
            h1 { font-size: 1.3rem; }
            .subtitle { font-size: 0.98rem; }
            .equipment-image { height: 120px; }
        }
    </style>
</head>
<body>
<div style="width:100%;display:flex;align-items:center;justify-content:flex-start;margin-bottom:18px;">
    <a href="dashboard.php" style="margin:18px 0 0 18px;display:inline-block;background:#217a2b;color:#ffe066;padding:10px 22px;border-radius:8px;font-weight:700;font-size:1rem;text-decoration:none;box-shadow:0 2px 8px #14532d22;transition:background 0.2s,box-shadow 0.2s,transform 0.2s;">
        <i class="fa-solid fa-arrow-left"></i> Dashboard
    </a>
</div>
<div style="max-width:900px;margin:0 auto 18px auto;padding:0 8px;">
    <div style="background:#ffe066;color:#14532d;border-radius:8px;padding:10px 18px;font-weight:600;font-size:1.02rem;box-shadow:0 2px 8px #ffe06644;display:flex;align-items:center;gap:10px;">
        <i class="fa-solid fa-circle-info" style="color:#217a2b;"></i>
        Please book equipment at least <b>2 days in advance</b> to ensure availability.
    </div>
</div>
<div class="container">
    <h1><i class="fa-solid fa-tractor"></i> Equipment Rental</h1>
    <div class="subtitle">Find and book modern agricultural equipment for your farm. Contact dealers directly for booking.</div>
    <div class="equipment-grid">
        <?php if (count($equipment) === 0): ?>
            <div style="grid-column: 1/-1; text-align:center; color:#888; font-size:1.1rem; padding:40px 0;">No equipment available at the moment.</div>
        <?php else: ?>
            <?php foreach ($equipment as $item): ?>
                <div class="equipment-card">
                    <img class="equipment-image" src="<?= htmlspecialchars($item['image_url']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                    <div class="equipment-content">
                        <div class="equipment-name"> <?= htmlspecialchars($item['name']) ?> </div>
                        <div class="equipment-desc"> <?= htmlspecialchars($item['description']) ?> </div>
                        <div class="equipment-info">
                            <span class="price">&#8377;<?= number_format($item['price']) ?>/day</span>
                            <span class="availability<?= $item['available'] ? '' : ' not-available' ?>">
                                <?= $item['available'] ? 'Available' : 'Not Available' ?>
                            </span>
                        </div>
                        <a class="dealer-link" href="<?= htmlspecialchars($item['dealer_link']) ?>" target="_blank" <?= $item['available'] ? '' : 'style="pointer-events:none;opacity:0.6;"' ?>>
                            <i class="fa-solid fa-phone"></i> Book with Dealer
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
</body>
</html> 