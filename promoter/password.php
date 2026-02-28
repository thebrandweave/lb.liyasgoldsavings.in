<!-- For Password generation   -->

<?php
$password = "pass"; // Example password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

echo $hashedPassword;
?>



