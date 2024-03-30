<?php

session_start();
$success = ''; // Variabila pentru a stoca mesajul de succes
$error = ''; // Variabila pentru a stoca mesajele de eroare

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../db_connect.php'; // Includem fisierul de conexiune la baza de date

    // Colectarea datelor din formular
    $nume = mysqli_real_escape_string($conn, trim($_POST['nume']));
    $prenume = mysqli_real_escape_string($conn, trim($_POST['prenume']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $telefon = mysqli_real_escape_string($conn, trim($_POST['telefon']));
    $parola = mysqli_real_escape_string($conn, trim($_POST['parola']));
    $confirmare_parola = mysqli_real_escape_string($conn, trim($_POST['confirmare_parola']));
    $terms = isset($_POST['terms']);

    // Verificam daca toate campurile sunt completate
    if (empty($nume) || empty($prenume) || empty($email) || empty($telefon) || empty($parola) || empty($confirmare_parola)) {
        $error = 'Toate câmpurile sunt obligatorii.';
    } elseif (!$terms) {
        // Verificarea specifică pentru termenii și condițiile nebifate
        $error = 'Trebuie să fii de acord cu termenii și condițiile pentru a te înregistra.';
    } elseif ($parola !== $confirmare_parola) {
        $error = 'Parolele nu coincid.';
    } else {
        // Verificarea unicității emailului
        $sql = "SELECT Email FROM Utilizatori WHERE Email = '$email'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $error = 'Email-ul este deja folosit.';
        } else {
            // Verificarea unicității numărului de telefon
            $sql = "SELECT Telefon FROM Utilizatori WHERE Telefon = '$telefon'";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $error = 'Numărul de telefon este deja folosit.';
            } else {
                // Toate verificările au trecut, inserăm utilizatorul în baza de date
                $parola_hash = password_hash($parola, PASSWORD_DEFAULT);
                $sql = "INSERT INTO Utilizatori (Nume, Prenume, Email, Telefon, Parola, Rol) VALUES ('$nume', '$prenume', '$email', '$telefon', '$parola_hash', 'user')";
                if (mysqli_query($conn, $sql)) {
                    $success = 'Contul a fost creat cu succes. Redirecționare...';
                    header("refresh:3;url=/Autentificare/autentificare.php");
                    exit();
                } else {
                    $error = 'A apărut o eroare la înregistrare.';
                }
            }
        }
    }
    mysqli_close($conn);
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inregistrare</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Inregistrare/inregistrare.css">
</head>
<body>
    <div class="container-fluid h-100">
        <div class="row h-100">
            <!-- Zona pentru imagine -->
            <div class="col-md-7 p-0">
                <img src="/Imagini/bilete-dinamo_CoverImage.png" class="img-fluid img-full-height" alt="bilete-dinamo Cover Image">
            </div>
            <!-- Zona pentru formular -->
            <div class="col-md-5 d-flex justify-content-center align-items-center">
                <div class="w-50">
                    <!-- Formularul de inregistrare -->
                    <form method="POST">
                        <h2 class="text-center">Înregistrare</h2>
                        <div class="alert-container w-100">
                            <?php if ($error): ?>
                                <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                            <?php elseif ($success): ?>
                                <div class="alert alert-success text-center" role="alert"><?php echo $success; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="nume" autocomplete="off" placeholder="Nume">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="prenume" autocomplete="off" placeholder="Prenume">
                        </div>
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" autocomplete="off" placeholder="Email">
                        </div>
                        <div class="form-group">
                            <input type="tel" class="form-control" name="telefon" autocomplete="off" placeholder="Număr de telefon">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="parola" placeholder="Parola">
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control" name="confirmare_parola" placeholder="Confirmă Parola">
                        </div>
                        <div class="form-group form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="terms" id="terms">
                            <label class="form-check-label" for="terms">Sunt de acord cu <a href="../termenii_si_conditiile.php" target="_blank">termenii și conditiile.</a></label>
                        </div>
                        <div class="mb-4 ml-4">
                            Ai deja un cont? <a href="/Autentificare/autentificare.php">Autentifică-te!</a>
                        </div>
                        <button type="submit" class="btn btn-block custom-btn mt-5 pt-2 pb-2">CREEAZĂ-ȚI CONTUL</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>


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
