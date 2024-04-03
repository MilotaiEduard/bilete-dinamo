<?php

session_start();

include '../db_connect.php';

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Adresa de email introdusă nu este validă.';
    header('Location: /ResetareParola/resetare_parola.php');
    exit();
}

$sql = "SELECT Email FROM Utilizatori WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $_SESSION['error'] = 'Email-ul nu a fost găsit în baza de date.';
    header('Location: /ResetareParola/resetare_parola.php');
    exit();
} else {
    $token = bin2hex(random_bytes(50));
    $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Verifică dacă există deja un token activ și îl actualizează sau inserează unul nou
    $sql = "DELETE FROM ResetareParola WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $sql = "INSERT INTO ResetareParola (Email, Token, Expira) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $token, $expira);
    $stmt->execute();

    // Trimite emailul de resetare
    $to = $email;
    $subject = 'Resetarea parolei contului dumneavoastră';
    $message = "Pentru a reseta parola, accesați următorul link în următoarea oră: \n\nhttp://localhost/ResetareParola/pagina_resetare_parola.php?token=" . $token . "\n\nDacă nu ați solicitat resetarea parolei, vă rugăm să ignorați acest email.";
    $headers = 'From: no-reply@bilete-dinamo.com';

    if (mail($to, $subject, $message, $headers)) {
        $_SESSION['success'] = 'Instrucțiunile pentru resetarea parolei au fost trimise la adresa de email specificată.';
    } else {
        $_SESSION['error'] = 'A apărut o eroare la trimiterea emailului. Vă rugăm să încercați din nou.';
    }

    header('Location: /ResetareParola/resetare_parola.php');
    exit();
}

mysqli_close($conn);

?>
