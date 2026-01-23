<?php
// backend/tts.php - Text-to-Speech API using SoundOfText
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$text = $_GET['text'] ?? '';
$lang = $_GET['lang'] ?? 'th';

if (empty($text)) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing text parameter']);
    exit;
}

// Create cache folder
$cacheDir = __DIR__ . '/../audio_cache';
if (!file_exists($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

// Generate cache filename based on text hash
$cacheFile = $cacheDir . '/' . md5($text . $lang) . '.mp3';

// Check cache first
if (file_exists($cacheFile)) {
    header('Content-Type: audio/mpeg');
    header('Content-Length: ' . filesize($cacheFile));
    readfile($cacheFile);
    exit;
}

// Use SoundOfText API to get audio
$apiUrl = 'https://api.soundoftext.com/sounds';
$data = [
    'engine' => 'Google',
    'data' => [
        'text' => $text,
        'voice' => 'th-TH'
    ]
];

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    // Fallback: Use Google Translate TTS directly through proxy
    $encodedText = urlencode($text);
    $googleUrl = "https://translate.google.com/translate_tts?ie=UTF-8&tl={$lang}&client=tw-ob&q={$encodedText}";

    $ch = curl_init($googleUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    $audioData = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && strlen($audioData) > 1000) {
        // Save to cache
        file_put_contents($cacheFile, $audioData);

        header('Content-Type: audio/mpeg');
        header('Content-Length: ' . strlen($audioData));
        echo $audioData;
        exit;
    }

    http_response_code(500);
    echo json_encode(['error' => 'TTS service unavailable']);
    exit;
}

$result = json_decode($response, true);
if (!isset($result['id'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Invalid API response']);
    exit;
}

// Wait for audio to be ready
$soundId = $result['id'];
$statusUrl = "https://api.soundoftext.com/sounds/{$soundId}";
$maxRetries = 10;

for ($i = 0; $i < $maxRetries; $i++) {
    usleep(500000); // 0.5 second delay

    $ch = curl_init($statusUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $statusResponse = curl_exec($ch);
    curl_close($ch);

    $status = json_decode($statusResponse, true);

    if (isset($status['status']) && $status['status'] === 'Done' && isset($status['location'])) {
        // Download audio file
        $ch = curl_init($status['location']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $audioData = curl_exec($ch);
        curl_close($ch);

        if (strlen($audioData) > 100) {
            // Save to cache
            file_put_contents($cacheFile, $audioData);

            header('Content-Type: audio/mpeg');
            header('Content-Length: ' . strlen($audioData));
            echo $audioData;
            exit;
        }
    }
}

http_response_code(500);
echo json_encode(['error' => 'Timeout waiting for audio']);
?>