<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> Permisos   </div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<?php
				if($item_id){
					?>
					<a class="btn btn-default" href="./?sec_id=<?php echo $sec_id;?>">
        				<i class="glyphicon glyphicon-arrow-left"></i>
        				Regresar
        			</a>
	                <button type="button" data-then="exit" class="save_btn btn btn-success">
	                	<i class="glyphicon glyphicon-floppy-save"></i>
	                	Guardar y Salir
	                </button>
	                <button type="button" data-then="reload" class="save_btn btn btn-success">
	                	<i class="glyphicon glyphicon-floppy-save"></i>
	                	Guardar
	                </button>
	                <?php
	                	if ($item_id!="new") {
	                ?>
	                <button 
	                	type="button" 
	                	data-then="exit" 
	                	data-table="tbl_personal_apt" 
	                	data-id="<?php echo $item_id;?>" 
	                	class="del_btn btn btn-danger pull-right">
		                	<i class="glyphicon glyphicon-remove"></i>
		                	Eliminar
	                	</button>
	                <?php	
	                	}
	                ?>	
					<?php
				}else{
					?>
					<a 
						href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=new"
						id="" 
						data-sec="<?php echo $sec_id;?>" 
						data-table="tbl_personal_apt"
						class="btn btn-rounded btn-min-width btn-success btn-add"><i class="glyphicon glyphicon-plus"></i>Agregar</a>
					<?php
				}
				?>
			</div>
				
		</div>
	</div>
	<?php
	if($item_id){
		$item = $mysqli->query("SELECT 
                                        id,
                                        grupo_id,
                                        codigo_id,
                                        relacion_id,
                                        relacion_grupo_id,
                                        usuario_id,
                                        personal_id,
                                        sistema_id,
                                        menu_id,
                                        estado 
                                        FROM tbl_permisos
									    WHERE id = '".$item_id."'")->fetch_assoc();
		
		?>
		<input type="hidden" class="save_data" data-col="table" value="tbl_personal_apt">
		<input type="hidden" class="save_data" data-col="id" value="<?php echo $item_id;?>">

		<div class="col-md-2 hidden-md hidden-lg">
			<ul class="nav nav-tabs local_tabs">
				<li class="active"><a class="tab_btn" href="#tab_usuario" data-tab="tab_usuario">Datos</a></li>
				<li class=""><a class="tab_btn" href="#tab_permisos" data-tab="tab_permisos">Permisos</a></li>
			</ul>
			<br>
		</div>
		<div class="col-md-10 ">
			<div class="tab-content">
				<div class="tab-pane active" id="tab_usuario">
					<div class="col-xs-12 col-md-12 col-lg-12">	
						<div class="col-xs-12 col-md-6 col-lg-6">	
							<p class="usuario_label">Usuario</p>
		            			<select class="select_permisos " name="select_permisos" >
								    <option value="-1">Seleccione</option>
								</select>
								<button type="button" class="btn btn-info btn_copiar_user_settings">
									COPIAR
								</button>
						</div>
						<div  class="col-xs-12 col-md-6 col-lg-6">

						</div>
					</div>
					<div class="col-xs-12 col-md-12 col-lg-12">


					<div class="col-xs-12 col-md-8 col-lg-8">	
							<table id="table_tree_permisos" class="table-hover table-striped tree" cellspacing="0" width="100%">
						        <thead>
						            <tr>
						            	<th></th>
						                <th>ID</th>
						                <th>TITULO</th>
						                <th>RELACIÓN</th>
						                <th>OPCIÓN</th>							                
						            </tr>
						        </thead>
						        <tbody>

								<?php

										$sql_selected = "SELECT id,titulo,relacion_id FROM tbl_menu_sistemas";
										$result_selected = $mysqli->query($sql_selected);
										$array_nombres_menu_sistemas= array();
										$array_relacion_ids=array();
										while($row_selected = $result_selected->fetch_assoc()) {
											$array_nombres_menu_sistemas[$row_selected["id"]]=$row_selected["titulo"];
											$array_relacion_ids[$row_selected["id"]] = $row_selected["relacion_id"];
										} 
										/*
										print "<pre>";
											print_r($array_relacion_ids);
										print "</pre>";	
										*/
										$sql_command 
										= 
										"SELECT 
										id,
										grupo_id,
										titulo,
										sec_id,
										sub_sec_id,
										codigo_id,
										descripcion,
										relacion_id,
										relacion_grupo_id,
										sistema_id,
										estado,
										ord 
										FROM 
										tbl_menu_sistemas";
											$ni=0;
											$parent=false;
											$sql_query = $mysqli->query($sql_command);
											while($l_v=$sql_query->fetch_assoc()){
												/*
												if (in_array($l_v["id"],$array_relacion_ids)) {
														print $l_v["id"];
												}
												*/
						
									?>				
										<tr class="clickable-row_permisos treegrid-<?php echo $ni;?> <?php if($parent){?>treegrid-parent-<?php echo $parent;}if($l_v["relacion_id"]!=0 && $l_v["relacion_grupo_id"]==1){?> children_color_row<?php	}if(in_array($l_v["id"],$array_relacion_ids)) {echo " parent_color_row";}?>">
									<?php
											print "		<td></td>
														<td>".$l_v['id']."</td>
														<td>".$l_v['titulo']."</td>						
														<td>".$l_v['relacion_id']."</td>
														<td><input type='radio' name='permisos' value='".$l_v['id']."' class='checkbox_option_menu_sistemas'></td>
														</tr>"; 
											$ni++;
											$parent=true;													
											}
									?>
									</tr>



						        </tbody>
						    </table>
					</div>	    
					<div class="col-xs-12 col-md-4 col-lg-4">
							

							<h3>SELECCIONE:</h3>
							<table id="table_botones_permisos" class="table table-hover table-bordered" cellspacing="0" width="100%">
						        <thead>
						            <tr>
						           		<th></th>
						                <th></th>
						                <th></th>						                

						            </tr>
						        </thead>
						        <tbody>
						        </tbody>
						        <tfoot>
						            <tr>
						           		<th></th>						            
						                <th></th>
						                <th></th>						                

						            </tr>
						        </tfoot>
						    </table>
						<div class="container_button_asignar_permisos">    	

							<button type="button" class="btn btn-primary btn_save_settings_users_permisos">GUARDAR</button>
						</div>	



							
					</div>









			










































					</div>				

				</div>
				<div class="tab-pane" id="tab_permisos">
					<?php
					$botones=array();
					$botones_query = $mysqli->query("SELECT id, nombre, boton FROM tbl_botones ORDER BY boton");
					while ($btn=$botones_query->fetch_assoc()) {
						$botones[]=$btn;
					}
					$permisos=array();
					$permisos_query = $mysqli->query("SELECT id,menu_id,boton FROM tbl_usuario_permisos WHERE usuario_id = '".$item["id"]."'");
					while($per=$permisos_query->fetch_assoc()){
						$permisos[$per["menu_id"]][]=$per["boton"];
					}
					//print_r($permisos);
					?>
					<div class="col-xs-12 col-md-6 col-md-offset-3 col-sm-10 col-sm-offset-1" style="background-color:orange !important;">
						<div class="panel" id="datos_de_contrato">
					        <div class="panel-heading">
					            <div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Configuracion</div>
					        </div>
					        <div id="panel-datos_de_contrato" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
					            <div class="panel-body locales_form_local">
					            	<?php
					            	$menu_command = "SELECT id,titulo,relacion_id,grupo_id,tipo,sec_id,sub_sec_id,icon,estado,ord 
                                        FROM tbl_menu_sistemas 
                                        WHERE estado = '1'
                                        ORDER BY ord ASC";
				                    $menu_query=$mysqli->query($menu_command);
				                    $menu_db_list=array();
				                    while ($li=$menu_query->fetch_assoc()) {
				                        $menu_db_list[]=$li;
				                    }
				                    $menu_list=array();
				                    foreach ($menu_db_list as $dbdb_k => $sbdb_val) {
				                        if($sbdb_val["relacion_id"]==0){
				                            $sbk=0;
				                            foreach ($menu_db_list as $dbdb_ks => $sbdb_vals) {
				                                if($sbdb_val["id"]==$sbdb_vals["relacion_id"]){
				                                    $sbdb_val["submenu"][$sbk]=$sbdb_vals;
				                                    $sbk++;
				                                }
				                            }
				                            $menu_list[]=$sbdb_val;
				                        }
				                    }
				                    ?>
				                    <ul>
				                    <?php
					                    foreach ($menu_list as $sb_key => $sb_val) {
					                    	if($sb_val["grupo_id"]==1){
					                            ?>
					                            <li>
				                                    <i class="icon icon-inline <?php echo $sb_val["icon"];?>"></i>
				                                    <span class="title"><?php echo $sb_val["titulo"];?></span>
				                                    <div class="btns_checkbox_holder" data-opt="<?php echo $sb_key;?>">
				                                    	<div class="checkbox">
															<label>
																<input type="checkbox" value="1" class="checkbox_todos" data-opt="<?php echo $sb_key;?>">
																Todos
															</label>
														</div>
				                                    	<?php
				                                    	foreach ($botones as $b_key => $b_val) {
				                                    		?>
															<div class="checkbox">
																<label>
																	<input 
																		type="checkbox" 
																		value="1" 
																		class="checkbox_btn save_extra" 
																		data-type="usuario_permiso"
																		data-menu="<?php echo $sb_val["id"];?>"
																		data-boton="<?php echo $b_val["boton"];?>"
																		data-opt="<?php echo $sb_key;?>"
																		<?php 
																			if(array_key_exists($sb_val["id"], $permisos)){
																				if(in_array($b_val["boton"], $permisos[$sb_val["id"]])){
																					?> checked<?php 
																				}
																			} 
																		?>>
																	<?php echo $b_val["nombre"];?>
																</label>
															</div><?php
				                                    	}
				                                    	?>
				                                   	</div>
					                                <?php
					                                if(array_key_exists("submenu", $sb_val)){
					                                    ?>
					                                    <ul class="sub-menu" data-menu-title="<?php echo $sb_val["titulo"];?>">
					                                    <?php
					                                    foreach ($sb_val["submenu"] as $sub_key => $sub_value) {
					                                        ?>
					                                        <li>
				                                    			<i class="icon icon-inline <?php echo $sub_value["icon"];?>"></i>
					                                            <span class="title"><?php echo $sub_value["titulo"];?></span>
					                                            <div class="btns_checkbox_holder" data-opt="<?php echo $sb_key.$sub_key;?>">
							                                    	<div class="checkbox">
																		<label>
																			<input type="checkbox" value="1" class="checkbox_todos" data-opt="<?php echo $sb_key.$sub_key;?>">
																			Todos
																		</label>
																	</div>
							                                    	<?php
							                                    	foreach ($botones as $b_key => $b_val) {
							                                    		?>
																		<div class="checkbox">
																			<label>
																				<input 
																					type="checkbox" 
																					value="1" 
																					class="checkbox_btn save_extra" 
																					data-type="usuario_permiso"
																					data-menu="<?php echo $sub_value["id"];?>"
																					data-boton="<?php echo $b_val["boton"];?>"
																					data-opt="<?php echo $sb_key.$sub_key;?>"
																					<?php 
																						if(array_key_exists($sub_value["id"], $permisos)){
																							if(in_array($b_val["boton"], $permisos[$sub_value["id"]])){
																								?> checked<?php 
																							}
																						} 
																					?>>
																				<?php echo $b_val["nombre"];?>
																			</label>
																		</div><?php
							                                    	}
							                                    	?>
							                                   	</div>
					                                        </li>
					                                        <?php
					                                    }
					                                    ?>
					                                    </ul>
					                                    <?php
					                                }
					                                ?>
					                            </li>
					                            <?php
					                        }

					                    }
					                ?>
					                </ul>
					            </div>
					        </div>
					    </div>
				    </div>
				</div>
			</div>
		</div>

		<?php
	}else{
        $list_query=$mysqli->query("SELECT tblu.id,
                                           tblu.nombre,
                                           tblu.usuario,
                                           tblu.password,
                                           tblp.grupo_id,
                                           tblp.codigo_id,
                                           tblp.relacion_id,
                                           tblp.relacion_grupo_id,
                                           tblp.usuario_id,
                                           tblp.personal_id,
                                           tblp.sistema_id,
                                           tblp.estado
                                        FROM tbl_usuarios tblu
                                        INNER JOIN tbl_permisos tblp 
                                        ON tblu.id = tblp.usuario_id");
		$list=array();
		while ($li=$list_query->fetch_assoc()) {
			$list[]=$li;
		}

		$list_cols = array();
			$list_cols["id"]="ID";
            $list_cols["nombre"]="USUARIO";
			$list_cols["grupo_id"]="GRUPO ID";
			$list_cols["codigo_id"]="CODIGO ID";
			$list_cols["relacion_id"]="RELACIÓN ID";
			$list_cols["relacion_grupo_id"]="RELACIÓN GRUPO ID";
            $list_cols["usuario_id"]="COD USUARIO";            
			$list_cols["personal_id"]="COD PERSONAL";
			$list_cols["sistema_id"]="COD SISTEMA";
            $list_cols["estado"]="ESTADO";
            $list_cols["opciones"]="OPCIONES";  

			

		?>
		<div class="row">
			<div class="col-xs-12">
				<table id="tbl_permisos"
					class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
		            <thead>
		                <tr>
		                	<?php
		                	foreach ($list_cols as $key => $value) {
		                		
		                		if($key=="id"){
		                			?><th class="w-20px">ID</th><?php
		                		}elseif($key=="opciones"){
		                			?><th class="w-65px">OPCIONES</th><?php
		                		}elseif($key=="estado"){
		                			?><th class="w-65px">ESTADO</th><?php
		                		}else{
			                		?>

			                		<th><?php echo $value;?></th>
			                		<?php
			                	}
		                	}
		                	?>		                    
		                </tr>

		            </thead>
		            <tbody>
		            	<?php 
		            	foreach ($list as $l_k => $l_v) {
		                	?>	
			                <tr>			                	
			                	<?php
			                	foreach ($list_cols as $key => $value) {
			                		if($key=="opciones"){
			                			?>
										<td class="text-center">
											<a 
												class="btn btn-rounded btn-default btn-sm btn-edit" 
												title="Editar"
												href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=<?php echo $l_v["id"];?>">
												<i class="glyphicon glyphicon-edit"></i>												
											</a>
										</td>
			                			<?php
			                		}elseif($key=="estado"){
			                			?><td class="text-center"><?php
			                			if($l_v["estado"]){ 
											?><div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div><?php 
										}else{ 
											?><div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div><?php
										}
										?></td><?php
									}elseif($key=="id"){
				                		?>
				                		<td class="text-right "><?php echo $l_v[$key];?></td>
				                		<?php
			                		}else{
				                		?>
				                		<td><?php echo $l_v[$key];?></td>
				                		<?php
				                	}
			                	}
			                	?>
			                </tr>
			                <?php
			            }
			            ?>
		            </tbody>
		        </table>			       
	        </div>
        </div>
		<?php
	}
	?>
</div>

<!-- Modal Copiar -->
<div class="modal fade" id="modal_copiar_permisos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Usuario Seleccionado: <span class="current_user"></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
			<p class="usuario_label">Usuario a Copiar</p>	             
			<select class="select_permisos " id="usuario_a_copiar_permisos" name="select_permisos" >
			    <option value="-1">Seleccione</option>
			</select>
			<input type="text" class="usuario_seleccionado_a_copiar"/>
      </div>
      <div class="modal-footer">
        <!--<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>-->
        <button type="button" class="btn btn-primary">GUARDAR</button>
      </div>
    </div>
  </div>
</div>