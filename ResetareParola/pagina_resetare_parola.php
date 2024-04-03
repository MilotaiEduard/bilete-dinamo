<?php

session_start();

include '../db_connect.php';

$error = '';
$success = '';
$tokenValid = false;

if (!empty($_GET['token'])) {
    $token = $_GET['token'];
    $sql = "SELECT Email, Expira FROM ResetareParola WHERE Token = ? AND Expira > NOW()";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($email, $expira);
            $stmt->fetch();
            $tokenValid = true;
        } else {
            $error = "Token invalid sau expirat.";
        }
    } else {
        $error = "Eroare la interogarea bazei de date.";
    }
    $stmt->close();
} else {
    $error = "Token necesar pentru resetarea parolei.";
}

if (isset($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}

if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagina de resetare a parolei</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ResetareParola/pagina_resetare_parola.css">
</head>
<body>
    <div class="container h-100 w-50">
        <div class="row justify-content-center align-items-center h-100">
            <div class="col-6">
                <?php if ($tokenValid): ?>
                <form action="reseteaza_parola.php" method="post">
                    <h2 class="text-center">Resetare parolă</h2>
                    <div class="alert-container w-100">
                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                            <div class="alert alert-success text-center" role="alert"><?php echo $success; ?></div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <input type="password" class="form-control" id="parola_noua" name="parola_noua" placeholder="Parola nouă" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="confirma_parola" name="confirma_parola" placeholder="Confirmă parola" required>
                    </div>
                    <button type="submit" class="btn btn-block custom-btn mt-5">RESETEAZĂ PAROLA</button>
                </form>
                <?php else: ?>
                    <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
</body>
</html>
