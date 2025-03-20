<div class="row bg-white">
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title">Registro de Parametros de Fraccionamiento</div>
        </div>
        <div class="panel-body no-pad">
            <form id="frm_vale_mant_fraccionamiento" method="POST">
                <input type="hidden" id="sec_vale_fracc_id" class="form-control" >
                <!-- <div class="col-md-1"></div> -->
                <div class="col-sm-6 col-md-2 col-md-offset-1">
                    <div class="form-group">
                        <label class="label-form" for="">Monto Minimo:</label>
                        <input id="sec_vale_fracc_monto_minimo" type="number" step="any" class="form-control text-right" placeholder="0.00">
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="form-group">
                        <label class="label-form" for="">Monto Maximo:</label>
                        <input id="sec_vale_fracc_monto_maximo" type="number" step="any" class="form-control text-right" placeholder="0.00">
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="form-group">
                        <label class="label-form" for="">Nro de Cuotas:</label>
                        <select class="form-control select2" id="sec_vale_fracc_cuotas">
                            <option value="0">- Seleccione -</option>
                            <option value="1">1 Cuota</option>
                            <option value="2">2 Cuotas</option>
                            <option value="3">3 Cuotas</option>
                            <option value="4">4 Cuotas</option>
                            <option value="5">5 Cuotas</option>
                            <option value="6">6 Cuotas</option>
                            <option value="7">7 Cuotas</option>
                            <option value="8">8 Cuotas</option>
                            <option value="9">9 Cuotas</option>
                            <option value="10">10 Cuotas</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="form-group">
                        <label class="label-form" for="">Estado:</label>
                        <select id="sec_vale_fracc_estado" class="form-control">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <div class="form-group">
                        <label class="label-form" for="">.</label>
                        <button type="submit" id="btn-fraccionamiento-registrar" class="form-control btn btn-info">Registrar</button>
                        <button type="submit" id="btn-fraccionamiento-modificar" class="form-control btn btn-warning" style="display: none;">Modificar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title">Listado de Parametros de Fraccionamiento</div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tbl_vale_fraccionamiento">
                    <thead>
                        <tr>
                            <th class="text-center" width="5%">#</th>
                            <th class="text-center" width="30%">Monto Minimo</th>
                            <th class="text-center" width="30%">Monto Maximo</th>
                            <th class="text-center" width="10%">Cuotas</th>
                            <th class="text-center" width="15%">Estado</th>
                            <th class="text-center" width="10%">Acciones</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>