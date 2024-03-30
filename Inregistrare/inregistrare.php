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
                        <!-- Zona pentru mesaje de succes sau eroare -->
                        <div id="messages"></div>
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
                            <input type="checkbox" class="form-check-input" id="terms">
                            <label class="form-check-label" for="terms">Sunt de acord cu <a href="../termenii_si_conditiile.php">termenii și conditiile.</a></label>
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
</body>
</html>
