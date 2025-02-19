<?php

// Logout the user
session_start();
session_unset();
session_destroy();

// Redirect to the login page
header('Location: ../index.html');
exit();
