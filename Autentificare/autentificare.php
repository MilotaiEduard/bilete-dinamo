<?php

session_start();

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include '../db_connect.php'; // Conectare la baza de date

    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $parola = mysqli_real_escape_string($conn, trim($_POST['parola']));

    if (empty($email) && empty($parola)) {
        $error = 'Vă rugăm să completați câmpurile pentru email și parolă!';
    } elseif (empty($email)) {
        $error = 'Vă rugăm să completați câmpul pentru email!';
    } elseif (empty($parola)) {
        $error = 'Vă rugăm să completați câmpul pentru parolă!';
    } else {
        // Dacă ambele câmpuri sunt completate, verificăm credențialele
        $sql = "SELECT UtilizatorID, Parola FROM Utilizatori WHERE Email = '$email'";
        $result = mysqli_query($conn, $sql);
        if ($result && mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($parola, $row['Parola'])) {
                // Parola este corectă, autentificarea a reușit
                $_SESSION['user_id'] = $row['UtilizatorID']; // Stocăm ID-ul utilizatorului în sesiune
                header('Location: MeniuPrincipal/meniu_principal.php'); // Redirecționăm utilizatorul
                exit();
            } else {
                $error = 'Email sau parolă incorectă.';
            }
        } else {
            $error = 'Email sau parolă incorectă.';
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
    <title>Autentificare</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Autentificare/autentificare.css">
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
                    <!-- Formularul de autentificare -->
                    <form method="POST">
                        <h2 class="text-center">Autentificare</h2>
                        <div class="alert-container w-100">
                            <?php if ($error): ?>
                                <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" name="email" autocomplete="off" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        <div class="form-group position-relative">
                            <input type="password" class="form-control" name="parola" placeholder="Parola" id="parola">
                            <i toggle="#parola" class="fas fa-fw fa-eye-slash field-icon toggle-password" style="color: black;"></i>
                        </div>
                        <div class="mb-4 mt-4 ml-1">
                            Nu ai un cont? <a href="/Inregistrare/inregistrare.php">Înregistrează-te!</a>
                        </div>
                        <button type="submit" class="btn btn-block custom-btn mt-5 pt-2 pb-2">INTRĂ ÎN CONT</button>
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