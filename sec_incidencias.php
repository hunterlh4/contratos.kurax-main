<style>
    .invisible {
        display: none;
    }
</style>

<?php
include("sys/db_connect.php");

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'incidencias'  LIMIT 1");
while ($r = $result->fetch_assoc()) $menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
    echo "No tienes permisos para acceder a este recurso";
    die;
}


$query = '';
if($login["usuario_locales"]){
    $query = "SELECT lr.id, lr.nombre FROM tbl_locales AS l 
    INNER JOIN tbl_locales_redes AS lr ON lr.id = l.red_id
    WHERE l.id IN (".implode(",", $login["usuario_locales"]).")
    GROUP BY lr.id
    ORDER BY lr.nombre ASC";
}else{
    $query = "SELECT id, nombre FROM tbl_locales_redes";
}

$result = $mysqli->query($query);
$redes = [];
while ($row = $result->fetch_assoc()) {
    $redes[] = $row;
}



$query = "SELECT id, nombre FROM tbl_servicio_tecnico_equipo";
$result = $mysqli->query($query);
$equipos = [];
while ($row = $result->fetch_assoc()) {
    $equipos[] = $row;
}
?>

<style>
    .campo_obligatorio_v2 {
        font-size: 13px;
        color: red;
    }

    .campo_obligatorio {
        font-size: 13px;
        color: red;
        padding-left: 50px;
    }

    /* Custom datatable wrapper for scroll on x-axis */
    .dataTables_scrollBody:has(table#tbl_incidencias) {
        max-height: calc(100vh - 390px);
    }

    body.expanded .dataTables_scrollBody:has(table#tbl_incidencias) {
        max-height: calc(100vh - 410px);
    }

    div#tbl_incidencias_info {
        text-wrap: wrap;
    }

    @media screen and (max-width: 1110px) {
        body.expanded .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 430px);
        }

        body.expanded .col-sm-5:has(#tbl_incidencias_info),
        body.expanded .col-sm-7:has(#tbl_incidencias_paginate) {
            width: 100%
        }
    }

    @media screen and (max-width: 964px) {
        .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 420px);
        }

        body.expanded .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 440px);
        }

        body.expanded .col-sm-5:has(#tbl_incidencias_info),
        body.expanded .col-sm-7:has(#tbl_incidencias_paginate) {
            width: 100%
        }
    }

    @media screen and (max-width: 796px) {
        .col-sm-5:has(#tbl_incidencias_info),
        .col-sm-7:has(#tbl_incidencias_paginate) {
            width: 100%
        }

        .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 440px);
        }

        body.expanded .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 470px);
        }
    }

    @media screen and (max-width: 768px) {
        .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 615px);
        }
    }

    
    @media screen and (max-width: 445px) {
        .dataTables_scrollBody:has(table#tbl_incidencias) {
            max-height: calc(100vh - 665px);
        }
    }

    /* Modal Detalle Incidencia */
    #modal_detalle_incidencia .modal-header {
        background: #659ce0;
    }
    
    #modal_detalle_incidencia .modal-header>button.close>span {
        font-size: 24px;
    }
    
    #modal_detalle_incidencia .modal-header>h5.modal-title {
        color: #fff;
        font-size: 16px;
        font-weight: 700;
    }

    #modal_detalle_incidencia .section-incidence_detail--content {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(min(350px, 100%), 1fr));
        column-gap: 2rem;
    }

    #modal_detalle_incidencia .modal_row_incidence--detail {
        display: grid;
        grid-template-columns: 120px 1fr;
        margin-bottom: 8px;
        column-gap: 6px;
    }

    #modal_detalle_incidencia .modal_row_incidence--detail label {
        display: flex;
        justify-content: space-between;
    }

    #modal_detalle_incidencia .modal_row_incidence--detail span {
        word-break: break-word;
    }

    /* Fix: Bug select/hover on table pagination  */
    #tbl_incidencias_paginate .pagination li a,
    #tbl_incidencias_paginate .pagination li.disabled a:hover {
        background-color: #fff;
        border: 1px solid #ddd;
        color: #8e8e93;
    }

    #tbl_incidencias_paginate .pagination li:not(#tbl_incidencias_ellipsis).disabled a,
    #tbl_incidencias_paginate .pagination li:not(#tbl_incidencias_ellipsis).disabled a:hover {
        background-color: #f4f4f4;
        opacity: 0.5;
        color: #575759;
        border-color: #c3c3c3;
    }    

    #tbl_incidencias_paginate .pagination li.active a,
    #tbl_incidencias_paginate .pagination li:hover a {
        color: #fff;
        background-color: #659ce0;
        border-color: #659ce0;
    }

    #tbl_incidencias_paginate .pagination li.active:hover a {
        background-color: #337ab7;
        border-color: #337ab7;
    }
    
</style>
<div class="content">
    <div class="row mb-4">
        <div class="col-xs-12 text-center">
            <div class="page-title"><i role="button" title="Recargar Incidencias"
                                       class="icon icon-inline fa fa-fw fa-envelope" id="incidencias_recargar"></i>
                Incidencias
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-xs-12">
            <div class="form-inline">
                <div class="form-group">
                    <label for="incidencias_redes">Red:</label>
                    <select name="incidencias_redes" id="incidencias_redes" class="form-control">
                        <option value="-1">Todos</option>
                        <?php foreach ($redes as $red) : ?>
                            <option value="<?= $red["id"] ?>"><?= $red["nombre"] ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-primary btn-sm" id="btn_actualizar_tbl">Actualizar</button>
                    <button type="button" class="btn btn-success modal_open_btn btn-sm" id="btn_columnas_incidencias">
                        <i class="glyphicon glyphicon-search"></i>
                        Columnas
                    </button>

                </div>
                <div class="pull-right">
                    <div class="form-group ml-3">
                        <label for="start_date">Fecha Inicio: </label>
                        <input type="date" id="start_date" class="sec_incidencia_date_input"
                               value="<?= date("Y-m-d", strtotime("-30 day")) ?>">
                    </div>
                    <div class="form-group ml-3">
                        <label for="end_date">Fecha Final: </label>
                        <input type="date" id="end_date" class="sec_incidencia_date_input" value="<?= date("Y-m-d") ?>">
                    </div>
                    <button class="btn btn-primary btn-sm" id="btn_actualizar_tbl_fechas", name="btn_actualizar_tbl_fechas"><i class="glyphicon glyphicon-search"></i></button>
                    <div class="form-group ml-2">
                        <button class="btn btn-primary btn-sm" id="sec_incidencias_csv_btn">CSV</button>
                    </div>
                    <div class="form-group ml-2">
                        <button class="btn btn-primary btn-sm" id="sec_incidencias_xls_btn">XLS</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-xs-6">
            <div class="form-inline">
                <label for="estado_select">Estado:</label>
                <select name="estado_select" id="estado_select" multiple="true" class="form-control input-sm"
                        style="width:50%">
                    <option value="0">Nuevo</option>
                    <option value="2">Asignado</option>
                    <option value="1">Atendido</option>
                    <option value="3">Reasignado</option>
                </select>
            </div>
        </div>
        <!--<div class="col-xs-6">
            <div class="form-inline">
                <label for="estado_select">Agente:</label>
                <select name="agente_select" id="agente_select" class="form-control input-sm" style="width:50%">
                    <option value="current_user" selected><?php /*echo $login["usuario"];*/ ?></option>
                    <option value="todos">Todos</option>
                </select>
            </div>
        </div>-->
    </div>
    <div class="row mt-4">
        <table
                id="tbl_incidencias"
                class="table table-striped table-hover table-condensed table-bordered"
                style="width:100%">
            <thead>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>


<div class="modal fade" id="modal_incidencia" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title" id="mdCrearGrupoTitle">Incidencia</h5>
            </div>
            <div class="modal-body">
                <div class="col-xs-12">
                    <div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
                </div>
                <div class="row">

                    <div class="col-sm-12">
                        <h5 id="txtGrupoTitle"></h5>
                        <form id="formGuardarSolucion" method="POST" name="formGuardarSolucion" class="form-horizontal">
                            <input type="hidden" id="reimpresion" name="reimpresion">
                            <input type="hidden" id="id" name="incidencia_id">
                            <input type="hidden" id="razon_social_id" name="razon_social_id">
                            <input type="hidden" id="razon_social" name="razon_social">
                            <input type="hidden" id="local_red_id" name="local_red_id">
                            <input type="hidden" id="local_red_nombre" name="local_red_nombre">
                            <div class="form-group">
                                <label class="col-xs-2 control-label" for="id">Incidencia :</label>
                                <div class="col-xs-10"><p class="form-control-static">-</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-2 control-label" for="created_at">Fecha y Hora :</label>
                                <div class="col-xs-10"><p class="form-control-static">-</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-xs-2 control-label" for="usuario">Usuario :</label>
                                <div class="col-xs-10"><p class="form-control-static">-</p>
                                </div>
                            </div>
                            <input type="hidden" id="tienda" name="local">
                            <div class="form-group">
                                <label class="col-xs-2 control-label" for="tienda">Tienda :</label>
                                <div class="col-xs-10"><p class="form-control-static">-</p>
                                </div>
                            </div>
                            <div class="form-group  mt-3">
                                <label class="col-xs-2 control-label" for="producto">Producto :</label>
                                <div class="col-xs-10">
                                    <select name="producto" id="producto" class="form-control w-50">
                                        <option>Apuestas Deportivas</option>
                                        <option>Juegos Virtuales</option>
                                        <option>Otros</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group  mt-3 mb-2">
                                <label class="col-xs-2 control-label mb-2" for="tipo">Tipo :</label>
                                <div class="col-xs-10">
                                    <select name="tipo" id="tipo" class="form-control w-50">
                                        <option style="display:none" data-producto="Apuestas Deportivas">BetShop
                                        </option>
                                        <option style="display:none" data-producto="Apuestas Deportivas">Betting
                                            terminal
                                        </option>
                                        <option style="display:none" data-producto="Apuestas Deportivas">Simulcast
                                        </option>
                                        <option style="display:none" data-producto="Apuestas Deportivas">Web Cliente</option>
                                        <option style="display:none" data-producto="Apuestas Deportivas">Aterax MVR</option>
                                        <option style="display:none" data-producto="Apuestas Deportivas">Aterax Terminal</option>

                                        <option style="display:none" data-producto="Juegos Virtuales">Golden Race
                                        </option>
                                        <option style="display:none" data-producto="Juegos Virtuales">Pre tickets
                                        </option>
                                        <option style="display:none" data-producto="Juegos Virtuales">Bingo</option>
                                        <option style="display:none" data-producto="Juegos Virtuales">Simulcast</option>
                                        <option style="display:none" data-producto="Juegos Virtuales">Nsoft (Lucky six)</option>
                                        <option style="display:none" data-producto="Juegos Virtuales">Torito</option>

                                        <option style="display:none" data-producto="Otros">CCTV</option>
                                        <option style="display:none" data-producto="Otros">Disashop</option>
                                        <option style="display:none" data-producto="Otros">GeoVictoria</option>
                                        <option style="display:none" data-producto="Otros">Gestión</option>
                                        <option style="display:none" data-producto="Otros">Snack</option>
                                        <option style="display:none" data-producto="Otros">Web (Retiro/Depósito)</option>
                                        <option style="display:none" data-producto="Otros">Sorteo</option>
                                    </select>
                                </div>
                            </div>
                            <input type="hidden" id="incidencia_txt" name="incidencia_txt">
                            <div class="form-group">
                                <label class="col-xs-2 control-label" for="incidencia_txt">Problema :</label>
                                <div class="col-xs-10"><p class="form-control-static">-</p>
                                </div>
                            </div>
                            <div class="form-group  mt-3 mb-2">
                                <label class="col-xs-2 control-label mb-2" for="tipo_inc">Tipo Inc<span
                                class="campo_obligatorio_v2">(*)</span>:</label>
                                <div class="col-xs-10">
                                    <select name="tipo_inc" id="tipo_inc" class="form-control w-50">
                                        <option value="" >Selecione</option>
                                        <option>Inc_proveedor</option>
                                        <option>Inc_cajero</option>
                                        <option>Inc_externo</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group  mt-3 mb-2">
                                <label class="col-xs-2 control-label mb-2" for="detalle_inc">Detalle Inc<span
                                class="campo_obligatorio_v2">(*)</span>:</label>
                                <div class="col-xs-10">
                                    <select name="detalle_inc" id="detalle_inc" class="form-control w-50">
                                        <option value="" >Seleccione</option>
                                        <option>caída_servicios</option>
                                        <option>Internet</option>
                                        <option>aplicativo_configuración</option>
                                        <option>aplicativo_accesos</option>
                                        <option>aplicativo_liquidación</option>
                                        <option>aplicativo_información</option>
                                        <option>aplicativo_odin</option>
                                        <option>aplicativo_noimpresión</option>
                                        <option>Billetero_error</option>
                                        <option>Billetero_openstacker</option>
                                        <option>Impresora_papel_atascado</option>
                                        <option>Impresora_averiada</option>
                                        <option>Impresora_configuración</option>
                                        <option>escaner_configuración</option>
                                        <option>escaner_averiado</option>
                                        <option>Pantalla_desconfigurada</option>
                                        <option>Pantalla_resolución</option>
                                        <option>adm_vpn</option>
                                        <option>adm_eliminacióndeturno</option>
                                        <option>adm_préstamos</option>
                                        <option>huellero_configuración</option>
                                        <option>huellero_averiado</option>
                                        <option>externo_streamingTV</option>
                                        <option>externo_pantalladecuotas</option>
                                        <option>apuestas_reclamos</option>
                                        <option>apuestas_oferta</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-xs-12 div_espacio"></div>
                            <div style="" class="form-group">
                                <label class="col-xs-2 control-label" for="txtGroupDesc">Solución <span
                                            class="campo_obligatorio_v2">(*)</span>:</label>
                                <div class="col-xs-10">
                                    <textarea id="solucion_txt" name="solucion_txt" rows="4" cols="48"></textarea>
                                </div>
                            </div>
                            <div class="col-xs-12 div_espacio"></div>

                            <div class="botones_inc form-group">
                                <label for="selectTipo" class="col-xs-2 control-label" style="padding-left:0px">Recomendación:</label>
                                <div class="col-xs-10">
                                    <div class="opciones_inc mb-1">
                                        <label for="selectTipo0"><span>Visita Técnica</span>
                                            <input id="selectTipo0" class="selectTipo" type="radio"
                                                   name="selectRecomendacion" value="Visita Técnica">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="opciones_inc mb-1">
                                        <label for="selectTipo1"><span>Proveedor de Internet</span>
                                            <input id="selectTipo1" class="selectTipo" type="radio"
                                                   name="selectRecomendacion" value="Proveedor de Internet">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="opciones_inc mb-1">
                                        <label for="selectTipo2"><span>Capacitación</span>
                                            <input id="selectTipo2" class="selectTipo" type="radio"
                                                   name="selectRecomendacion" value="Capacitación">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="opciones_inc mb-1">
                                        <label for="selectTipo3"><span>Seguimiento Soporte</span>
                                            <input id="selectTipo3" class="selectTipo" type="radio"
                                                   name="selectRecomendacion" value="Seguimiento Soporte">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                    <div class="opciones_inc mb-1">
                                        <label for="selectTipo4"><span>RR.HH.</span>
                                            <input id="selectTipo4" class="selectTipo" type="radio"
                                                   name="selectRecomendacion" value="RR.HH.">
                                            <span class="checkmark"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!--<div class="visita_tecnica" style="display:none">
                                <div class="botones_inc form-group">
                                    <label for="selectEquipo" class="col-xs-2 control-label"  style="padding-left:0px">Equipo a Revisar:</label>
                                    <div class="col-xs-10">
                                        <div class="opciones_inc mb-1">
                                            <label for="selectEquipo0" ><span>CPU</span>
                                                <input id="selectEquipo0" class="selectEquipo" type="radio" name="selectEquipo" value="CPU" >
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="opciones_inc mb-1">
                                            <label for="selectEquipo1"><span>TERMINAL</span>
                                                <input id="selectEquipo1" class="selectEquipo" type="radio" name="selectEquipo" value="TERMINAL">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                        <div class="opciones_inc mb-1">
                                            <label for="selectEquipo2"><span>AIO</span>
                                                <input id="selectEquipo2" class="selectEquipo" type="radio" name="selectEquipo" value="AIO">
                                                <span class="checkmark"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>-->

                            <div class="visita_tecnica" style="display:none">
                                <div class="botones_inc form-group">
                                    <label for="selectEquipo" class="col-xs-2 control-label" style="padding-left:0px">Equipo
                                        a Revisar:</label>
                                    <div class="col-xs-10">
                                        <select name="equipo_id" id="equipo_id" class="form-control input-sm"
                                                style="width:50%">
                                            <option value=0>Nuevo</option>
                                            <?php foreach ($equipos as $equipo) : ?>
                                                <option value="<?= $equipo["id"] ?>"><?= $equipo["nombre"] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

			                    <!--<div class="botones_inc form-group">
			                        <label for="selectPeri" class="col-xs-2 control-label"  style="padding-left:0px">Periférico:</label>
			                        <div class="col-xs-10">
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri0" ><span>Imp. Multifuncional</span>
				                                <input id="selectPeri0" class="selectPeri" type="checkbox" name="selectPeri" value="Imp. Multifuncional" >
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri1"><span>Imp. térmica</span>
				                                <input id="selectPeri1" class="selectPeri" type="checkbox" name="selectPeri" value="Imp. térmica">
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri2"><span>Televisor</span>
				                                <input id="selectPeri2" class="selectPeri" type="checkbox" name="selectPeri" value="Televisor">
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri3"><span>Monitor</span>
				                                <input id="selectPeri3" class="selectPeri" type="checkbox" name="selectPeri" value="Monitor">
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri4"><span>Lector de Barra</span>
				                                <input id="selectPeri4" class="selectPeri" type="checkbox" name="selectPeri" value="Lector de Barra">
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri5"><span>Huellero</span>
				                                <input id="selectPeri5" class="selectPeri" type="checkbox" name="selectPeri" value="Huellero">
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
				                        <div class="opciones_inc mb-1">
				                            <label for="selectPeri6"><span>Billetero</span>
				                                <input id="selectPeri6" class="selectPeri" type="checkbox" name="selectPeri" value="Billetero">
				                                <span class="checkmark"></span>
				                            </label>
				                        </div>
									</div>
								</div>-->
			                    <div class="form-group">
			                    	<label class="col-xs-2 control-label" for="nota_tecnico">Nota para el Técnico <span class="campo_obligatorio_v2">(*)</span>:</label>
			                    	<div class="col-xs-10">
			                    		<textarea  id="nota_tecnico" name="nota_tecnico" rows="4" cols="48" maxlength="200"></textarea>
			                    	</div>
			                    </div>
			                </div>
							<div class="seguimiento_soporte" style="display:none">
                               <div class="form-group">
                                   <label class="col-xs-2 control-label" for="nota_soporte">Nota:</label>
                                   <div class="col-xs-10">
                                       <textarea  id="nota_soporte" name="nota_soporte" rows="4" cols="48"></textarea>
                                   </div>
                               </div>
                           </div>
						   <div class="foto" style="display:none">
							<label  class="col-xs-2 control-label" for="estado">Foto</label>
							<div class="col-xs-10">
								<input type="file" name="foto" id="foto">
								<!--<div class="col-xs-10" id="imagen_terminado">
									<img id="foto_terminado" name="foto_terminado" class="imagenes_modal">
								</div>-->
							</div>
						</div>
						</form>
					</div>
				</div>
			</div>
			<span class="campo_obligatorio">Los Campos con (*) son obligatorios</span>
			<div class="modal-footer">
				<div class="form-group ">
			        <?php if(array_key_exists(147,$usuario_permisos) && in_array("save", $usuario_permisos[147])) { ?>
					<button class="btn btn-success open_btn" title="Abrir" id="solve_btn"><span class='glyphicon glyphicon-floppy-save'></span> Enviar</button>
				    <?php } ?>
					<button class="btn btn-default close_btn">Cancelar</button>
				</div>
				
			</div>
			
		</div>
	</div>
</div>

<!-- Modal show/hide columnas-->
<div class="modal fade" id="filter_columnas_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">Elegir Columnas</h4>
            </div>
            <div class="modal-body pre-scrollable">
                <div class="row">
                    <form class="form" method="post" id="contratos_list_cols">
                        <div class="col-xs-12 mb-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control list-filter-input"
                                           data-list="col_select_list" id="filtro" placeholder="Busqueda" autofocus
                                           autocomplete="off">
                                    <div class="input-group-addon"><span class="glyphicon glyphicon-search"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <ul class="col-xs-12" id="col_select_list">
                        </ul>
                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="sec_incidencias_modal_agentes" tabindex="-1" role="dialog"
     aria-labelledby="sec_incidencias_modal_agentes_label">
    <div class="modal-dialog">
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="sec_incidencias_modal_agentes_label">Agentes</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <form class="form" method="post" id="sec_incidencias_form_agentes">
                        <input type="hidden" id="sec_incidencias_input_incidencia_id" value="">
                        <div class="col-xs-12 mb-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <input type="text" class="form-control" id="sec_incidencias_input_filtrar_agentes"
                                           placeholder="Busqueda" autofocus autocomplete="off">
                                    <div class="input-group-addon"><span class="glyphicon glyphicon-search"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="col-xs-12" id="sec_incidencias_list_agentes" style="overflow: auto; height: 250px; list-style-type: none;">
                        </ul>

                    </form>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="sec_incidencias_btn_reasignar_agente_seleccionado">Aceptar</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detalle Incidencia -->
<div class="modal fade" id="modal_detalle_incidencia" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h5 class="modal-title">Detalle de Incidencia</h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="section-incidence_detail--content col-sm-12">
                        <div class="modal_row_incidence--detail">
                            <label>Id Incidencia<span>:</span></label> 
                            <span id="id_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Fecha y Hora<span>:</span></label>
                            <span id="date_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Usuario<span>:</span></label>
                            <span id="user_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Tienda<span>:</span></label>
                            <span id="local_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Red<span>:</span></label>
                            <span id="red_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Tel. Tienda<span>:</span></label>
                            <span id="phone_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Teléfono 2<span>:</span></label>
                            <span id="phone2_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Producto<span>:</span></label>
                            <span id="product_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Tipo<span>:</span></label>
                            <span id="type_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Reimpresión<span>:</span></label>
                            <span id="reprint_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Id Teamviewer<span>:</span></label>
                            <span id="id_tvw_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Contraseña Teamviewer<span>:</span></label>
                            <span id="pass_tvw_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Incidencia<span>:</span></label>
                            <span id="problem_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Estado<span>:</span></label>
                            <span id="status_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Estado Serv. Téc.<span>:</span></label>
                            <span id="status_serv_tec_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Fecha Asignada<span>:</span></label>
                            <span id="assigned_date_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Agente<span>:</span></label>
                            <span id="agent_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Agente 2<span>:</span></label>
                            <span id="agent2_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Agente Reasignado<span>:</span></label>
                            <span id="agent_reasignado_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Fecha Solución<span>:</span></label>
                            <span id="solution_date_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Recomendación<span>:</span></label>
                            <span id="tip_incidence--selected">---</span>
                        </div>

                        <div class="modal_row_incidence--detail">
                            <label>Observación<span>:</span></label>
                            <span id="obs_incidence--selected">---</span>
                        </div>
                        <div class="modal_row_incidence--detail">
                            <label>Satisfacción<span>:</span></label>
                            <span id="satisfaction_incidence--selected">---</span>
                        </div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
                <div class="d-flex justify-content-between">
                    <button id="btn_reasignar" class="btn btn-warning mr-5">Reasignar</button>
                    <button class="btn btn-danger" data-dismiss="modal">Cerrar</button>
                </div>
			</div>
		</div>
	</div>
</div>