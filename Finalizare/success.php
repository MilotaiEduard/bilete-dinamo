<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

if ($_SESSION['can_access_finalizare'] != true || !isset($_SESSION['can_access_finalizare'])) {
    header('Location: ../MeniuPrincipal/meniu_principal.php');
    exit();
}

include '../db_connect.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Stripe\Stripe;
use Stripe\Checkout\Session as CheckoutSession;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];
\Stripe\Stripe::setApiKey($stripeSecretKey);

if (!isset($_GET['session_id'])) {
    die('Missing session ID.');
}

$session_id = $_GET['session_id'];

try {
    $session = CheckoutSession::retrieve($session_id);
} catch (Exception $e) {
    die('Error retrieving Stripe session: ' . $e->getMessage());
}

$sql = "SELECT Nume_Eveniment FROM Evenimente ORDER BY EvenimentID DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Preia numele evenimentului
    $row = $result->fetch_assoc();
    $numeEveniment = $row['Nume_Eveniment'];
} else {
    $numeEveniment = "Nu există evenimente disponibile.";
}

$email = $session->customer_details->email;
$totalPret = $session->amount_total / 100;

// Funcția de trimitere a emailului
function sendConfirmationEmail($email, $numeEveniment, $totalPret) {
    $mail = new PHPMailer(true);

    try {
        // Configurări server
        $mail->isSMTP();
        $mail->Host       = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['SMTP_USERNAME'];
        $mail->Password   = $_ENV['SMTP_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet = 'UTF-8';

        // Destinatari
        $mail->setFrom($_ENV['SMTP_FROM'], $_ENV['SMTP_FROM_NAME']);
        $mail->addAddress($email);

        // Conținut
        $mail->isHTML(true);
        $mail->Subject = 'Confirmarea tranzacției';
        $mail->Body    = "Bună ziua,<br><br>Vă mulțumim pentru achiziționarea biletelor la evenimentul <b>$numeEveniment</b>. Suma totală achitată este de <b>$totalPret RON</b>.<br><br>Vă așteptăm la eveniment!<br><br>Cu respect,<br>Echipa Dinamo București";

        $mail->send();
        error_log("Email sent successfully to: " . $email);
    } catch (Exception $e) {
        error_log("Mesajul nu a putut fi trimis. Eroare Mailer: {$mail->ErrorInfo}");
    }
}

// Apelează funcția de trimitere a emailului
sendConfirmationEmail($email, $numeEveniment, $totalPret);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tranzacție efectuată cu succes</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Finalizare/success.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid position-relative">
            <a class="navbar-brand logo-outside" href="/MeniuPrincipal/meniu_principal.php">
                <img src="/Imagini/LogoDinamoBucuresti.png" alt="Logo" height="100" width="90">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fas fa-bars text-black"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item mr-4">
                        <a class="nav-link text-white" href="../InformatiiUtile/informatii_utile.php">INFORMAȚII UTILE</a>
                    </li>
                    <!-- Verifică dacă utilizatorul este autentificat și afișează link-uri corespunzătoare -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item mr-4">
                            <a class="nav-link text-white" href="/Profil/profil.php">CONTUL MEU</a>
                        </li>
                        <li class="nav-item mr-3">
                            <a class="nav-link text-white" href="../logout.php">DECONECTEAZĂ-TE</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mr-3">
                            <a class="nav-link text-white" href="/Autentificare/autentificare.php">INTRĂ ÎN CONT</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="step-progress">
        <div class="step">
            <div class="step-circle done"><span><i class="fas fa-check"></i></span></div>
            <div class="step-label">SELECTARE LOCURI</div>
        </div>
        <div class="step">
            <div class="step-circle done"><span><i class="fas fa-check"></i></span></div>
            <div class="step-label">COMANDA</div>
        </div>
        <div class="step">
            <div class="step-circle active">3</div>
            <div class="step-label">FINALIZARE</div>
        </div>
    </div>

    <h2 class="mt-5">Tranzacția a fost efectuată cu succes!</h2>
    <p class="success-p">Vă mulțumim pentru achiziționarea biletelor!</p>
    <p class="success-p">Urmează să primiți confirmarea și pe email la adresa specificată, alături de bilete și de factura fiscală. Vă așteptăm la eveniment!</p>

    <img src="../Imagini/SuccessfulTransaction.png" alt="Tranzacție efectuată cu succes" class="success-image mt-4" width="400" height="400">

    <footer class="footer-custom">
        <div class="footer-container">
            <div class="row" style="margin-right: 0px;">
                <div class="col-md-5 footer-left">
                    <a href="../Legal/politica_confidentialitate.html" target="_blank">Politica de confidențialitate</a> | 
                    <a href="../Legal/contact.html" target="_blank">Contact</a>
                    <p class="p-copyrights">Dinamo 1948 București <i class="far fa-copyright"></i> 2024. Toate drepturile sunt rezervate.</p>
                </div>
                <div class="col-md-5 footer-right">
                    <a href="https://ec.europa.eu/consumers/odr" target="_blank" style="margin-right: 0px; margin-top: 20px;">
                        <img src="/Imagini/solutionare_online_litigii.png" alt="Solutionarea online a litigiilor" height="50" class="first-image">
                    </a>
                    <a href="https://anpc.ro/ce-este-sal/" target="_blank">
                        <img src="/Imagini/solutionare_alternativa_litigii.png" alt="Solutionarea alternativa a litigiilor" height="50" class="second-image">
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.navbar-toggler').click(function () {
                // Schimbă clasa iconiței la click
                var icon = $(this).find('i'); // Găsește elementul <i> din interiorul butonului
                if (icon.hasClass('fa-bars')) {
                    icon.removeClass('fa-bars').addClass('fa-times'); // Schimbă în X
                } else {
                    icon.removeClass('fa-times').addClass('fa-bars'); // Schimbă înapoi în hamburger
                }
            });
        });
    </script>

</body>
</html>