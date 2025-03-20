<?php
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);

if (isset($_GET['action'])) {

    echo  "getting action ...";

} else {

?>

    <div>
        <form method="get">
            <input type="text" name="action" value="hola" readonly>
            <input type="submit" value="Migrar">
        </form>
    </div>
<?php
}
?>