<div class="content container-fluid">
	<div class="page-header wide">
		<h1 class="page-title"><?php echo $sec_id;?></h1>
		<div class="page-subtitle"><?php echo $sub_sec_id;?></div>
		<?php
		if($item_id){
			?><div class="panel-title">ID: <?php echo $item_id;?></div><?php
		}
		?>
	</div>

	<?php
	if($item_id){
		$item = $mysqli->query("SELECT id, nombre, tabla, opciones, input_text, switch, orden, estado 
									FROM adm_mantenimientos
									WHERE id = '".$item_id."'")->fetch_assoc();
		?>
		<input type="hidden" class="save_data" data-col="table" value="adm_mantenimientos">
		<input type="hidden" class="save_data" data-col="id" value="<?php echo $item["id"];?>">
		<div class="row">
        	<div class="col-xs-12">
        		<div class="col-xs-12 hidden">
        			<table class="table table-striped table-bordered">
        				<thead>
        					<tr>
	        					<th>col</th>
	        					<th>type</th>
	        					<th>input_col</th>
	        					<th>label</th>
	        					<th>title</th>
	        					<th>table</th>
	        					<th>cols</th>
	        					<th>plugin</th>
	        					<th>ord</th>
        					</tr>
        				</thead>
        				<tbody>
			        		<?php
			        		$form_items_query=$mysqli->query("SELECT id,form_table,form_col,input_type,input_col,input_label,input_title,input_table,input_cols,input_plugin,ord
			    									FROM adm_form_items
			    									WHERE form_table = '".$item["tabla"]."'
			    									ORDER BY form_col,ord");
					    	$form_items = array();
					    	while($fi=$form_items_query->fetch_assoc()){
					    		//$form_items[]=$fi;
					    	}
					    	//$form_items[]=array("id"=>"new","form_table"=>$item["tabla"],"form_col"=>"0","input_type"=>"text","input_col"=>"","input_label"=>"","input_title"=>"","input_table"=>"","input_cols"=>"","input_plugin"=>"","ord"=>count($form_items));

					    	foreach ($form_items as $t_k => $t_v) {
					    		if($t_v["form_col"]==0){
					    			?>
					    			<tr data-id="<?php echo $t_v["id"];?>" id="tr_<?php echo $t_v["id"];?>" class="adm_box_inputs">
					    				<td>
					    					<!--<input type="hidden" name="id" value="<?php echo $t_v["id"];?>">-->
					    					<input type="hidden" name="form_table" value="<?php echo $t_v["form_table"];?>">
					    					<input type="text" class="w-20px" name="form_col" value="<?php echo $t_v["form_col"];?>">
					    				</td>
					    				<td>
					    					<select name="input_type">
					    						<option value="select" <?php if($t_v["input_type"]=="select"){ ?>selected="selected"<?php } ?>>select</option>
					    						<option value="text" <?php if($t_v["input_type"]=="text"){ ?>selected="selected"<?php } ?>>text</option>
					    					</select>
					    				</td>
					    				<td>
					    					<input type="text" class="w-100px" name="input_col" value="<?php echo $t_v["input_col"];?>">
					    				</td>
					    				<td>
					    					<input type="text" name="input_label" value="<?php echo $t_v["input_label"];?>">
					    				</td>
					    				<td>
					    					<input type="text" name="input_title" value="<?php echo $t_v["input_title"];?>">
					    				</td>
					    				<td>
					    					<input type="text" class="w-100px" name="input_table" value="<?php echo $t_v["input_table"];?>">
					    				</td>
					    				<td>
					    					<input type="text" name="input_cols" value="<?php echo $t_v["input_cols"];?>">
					    				</td>
					    				<td>
					    					<input type="text" class="w-100px" name="input_plugin" value="<?php echo $t_v["input_plugin"];?>">
					    				</td>
					    				<td>
					    					<input type="text" class="w-20px" name="ord" value="<?php echo $t_v["ord"];?>">
					    				</td>
					    			</tr>
					    			<?php
									//build_input($t_v);
					    		}
					    	}
					    	//print_r($form_items);
		        			?>

			    			
        				</tbody>
        			</table>
                    <button type="submit" class="save_adm_btn btn btn-success">Guardar</button>
        		</div>
        		<div class="col-xs-4">
	                        
                	<?php
                	$form_items = array();
                	$form_items["input_text"]=array("nombre"=>"Nombre","tabla"=>"Tabla","input_text"=>"Campos de Texto","switch"=>"Switches");
                	foreach ($form_items["input_text"] as $key => $value) {
                		?>
                		<label for="varchar_<?php echo $key;?>"><?php echo $value;?></label>
                		<textarea 
                			class="form-control input_text" 
                			data-col="<?php echo $key;?>"
						    id="varchar_<?php echo $key;?>"
                			rows="3"><?php echo $item[$key];?></textarea>										                        		
                		<?php
                	}	                        	
                	?>
                    <button type="submit" class="save_btn btn btn-success" data-then="reload">Guardar</button>
                    <?php
	                    	$form_items["checkbox"]=array("estado"=>"Estado");
                        	foreach ($form_items["checkbox"] as $key => $value) {
                        		?>
                        		<div class="form-group">
									<div class="checkbox">
										<label for="checkbox_<?php echo $key;?>"><?php echo $value;?></label>
										<input 
				                   			class="switch" 
				                   			id="checkbox_<?php echo $key;?>"
											type="checkbox" 
											<?php if($item["estado"]){ ?>checked="checked"<?php } ?>
											data-table="adm_mantenimientos"
											data-id="<?php echo $item["id"];?>"
											data-col="estado"
											data-on-value="1"
											data-off-value="0">
									</div>
								</div>
                        		<?php
                        	}
	                    	?>	  
        		</div>
        		
        	</div>

        </div>
		<?php
	}else{
		$list_query=$mysqli->query("SELECT id, nombre, tabla, opciones, orden, estado 
									FROM adm_mantenimientos
									ORDER BY orden ASC");
		$list=array();
		while ($li=$list_query->fetch_assoc()) {
			$list[]=$li;
		}
		?>
		<div class="row">
			<div class="col-xs-12">
				<button 
					id="" 
					data-sec="<?php echo $sec_id;?>" 
					data-sub-sec="<?php echo $sub_sec_id;?>" 
					data-table="adm_mantenimientos"
					class="add_btn btn btn-min-width btn-success">Agregar</button>
			</div>
		</div>
		<hr>
		<div class="row">
			<div class="col-xs-12">
				<table class="table table-condensed table-bordered">
		            <thead>
		                <tr>
		                    <th class="col-xs-1">ID</th>
		                    <th>nombre</th>
		                    <th>tabla</th>
		                    <th>opciones</th>
		                </tr>
		            </thead>
		            <tbody class="sort_list" data-sort-tabla="adm_mantenimientos">
		            	<?php 
		            	foreach ($list as $l_k => $l_v) {
		                	?>	
			                <tr class="sort_item" data-sort-id="<?php echo $l_v["id"];?>">
			                    <td><?php echo $l_v["id"];?></td>
			                    <td><?php echo $l_v["nombre"];?></td>
			                    <td><?php echo $l_v["tabla"];?></td>
			                    <td>
			                    	<a href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=<?php echo $sub_sec_id;?>&amp;item_id=<?php echo $l_v["id"];?>" class="icon-theme fa fa-pencil icon-primary icon-theme-xs"></a>
			                    	<input 
				                   			class="switch" 
				                   			id="checkbox_<?php echo $l_k;?>"
											type="checkbox" 
											<?php if($l_v["estado"]){ ?>checked="checked"<?php } ?>
											data-table="adm_mantenimientos"
											data-id="<?php echo $l_v["id"];?>"
											data-col="estado"
											data-on-value="1"
											data-off-value="0">
			                    	<?php /*if($l_v["estado"]){ 
			                    		?><span class="label label-success">activo</span><?php 
			                    		}else{ 
		                    			?><span class="label label-danger">inactivo</span><?php
		                    			}*/ ?>
			                    	
			                    </td>
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