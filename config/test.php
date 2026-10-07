<?php
$empaques = require __DIR__ . '/empaques.php';
var_dump($empaques['paquete_chimo']['unidades']);  // int(10)
var_dump(array_key_exists('caja', $empaques));      // bool(false)