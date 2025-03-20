
<!-- INICIO MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->
<?php
                global $mysqli;
                $menu_id = "";
                $result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'mantenimiento' LIMIT 1");
                while ($r = $result->fetch_assoc())
                    $menu_id = $r["id"];

                if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("Nuevo_Cargo", $usuario_permisos[$menu_id])) {
                    ?>
                    <input id="modal_mant_cargo_id" value="-1" type="hidden" class="form-control">
                    <input id="modal_mant_cargo_metodo_id_sin_permiso" value="-1" type="hidden" class="form-control">
                    
                <?php } else {
                ?>
<div id="modalMantemientoCargo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_cargo">Cargos</h4>
			</div>
			<div class="modal-body">
                
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Registro Cargo</legend>
                    <form method="POST" id="Frm_RegistroCargo" enctype="multipart/form-data" autocomplete="off">
                        <input id="modal_mant_cargo_id" type="hidden" class="form-control">
                        <input id="modal_mant_cargo_metodo_id" type="hidden" class="form-control">

                        <div class="row">                       
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Cargo</label>
                                    <select id="modal_mant_cargo_cargo_id" name="modal_mant_corr_usuario_id" class="form-control select2"></select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label></label>
                                    <button type="submit" class="btn form-control btn-info" id="btn-form-registro-correo-metodo-registrar">Registrar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </fieldset>
              
                <hr>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table display responsive" cellspacing="0" width="100%"" id="tbl_modal_cargo">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Cargos</th>
                                    <th class="text-center">Registrado por</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Estado</th>
                                    <!-- <th class="text-center">Acciones</th> -->
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
			</div>
			<div class="modal-footer">
				
			</div>
		</div>
	</div>
</div>
<?php
                }
?>
<!-- FIN MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->




<!-- INICIO MODAL AGREGAR HISTORIAL -->
<div id="modalMantemientoCargo_historial" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_cargo_historial">Cargos</h4>
			</div>
			<div class="modal-body">
                
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Historial Cargo</legend>
                    <form method="POST" id="" enctype="multipart/form-data" autocomplete="off">
                        <input id="modal_mant_cargo_id" type="hidden" class="form-control">
                        <input id="modal_mant_cargo_metodo_id_historial" type="hidden" class="form-control">
                    </form>
                </fieldset>
                
                <hr>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table display responsive" cellspacing="0" width="100%" id="tbl_modal_cargo_historial">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Cargos</th>
                                    <th class="text-center">Registrado por</th>
                                    <th class="text-center">Fecha modificación</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
			</div>
			<div class="modal-footer">
				
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL HISTORIAL -->



<!-- INICIO MODAL LSITAR PERSONAL ACTIVO POR AREA Y CARGO -->
<div id="modalMantemientoCargo_personal" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_cargo_personal">Cargos</h4>
			</div>
			<div class="modal-body">
                    <input type="hidden" id="modal_personal_cargo_area_id">
               

                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#PersonalPorAreaCargo" aria-controls="PersonalPorAreaCargo" role="tab" data-toggle="tab">Personal Por Area y Cargo</a></li>
                        <li role="presentation"><a href="#CorreoPorArea" aria-controls="CorreoPorArea" role="tab" data-toggle="tab">Correos Por Área</a></li>
                    </ul>

                    <!-- Tab panes -->
                    <div class="tab-content">
                        <div role="tabpanel" class="tab-pane active" id="PersonalPorAreaCargo">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-condensed" cellspacing="0" width="100%" id="tbl_modal_cargo_personal">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Personal</th>
                                            <th class="text-center">Correo</th>
                                            <th class="text-center">Cargo</th>
                                            <th class="text-center">Fecha de Ingreso</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div role="tabpanel" class="tab-pane" id="CorreoPorArea">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label>Personal</label>
                                    <select name="modal_usuario_correo_area_id" id="modal_usuario_correo_area_id" class="form-control select2">
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for=""><br></label>
                                    <button type="button" class="form-control btn btn-primary" onclick="sec_contrato_mant_met_modal_registrar_correo_area()">Registrar</button>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <br>
                            </div>

                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-condensed" cellspacing="0" width="100%" id="tbl_modal_correo_area">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Personal</th>
                                                <th class="text-center">Correo</th>
                                                <th class="text-center">Cargo</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acc.</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                           
                        </div>
                    </div>

              

            
                    
                
			</div>
			<div class="modal-footer">
				
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL LSITAR PERSONAL ACTIVO POR AREA Y CARGO -->
