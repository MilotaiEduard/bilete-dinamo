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
        $error = 'Trebuie să fii de acord cu termenii și condițiile pentru a te înregistra.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Formatul adresei de email este incorect.';
    } elseif ($parola !== $confirmare_parola) {
        $error = 'Parolele nu coincid.';
    } elseif (strlen($parola) < 5) {
        $error = 'Parola trebuie să conțină cel puțin 5 caractere.';
    } else {
        // Verificarea lungimii numărului de telefon
        $lungimeTelefon = strlen($telefon);
        if ($lungimeTelefon < 7 || $lungimeTelefon > 15) {
            $error = 'Formatul numărului de telefon este incorect.';
        } else {
            // Verificarea unicității emailului
            $sql = "SELECT Email FROM Utilizatori WHERE Email = '$email'";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $error = 'Email-ul este deja existent.';
            } else {
                // Verificarea unicității numărului de telefon
                $sql = "SELECT Telefon FROM Utilizatori WHERE Telefon = '$telefon'";
                $result = mysqli_query($conn, $sql);
                if (mysqli_num_rows($result) > 0) {
                    $error = 'Numărul de telefon este deja existent.';
                } else {
                    // Toate verificările au trecut, inserăm utilizatorul în baza de date
                    $parola_hash = password_hash($parola, PASSWORD_DEFAULT);
                    $sql = "INSERT INTO Utilizatori (Nume, Prenume, Email, Telefon, Parola, Rol) VALUES ('$nume', '$prenume', '$email', '$telefon', '$parola_hash', 'user')";
                    if (mysqli_query($conn, $sql)) {
                        $success = 'Contul a fost creat cu succes. Redirecționare...';
                        header("refresh:3;url=/Autentificare/autentificare.php");
                    } else {
                        $error = 'A apărut o eroare la înregistrare.';
                    }
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
    <title>Înregistrare</title>
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
                            <input type="text" class="form-control" name="nume" autocomplete="off" placeholder="Nume" value="<?php echo isset($_POST['nume']) ? htmlspecialchars($_POST['nume']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="prenume" autocomplete="off" placeholder="Prenume" value="<?php echo isset($_POST['prenume']) ? htmlspecialchars($_POST['prenume']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="email" autocomplete="off" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <input type="tel" class="form-control" name="telefon" autocomplete="off" placeholder="Număr de telefon" value="<?php echo isset($_POST['telefon']) ? htmlspecialchars($_POST['telefon']) : ''; ?>">
                        </div>
                        <div class="form-group position-relative">
                            <input type="password" class="form-control" name="parola" placeholder="Parola" id="parola">
                            <i toggle="#parola" class="fas fa-fw fa-eye-slash field-icon toggle-password" style="color: black;"></i>
                        </div>
                        <div class="form-group position-relative">
                            <input type="password" class="form-control" name="confirmare_parola" placeholder="Confirmă Parola" id="confirmare_parola">
                            <i toggle="#confirmare_parola" class="fas fa-fw fa-eye-slash field-icon toggle-password" style="color: black;"></i>
                        </div>
                        <div class="form-group form-check mt-4">
                            <input type="checkbox" class="form-check-input" name="terms" id="terms" <?php echo isset($_POST['terms']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="terms">Sunt de acord cu <a href="../Legal/termenii_si_conditiile.html" target="_blank">termenii și condițiile.</a></label>
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

    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Adaugă listener pe click pentru toate elementele cu clasa "toggle-password"
        document.querySelectorAll('.toggle-password').forEach(function(item) {
            item.addEventListener('click', function() {
                // Identifică input-ul corespunzător
                var input = document.querySelector(this.getAttribute("toggle"));
                if (input.getAttribute("type") == "password") {
                    input.setAttribute("type", "text");
                    this.classList.remove("fa-eye-slash");
                    this.classList.add("fa-eye");
                } else {
                    input.setAttribute("type", "password");
                    this.classList.remove("fa-eye");
                    this.classList.add("fa-eye-slash");
                }
            });
        });
    });
    </script>

</body>
</html>
