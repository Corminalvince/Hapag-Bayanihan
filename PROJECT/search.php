<?php
// search.php
$apiKey = "YOUR_GOOGLE_MAPS_API_KEY"; // <-- Replace with your API Key

$userLat = isset($_GET['latitude']) ? floatval($_GET['latitude']) : null;
$userLng = isset($_GET['longitude']) ? floatval($_GET['longitude']) : null;
$address = isset($_GET['address']) ? htmlspecialchars($_GET['address']) : '';

if (!$userLat || !$userLng) {
    die("Location not provided.");
}

// Google Places Nearby Search API URL
$url = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=$userLat,$userLng&radius=5000&type=restaurant&keyword=carinderia&key=$apiKey";

// Initialize cURL request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

// Decode API response
$data = json_decode($response, true);
$results = $data['results'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Nearby Restaurants & Carinderias</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: orange; }
        .place { padding: 10px; border-bottom: 1px solid #ccc; }
        .place .address { color: #555; font-size: 0.9em; }
    </style>
</head>
<body>

<h1>Nearby food places near "<?php echo $address; ?>"</h1>

<?php if (!empty($results)): ?>
    <?php foreach ($results as $place): ?>
        <div class="place">
            <strong><?php echo htmlspecialchars($place['name']); ?></strong><br>
            <span class="address">
                <?php echo isset($place['vicinity']) ? htmlspecialchars($place['vicinity']) : 'Address not available'; ?>
            </span>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No nearby food establishments found.</p>
<?php endif; ?>

</body>
</html>
