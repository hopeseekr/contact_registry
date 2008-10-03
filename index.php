<?php
require 'config.inc';

header('HTTP/1.1 301 Moved Permanently'); 
header("Location: http://" . $_SERVER['HTTP_HOST'] . APP_URI . '/login.php');
