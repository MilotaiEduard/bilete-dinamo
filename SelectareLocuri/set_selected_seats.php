<?php

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedSeats = isset($_POST['seats']) ? $_POST['seats'] : [];
    $_SESSION['selected_seats'] = $selectedSeats;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

?>
