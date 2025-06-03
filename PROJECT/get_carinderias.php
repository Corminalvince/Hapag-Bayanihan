<?php
// get_carinderias.php
include "dbconfig.php";

$sql = "SELECT name, latitude, longitude, address FROM carinderias";
$result = $conn->query($sql);

$carinderias = [];
while ($row = $result->fetch_assoc()) {
    $carinderias[] = $row;
}

header('Content-Type: application/json');
echo json_encode($carinderias);
$conn->close();
?>