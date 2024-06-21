<?php

session_start();

include '../db_connect.php';

// Setează locale pentru limba română
setlocale(LC_TIME, 'ro_RO.UTF-8');

// Obține datele celui mai recent eveniment
$sql = "SELECT Sezon_Eveniment, Nume_Eveniment, Data_Eveniment, Locatie_Eveniment, Logo_Echipa_Oaspete FROM Evenimente ORDER BY EvenimentID DESC LIMIT 1"; 
$result = $conn->query($sql);

$eveniment_disponibil = false;

if ($result->num_rows > 0) {
    // Preia datele evenimentului
    $row = $result->fetch_assoc();
    $sezonEveniment = $row['Sezon_Eveniment'];
    $numeEveniment = $row['Nume_Eveniment'];
    $dataEveniment = strftime('%d %B %Y - %H:%M', strtotime($row['Data_Eveniment'])); // Formatul datei în limba română
    $locatieEveniment = $row['Locatie_Eveniment'];
    $logoEchipaOaspete = $row['Logo_Echipa_Oaspete'];
    $dataEvenimentTimestamp = strtotime($row['Data_Eveniment']);

    // Verifică dacă data evenimentului este în viitor
    if ($dataEvenimentTimestamp >= time()) {
        $eveniment_disponibil = true;
    }
} 

if (!$eveniment_disponibil) {
    // Setează valori implicite dacă nu există niciun eveniment disponibil
    $sezonEveniment = "";
    $numeEveniment = "Niciun eveniment disponibil";
    $dataEveniment = "";
    $locatieEveniment = "";
    $logoEchipaOaspete = "";
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meniu Principal</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/MeniuPrincipal/meniu_principal.css">
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

    <div class="container mt-5">
        <?php if ($eveniment_disponibil): ?>
            <div class="card mx-auto" style="max-width: 840px; height: 650px;">
                <div class="card-header text-center">
                    <?php echo htmlspecialchars($sezonEveniment); ?>
                </div>
                <div class="card-body">
                    <div class="card-content text-center mb-3">
                        <h5 class="card-title text-white"><?php echo htmlspecialchars($numeEveniment); ?></h5>
                        <p class="card-text text-white"><?php echo htmlspecialchars($locatieEveniment); ?></p>
                        <p class="card-text text-white"><?php echo htmlspecialchars($dataEveniment); ?></p>
                    </div>
                    <div>
                        <img src="/Imagini/LogoDinamoBucuresti.png" alt="Dinamo Bucuresti" class="img-fluid home-team">
                        <img src="<?php echo htmlspecialchars($logoEchipaOaspete); ?>" alt="Echipa oaspete" class="img-fluid away-team">
                    </div>
                </div>
                <div class="card-footer">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/SelectareLocuri/selectare_locuri.php" class="btn btn-block custom-btn">CUMPĂRĂ BILET</a>
                    <?php else: ?>
                        <a href="/Autentificare/autentificare.php" class="btn btn-block custom-btn">CUMPĂRĂ BILET</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card mx-auto" style="max-width: 840px; height: 650px;">
                <div class="card-body text-center">
                    <h5 class="card-title text-white">Momentan nu există niciun eveniment disponibil.</h5>
                </div>
            </div>
        <?php endif; ?>
    </div>

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
