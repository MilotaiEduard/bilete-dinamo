<?php

session_start();

// Verifică dacă utilizatorul este autentificat
if (!isset($_SESSION['user_id'])) {
    header('Location: /Autentificare/autentificare.php');
    exit();
}

// Verifică rolul utilizatorului
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
    header('Location: ../MeniuPrincipal/meniu_principal.php');
    exit();
}

include '../db_connect.php';

// Obține ID-ul utilizatorului din sesiune
$user_id = $_SESSION['user_id'];

// Interogare pentru a obține numele utilizatorului
$sql = "SELECT Nume FROM Utilizatori WHERE UtilizatorID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nume_admin);
$stmt->fetch();
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rapoarte</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="/Dashboard/rapoarte.css">
</head>

<body class="<?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
    <div class="sidebar <?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
        <div class="top-section">
            <img src="../Imagini/LogoDinamoBucuresti.png" alt="Logo">
            <h4>Bine ai venit, <b><?php echo htmlspecialchars($nume_admin); ?></b>!</h4>
            <a href="/Dashboard/acasa.php"><i class="fas fa-home"></i> Acasă</a>
            <a href="/Dashboard/inregistrare_eveniment.php"><i class="fas fa-calendar-alt"></i> Eveniment Nou</a>
            <a href="/Dashboard/rapoarte.php" class="active"><i class="fas fa-chart-bar"></i> Rapoarte</a>
            <a href="/Dashboard/grafice.php"><i class="fas fa-chart-line"></i> Grafice</a>
        </div>
        <div class="bottom-section">
            <a href="#" id="mode-toggle"><i class="fas fa-sun" id="mode-icon"></i> Schimbare mod</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Deconectare</a>
        </div>
    </div>
    <div class="content">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><b>Generați un raport:</b></h5>
                <form id="reportForm" action="generare_raport.php" method="post">
                    <div class="form-check text-left">
                        <input class="form-check-input" type="radio" name="reportType" id="vanzariBilete" value="vanzariBilete">
                        <label class="form-check-label" for="vanzariBilete">Raport Vânzări Bilete</label>
                    </div>
                    <div class="form-check text-left">
                        <input class="form-check-input" type="radio" name="reportType" id="disponibilitateBilete" value="disponibilitateBilete">
                        <label class="form-check-label" for="disponibilitateBilete">Raport Disponibilitate Bilete</label>
                    </div>
                    <div class="form-check text-left">
                        <input class="form-check-input" type="radio" name="reportType" id="performantaEveniment" value="performantaEveniment">
                        <label class="form-check-label" for="performantaEveniment">Raport Performanță Eveniment</label>
                    </div>
                    <div class="form-check text-left">
                        <input class="form-check-input" type="radio" name="reportType" id="venituri" value="venituri">
                        <label class="form-check-label" for="venituri">Raport Venituri</label>
                    </div>
                    <div class="form-check text-left">
                        <input class="form-check-input" type="radio" name="reportType" id="utilizatori" value="utilizatori">
                        <label class="form-check-label" for="utilizatori">Raport Utilizatori</label>
                    </div>
                    <div class="form-check text-left">
                        <input class="form-check-input" type="radio" name="reportType" id="facturi" value="facturi">
                        <label class="form-check-label" for="facturi">Raport Facturi</label>
                    </div>
                    <button type="submit" class="btn custom-btn">Generează Raport</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        document.getElementById('mode-toggle').addEventListener('click', function() {
            document.body.classList.toggle('dark-mode');
            document.querySelector('.sidebar').classList.toggle('dark-mode');
            
            // Change the icon
            var icon = document.getElementById('mode-icon');
            if (document.body.classList.contains('dark-mode')) {
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
            } else {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }

            // Save the mode in a cookie
            var theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';
            document.cookie = "theme=" + theme + ";path=/";
        });

        // Apply the mode from the cookie
        window.addEventListener('load', function() {
            if (document.cookie.split(';').some((item) => item.trim().startsWith('theme='))) {
                var theme = document.cookie.split(';').find(item => item.trim().startsWith('theme=')).split('=')[1];
                if (theme === 'dark') {
                    document.body.classList.add('dark-mode');
                    document.querySelector('.sidebar').classList.add('dark-mode');
                    document.getElementById('mode-icon').classList.remove('fa-sun');
                    document.getElementById('mode-icon').classList.add('fa-moon');
                }
            }
        });

        // Validare formular și afișare alertă cu SweetAlert
        document.getElementById('reportForm').addEventListener('submit', function(event) {
            var reportType = document.querySelector('input[name="reportType"]:checked');
            if (!reportType) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare',
                    text: 'Vă rugăm să selectați un tip de raport înainte de a continua!',
                    confirmButtonText: 'OK'
                });
            }
        });
    </script>
    
</body>
</html>
