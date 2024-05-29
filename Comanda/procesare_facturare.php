<?php

session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$stripeSecretKey = $_ENV['STRIPE_SECRET_KEY'];

\Stripe\Stripe::setApiKey($stripeSecretKey);

if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

if (empty($_POST['contact_nume']) || empty($_POST['contact_prenume']) || empty($_POST['contact_telefon']) || empty($_POST['adresa_strada']) || empty($_POST['adresa_numar']) || empty($_POST['adresa_bloc']) || empty($_POST['adresa_scara']) || empty($_POST['adresa_etaj']) || empty($_POST['adresa_apartament']) || empty($_POST['adresa_judet']) || empty($_POST['adresa_localitate']) || empty($_POST['modalitate_plata'])) {
    $_SESSION['error'] = 'Toate câmpurile sunt obligatorii.';
    header('Location: comanda.php');
    exit();
}

$contactTelefon = $_POST['contact_telefon'];
if (strlen($contactTelefon) < 7 || strlen($contactTelefon) > 15 || !ctype_digit($contactTelefon)) {
    $_SESSION['error'] = 'Formatul numărului de telefon este incorect.';
    header('Location: comanda.php');
    exit();
}

include '../db_connect.php';

$sql = "SELECT Nume_Eveniment FROM Evenimente ORDER BY EvenimentID DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Preia numele evenimentului
    $row = $result->fetch_assoc();
    $numeEveniment = $row['Nume_Eveniment'];
} else {
    $numeEveniment = "Nu există evenimente disponibile.";
}

// Preluare detalii bilete selectate
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

    // Calculul totalului prețurilor biletelor
    $totalPret = 0;
    foreach ($detaliiBilete as $bilet) {
        $totalPret += $bilet['Pret'];
    }
    
    $stmt->close();
}

$contactNume = ucfirst(strtolower(trim($_POST['contact_nume'])));
$contactPrenume = ucfirst(strtolower(trim($_POST['contact_prenume'])));
$adresaStrada = ucfirst(strtolower(trim($_POST['adresa_strada'])));
$adresaNumar = trim($_POST['adresa_numar']);
$adresaBloc = trim($_POST['adresa_bloc']);
$adresaScara = ucfirst(trim($_POST['adresa_scara']));
$adresaEtaj = trim($_POST['adresa_etaj']);
$adresaApartament = trim($_POST['adresa_apartament']);
$adresaJudet = trim($_POST['adresa_judet']);
$adresaLocalitate = trim($_POST['adresa_localitate']);

$sql = "SELECT * FROM DateFacturare WHERE UtilizatorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$existingData = $result->fetch_assoc();

function redirectToStripe($numeEveniment, $totalPret) {
    try {
        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'ron',
                    'product_data' => [
                        'name' => $numeEveniment,
                    ],
                    'unit_amount' => $totalPret * 100,  // Suma totală
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'locale' => 'ro',
            'success_url' => 'http://localhost/Finalizare/success.php?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'http://localhost/Comanda/comanda.php',
        ]);
        header('Location: ' . $checkout_session->url);
    } catch (Exception $e) {
        error_log("Eroare Stripe: " . $e->getMessage());
        $_SESSION['error'] = 'Eroare la crearea sesiunii Stripe: ' . $e->getMessage();
        header('Location: comanda.php');
    }
    exit();
}

if ($existingData) {
    if ($existingData['PersoanaContact_Nume'] === $contactNume &&
        $existingData['PersoanaContact_Prenume'] === $contactPrenume &&
        $existingData['Telefon'] === $contactTelefon &&
        $existingData['Adresa_Strada'] === $adresaStrada &&
        $existingData['Adresa_Numar'] === $adresaNumar &&
        $existingData['Adresa_Bloc'] === $adresaBloc &&
        $existingData['Adresa_Scara'] === $adresaScara &&
        $existingData['Adresa_Etaj'] === $adresaEtaj &&
        $existingData['Adresa_Apartament'] === $adresaApartament &&
        $existingData['Judet'] === $adresaJudet &&
        $existingData['Localitate'] === $adresaLocalitate) {
            $_SESSION['can_access_finalizare'] = true;
            redirectToStripe($numeEveniment, $totalPret);
    } else {
        $sql = "UPDATE DateFacturare SET PersoanaContact_Nume = ?, PersoanaContact_Prenume = ?, Telefon = ?, Adresa_Strada = ?, Adresa_Numar = ?, Adresa_Bloc = ?, Adresa_Scara = ?, Adresa_Etaj = ?, Adresa_Apartament = ?, Judet = ?, Localitate = ? WHERE UtilizatorID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssi', $contactNume, $contactPrenume, $contactTelefon, $adresaStrada, $adresaNumar, $adresaBloc, $adresaScara, $adresaEtaj, $adresaApartament, $adresaJudet, $adresaLocalitate, $_SESSION['user_id']);
        if ($stmt->execute()) {
            $_SESSION['can_access_finalizare'] = true;
            redirectToStripe($numeEveniment, $totalPret);
        } else {
            $_SESSION['error'] = 'A apărut o problemă la actualizarea datelor de facturare. Te rugăm să încerci din nou.';
            header('Location: comanda.php');
            exit();
        }
    }
} else {
    $sql = "INSERT INTO DateFacturare (PersoanaContact_Nume, PersoanaContact_Prenume, Telefon, Adresa_Strada, Adresa_Numar, Adresa_Bloc, Adresa_Scara, Adresa_Etaj, Adresa_Apartament, Judet, Localitate, UtilizatorID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssssi', $contactNume, $contactPrenume, $contactTelefon, $adresaStrada, $adresaNumar, $adresaBloc, $adresaScara, $adresaEtaj, $adresaApartament, $adresaJudet, $adresaLocalitate, $_SESSION['user_id']);
    if ($stmt->execute()) {
        $_SESSION['can_access_finalizare'] = true;
        redirectToStripe($numeEveniment, $totalPret);
    } else {
        $_SESSION['error'] = 'A apărut o problemă la procesarea facturării. Te rugăm să încerci din nou.';
        header('Location: comanda.php');
        exit();
    }
}

?>
