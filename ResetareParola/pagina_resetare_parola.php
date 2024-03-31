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
                <form action="reseteaza_parola.php" method="post">
                    <h2 class="text-center">Resetare parolă</h2>
                    <div class="alert-container w-100">
                        <?php if ($error): ?>
                            <div class="alert alert-danger text-center" role="alert"><?php echo $error; ?></div>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="token" value="<?php echo $_GET['token']; ?>">
                    <div class="form-group">
                        <input type="password" class="form-control" id="parola_noua" name="parola_noua" placeholder="Parola nouă">
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control" id="confirma_parola" name="confirma_parola" placeholder="Confirmă parola">
                    <button type="submit" class="btn btn-block custom-btn mt-5">RESETEAZĂ PAROLA</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>