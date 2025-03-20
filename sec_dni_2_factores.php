<?php
// $menu_id = "";
// $result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' LIMIT 1");
// while($r = $result->fetch_assoc()) $menu_id = $r["id"];

// $query = "SELECT id, nombre FROM tbl_zonas";
// $result = $mysqli->query($query);
// $zonas = [];
// while ($row = $result->fetch_assoc()){
//     $zonas[] = $row;
// }

// $query = "SELECT id, cc_id, nombre FROM tbl_locales WHERE operativo = 1 AND red_id = 1";
// $result = $mysqli->query($query);
// $locales = [];
// while ($row = $result->fetch_assoc()){
//     $locales[] = $row;
// }

// $query = "SELECT id, nombre FROM tbl_garantia_estados";
// $result = $mysqli->query($query);
// $estados_solicitud = [];
// while ($row = $result->fetch_assoc()){
//     $estados_solicitud[] = $row;
// }

// $query = "SELECT id, nombre FROM tbl_garantia_sistemas WHERE estado = 1";
// $result = $mysqli->query($query);
// $sistemas = [];
// while ($row = $result->fetch_assoc()){
//     $sistemas[] = $row;
// }
// $query = "SELECT id, nombre FROM tbl_garantia_subsistemas WHERE estado = 1";
// $result = $mysqli->query($query);
// $subsistemas = [];
// while ($row = $result->fetch_assoc()){
//     $subsistemas[] = $row;
// }

// $query = "SELECT id, nombre FROM tbl_garantia_tipo_criticidad";
// $result = $mysqli->query($query);
// $tipo_criticidad = [];
// while ($row = $result->fetch_assoc()){
//     $tipo_criticidad[] = $row;
// }
include("sys/db_connect.php");

$query = "SELECT id,nombre from tbl_locales_redes";
$result = $mysqli->query($query);

if($mysqli->error){
	echo $mysqli->error . $query;
}

$locales_redes = [];
while ($row = $result->fetch_assoc()){
    $locales_redes[] = $row;
}
?>

<div class="content container-fluid">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> DNI 2 Factores</div>
            </div>
        </div>
    </div>
	<div class="col-md-10">
		<div class="tab-content">
            <div class="tab-pane active" id="tab_local_garantia">
                <div class="content container-fluid">
                    <div class="page-header wide">
                        <div class="row mb-4">
                            <div class="col-xs-12">
                                <div class="col-xs-12">
                                    <div class="form-inline">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row container_filtros_recaudacion">
                            <div id="div-fil-est" class="col-lg-2 col-xs-12">
                                <p class="text-center">Estado</p>
                                <select name="sec_dni_2_factores_estado_select" id="sec_dni_2_factores_estado_select" class="form-control input-sm select2" multiple="true" style="width:100%">
                                    <!-- <option value = "">Seleccione</option> -->
                                    <option value = "1">Activo</option>
                                    <option value = "0">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-lg-2 col-xs-12">
                                <p class="text-center">Locales Redes</p>
                                <select name="locales_redes_select" id="locales_redes_select" multiple="true" class="form-control input-sm select2" style="width:100%">
                                    <?php foreach ($locales_redes as $key => $value) {?>
                                    <option value="<?php echo $value["id"];?>"><?php echo $value["nombre"]?></option>
                                    <?php }?>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-12 col-xs-12 mt-2 mb-2">
                                <!--<p class="text-center">&nbsp;</p>-->
                                <a class="btn btn-rounded btn-primary btn-sm agregar_dni_2_factores" title="Agregar DNI 2 Factores" data-toggle="modal" data-target="#agregar_dni_2_factores">
                                    <i class="fa fa-plus"></i> Agregar
                                </a>
                                <button class="btn btn-success" id="btn_dni_2_factores_search">
                                    <span class="glyphicon glyphicon-search"></span> Consultar
                                </button>
                            </div>
                        </div>
                        
                    </div>
                <br>
                    <div class="row">
                        <!-- table-striped -->
                        <table 
                            id="tbl_dni_2_factores"
                            class="table table-hover table-condensed table-bordered " 
                            cellspacing="0" 
                            width="100%" style="width:100%">
                            <thead >
                                <tr>
                                    <th></th>		                    
                                    <th></th>		                    
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>			       
                    </div>
                </div>
            </div>

            <div class="tab-pane" id="tab_solicitudes_garantia">
                <div class="col-xs-12" id="cont_filtros_garantia_solicitudes" style="padding-bottom: 1%;">
                    <div class="row container_filtros_garantia_solicitudes">
                        <div class="col-xs-12 col-sm-6 col-lg-2">
                            <?php 
                                $query1 = "SELECT valor,estado from tbl_parametros_generales where codigo = 'time_cod_verif'";
                                $list_query1 = $mysqli->query($query1)->fetch_assoc();

                                if($mysqli->error){
                                    echo $mysqli->error . $query1;
                                }

                                $query2 = "SELECT valor,estado from tbl_parametros_generales where codigo = 'max_intentos_sms'";
                                $list_query2 = $mysqli->query($query2)->fetch_assoc();

                                if($mysqli->error){
                                    echo $mysqli->error . $query2;
                                }

                                $query3 = "SELECT valor,estado from tbl_parametros_generales where codigo = 'tiempo_intentos_sms'";
                                $list_query3 = $mysqli->query($query3)->fetch_assoc();

                                if($mysqli->error){
                                    echo $mysqli->error . $query3;
                                }

                                $query4 = "SELECT valor,estado from tbl_parametros_generales where codigo = '2doFactor_autenticacion'";
                                $list_query4 = $mysqli->query($query4)->fetch_assoc();
                                if($mysqli->error){
                                    echo $mysqli->error . $query4;
                                }
                            ?>
                            <!--104-->
                            <form id="form_time_cod_verif" method="POST" class="form-horizontal">
                                <input type="hidden" id="time_cod_verif" value="time_cod_verif">
                                <br>
                                <div class="form-group">
                                    <label  class="col-xs-5 control-label" for="dni">Código: time_cod_verif</label>
                                    <div class="col-xs-9">
                                        Valor: <input type="number" id="valor_104" name="valor_104" style="width:100%" value="<?= $list_query1["valor"] ?>">
                                        Estado: <input type="number" id="estado_104" name="estado_104" style="width:100%" value="<?= $list_query1["estado"] ?>">
                                    </div>
                                </div>
                                <button class="btn btn-success" id="btn_dni_2_factores_save_104">
                                    <span class="glyphicon glyphicon-save"></span> Guardar
                                </button>
                            </form>
                            <!--105-->
                            <form id="form_max_intentos_sms" method="POST" class="form-horizontal">
                                <input type="hidden" id="max_intentos_sms" value="max_intentos_sms">
                                <br>
                                <div class="form-group">
                                    <label  class="col-xs-5 control-label" for="dni">Código: max_intentos_sms</label>
                                    <div class="col-xs-9">
                                        Valor: <input type="number" id="valor_105" name="valor_105" style="width:100%" value="<?= $list_query2["valor"] ?>">
                                        Estado: <input type="number" id="estado_105" name="estado_105" style="width:100%" value="<?= $list_query2["estado"] ?>">
                                    </div>
                                </div>
                                <button class="btn btn-success" id="btn_dni_2_factores_save_105">
                                    <span class="glyphicon glyphicon-save"></span> Guardar
                                </button>
                            </form>
                            <!--106-->
                            <form id="form_tiempo_intentos_sms" method="POST" class="form-horizontal">
                                <input type="hidden" id="tiempo_intentos_sms" value="tiempo_intentos_sms">
                                <br>
                                <div class="form-group">
                                    <label  class="col-xs-5 control-label" for="dni">Código: tiempo_intentos_sms</label>
                                    <div class="col-xs-9">
                                        Valor: <input type="number" id="valor_106" name="valor_106" style="width:100%" value="<?= $list_query3["valor"] ?>">
                                        Estado: <input type="number" id="estado_106" name="estado_106" style="width:100%" value="<?= $list_query3["estado"] ?>">
                                    </div>
                                </div>
                                <button class="btn btn-success" id="btn_dni_2_factores_save_106">
                                    <span class="glyphicon glyphicon-save"></span> Guardar
                                </button>
                            </form>
                            <!--107-->
                            <form id="form_2doFactor_autenticacion" method="POST" class="form-horizontal">
                                <input type="hidden" id="a2doFactor_autenticacion" value="2doFactor_autenticacion">
                                <br>
                                <div class="form-group">
                                    <label  class="col-xs-5 control-label" for="dni">Código: 2doFactor_autenticacion</label>
                                    <div class="col-xs-9">
                                        Valor: <input type="number" id="valor_107" name="valor_107" style="width:100%" value="<?= $list_query4["valor"] ?>">
                                        Estado: <input type="number" id="estado_107" name="estado_107" style="width:100%" value="<?= $list_query4["estado"] ?>">
                                    </div>
                                </div>
                                <button class="btn btn-success" id="btn_dni_2_factores_save_107">
                                    <span class="glyphicon glyphicon-save"></span> Guardar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
		</div>
	</div>
	<div class="col-md-2 hidden-xs hidden-sm">
		<ul class="nav nav-tabs tabs-right local_tabs">
				<li id="li_local_table" class="active"><a id="tab_local_table" class="tab_btn" href="#tab_local_garantia" data-tab="tab_local_garantia">Locales</a></li>
				<li id="li_solicitud_table" class=""><a id="tab_solicitudes_table" class="tab_btn" href="#tab_solicitudes_garantia" data-tab="tab_solicitudes_garantia">Configuraciones</a></li>
		</ul>
	</div>
</div>




<!-- MODAL AGREGAR DNI 2 FACTORES -->
<div class="modal fade" id="agregar_dni_2_factores" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>      
				<h5 class="modal-title" id="mdCrearGrupoTitle">Agregar DNI 2 Factores</h5>
			</div>
			<div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
                    </div>
                </div>
				<div class="row">
					<div class="col-sm-12" >
						<h5 id="txtGrupoTitle"></h5>
						<form method="POST" class="form-horizontal">
                            <br>
							<div class="form-group">
								<label  class="col-xs-5 control-label" for="dni">DNI</label>
                                <div class="col-xs-7">
                                    <input type="number" id="dni" name="dni" style="width:100%">
								</div>
							</div>
                            <br>	
							<div class="form-group">
                                <label class="col-xs-5 control-label" for="locales_redes">Locales Redes</label>
                                <div class="col-xs-7">
                                    <select id="locales_redes" name="locales_redes" class="form-control" style="width:100%">
                                        <option value="0">--Seleccione--</option>
                                        <?php foreach ($locales_redes as $key => $value) { ?>
                                            <option data-nombre= "<?php echo $value["id"];?>" value="<?php echo $value["id"];?>"><?php echo $value["nombre"]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-group equipo">
								<label class="col-xs-5 control-label" for="estado">Estado</label>
								<div class="col-xs-7">
									<select id="estado" name="estado" class="form-control" style="width:100%">
                                        <option value="0">--Seleccione--</option>
										<option value = "1">Activo</option>
                                        <option value = "0">Inactivo</option>
									</select>
								</div>
							</div>
							<br>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-success open_btn" title="Abrir" id="sec_dni_2_factores_guardar_btn"><span class='glyphicon glyphicon-floppy-save'></span> Guardar</button>
					<button class="btn btn-default close_btn" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_detalle" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
        <div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>      
				<h5 class="modal-title" id="mdCrearGrupoTitle">Agregar DNI 2 Factores</h5>
			</div>
			<div class="modal-body">
                <div class="row">
                    <div class="col-xs-12">
                        <div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
                    </div>
                </div>
				<div class="row">
					<div class="col-sm-12" >
						<h5 id="txtGrupoTitle"></h5>
						<form method="POST" class="form-horizontal">
                            <input type="hidden"  id="id" name="id">
                            <br>
							<div class="form-group">
								<label  class="col-xs-5 control-label" for="dni">DNI</label>
                                <div class="col-xs-7">
                                    <input type="number" id="dni" name="dni" style="width:100%">
								</div>
							</div>
                            <br>	
							<div class="form-group">
                                <label class="col-xs-5 control-label" for="locales_redes">Locales Redes</label>
                                <div class="col-xs-7">
                                    <select id="locales_redes" name="locales_redes" class="form-control" style="width:100%">
                                        <option value="0">--Seleccione--</option>
                                        <?php foreach ($locales_redes as $key => $value) { ?>
                                            <option data-nombre= "<?php echo $value["id"];?>" value="<?php echo $value["id"];?>"><?php echo $value["nombre"]?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <div class="form-group equipo">
								<label class="col-xs-5 control-label" for="estado">Estado</label>
								<div class="col-xs-7">
									<select id="estado" name="estado" class="form-control" style="width:100%">
                                        <option value="">--Seleccione--</option>
										<option value = "1">Activo</option>
                                        <option value = "0">Inactivo</option>
									</select>
								</div>
							</div>
							<br>
						</form>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-success open_btn" title="Abrir" id="sec_dni_2_factores_update_btn"><span class='glyphicon glyphicon-floppy-save'></span> Guardar</button>
					<button class="btn btn-default close_btn" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>