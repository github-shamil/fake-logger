<?php
// === Get Visitor IP ===
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

// === Collect IP and Geo Info ===
$ip = get_client_ip();
$geo = json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=status,message,country,regionName,city,district,lat,lon,query"), true);

if ($geo['status'] !== 'success') {
    http_response_code(500);
    echo "Failed to get geolocation";
    exit;
}

// === Extract Info ===
$country = $geo['country'] ?? '';
$region  = $geo['regionName'] ?? '';
$city    = $geo['city'] ?? '';
$town    = $geo['district'] ?? ''; // Narath, etc.
$lat     = $geo['lat'] ?? '';
$lon     = $geo['lon'] ?? '';
$ip      = $geo['query'] ?? $ip;
$time    = date("Y-m-d H:i:s");

// === Save to log.txt ===
$log_line = "$time | $ip | $country | $region | $city | $town | $lat,$lon\n";
file_put_contents("log.txt", $log_line, FILE_APPEND);

// === Save to SQLite ===
try {
    $db = new SQLite3("log.db");
    $db->exec("CREATE TABLE IF NOT EXISTS visitors (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        ip TEXT,
        country TEXT,
        region TEXT,
        city TEXT,
        town TEXT,
        lat TEXT,
        lon TEXT,
        timestamp TEXT
    )");

    $stmt = $db->prepare("INSERT INTO visitors (ip, country, region, city, town, lat, lon, timestamp)
                          VALUES (:ip, :country, :region, :city, :town, :lat, :lon, :timestamp)");
    $stmt->bindValue(':ip', $ip);
    $stmt->bindValue(':country', $country);
    $stmt->bindValue(':region', $region);
    $stmt->bindValue(':city', $city);
    $stmt->bindValue(':town', $town);
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lon', $lon);
    $stmt->bindValue(':timestamp', $time);
    $stmt->execute();

} catch (Exception $e) {
    http_response_code(500);
    echo "Database error";
    exit;
}

// === Optional Output ===
echo "Logged successfully";
?>
