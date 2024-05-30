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
use TCPDF;

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

$selectedSeats = $_SESSION['selected_seats'];
$detaliiBilete = [];
if (!empty($selectedSeats)) {
    $placeholders = implode(',', array_fill(0, count($selectedSeats), '?'));
    $types = str_repeat('i', count($selectedSeats));
    
    $sqlBilete = "SELECT DetaliiLocuri.Sector, DetaliiLocuri.Rand, DetaliiLocuri.Loc, CategoriiLocuri.Nume_Categorie, CategoriiLocuri.Pret
                FROM DetaliiLocuri
                JOIN CategoriiLocuri ON DetaliiLocuri.CategorieID = CategoriiLocuri.CategorieID
                WHERE DetaliiLocuri.DetaliiLocID IN ($placeholders)";
    
    $stmt = $conn->prepare($sqlBilete);
    $stmt->bind_param($types, ...$selectedSeats);
    $stmt->execute();
    $resultBilete = $stmt->get_result();
    
    while ($row = $resultBilete->fetch_assoc()) {
        $detaliiBilete[] = $row;
    }
    
    $stmt->close();
}

$email = $session->customer_details->email;
$totalPret = $session->amount_total / 100;

// Funcția de generare a PDF-ului
function generateBillPDF($email, $numeEveniment, $totalPret, $customerDetails, $detaliiBilete) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 12);

    // Adăugare logo în colțul stânga sus
    $logo = '../Imagini/LogoDinamoBucuresti.png'; // Calea către logo-ul tău
    $pdf->Image($logo, 10, 10, 20, '', 'PNG'); // X, Y, Width, Height, Image type

    // Datele clientului
    $customerName = $customerDetails['PersoanaContact_Nume'] . ' ' . $customerDetails['PersoanaContact_Prenume'];
    $customerAddress1 = 'Strada ' . $customerDetails['Adresa_Strada'] . ' Nr. ' . $customerDetails['Adresa_Numar'] . 
                        ' Bl. ' . $customerDetails['Adresa_Bloc'] . ' Scara ' . $customerDetails['Adresa_Scara'] . 
                        ' Etaj ' . $customerDetails['Adresa_Etaj'] . ' Ap. ' . $customerDetails['Adresa_Apartament'];
    $customerAddress2 = $customerDetails['Judet'] . ', ' . $customerDetails['Localitate'];

    // Data facturării
    $billingDate = date('d/m/Y');

    // Setare conținut text
    $pdf->SetXY(10, 46); // Mutăm cursorul după logo
    $pdf->Write(0, "Cumpărător:", '', 0, '', true, 0, false, false, 0);
    $pdf->Ln(3); // Adăugăm un spațiu suplimentar sub "Cumpărător:"
    $pdf->Write(0, $customerName, '', 0, '', true, 0, false, false, 0);
    $pdf->Write(0, $customerAddress1, '', 0, '', true, 0, false, false, 0);
    $pdf->Write(0, $customerAddress2, '', 0, '', true, 0, false, false, 0);

    // Mutăm cursorul în partea dreaptă pentru data facturării
    $pdf->SetXY(150, 45);
    $pdf->Write(0, "Data facturării:", '', 0, 'R', true, 0, false, false, 0);
    $pdf->Ln(3); // Adăugăm un spațiu suplimentar sub "Data facturării:"
    $pdf->Write(0, $billingDate, '', 0, 'R', true, 0, false, false, 0);

    // Titlul "Factura #1"
    $pdf->SetFont('dejavusans', 'B', 14);
    $pdf->SetXY(0, 90); // Poziționare pe centru sub detaliile clientului
    $pdf->Cell(0, 10, 'Factura #1', 0, 1, 'C');

    // Tabelul cu 3 coloane: Denumire, Cant., Pret unitar
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->SetXY(10, 110);
    $html = '<table border="1" cellpadding="4">
                <tr style="background-color: #e5e5e5; font-weight: bold;">
                    <th width="60%">Denumire</th>
                    <th width="20%">Cant.</th>
                    <th width="20%">Preț Unitar</th>
                </tr>';
    $pdf->SetFont('dejavusans', '', 12);
    foreach ($detaliiBilete as $bilet) {
        $denumire = $bilet['Nume_Categorie'] . " " . $bilet['Sector'] . " Rand " . $bilet['Rand'] . " Loc " . $bilet['Loc'];
        $cantitate = 1;
        $pretUnitar = $bilet['Pret'];
        $html .= '<tr>
                    <td>' . $denumire . '</td>
                    <td>' . $cantitate . '</td>
                    <td>' . $pretUnitar . ' RON</td>
                </tr>';
    }
    // Adăugăm rânduri goale dacă sunt mai puțin de 8 bilete
    for ($i = count($detaliiBilete); $i < 8; $i++) {
        $html .= '<tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>';
    }
    $html .= '</table>';

    $pdf->writeHTML($html, true, false, false, false, '');

    // Textul "Total de plată" și suma pe același rând sub tabel
    $pdf->SetFont('dejavusans', 'B', 12);
    $pdf->SetXY(10, 185); // Ajustăm coordonatele Y pentru a reduce distanța
    $pdf->Cell(0, 10, 'Total de plată: ' . $totalPret . ' RON', 0, 1, 'R');

    // Textul inclinat la finalul paginii
    $pdf->SetFont('dejavusans', 'I', 10);
    $pdf->SetXY(10, 240); // Setăm cursorul mai sus pentru a încăpea toate informațiile
    $pdf->Write(0, "Factură valabilă fără semnătură și ștampilă cf. art. V. alin (2) din Ordonanța nr.17/2015 și art. 319 alin (29) din Legea nr. 227/2015 privind Codul fiscal.", '', 0, 'L', true, 0, false, false, 0);

    // Setăm fontul normal pentru restul informațiilor
    $pdf->SetFont('dejavusans', '', 10);

    // Adăugăm restul informațiilor pe linii separate
    $pdf->SetXY(10, 254); // Setăm cursorul mai sus pentru a încăpea toate informațiile
    $pdf->Cell(60, 10, 'DINAMO 1948 SA', 0, 0, 'L');
    $pdf->Cell(0, 10, 'Banca Transilvania', 0, 1, 'R');

    $pdf->SetXY(10, 258); // Setăm cursorul mai sus pentru a încăpea toate informațiile
    $pdf->Cell(60, 10, 'Șoseaua Ștefan cel Mare 7-9, Bucureşti', 0, 0, 'L');
    $pdf->Cell(0, 10, 'Telefon: +40 316 406 974', 0, 1, 'R');

    $pdf->SetXY(10, 262); // Setăm cursorul mai sus pentru a încăpea toate informațiile
    $pdf->Cell(60, 10, 'Reg.com.: J40/13568/2004', 0, 0, 'L');
    $pdf->Cell(0, 10, 'IBAN: RO49AAAA1B31007593840000', 0, 1, 'R');

    $pdf->SetXY(10, 266); // Setăm cursorul mai sus pentru a încăpea toate informațiile
    $pdf->Cell(60, 10, 'CIF: RO1609018', 0, 0, 'L');
    $pdf->Cell(0, 10, 'SWIFT: AAAAROBU', 0, 1, 'R');

    $pdf->Output('/tmp/factura.pdf', 'F'); // Save the PDF to /tmp
}

// Obțineți detaliile clientului din baza de date
$sql = "SELECT * FROM DateFacturare WHERE UtilizatorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$customerDetails = $result->fetch_assoc();

// Generare PDF pentru factură
generateBillPDF($email, $numeEveniment, $totalPret, $customerDetails, $detaliiBilete);

// Funcția de generare a biletului PDF
function generateTicketPDF($numeEveniment, $totalPret) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('dejavusans', '', 12);

    // Adăugare logo în colțul stânga sus
    $logo = '../Imagini/LogoDinamoBucuresti.png'; // Calea către logo-ul tău
    $pdf->Image($logo, 10, 10, 50, '', 'PNG'); // X, Y, Width, Height, Image type

    // Setare conținut text
    $pdf->SetXY(10, 60); // Mutăm cursorul după logo
    $pdf->Write(0, "Bilet pentru evenimentul:", '', 0, '', true, 0, false, false, 0);
    $pdf->Write(0, $numeEveniment, '', 0, '', true, 0, false, false, 0);
    $pdf->Write(0, "Suma totală achitată: " . $totalPret . " RON", '', 0, '', true, 0, false, false, 0);

    $pdf->Output('/tmp/bilet.pdf', 'F'); // Save the PDF to /tmp
}

// Generare PDF pentru bilet
generateTicketPDF($numeEveniment, $totalPret);

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
        $mail->Body    = "Vă mulțumim pentru achiziționarea biletelor la evenimentul <b>$numeEveniment</b>. Suma totală achitată este de <b>$totalPret RON</b>.<br><br>Vă așteptăm la eveniment!<br><br>Cu respect,<br>Echipa Dinamo București";

        // Atașează fișiere PDF
        $mail->addAttachment('/tmp/factura.pdf');
        $mail->addAttachment('/tmp/bilet.pdf');

        $mail->send();
        error_log("Email sent successfully to: " . $email);

        // Șterge fișierele PDF după trimiterea emailului
        unlink('/tmp/factura.pdf');
        unlink('/tmp/bilet.pdf');

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