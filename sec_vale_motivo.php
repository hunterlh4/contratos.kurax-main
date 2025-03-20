<div class="row bg-white">
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title">Registro de Motivo</div>
        </div>
        <div class="panel-body no-pad">
            <form id="frm_vale_mant_motivo" method="POST">
                <input type="hidden" id="sec_vale_motivo_id" class="form-control" >
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="label-form" for="">Empresa:</label>
                        <select id="sec_vale_motivo_empresa" class="form-control select2"></select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="label-form" for="">Descripción del Motivo:</label>
                        <input id="sec_vale_motivo_nombre" maxlength="200" type="text" class="form-control" placeholder="Ingrese la descripción del motivo">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="label-form" for="">Estado:</label>
                        <select id="sec_vale_motivo_estado" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label class="label-form" for="">.</label>
                        <button type="submit" id="btn-motivo-registrar" class="form-control btn btn-info">Registrar</button>
                        <button type="submit" id="btn-motivo-modificar" class="form-control btn btn-warning" style="display: none;">Modificar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title">Listado de Motivos</div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tbl_vale_motivos">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th class="text-center" width="40%">Empresa</th>
                            <th class="text-center" width="30%">Nombre</th>
                            <th class="text-center" width="15%">Estado</th>
                            <th class="text-center" width="10%">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>