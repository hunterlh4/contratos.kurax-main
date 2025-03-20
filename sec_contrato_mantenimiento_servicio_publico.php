<div class="panel">
    <div class="panel-heading text-center">
				<div class="panel-title"> SERVICIO PÚBLICO</div>
	</div>
    <br>

    
    <div class="row">
        <div class="col-md-2">
            <div class="form-group">
                <label for="">RUC: </label>
                <input type="text" id="sec_cont_mant_ser_pub_ruc" class="form-control">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="">Razón Social: </label>
                <input type="text" id="sec_cont_mant_ser_pub_razon_social" class="form-control">
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                <label for="">Nombre Comercial: </label>
                <input type="text" id="sec_cont_mant_ser_pub_nombre_comercial" class="form-control">
            </div>
        </div>
        <div class="col-md-2">
            <label for="">.</label>
            <button type="button" onclick="sec_contrato_mant_registrar_empresa_servicio_publico()" class="btn btn-info form-control">Guardar</button>
        </div>
    </div>


    <br>
    <br>

    <div class="row">
        <div class="col-md-12">
            <div class="tab-content">
                <div class="modo1 activo">
                    <div class="row">
                        <div class="table-responsive">

                            <table id="tbl_datos_mantenimientos_empresas_servicio_publicos" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">RUC</th>
                                        <th class="text-center">Razón Social</th>
                                        <th class="text-center">Nombre Comercial</th>
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

    </div>
</div>


<div class="modal fade" id="modal_empresa_servicio_publico_editar" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title titulo_modal_contrato_tipo"> Modificar Empresa de Servicio Público: </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">RUC: </label>
                            <input type="text" id="modal_ser_pub_ruc" class="form-control">
                            <input type="hidden" id="modal_ser_pub_id" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Razón Social: </label>
                            <input type="text" id="modal_ser_pub_razon_social" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Nombre Comercial: </label>
                            <input type="text" id="modal_ser_pub_nombre_comercial" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="">Estado: </label>
                            <select id="modal_ser_pub_status" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="icon fa fa-close"></i>
                    Cancelar</button>
                <button type="button" class="btn btn-success" onclick="sec_contrato_mant_modificar_empresa_servicio_publico()">
                    <i class="icon fa fa-save"></i>
                    <span>Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>