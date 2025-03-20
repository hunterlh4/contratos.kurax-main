<style>
    .campo_obligatorio_v2 {
        font-size: 13px;
        color: red;
    }
</style>
<div class="content ">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> Incidencia</div>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-12">
            </div>
        </div>
    </div>
    <?php
    $locales_arr = array();
    $locales_command = "SELECT l.id, l.nombre, l.phone FROM tbl_locales l";
    if ($login["usuario_locales"]) {
        $locales_command .= " WHERE l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
    }
    $locales_command .= " ORDER BY l.nombre ASC";
    $locales_query = $mysqli->query($locales_command);
    if ($mysqli->error) {
        print_r($mysqli->error);
        echo "\n";
        echo $locales_command;
        exit();
    }
    while ($l = $locales_query->fetch_assoc()) {
        $locales_arr[$l["id"]]["nombre"] = '[' . $l["id"] . '] ' . $l["nombre"];
        $locales_arr[$l["id"]]["telefono"] = $l["phone"];
    }

    ?>
    <div class="col-xs-12">
        <div class="panel">
            <div class="panel-heading">
                <div class="panel-title incidencia_title">¿Tienes problemas con nuestra línea?.Solicita atención
                    inmediata aquí
                </div>
            </div>
            <div id="panel-datos_2" class="panel-collapse collapse in" role="tabpanel"
                 aria-labelledby="panel-collapse-1-heading">
                <div class="panel-body" style="padding:0px !important">
                    <?php if ($login["area_id"] == 21 && in_array($login["cargo_id"], [4, 5])): //CAJERO ?>
                        <select class="select2 save_data" name="local_id" id="local_id"
                                style="display: none;width:100%">
                            <option value="<?= $login["local_id"] ?>"></option>
                        </select>
                    <?php else: ?>
                        <div class="col-xs-12">
                            <div class="form-group">
                                <select class="select2 save_data" name="local_id" id="local_id" style="width:100%">
                                    <?php foreach ($locales_arr as $id => $value) { ?>
                                        <option value="<?php echo $id; ?>"
                                                data-phone="<?= $value["telefono"] ?>"><?php echo $value["nombre"]; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-xs-12 div_espacio"></div>
                    <div class="col-xs-12">
                        <div class="form-group">
                            <label class="" for="local_phone">Teléfono de tienda</label>
                            <div class="">
                                <?php if ($login["area_id"] == 21 && in_array($login["cargo_id"], [4, 5])): //CAJERO ?>
                                    <input id="local_phone" class="form-control" type="text"
                                           value="<?= isset($locales_arr[$login["local_id"]]["telefono"]) ? $locales_arr[$login["local_id"]]["telefono"] : ""; ?>"
                                           readonly>
                                <?php else: ?>
                                    <input id="local_phone" class="form-control" type="text"
                                           value="<?= reset($locales_arr)["telefono"] ?>" readonly>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <p class="col-xs-12 pt-2">Si el número no es correcto o el campo esta en blanco, reportarlo con su
                        supervisor.</p>

                    <div class="botones_inc col-xs-12">
                        <label for="selectTipo" class="col-xs-2 col-md-1" style="padding-left:0px">Producto:</label>
                        <div class="col-xs-10">
                            <div class="opciones_inc mb-1">
                                <label for="selectProducto0"><span>Apuestas Deportivas</span>
                                    <input id="selectProducto0" class="selectTipo save_data" type="radio"
                                           name="selectProducto" value="Apuestas Deportivas">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectProducto1"><span>Juegos Virtuales/Lotería</span>
                                    <input id="selectProducto1" class="selectTipo save_data" type="radio"
                                           name="selectProducto" value="Juegos Virtuales">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectProducto2"><span>Otros</span>
                                    <input id="selectProducto2" class="selectTipo save_data" type="radio"
                                           name="selectProducto" value="Otros">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="botones_inc div_tipos col-xs-12" data-producto="Apuestas Deportivas">
                        <label for="selectTipo" class="col-xs-2 col-md-1" style="padding-left:0px">Tipo:</label>
                        <div class="col-xs-10">
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo0"><span>BetShop</span>
                                    <input id="selectTipo0" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="BetShop">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo1"><span>Betting terminal</span>
                                    <input id="selectTipo1" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Betting terminal">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <!--
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo2"><span>Simulcast</span>
                                    <input id="selectTipo2" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Simulcast">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            -->
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo3"><span>Web Cliente-Código de Reserva</span>
                                    <input id="selectTipo3" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Web Cliente">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo4"><span>Aterax MVR</span>
                                    <input id="selectTipo4" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Aterax MVR">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo5"><span>Aterax Terminal</span>
                                    <input id="selectTipo5" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Aterax Terminal">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="botones_inc  div_tipos col-xs-12" data-producto="Juegos Virtuales">
                        <label for="selectTipo" class="col-xs-2 col-md-1" style="padding-left:0px">Tipo:</label>
                        <div class="col-xs-10">
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo6"><span>Golden Race</span>
                                    <input id="selectTipo6" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Golden Race">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo7"><span>Pre tickets</span>
                                    <input id="selectTipo7" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Pre tickets">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo8"><span>Bingo</span>
                                    <input id="selectTipo8" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Bingo">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo9"><span>Torito</span>
                                    <input id="selectTipo9" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Torito">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo10"><span>Simulcast</span>
                                    <input id="selectTipo10" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Simulcast">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo11"><span>Nsoft (Lucky six)</span>
                                    <input id="selectTipo11" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Nsoft (Lucky six)">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="botones_inc  div_tipos col-xs-12" data-producto="Otros">
                        <label for="selectTipo" class="col-xs-2 col-md-1" style="padding-left:0px">Tipo:</label>
                        <div class="col-xs-10">
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo12"><span>CCTV</span>
                                    <input id="selectTipo12" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="CCTV">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo13"><span>Disashop</span>
                                    <input id="selectTipo13" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Disashop">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo14"><span>GeoVictoria</span>
                                    <input id="selectTipo14" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="GeoVictoria">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo15"><span>Gestión</span>
                                    <input id="selectTipo15" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Gestión">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo16"><span>Snack</span>
                                    <input id="selectTipo16" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Snack">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo17"><span>Web (Retiro/Depósito)</span>
                                    <input id="selectTipo17" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Web (Retiro/Depósito)">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label for="selectTipo18"><span>Sorteo</span>
                                    <input id="selectTipo18" class="selectTipo save_data" type="radio" name="selectTipo"
                                           value="Sorteo">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="botones_inc col-xs-12">
                        <label for="reimpresion" class="col-xs-2 col-md-1" style="padding-left:0px">Reimpresión:</label>
                        <div class="col-xs-10">
                            <div class="opciones_inc mb-1">
                                <label><span>Si</span>
                                    <input class="reimpresion save_data" type="radio" name="reimpresion" value="1">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                            <div class="opciones_inc mb-1">
                                <label><span>No</span>
                                    <input class="reimpresion save_data" type="radio" name="reimpresion" value="0">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-xs-12 div_espacio"></div>
                    <div class="col-xs-12">
                        <div style="color:red">Por favor no cerrar el aplicativo TEAMVIEWER para que la contraseña no
                            cambie hasta recibir la atención.
                        </div>
                    </div>

                    <div class="col-xs-12 div_espacio"></div>
                    <div class="row">
                        <div class="form-group">
                            <label class="col-xs-12 col-md-1" for="teamviewer_id">ID de Teamviewer</label>
                            <div class="col-xs-12 col-md-3">
                                <input id="teamviewer_id" name="teamviewer_id" type="text"
                                       class="form-control save_data" maxlength=15>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-xs-12 col-md-2" for="teamviewer_password">Contraseña Teamviewer</label>
                            <div class="col-xs-12 col-md-3">
                                <input id="teamviewer_password" name="teamviewer_password" type="text"
                                       class="form-control save_data" maxlength=12>
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12 div_espacio"></div>
                    <div class="row">
                        <div class="form-group">
                            <label class="col-xs-12 col-md-2" for="telefono2"><b>Déjanos tu teléfono para llamarte <span
                                            class="campo_obligatorio_v2">(*)</span>:</b></label>
                            <div class="col-xs-12 col-md-3">
                                <input id="telefono2" name="telefono2" type="text" maxlength="9"
                                       class="form-control save_data">
                            </div>
                        </div>
                    </div>

                    <div class="col-xs-12 div_espacio"></div>
                    <div class="col-xs-12">
                        <label class="control-label">Detállanos el incidente(160 caracteres) <span
                                    class="campo_obligatorio_v2">(*)</span>:</label>
                        <textarea name="incidencia_txt" class="form-control save_data" autofocus></textarea>
                    </div>
                    <div class="col-xs-12 div_espacio"></div>
                    <div class="col-xs-12 div_espacio"></div>

                    <?php if (array_key_exists(148, $usuario_permisos) && in_array("save", $usuario_permisos[148])) { ?>
                    <div class="col-xs-8">
                        <div class="form-group">
                            <button type="button" data-then="exit" class="save_btn btn btn-danger" data-button="save">
                                <i class="glyphicon glyphicon-floppy-save"></i>
                                Enviar
                            </button>

                        </div>
                        <span class="campo_obligatorio_v2">(*) Campos obligatorios</span></div>
                </div>
                <?php } ?>
                <div class="col-xs-12 div_espacio"></div>
                <div class="col-xs-12 div_espacio"></div>
                <div class="col-xs-12">Para solicitud de documentos o reporte de faltantes por errores de sistema
                    comunicarse a <u>soporte@testtest.apuestatotal.com</u></div>
                <div class="col-xs-12 div_espacio"></div>
                <div class="col-xs-12"><strong>Recomendaciones:</strong></div>
                <div class="col-xs-12">Para reimpresiones de JV – Comunicar hora y Fecha en la solicitud</div>
                <div class="col-xs-12">Para reimpresiones de AD - Enviar el evento, monto y hora aproximada del ticket
                </div>
                <div class="col-xs-12">Para evaluaciones de tickets – Enviar el código de ticket en la incidencia</div>
                <div class="col-xs-12">Enviar todos los detalles dentro de la misma incidencia, debido a que todos los
                    atendidos son archivados. (No es un chat)
                </div>
                <div class="col-xs-12">Revisa nuestro muro de IMPORTANTE para enterarte de nuestros problemas masivos
                </div>
            </div>
        </div>
    </div>
</div>
<div class="col-xs-12">
    <div class="row imp">
        <div class="col-xs-6">
            IMPORTANTE
        </div>
        <div class="col-xs-6">
            <?php if (array_key_exists(148, $usuario_permisos) && in_array("guardar_nota", $usuario_permisos[148])) { ?>
                <button class='btn btn-sm text-danger btn-default pull-right' id="btn_agregar_nota">+ Agregar Nota
                </button>
            <?php } ?>
        </div>
    </div>
</div>
<?php
$area_id = isset($login["area_id"]) ? $login["area_id"] : "";
$cargo_id = isset($login["cargo_id"]) ? $login["cargo_id"] : "";
$where = ($area_id == 21 && $cargo_id == 5) ? " where notas.estado=1" : " ";

$list = array();
$list_query = $mysqli->query("
					SELECT notas.id,
					notas.nota_txt,
					notas.estado
					,notas.created_at
					,notas.imagen
					FROM tbl_soporte_notas notas
		            left join tbl_usuarios usu on usu.id= notas.user_id
					left join tbl_personal_apt usu_age_pers on usu_age_pers.id= usu.personal_id
					$where
					order by notas.created_at DESC
	            ");
while ($li = $list_query->fetch_assoc()) {
    $list[] = $li;
}
if ($mysqli->error) {
    print_r($mysqli->error);
}
?>
<div class="col-xs-12">
    <div class="col-xs-12" id="incidencias_ca_notas">
        <div class="contenedor notas_tbl">
            <?php foreach ($list as $id => $valor) { ?>
                <!--CHECK IF ITS A CASHIER AND IF SO, IF INCIDENCE HAS STATE == 1-->
                <?php if (($login["area_id"] == 21 && in_array($login["cargo_id"], [4, 5]) && $valor["estado"]) || ($login["area_id"] != 21 && !in_array($login["cargo_id"], [4, 5]))
                    || ($login["area_id"] == 21 && $login["cargo_id"] == 16) /*operaciones-jefe*/
                ): ?>
                    <div class="nota_fila">
                        <div class="nota_td"><?php
                            $english = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
                            $Meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre');
                            //setlocale(LC_TIME,"es_PE.utf8");
                            $fecha = ucwords(strftime("%d - %B del %Y %H:%M %P", strtotime($valor["created_at"])));
                            echo str_replace($english, $Meses, $fecha);
                            ?></div>
                        <div class="nota_texto nota_texto_td"><?php echo $valor["nota_txt"]; ?></div>
                        <div class=" div_botones botones_td">
                            <?php if (array_key_exists(148, $usuario_permisos) && in_array("state_nota", $usuario_permisos[148])) { ?>
                                <input
                                        class="switch"
                                        type="checkbox"
                                        id="checkbox_<?php echo $valor['id']; ?>"

                                    <?php if ($valor["estado"]): ?>
                                        checked="checked"
                                    <?php endif; ?>
                                        data-table="tbl_soporte_notas"
                                        data-id="<?php echo $valor['id']; ?>"
                                        data-col="estado"
                                        data-on-value="1"
                                        data-off-value="0"
                                        data-ignore='true'>
                            <?php } ?>

                            <?php if (array_key_exists(148, $usuario_permisos) && in_array("edit_nota", $usuario_permisos[148])) { ?>
                                <button class="btn btn-rounded btn-default btn-sm btn-edit editar_notas"
                                        data-id="<?php echo $valor['id']; ?>"
                                        data-imagen="<?php echo $valor['imagen'] ?>"><i
                                            class="glyphicon glyphicon-edit"></i>
                                </button>
                            <?php } ?>
                            <?php if ($valor["imagen"] != "") {
                                ?>
                                <button class="btn btn-rounded btn-primary btn-sm nota_fila_imagen"
                                        data-src="<?php echo $valor["imagen"]; ?>">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <?php
                            }
                            ?>
                        </div>

                    </div>
                <?php endif ?>
            <?php } ?>
        </div>
    </div>
</div>
<?php
$user_id = $login["id"];
$list = array();
$list_query = $mysqli->query("
				SELECT inci.id as Id
					,inci.created_at as 'Fecha y Hora'
					,usu.usuario as Usuario
					,loc.nombre as Tienda
					,loc.phone as Teléfono
					,inci.incidencia_txt as Incidencia
					,CASE 
						WHEN inci.estado=0 THEN 'Nuevo' 
			            WHEN inci.estado=2 then 'Asignado'
			            ELSE 'Atendido'       
			            END AS Estado
					,ag.usuario as Agente
					,inci.solucion_txt as Observación
				FROM tbl_soporte_incidencias inci 
	            left join tbl_locales loc on  inci.local_id=loc.id
	            left join tbl_usuarios usu on usu.id= inci.user_id
	            left join tbl_usuarios ag on ag.id= inci.update_user_id
				left join tbl_personal_apt usu_age_pers on usu_age_pers.id= ag.personal_id
				where  usu.id= $user_id 
				order by  inci.id desc 
				 limit 5 
            ");
while ($li = $list_query->fetch_assoc()) {
    $list[] = $li;
}
if ($mysqli->error) {
    print_r($mysqli->error);
}
?>

<div class="col-xs-12 div_espacio"></div>
<div class="col-xs-12 div_espacio"></div>
<div class="col-xs-12 row">
    <div class="row imp">
        <div class="col-xs-12 imp">HISTORIAL</div>
    </div>
    <div class="col-xs-6">
        <strong> Tus últimos casos</strong>
    </div>
</div>

<!-- RED COMBO, deprecated-->
<?php

$query = "SELECT id, nombre FROM tbl_locales_redes";
$result = $mysqli->query($query);
$redes = [];
while ($row = $result->fetch_assoc()) {
    $redes[] = $row;
}
?>
<div class="col-xs-12 div_espacio"></div>
<div class="row m-5" style="display: none;">
    <div class="col-xs-3 form-inline">
        <label for="incidencias_redes_ca">Red:</label>
        <select name="incidencias_redes" id="incidencias_redes_ca" class="form-control">
            <option value="-1">Todos</option>
            <?php foreach ($redes as $red) : ?>
                <option value="<?= $red["id"] ?>"><?= $red["nombre"] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
<!-- RED COMBO, end-->

<?php if (array_key_exists(148, $usuario_permisos) && in_array("xls", $usuario_permisos[148])) { ?>
    <div class="col-xs-12 row mb-3 pull-right">
        <button class="btn btn-success" id="incidencias_ca_excel">Descargar Excel</button>
    </div>
<?php } ?>

<div class="col-xs-12" style="">
    <div style="overflow : auto">
        <table
                id="tbl_incidencias_historial"
                class="table table-striped table-hover table-condensed table-bordered "
                cellspacing="0"
                width="100%" style="width:100%">
            <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Usuario</th>
                <th>Tienda</th>
                <th>Teléfono</th>
                <th>Incidencia</th>
                <th>Estado</th>
                <th>Agente</th>
                <th>Observación</th>
                <th>Satisfacción</th>
            </tr>
            </thead>
            <tbody id="tbl_incidencias_historial_body">
            </tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="modal_satisfaccion" role="dialog" aria-labelledby="" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="mdCrearGrupoTitle">Notas</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" id="incidencia_id" name="incidencia_id">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                     viewBox="0 0 2911 1582">
                    <image width="2911" height="1582" xlink:href="/images/incidencias_satisfaccion.jpeg"></image>
                    <a class="satisfaccion_choice" xlink:href="#" data-value="0">
                        <rect x="340" y="438" fill="#fff" opacity="0" width="447" height="824"></rect>
                    </a>
                    <a class="satisfaccion_choice" xlink:href="#" data-value="1">
                        <rect x="787" y="438" fill="#fff" opacity="0" width="445" height="824"></rect>
                    </a>
                    <a class="satisfaccion_choice" xlink:href="#" data-value="2">
                        <rect x="1232" y="438" fill="#fff" opacity="0" width="446" height="824"></rect>
                    </a>
                    <a class="satisfaccion_choice" xlink:href="#" data-value="3">
                        <rect x="1681" y="440" fill="#fff" opacity="0" width="449" height="821"></rect>
                    </a>
                    <a class="satisfaccion_choice" xlink:href="#" data-value="4">
                        <rect x="2125" y="438" fill="#fff" opacity="0" width="455" height="827"></rect>
                    </a>
                </svg>
            </div>
            <div class="modal-footer">
                <div class="form-group ">
                    <button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal" id="vista_previa_modal" data-backdrop="static" tabindex="-1" style="z-index:1900 !important">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <img id="img01" style="width:100%">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default " data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_notas" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-ml" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="mdCrearGrupoTitle">Notas</h5>
            </div>
            <div class="modal-body">
                <div class="col-xs-12">
                    <div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
                </div>
                <div class="row">

                    <div class="col-sm-12">
                        <h5 id="txtGrupoTitle"></h5>
                        <form id="formGuardar" method="POST" name="formGuardar" class="form-horizontal">
                            <div class="form-group">
                                <div class="col-md-2 px-2">
                                    <label for="nota_txt" style="line-height:35px">Imagen</label>
                                </div>
                                <div class="col-md-10 px-2">
                                    <button class="btn btn-rounded btn-primary btn-sm vista_previa_nota_img"
                                            style="display:none">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                </div>
                                <div class="col-md-12 px-2">
                                    <input type="file" name="nota_imagen" id="nota_imagen" accept=".jpeg,.png,.jpg">
                                </div>
                            </div>
                            <br>
                            <div style="" class="form-group">
                                <label class="col-xs-12 " for="nota_txt">Nota</label>
                                <input type="hidden" name="nota_id" id="nota_id">
                                <div class="col-md-12 px-2">
                                    <textarea id="nota_txt" name="nota_txt" rows="6" cols="48"
                                              style="width:100%"></textarea>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <div class="form-group ">
                    <?php if (array_key_exists(148, $usuario_permisos) && in_array("guardar_nota", $usuario_permisos[148])) { ?>
                        <button class="btn btn-success open_btn" title="Guardar" id="save_btn"><span
                                    class='glyphicon glyphicon-floppy-save'></span> Guardar
                        </button>
                    <?php } ?>
                    <button class="btn btn-default close_btn">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<input type="hidden" value="<?= $user_id ?>" id="sec_incidencias_ca_user_id">