<?php
//Session
session_start();
//if session empty
if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
}
