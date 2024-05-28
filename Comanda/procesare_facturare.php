<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

// Verifică dacă toate câmpurile au fost completate
if (empty($_POST['contact_nume']) || empty($_POST['contact_prenume']) || empty($_POST['contact_telefon']) || empty($_POST['adresa_strada']) || empty($_POST['adresa_numar']) || empty($_POST['adresa_bloc']) || empty($_POST['adresa_scara']) || empty($_POST['adresa_etaj']) || empty($_POST['adresa_apartament']) || empty($_POST['adresa_judet']) || empty($_POST['adresa_localitate']) || empty($_POST['modalitate_plata'])) {
    $_SESSION['error'] = 'Toate câmpurile sunt obligatorii.';
    header('Location: comanda.php');
    exit();
}

// Verifică formatul numărului de telefon
$contactTelefon = $_POST['contact_telefon'];
if (strlen($contactTelefon) < 7 || strlen($contactTelefon) > 15 || !ctype_digit($contactTelefon)) {
    $_SESSION['error'] = 'Formatul numărului de telefon este incorect.';
    header('Location: comanda.php');
    exit();
}

include '../db_connect.php';

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

// Selectează datele de facturare existente pentru utilizatorul curent
$sql = "SELECT * FROM DateFacturare WHERE UtilizatorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$existingData = $result->fetch_assoc();

if ($existingData) {
    // Verifică dacă datele introduse sunt aceleași cu cele existente
    if (
        $existingData['PersoanaContact_Nume'] === $contactNume &&
        $existingData['PersoanaContact_Prenume'] === $contactPrenume &&
        $existingData['Telefon'] === $contactTelefon &&
        $existingData['Adresa_Strada'] === $adresaStrada &&
        $existingData['Adresa_Numar'] === $adresaNumar &&
        $existingData['Adresa_Bloc'] === $adresaBloc &&
        $existingData['Adresa_Scara'] === $adresaScara &&
        $existingData['Adresa_Etaj'] === $adresaEtaj &&
        $existingData['Adresa_Apartament'] === $adresaApartament &&
        $existingData['Judet'] === $adresaJudet &&
        $existingData['Localitate'] === $adresaLocalitate
    ) {
        // Datele există deja, redirecționează utilizatorul la pagina următoare
        header('Location: ../Finalizare/finalizare.php');
        exit();
    } else {
        // Datele sunt diferite, actualizează în baza de date
        $sql = "UPDATE DateFacturare SET PersoanaContact_Nume = ?, PersoanaContact_Prenume = ?, Telefon = ?, Adresa_Strada = ?, Adresa_Numar = ?, Adresa_Bloc = ?, Adresa_Scara = ?, Adresa_Etaj = ?, Adresa_Apartament = ?, Judet = ?, Localitate = ? WHERE UtilizatorID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssssi', $contactNume, $contactPrenume, $contactTelefon, $adresaStrada, $adresaNumar, $adresaBloc, $adresaScara, $adresaEtaj, $adresaApartament, $adresaJudet, $adresaLocalitate, $_SESSION['user_id']);
        
        if ($stmt->execute()) {
            header('Location: ../Finalizare/finalizare.php');
            exit();
        } else {
            $_SESSION['error'] = 'A apărut o problemă la actualizarea datelor de facturare. Te rugăm să încerci din nou.';
            header('Location: comanda.php');
            exit();
        }
    }
} else {
    // Inserează datele doar dacă nu există deja
    $sql = "INSERT INTO DateFacturare (PersoanaContact_Nume, PersoanaContact_Prenume, Telefon, Adresa_Strada, Adresa_Numar, Adresa_Bloc, Adresa_Scara, Adresa_Etaj, Adresa_Apartament, Judet, Localitate, UtilizatorID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssssi', $contactNume, $contactPrenume, $contactTelefon, $adresaStrada, $adresaNumar, $adresaBloc, $adresaScara, $adresaEtaj, $adresaApartament, $adresaJudet, $adresaLocalitate, $_SESSION['user_id']);

    if ($stmt->execute()) {
        header('Location: ../Finalizare/finalizare.php');
        exit();
    } else {
        $_SESSION['error'] = 'A apărut o problemă la procesarea facturării. Te rugăm să încerci din nou.';
        header('Location: comanda.php');
        exit();
    }
}

?>

