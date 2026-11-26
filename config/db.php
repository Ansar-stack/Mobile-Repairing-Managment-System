<?php

$conn = oci_connect(
    'ANSAR',
    'ansar123', 
    'localhost:1539/orclpdb'
);

if (!$conn) {
    $e = oci_error();
    die('Database connection failed: ' . $e['message']);
}
