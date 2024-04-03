<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include '../db_connect.php';

// Inițializează variabila pentru mesaje de eroare
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $parola_noua = $_POST['parola_noua'];
    $confirma_parola = $_POST['confirma_parola'];

    if (empty($parola_noua) && empty($confirma_parola)) {
        $error = 'Ambele câmpuri sunt obligatorii.';
    } elseif (empty($parola_noua)) {
        $error = 'Câmpul pentru parola nouă este obligatoriu.';
    } elseif (empty($confirma_parola)) {
        $error = 'Câmpul pentru confirmarea parolei este obligatoriu.';
    } elseif ($parola_noua != $confirma_parola) {
        $error = 'Parolele introduse nu coincid.';
    } elseif (strlen($parola_noua) < 5) {
        $error = 'Parola trebuie să aibă cel puțin 5 caractere.';
    } else {
        // Încearcă să găsești un utilizator cu tokenul specificat
        $sql = "SELECT Email FROM ResetareParola WHERE Token = ? AND Expira > NOW()";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();
                $email = $row['Email'];

                // Actualizează parola în tabela Utilizatori
                $parola_hash = password_hash($parola_noua, PASSWORD_DEFAULT);
                $sqlUpdate = "UPDATE Utilizatori SET Parola = ? WHERE Email = ?";
                if ($stmtUpdate = $conn->prepare($sqlUpdate)) {
                    $stmtUpdate->bind_param("ss", $parola_hash, $email);
                    $stmtUpdate->execute();

                /*
                    // Șterge înregistrările expirate și tokenul folosit
                    $sqlDelete = "DELETE FROM ResetareParola WHERE Email = ? OR Expira < NOW()";
                    if ($stmtDelete = $conn->prepare($sqlDelete)) {
                        $stmtDelete->bind_param("s", $email);
                        $stmtDelete->execute();
                    } 
                */

                    $_SESSION['success'] = 'Parola a fost resetată cu succes. Redirecționare...';
                    header("Location: pagina_resetare_parola.php?token=$token");
                    exit();
                } else {
                    $error = "Eroare la actualizarea parolei.";
                }
            } else {
                $error = 'Token invalid sau expirat.';
            }
            $stmt->close();
        } else {
            $error = "Eroare la executarea interogării.";
        }
    }

    if (!empty($error)) {
        $_SESSION['error'] = $error;
        header("Location: pagina_resetare_parola.php?token=$token");
        exit();
    }
}
?>
