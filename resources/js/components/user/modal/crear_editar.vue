<template>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-crear-editar" @click="_crear()">Crear {{ auth.rol_id == 1 ? 'conjunto' : 'usuario' }}</button>

        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.id ? 'Modificar' : 'Crear' }} {{ auth.rol_id == 1 ? 'conjunto' : 'usuario' }}</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12 mb-2" v-if="auth.rol_id == 1">
                                    <p class="mb-1">Tipo de vivienda</p>

                                    <div class="d-flex">
                                        <div class="form-check mr-3">
                                            <input class="form-check-input" type="radio" name="check_tipo"
                                            id="check_apartamento" value="Apartamento" v-model="form.tipo" required>
                                            <label class="form-check-label" for="check_apartamento">Apartamento</label>
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="check_tipo"
                                            id="check_casa" value="Casa" v-model="form.tipo" required>
                                            <label class="form-check-label" for="check_casa">Casa</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Nombre {{ auth.rol_id == 1 ? 'del conjunto' : '' }}</label>
                                        <input type="text" class="form-control" :class="{'is-invalid' : 'name' in errors}" v-model="form.name" required>

                                        <div class="text-danger mt-2" v-if="'name' in errors">
                                            {{ errors.name[0] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" :class="{'is-invalid' : 'email' in errors}" v-model="form.email" required>

                                        <div class="text-danger mt-2" v-if="'email' in errors">
                                            {{ errors.email[0] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="auth.rol_id != 1">
                                    <div class="form-group">
                                        <label class="form-label">Rol</label>
                                        <select class="form-control" v-model="form.rol_id">
                                            <option value="">Elige una opción</option>
                                            <option value="3" v-if="auth.rol_id == 2">Administrador</option>
                                            <option value="4">Portero</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="form.rol_id == 2">
                                    <div class="form-group">
                                        <label class="form-label">NIT</label>
                                        <input type="text" class="form-control" v-model="form.nit" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Teléfono</label>
                                        <input type="number" class="form-control" v-model="form.telefono" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" class="form-control" v-model="form.direccion" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Numero Enmascardo</label><br/>
                                        <toggle-button :sync="true" v-model="form.masked_phone"/>
                                    </div>
                                </div>

                                <div class="col-lg-12" v-if="form.id">
                                    <p class="text-warning">Complete estos campos si desea cambiar la contraseña</p>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" :class="{'is-invalid' : 'password' in errors}" v-model="form.password" :required="!form.id">

                                        <div class="text-danger mt-2" v-if="'password' in errors">
                                            {{ errors.password[0] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Confirmar contraseña</label>
                                        <input type="password" class="form-control" v-model="form.password_confirmation" :required="!form.id">
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
        props: ['attrib', 'auth'],
        data() {
            return {
                form: {
                    id: '',
                    name: '',
                    email: '',
                    nit: '',
                    telefono: '',
                    direccion: '',
                    tipo: '',
                    rol_id: '',
                    password: '',
                    password_confirmation: '',
                    masked_phone: false
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
                    .post('usuario', this.form)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal();
                            this._showAlert('success', response.data);
                        }
                        else {
                            this.errors = response.data;
                        }
                    })
                    .catch(error => {
                        console.log(error)
                        this.btnDisabled = false;
                    })
            },
            __update() {
                axios
                    .put('usuario/' + this.form.id, this.form)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal();
                            this._showAlert('success', response.data);
                        }
                        else {
                            this.errors = response.data;
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
                console.log('cambiando props')
                this.form.id = this.attrib.id;
                this.form.name = this.attrib.name;
                this.form.email = this.attrib.email;
                this.form.nit = this.attrib.nit;
                this.form.telefono = this.attrib.telefono;
                this.form.direccion = this.attrib.direccion;
                this.form.rol_id = this.attrib.rol_id;
                this.form.tipo = this.attrib.tipo;
                this.form.masked_phone = Boolean(this.attrib.masked_phone);
                $("#modal-crear-editar").modal('toggle');
            },
            _crear() {
                this.form = {};
                if(this.auth.rol_id == 1){
                    this.form.rol_id = 2;
                }

                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal").click();
                this.form = {
                    id: '',
                    name: '',
                    email: '',
                    telefono: '',
                    rol_id: '',
                    password: '',
                    password_confirmation: '',
                    masked_phone: false
                };

                if(this.auth.rol_id == 1){
                    this.form.rol_id = 2;
                }

                this._updateList();
            }
        },
        mounted() {
            if(this.auth.rol_id == 1){
                this.form.rol_id = 2;
            }
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
        },
    }

</script>
