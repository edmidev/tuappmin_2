<template>
    <div>
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-crear-editar" @click="_crear()">Crear</button>

        <!-- Modal -->
        <div class="modal fade" id="modal-crear-editar" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ form.id ? 'Modificar' : 'Crear' }} noticia</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Imagen de la noticia</label>
                                        <input type="file" class="dropify" id="input-image"
                                        data-allowed-file-extensions='["jpg", "png", "jpeg"]'
                                        v-on:change="handleFileUpload()" ref="file" />
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Título</label>
                                        <input type="text" class="form-control" v-model="form.titulo" required>                                        
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Link</label>
                                        <input type="text" class="form-control" v-model="form.link" required>                                        
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
        props: ['attrib', 'asset'],
        data() {
            return {
                form: {
                    id: '',
                    image: '',
                    titulo: '',
                    link: ''
                },
                errors: {},
                btnDisabled: false,
                file: null,
                drEvent: null
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
                formData.append('image', this.file);
                formData.append('titulo', this.form.titulo);
                formData.append('link', this.form.link);

                axios
                    .post('noticia', formData)
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
                formData.append('image', this.file);
                formData.append('titulo', this.form.titulo);
                formData.append('link', this.form.link);
                formData.append('_method', 'PUT');

                axios
                    .post('noticia/' + this.form.id, formData)
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
                this.form.image = this.attrib.image;
                this.form.titulo = this.attrib.titulo;
                this.form.link = this.attrib.link;

                var drEvent = this.drEvent.data('dropify');
                drEvent.resetPreview();
                drEvent.clearElement();                
                drEvent.settings.defaultFile = this.form.image ? this.asset + 'storage/images_noticias/' + this.form.image : null;
                drEvent.destroy();
                drEvent.init();                

                $("#modal-crear-editar").modal('toggle');
            },
            _crear() {
                this.form = {};
                this.errors = {};
                this.file = null;

                var drEvent = this.drEvent.data('dropify');
                drEvent.resetPreview();
                drEvent.clearElement();
                drEvent.settings.defaultFile = null;
                drEvent.destroy();
                drEvent.init();
            },
            _closeModal() {
                $("#btn-close-modal").click();
                this.form = {
                    id: '',
                    image: '',
                    titulo: '',
                    link: '',
                };

                this.file = null;   

                this._updateList();
            },
            handleFileUpload() {
                this.file = this.$refs.file.files[0];          
            },
        },
        mounted() {
            this.drEvent = $('#input-image').dropify({
                messages: {
                    'default': 'Arrastra y suelta un archivo aquí o haz clic',
                    'replace': 'Arrastra y suelta o haz clic para reemplazar',
                    'remove': 'Eliminar',
                    'error': 'Vaya, algo malo sucedió.',
                    'defaultFile': null,
                }
            });
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
        },
    }

</script>