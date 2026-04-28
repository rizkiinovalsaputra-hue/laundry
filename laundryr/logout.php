<?php
session_start();
session_destroy();
header("Location: /laundryr/index.php");
exit;
