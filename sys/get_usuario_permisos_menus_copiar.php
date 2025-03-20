<?php
// print_r($_POST);
// exit();
include("db_connect.php");
if(isset($_POST["opt"])){
	$opt = $_POST["opt"];	
	if ($opt=="sec_permisos_get_data_tipo_permisos_menus_copiar") {
		if ($filtro["tipo"]="menus") {
			$btn_text = array();
			$query_botones_text ="SELECT id,boton FROM tbl_botones";
			$result_btns_text = $mysqli->query($query_botones_text);
			while($row_btns = $result_btns_text->fetch_assoc()) {
				$btn_text[$row_btns["id"]] = $row_btns["boton"];

			}

			$filtro = $_POST['filtro'];
			$array_menu_id=array();
			$query_menu_usuarios_botones="SELECT usuario_id,menu_id,boton_id,boton_nombre FROM tbl_permisos 
			WHERE usuario_id='".$filtro['usuario']."' AND estado = '1'";
			$result_menu_usuarios_botones = $mysqli->query($query_menu_usuarios_botones);
			while ($row_menu_usuario_botones= $result_menu_usuarios_botones->fetch_assoc()) {
				$array_menu_id[$row_menu_usuario_botones["menu_id"]]=$row_menu_usuario_botones["menu_id"];
			}
			$query_menu_sistemas = "SELECT id,titulo,relacion_id FROM tbl_menu_sistemas ORDER BY COALESCE(relacion_id, '0')";
			$state=[];
			$result = $mysqli->query($query_menu_sistemas);
			while($row_menu_sistemas = $result->fetch_assoc()){
				 $sub_data["id"] = $row_menu_sistemas["id"];
				 $sub_data["name"] = $row_menu_sistemas["titulo"];
				 $sub_data["text"] = $row_menu_sistemas["titulo"];
				 $sub_data["selectable"]=true;
				 $sub_data["selectedIcon"]="glyphicon glyphicon-ok";
				 if (array_key_exists($row_menu_sistemas["id"], $array_menu_id)) {
				 		$state["checked"]=true;
				 }else{
				 		$state["checked"]=false;	
				 }
				 $state["expanded"]=true;
				 $sub_data["state"] = $state;
				 $sub_data["parent_id"] = $row_menu_sistemas["relacion_id"];
				 $pre_return[] = $sub_data;
			}
			foreach($pre_return as $key => &$value){
			 	$output[$value["id"]] = &$value;
			}
			foreach($pre_return as $key => &$value){
				if($value["parent_id"] && isset($output[$value["parent_id"]]))
				{
					$output[$value["parent_id"]]["nodes"][] = &$value;
				}
			}
			foreach($pre_return as $key => &$value){
				if($value["parent_id"] && isset($output[$value["parent_id"]]))
				{
					unset($pre_return[$key]);
				}
			}
			?>
			<table class="tbl_copiar_permisos_menus_sub_menus" cellpadding="0" cellspacing="0" width="100%">
				<?php
				foreach ($pre_return as $key_parent => $parent){
					?>					
					<tr>
						<td  class="tbl_menu_sub_menu_botones_primer_td_copiar" colspan="2">
							<span class="icon node-icon glyphicon glyphicon-list 
								tbl_menu_sub_menu_botones_icon_lista_abuelo">
							</span>
							<span class="tbl_menu_sub_menu_botones_texto">
								<?php echo "[".$parent["id"]."] ".$parent["text"]; ?>
							</span>
						</td>
						<td class="tbl_menu_sub_menu_botones_ultimo_td">
						</td>
					</tr>
					<tr class="tbl_menu_sub_menu_botones_padres_detalles tbl_menu_sub_menu_botones_padres_detalles_<?php echo $parent["id"]; ?> rows_hidden_usuarios_permisos_copiar_copiar">
						<td  class="tbl_menu_sub_menu_botones_primer_td_copiar tbl_menu_sub_menu_botones_primer_td_copiar_border_none"></td>						
						<td class="tbl_menu_sub_menu_botones_ultimo_td_copiar" colspan="2">	
							<table width="100%" class="tabla_menu_sub_menu_botones_checkbox_botones_padre_copiar">
								<tbody>
									<?php
											$result_final_selected = array();	
											$sql_selected = "SELECT boton_id,boton_nombre FROM tbl_permisos WHERE menu_id = '".$parent["id"]."' AND usuario_id='".$filtro['usuario']."' AND estado ='1'";
											$result_selected = $mysqli->query($sql_selected);
											while($row_selected = $result_selected->fetch_assoc()) {
												$result_final_selected[$row_selected['boton_id']] = $row_selected["boton_nombre"];
											} 
											$query_botones ="SELECT boton,nombre FROM tbl_menu_sistemas_botones 
											WHERE menu_id  ='".$parent["id"]."'";
											$result_btns = $mysqli->query($query_botones);
											while($row_btns = $result_btns->fetch_assoc()) {
											?>
												<tr>
													<td class="tbl_menu_sub_menu_botones_botones_padre_td_copiar">
														<span class="glyphicon glyphicon-th-large tbl_menu_sub_menu_botones_iconos"></span>
														<span class="tbl_menu_sub_menu_botones_texto_botones_padre">						
															<?php echo "[".$btn_text[$row_btns["boton"]]."] ".$row_btns["nombre"]; ?>
														</span>	
													</td>
													<td class="tbl_menu_sub_menu_botones_checbox" style="width:50px !important;">
														<div class="checkbox tbl_menu_sub_menu_botones_checkbox_botones_padre">
															<input type="checkbox"
															<?php if (array_key_exists($row_btns["boton"], $result_final_selected)){?> checked = "checked" <?php } ?>
															disabled="disabled"
															class="select_permisos_checkbox checkbox-warning"  
															value="<?php echo $row_btns["boton"]; ?>"
															data-menu-id="<?php echo $parent["id"]; ?>"/>
															<label></label>
														</div>													
													</td>
												</tr>	
											<?php			
											}
									?>
								</tbody>	
							</table>
						</td>
					</tr>					
					<?php
					if (isset($parent["nodes"])) {
						foreach ($parent["nodes"] as $key_parent_count => $parent_count) {
							?>
							<tr class="tbl_menu_sub_menu_botones_padres_detalles tbl_menu_sub_menu_botones_padres_detalles_<?php echo $parent["id"]; ?> rows_hidden_usuarios_permisos_copiar">
								<td class="tbl_menu_sub_menu_botones_primer_td_copiar tbl_menu_sub_menu_botones_primer_td_copiar_border_none" ></td>		
								<td>
									<span class="icon node-icon glyphicon glyphicon-equalizer 
										tbl_menu_sub_menu_botones_icon_lista_hijos_copiar">
									</span> 
								
									<span class="tbl_menu_sub_menu_botones_texto">										
										<?php echo "[".$parent_count["id"]."] ".$parent_count["text"]; ?>
									</span>	
								</td>
								<td class="tbl_menu_sub_menu_botones_ultimo_td">
								</td>
							</tr>
							<tr class="tbl_menu_sub_menu_botones_detalles tbl_menu_sub_menu_botones_detalles_<?php echo $parent_count["id"]; ?> rows_hidden_usuarios_permisos_copiar">
								<td  class="tbl_menu_sub_menu_botones_primer_td_copiar tbl_menu_sub_menu_botones_primer_td_copiar_border_none" ></td>
								<td  class="tbl_menu_sub_menu_botones_ultimo_td_copiar " colspan="2">									
									<table class="tbl_sub_menu_botones" width="95%"    >
										<tbody>
											<?php
													$result_final_selected = array();	
													$sql_selected = "SELECT boton_id,boton_nombre FROM tbl_permisos WHERE menu_id = '".$parent_count["id"]."' AND usuario_id='".$filtro['usuario']."' AND estado ='1'";
													$result_selected = $mysqli->query($sql_selected);
														while($row_selected = $result_selected->fetch_assoc()) {
															$result_final_selected[$row_selected['boton_id']] = $row_selected["boton_nombre"];
														} 

													$query_botones ="SELECT boton,nombre FROM tbl_menu_sistemas_botones 
													WHERE menu_id  ='".$parent_count["id"]."'";
													$result_btns = $mysqli->query($query_botones);
													while($row_btns = $result_btns->fetch_assoc()) {
														?>
														<tr class="tr_tbl_sub_menu_botones">
															<td class="tbl_menu_sub_menu_botones_botones_hijos_td_copiar">
																<span class="glyphicon glyphicon-th-large tbl_menu_sub_menu_botones_iconos_hijo">
																</span>
																<span class="tbl_menu_sub_menu_botones_texto_botones_hijo">				
																	<?php echo "[".$btn_text[$row_btns["boton"]]."] ".$row_btns["nombre"]; ?>
																</span>														
															</td>
															<td class="td_menu_sub_menu_botones_checkbox_botones_hijo" style="width:15px !important;">
																<div class="checkbox tbl_menu_sub_menu_botones_checkbox_botones_hijo">
																	<input type="checkbox"
																	<?php if (array_key_exists($row_btns["boton"], $result_final_selected)){?> checked = "checked" <?php } ?>
																	disabled="disabled"
																	class="select_permisos_checkbox checkbox-warning"  
																	value="<?php echo $row_btns["boton"]; ?>"
																	data-menu-id="<?php echo $parent_count["id"]; ?>"/>
																	<label></label>
																</div>													
															</td>
														</tr>	
														<?php			
													}
											?>								
										</tbody>	
									</table>
								</td>									
							</tr>
							<?php		
						}
					}
				}
				?>
			</table>
		<?php			
		}
	}
}
?>			