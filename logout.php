<?php
require_once('config.inc');         // Needed for validateSession() and getDB()
session_start();

logout();
header("Location: http://" . $_SERVER['HTTP_HOST'] . '/');
?>