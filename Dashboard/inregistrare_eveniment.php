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

// Verifică dacă formularul a fost trimis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obține datele din formular
    $sezon_eveniment = trim($_POST['sezon_eveniment']);
    $nume_eveniment = trim($_POST['nume_eveniment']);
    $data_eveniment = trim($_POST['data_eveniment']);
    $locatie_eveniment = trim($_POST['locatie_eveniment']);
    $logo_echipa_oaspete = trim($_POST['logo_echipa_oaspete']);

    // Verifică dacă toate câmpurile sunt completate
    if (!empty($sezon_eveniment) && !empty($nume_eveniment) && !empty($data_eveniment) && !empty($locatie_eveniment) && !empty($logo_echipa_oaspete)) {
        // Încearcă să inserezi evenimentul în baza de date
        $stmt = $conn->prepare("INSERT INTO Evenimente (Sezon_Eveniment, Nume_Eveniment, Data_Eveniment, Locatie_Eveniment, Logo_Echipa_Oaspete) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $sezon_eveniment, $nume_eveniment, $data_eveniment, $locatie_eveniment, $logo_echipa_oaspete);

        if ($stmt->execute()) {
            // Resetează disponibilitatea locurilor doar pentru locurile ocupate
            $resetStmt = $conn->prepare("UPDATE DetaliiLocuri SET Disponibilitate = 'Disponibil' WHERE Disponibilitate = 'Ocupat'");
            $resetStmt->execute();
            $resetStmt->close();
            
            // Afișează mesajul de succes
            echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Succes',
                    text: 'Evenimentul a fost înregistrat cu succes.',
                    confirmButtonText: 'OK'
                }).then(() => {
                    window.location.href = 'inregistrare_eveniment.php';
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare',
                    text: 'A apărut o eroare la înregistrarea evenimentului.',
                    confirmButtonText: 'OK'
                });
            </script>";
        }

        $stmt->close();
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Eroare la înregistrarea evenimentului',
                text: 'Toate câmpurile sunt obligatorii!',
                confirmButtonText: 'OK'
            });
        </script>";
    }
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Înregistrare Eveniment</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="/Dashboard/inregistrare_eveniment.css">
</head>

<body class="<?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
    <div class="sidebar <?php if(isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'dark') { echo 'dark-mode'; } ?>">
        <div class="top-section">
            <img src="../Imagini/LogoDinamoBucuresti.png" alt="Logo">
            <h4>Bine ai venit, <b><?php echo htmlspecialchars($nume_admin); ?></b>!</h4>
            <a href="/Dashboard/acasa.php"><i class="fas fa-home"></i> Acasă</a>
            <a href="/Dashboard/inregistrare_eveniment.php" class="active"><i class="fas fa-calendar-alt"></i> Eveniment Nou</a>
            <a href="/Dashboard/rapoarte.php"><i class="fas fa-chart-bar"></i> Rapoarte</a>
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
                <h5 class="card-title text-center"><b>Înregistrați un eveniment</b></h5>
                <form id="eventForm" action="inregistrare_eveniment.php" method="post">
                    <div class="form-group">
                        <label for="sezon_eveniment" class="form-label">Sezon Eveniment</label>
                        <input type="text" class="form-control" id="sezon_eveniment" name="sezon_eveniment" autocomplete="off" placeholder="ex: Sezonul 2024-2025">
                    </div>
                    <div class="form-group">
                        <label for="nume_eveniment" class="form-label">Nume Eveniment</label>
                        <input type="text" class="form-control" id="nume_eveniment" name="nume_eveniment" autocomplete="off" placeholder="ex: Dinamo Bucuresti - UTA Arad - Etapa 2">
                    </div>
                    <div class="form-group">
                        <label for="data_eveniment" class="form-label">Data Eveniment</label>
                        <input type="datetime-local" class="form-control" id="data_eveniment" name="data_eveniment">
                    </div>
                    <div class="form-group">
                        <label for="locatie_eveniment" class="form-label">Locație Eveniment</label>
                        <input type="text" class="form-control" id="locatie_eveniment" name="locatie_eveniment" autocomplete="off" placeholder="ex: Stadion Arcul de Triumf, Bucuresti">
                    </div>
                    <div class="form-group">
                        <label for="echipa_oaspete" class="form-label">Echipa Oaspete</label>
                        <select class="form-control" id="echipa_oaspete" name="echipa_oaspete">
                            <option value="">Selectează echipa</option>
                            <optgroup label="Superliga">
                                <option value="Botosani">FC Botoșani</option>
                                <option value="CFR">CFR Cluj</option>
                                <option value="Farul">Farul Constanța</option>
                                <option value="FCSB">FCSB</option>
                                <option value="Hermannstadt">FC Hermannstadt</option>
                                <option value="Otelul">Oțelul Galați</option>
                                <option value="Petrolul">Petrolul Ploiești</option>
                                <option value="Iasi">Poli Iași</option>
                                <option value="Rapid">Rapid București</option>
                                <option value="Sepsi">Sepsi Sf. Gheorghe</option>
                                <option value="UCluj">U Cluj</option>
                                <option value="UCraiova">U Craiova 1948</option>
                                <option value="UnivCraiova">Universitatea Craiova</option>
                                <option value="UTA">UTA Arad</option>
                                <option value="Voluntari">FC Voluntari</option>
                            </optgroup>
                            <optgroup label="Liga II">
                                <option value="Alexandria">Alexandria</option>
                                <option value="Arges">FC Argeș</option>
                                <option value="Ceahlaul">Ceahlăul P. Neamț</option>
                                <option value="Chindia">Chindia Târgoviște</option>
                                <option value="Concordia">Concordia Chiajna</option>
                                <option value="Corvinul">Corvinul Hunedoara</option>
                                <option value="Csikszereda">Csikszereda M. Ciuc</option>
                                <option value="Dumbravita">CSC Dumbrăvița</option>
                                <option value="Gloria">Gloria Buzău</option>
                                <option value="Metaloglobus">Metaloglobus București</option>
                                <option value="Mioveni">CS Mioveni</option>
                                <option value="Progresul">Progresul Spartac</option>
                                <option value="Resita">CSM Reșița</option>
                                <option value="Selimbar">CSC 1599 Șelimbăr</option>
                                <option value="Slatina">CSM Slatina</option>
                                <option value="Steaua">CSA Steaua București</option>
                                <option value="Tunari">Tunari</option>
                                <option value="Dej">Unirea Dej</option>
                                <option value="Slobozia">Unirea Slobozia</option>
                                <option value="Viitorul">ACS Viitorul Tg. Jiu</option>
                            </optgroup>
                        </select>
                        <!-- Input hidden pentru logo echipa oaspete -->
                        <input type="hidden" id="logo_echipa_oaspete" name="logo_echipa_oaspete">
                    </div>
                    <button type="submit" class="btn custom-btn">Înregistrează Eveniment</button>
                </form>
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
        });

        const echipeLogoMap = {
            'Botosani': '/Imagini/EchipaOaspete/Superliga/LogoBotosani.png',
            'CFR': '/Imagini/EchipaOaspete/Superliga/LogoCFR.png',
            'Farul': '/Imagini/EchipaOaspete/Superliga/LogoFarul.png',
            'FCSB': '/Imagini/EchipaOaspete/Superliga/LogoFCSB.png',
            'Hermannstadt': '/Imagini/EchipaOaspete/Superliga/LogoHermannstadt.png',
            'Otelul': '/Imagini/EchipaOaspete/Superliga/LogoOtelul.png',
            'Petrolul': '/Imagini/EchipaOaspete/Superliga/LogoPetrolul.png',
            'Iasi': '/Imagini/EchipaOaspete/Superliga/LogoPoliIasi.png',
            'Rapid': '/Imagini/EchipaOaspete/Superliga/LogoRapid.png',
            'Sepsi': '/Imagini/EchipaOaspete/Superliga/LogoSepsi.png',
            'UCluj': '/Imagini/EchipaOaspete/Superliga/LogoUCluj.png',
            'UCraiova': '/Imagini/EchipaOaspete/Superliga/LogoUCraiova.png',
            'UnivCraiova': '/Imagini/EchipaOaspete/Superliga/LogoUnivCraiova.png',
            'UTA': '/Imagini/EchipaOaspete/Superliga/LogoUTA.png',
            'Voluntari': '/Imagini/EchipaOaspete/Superliga/LogoVoluntari.png',
            'Alexandria': '/Imagini/EchipaOaspete/Liga II/LogoAlexandria.png',
            'Arges': '/Imagini/EchipaOaspete/Liga II/LogoArges.png',
            'Ceahlaul': '/Imagini/EchipaOaspete/Liga II/LogoCeahlaul.png',
            'Chindia': '/Imagini/EchipaOaspete/Liga II/LogoChindia.png',
            'Concordia': '/Imagini/EchipaOaspete/Liga II/LogoConcordia.png',
            'Corvinul': '/Imagini/EchipaOaspete/Liga II/LogoCorvinulHunedoara.png',
            'Csikszereda': '/Imagini/EchipaOaspete/Liga II/LogoCsikszereda.png',
            'Dumbravita': '/Imagini/EchipaOaspete/Liga II/LogoDumbravita.png',
            'Gloria': '/Imagini/EchipaOaspete/Liga II/LogoGloriaBuzau.png',
            'Metaloglobus': '/Imagini/EchipaOaspete/Liga II/LogoMetaloglobus.png',
            'Mioveni': '/Imagini/EchipaOaspete/Liga II/LogoMioveni.png',
            'Progresul': '/Imagini/EchipaOaspete/Liga II/LogoProgresulSpartac.png',
            'Resita': '/Imagini/EchipaOaspete/Liga II/LogoResita.png',
            'Selimbar': '/Imagini/EchipaOaspete/Liga II/LogoSelimbar.png',
            'Slatina': '/Imagini/EchipaOaspete/Liga II/LogoSlatina.png',
            'Steaua': '/Imagini/EchipaOaspete/Liga II/LogoSteaua.png',
            'Tunari': '/Imagini/EchipaOaspete/Liga II/LogoTunari.png',
            'Dej': '/Imagini/EchipaOaspete/Liga II/LogoUnireaDej.png',
            'Slobozia': '/Imagini/EchipaOaspete/Liga II/LogoUnireaSlobozia.png',
            'Viitorul': '/Imagini/EchipaOaspete/Liga II/LogoViitorulTgJiu.png',
        };

        document.getElementById('echipa_oaspete').addEventListener('change', function() {
            const selectedTeam = this.value;
            const logoSrc = echipeLogoMap[selectedTeam] || '';
            document.getElementById('logo_echipa_oaspete').value = logoSrc;
        });

        // Validare formular
        document.querySelector('form').addEventListener('submit', function(event) {
            event.preventDefault(); // Previne trimiterea implicită a formularului
            let valid = true;
            let errorMessage = '';

            const sezonEveniment = document.getElementById('sezon_eveniment').value.trim();
            const numeEveniment = document.getElementById('nume_eveniment').value.trim();
            const dataEveniment = document.getElementById('data_eveniment').value.trim();
            const locatieEveniment = document.getElementById('locatie_eveniment').value.trim();
            const echipaOaspete = document.getElementById('echipa_oaspete').value.trim();
            const currentDateTime = new Date().toISOString().slice(0, 16); // Data și ora curentă în format ISO

            if (sezonEveniment === '') {
                valid = false;
                errorMessage += 'Sezonul evenimentului este obligatoriu.<br>';
            }
            if (numeEveniment === '') {
                valid = false;
                errorMessage += 'Numele evenimentului este obligatoriu.<br>';
            }
            if (dataEveniment === '') {
                valid = false;
                errorMessage += 'Data evenimentului este obligatorie.<br>';
            } else if (dataEveniment < currentDateTime) {
                valid = false;
                errorMessage += 'Data evenimentului nu poate fi în trecut.<br>';
            }
            if (locatieEveniment === '') {
                valid = false;
                errorMessage += 'Locația evenimentului este obligatorie.<br>';
            }
            if (echipaOaspete === '') {
                valid = false;
                errorMessage += 'Selectarea echipei oaspete este obligatorie.<br>';
            }

            if (!valid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Eroare la înregistrarea evenimentului',
                    html: errorMessage,
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Succes',
                    text: 'Evenimentul a fost înregistrat cu succes.',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('eventForm').submit(); // Trimite formularul manual
                    }
                });
            }
        });
    </script>
    
</body>
</html>
