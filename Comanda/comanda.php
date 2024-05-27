<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

// Verifică dacă locurile selectate sunt setate în sesiune
if (!isset($_SESSION['selected_seats']) || empty($_SESSION['selected_seats'])) {
    header('Location: ../MeniuPrincipal/meniu_principal.php');
    exit();
}

include '../db_connect.php';

// Afișează locurile selectate
$selectedSeats = $_SESSION['selected_seats'];

// PreluareA ultimului eveniment adăugat
$sql = "SELECT Nume_Eveniment, Data_Eveniment, Locatie_Eveniment FROM Evenimente ORDER BY EvenimentID DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Preia datele evenimentului
    $row = $result->fetch_assoc();
    $numeEveniment = $row['Nume_Eveniment'];
    $dataEveniment = $row['Data_Eveniment'];
    $locatieEveniment = $row['Locatie_Eveniment'];
} else {
    $numeEveniment = "Nu există evenimente disponibile.";
    $dataEveniment = "";
    $locatieEveniment = "";
}

// Preluare detalii bilete selectate
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



$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comanda</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Comanda/comanda.css">
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
            <div class="step-circle active"><span>2</span></div>
            <div class="step-label">COMANDA</div>
        </div>
        <div class="step">
            <div class="step-circle"><span>3</span></div>
            <div class="step-label">FINALIZARE</div>
        </div>
    </div>

    <h2 class="mt-5 mb-5">Comanda ta</h2>

    <h5 class="mt-4"><?php echo htmlspecialchars($numeEveniment); ?></h5>
    <div class="d-flex date-and-place">
        <p><i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($dataEveniment)); ?></p>
        <p><i class="fas fa-map-marker-alt ml-4"></i> <?php echo htmlspecialchars($locatieEveniment); ?></p>
    </div>

    <div class="table-responsive mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>BILETE</th>
                    <th>LOC</th>
                    <th class="last-th">VALOARE BILETE</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detaliiBilete as $bilet): ?>
                    <tr>
                        <td><?php echo "1 x " . htmlspecialchars($bilet['Nume_Categorie']) . " - " . htmlspecialchars($bilet['Pret']) . " RON"; ?></td>
                        <td><?php echo htmlspecialchars($bilet['Nume_Categorie']) . ", " . htmlspecialchars($bilet['Sector']) . ", Rand " . htmlspecialchars($bilet['Rand']) . ", Loc " . htmlspecialchars($bilet['Loc']); ?></td>
                        <td class="text-right"><?php echo htmlspecialchars($bilet['Pret']) . " RON"; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-4 float-right total-price">
        <h4>Total: <?php echo number_format($totalPret, 2); ?> RON</h4>
    </div>

    <div class="date-facturare">
        <h5 class="date-facturare-header">Date facturare</h5>
    </div>

    <form id="formular-facturare" action="procesare_facturare.php" method="post" class="mt-3">
        <div class="form-group">
            <label for="contact-name" class="mb-3">Persoana de contact</label>
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" id="contact-nume" name="contact_nume" autocomplete="off" placeholder="Nume">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="contact-prenume" name="contact_prenume" autocomplete="off" placeholder="Prenume">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="contact-telefon">Telefon</label>
            <input type="tel" class="form-control" id="contact-telefon" name="contact_telefon" autocomplete="off" placeholder="Număr de telefon">
        </div>
        <div class="form-group">
            <label for="adresa-facturare" class="mb-3">Adresa de facturare</label>
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" id="adresa-strada" name="adresa_strada" autocomplete="off" placeholder="Strada">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-numar" name="adresa_numar" autocomplete="off" placeholder="Număr">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-bloc" name="adresa_bloc" autocomplete="off" placeholder="Bloc">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-scara" name="adresa_scara" autocomplete="off" placeholder="Scara">
                </div>
            </div>
            <div class="form-row mt-2">
                <div class="col">
                    <input type="text" class="form-control" id="adresa-etaj" name="adresa_etaj" autocomplete="off" placeholder="Etaj">
                </div>
                <div class="col">
                    <input type="text" class="form-control" id="adresa-apartament" name="adresa_apartament" autocomplete="off" placeholder="Apartament">
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="adresa-judet">Județ</label>
            <select class="form-control" id="adresa-judet" name="adresa_judet">
                <!-- Opțiuni vor fi adăugate ulterior -->
            </select>
        </div>
        <div class="form-group">
            <label for="adresa-localitate">Localitate</label>
            <select class="form-control" id="adresa-localitate" name="adresa_localitate">
                <!-- Opțiuni vor fi adăugate ulterior -->
            </select>
        </div>
    </form>

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