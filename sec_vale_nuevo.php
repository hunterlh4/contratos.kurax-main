<div class="panel">
    <div class="panel-heading">
        <div class="panel-title">Registro de Vale de Descuento</div>
    </div>
    <div class="panel-body no-pad">

        <form id="frm_nuevo_vale_descuento" method="post">
            <div class="row">
                <div class="col-sm-6 col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Empresa: <strong class="text-danger">*</strong> </label>
                        <select class="form-control select2" name="sec_vale_nuevo_empresa" id="sec_vale_nuevo_empresa"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="form-group">
                        <label for="" class="control-label">Zona: <strong class="text-danger">*</strong></label>
                        <select class="form-control select2" name="sec_vale_nuevo_zona" id="sec_vale_nuevo_zona">
                            <option value="0">- Seleccione -</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-md-5">
                    <div class="form-group">
                        <label for="" class="control-label">Local: <strong class="text-danger">*</strong></label>
                        <select class="form-control select2" name="sec_vale_nuevo_local" id="sec_vale_nuevo_local">
                            <option value="0">- Seleccione -</option>
                        </select>
                    </div>
                </div>

                <div class="col-sm-6 col-md-4">
                    <div class="form-group">
                        <label for="" class="control-label">Empleado: <strong class="text-danger">*</strong></label>
                        <select name="sec_vale_nuevo_empleado" id="sec_vale_nuevo_empleado" class="form-control select2">
                            <option value="0">- Seleccione -</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-4 col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">Fecha Vale: <strong class="text-danger">*</strong></label>
                        <input type="text" readonly value="<?= date('d-m-Y') ?>" class="form-control text-center sec_vale_nuevo_datepicker" name="sec_vale_nuevo_fecha_vale" id="sec_vale_nuevo_fecha_vale" class="form-control">
                    </div>
                </div>
                <div class="col-sm-5 col-md-4">
                    <div class="form-group">
                        <label for="sec_vale_nuevo_motivo" class="control-label">Motivo: <strong class="text-danger">*</strong></label>
                        <select name="sec_vale_nuevo_motivo" id="sec_vale_nuevo_motivo" class="form-control select2">
                            <option value="0">- Seleccione -</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-3 col-md-2">
                    <div class="form-group">
                        <label for="" class="control-label">Monto: <strong class="text-danger">*</strong></label>
                        <input type="number" step="any" min="0" class="form-control text-right" name="sec_vale_nuevo_monto" id="sec_vale_nuevo_monto" placeholder="Ingrese un monto">
                    </div>
                </div>
                <div class="col-sm-12 col-md-12">
                    <div class="form-group">
                        <label for="sec_vale_nuevo_observacion" class="control-label">Observación:</label>
                        <textarea class="form-control" name="sec_vale_nuevo_observacion" id="sec_vale_nuevo_observacion" cols="30" rows="1" placeholder="Ingrese una observacion"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-group mt-2">
                <div class="col-sm-8"></div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-info form-control">Guardar</button>
                </div>
                <div class="col-sm-2">
                    <button type="button" id="frm-vale-descuento-btn-reset" class="btn btn-danger form-control">Cancelar</button>
                </div>
            </div>
        </form>

    </div>
</div>