<?php

include_once '../config/db.php';
session_start();
session_destroy();
header("location: ../index.php?logout=success");
exit();
 ?>