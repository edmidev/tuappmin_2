<template>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-crear-editar" @click="_crear()">Crear reservación</button>

        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.id ? 'Modificar' : 'Crear' }} reservación</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Residente</label>
                                        <select class="form-control" v-model="form.residente_conjunto_id" required>
                                            <option value="">Elige una opción</option>
                                            <option v-for="(item, key) in residentes" :key="key" :value="item.id">{{ item.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Zona común</label>
                                        <select class="form-control" v-model="zona_comun_id" @change="_getHorariosDisponibles()" required>
                                            <option value="">Elige una opción</option>
                                            <option v-for="(item, key) in zonas_comunes" :key="key" :value="item.id">{{ item.name }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Fecha de la reservación</label>
                                        <input type="date" class="form-control" v-model="form.fecha" @change="_getHorariosDisponibles()" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Horario</label>
                                        <select class="form-control" v-model="form.horario_id" @change="_getDetallesHorario()" required>
                                            <option value="">Elige una opción</option>
                                            <option v-for="(item, key) in horarios" :key="key" :value="item.id">De {{ item.hora_inicial }} a {{ item.hora_final }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <h6 v-if="horario.id">Total: 
                                        <strong>
                                            $<number-format-component :valor="horario.valor"></number-format-component>
                                        </strong>
                                    </h6>
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
                    residente_conjunto_id: '',
                    horario_id: '',
                    fecha: '',
                },
                zona_comun_id: '',
                residentes: [],
                zonas_comunes: [],
                horarios: [],
                horario: {},
                errors: {},
                btnDisabled: false
            }
        },
        methods: {     
            _getResidentes() {
                axios
                    .get("residentes/get-all")
                    .then((response) => {
                        this.residentes = response.data.users;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getZonasComunes() {
                axios
                    .get("zonas_comunes/get-all")
                    .then((response) => {
                        this.zonas_comunes = response.data.zonas;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getHorariosDisponibles() {
                if(!this.zona_comun_id || !this.form.fecha){
                    return;
                }

                axios
                    .get("zonas_comunes/horarios_disponibles", {
                        params: {
                            zona_comun_id: this.zona_comun_id,
                            fecha: this.form.fecha
                        }
                    })
                    .then((response) => {
                        this.horarios = response.data.horarios;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },                  
            _getDetallesHorario(){
                for (let index = 0; index < this.horarios.length; index++) {
                    if(this.form.horario_id == this.horarios[index].id){
                        this.horario = this.horarios[index];
                        break;
                    }
                }
            },
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
                const data = {
                    fecha: this.form.fecha,
                    hora_inicial: this.horario.hora_inicial,
                    hora_final: this.horario.hora_final,
                    horario_id: this.form.horario_id,
                    total: this.horario.valor,
                    residente_conjunto_id: this.form.residente_conjunto_id
                }

                axios
                    .post('zonas_comunes/reservacion', data)
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
                    .put('zonas_comunes/reservacion/' + this.form.id, this.form)
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
                this.form.id = this.attrib.id;
                this.form.bloque = this.attrib.bloque;
                this.form.apartamento = this.attrib.apartamento;
                this.form.nombre_propietario = this.attrib.nombre_propietario;
                this.form.telefono = this.attrib.telefono;
                $("#modal-crear-editar").modal('toggle');
            },
            _crear() {
                this.form = {
                    id: '',
                    residente_conjunto_id: '',
                    horario_id: '',
                    fecha: '',
                };
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
            this._getResidentes();
            this._getZonasComunes();
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
        },
    }

</script>