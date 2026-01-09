<?php
// Maintenance check - must be first
require_once("./utils/maintenance_check.php");
?>
<div class="hiddenDiv">
    <style>
        .hiddenDiv{
            display: none;
        }
    </style>

<script>
    window.location.href="./landing"
</script>
<?php 
require_once("./config/config.php");

require_once("./noInternet/index.html");
require_once("./noInternet/sw.js");

// header("Location: ./landing");
?>

</div>
