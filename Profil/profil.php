<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

// Verifică rolul utilizatorului și redirecționează adminii către dashboard
if (isset($_SESSION['rol']) && $_SESSION['rol'] == 'admin') {
    header('Location: ../Dashboard/acasa.php');
    exit();
}

include '../db_connect.php';

$error = '';
$success = '';

// Determină partea zilei pentru mesaj
date_default_timezone_set('Europe/Bucharest'); // Setează fusul orar adecvat
$hour = date('G');
if ($hour < 12) {
    $salut = "Bună dimineața";
} elseif ($hour < 18) {
    $salut = "Bună ziua";
} else {
    $salut = "Bună seara";
}

// Extrage numele, prenumele, email-ul și telefonul utilizatorului din baza de date
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT Nume, Prenume, Email, Telefon FROM Utilizatori WHERE UtilizatorID = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $nume = $user['Nume'];
    $prenume = $user['Prenume'];
    $email = $user['Email'];
    $telefon = $user['Telefon'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_nume = mysqli_real_escape_string($conn, $_POST['nume']);
    $new_prenume = mysqli_real_escape_string($conn, $_POST['prenume']);
    $new_email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_telefon = mysqli_real_escape_string($conn, $_POST['telefon']);

    if (empty($new_nume) || empty($new_prenume) || empty($new_email) || empty($new_telefon)) {
        $error = 'Toate câmpurile sunt obligatorii.';
    } elseif ($new_nume == $nume && $new_prenume == $prenume && $new_email == $email && $new_telefon == $telefon) {
        $error = 'Vă rugăm să modificați informațiile înainte de actualizare.';
    } else { 
        // Actualizează datele în baza de date
        $update_query = "UPDATE Utilizatori SET Nume = ?, Prenume = ?, Email = ?, Telefon = ? WHERE UtilizatorID = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssssi", $new_nume, $new_prenume, $new_email, $new_telefon, $user_id);
        if ($update_stmt->execute()) {
            $success = 'Informațiile au fost actualizate cu succes.';
            // Actualizează variabilele pentru a reflecta noile valori
            $nume = $new_nume;
            $prenume = $new_prenume;
            $email = $new_email;
            $telefon = $new_telefon;
        } else {
            $error = 'A apărut o eroare la actualizarea datelor. Te rugăm să încerci din nou.';
        }
    }
}

$conn->close(); // Închide conexiunea la baza de date

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contul meu</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Profil/profil.css">
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
                            <a class="nav-link text-white active" href="/Profil/profil.php">CONTUL MEU</a>
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

    <div class="container">
        <div class="card" style="border-radius: 20px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div class="card-body">
                <!-- Container Flex pentru Salut și Titlu -->
                <div style="display: flex; flex-direction: column; align-items: center; width: 100%;">
                    <div style="width: 100%; text-align: left;">
                        <h4 class="text-center">
                            <?php echo $salut; ?>, <?php echo htmlspecialchars($prenume); ?>!
                        </h4>
                    </div>
                    <div style="width: 100%;" class="text-center">
                        <i class="fas fa-user mb-4 mt-3"></i>
                        <h2 class="text-center">Informații cont</h2>
                    </div>
                </div>
                <form method="POST">
                    <div class="alert-container w-100">
                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                        <?php elseif ($success): ?>
                            <div class="alert alert-success text-center" role="alert"><?php echo $success; ?></div>
                        <?php endif; ?>
                    </div>
                    <!-- Restul formularului rămâne la fel -->
                    <div class="form-group position-relative">
                        <label for="nume" class="float-left ml-1">Nume:</label>
                        <input type="text" class="form-control small-input" name="nume" autocomplete="off" placeholder="Nume" value="<?php echo htmlspecialchars($nume); ?>">
                        <i class="fas fa-fw fa-pencil-alt field-icon" style="color: black;"></i>
                    </div>
                    <div class="form-group position-relative">
                        <label for="prenume" class="float-left ml-1">Prenume:</label>
                        <input type="text" class="form-control small-input" name="prenume" autocomplete="off" placeholder="Prenume" value="<?php echo htmlspecialchars($prenume); ?>">
                        <i class="fas fa-fw fa-pencil-alt field-icon" style="color: black;"></i>
                    </div>
                    <div class="form-group position-relative">
                        <label for="email" class="float-left ml-1">Email:</label>
                        <input type="text" class="form-control small-input" name="email" autocomplete="off" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>">
                        <i class="fas fa-fw fa-pencil-alt field-icon" style="color: black;"></i>
                    </div>
                    <div class="form-group position-relative">
                        <label for="telefon" class="float-left ml-1">Număr de telefon:</label>
                        <input type="tel" class="form-control small-input" name="telefon" autocomplete="off" placeholder="Număr de telefon" value="<?php echo htmlspecialchars($telefon); ?>">
                        <i class="fas fa-fw fa-pencil-alt field-icon" style="color: black;"></i>
                    </div>
                    <button type="submit" class="btn btn-block custom-btn mt-5 pt-2 pb-2">ACTUALIZEAZĂ INFORMAȚIILE</button>
                </form>
            </div>
        </div>
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

    <!-- Script pentru a ascunde mesajele de succes sau de eroare după 3 secunde -->
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 3000);
        });
    </script>

</body>
</html>