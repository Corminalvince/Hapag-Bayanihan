<?php 

$localhost = "localhost";
$database= "hapagbayanihan" ;
$user= "admin";
$password = "hapagbayanihan";



$conn = new mysqli($localhost, $user, $password, $database);

// Check connection
if ($conn) 
{
  
}
else{
    echo"Connection failed: ";
}
 ?>