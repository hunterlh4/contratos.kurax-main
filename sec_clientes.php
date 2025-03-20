<?php
	$id_padre=0;
	$m=$mysqli->query("SELECT nombre, tabla, opciones, input_text, switch FROM adm_mantenimientos WHERE tabla = 'tbl_".$sec_id."'")->fetch_assoc();
?>
<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> Clientes</div>
			</div>
		</div>
		<!-- BOTON AGREGAR NUEVO CLIENTE Y FORMULARIO-->
		<div class="row">
			<div class="col-xs-12">
				<?php
				$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' LIMIT 1")->fetch_assoc();				
				if($item_id){

					?>
					<a class="btn btn-default" href="./?sec_id=<?php echo $sec_id;?>">
        				<i class="glyphicon glyphicon-arrow-left"></i>
        				Regresar
        			</a>
	                <button type="button" data-then="exit" class="save_btn btn btn-success" data-button="save">
	                	<i class="glyphicon glyphicon-floppy-save"></i>
	                	Guardar y Salir
	                </button>
	                <button type="button" data-then="reload" class="save_btn btn btn-success" data-button="save">
	                	<i class="glyphicon glyphicon-floppy-save"></i>
	                	Guardar
	                </button>
	                
              	
					<?php
				}else{
					?>
					<a 
						href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=new"
						id="" 
						data-sec="<?php echo $sec_id;?>" 
						data-table="tbl_clientes"
						class="btn btn-rounded btn-min-width btn-success btn-add"><i class="glyphicon glyphicon-plus"></i>Agregar</a>

						<!--<button class="btn_asignar_permisos_multiple_usuarios" ><i class="glyphicon glyphicon-equalizer"></i>Permisos</button>-->
					<?php
				}
				?>
			</div>
		</div>
		<!-- FIN BOTON AGREGAR NUEVO CLIENTE Y FORMULARIO-->		
	</div>
	<?php

	if($item_id){
		$item = $mysqli->query("SELECT id,	
										tipo_cliente_id,
										ruc,
										dni,
										razon_social,
										nombre,
										email,
										telefono,
										celular,
										direccion,
										ubigeo_id,
										banco_id,
										moneda_id,
										numero_cuenta,
										representante_id,
										infocorp,
										como_se_entero,
										como_se_entero_des,
										estado
										FROM tbl_clientes
										WHERE id = '".$item_id."'")->fetch_assoc();
		?>
		<input type="hidden" class="save_data" data-col="table" value="<?php echo $m["tabla"];?>" />
		<input type="hidden" class="save_data" data-col="id" value="<?php echo $item_id;?>" />
		<!-- FORMULARIO CREAR NUEVO/EDITAR CLIENTE -->
		<div class="row">
        	<div class="col-xs-12 ">
        		<?php
        		include("sec_adm_mantenimientos_form.php");
        		?>
        	</div>
        </div>
		<!-- FIN FORMULARIO CREAR NUEVO/EDITAR CLIENTE -->		
		<?php
	}else{
		$list_query=$mysqli->query("SELECT  id,
											tipo_cliente_id,
											ruc,
											dni,
											razon_social,
											nombre,
											email,
											telefono,
											celular,
											direccion,
											ubigeo_id,
											banco_id,
											moneda_id,
											numero_cuenta,
											representante_id,
											infocorp,
											como_se_entero,
											como_se_entero_des,
											estado
											FROM tbl_clientes ORDER BY id DESC");


		$list=array();
		while ($li=$list_query->fetch_assoc()) {
			$list[]=$li;
		}
		if($mysqli->error){
			print_r($mysqli->error);
		}
		$list_cols = array();
		
			$list_cols["id"]="ID";
			$list_cols["razon_social"]="RAZON SOCIAL";
			$list_cols["nombre"]="NOMBRE";			
			$list_cols["estado"]="ESTADO";
			$list_cols["opciones"]="OPCIONES";
		?>
		<!-- TABLA CLIENTES -->
		<div class="row">
			<div class="col-xs-12">
				<input type="hidden" class="export_clientes_filename" value="export_clientes_<?php echo date("c");?>">
				<table 
					id="clientes_list"
					class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
		            <thead>
		                <tr>
		                	<?php
		                	foreach ($list_cols as $key => $value) {
		                		if($key=="id"){
		                			?><th class="w-20px">ID</th><?php
		                		}elseif($key=="estado"){
		                			?><th class="w-85px text-center">ESTADO</th><?php
		                		}elseif($key=="opciones"){
		                			?><th class="w-85px text-center">OPCIONES</th><?php
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
												class="btn btn-rounded btn-default btn-sm btn-edit btn_editar_clientes" 
												title="Editar"
												data-button="edit"
												data-href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=<?php echo $l_v["id"];?>"
												href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=<?php echo $l_v["id"];?>">
												<i class="glyphicon glyphicon-edit"></i>												
											</a>																
												<button 
													class="btn btn-rounded btn-default btn-sm btn-edit btn-preview btn_vista_previa_clientes" 
													title="Vista Previa"
													data-button="information" 
													data-table="<?php echo 'tbl_clientes'; ?>"
													data-id="<?php echo $l_v["id"];?>"
													>
													<i class="glyphicon glyphicon-info-sign"></i>
												</button>	
												

										</td>
			                			<?php
			                		}
									elseif($key=="estado"){
				                			?><td class="text-center"><?php
				                			if($l_v["estado"]){ 
												?><div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div><?php 
											}else{ 
												?><div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div><?php
											}
											?></td><?php
										}
									
									elseif($key=="id"){
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
        <!-- FIN TABLA CLIENTES -->
		<?php
	}
	?>
</div>



