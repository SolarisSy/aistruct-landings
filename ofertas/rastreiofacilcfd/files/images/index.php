<?php
echo '<script>window.location.replace("https://' . explode('/', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]")[2] . '")</script>';
die();
