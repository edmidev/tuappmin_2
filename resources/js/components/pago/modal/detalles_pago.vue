<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="modal-detalles-pago" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content" v-if="form.id && data">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles del pago</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <h6 class="text-center">{{ months[data.month - 1] }} {{ data.year }}</h6>

                        <p class="mb-1">
                            Total: $<strong><number-format-component :valor="data.total / data.cantidad_meses"></number-format-component></strong>
                        </p>

                        <p class="mb-1" v-if="data.valor_mora">
                            Mora: $<strong><number-format-component :valor="data.valor_mora"></number-format-component></strong>
                        </p>

                        <p class="mb-1">
                            Metodo pago: <strong>{{ metodos_pagos[data.metodo_pago - 1] }}</strong>
                        </p>

                        <p class="mb-1">
                            Estatus: <strong>{{ estatus_pago[data.estatus_pago - 1] }}</strong>
                        </p>

                        <p v-if="data.metodo_pago != 1" class="mb-1">
                            Ref. epayco: <strong>{{ data.x_ref_payco }}</strong>
                        </p>

                        <p v-if="data.x_cardnumber" class="mb-1">
                            Número de tarjeta: <strong>{{ data.x_cardnumber }}</strong>
                        </p>

                        <p class="mb-1">
                            Fecha de pago: <strong><dateformat-component :date="data.created_at"></dateformat-component></strong>
                        </p>

                        <a v-if="data.metodo_pago == 1 && data.comprobante" class="btn btn-success btn-sm mt-3"
                        :href="asset + 'storage/transferencias/' + data.comprobante" target="_blank">
                            Ver comprobante
                        </a>
                    </div>
                    <div class="modal-footer">
                        <button id="btn-close-modal-detalles-pago" type="button" class="btn btn-secondary" @click="_closeModal()">Cerrar</button>
                        <button type="button" class="btn btn-primary" @click="_aprobarPago()" :disabled="btnDisabled"
                        v-if="data.metodo_pago == 1 && data.comprobante && data.estatus_pago == 1">Aprobar pago</button>
                        <button type="button" class="btn btn-danger" @click="_rechazarPago()" :disabled="btnDisabled"
                        v-if="data.metodo_pago == 1 && data.comprobante && data.estatus_pago == 1">Rechazar pago</button>
                    </div>                  
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['attrib', 'asset'],
        data() {
            return {
                form: {},
                errors: {},
                btnDisabled: false,
                data: {},
                months: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                metodos_pagos: [
                    'Transferencia bancaria', 'Pago con tarjeta',
                    'Transacción PSE'
                ],
                estatus_pago: [
                    'En revisión', 'Rechazado',
                    'Aprobado'
                ]
            }
        },
        methods: {                        
            _getData() {
                axios
                    .get("pagos/detalles/" + this.form.id)
                    .then((response) => {
                        this.data = response.data.administracion_pago;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _updateList() {
                this.$emit('updateList2');
            },
            _getAttrib() {
                this.form = this.attrib;
                this._getData();     

                $("#modal-detalles-pago").modal('toggle');
            },
            _crear() {
                this.form = {};
                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal-detalles-pago").click();
                this.form = {};

                this._updateList();
            },
            _aprobarPago(){
                this._confirmAccion(null, "¿Quieres aprobar este pago?").then(t => {
                    if (t.value) {
                        axios
                            .post('pagos/aprobar', {
                                pago_month_id: this.data.id
                            })
                            .then(response => {
                                if (response.status == 201) {
                                    this._getData();
                                    this._showAlert('success', response.data);
                                }
                            })
                            .catch(error => {
                                console.log(error)
                                this.btnDisabled = false;
                            })
                    }
                })
            },
            _rechazarPago(){
                this._confirmAccion(null, "¿Quieres rechazar este pago?").then(t => {
                    if (t.value) {
                        axios
                            .post('pagos/rechazar', {
                                pago_month_id: this.data.id
                            })
                            .then(response => {
                                if (response.status == 201) {
                                    this._getData();
                                    this._showAlert('success', response.data);
                                }
                            })
                            .catch(error => {
                                console.log(error)
                                this.btnDisabled = false;
                            })
                    }
                })
            }
        },
        mounted() {
            
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
        },
    }

</script>