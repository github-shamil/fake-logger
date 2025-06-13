<?php
// Sends a custom message to Telegram

function sendTelegram($msg) {
    $chat_id = "6602027873";
    $bot_token = "7943375930:AAEiifo4A9NiuxY13o73qjCJVUiHXEu2ta8";

    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $params = [
        "chat_id" => $chat_id,
        "text" => $msg,
        "parse_mode" => "Markdown"
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded",
            'method'  => 'POST',
            'content' => http_build_query($params)
        ]
    ];

    $context  = stream_context_create($options);
    return file_get_contents($url, false, $context);
}

// Optional test message
if ($_GET['test'] ?? false) {
    echo sendTelegram("🔔 Telegram test successful!");
}
?>
