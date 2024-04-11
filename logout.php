<?php

session_start();
session_unset();
session_destroy();

header('Location: /MeniuPrincipal/meniu_principal.php'); // Redirecționează utilizatorul către pagina de autentificare
exit();

?>