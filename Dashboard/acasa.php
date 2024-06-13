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

$totalUtilizatori = $conn->query("SELECT COUNT(*) FROM Utilizatori WHERE Rol='user'")->fetch_row()[0];
$totalEvenimente = $conn->query("SELECT COUNT(*) FROM Evenimente")->fetch_row()[0];
$totalBileteVandute = $conn->query("SELECT COUNT(*) FROM Vanzari")->fetch_row()[0];
$totalVenituri = $conn->query("SELECT SUM(Suma_Platita) FROM Plati")->fetch_row()[0];
$totalFacturiEmise = $conn->query("SELECT COUNT(*) FROM Facturi")->fetch_row()[0];

// Obține datele pentru grafice
$venituriEvenimente = $conn->query("
    SELECT E.Nume_Eveniment, SUM(P.Suma_Platita) as total_venituri
    FROM Evenimente E
    LEFT JOIN Plati P ON E.EvenimentID = P.EvenimentID
    GROUP BY E.Nume_Eveniment
")->fetch_all(MYSQLI_ASSOC);

$locuriDisponibilitate = $conn->query("
    SELECT Disponibilitate, COUNT(*) as numar_locuri
    FROM DetaliiLocuri
    GROUP BY Disponibilitate
")->fetch_all(MYSQLI_ASSOC);

$conn->close();

// Formatează datele pentru grafice
$evenimentNume = [];
$totalVenituriArr = [];
foreach ($venituriEvenimente as $row) {
    $evenimentNume[] = $row['Nume_Eveniment'];
    $totalVenituriArr[] = $row['total_venituri'];
}

$disponibilitateLocuri = [];
$numarLocuri = [];
foreach ($locuriDisponibilitate as $row) {
    $disponibilitateLocuri[] = $row['Disponibilitate'];
    $numarLocuri[] = $row['numar_locuri'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acasă</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/Dashboard/acasa.css">
</head>

<body class="<?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
    <div class="sidebar <?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
        <div class="top-section">
            <img src="../Imagini/LogoDinamoBucuresti.png" alt="Logo">
            <h4>Bine ai venit, <b><?php echo htmlspecialchars($nume_admin); ?></b>!</h4>
            <a href="/Dashboard/acasa.php" class="active"><i class="fas fa-home"></i> Acasă</a>
            <a href="/Dashboard/inregistrare_eveniment.php"><i class="fas fa-calendar-alt"></i> Eveniment Nou</a>
            <a href="/Dashboard/rapoarte.php"><i class="fas fa-chart-bar"></i> Rapoarte</a>
            <a href="/Dashboard/grafice.php"><i class="fas fa-chart-line"></i> Grafice</a>
        </div>
        <div class="bottom-section">
            <a href="#" id="mode-toggle"><i class="fas fa-sun" id="mode-icon"></i> Schimbare mod</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Deconectare</a>
        </div>
    </div>
    <div class="content">
        <div class="cards-container">
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-content">
                    <h4><?php echo $totalUtilizatori; ?></h4>
                    <p>Total Utilizatori</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="card-content">
                    <h4><?php echo $totalEvenimente; ?></h4>
                    <p>Total Evenimente</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="card-content">
                    <h4><?php echo $totalBileteVandute; ?></h4>
                    <p>Total Bilete Vândute</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="card-content">
                    <h4><?php echo $totalVenituri; ?> RON</h4>
                    <p>Total Venituri</p>
                </div>
            </div>
            <div class="card">
                <div class="card-icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <div class="card-content">
                    <h4><?php echo $totalFacturiEmise; ?></h4>
                    <p>Total Facturi Emise</p>
                </div>
            </div>
        </div>
        <!-- Container pentru grafice -->
        <div class="charts-container">
            <!-- Card pentru graficul bar chart -->
            <div class="card" style="width: 946px; height: 705px; padding: 30px; margin-top: 10px;">
                <canvas id="venituriChart"></canvas>
            </div>
            <!-- Card pentru graficul pie chart -->
            <div class="card" style="width: 624px; height: 705px; padding: 30px; margin-top: 10px;">
                <canvas id="locuriChart"></canvas>
            </div>
        </div>
    </div>
            
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>

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

            // Configurare Chart.js pentru veniturile evenimentelor
            var ctx = document.getElementById('venituriChart').getContext('2d');
            var venituriChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($evenimentNume); ?>,
                    datasets: [{
                        label: 'Venituri Totale',
                        data: <?php echo json_encode($totalVenituriArr); ?>,
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        x: {
                            beginAtZero: true
                        },
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Configurare Chart.js pentru locuri
            var ctx2 = document.getElementById('locuriChart').getContext('2d');
            var locuriChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($disponibilitateLocuri); ?>,
                    datasets: [{
                        label: 'Distribuția Locurilor',
                        data: <?php echo json_encode($numarLocuri); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.2)',
                            'rgba(54, 162, 235, 0.2)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>
    
</body>
</html>
