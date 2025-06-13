<?php
$botToken = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";
$chatId = "6602027873";
$text = "Test notification: Backend online!";
file_get_contents("https://api.telegram.org/bot$botToken/sendMessage?chat_id=$chatId&text=" . urlencode($text));
?>
