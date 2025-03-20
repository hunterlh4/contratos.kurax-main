<?php
function marketing_pizarra_add($post){
	global $mysqli;
	global $return;
	global $login;

	$post["usuario_id"]=$login["id"];
	$post["fecha_ingreso"]=date("Y-m-d H:i:s");
	$post["estado"]=1;
	
	$data_to_db=array();
	$nulls=array("null","",false);
	foreach ($post as $k => $v) {
		if($v===0){
			$data_to_db[$k]=$v;
		}elseif(in_array($v, $nulls)){
			$data_to_db[$k]="NULL";
		}else{
			if(is_float($v)){
				$data_to_db[$k]="'".$v."'";
			}elseif(is_int($v)){
				$data_to_db[$k]=$v;
			}else{
				$data_to_db[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		}
	}

	$command = "INSERT INTO tbl_marketing_pizarra";
	$command.="(";
	$command.=implode(",", array_keys($data_to_db));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $data_to_db);
	$command.=")";
	$mysqli->query($command);
	if($mysqli->error){
		// $return["ERROR_MYSQL"]=$mysqli->error;
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}
}
?>