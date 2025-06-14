<?php
/* php & Oracle DB connection file */
$user = "HR"; //Oracle username
$pass = "oracle"; //Oracle password
$host = "localhost/freepdb1"; //server name or ip address
$dbconn = oci_connect($user, $pass, $host);
if (!$dbconn) {
    $e = oci_error();
    trigger_error(
        htmlentities($e['message'], ENT_QUOTES),
        E_USER_ERROR
    );
} else {
    echo "ORACLE DATABASE CONNECTED SUCCESSFULLY!!!";
}
?>