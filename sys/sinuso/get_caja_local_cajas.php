<?php
if(isset($_POST["get_local_cajas"])){
	$local_id = $_POST["get_local_cajas"];
	$sql_command = "SELECT 
					lc.id as caja_id, 
					lc.caja_tipo_id as tipo_id,
					lc.nombre as caja_nombre,
					ct.nombre as tipo_nombre
					FROM tbl_local_cajas lc
					INNER JOIN tbl_caja_tipos ct ON (ct.id = lc.caja_tipo_id)
					WHERE lc.estado = '1'
					AND lc.local_id ='".$local_id."'
					ORDER BY lc.caja_tipo_id ASC , lc.nombre ASC";			
	$sql_query = $mysqli->query($sql_command);
	while($itm=$sql_query->fetch_assoc()){
		?>
		<option value="<?php echo $itm["caja_id"];?>"><?php echo $itm["caja_nombre"];?></option>
		<?php
	}
}
?>