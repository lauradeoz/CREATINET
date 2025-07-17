<?php
session_start();

// borra todos los datos de la sesion
session_unset();

// destruye la sesion
session_destroy();

// Redirige al login
header('Location: login.php');
exit();
