<?php
$db = new SQLite3("log.db");
$result = $db->query("SELECT * FROM logs");
?>

<h2>📋 Visitor IP Logs</h2>
<table border="1">
  <tr>
    <th>IP</th><th>Country</th><th>Region</th><th>City</th>
    <th>ZIP</th><th>Lat</th><th>Lon</th><th>ISP</th><th>Time</th>
  </tr>
  <?php while ($row = $result->fetchArray()) { ?>
    <tr>
      <td><?= $row['ip'] ?></td>
      <td><?= $row['country'] ?></td>
      <td><?= $row['region'] ?></td>
      <td><?= $row['city'] ?></td>
      <td><?= $row['zip'] ?></td>
      <td><?= $row['lat'] ?></td>
      <td><?= $row['lon'] ?></td>
      <td><?= $row['isp'] ?></td>
      <td><?= $row['time'] ?></td>
    </tr>
  <?php } ?>
</table>
