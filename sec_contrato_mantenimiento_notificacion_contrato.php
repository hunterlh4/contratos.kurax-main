<div class="panel">
    <div class="panel-heading text-center">
        <div class="panel-title">Notificaciones de Contratos</div>
    </div>
    <div class="panel-body">
        <?php
        global $mysqli;
        $menu_id = "";
        $result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'mantenimiento' LIMIT 1");
        while ($r = $result->fetch_assoc())
            $menu_id = $r["id"];

        if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("nuevo_metodo_correo", $usuario_permisos[$menu_id])) {
            //nuevo_director
        } else {
        ?>
        <fieldset class="dhhBorder">
            <legend class="dhhBorder">Registro de Notificación de Contrato</legend>
            <form method="POST" id="Frm_RegistroNotificacionContrato" enctype="multipart/form-data" autocomplete="off">
                <input id="cont_mant_corr_met_id" type="hidden" class="form-control">

                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label>Ingrese usuario</label>
                            <select id="modal_mant_not_cont_usuario_id" name="modal_mant_not_cont_usuario_id" class="form-control select2"></select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>Ingrese área</label>
                            <select id="modal_mant_not_cont_area_id" name="modal_mant_not_cont_area_id" class="form-control select2"></select>
                        </div>
                    </div>
                    <div class="col-md-4 btn-form-registro-correo-metodo-registrar">
                        <div class="form-group">
                            <label></label>
                            <button type="submit" class="btn form-control btn-info" id="btn-form-registro-correo-metodo-registrar">Registrar</button>
                        </div>
                    </div>
                    <div class="col-md-2 btn-form-correo-metodo-modificar" style="display:none;">
                        <div class="form-group">
                            <label></label>
                            <button type="button" class="btn form-control btn-warning" id="btn-form-correo-metodo-modificar">Modificar</button>
                        </div>
                    </div>
                    <div class="col-md-2 btn-form-correo-metodo-cancelar" style="display:none;">
                        <div class="form-group">
                            <label></label>
                            <button type="button" class="btn form-control btn-danger" id="btn-form-correo-metodo-cancelar">Cancelar</button>
                        </div>
                    </div>
            
                    
                </div>
            </form>
        </fieldset>
        <?php
        }
        $permiso_edicion = 0;
        if (!(!array_key_exists($menu_id, $usuario_permisos) || !in_array("edicion_metodo_correo", $usuario_permisos[$menu_id]))) {
            $permiso_edicion = 1;
        } 
        ?>
        <hr>
        <input type="hidden" id="edicion_metodo_correo" value="<?=$permiso_edicion?>">

        <div class="col-md-12">
            <div class="table-responsive" id="notificacion_contrato_div_tabla">
                <table class="table display responsive" style="width:100%" id="notificacion_contrato_datatable">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Usuario</th>
                            <th class="text-center">Área</th>
                            <th class="text-center">Registrado Por</th>
                            <th class="text-center">Fecha de Registro</th>
                            <th class="text-center">Modificado Por</th>
                            <th class="text-center">Fecha de Modificación</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        
    </div>
</div>


<!-- INICIO MODAL HISTORICO CAMBIOS -->
<div id="modalNotificacionHistoricoContrato" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Historial de cambios de Notificación de Contratos</h4>
            </div>
            <div class="modal-body">

                <div class="col-md-12">
                    <div class="table-responsive" id="notificacion_contrato_historial_div_tabla">
                        <table class="table display responsive" style="width:100%" id="notificacion_contrato_historial_datatable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Valor Nuevo</th>
                                    <th class="text-center">Valor Anterior</th>
                                    <th class="text-center">Usuario</th>
                                    <th class="text-center">Fecha</th>
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
<!-- FIN MODAL HISTORICO CAMBIOS -->