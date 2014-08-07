<?php
require 'config.php';

header('HTTP/1.1 301 Moved Permanently'); 
header("Location: http://" . $_SERVER['HTTP_HOST'] . APP_URI . '/login.php');
