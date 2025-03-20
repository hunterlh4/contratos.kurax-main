<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("db_connect.php");

if(isset($_POST["opt"])){
	if($_POST["opt"]=="formula_tipo_3_copy"){

		function get_condicionales($f){
			global $mysqli;
			$command = "
				SELECT
					fc.id,
					fc.var1,
					fc.var_operador,
					fc.var2,
					fc.is_true_id,
					fc.is_false_id,
					fc.valor,
					fc.valor_operador,
					fc.donde,
					fc.tipo
				FROM tbl_contrato_formula_condicionales fc
				WHERE fc.id IS NOT NULL
				AND fc.formula_id = '".$f["formula_id"]."'
				AND fc.contrato_id = '".$f["contrato_id"]."'
				AND fc.servicio_id = '".$f["servicio_id"]."'
				AND fc.producto_id = '".$f["producto_id"]."'
				AND fc.estado = '1'
				ORDER BY fc.ord ASC
				";
			$query = $mysqli->query($command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$return = array();
			while($fc = $query->fetch_assoc()){
				$return[$fc["id"]]=$fc;
			}
			return $return;
		}
		function build_formula($fs,$f_id){
			$f=$fs[$f_id];
			?>
			<div 
				class="f_condicional" 
				id="f_condicional_<?php echo $f_id;?>" 
				data-tmp_id="<?php echo $f_id;?>"
				data-tipo="<?php echo $f['tipo'];?>"
				data-var1="<?php echo $f['var1'];?>"
				data-var_operador="<?php echo $f['var_operador'];?>"
				data-var2="<?php echo $f['var2'];?>"
				data-valor="<?php echo $f['valor'];?>"
				data-valor_operador="<?php echo $f['valor_operador'];?>"
				data-donde="<?php echo $f['donde'];?>"
				>
				<div class="input-group f_line_holder">
					<?php
					if($f["tipo"]=="if"){
						?><span class="form-control con_text" id="con_text_<?php echo $f_id;?>">SI(<?php echo $f['var1'];?> <?php echo $f['var_operador'];?> <?php echo $f['var2'];?>){</span><?php
					}else{
						?><span class="form-control con_text" id="con_text_<?php echo $f_id;?>"><?php echo $f['valor'];?><?php echo $f['valor_operador'];?> <?php echo $f['donde'];?></span><?php
					}
					?>																					
					<?php 
						$this_f = $f;
						$this_f["where"] = "f_condicional"."_".$f_id;
						c_form_con_build_buttons(2,$this_f); 
					?>
				</div>
				<?php if($f["tipo"]=="if"){ ?>
					<div class="f_if_holder" id="<?php echo $f_id;?>_f_if_holder">
						<?php 
						if($f["is_true_id"]){
							build_formula($fs,$f["is_true_id"]);
						}else{
							//btn_data.where = f_condicional_id+'_'+'f_if_holder';
							$this_f = $f;
							$this_f["where"] = $f_id."_"."f_if_holder";
							c_form_con_build_buttons(1,$this_f);
						}
						?>
					</div>
					<div class="form-control">}DE LO CONTRARIO{</div>
					<div class="f_else_holder" id="<?php echo $f_id;?>_f_else_holder">
						<?php 
						if($f["is_false_id"]){
							build_formula($fs,$f["is_false_id"]);
						}else{
							// c_form_con_build_buttons(1,$f);
							$this_f = $f;
							$this_f["where"] = $f_id."_"."f_else_holder";
							c_form_con_build_buttons(1,$this_f);
						}
						?>
					</div>
					<span class="form-control">}</span>
				<?php } ?>
			</div>
			<?php
		}
		function c_form_con_build_buttons($opt=false,$data=false){
			?>
			<span class="input-group-btn">
				<?php
				if($opt==1){
					?>
					<button
						class="btn btn-primary c_form_con_btn"
						data-id="<?php echo $data["id"];?>"
						data-what="add"
						data-where="<?php echo $data["where"];?>"
						>
						<span class="glyphicon glyphicon-plus"></span>
					</button>
					<?php
				}elseif($opt==2){
					?>
					<button 
						class="btn btn-warning c_form_con_btn"
						data-what="edit"
						data-id="<?php echo $data["id"];?>"
						data-where="<?php echo $data["where"];?>"
						data-tipo="<?php echo $data["tipo"];?>"
						>
						<span class="glyphicon glyphicon-pencil"></span>
					</button>
					<button
						class="btn btn-danger c_form_con_btn"
						data-what="remove"
						data-id="<?php echo $data["id"];?>"
						data-where="<?php echo $data["where"];?>"
						>
						<span class="glyphicon glyphicon-minus"></span>
					</button>
					<?php
				}elseif($opt==3){
					?>
					<button
						class="btn btn-primary c_form_con_btn"
						data-what="add"
						data-where="new"
						>
						<span class="glyphicon glyphicon-plus"></span>
					</button>
					<?php
				}
				?>	
			</span>
			<?php
		}
		if($_POST["data"]["cf"]){
			// print_r($_POST["data"]["cf"]);
			$to_copy_command = "SELECT 
									cf.formula_id,
									cf.contrato_id,
									cf.servicio_id,
									cf.producto_id
								FROM tbl_contrato_formulas cf
								WHERE cf.id = '".$_POST["data"]["cf"]."'";
			$to_copy_query = $mysqli->query($to_copy_command);
			$to_copy = $to_copy_query->fetch_assoc();
			// print_r($to_copy);
			$condicionales = get_condicionales($to_copy);
			$first_id = array_values($condicionales)[0]["id"];
			build_formula($condicionales,$first_id);
			// print_r($condicionales);
		}
	}
}
$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
// print_r(json_encode($return));
?>