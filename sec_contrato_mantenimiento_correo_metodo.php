<div class="panel">
    <div class="panel-heading text-center">
        <div class="panel-title">Métodos de Correo</div>
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
            <legend class="dhhBorder">Registro</legend>
            <form method="POST" id="Frm_RegistroCorreoMetodo" enctype="multipart/form-data" autocomplete="off">
                <input id="cont_mant_corr_met_id" type="hidden" class="form-control">

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tipo Método</label>
                            <select id="cont_mant_corr_met_tipo_correo_metodo_id" name="cont_mant_corr_met_tipo_correo_metodo_id" class="form-control select2"></select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input id="cont_mant_corr_met_nombre" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Método</label>
                            <input id="cont_mant_corr_met_metodo" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>Estado</label>
                            <select id="cont_mant_corr_met_status" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label></label>
                            <button type="submit" class="btn form-control btn-info" id="btn-form-registro-correo-metodo-registrar">Registrar</button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label></label>
                            <button type="button" onclick="sec_contrato_mant_met_frm_reset()" class="btn form-control btn-danger">Cancelar</button>
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
            <div class="table-responsive">
                <table class="table display responsive" style="width:100%"" id="tbl_correo_metodo">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">Nombre</th>
                            <th class="text-center">Método</th>
                            <th class="text-center">Estado</th>
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