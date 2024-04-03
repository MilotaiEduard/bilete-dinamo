<?php

session_start();

require '../vendor/autoload.php';
include '../db_connect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
}

$token = bin2hex(random_bytes(50));
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Sterge orice token existent pentru acest email
$sql = "DELETE FROM ResetareParola WHERE Email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();

// Insereaza noul token
$sql = "INSERT INTO ResetareParola (Email, Token, Expira) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $email, $token, $expira);
$stmt->execute();

$mail = new PHPMailer(true);

try {
    // Setari server
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'edi.milotai@gmail.com'; // SMTP username
    $mail->Password = 'gxlv ysnd yrbg jcvf'; // SMTP password
    $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587; // Port pentru TLS/STARTTLS
    $mail->CharSet = 'UTF-8';

    // Destinatari
    $mail->setFrom('no-reply@bilete-dinamo.com', 'no-reply@bilete-dinamo.com');
    $mail->addAddress($email); // Adauga destinatarul

    // Content
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Resetarea parolei contului dumneavoastră';
    $mail->Body    = "Pentru a reseta parola, accesați următorul link în următoarea oră: <a href='http://localhost/ResetareParola/pagina_resetare_parola.php?token=" . $token . "'>Resetare parola</a><br>Dacă nu ați solicitat resetarea parolei, vă rugăm să ignorați acest email.";

    $mail->send();
    $_SESSION['success'] = 'Instrucțiunile pentru resetarea parolei au fost trimise la adresa de email specificată.';
} catch (Exception $e) {
    $_SESSION['error'] = 'A apărut o eroare la trimiterea emailului. Vă rugăm să încercați din nou. ' . $mail->ErrorInfo;
}

header('Location: /ResetareParola/resetare_parola.php');
exit();

?>
