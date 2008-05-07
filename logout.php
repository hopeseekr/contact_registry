<?php
require_once('config.php');         // Needed for validateSession() and getDB()
session_start();

logout();
header("Location: http://" . $_SERVER['HTTP_HOST'] . '/');
?>