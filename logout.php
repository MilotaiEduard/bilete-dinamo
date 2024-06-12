<?php

session_start();

if ($_SESSION['rol'] == 'admin') {
    header('Location: /Autentificare/autentificare.php'); // Redirecționează adminul către pagina de autentificare
} else {
    header('Location: /MeniuPrincipal/meniu_principal.php'); // Redirecționează utilizatorul către meniul principal
}

session_unset();
session_destroy();

exit();

?>