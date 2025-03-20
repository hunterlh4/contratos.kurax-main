<?php
include("set_data.php");

if(isset($_POST["switch_contrato_terminado"])){
	$query = "UPDATE tbl_contratos SET estado = '2' WHERE id=".$_POST["data"]["id"];
	$mysqli->query($query);
	echo true;
}

if(isset($_POST["opt"])){
	extract($_POST);
	if($opt=="contratos_formula_tipo_3_save"){
		print_r($_POST);
		$new_cons = $data;
		$disable_prev_command = "UPDATE tbl_contrato_formula_condicionales SET estado = '0' WHERE producto_id = '".$data["producto_id"]."' AND servicio_id = '".$data["servicio_id"]."' AND contrato_id = '".$data["contrato_id"]."' AND formula_id = '".$data["formula_id"]."'";
		$mysqli->query($disable_prev_command);

		if(array_key_exists("cons", $data)){
			foreach ($data["cons"] as $key => $value) {
				// $new_cons_insert_query = ""
				$value["producto_id"]=$data["producto_id"];
				$value["servicio_id"]=$data["servicio_id"];
				$value["contrato_id"]=$data["contrato_id"];
				$value["formula_id"]=$data["formula_id"];
				$value["estado"]=1;

				unset($value["tmp_id"]);

				$data_to_db = cons_data_to_db($value);
				$insert_command = "INSERT INTO tbl_contrato_formula_condicionales";
					$insert_command.="(";
					$insert_command.=implode(",", array_keys($data_to_db));
					$insert_command.=")";
					$insert_command.=" VALUES ";
					$insert_command.="(";
					$insert_command.=implode(",", $data_to_db);
					$insert_command.=")";
					// $insert_command.=" ON DUPLICATE KEY UPDATE ";
					// $uqn=0;
					// $only_update_array = array("fecha_ingreso");
					// foreach ($data_to_db as $key => $value) {
					// 	if(in_array($key, $only_update_array)){
					// 		if($uqn>0) { $insert_command.=","; }
					// 		$insert_command.= $key." = VALUES(".$key.")";
					// 		$uqn++;
					// 	}
					// }
				echo $insert_command;
				$mysqli->query($insert_command);
				$data["cons"][$key]["id"] = $mysqli->insert_id;
				if($mysqli_error = $mysqli->error){
					echo "\n";
					print_r($mysqli_error);
					echo "\n";
					// echo $insert_command;
					exit();
				}else{
					// $affected_rows = $mysqli->affected_rows;
					// if($affected_rows==2){
					// 	$return["num_update"]++;
					// }elseif($affected_rows==1){
					// 	$return["num_insert"]++;
					// }else{
					// 	$return["num_nothing"]++;
					// }
				}
			}
			print_r($data["cons"]);
			foreach ($data["cons"] as $key => $value) {

					$update_rel_command = "UPDATE tbl_contrato_formula_condicionales SET";
					if(array_key_exists("is_true_id", $value)){
						$update_rel_command = "UPDATE tbl_contrato_formula_condicionales SET";
						$update_rel_command.= " is_true_id = '".$data["cons"][$value["is_true_id"]]["id"]."'";
						$update_rel_command.= " WHERE id = '".$value["id"]."'";
						$mysqli->query($update_rel_command);
						echo $update_rel_command;
						echo "\n";
					}
					if(array_key_exists("is_false_id", $value)){
						$update_rel_command = "UPDATE tbl_contrato_formula_condicionales SET";
						$update_rel_command.= " is_false_id = '".$data["cons"][$value["is_false_id"]]["id"]."'";
						$update_rel_command.= " WHERE id = '".$value["id"]."'";
						$mysqli->query($update_rel_command);
						echo $update_rel_command;
						echo "\n";
					}
			}
			
		}
	}
	else if($opt=="contratos_actualiza_contrato_agente") {
		$query_contrato_agente = "
		UPDATE cont_cc_agente ca
		INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id
		LEFT JOIN tbl_locales loc ON cc.c_costos = loc.cc_id
		SET ca.porcentaje_participacion = ".$data["porcentaje_participacion"]." -- el nuevo porcentaje de participación
		WHERE tipo_contrato_id = 6 -- tipo de contrato 6:agente
		AND cc.status = 1 -- estado del contrato 1:activo
		AND cc.etapa_id = 5	-- si el contrato esta firmado
		AND ca.participacion_id = 5 -- tipo de participacion 5:DEPOSITO WEB
		AND loc.id = ".$data["local_id"];
		$mysqli->query($query_contrato_agente);

		echo $query_contrato_agente;
	}
}
function cons_data_to_db($d){
	global $mysqli;
	$tmp=array();
	$nulls=array("null","",false);
	foreach ($d as $k => $v) {
		// if($v===0){
		if(is_numeric($v)){
			// $tmp[$k]=$v;
			$tmp[$k]="'".$v."'";
		}elseif(in_array($v, $nulls)){
			$tmp[$k]="NULL";
		}else{
			if(is_float($v)){
				$tmp[$k]="'".$v."'";
			}elseif(is_int($v)){
				$tmp[$k]=$v;
			}else{
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		}
	}
	return $tmp;
}
?>