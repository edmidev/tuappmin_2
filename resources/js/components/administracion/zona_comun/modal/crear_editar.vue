<template>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-crear-editar" @click="_crear()">Crear zona común</button>

        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.id ? 'Modificar' : 'Crear' }} zona común</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Nombre de la zona</label>
                                        <input type="text" class="form-control" v-model="form.name" required>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Imagenes</label>
                                        <input type="file" id="input-image"
                                        v-on:change="handleFileUpload()" ref="file" style="display: none;" />
                                        <div>
                                            <div class="d-flex flex-wrap">
                                                <div class="tarjeta-image position-relative" v-for="(item, key) in images" :key="key">
                                                    <img :src="item" class="w-100">

                                                    <div class="icon-delete-image" @click="_removeFileToLocal(key)">
                                                        <i class="bx bx-x"></i>
                                                    </div>
                                                </div>

                                                <div class="tarjeta-image position-relative" v-for="(item, key) in images_guardadas" :key="1000 + key">
                                                    <img :src="asset + 'storage/zonas_comunes/' + item.imagen" class="w-100">

                                                    <div class="icon-delete-image" @click="_removeFileToServer(item.id, key)">
                                                        <i class="bx bx-x"></i>
                                                    </div>
                                                </div>

                                                <label for="input-image">
                                                    <div class="tarjeta-add">
                                                        <i class="bx bx-plus" style="font-size: 2em;"></i>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Descripción (opcional)</label>
                                        <textarea class="form-control" v-model="form.descripcion" rows="3"></textarea>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    Valor del alquiler
                                </div>

                                <div class="col-md-12">
                                    <div class="row" v-for="(item, key) in form.horarios" :key="key">
                                        <div :class="{'col-md-4': !form.id, 'col-md-3': form.id}">
                                            <div class="form-group">
                                                <label class="form-label">Desde</label>
                                                <input type="time" class="form-control" v-model="item.hora_inicial" required>
                                            </div>
                                        </div>

                                        <div :class="{'col-md-4': !form.id, 'col-md-3': form.id}">
                                            <div class="form-group">
                                                <label class="form-label">Hasta</label>
                                                <input type="time" class="form-control" v-model="item.hora_final" required>
                                            </div>
                                        </div>

                                        <div :class="{'col-md-4': !form.id, 'col-md-3': form.id}">
                                            <div class="form-group">
                                                <label class="form-label">Valor</label>
                                                <input type="number" class="form-control" step=".01" v-model="item.valor" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3" v-if="item.id">
                                            <div class="form-group">
                                                <label class="form-label d-block">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm" title="Eliminar"
                                                @click="_removeHorarioToServer(item.id, key)">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-center">
                                        <button type="button" class="btn btn-success btn-sm"
                                        @click="_addHorario()">
                                            <i class="bx bx-plus" title="Agregar horario"></i>
                                        </button>

                                        <button type="button" class="btn btn-danger btn-sm ml-2"
                                        @click="_removeHorario()">
                                            <i class="bx bx-minus" title="Eliminar horario"></i>
                                        </button>
                                    </div>
                                </div>                                        
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal" type="button" class="btn btn-secondary" @click="_closeModal()">Cerrar</button>
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
        props: ['asset', 'attrib'],
        data() {
            return {
                form: {
                    id: '',
                    name: '',
                    descripcion: '',
                    horarios: []
                },
                errors: {},
                btnDisabled: false,
                files: [],
                images: [],
                images_guardadas: [],
                images_eliminadas: [],
                horarios_eliminados: [],
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
                var formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('descripcion', this.form.descripcion);
                formData.append('num_files', this.files.length);
                formData.append('horarios', JSON.stringify(this.form.horarios));

                this.files.map((item, key) => {
                    formData.append('imagen_' + key, item);
                })

                axios
                    .post('zona_comun', formData)
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
                var formData = new FormData();
                formData.append('name', this.form.name);
                formData.append('descripcion', this.form.descripcion);
                formData.append('horarios', JSON.stringify(this.form.horarios));
                formData.append('num_files', this.files.length);
                formData.append('imagenes_eliminadas', JSON.stringify(this.images_eliminadas));
                formData.append('horarios_eliminados', JSON.stringify(this.horarios_eliminados));
                formData.append('_method', 'PUT');

                this.files.map((item, key) => {
                    formData.append('imagen_' + key, item);
                })

                axios
                    .post('zona_comun/' + this.form.id, formData)
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
                this.form.id = this.attrib.id;
                this.form.name = this.attrib.name;
                this.form.descripcion = this.attrib.descripcion;
                this.images_guardadas = this.attrib.imagenes;
                this.form.horarios = this.attrib.horarios;
                this.images_eliminadas = [];
                this.horarios_eliminados = [];
                $("#modal-crear-editar").modal('toggle');
            },
            _crear() {
                this.form = {
                    id: '',
                    name: '',
                    descripcion: '',
                    horarios: []
                };
                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal").click();
                this.form = {
                    id: '',
                    name: '',
                    descripcion: '',
                    horarios: []     
                };

                this.files = [];
                this.images = [];
                this.images_guardadas = [];
                this.images_eliminadas = [];
                this.horarios_eliminados = [];

                this._updateList();
            },
            handleFileUpload() {
                if(this.$refs.file.files[0]){
                    this.files.push(
                        this.$refs.file.files[0]
                    );

                    this._renderImage(this.$refs.file.files[0]);
                }
            },
            _renderImage(image){
                var reader = new FileReader();
                reader.onload = () => {
                    this.images.unshift(reader.result);
                }
    
                reader.readAsDataURL(image);
            },
            _removeFileToLocal(key){
                var files = [];
                var images = [];
                this.files.map((item, index) => {
                    if(key != index){
                        files.push(item);
                        images.push(this.images[index]);
                    }
                });

                this.files = files
                this.images = images;
            },
            _removeFileToServer(id, key){
                this.images_eliminadas.push(id);

                var imagenes = [];
                this.images_guardadas.map((item, index) => {
                    if(index != key){
                        imagenes.push(item);
                    }
                });

                this.images_guardadas = imagenes;
            },
            _addHorario(){
                this.form.horarios.push({
                    hora_inicial: '', hora_final: '',
                    value: ''
                });
            },
            _removeHorario(){
                var horarios = this.form.horarios.slice(0, -1);
                this.form.horarios = horarios;
            },
            _removeHorarioToServer(id, key){
                this.horarios_eliminados.push(id);

                var horarios = [];
                this.form.horarios.map((item, index) => {
                    if(index != key){
                        horarios.push(item);
                    }
                });

                this.form.horarios = horarios;
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