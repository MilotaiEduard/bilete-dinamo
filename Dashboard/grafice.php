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

// Interogare pentru numărul de evenimente pe sezon
$sql = "SELECT Sezon_Eveniment, COUNT(*) as numar_evenimente FROM Evenimente GROUP BY Sezon_Eveniment";
$result = $conn->query($sql);

$seasons = [];
$event_counts = [];

while ($row = $result->fetch_assoc()) {
    $seasons[] = $row['Sezon_Eveniment'];
    $event_counts[] = $row['numar_evenimente'];
}

$result->free();

// Interogare pentru numărul de bilete vândute per eveniment
$sql = "SELECT E.Nume_Eveniment, COUNT(B.BiletID) as numar_bilete FROM Evenimente E JOIN Bilete B ON E.EvenimentID = B.EvenimentID GROUP BY E.Nume_Eveniment";
$result = $conn->query($sql);

$event_names = [];
$ticket_counts = [];

while ($row = $result->fetch_assoc()) {
    $event_names[] = $row['Nume_Eveniment'];
    $ticket_counts[] = $row['numar_bilete'];
}

$result->free();

// Interogare pentru prețurile biletelor pe categorii
$sql = "SELECT Nume_Categorie, Pret FROM CategoriiLocuri";
$result = $conn->query($sql);

$category_names = [];
$category_prices = [];

while ($row = $result->fetch_assoc()) {
    $category_names[] = $row['Nume_Categorie'];
    $category_prices[] = $row['Pret'];
}

$result->free();

// Interogare pentru disponibilitatea locurilor
$sql = "SELECT Disponibilitate, COUNT(*) as count FROM DetaliiLocuri GROUP BY Disponibilitate";
$result = $conn->query($sql);

$availability_status = [];
$availability_counts = [];

while ($row = $result->fetch_assoc()) {
    $availability_status[] = $row['Disponibilitate'];
    $availability_counts[] = $row['count'];
}

$result->free();

// Interogare pentru suma plătită în funcție de utilizator
$sql = "SELECT CONCAT(U.Nume, ' ', U.Prenume) as nume_complet, SUM(P.Suma_Platita) as suma_totala FROM Plati P JOIN Utilizatori U ON P.UtilizatorID = U.UtilizatorID GROUP BY nume_complet";
$result = $conn->query($sql);

$user_names_payments = [];
$total_payments = [];

while ($row = $result->fetch_assoc()) {
    $user_names_payments[] = $row['nume_complet'];
    $total_payments[] = $row['suma_totala'];
}

$result->free();

// Interogare pentru numărul de vânzări în funcție de utilizator
$sql = "SELECT CONCAT(U.Nume, ' ', U.Prenume) as nume_complet, COUNT(V.VanzareID) as numar_vanzari FROM Vanzari V JOIN Utilizatori U ON V.UtilizatorID = U.UtilizatorID GROUP BY nume_complet";
$result = $conn->query($sql);

$user_names_sales = [];
$total_sales = [];

while ($row = $result->fetch_assoc()) {
    $user_names_sales[] = $row['nume_complet'];
    $total_sales[] = $row['numar_vanzari'];
}

$result->free();

// Interogare pentru numărul de facturi emise pe lună
$sql = "SELECT DATE_FORMAT(Data_Factura, '%Y-%m') as luna, COUNT(*) as numar_facturi FROM Facturi GROUP BY luna";
$result = $conn->query($sql);

$months = [];
$invoice_counts = [];

while ($row = $result->fetch_assoc()) {
    $months[] = $row['luna'];
    $invoice_counts[] = $row['numar_facturi'];
}

$result->free();

// Interogare pentru locațiile utilizatorilor
$sql = "SELECT Judet, COUNT(*) as numar_utilizatori FROM DateFacturare GROUP BY Judet";
$result = $conn->query($sql);

$locations = [];
$user_counts = [];

while ($row = $result->fetch_assoc()) {
    $locations[] = $row['Judet'];
    $user_counts[] = $row['numar_utilizatori'];
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafice</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="/Dashboard/grafice.css">
</head>

<body class="<?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
    <div class="sidebar <?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
        <div class="top-section">
            <img src="../Imagini/LogoDinamoBucuresti.png" alt="Logo">
            <h4>Bine ai venit, <b><?php echo htmlspecialchars($nume_admin); ?></b>!</h4>
            <a href="/Dashboard/acasa.php"><i class="fas fa-home"></i> Acasă</a>
            <a href="/Dashboard/inregistrare_eveniment.php"><i class="fas fa-calendar-alt"></i> Eveniment Nou</a>
            <a href="/Dashboard/rapoarte.php"><i class="fas fa-chart-bar"></i> Rapoarte</a>
            <a href="/Dashboard/grafice.php" class="active"><i class="fas fa-chart-line"></i> Grafice</a>
        </div>
        <div class="bottom-section">
            <a href="#" id="mode-toggle"><i class="fas fa-sun" id="mode-icon"></i> Schimbare mod</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Deconectare</a>
        </div>
    </div>
    <div class="content">
        <div class="grid-container">
            <div class="card">
                <h5>Numărul de evenimente pe sezon</h5>
                <canvas id="eventsPerSeasonChart"></canvas>
            </div>
            <div class="card">
                <h5>Numărul de bilete vândute per eveniment</h5>
                <canvas id="ticketsPerEventChart"></canvas>
            </div>
            <div class="card">
                <h5>Prețurile biletelor pe categorii (RON)</h5>
                <canvas id="ticketPricesChart"></canvas>
            </div>
            <div class="card">
                <h5>Disponibilitatea locurilor</h5>
                <canvas id="seatAvailabilityChart"></canvas>
            </div>
            <div class="card">
                <h5>Suma plătită în funcție de utilizator</h5>
                <canvas id="userPaymentsChart"></canvas>
            </div>
            <div class="card">
                <h5>Numărul de vânzări în funcție de utilizator</h5>
                <canvas id="userSalesChart"></canvas>
            </div>
            <div class="card">
                <h5>Numărul de facturi emise pe lună</h5>
                <canvas id="invoiceCountsChart"></canvas>
            </div>
            <div class="card">
                <h5>Distribuția utilizatorilor pe județe</h5>
                <canvas id="userLocationsChart"></canvas>
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

            // Graficul pentru numărul de evenimente pe sezon
            var ctx1 = document.getElementById('eventsPerSeasonChart').getContext('2d');
            var eventsPerSeasonChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($seasons); ?>,
                    datasets: [{
                        label: 'Număr de evenimente',
                        data: <?php echo json_encode($event_counts); ?>,
                        backgroundColor: '#007bff',
                        borderColor: '#0056b3',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Graficul pentru numărul de bilete vândute per eveniment
            var ctx2 = document.getElementById('ticketsPerEventChart').getContext('2d');
            var ticketsPerEventChart = new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($event_names); ?>,
                    datasets: [{
                        label: 'Număr de bilete vândute',
                        data: <?php echo json_encode($ticket_counts); ?>,
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Graficul pentru prețurile biletelor pe categorii
            var ctx3 = document.getElementById('ticketPricesChart').getContext('2d');
            var ticketPricesChart = new Chart(ctx3, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode($category_names); ?>,
                    datasets: [{
                        label: 'Preț bilet',
                        data: <?php echo json_encode($category_prices); ?>,
                        backgroundColor: ['#007bff', '#28a745', '#ffc107', '#dc3545', '#17a2b8'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // Graficul pentru disponibilitatea locurilor
            var ctx4 = document.getElementById('seatAvailabilityChart').getContext('2d');
            var seatAvailabilityChart = new Chart(ctx4, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($availability_status); ?>,
                    datasets: [{
                        label: 'Disponibilitatea locurilor',
                        data: <?php echo json_encode($availability_counts); ?>,
                        backgroundColor: ['#28a745', '#dc3545'],
                        borderColor: '#fff',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // Graficul pentru suma plătită în funcție de utilizator
            var ctx5 = document.getElementById('userPaymentsChart').getContext('2d');
            var userPaymentsChart = new Chart(ctx5, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($user_names_payments); ?>,
                    datasets: [{
                        label: 'Suma plătită (RON)',
                        data: <?php echo json_encode($total_payments); ?>,
                        backgroundColor: '#28a745',
                        borderColor: '#1f7a2d',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            
            // Graficul pentru numărul de vânzări în funcție de utilizator
            var ctx6 = document.getElementById('userSalesChart').getContext('2d');
            var userSalesChart = new Chart(ctx6, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($user_names_sales); ?>,
                    datasets: [{
                        label: 'Număr de vânzări',
                        data: <?php echo json_encode($total_sales); ?>,
                        backgroundColor: '#007bff',
                        borderColor: '#0056b3',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Graficul pentru numărul de facturi emise pe lună
            var ctx7 = document.getElementById('invoiceCountsChart').getContext('2d');
            var invoiceCountsChart = new Chart(ctx7, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($months); ?>,
                    datasets: [{
                        label: 'Număr de facturi',
                        data: <?php echo json_encode($invoice_counts); ?>,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Graficul pentru distribuția utilizatorilor pe județe
            var ctx8 = document.getElementById('userLocationsChart').getContext('2d');
            var userLocationsChart = new Chart(ctx8, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($locations); ?>,
                    datasets: [{
                        label: 'Număr de utilizatori',
                        data: <?php echo json_encode($user_counts); ?>,
                        backgroundColor: '#ffc107',
                        borderColor: '#ffc107',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        });
    </script>
    
</body>
</html>
