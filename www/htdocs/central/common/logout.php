<?php
/*
20240514 fho4abcd Added alternative return script. When the standard index.php is forbidden
20251014 fho4abcd Cope with expired SESSION variables
2025-11-24 fho4abcd return page set for architecture with OPAC
*/
session_start();
include("../config.php");
if (file_exists($db_path."logtrans/data/logtrans.mst") and isset($_SESSION["MODULO"]) and $_SESSION["MODULO"]=="loan"){
	include("../circulation/grabar_log.php");
	$datos_trans["operador"]=$_SESSION["login"];
	GrabarLog("Q",$datos_trans,$Wxis,$xWxis,$wxisUrl,$db_path);

}
if (isset($_SESSION["HOME"]))
	$retorno=$_SESSION["HOME"];
else {
	if (file_exists("../../login_abcd.php")){
		$retorno="../../login_abcd.php";
	} else if (file_exists("../../login.php")){
		$retorno="../../login.php";
	} else {
		$retorno="../../index.php";
	}
}
$_SESSION=array();
unset($_SESSION);
session_unset();
session_destroy();
?>
<script>
	top.window.location.href="<?php echo $retorno?>";
</script>