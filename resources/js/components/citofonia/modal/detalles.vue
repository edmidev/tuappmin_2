<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Detalles</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form>
                        <div class="modal-body" style="pointer-events: none;">
                            <div class="row">
                                <div class="col-md-12">
                                    <p v-if="form.rol_id_entrada == 5" style="color: #107ACC;">El residente registro esta información</p>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Motivo</label>
                                        <p>{{ form.motivo }}</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Fecha y hora de ingreso</label>
                                        <input type="text" class="form-control" :value="form.fecha_ingreso">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Acceso entrada</label>
                                        <input type="text" class="form-control" :value="(form.rol_id_entrada == 5 ? 'Residente: ' : '') + form.portero_entrada">
                                    </div>
                                </div>                        
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal" type="button" class="btn btn-secondary" @click="_closeModal()">Cancelar</button>
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