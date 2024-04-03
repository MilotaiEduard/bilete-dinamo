<?php

session_start();

include '../db_connect.php';

if (!empty($_GET['token'])) {
    $token = $_GET['token'];

    // Șterge tokenul specificat
    $sqlDelete = "DELETE FROM ResetareParola WHERE Token = ?";
    if ($stmtDelete = $conn->prepare($sqlDelete)) {
        $stmtDelete->bind_param("s", $token);
        if ($stmtDelete->execute()) {
            echo "Token șters cu succes.";
        } else {
            echo "Eroare la ștergerea tokenului.";
        }
        $stmtDelete->close();
    } else {
        echo "Nu s-a putut pregăti interogarea pentru ștergerea tokenului.";
    }
} else {
    echo "Token invalid.";
}

?>
