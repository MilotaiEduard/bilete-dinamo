<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

include '../db_connect.php';

if (!isset($_GET['sector'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Sectorul este necesar']);
    exit();
}

$sector = $_GET['sector'];
error_log("Sector received: " . $sector); // Log pentru a verifica sectorul primit

// Verifică conexiunea la baza de date
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$sql = "SELECT d.DetaliiLocID, d.Sector, d.Rand, d.Loc, d.Disponibilitate, c.Nume_Categorie, c.Pret 
        FROM DetaliiLocuri d
        LEFT JOIN CategoriiLocuri c ON d.CategorieID = c.CategorieID
        WHERE d.Sector = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'Prepare failed']);
    exit();
}

$stmt->bind_param("s", $sector);
if (!$stmt->execute()) {
    error_log("Execute failed: " . $stmt->error);
    echo json_encode(['error' => 'Execute failed']);
    exit();
}

$result = $stmt->get_result();

$locuri = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $locuri[] = $row;
    }
    error_log("Data found: " . json_encode($locuri)); // Log pentru a verifica datele returnate
} else {
    error_log("No data found for sector: " . $sector); // Log pentru a verifica dacă nu există date
}

$stmt->close();
$conn->close();
echo json_encode($locuri);

?>
