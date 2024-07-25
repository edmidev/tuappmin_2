<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form>
                        <div class="modal-body" style="pointer-events: none;">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Tipo de visitante</label>
                                        <input type="text" class="form-control" :value="form.tipo ? tipos[form.tipo - 1] : ''">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Número de documento</label>
                                        <input type="text" class="form-control" :value="form.numero_documento">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Nombre completo</label>
                                        <input type="text" class="form-control" :value="form.name">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Fecha de nacimiento</label>
                                        <input type="text" class="form-control" :value="form.fecha_nacimiento">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">RH</label>
                                        <input type="text" class="form-control" :value="form.rh">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Sexo</label>
                                        <input type="text" class="form-control" :value="form.sexo == 'M' ? 'Hombre' : 'Mujer'">
                                    </div>
                                </div>

                                <div class="col-md-12" v-if="form.observacion">
                                    <div class="form-group">
                                        <label class="form-label">Observación</label>
                                        <p>{{ form.observacion }}</p>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Fecha y hora de ingreso</label>
                                        <input type="text" class="form-control" :value="form.fecha_ingreso">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Fecha y hora de salida</label>
                                        <input type="text" class="form-control" :value="form.fecha_salida">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Portero entrada</label>
                                        <input type="text" class="form-control" :value="form.portero_entrada">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Portero salida</label>
                                        <input type="text" class="form-control" :value="form.portero_salida">
                                    </div>
                                </div>                          
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal" type="button" class="btn btn-secondary" @click="_closeModal()">Cerrar</button>
                        </div>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['attrib'],
        data() {
            return {
                form: {
                    
                },
                tipos: [
                    'Visitante', 'Domiciliario', 'Técnico'
                ],
                errors: {},
                btnDisabled: false
            }
        },
        methods: {
            _updateList() {
                this.$emit('updateList');
            },
            _getAttrib() {
                this.form = this.attrib;
                $("#modal-crear-editar").modal('toggle');
            },
            _crear() {
                this.form = {};
                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal").click();
                this.form = {
                                    
                };                

                this._updateList();
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