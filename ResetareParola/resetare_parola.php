<?php

session_start(); // Începe sesiunea

include '../db_connect.php'; // Include fișierul de conexiune la baza de date

// Verifică dacă există un mesaj de eroare sau succes setat în sesiune și îl stocăm în variabila $error, respectiv $success
$error = '';
$success = '';

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']); // Șterge mesajul de eroare din sesiune după ce a fost preluat
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']); // Șterge mesajul de succes din sesiune după ce a fost preluat
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email de resetare</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="resetare_parola.css">
</head>
<body>
    <div class="container h-100 w-50">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-6">
                <div class="form-container">
                    <form action="trimite_email_resetare.php" method="post" class="p-4 rounded">
                        <div class="text-center mb-4">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h2 class="text-center">Email de resetare</h2>
                        <div class="alert-container w-100">
                            <?php if ($error): ?>
                                <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if ($success): ?>
                                <div class="alert alert-success text-center" role="alert"><?php echo $success; ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control" id="email" name="email" autocomplete="off" placeholder="Email">
                        </div>
                        <button type="submit" class="btn custom-btn btn-block mt-5">TRIMITE EMAIL-UL DE RESETARE</button>
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
