<?php
function db_connect() {
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $database = 'text_b1_bewertung';

    $conn = new mysqli($host, $user, $password, $database);
    if ($conn->connect_error) {
        die("❌ Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error);
    }
    return $conn;
}
