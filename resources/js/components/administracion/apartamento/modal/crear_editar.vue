<template>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-crear-editar" @click="_crear()">Crear apartamento</button>

        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.id ? 'Modificar' : 'Crear' }} apartamento</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Bloque</label>
                                        <input type="text" class="form-control" v-model="form.bloque" :disabled="form.id" required>

                                        <div class="text-danger mt-2">
                                            {{ !form.id ? 'Este campo no se podrá modificar después' : 'Este campo ya no se puede modificar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Apartamento</label>
                                        <input type="text" class="form-control" v-model="form.apartamento" :disabled="form.id" required>

                                        <div class="text-danger mt-2">
                                            {{ !form.id ? 'Este campo no se podrá modificar después' : 'Este campo ya no se puede modificar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Nombre propietario</label>
                                        <input type="text" class="form-control" v-model="form.nombre_propietario" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Teléfono</label>
                                        <input type="number" class="form-control" v-model="form.telefono" required>
                                    </div>
                                </div>                                                              
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal" type="button" class="btn btn-secondary" @click="_closeModal()">Cancelar</button>
                            <button type="submit" class="btn btn-primary" :disabled="btnDisabled">{{ form.id ? 'Modificar' : 'Crear' }}</button>
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
                    id: '',
                    bloque: '',
                    apartamento: '',
                    nombre_propietario: '',
                    telefono: '',
                },
                errors: {},
                btnDisabled: false
            }
        },
        methods: {                        
            _onSubmit() {
                this.errors = {};
                this.btnDisabled = true;

                if (this.form.id) {
                    this.__update();
                }
                else {
                    this._store();
                }
            },
            _store() {
                axios
                    .post('apartamento', this.form)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal();
                            this._showAlert('success', response.data);
                        }
                        else {
                            this.errors = response.data;
                            this._showAlert('warning', response.data);
                        }
                    })
                    .catch(error => {
                        console.log(error)
                        this.btnDisabled = false;
                    })
            },
            __update() {
                axios
                    .put('apartamento/' + this.form.id, this.form)
                    .then(response => {                        
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal();
                            this._showAlert('success', response.data);
                        }
                        else {
                            this.errors = response.data;
                            this._showAlert('warning', response.data);
                        }
                    })
                    .catch(error => {
                        console.log(error)
                        this.btnDisabled = false;
                    })
            },
            _updateList() {
                this.$emit('updateList');
            },
            _getAttrib() {
                console.log(this.attrib)
                this.form.id = this.attrib.id;
                this.form.bloque = this.attrib.bloque;
                this.form.apartamento = this.attrib.apartamento;
                this.form.nombre_propietario = this.attrib.nombre_propietario;
                this.form.telefono = this.attrib.telefono;
                $("#modal-crear-editar").modal('toggle');
            },
            _crear() {
                this.form = {};
                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal").click();
                this.form = {
                    id: '',
                    bloque: '',
                    apartamento: '',
                    nombre_propietario: '',
                    telefono: '',                   
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