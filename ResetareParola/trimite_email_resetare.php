<<?php

session_start(); // Începe sesiunea

include '../db_connect.php'; // Conectarea la baza de date

$email = mysqli_real_escape_string($conn, trim($_POST['email']));

// Verifică dacă emailul există în baza de date
$sql = "SELECT Email FROM Utilizatori WHERE Email = '$email'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    // Dacă emailul nu există, setează un mesaj de eroare și redirecționează înapoi la formular
    $_SESSION['error'] = 'Emailul nu a fost găsit în baza de date.';
    header('Location: /ResetareParola/resetare_parola.php');
    exit();
} else {
    $token = bin2hex(random_bytes(50)); // Generează un token unic
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour")); // Setează expirarea tokenului la 1 oră de la momentul generării

    // Inserează informațiile în tabelul ResetareParola
    $sql = "INSERT INTO ResetareParola (Email, Token, Expira) VALUES ('$email', '$token', '$expira')";
    $result = mysqli_query($conn, $sql);

    if ($result) {
        // Trimite emailul de resetare
        $to = $email;
        $subject = 'Resetarea parolei contului dumneaovoastră';
        $message = 'Pentru a reseta parola, accesați următorul link: http://localhost/ResetareParola/pagina_resetare_parola.php?token=' . $token;
        $headers = 'From: no-reply@bilete-dinamo.com';

        mail($to, $subject, $message, $headers);
    }

}

mysqli_close($conn);
?>

