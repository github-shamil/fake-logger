<?php
date_default_timezone_set('Asia/Kolkata');

$ip = $_SERVER['REMOTE_ADDR'];
$details = json_decode(file_get_contents("http://ip-api.com/json/$ip?fields=status,country,regionName,city,district,zip,lat,lon,isp,query"), true);

if ($details['status'] !== 'success') {
    $details = [
        'country' => 'Unknown',
        'regionName' => 'Unknown',
        'city' => 'Unknown',
        'district' => 'Unknown',
        'zip' => 'Unknown',
        'lat' => '0.0',
        'lon' => '0.0',
        'isp' => 'Unknown',
        'query' => $ip
    ];
}

$log = "IP: " . $ip . " | Country: " . $details['country'] . " | Region: " . $details['regionName'] .
       " | City: " . $details['city'] . " | District: " . $details['district'] .
       " | ZIP: " . $details['zip'] . " | Lat: " . $details['lat'] . " | Lon: " . $details['lon'] .
       " | ISP: " . $details['isp'] . " | Time: " . date("d-m-Y H:i:s") . "\n";

file_put_contents("log.txt", $log, FILE_APPEND);

$db = new SQLite3("log.db");
$db->exec("CREATE TABLE IF NOT EXISTS logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT,
    country TEXT,
    region TEXT,
    city TEXT,
    zip TEXT,
    lat TEXT,
    lon TEXT,
    isp TEXT,
    time TEXT
)");

$stmt = $db->prepare("INSERT INTO logs (ip, country, region, city, zip, lat, lon, isp, time)
                      VALUES (:ip, :country, :region, :city, :zip, :lat, :lon, :isp, :time)");

$stmt->bindValue(':ip', $ip);
$stmt->bindValue(':country', $details['country']);
$stmt->bindValue(':region', $details['regionName']);
$stmt->bindValue(':city', $details['city']);
$stmt->bindValue(':zip', $details['zip']);
$stmt->bindValue(':lat', $details['lat']);
$stmt->bindValue(':lon', $details['lon']);
$stmt->bindValue(':isp', $details['isp']);
$stmt->bindValue(':time', date("d-m-Y H:i:s"));

$stmt->execute();

echo "📍 Visitor tracked successfully.";
?>
