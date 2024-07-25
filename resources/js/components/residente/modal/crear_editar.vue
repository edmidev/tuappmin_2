<template>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-crear-editar" @click="_crear()" v-if="auth.rol_id != 1">Crear residente</button>

        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" v-if="auth.rol_id != 1">{{ form.id ? 'Modificar' : 'Crear' }} residente</h5>
                        <h5 class="modal-title" v-else>Ver residente</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST" :style="{'pointer-events' : auth.rol_id == 1 ? 'none' : 'visible'}">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6" v-if="residencia.tipo == 'Apartamento'">
                                    <div class="form-group">
                                        <label class="form-label">Bloque</label>
                                        <select class="form-control" :class="{'is-invalid' : 'bloque' in errors}" v-model="form.bloque"
                                        @change="_getApartamentos()" :disabled="form.id" required>
                                            <option value="">Elige una opción</option>
                                            <option v-for="(item, key) in bloques" :key="key">{{ item.bloque }}</option>
                                        </select>

                                        <div class="text-danger mt-2" v-if="'bloque' in errors">
                                            {{ errors.bloque[0] }}
                                        </div>

                                        <div class="text-danger mt-2">
                                            {{ !form.id ? 'Este campo no se podrá modificar después' : 'Este campo ya no se puede modificar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="residencia.tipo == 'Casa'">
                                    <div class="form-group">
                                        <label class="form-label">Número de casa</label>
                                        <select class="form-control" :class="{'is-invalid' : 'casa_id' in errors}" v-model="form.casa_id"
                                        :disabled="form.id" required>
                                            <option value="">Elige una opción</option>
                                            <option v-for="(item, key) in casas" :key="key" :value="item.id">{{ item.numero }}</option>
                                        </select>

                                        <div class="text-danger mt-2" v-if="'casa_id' in errors">
                                            {{ errors.casa_id[0] }}
                                        </div>

                                        <div class="text-danger mt-2">
                                            {{ !form.id ? 'Este campo no se podrá modificar después' : 'Este campo ya no se puede modificar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="residencia.tipo == 'Apartamento'">
                                    <div class="form-group">
                                        <label class="form-label">Apartamento</label>
                                        <select class="form-control" :class="{'is-invalid' : 'apartamento_id' in errors}" v-model="form.apartamento_id"
                                        :disabled="form.id" required>
                                            <option value="">Elige una opción</option>
                                            <option v-for="(item, key) in apartamentos" :key="key" :value="item.id">{{ item.apartamento }}</option>
                                        </select>

                                        <div class="text-danger mt-2" v-if="'apartamento_id' in errors">
                                            {{ errors.apartamento_id[0] }}
                                        </div>

                                        <div class="text-danger mt-2">
                                            {{ !form.id ? 'Este campo no se podrá modificar después' : 'Este campo ya no se puede modificar' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Nombre del residente</label>
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

                                <div class="col-12 row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-label">Teléfonos</label>
                                            <button type="button" class="btn btn-success mx-1" @click="_addPhone" v-if="auth.rol_id != 1">+</button>
                                            <div class="d-flex my-3" v-for="(item, index) in this.form.phones" v-if="!item.deleted">
                                                <input type="number" class="form-control" v-model="item.number">
                                                <button type="button" class="btn btn-danger ml-1" @click="_removePhone(index)" v-if="auth.rol_id != 1">-</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-12" v-if="form.id && auth.rol_id != 1">
                                    <p class="text-warning">Complete estos campos si desea cambiar la contraseña</p>
                                </div>

                                <div class="col-md-6" v-if="auth.rol_id != 1">
                                    <div class="form-group">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" :class="{'is-invalid' : 'password' in errors}" v-model="form.password" :required="!form.id">

                                        <div class="text-danger mt-2" v-if="'password' in errors">
                                            {{ errors.password[0] }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6" v-if="auth.rol_id != 1">
                                    <div class="form-group">
                                        <label class="form-label">Confirmar contraseña</label>
                                        <input type="password" class="form-control" v-model="form.password_confirmation" :required="!form.id">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal" type="button" class="btn btn-secondary" @click="_closeModal()"
                            v-if="auth.rol_id != 1">Cerrar</button>
                            <button type="submit" class="btn btn-primary" :disabled="btnDisabled" v-if="auth.rol_id != 1">{{ form.id ? 'Modificar' : 'Crear' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['attrib', 'auth', 'residencia'],
        data() {
            return {
                form: {
                    id: '',
                    bloque: '',
                    casa_id: '',
                    apartamento_id: '',
                    name: '',
                    email: '',
                    nit: '',
                    telefono: '',
                    direccion: '',
                    password: '',
                    password_confirmation: '',
                    save_force: false,
                    phones: []
                },
                errors: {},
                btnDisabled: false,
                apartamentos: [],
                bloques: [],
                casas: []
            }
        },
        methods: {
            _addPhone(){
                this.form.phones.push({
                    id: null,
                    number: '',
                    deleted: false
                })
            },
            _removePhone(i){
                this.form.phones[i].deleted = true
            },
            _getApartamentosBloques(){
                axios
                    .get("apartamentos/get-all")
                    .then((response) => {
                        var apartamentos = [...new Map(response.data.apartamentos.map(item =>[item['bloque'], item])).values()];
                        this.bloques = apartamentos;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getApartamentos(){
                var busqueda = {};
                busqueda.bloque = this.form.bloque;

                axios
                    .get("apartamentos/get-all", {
                        params: busqueda,
                    })
                    .then((response) => {
                        this.apartamentos = response.data.apartamentos;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getCasas(){
                axios
                    .get("casas/get-all")
                    .then((response) => {
                        this.casas = response.data.casas;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _onSubmit() {
                this.errors = {};
                this.btnDisabled = true;
                this.form.save_force = false;

                if (this.form.id) {
                    this.__update();
                }
                else {
                    this._store();
                }
            },
            _store() {
                axios
                    .post('residente', this.form)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal();
                            this._showAlert('success', response.data);
                        }
                        else if(response.status == 202){
                            this._confirmarSaveForce();
                        }
                        else if (response.status == 203) {
                            this._showAlert('warning', response.data);
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
                    .put('residente/' + this.form.id, this.form)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal();
                            this._showAlert('success', response.data);
                        }
                        else if (response.status == 203) {
                            this._showAlert('warning', response.data);
                        }
                        else if (response.status == 200) {
                            this._showAlert('warning', response.data);
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
                this.form.id = this.attrib.id;
                this.form.bloque = this.attrib.bloque;
                this.form.casa_id = this.attrib.casa_id;
                this.form.apartamento_id = this.attrib.apartamento_id;
                this.form.name = this.attrib.name;
                this.form.email = this.attrib.email;
                this.form.phones = []
                if (this.attrib.phones){
                    this.attrib.phones.forEach(phone => {
                        this.form.phones.push({
                            id: phone.id,
                            number: phone.phone_number,
                            deleted: false
                        })
                    })
                }
                if(this.residencia.tipo == 'Apartamento'){
                    this._getApartamentos();
                }
                else{
                    this._getCasas();
                }
                $("#modal-crear-editar").modal('toggle');
            },
            _confirmarSaveForce(){
                this._confirmAccion(null, 'El email ' + this.form.email + ' ya esta registrado, ¿Desea guardar los datos?').then(t => {
                    if (t.value) {
                        this.form.save_force = true;
                        this._store();
                    }
                })
            },
            _crear() {
                this.form = {
                    phones: []
                };
                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal").click();
                this.form = {
                    id: '',
                    bloque: '',
                    casa_id: '',
                    apartamento_id: '',
                    name: '',
                    email: '',
                    telefono: '',
                    password: '',
                    password_confirmation: '',
                    phones: []
                };

                this._updateList();
            }
        },
        mounted() {
            if(this.residencia.tipo == 'Apartamento'){
                this._getApartamentosBloques();
            }
            else{
                this._getCasas();
            }
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
        },
    }

</script>
