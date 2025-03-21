const contrato = {
  template: `
    <section>


    <!-- Inicio Panel Ficha de Condiciones -->
    <div class="panel" :ref="'panel_contrato_' + index">
        <div class="panel-heading">
            <div class="col-md-9">
                <div class="panel-title title-panel ">Ficha de Condiciones del Contrato de Arrendatario #{{ index + 1}}</div>
            </div>
            <div class="col-md-3 text-right">
                <button @click="agregar_contrato" class="btn btn-info btn-sm" type="button"> <i class="icon fa fa-plus"></i>  Agregar Ficha de Condiciones</button>
                <button v-if="index > 0" @click="eliminar_contrato(index)" class="btn btn-danger btn-sm" type="button"> <i class="icon fa fa-trash"></i></button>
            </div>
        </div>
        
        <div class="panel-body mt-5">
            <!-- Inicio Panel Innmuebles -->
            <div class="panel">
                <div class="panel-heading">
                    Datos Del Inmueble
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Departamento:</label>
                            <v-select ref="departamento" placeholder="-- Seleccione --" class="w-100" :options="departamentos" :filterable="true" label="text"  v-model='departamento_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Provincia:</label>
                            <v-select ref="provincia" placeholder="-- Seleccione --" class="w-100" :options="provincias" :filterable="true" label="text"  v-model='provincia_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Distrito:</label>
                            <v-select ref="distrito" placeholder="-- Seleccione --" class="w-100" :options="distritos" :filterable="true" label="text"  v-model='distrito_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <div class="form-group">
                            <label for="">Ubicación del Inmueble:</label>
                            <input ref="ubicacion" class="form-control" v-model="contrato.inmuebles.ubicacion" type="text">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Área Arrendada (m2):</label>
                            <input ref="area_arrendada" class="form-control" v-model="contrato.inmuebles.area_arrendada"  >
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">N° Partida Registral:</label>
                            <input ref="num_partida_registral" class="form-control" v-model="contrato.inmuebles.num_partida_registral"  type="text">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Oficina Registral (Sede):</label>
                            <input ref="oficina_registral" class="form-control" v-model="contrato.inmuebles.oficina_registral" type="text">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Latitud:</label>
                            <input ref="latitud" class="form-control" v-model="contrato.inmuebles.latitud" type="text">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Longitud:</label>
                            <input ref="longitud" class="form-control" v-model="contrato.inmuebles.longitud" type="text">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final Panel Innmuebles -->
            
            <!-- Inicio Panel Innmuebles - Servicio de Agua -->
            <div class="panel">
                <div class="panel-heading">
                    Datos Del inmueble - Servicio De Agua
                </div>
                <div class="panel-body">
                    <div class="w-100 text-right">
                        <button type="button" @click="agregar_nuevo_suministro_agua" class="btn btn-info btn-sm"><i class="icon fa fa-plus"></i> Nuevo Suministro</button>
                    </div>
                  
                    <div class="row" v-for="(item, it) in contrato.inmuebles.inmueble_servicio_agua" :key="it">
                        <component-inmueble-suministro-agua :ref="'comp_suministro_agua_'+it"  @event_eliminar_suministro_agua="eliminar_suministro_agua"  :suministro_agua="item" :index-contrato="index" :index="it"></component-inmueble-suministro-agua>
                    </div>
                </div>
            </div>
            <!-- Final Panel Innmuebles - Servicio de Agua -->

            <!-- Inicio Panel Innmuebles - Servicio de Luz -->
            <div class="panel">
                <div class="panel-heading">
                    Datos Del inmueble - Servicio De Luz
                </div>
                <div class="panel-body">

                    <div class="w-100 text-right">
                        <button type="button" @click="agregar_nuevo_suministro_luz" class="btn btn-info btn-sm"><i class="icon fa fa-plus"></i> Nuevo Suministro</button>
                    </div>
                  
                    <div class="row" v-for="(item, it) in contrato.inmuebles.inmueble_servicio_luz" :key="it">
                        <component-inmueble-suministro-luz :ref="'comp_suministro_luz_'+it" @event_eliminar_suministro_luz="eliminar_suministro_luz"  :suministro_luz="item" :index-contrato="index" :index="it"></component-inmueble-suministro-luz>
                    </div>
                    
                </div>
            </div>
            <!-- Final Panel Innmuebles - Servicio de Luz -->

            <!-- Inicio Panel Innmuebles - Arbitrios Municipales -->
            <div class="panel">
                <div class="panel-heading">
                    Datos Del inmueble - Arbitrios Municipales
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Compromiso de Pago:</label>
                            <v-select ref="tipo_compromiso_pago_arbitrios" placeholder="-- Seleccione --" class="w-100" :options="compromiso_pago_arbitrios" :filterable="true" label="text"  v-model='compromiso_pago_arbitrio_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3" v-if="contrato.inmuebles.tipo_compromiso_pago_arbitrios == 1">
                        <div class="form-group">
                            <label for="">Porcentaje del Pago(%):</label>
                            <input ref="porcentaje_pago_arbitrios" v-model="contrato.inmuebles.porcentaje_pago_arbitrios"  type="text" class="form-control text-right">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final Panel Innmuebles - Arbitrios Municipales -->

            <!-- Inicio Panel Condiciones Economicas -->
            <div class="panel">
                <div class="panel-heading">
                    Condiciones Económicas y Comerciales
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Moneda del Contrato:</label>
                            <v-select ref="tipo_moneda_id" placeholder="-- Seleccione --" class="w-100" :options="monedas" :filterable="true" label="text"  v-model='moneda_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Pago de Renta:</label>
                            <v-select ref="pago_renta_id" placeholder="-- Seleccione --" class="w-100" :options="pago_renta" :filterable="true" label="text"  v-model='pago_renta_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Pago de Renta Pactada (Cuota Fija):</label>
                            <input @change="calcular_monto_segun_impuesto" ref="monto_renta" v-model="contrato.condicion_economica.monto_renta" class="form-control text-right" type="text">
                        </div>
                    </div>

                    <div class="col-md-3" v-if="contrato.condicion_economica.pago_renta_id == 2">
                        <div class="form-group">
                            <label for="">Porcentaje de Venta (Cuota Variable):</label>
                            <input ref="cuota_variable" v-model="contrato.condicion_economica.cuota_variable" class="form-control text-right" type="text">
                        </div>
                    </div>

                    <div class="col-md-3" v-if="contrato.condicion_economica.pago_renta_id == 2">
                        <div class="form-group">
                            <label for="">Tipo de Venta:</label>
                            <v-select ref="tipo_venta_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_venta" :filterable="true" label="text"  v-model='tipo_venta_val'></v-select>
                        </div>
                    </div>
                    

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">IGV en la Renta:</label>
                            <v-select ref="afectacion_igv_id" placeholder="-- Seleccione --" class="w-100" :options="igv_venta" :filterable="true" label="text"  v-model='igv_renta_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Monto de Garantia:</label>
                            <input ref="garantia_monto" v-model="contrato.condicion_economica.garantia_monto" class="form-control text-right" type="text">
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Adelantos:</label>
                            <v-select ref="tipo_adelanto_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_adelanto" :filterable="true" label="text"  v-model='tipo_adelanto_val'></v-select>
                        </div>
                    </div>


                    <div class="col-md-12"></div>
                    <div class="col-md-3" v-if="contrato.condicion_economica.adelantos.length > 0">
                        <table :id="'table-adelantos-'+ index" tabindex="0" class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Adelantos</th>
                                    <th>Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, index) in contrato.condicion_economica.adelantos" :key="index">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ item.text }}</td>
                                    <td>
                                        <a class="btn btn-success btn-xs" data-toggle="tooltip" 
                                        data-placement="top" title="Editar" style="width: 24px;" 
                                        @click="show_modal_modificar_adelantos">
                                        <i class="fa fa-edit"></i></a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            <!-- Final Panel Condiciones Economicas -->
            
            <!-- Inicio Panel Inpusto a la Renta -->
            <div class="panel">
                <div class="panel-heading">
                    Inpuesto a la Renta
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Impuesto a la Renta / Detracción:</label>
                            <v-select ref="impuesto_a_la_renta_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_impuesto_renta" :filterable="true" label="text"  v-model='tipo_impuesto_renta_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">¿AT Deposita a Renta a SUNAT? Carta de Instrucción:</label>
                            <v-select ref="carta_de_instruccion_id" placeholder="-- Seleccione --" class="w-100" :options="carta_instruccion" :filterable="true" label="text"  v-model='carta_instruccion_val'></v-select>
                        </div>
                    </div>
                    
                    <div class="col-md-12" v-if="contrato.condicion_economica.view_ir_detalle">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" style="font-size: 10px; margin-top: 10px; margin-bottom: 0px">
                                <tbody>
                                <tr>
                                    <td style="width: 120px; font-weight: bold">Impuesto a la renta</td>
                                    <td style="text-align: right">{{ contrato.condicion_economica.ir_detalle.impuesto_a_la_renta }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 120px; font-weight: bold">Renta Bruta</td>
                                    <td style="text-align: right">{{ contrato.condicion_economica.ir_detalle.renta_bruta }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 120px; font-weight: bold">Renta a pagar</td>
                                    <td style="text-align: right">{{ contrato.condicion_economica.ir_detalle.renta_neta }}</td>
                                </tr>
                                <tr>
                                    <td style="width: 120px; font-weight: bold">Detalle</td>
                                    <td style="text-align: right">{{ contrato.condicion_economica.ir_detalle.detalle }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final Panel Inpusto a la Renta -->

            <!-- Inicio Panel Periodo de Gracia -->
            <div class="panel">
                <div class="panel-heading">
                    Período de Gracia
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Periodo de Gracia:</label>
                            <v-select ref="periodo_gracia_id" placeholder="-- Seleccione --" class="w-100" :options="periodo_gracia" :filterable="true" label="text"  v-model='periodo_gracia_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-3" v-if="contrato.condicion_economica.periodo_gracia_id == 1">
                        <div class="form-group">
                            <label for="">Numero de Dias:</label>
                            <input ref="periodo_gracia_numero" v-model="contrato.condicion_economica.periodo_gracia_numero" class="form-control" type="text">
                        </div>
                    </div>

                </div>
            </div>
            <!-- Final Panel Periodo de Gracia -->

            <!-- Inicio Panel Vigencia -->
            <div class="panel">
                <div class="panel-heading">
                    Vigencia
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Vigencia</label>
                            <v-select ref="plazo_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_periodo" :filterable="true" label="text"  v-model='tipo_periodo_val'></v-select>
                        </div>
                    </div>

                    <div :class="'col-md-2 ' + (contrato.condicion_economica.plazo_id == 1 ? 'show':'hide')">
                        <div class="form-group">
                            <label for="">Vigencia del Contrato en Meses:</label>
                            <input ref="cant_meses_contrato" v-model="cant_meses_contrato" type="number" class="form-control">
                        </div>
                    </div>

                    <div :class="'col-md-3 ' + (contrato.condicion_economica.plazo_id == 1 ? 'show':'hide')">
                        <div class="form-group">
                            <label for="">Vigencia del Contrato (Solo Lectura):</label>
                            <input ref="vigencia_contrato_lectura" disabled v-model="contrato.condicion_economica.vigencia_contrato_lectura" type="text" class="form-control">
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="">Fecha de Inicio:</label>
                            <input ref="fecha_inicio" v-model="fecha_inicio_val" type="text" class="form-control text-center flatpickr-nuevo">
                        </div>
                    </div>

                    <div :class="'col-md-2 ' + (contrato.condicion_economica.plazo_id == 1 ? 'show':'hide')">
                        <div class="form-group">
                            <label for="">Fecha de Fin:</label>
                            <input ref="fecha_fin" v-model="fecha_fin_val"  type="text" class="form-control text-center flatpickr-nuevo">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final Panel Vigencia -->

            <!-- Inicio Panel Incremento -->
            <div class="panel">
                <div class="panel-heading">
                    Incremento
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Incrementos</label>
                            <v-select ref="tipo_incremento_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_incremento" :filterable="true" label="text"  v-model='tipo_incremento_val'></v-select>
                        </div>
                    </div>
                    <div class="col-md-12"></div>
                    <div class="col-md-6" v-if="contrato.condicion_economica.tipo_incremento_id == 1">
                        <div class="table-responsive">
                            <table :id="'table-incrementos-'+ index" tabindex="0" class="table table-bordered" style="font-size:10px; margin-top: 12px;">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Incremento</th>
                                        <th class="text-center">Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, it) in contrato.condicion_economica.incrementos" :key="it">
                                        <td class="text-center">{{ it + 1 }}</td>
                                        <td>{{ item.valor + ' ' + item.tipo_valor + ' ' + item.tipo_continuidad + ' ' + item.a_partir_del_anio  }}</td>
                                        <td class="text-center">
                                            <a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" 
                                            title="Editar" @click="show_modal_modificar_incremento(it)">
                                            <i class="fa fa-edit"></i></a>
                                            <a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" 
                                            title="Eliminar" @click="eliminar_incremento(it)">
                                            <i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="text-align:center;">
                                            <button type="button" class="btn btn-sm btn-success" 
                                            @click="show_modal_nuevo_incremento" >
                                            <i class="icon fa fa-plus"></i><span> Agregar Incremento</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
            <!-- Final Panel Incremento --

            <!-- Inicio Panel Inflación -->
            <div class="panel">
                <div class="panel-heading">
                    Inflación
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Inflación</label>
                            <v-select ref="tipo_inflacion_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_inflacion" :filterable="true" label="text"  v-model='tipo_inflacion_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-12"></div>
                    <div class="col-md-8" v-if="contrato.condicion_economica.tipo_inflacion_id == 1">
                        <div class="table-responsive">
                            <table :id="'table-inflaciones-'+ index" tabindex="0" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Periodicidad</th>
                                        <th class="text-center">Porcentaje Añadido</th>
                                        <th class="text-center">Tope de Inflación</th>
                                        <th class="text-center">Minimo de Inflación</th>
                                        <th class="text-center">Aplicación</th>
                                        <th class="text-center">Acc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, it) in contrato.condicion_economica.inflaciones" :key="it">
                                        <td class="text-center">{{ it + 1 }}</td>
                                        <td class="text-center">{{ item.tipo_periodicidad + ' ' + item.numero + ' ' + item.tipo_anio_mes }}</td>
                                        <td class="text-center">{{ item.porcentaje_anadido }}</td>
                                        <td class="text-center">{{ item.tope_inflacion }}</td>
                                        <td class="text-center">{{ item.minimo_inflacion }}</td>
                                        <td class="text-center">{{ item.tipo_aplicacion }}</td>
                                        <td class="text-center">
                                            <a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" @click="show_modal_modificar_inflacion(it)"><i class="fa fa-edit"></i></a>
                                            <a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" @click="eliminar_inflacion(it)"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="7" style="text-align:center;">
                                            <button type="button" class="btn btn-sm btn-success" 
                                            @click="show_modal_nueva_inflacion" >
                                            <i class="icon fa fa-plus"></i><span> Agregar Inflación</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    

                </div>
            </div>
            <!-- Final Panel Inflación -->

        

            <!-- Inicio Panel Cuota Extraordinaria -->
            <div class="panel">
                <div class="panel-heading">
                    Cuota Extraordinaria
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="">Cuota Extraordinaria</label>
                            <v-select ref="tipo_cuota_extraordinaria_id" placeholder="-- Seleccione --" class="w-100" :options="tipo_cuota_extraordinaria" :filterable="true" label="text"  v-model='tipo_cuota_extraordinaria_val'></v-select>
                        </div>
                    </div>

                    <div class="col-md-12"></div>
                    <div class="col-xs-12 col-md-8 col-lg-8" v-if="contrato.condicion_economica.tipo_cuota_extraordinaria_id == 1">
                        <div class="table-responsive">
                            <table :id="'table-cuotas-extraordinarias-'+ index" tabindex="0" class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th class="text-center">Mes en el que se Paga</th>
                                        <th class="text-center">Multiplicador</th>
                                        <th class="text-center">Acc.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, it) in contrato.condicion_economica.cuotas_extraordinarias" :key="it">
                                        <td class="text-left">{{ it + 1}}</td>
                                        <td class="text-left">{{ item.mes }}</td>
                                        <td class="text-center">{{ item.multiplicador }}</td>
                                        <td class="text-center">
                                            <a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" @click="show_modal_modificar_cuota_extraordinaria(it)"><i class="fa fa-edit"></i></a>
                                            <a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" @click="eliminar_cuota_extraordinaria(it)"><i class="fa fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" style="text-align:center;">
                                            <button type="button" class="btn btn-sm btn-success" 
                                            @click="show_modal_nueva_cuota_extraordinaria" >
                                            <i class="icon fa fa-plus"></i><span> Agregar Cuota Extraordinaria</span>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final Panel Cuota Extraordinaria -->
       
            <!-- Inicio Panel Beneficiarios -->
            <div class="panel">
                <div class="panel-heading">
                    Beneficiarios
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table :id="'table-beneficiarios-'+ index" tabindex="0" class="table table-bordered table-hover no-mb" style="font-size: 11px; margin-top: 10px">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Número Documento</th>
                                    <th class="text-center">Nombre del Beneficiario</th>
                                    <th class="text-center">Forma de Pago</th>
                                    <th class="text-center">Banco</th>
                                    <th class="text-center">Número de cuenta bancaria</th>
                                    <th class="text-center">Número de CCI</th>
                                    <th class="text-center">Monto a depositar</th>
                                    <th class="text-center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, it) in contrato.condicion_economica.beneficiarios" :key="it">
                                    <td class="text-center">{{ it + 1 }}</td>
                                    <td class="text-left">{{ item.num_docu }}</td>
                                    <td class="text-left">{{ item.nombre }}</td>
                                    <td class="text-left">{{ item.forma_pago }}</td>
                                    <td class="text-left">{{ item.banco }}</td>
                                    <td class="text-left">{{ item.num_cuenta_bancaria }}</td>
                                    <td class="text-left">{{ item.num_cuenta_cci }}</td>
                                    <td class="text-left">
                                        <span v-if="item.tipo_monto_id == 1">{{ item.monto }}</span>
                                        <span v-if="item.tipo_monto_id == 2">{{ item.monto + '%'}}</span>
                                        <span v-if="item.tipo_monto_id == 3">Totalidad de la renta</span>
                                    </td>
                                    <td  class="text-center">
                                        <a class="btn btn-success btn-xs" data-placement="top"  title="Editar"  @click="show_modal_modificar_beneficiario(it)"><i class="fa fa-edit"></i></a>
                                        <a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar"  @click="eliminar_beneficiario(it)"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9" style="text-align: center">
                                    <button @click="show_modal_nuevo_beneficiario" type="button" class="btn btn-success">
                                        <i class="icon fa fa-plus"></i><span> Agregar beneficiario</span>
                                    </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Final Panel Beneficiarios -->

            <!-- Inicio Panel Responsable IR -->
            <div class="panel">
                <div class="panel-heading">
                    Responsable IR
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table :id="'table-responsable-ir-'+ index" tabindex="0" class="table table-bordered table-hover no-mb" style="font-size: 11px; margin-top: 10px">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Número Documento</th>
                                    <th width="60%" class="text-center">Nombre</th>
                                    <th class="text-center">Porcentaje</th>
                                    <th width="8%" class="text-center">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item, it) in contrato.condicion_economica.responsables_ir" :key="it">
                                    <td class="text-center">{{ it + 1 }}</td>
                                    <td class="text-left">{{ item.num_documento }}</td>
                                    <td class="text-left">{{ item.nombres }}</td>
                                    <td class="text-right">{{ item.porcentaje }}</td>
                                    <td  class="text-center">
                                        <a class="btn btn-success btn-xs" data-placement="top"  title="Editar"  @click="show_modal_modificar_responsable_ir(it)"><i class="fa fa-edit"></i></a>
                                        <a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar"  @click="eliminar_responsable_ir(it)"><i class="fa fa-trash"></i></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="9" style="text-align: center">
                                    <button @click="show_modal_nuevo_responsable_ir" type="button" class="btn btn-success">
                                        <i class="icon fa fa-plus"></i><span> Agregar Responsable IR</span>
                                    </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Final Panel Responsable IR -->

            <!-- Inicio Panel Fecha Suscripcion -->
            <div class="panel">
                <div class="panel-heading">
                    Fecha de Suscripción del Contrato
                </div>
                <div class="panel-body">
                    
                    <div class="col-md-2">
                        <div class="form-group">
                            <input ref="fecha_suscripcion" v-model="contrato.condicion_economica.fecha_suscripcion"  type="text" class="form-control text-center flatpickr-nuevo">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Final Panel Fecha Suscripcion -->


            <div class="row">
                <!-- Inicio Panel Anexos -->
                <div class="panel">
                    <div class="panel-heading">
                        Observaciones
                    </div>
                    <div class="panel-body">
                        
                        <div class="col-md-12">
                          <textarea class="form-control" v-model="contrato.observaciones" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <!-- Final Panel Anexos -->
              </div>

              <div class="row">
                <!-- Inicio Panel Anexos -->
                <div class="panel">
                    <div class="panel-heading">
                        Anexos
                    </div>
                    <div class="panel-body">
                        
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Partida Registral del inmueble actualizada:</div>
                        <input type="file" :ref="'archivo_partida_registral_'+index" :name="'archivo_partida_registral_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Recibo de agua:</div>
                        <input type="file" :ref="'archivo_recibo_agua_'+index" :name="'archivo_recibo_agua_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Recibo de luz:</div>
                        <input type="file" :ref="'archivo_recibo_luz_'+index" :name="'archivo_recibo_luz_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">DNI del propietario:</div>
                        <input type="file" :ref="'archivo_dni_propietario_'+index" :name="'archivo_dni_propietario_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Pago de Recibo de agua:</div>
                        <input type="file" :ref="'archivo_pago_recibo_agua_'+index" :name="'archivo_pago_recibo_agua_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Pago de Recibo de Luz:</div>
                        <input type="file" :ref="'archivo_pago_recibo_luz_'+index" :name="'archivo_pago_recibo_luz_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Vigencia de poder (Si es empresa):</div>
                        <input type="file" :ref="'archivo_vigencia_poder_'+index" :name="'archivo_vigencia_poder_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">DNI de representante legal (Si es empresa):</div>
                        <input type="file" :ref="'archivo_dni_representante_legal_'+index" :name="'archivo_dni_representante_legal_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">HR del inmueble:</div>
                        <input type="file" :ref="'archivo_hr_inmueble_'+index" :name="'archivo_hr_inmueble_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">PU del inmueble:</div>
                        <input type="file" :ref="'archivo_pu_inmueble_'+index" :name="'archivo_pu_inmueble_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>                 

                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Pago de Impuesto Predial:</div>
                        <input type="file" :ref="'archivo_pago_impuesto_predial_'+index" :name="'archivo_pago_impuesto_predial_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>    
                    <div class="col-xs-12 col-md-4 col-lg-4">
                      <div class="form-group">
                        <div class="control-label">Pago de Arbitrios:</div>
                        <input type="file" :ref="'archivo_pago_arbitrios_'+index" :name="'archivo_pago_arbitrios_'+index" required accept=".jpg, .jpeg, .png, .pdf">
                      </div>
                    </div>    

                    </div>
                </div>
                <!-- Final Panel Anexos -->
              
              </div>

              <div class="row">
                <!-- Inicio Panel Anexos -->
                <div class="panel">
                    <div class="panel-heading">
                        Otros Anexos
                    </div>
                    <div class="panel-body">
                      <button type="button" class="btn btn-info btn-sm" @click="show_modal_anexo">
                        <i class="icon fa fa-plus"></i>
                        Agregar otros anexos
                      </button>

                      <div class="col-md-12" :id="'body_otros_anexos_'+ index" tabindex="0" >
                        <br>
                      </div>

                      <div class="col-md-4" v-for="(item, it) in contrato.otros_anexos" :key="it">
                        <div class="form-group">
                            <div class="control-label">{{ item.nombre  }}: </div>
                            <div style="margin-top:10px;">
                              <input :ref="'otro_anexo_' + it" @change="cargar_otros_anexos(it)" :name="'miarchivo_'+index+'[]'" class="col-md-11" type="file" accept=".jpg, .jpeg, .png, .pdf" style="padding: 0px 0px;">
                              <button type="button" class="btn btn-xs btn-danger col-md-1" style="width: 22px;" @click="borrar_anexo(it)"><i class="fa fa-trash-o"></i></button>
                            </div>
                        </div>
                      </div>
                    </div>
                </div>
                <!-- Final Panel Anexos -->
              </div>

     
        </div>
        
    </div>
    <!-- Final Panel Ficha de Condiciones -->

    <component-modal-adelantos :index="index" :adelantos="contrato.condicion_economica.adelantos"></component-modal-adelantos>
    <component-modal-incrementos :index="index" :incrementos="contrato.condicion_economica.incrementos"></component-modal-incrementos>
    <component-modal-inflaciones :index="index" :inflaciones="contrato.condicion_economica.inflaciones"></component-modal-inflaciones>
    <component-modal-cuotas-extraordinarias :index="index" :cuotas_extraordinarias="contrato.condicion_economica.cuotas_extraordinarias"></component-modal-cuotas-extraordinarias>
    
    <component-modal-beneficiarios :propietarios="propietarios" :index="index" :beneficiarios="contrato.condicion_economica.beneficiarios"></component-modal-beneficiarios>
    <component-modal-beneficiario-registro :propietarios="propietarios" :index="index" :beneficiarios="contrato.condicion_economica.beneficiarios"></component-modal-beneficiario-registro>

    <component-modal-responsables-ir :propietarios="propietarios" :index="index" :responsables_ir="contrato.condicion_economica.responsables_ir"></component-modal-responsables-ir>
    <component-modal-responsable-ir-registro :propietarios="propietarios" :index="index" :responsables_ir="contrato.condicion_economica.responsables_ir"></component-modal-responsable-ir-registro>


    <component-modal-anexo :index="index" :otros_anexos="contrato.otros_anexos"></component-modal-anexo>
    <component-modal-anexo-registro :index="index" :otros_anexos="contrato.otros_anexos"></component-modal-anexo-registro>

    </section>
    `,
  components: {
    "v-select": VueSelect.VueSelect,
  },
  props: ["contrato", "index", "arrendamiento", "propietarios"],
  data() {
    return {
      departamentos: [],
      provincias: [],
      distritos: [],
      compromiso_pago_servicios: [],
      compromiso_pago_arbitrios: [],
      monedas: [],
      pago_renta: [],
      tipo_venta: [],
      igv_venta: [],
      tipo_adelanto: [],
      tipo_impuesto_renta: [],
      carta_instruccion: [],
      periodo_gracia: [],
      tipo_periodo: [],
      tipo_incremento: [],
      tipo_inflacion: [],
      tipo_cuota_extraordinaria: [],

      departamento_val: null,
      provincia_val: null,
      distrito_val: null,
      compromiso_pago_agua_val: null,
      compromiso_pago_luz_val: null,
      compromiso_pago_arbitrio_val: null,
      moneda_val: null,
      pago_renta_val: null,
      tipo_venta_val: null,
      igv_renta_val: null,
      tipo_adelanto_val: null,
      tipo_impuesto_renta_val: null,
      carta_instruccion_val: null,
      periodo_gracia_val: null,
      tipo_periodo_val: { id: "1", nombre: "Periodo Definido" },
      cant_meses_contrato: "",
      fecha_inicio_val: "",
      fecha_fin_val: "",
      tipo_incremento_val: null,
      tipo_inflacion_val: null,
      tipo_cuota_extraordinaria_val: null,

      label_form: {
        monto_o_porcentaje_luz: "",
        monto_o_porcentaje_agua: "",
      },
    };
  },
  created() {
    this.obtener_departamentos();
    this.obtener_compromiso_pago_servicios();
    this.obtener_compromiso_pago_arbitrios();
    this.obtener_monedas();
    this.obtener_pago_renta();
    this.obtener_tipo_venta();
    this.obtener_igv_renta();
    this.obtener_tipo_adelanto();
    this.obtener_impuesto_renta();
    this.obtener_carta_instruccion();
    this.obtener_periodo_gracia();
    this.obtener_tipo_periodo();
    this.obtener_tipo_incremento();
    this.obtener_tipo_inflacion();
    this.obtener_tipo_cuota_extraordinaria();
    this.obtener_autodetraccion();
  },
  mounted() {
    this.inicializar_funciones();
    flatpickr(".flatpickr-nuevo", {
      dateFormat: "d-m-Y",
      locale: "es",
    });
  },
  methods: {
    agregar_contrato() {
      fetch("./vue/contrato-arrendamiento/data/contrato.php")
        .then((response) => response.json())
        .then((data) => {
          this.$store.dispatch("contratos/ActionAgregarContrato", data);
          alertify.success("Se ha agregado una nueva ficha de contrato", 5);
        })
        .catch((error) => {
          console.error(error);
        });
    },
    eliminar_contrato(index) {
      this.$store.dispatch("contratos/ActionEliminarContrato", index);
      alertify.success("Se ha eliminado la ficha de contrato seleccionada", 5);
    },
    inicializar_funciones,

    obtener_departamentos,
    obtener_provincias,
    obtener_distritos,
    obtener_compromiso_pago_servicios,
    obtener_compromiso_pago_arbitrios,
    obtener_monedas,
    obtener_pago_renta,
    obtener_tipo_venta,
    obtener_igv_renta,
    obtener_tipo_adelanto,
    obtener_impuesto_renta,
    obtener_carta_instruccion,
    obtener_periodo_gracia,
    obtener_tipo_periodo,
    obtener_tipo_incremento,
    obtener_tipo_inflacion,
    obtener_tipo_cuota_extraordinaria,
    obtener_autodetraccion,
    show_modal_modificar_adelantos,
    calcular_monto_segun_impuesto,
    calcular_fecha_fin_vigencia,
    calcular_vigencia_anios_y_meses,
    calcular_meses,
    //suministros
    agregar_nuevo_suministro_agua,
    eliminar_suministro_agua,

    agregar_nuevo_suministro_luz,
    eliminar_suministro_luz,

    show_modal_adelantos,
    //incrementos
    show_modal_nuevo_incremento,
    show_modal_modificar_incremento,
    eliminar_incremento,
    //inflaciones
    show_modal_nueva_inflacion,
    show_modal_modificar_inflacion,
    eliminar_inflacion,
    //cuotas extraordinarias
    show_modal_nueva_cuota_extraordinaria,
    show_modal_modificar_cuota_extraordinaria,
    eliminar_cuota_extraordinaria,
    //beneficario
    show_modal_nuevo_beneficiario,
    show_modal_modificar_beneficiario,
    eliminar_beneficiario,
    //responsable ir
    show_modal_nuevo_responsable_ir,
    show_modal_modificar_responsable_ir,
    eliminar_responsable_ir,

    show_modal_anexo,
    borrar_anexo,
    cargar_otros_anexos,
  },
  computed: {
    contratos() {
      return this.$store.state.contratos.contratos;
    },
  },
  watch: {
    departamento_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.departamento_id = "";
        this.contrato.inmuebles.provincia_id = "";
        this.contrato.inmuebles.distrito_id = "";
        this.contrato.inmuebles.ubigeo_id = "";
        this.provincias = [];
        this.distritos = [];
        this.provincia_val = null;
        this.distrito_val = null;
        return false;
      }
      this.contrato.inmuebles.departamento_id = newValue.id;
      this.obtener_provincias();
      this.contrato.inmuebles.provincia_id = "";
      this.contrato.inmuebles.distrito_id = "";
      this.contrato.inmuebles.ubigeo_id = "";
      this.$refs.provincia.focus();
    },
    provincia_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.provincia_id = "";
        this.contrato.inmuebles.distrito_id = "";
        this.contrato.inmuebles.ubigeo_id = "";
        this.distritos = [];
        this.distrito_val = null;
        return false;
      }

      this.contrato.inmuebles.provincia_id = newValue.id;
      this.obtener_distritos();
      this.contrato.inmuebles.distrito_id = "";
      this.contrato.inmuebles.ubigeo_id = "";
      this.$refs.distrito.focus();
    },
    distrito_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.ubigeo_id = "";
        return false;
      }
      this.contrato.inmuebles.distrito_id = newValue.id;
      this.contrato.inmuebles.ubigeo_id =
        this.contrato.inmuebles.departamento_id +
        this.contrato.inmuebles.provincia_id +
        this.contrato.inmuebles.distrito_id;

      this.$refs.ubicacion.focus();
    },

    compromiso_pago_agua_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.tipo_compromiso_pago_agua = "";
        this.contrato.inmuebles.monto_o_porcentaje_agua = "";
        return false;
      }
      this.contrato.inmuebles.tipo_compromiso_pago_agua = newValue.id;
      if (
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 1 ||
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 2 ||
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 6 ||
        this.contrato.inmuebles.tipo_compromiso_pago_agua == 7
      ) {
        if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 1) {
          this.label_form.monto_o_porcentaje_agua = "(%) del recibo de agua";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 2) {
          this.label_form.monto_o_porcentaje_agua =
            "Monto fijo del servicio de agua";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 6) {
          this.label_form.monto_o_porcentaje_agua =
            "Monto base del servicio de agua";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_agua == 7) {
          this.label_form.monto_o_porcentaje_agua =
            "Monto a facturar del servicio de agua";
        }
        let me = this;
        setTimeout(function () {
          $(me.$refs.monto_o_porcentaje_agua).on({
            focus: function (event) {
              $(event.target).select();
            },
            blur: function (event) {
              var tipo_compromiso_pago_agua =
                me.contrato.inmuebles.tipo_compromiso_pago_agua;
              if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                if (parseInt(tipo_compromiso_pago_agua) != 1) {
                  $(event.target).val(
                    parseFloat(
                      $(event.target).val().replace(/\,/g, "")
                    ).toFixed(2)
                  );
                  $(event.target).val(function (index, value) {
                    var new_value = value
                      .replace(/\D/g, "")
                      .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                      .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    me.contrato.inmuebles.monto_o_porcentaje_agua = new_value;
                    return new_value;
                  });
                }
              } else {
                if (parseInt(tipo_compromiso_pago_agua) != 1) {
                  me.contrato.inmuebles.monto_o_porcentaje_agua = "0.00";
                  $(event.target).val("0.00");
                } else {
                  me.contrato.inmuebles.monto_o_porcentaje_agua = "0";
                  $(event.target).val("0");
                }
              }
            },
          });
          $(me.$refs.monto_o_porcentaje_agua).unmask();
          if (me.contrato.inmuebles.tipo_compromiso_pago_agua == 1) {
            $(me.$refs.monto_o_porcentaje_agua).mask("00");
          }
          $(me.$refs.monto_o_porcentaje_agua).focus();
        }, 100);
      } else {
        this.contrato.inmuebles.monto_o_porcentaje_agua = "";
        this.label_form.monto_o_porcentaje_agua = "";
      }
    },
    compromiso_pago_luz_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.tipo_compromiso_pago_luz = "";
        this.contrato.inmuebles.monto_o_porcentaje_luz = "";
        return false;
      }
      this.contrato.inmuebles.tipo_compromiso_pago_luz = newValue.id;
      if (
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 1 ||
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 2 ||
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 6 ||
        this.contrato.inmuebles.tipo_compromiso_pago_luz == 7
      ) {
        if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 1) {
          this.label_form.monto_o_porcentaje_luz = "(%) del recibo de luz";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 2) {
          this.label_form.monto_o_porcentaje_luz =
            "Monto fijo del servicio de luz";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 6) {
          this.label_form.monto_o_porcentaje_luz =
            "Monto base del servicio de luz";
        } else if (this.contrato.inmuebles.tipo_compromiso_pago_luz == 7) {
          this.label_form.monto_o_porcentaje_luz =
            "Monto a facturar del servicio de luz";
        }
        let me = this;
        setTimeout(function () {
          $(me.$refs.monto_o_porcentaje_luz).on({
            focus: function (event) {
              $(event.target).select();
            },
            blur: function (event) {
              var tipo_compromiso_pago_luz =
                me.contrato.inmuebles.tipo_compromiso_pago_luz;
              if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                if (parseInt(tipo_compromiso_pago_luz) != 1) {
                  $(event.target).val(
                    parseFloat(
                      $(event.target).val().replace(/\,/g, "")
                    ).toFixed(2)
                  );
                  $(event.target).val(function (index, value) {
                    var new_value = value
                      .replace(/\D/g, "")
                      .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                      .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                    me.contrato.inmuebles.monto_o_porcentaje_luz = new_value;
                    return new_value;
                  });
                }
              } else {
                if (parseInt(tipo_compromiso_pago_luz) != 1) {
                  me.contrato.inmuebles.monto_o_porcentaje_luz = "0.00";
                  $(event.target).val("0.00");
                } else {
                  me.contrato.inmuebles.monto_o_porcentaje_luz = "0";
                  $(event.target).val("0");
                }
              }
            },
          });
          $(me.$refs.monto_o_porcentaje_luz).unmask();
          if (me.contrato.inmuebles.tipo_compromiso_pago_luz == 1) {
            $(me.$refs.monto_o_porcentaje_luz).mask("00");
          }
          $(me.$refs.monto_o_porcentaje_luz).focus();
        }, 100);
      } else {
        this.contrato.inmuebles.monto_o_porcentaje_luz = "";
        this.label_form.monto_o_porcentaje_luz = "";
      }
    },
    compromiso_pago_arbitrio_val(newValue) {
      if (newValue == null) {
        this.contrato.inmuebles.tipo_compromiso_pago_arbitrios = "";
        this.contrato.inmuebles.porcentaje_pago_arbitrios = "";
        return false;
      }
      this.contrato.inmuebles.tipo_compromiso_pago_arbitrios = newValue.id;

      let me = this;
      setTimeout(function () {
        if (me.contrato.inmuebles.tipo_compromiso_pago_arbitrios == 1) {
          $(me.$refs.porcentaje_pago_arbitrios).mask("00");
        }
        $(me.$refs.porcentaje_pago_arbitrios).focus();
      }, 100);
    },
    moneda_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_moneda_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_moneda_id = newValue.id;

      let me = this;
      setTimeout(function () {
        $(me.$refs.monto_renta).focus();
      }, 100);
      this.calcular_monto_segun_impuesto();
    },
    pago_renta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.pago_renta_id = "";
        return false;
      }
      this.contrato.condicion_economica.pago_renta_id = newValue.id;

      let me = this;
      setTimeout(function () {
        if (newValue.id == 2) {
          $(me.$refs.monto_renta).focus();

          $(me.$refs.cuota_variable).on({
            focus: function (event) {
              $(event.target).select();
            },
            blur: function (event) {
              if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
                $(event.target).val(
                  parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(
                    2
                  )
                );
                $(event.target).val(function (index, value) {
                  var new_value = value
                    .replace(/\D/g, "")
                    .replace(/([0-9])([0-9]{2})$/, "$1.$2")
                    .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

                  me.contrato.inmuebles.cuota_variable = new_value;
                  return new_value;
                });
              } else {
                me.contrato.inmuebles.cuota_variable = "0";
                $(event.target).val("0");
              }
            },
          });
        }
      }, 100);
    },
    tipo_venta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_venta_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_venta_id = newValue.id;
    },
    igv_renta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.afectacion_igv_id = "";
        return false;
      }
      this.contrato.condicion_economica.afectacion_igv_id = newValue.id;
    },
    tipo_adelanto_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_adelanto_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_adelanto_id = newValue.id;

      if (newValue.id == 1) {
        this.show_modal_adelantos();
      } else {
        this.contrato.condicion_economica.adelantos = [];
        this.$refs.impuesto_a_la_renta_id.focus();
      }
    },
    tipo_impuesto_renta_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.impuesto_a_la_renta_id = "";
        return false;
      }
      this.contrato.condicion_economica.impuesto_a_la_renta_id = newValue.id;
      this.calcular_monto_segun_impuesto();
    },
    carta_instruccion_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.carta_de_instruccion_id = "";
        return false;
      }
      this.contrato.condicion_economica.carta_de_instruccion_id = newValue.id;
      this.calcular_monto_segun_impuesto();
    },
    periodo_gracia_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.periodo_gracia_id = "";
        return false;
      }
      this.contrato.condicion_economica.periodo_gracia_id = newValue.id;
      let me = this;
      setTimeout(function () {
        if (me.contrato.condicion_economica.periodo_gracia_id == 1) {
          $(me.$refs.periodo_gracia_numero).mask("000");
          $(me.$refs.periodo_gracia_numero).focus();
        }
      }, 100);
    },
    tipo_periodo_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.plazo_id = "";
        return false;
      }
      this.contrato.condicion_economica.plazo_id = newValue.id;
      let me = this;
      setTimeout(function () {
        if (me.contrato.condicion_economica.plazo_id == 1) {
          $(me.$refs.cant_meses_contrato).focus();
        } else if (me.contrato.condicion_economica.plazo_id == 2) {
          $(me.$refs.fecha_inicio).focus();
          me.cant_meses_contrato = "";
          me.fecha_fin_val = "";
        }
      }, 200);
    },
    cant_meses_contrato(newValue) {
      this.contrato.condicion_economica.cant_meses_contrato = newValue;
      this.calcular_vigencia_anios_y_meses();
      this.calcular_fecha_fin_vigencia();
    },
    fecha_inicio_val(newValue) {
      this.calcular_fecha_fin_vigencia();
    },
    fecha_fin_val(newValue) {
      this.calcular_meses();
    },
    tipo_incremento_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_incremento_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_incremento_id = newValue.id;
      if (newValue.id == 1) {
        this.show_modal_nuevo_incremento();
      } else {
        this.contrato.condicion_economica.incrementos = [];
      }
    },
    tipo_inflacion_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_inflacion_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_inflacion_id = newValue.id;
      if (newValue.id == 1) {
        this.show_modal_nueva_inflacion();
      } else {
        this.contrato.condicion_economica.inflaciones = [];
      }
    },
    tipo_cuota_extraordinaria_val(newValue) {
      if (newValue == null) {
        this.contrato.condicion_economica.tipo_cuota_extraordinaria_id = "";
        return false;
      }
      this.contrato.condicion_economica.tipo_cuota_extraordinaria_id =
        newValue.id;
      if (newValue.id == 1) {
        this.show_modal_nueva_cuota_extraordinaria();
      } else {
        this.contrato.condicion_economica.cuotas_extraordinarias = [];
      }
    },
  },
};

Vue.component("component-contrato", contrato);

function show_modal_modificar_adelantos() {
  EventBus.$emit("abrir_modal_adelantos", {});
  $("#component_modal_adelantos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_adelantos_" + this.index).focus();
}

function inicializar_funciones() {
  let me = this;

  setTimeout(function () {
    $(me.$refs.monto_renta).on({
      focus: function (event) {
        $(event.target).select();
      },
      blur: function (event) {
        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
          $(event.target).val(
            parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
          );
          $(event.target).val(function (index, value) {
            var new_value = value
              .replace(/\D/g, "")
              .replace(/([0-9])([0-9]{2})$/, "$1.$2")
              .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

            me.contrato.condicion_economica.monto_renta = new_value;
            return new_value;
          });
        } else {
          me.contrato.condicion_economica.monto_renta = "0.00";
          $(event.target).val("0.00");
        }
      },
    });

    $(me.$refs.garantia_monto).on({
      focus: function (event) {
        $(event.target).select();
      },
      blur: function (event) {
        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
          $(event.target).val(
            parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
          );
          $(event.target).val(function (index, value) {
            var new_value = value
              .replace(/\D/g, "")
              .replace(/([0-9])([0-9]{2})$/, "$1.$2")
              .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");

            me.contrato.condicion_economica.garantia_monto = new_value;
            return new_value;
          });
        } else {
          me.contrato.condicion_economica.garantia_monto = "0.00";
          $(event.target).val("0.00");
        }
      },
    });

    $(me.$refs.area_arrendada).on({
      focus: function (event) {
        $(event.target).select();
      },
      blur: function (event) {
        if (parseFloat($(event.target).val().replace(/\,/g, "")) > 0) {
          $(event.target).val(
            parseFloat($(event.target).val().replace(/\,/g, "")).toFixed(2)
          );
          $(event.target).val(function (index, value) {
            var new_value = value
              .replace(/\D/g, "")
              .replace(/([0-9])([0-9]{2})$/, "$1.$2")
              .replace(/\B(?=(\d{3})+(?!\d)\.?)/g, ",");
            me.contrato.inmuebles.area_arrendada = new_value;
            return new_value;
          });
        } else {
          me.contrato.inmuebles.area_arrendada = "0.00";
          $(event.target).val("0.00");
        }
      },
    });
  }, 3000);

  setTimeout(function () {
    $(me.$refs.cant_meses_contrato).mask("000");
  }, 5000);
}

function obtener_departamentos() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  let data = {
    action: "obtener_departartamentos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    me.departamentos = [];
    console.log("departamentos desde contrato");
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.departamentos.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_provincias() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.provincias = [];
  this.provincia_val = null;
  let data = {
    action: "obtener_provincias_segun_departamento",
    departamento_id: this.contrato.inmuebles.departamento_id,
  };
  if (data.departamento_id.length == 0) {
    return false;
  }
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.provincias.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_distritos() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.distritos = [];
  this.distrito_val = null;
  let data = {
    action: "obtener_distritos_segun_provincia",
    departamento_id: this.contrato.inmuebles.departamento_id,
    provincia_id: this.contrato.inmuebles.provincia_id,
  };
  if (data.provincia_id.length == 0) {
    return false;
  }
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.distritos.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_compromiso_pago_servicios() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.compromiso_pago_servicios = [];
  let data = {
    action: "obtener_tipo_compromiso_pago",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.compromiso_pago_servicios.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_compromiso_pago_arbitrios() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.compromiso_pago_arbitrios = [];
  let data = {
    action: "obtener_tipo_compromiso_pago_arbitrio",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.compromiso_pago_arbitrios.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_monedas() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.monedas = [];
  let data = {
    action: "obtener_moneda_de_contrato",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.monedas.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_pago_renta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.pago_renta = [];
  let data = {
    action: "obtener_tipo_pago_renta",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        if (element.id == 1) {
          me.pago_renta_val = {
            id: element.id,
            text: element.nombre,
          };
        }
        me.pago_renta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_venta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_venta = [];
  let data = {
    action: "obtener_tipo_venta",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_venta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_igv_renta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.igv_venta = [];
  let data = {
    action: "obtener_tipo_afectacion_igv",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.igv_venta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_adelanto() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_adelanto = [];
  let data = {
    action: "obtener_tipo_adelantos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_adelanto.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_impuesto_renta() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_impuesto_renta = [];
  let data = {
    action: "obtener_tipo_impuesto_a_la_renta",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_impuesto_renta.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_carta_instruccion() {
  this.carta_instruccion = [
    { id: 1, text: "Si" },
    { id: 2, text: "No" },
  ];
}
function obtener_autodetraccion() {
  this.tipo_autodetraccion = [
    { id: 1, text: "Si (CONCAR)" },
    { id: 2, text: "No (SISPAC)" },
  ];
}
function calcular_monto_segun_impuesto() {
  let me = this;
  let url = "sys/router/contratos/index.php";

  let data = {
    action: "calcular_monto_segun_impuesto",
    tipo_moneda_id: this.contrato.condicion_economica.tipo_moneda_id,
    monto_renta: this.contrato.condicion_economica.monto_renta,
    impuesto_a_la_renta_id:
      this.contrato.condicion_economica.impuesto_a_la_renta_id,
    carta_de_instruccion_id:
      this.contrato.condicion_economica.carta_de_instruccion_id,
  };

  if (
    data.tipo_moneda_id.length == 0 ||
    data.monto_renta.length == 0 ||
    data.impuesto_a_la_renta_id.length == 0 ||
    data.impuesto_a_la_renta_id == 4 ||
    data.impuesto_a_la_renta_id == 5 ||
    data.carta_de_instruccion_id.length == 0
  ) {
    me.contrato.condicion_economica.view_ir_detalle = false;
    me.contrato.condicion_economica.ir_detalle.renta_neta = "";
    me.contrato.condicion_economica.ir_detalle.renta_bruta = "";
    me.contrato.condicion_economica.ir_detalle.impuesto_a_la_renta = "";
    me.contrato.condicion_economica.ir_detalle.detalle = "";
    return false;
  }

  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      me.contrato.condicion_economica.view_ir_detalle = true;
      const renta_neta = response.data.result.renta_neta;
      const renta_bruta = response.data.result.renta_bruta;
      const impuesto_a_la_renta = response.data.result.impuesto_a_la_renta;
      const detalle = response.data.result.detalle;

      me.contrato.condicion_economica.ir_detalle.renta_neta = renta_neta;
      me.contrato.condicion_economica.ir_detalle.renta_bruta = renta_bruta;
      me.contrato.condicion_economica.ir_detalle.impuesto_a_la_renta =
        impuesto_a_la_renta;
      me.contrato.condicion_economica.ir_detalle.detalle = detalle;
    } else {
      me.contrato.condicion_economica.view_ir_detalle = false;
      me.contrato.condicion_economica.ir_detalle.renta_neta = "";
      me.contrato.condicion_economica.ir_detalle.renta_bruta = "";
      me.contrato.condicion_economica.ir_detalle.impuesto_a_la_renta = "";
      me.contrato.condicion_economica.ir_detalle.detalle = "";
    }
  });
}

function obtener_periodo_gracia() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.periodo_gracia = [];
  let data = {
    action: "obtener_tipo_periodo_de_gracia",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        if (element.id == 1) {
          me.plazo_id = { id: element.id, text: element.nombre };
        }
        me.periodo_gracia.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_periodo() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_periodo = [];
  let data = {
    action: "obtener_tipo_periodo",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        if (element.id == 1) {
          me.tipo_periodo_val = { id: element.id, text: element.nombre };
        }
        me.tipo_periodo.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function calcular_fecha_fin_vigencia() {
  const fecha_inicio = flatpickr.parseDate(this.fecha_inicio_val, "d-m-Y");
  const meses = parseInt(this.contrato.condicion_economica.cant_meses_contrato);
  this.contrato.condicion_economica.fecha_inicio = this.fecha_inicio_val;

  if (!isNaN(meses) && fecha_inicio != undefined) {
    const end = new Date(
      fecha_inicio.getFullYear(),
      fecha_inicio.getMonth() + meses,
      fecha_inicio.getDate()
    );
    this.fecha_fin_val = flatpickr.formatDate(end, "d-m-Y");
    this.contrato.condicion_economica.fecha_fin = this.fecha_fin_val;
  } else {
    this.fecha_fin_val = "";
    this.contrato.condicion_economica.fecha_fin = this.fecha_fin_val;
  }
}

function calcular_vigencia_anios_y_meses() {
  let meses = this.contrato.condicion_economica.cant_meses_contrato;
  if (meses == 0 || meses == "") {
    this.contrato.condicion_economica.vigencia_contrato_lectura =
      "0 años y 0 meses";
  } else if (meses < 12) {
    this.contrato.condicion_economica.vigencia_contrato_lectura =
      meses + " meses";
  } else {
    var anio = parseInt(meses / 12);
    var meses_restantes = meses % 12;

    if (anio == 0) {
      anio = "";
    } else if (anio == 1) {
      anio = anio + " año";
    } else if (anio > 1) {
      anio = anio + " años";
    }

    if (meses_restantes == 0) {
      meses_restantes = "";
    } else if (meses_restantes == 1) {
      meses_restantes = " y " + meses_restantes + " mes";
    } else if (meses_restantes > 1) {
      meses_restantes = " y " + meses_restantes + " meses";
    }
    this.contrato.condicion_economica.vigencia_contrato_lectura =
      anio + meses_restantes;
  }
}

function calcular_meses() {
  const fecha_inicio = flatpickr.parseDate(this.fecha_inicio_val, "d-m-Y");
  const fecha_fin = flatpickr.parseDate(this.fecha_fin_val, "d-m-Y");
  if (fecha_inicio != "" && fecha_fin != "") {
    const start = flatpickr.parseDate(fecha_inicio, "d-m-Y");
    const end = flatpickr.parseDate(fecha_fin, "d-m-Y");
    const months =
      end.getMonth() -
      start.getMonth() +
      12 * (end.getFullYear() - start.getFullYear());
    this.contrato.condicion_economica.cant_meses_contrato = months;
    this.calcular_vigencia_anios_y_meses();
  }
}

function obtener_tipo_incremento() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_incremento = [];
  let data = {
    action: "obtener_tipo_incrementos",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_incremento.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_inflacion() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_inflacion = [];
  let data = {
    action: "obtener_tipo_inflacion",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_inflacion.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function obtener_tipo_cuota_extraordinaria() {
  let me = this;
  let url = "sys/router/contratos/index.php";
  this.tipo_cuota_extraordinaria = [];
  let data = {
    action: "obtener_tipo_cuota_extraordinaria",
  };
  axios({
    method: "post",
    url: url,
    data: data,
  }).then(function (response) {
    if (response.data.status == 200) {
      response.data.result.forEach((element) => {
        me.tipo_cuota_extraordinaria.push({
          id: element.id,
          text: element.nombre,
        });
      });
    }
  });
}

function agregar_nuevo_suministro_agua() {
  this.contrato.inmuebles.inmueble_servicio_agua.push({
    contrato_id: "",
    inmueble_id: "",
    tipo_servicio_id: "",
    nro_suministro: "",
    tipo_compromiso_pago_id: "",
    monto_o_porcentaje: "",
    tipo_documento_beneficiario: 1,
    nombre_beneficiario: "",
    nro_documento_beneficiario: "",
    nro_cuenta_soles: "",
  });
}

function eliminar_suministro_agua(index) {
  this.contrato.inmuebles.inmueble_servicio_agua.splice(index, 1);
}

function agregar_nuevo_suministro_luz() {
  this.contrato.inmuebles.inmueble_servicio_luz.push({
    contrato_id: "",
    inmueble_id: "",
    tipo_servicio_id: "",
    nro_suministro: "",
    tipo_compromiso_pago_id: "",
    monto_o_porcentaje: "",
    tipo_documento_beneficiario: 1,
    nombre_beneficiario: "",
    nro_documento_beneficiario: "",
    nro_cuenta_soles: "",
  });
}

function eliminar_suministro_luz(index) {
  this.contrato.inmuebles.inmueble_servicio_luz.splice(index, 1);
}

function show_modal_adelantos() {
  let data = {};
  EventBus.$emit("abrir_modal_adelantos", data);
  $("#component_modal_adelantos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_adelantos_" + this.index).focus();
}

function show_modal_nuevo_incremento() {
  let data = {
    title: "Registrar Incremento",
    action: "nuevo",
  };
  EventBus.$emit("nuevo_incremento", data);

  $("#component_modal_incrementos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_incremento_" + this.index).focus();
}

function show_modal_modificar_incremento(index_incremento) {
  let data = {
    title: "Modificar Incremento",
    index: index_incremento,
    action: "modificar",
  };
  EventBus.$emit("editar_incremento", data);

  $("#component_modal_incrementos_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_incremento_" + this.index).focus();
}

function eliminar_incremento(index) {
  this.contrato.condicion_economica.incrementos.splice(index, 1);
  alertify.success("Se ha eliminado el incremento", 5);
}

function show_modal_nueva_inflacion() {
  let data = {
    title: "Nueva Inflación",
    action: "nuevo",
  };
  EventBus.$emit("nueva_inflacion", data);

  $("#component_modal_inflaciones_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_inflaciones_" + this.index).focus();
}

function show_modal_modificar_inflacion(index_inflacion) {
  let data = {
    title: "Modificar Inflación",
    index: index_inflacion,
    action: "modificar",
  };
  EventBus.$emit("editar_inflacion", data);

  $("#component_modal_inflaciones_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_inflaciones_" + this.index).focus();
}

function eliminar_inflacion(index) {
  this.contrato.condicion_economica.inflaciones.splice(index, 1);
  alertify.success("Se ha eliminado la inflación", 5);
}

function show_modal_nueva_cuota_extraordinaria() {
  let data = {
    title: "Nueva Cuota Extraordinaria",
    action: "nuevo",
  };
  EventBus.$emit("nueva_cuota_extraordinaria", data);
  $("#component_modal_cuotas_extraordinarias_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_cuotas_extraordinarias_" + this.index).focus();
}

function show_modal_modificar_cuota_extraordinaria(index_inflacion) {
  let data = {
    title: "Modificar Cuota Extraordinaria",
    index: index_inflacion,
    action: "modificar",
  };
  EventBus.$emit("editar_cuota_extraordinaria", data);
  $("#component_modal_cuotas_extraordinarias_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_cuotas_extraordinarias_" + this.index).focus();
}

function eliminar_cuota_extraordinaria(index) {
  this.contrato.condicion_economica.cuotas_extraordinarias.splice(index, 1);
  alertify.success("Se ha eliminado la cuota extraordinaria", 5);
}

function show_modal_nuevo_beneficiario() {
  EventBus.$emit("abrir_modal_beneficiarios", {});
  $("#component_modal_beneficiarios_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_beneficiarios_" + this.index).focus();
}

function show_modal_modificar_beneficiario(index_beneficiario) {
  const beneficiario =
    this.contrato.condicion_economica.beneficiarios[index_beneficiario];

  let data = {
    title: "Modificar Beneficiario",
    action: "registrar",
    beneficiario: {
      id: beneficiario.id,
      contrato_id: "",
      tipo_persona_id: beneficiario.tipo_persona_id,
      tipo_docu_identidad_id: beneficiario.tipo_docu_identidad_id,
      num_docu: beneficiario.num_docu,
      nombre: beneficiario.nombre,
      forma_pago_id: beneficiario.forma_pago_id,
      banco_id: beneficiario.banco_id,
      num_cuenta_bancaria: beneficiario.num_cuenta_bancaria,
      num_cuenta_cci: beneficiario.num_cuenta_cci,
      tipo_monto_id: beneficiario.tipo_monto_id,
      monto: beneficiario.monto,
    },
  };

  EventBus.$emit("modificar-beneficiario", data);
  $("#component_modal_beneficiario_registro_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_beneficiario_registro_" + this.index).focus();
}

function eliminar_beneficiario(index) {
  this.contrato.condicion_economica.beneficiarios.splice(index, 1);
  alertify.success("Se ha eliminado el beneficiario", 5);
}

function show_modal_nuevo_responsable_ir() {
  let data = {
    title: "Nuevo Responsable IR",
    action: "nuevo",
  };
  EventBus.$emit("nuevo-responsable-ir", data);
  $("#component_modal_responsables_ir_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_responsable_ir_" + this.index).focus();
}

function show_modal_modificar_responsable_ir(index_responsable_ir) {
  const responsable_ir =
    this.contrato.condicion_economica.responsables_ir[index_responsable_ir];

  let data = {
    title: "Modificar Responsable IR",
    action: "registrar",
    responsable_ir: {
      id: responsable_ir.id,
      contrato_id: responsable_ir.contrato_id,
      tipo_documento_id: responsable_ir.tipo_documento_id,
      num_documento: responsable_ir.num_documento,
      nombres: responsable_ir.nombres,
      estado_emisor: responsable_ir.estado_emisor,
      porcentaje: responsable_ir.porcentaje,
    },
  };

  EventBus.$emit("modificar-registro-responsable-ir", data);

  $("#component_modal_responsable_ir_registro_" + this.index).modal({
    backdrop: "static",
    keyboard: false,
  });
  $("#modal_body_responsable_ir_registro_" + this.index).focus();
}

function eliminar_responsable_ir(index) {
  this.contrato.condicion_economica.responsables_ir.splice(index, 1);
  alertify.success("Se ha eliminado el responsable IR", 5);
}

function show_modal_anexo() {
  EventBus.$emit("show-modal-anexos");
  $("#component-modal-anexo-" + this.index).modal("show");
  $("#modal_body_anexo_" + this.index).focus();
}

function borrar_anexo(index) {
  this.contrato.otros_anexos.splice(index, 1);
}

function cargar_otros_anexos(index) {
  var file = this.$refs["otro_anexo_" + index][0].files[0];
  this.contrato.otros_anexos[index].file_name = file.name.substring(
    0,
    file.name.lastIndexOf(".")
  );
  this.contrato.otros_anexos[index].file_size = file.size;
  this.contrato.otros_anexos[index].file_extension = file.name
    .split(".")
    .reverse()[0];
}
