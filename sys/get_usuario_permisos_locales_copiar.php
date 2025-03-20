<?php
include("db_connect.php");
if(isset($_POST["opt"])){
	$opt = $_POST["opt"];	
	if ($opt=="sec_permisos_get_data_tipo_permisos_locales_copiar") {

		if ($filtro["tipo"]="locales") {
            $array_redes = [];
            $query = "SELECT id, nombre FROM tbl_locales_redes;";
            $exec_query = $mysqli->query($query);
            while( $item = $exec_query->fetch_assoc()){
                $array_redes[$item['id']] = $item['nombre'];
            }
		    /*
			$array_redes = [];
			$array_redes[1] = "Corporación PJ";
			$array_redes[2] = "Dalu";
			$array_redes[3] = "Pacific Lotteries";
			$array_redes[4] = "Terceros";*/

			$filtro = $_POST["filtro"];
			$sql_what = "id,nombre,IF(ISNULL(red_id),'4',red_id) as red_id ";
			if(array_key_exists("what", $_POST)){
				$sql_what = implode(",", $_POST["what"]);
			}
			$array_select = array();
			$array_locales_seleccionados = array();
			$array_locales_activos_x_red = array();
			$query_locales ="SELECT local_id,usuario_id FROM tbl_usuarios_locales 
			WHERE usuario_id  ='".$filtro["usuario"]."' AND estado = '1'";
			$result_locales = $mysqli->query($query_locales);
			while($row_locales_asignados = $result_locales->fetch_assoc()) {
				$array_locales_seleccionados[$row_locales_asignados["local_id"]]=$row_locales_asignados["local_id"];
			}
			$array_lista_locales_disponibles = array();
			$sql_command = "SELECT id,nombre,IF(ISNULL(red_id),'4',red_id) as red_id FROM tbl_locales ORDER BY nombre ";
			$sql_query = $mysqli->query($sql_command);
			$row_cnt = $sql_query->num_rows;
			$data = array();		
			while($itm=$sql_query->fetch_assoc()){
				$array_lista_locales_disponibles[$itm["id"]] =  $itm["id"];
				if (array_key_exists($itm["id"], $array_locales_seleccionados)) {
					$array_select[$itm["red_id"]][]= array("nombre"=>$itm["nombre"],"id"=>$itm["id"],"checked"=>"checked","estado"=>"activo");
				}else{
					$array_select[$itm["red_id"]][]= array("nombre"=>$itm["nombre"],"id"=>$itm["id"],"checked"=>"unchecked","estado"=>"desactivo");	
				}	
			}	
			?>
			<table class="" width="100%" cellpadding="0" cellspacing="0">
				<?php
				foreach ($array_select as $red => $locales_data) {
					?>		
						<tr class="parent_redes_usuarios_permisos_copiar">
							<td>
								<span class="icon node-icon glyphicon glyphicon-tower icono_red_usuarios_permisos_copiar">
								</span>								
								<span class="nombre_red_usuarios_permisos_copiar"><?php echo $array_redes[$red]; ?></span>
							</td>
							<td></td>
							<td></td>						
							<td>
			                    <div class="checkbox">
									<input class="checkbox_locales_red_copiar checkbox_red_copiar checkbox_red_copiar_<?php echo $red; ?>"
											<?php
											foreach ($locales_data as $key => $local) {
												if (in_array("activo",$local)) {

													?>
													checked = "checked"
												<?php
												}
											}	
											?>
									data-red="<?php echo $red; ?>"  type="checkbox" disabled="disabled"></input>
									<label></label>
			                    </div>
							</td>
						</tr>
						<?php	
						foreach ($locales_data as $key => $val_local) {
							?>			
							<tr class="rows_hidden_usuarios_permisos_copiar children_usuarios_permisos_copiar children_row_collapse_expand_copiar_<?php echo $red; ?> checkbox_me_usuarios_permisos_copiar">
								<td class="td_nombres_locales_usuarios_copiar">
									<span class="glyphicon glyphicon-home locales_usuarios_span_copiar"></span>
									<span class="td_id_locales_usuarios_copiar"><?php echo $val_local["id"]; ?></span>
									<span><?php echo $val_local["nombre"]; ?></span>
								</td>
								<td class=""></td> 
								<td class="td_red_locales_usuarios_copiar"><?php echo $array_redes[$red]; ?></td>
								<td class="checkbox_me_locales_usuarios_copiar">
				                    <div class="checkbox ">							
										<input type="checkbox"
										<?php
											if (array_key_exists($val_local["id"],$array_locales_seleccionados)) {
										?>
												checked = "checked"
										<?php
											}
										?>
										 value='<?php echo $val_local["id"]; ?>' class="checkbox_locales_to_usuarios_copiar checkbox-primary 
										 checkbox_locales_to_usuarios_copiar_<?php echo $red; ?>" data-red="<?php echo $red; ?>"  disabled="disabled"></div>
										<label></label>
				                    </div>		 
								</td>
							</tr>
							<?php
						}					
				}
				?>
			</table>
		<?php		
		}
	}
}
?>			