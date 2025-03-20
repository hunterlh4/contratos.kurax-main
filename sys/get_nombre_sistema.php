<?php
	function get_nombre_sistema(){
		global $mysqli,$return;
		$sql_command = "SELECT nombre FROM tbl_sistemas WHERE id = '".$_POST["filtro"]["sistema_id"]."'";
			$return["query"] = $sql_command;
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			$return["data"]=$itm;
		}		
	}
?>