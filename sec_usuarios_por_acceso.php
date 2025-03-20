<style>
.menu-submenus {
    display: none; /* Oculta los submenús por defecto */
}

.menu-subitem {
    padding-left: 20px;
}
</style>
<div class="sec_usuarios_por_acceso">
	<input id="fecha_actual" type="hidden" value="<?php echo date('Y-m-d'); ?>">

	<div id="loader_"></div>
    <div class="panel" style="border-color: transparent;">

        <div class="panel-heading">
            <div class="panel-title" style="color: #000;text-align: center;font-size: 22px;">Usuarios por acceso</div>
        </div>

        <div class="panel-body">

            <div class="row">
                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="SecAcceso_usuarios">Área</label>
                        <?php
                        $area_command = "SELECT * FROM tbl_areas ta WHERE ta.estado = 1";

                        $area_query = $mysqli->query($area_command);
                        ?>
                        <select class="form-control" data-col="area_id" name="area_id" id="select-area_id" style="width:100%;">
                            <option value="0" selected>Todos</option>
                            <?php
                            while ($ar = $area_query->fetch_assoc()) {
                            ?>
                                <option value="<?php echo $ar["id"]; ?>"><?php echo $ar["nombre"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="SecAcceso_usuarios">Cargo</label>
                        <?php
                        $cargo_command = "SELECT * FROM tbl_cargos ta WHERE ta.estado = 1";

                        $cargo_query = $mysqli->query($cargo_command);
                        ?>
                        <select class="form-control" data-col="cargo_id" name="cargo_id" id="select-cargo_id" style="width:100%;">
                            <option value="0" selected>Todos</option>
                            <?php
                            while ($car = $cargo_query->fetch_assoc()) {
                            ?>
                                <option value="<?php echo $car["id"]; ?>"><?php echo $car["nombre"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label for="SecAcceso_usuarios">Usuario</label>
                        <?php
                        $usuarios_command = "SELECT 
                        t1.id,
                        t1.usuario,
                        t2.nombre,
                        t2.apellido_paterno,
                        t2.apellido_materno
                        FROM tbl_usuarios t1
                        INNER JOIN tbl_personal_apt t2 ON t2.id = t1.personal_id
                        WHERE t1.estado = 1 AND t2.estado = 1
                        ";

                        $usuarios_query = $mysqli->query($usuarios_command);
                        ?>
                        <select class="form-control" data-col="usuario_id" name="usuario_id" id="select-usuario_id" style="width:100%;">
                            <option value="0" selected>Todos</option>
                            <?php
                            while ($us = $usuarios_query->fetch_assoc()) {
                            ?>
                                <option value="<?php echo $us["id"]; ?>"><?php echo $us["usuario"]; ?>
                                </option>
                            <?php
                            }
                            ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-md-2">
                    <button class="btn btn-block btn-rounded btn-primary" id="SecAcceso_btn_buscar" style="margin-top: 13px;">
                        <span class="glyphicon glyphicon-search"></span> Buscar
                    </button>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="form-group">
                        <button class="btn btn-block btn-rounded btn-success" id="SecAcceso_btn_exportar" style="margin-top: 13px;">
                            <span class="glyphicon glyphicon-download-alt"></span>
                            Exportar
                        </button>
                    </div>
                </div>
            </div>

            <br>


            <br>
            <div class="col-md-12">
                <div class="table-responsive" id="login_log_div_tabla">
                    <table class="table display responsive" style="width:100%" id="tabla_login_log">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Usuario</th>
                                <th>Área</th>
                                <th>Cargo</th>
                                <th>Menú</th>
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

<div class="listar" id="listar">

</div>