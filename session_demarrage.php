<?php
//DEMARER LA SESSION GLOBALE ICI AU BESOIN SANS DEFINIR LA VARIABLE $_SESSION['parametre']
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

?>