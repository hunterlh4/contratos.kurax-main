<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_asignacion_caja_chica = $menu_id_consultar["id"];


?>

<style>
    textarea {
      resize: none;
    }

    .sec_caja_chica_form_asignacion_campo_obligatorio{
        font-size: 15px;
        color: red;
    }

    .sec_caja_chica_texto_campo_obligatorio{
        font-size: 13px;
        color: red;
    }
    
</style>

<div class="content container-fluid vista_sec_caja_chica">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Asignación - Caja Chica</h1>
            </div>
        </div>
    </div>

    <div class="col-md-12" style="margin-bottom: 10px;">
        <a class="btn btn-primary btn-sm" id="btnRegresar" href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=mesa_partes">
            <i class="glyphicon glyphicon-arrow-left"></i>
            Regresar
        </a>
    </div>
    
    <?php

    if(array_key_exists($menu_asignacion_caja_chica,$usuario_permisos) && in_array("AsignacionCajaChica", $usuario_permisos[$menu_asignacion_caja_chica]))
    {
        ?>

        <div class="col-xs-12 col-md-12 col-lg-12 mt-3" id="sec_form_nueva_asignacion">
            <div class="panel">
                <div class="panel-heading">
                    <div class="panel-title">Nueva Asignación <span class="sec_caja_chica_texto_campo_obligatorio" style="text-transform: none;">(*) Campos obligatorios</span></div>
                </div>
                <div class="panel-body wide">
                    <form id="sec_mepa_caja_chica_formulario_nueva_asignacion" method="POST" enctype="multipart/form-data">

                        <div class="alert alert-info" role="alert">
                            <h4 class="alert-heading">
                                ¡IMPORTANTE!
                            </h4>
                            <p style="font-size: 15px;"> La información ingresada en este formulario será estrictamente responsabilidad del Usuario quien registra, no se permite confusiones al ingresar el Número de Cuenta de su Banco.</p>
                        </div>

                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12" id="">
                            <label for="sec_mepa_caja_chica_txt_motivo">Motivo: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>
                            <textarea type="text" id="sec_mepa_caja_chica_txt_motivo" class="form-control" autocomplete="off" value="" placeholder="Ingrese el motivo" cols="4"></textarea>
                        </div>

                        <div class="form-group col-xs-12 col-sm-12 col-md-2 col-lg-3" id="">
                            <label for="sec_mepa_caja_chica_txt_banco">Banco: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>
                            <select
                                class="form-control input_text sec_mepa_caja_chica_select_filtro"
                                data-live-search="true" 
                                id="sec_mepa_caja_chica_txt_banco" 
                                title="">
                                <option value="0">-- Seleccione --</option>
                                <?php
                                $sel_query = $mysqli->query(
                                    "
                                        SELECT 
                                            id, nombre, estado 
                                        FROM tbl_bancos
                                        WHERE estado = 1
                                    ");

                                while($sel=$sel_query->fetch_assoc())
                                {
                                    
                                    ?>
                                        <option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3" id="">
                            <label for="sec_mepa_caja_chica_txt_numero_cuenta">
                                Número de Cuenta (<span id="sec_mepa_caja_chica_txt_num_digitos_cuenta">0</span> Dígitos): 
                                <span class="sec_caja_chica_form_asignacion_campo_obligatorio">
                                    (*)
                                </span>
                            </label>
                            <input type="text" id="sec_mepa_caja_chica_txt_numero_cuenta" class="form-control sec_mepa_caja_chica_txt_numero_cuenta" autocomplete="off" style="width: 100%; height: 30px;" value="" placeholder="Ingrese el numero de Cuenta">
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-3 col-lg-2" id="">
                            <label for="sec_mepa_caja_chica_txt_fondo_asignado">Fondo Asignado: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>
                            <input type="text" id="sec_mepa_caja_chica_txt_fondo_asignado" class="form-control sec_mepa_caja_chica_txt_fondo_asignado" autocomplete="off" style="width: 100%; height: 30px;" value="" placeholder="Ingrese el monto">
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-3 col-lg-4" id="">
                            <label for="sec_mepa_caja_chica_txt_zona">Zona - Centro de Costo: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>
                            <select
                                class="form-control input_text sec_mepa_caja_chica_select_filtro"
                                data-live-search="true" 
                                id="sec_mepa_caja_chica_txt_zona" 
                                title="">
                                <option value="0">-- Seleccione --</option>
                                <?php
                                $sel_query = $mysqli->query(
                                    "
                                        SELECT 
                                            id, centro_costo, nombre, status 
                                        FROM mepa_zona_asignacion
                                        WHERE status = 1 AND NOT id = 42
                                    ");

                                while($sel=$sel_query->fetch_assoc())
                                {
                                    
                                    ?>
                                        <option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-12 col-lg-12">
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3" id="">
                            <label for="sec_mepa_caja_chica_txt_buscar_dni">Buscar DNI: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>                         
                            <div class="input-group">
                                <input type="text" id="sec_mepa_caja_chica_txt_buscar_dni" name="sec_mepa_caja_chica_txt_buscar_dni" class="form-control sec_mepa_caja_chica_txt_buscar_dni" placeholder="Buscar" style="height:34px !important" maxlength="8" autocomplete="off">
                                <span class="input-group-btn">
                                    <button type="button" id="sec_mepa_caja_chica_buscar_dni" class="btn btn-primary btn" style="padding-left: 10%; padding-right: 10px;"><i class="fa fa-search"></i> Buscar</button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3" id="">
                            <label for="sec_mepa_caja_chica_txt_fondo_asignado">
                                Usuario Asignado: 
                                <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span>
                            </label>
                            <input type="hidden" id="sec_mepa_caja_chica_txt_usuario_asignado_id" class="form-control sec_mepa_caja_chica_txt_usuario_asignado_id" autocomplete="off" style="width: 100%; height: 30px;" value="" placeholder="ID" disabled>
                            <input type="text" id="sec_mepa_caja_chica_txt_usuario_asignado_nombre" class="form-control sec_mepa_caja_chica_txt_usuario_asignado_nombre" autocomplete="off" style="width: 100%; height: 30px;" value="" placeholder="Usuario" disabled>
                        </div>

                        <div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-4" id="">
                            <label for="sec_mepa_caja_chica_txt_zona">Empresa: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>
                            <select
                                class="form-control input_text sec_mepa_caja_chica_select_filtro"
                                data-live-search="true" 
                                id="sec_mepa_caja_chica_txt_empresa" 
                                title="">
                                <option value="0">-- Seleccione --</option>
                            </select>
                        </div>

                        <div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12" id="mepa_asignacion_div_reportar_usuarios" style="display: none;">
                            <input type="hidden" id="mepa_asignacion_se_reportara_al_usuario" value="0">
                            <div class="form-group col-xs-12 col-sm-4 col-md-4 col-lg-4">
                                
                                <div style="margin-top: 20px;">
                                    
                                    <label for="sec_mepa_caja_chica_txt_reportar_usuario">Reportar a: <span class="sec_caja_chica_form_asignacion_campo_obligatorio">(*)</span></label>
                                    <select
                                        class="form-control input_text sec_mepa_caja_chica_select_filtro"
                                        data-live-search="true" 
                                        id="sec_mepa_caja_chica_txt_reportar_usuario" 
                                        title="">
                                        <option value="0">-- Seleccione --</option>
                                        <?php
                                        $sel_query = $mysqli->query(
                                            "
                                                SELECT 
                                                    u.id,
                                                    concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS nombre_personal
                                                FROM tbl_personal_apt p
                                                    INNER JOIN tbl_usuarios u
                                                    ON p.id = u.personal_id
                                                WHERE u.estado = 1 AND p.estado = 1 AND p.area_id = 16
                                                    AND p.cargo_id = 26
                                            ");

                                        while($sel=$sel_query->fetch_assoc())
                                        {
                                            
                                            ?>
                                                <option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre_personal"];?></option>
                                            <?php
                                        }
                                        ?>
                                    </select>

                                    <button type="button" class="btn btn-warning btn-xs" id="" onclick="mepa_asignacion_agregar_usuarios_a_reportar();">
                                        <span class="glyphicon glyphicon-plus"></span>
                                        <span id="demo-button-text">Agregar</span>
                                    </button>
                                </div>

                                <div style="margin-top: 12px; width: 100%;">
                                    <table id="mepa_asignacion_reportar_usuarios_detalle_table" class="mepa_asignacion_reportar_usuarios_detalle_table table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="70%">
                                        <thead style="background-color: #A9D0F5;">
                                            <tr>
                                                <th class="text-center" colspan="2">Detalle de usuarios a reportar</th>
                                            </tr>
                                        </thead>            
                                        <tbody>
                                            
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 20px;">
                            <button type="button" class="btn btn-success btn-block" id="btn_guardar_solicitud_asignacion_caja_chica">
                                <i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
                                <span id="demo-button-text">Solicitar Asignación</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <?php
    }
    else
    {
        include("403.php");
        return false;
    }

    ?>
</div>



