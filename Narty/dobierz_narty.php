<?php
header('Content-Type: application/json');


$wzrost = isset($_POST['wzrost']) ? (int)$_POST['wzrost'] : 0;
$waga = isset($_POST['waga']) ? (int)$_POST['waga'] : 0;
$poziom = $_POST['poziom'] ?? '';
$styl = $_POST['styl'] ?? '';

if ($wzrost < 100 || $wzrost > 220) {
    echo json_encode(['error' => 'Nieprawidłowy wzrost.']);
    exit;
}

// Jakaś czarna magia
$procent = 0.9;
if ($styl === 'rekreacyjny') $procent = 0.85;
if ($styl === 'sportowy') $procent = 0.95;
if ($styl === 'freeride') $procent = 1.0;
$dlugosc = round($wzrost * $procent);

// Połączenie z bazą danych
$conn = new mysqli(hostname: "localhost", username: "root", password: "", database: "narty_db");
if ($conn->connect_error) {
    echo json_encode(['error' => 'Błąd połączenia z bazą danych.']);
    exit;
}

$sql = "SELECT model, dlugosc FROM narty ORDER BY ABS(dlugosc - ?) ASC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $dlugosc);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $model = $row['model'];
    $dlugosc_baza = $row['dlugosc'];
    // model
    $filename = 'img/' . preg_replace('/[^A-Za-z0-9_\-]/', '', $model) . '.jpg';
    if (!file_exists($filename)) {
        $filename = 'img/default.jpg'; // domyślne zdjęcie jeśli brak pliku
    }
    $img_url = $filename;
} else {
    echo json_encode(['error' => 'Brak odpowiednich nart w bazie.']);
    exit;
}

echo json_encode([
    'model' => $model,
    'dlugosc' => $dlugosc_baza,
    'poziom' => $poziom,
    'waga' => $waga,
    'styl' => $styl,
    'img' => $img_url
]);
$conn->close();
?>