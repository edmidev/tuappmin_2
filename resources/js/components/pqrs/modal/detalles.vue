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
                                <div class="col-md-12 mb-3" v-if="form.image">
                                    <img :src="asset + 'storage/fotos_pqrs/' + form.image" style="max-width: 180px;" 
                                    class="m-auto d-block">
                                </div>

                                <div class="col-md-6" v-if="conjunto_residencial.tipo == 'Apartamento'">
                                    <div class="form-group">
                                        <label class="form-label">Bloque</label>
                                        <input type="text" class="form-control" :value="form.bloque">
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="conjunto_residencial.tipo == 'Apartamento'">
                                    <div class="form-group">
                                        <label class="form-label">Apartamento</label>
                                        <input type="text" class="form-control" :value="form.apartamento">
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="conjunto_residencial.tipo == 'Casa'">
                                    <div class="form-group">
                                        <label class="form-label">Casa</label>
                                        <input type="text" class="form-control" :value="form.casa">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Tipo</label>
                                        <input type="text" class="form-control" :value="form.tipo ? tipos[form.tipo - 1] : ''">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Residente</label>
                                        <input type="text" class="form-control" :value="form.residente">
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Motivo</label>
                                        <p>{{ form.motivo }}</p>
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
        props: ['attrib', 'asset', 'conjunto_residencial'],
        data() {
            return {
                form: {
                    
                },
                tipos: [
                    'Petición',
                    'Queja',
                    'Reclamo',
                    'Sugerencia',
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