<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="modal-preview-image" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <img id="img-preview" class="w-100 m-auto d-block" style="max-width: 350px;"/>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Mensaje</label>
                                        <input type="text" class="form-control" maxlength="500" v-model="form.message" placeholder="Escribe un mensaje">                                        
                                    </div>
                                </div>                                                                                                                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal2" type="button" class="btn btn-secondary" @click="_closeModal()">Cancelar</button>
                            <button type="submit" class="btn btn-primary" :disabled="btnDisabled">Enviar</button>
                        </div>
                    </form>                    
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['asset', 'auth', 'attrib', 'file'],
        data() {
            return {
                form: {
                    message: '',
                },
                errors: {},
                btnDisabled: false,
                file_aux: null,
                conversacion: {
                    codigo: ''
                },
            }
        },
        methods: {                        
            _onSubmit() {
                this.errors = {};
                this.btnDisabled = true;

                this._store();
            },
            _store() {
                if(!this.file){
                    this._showAlert('warning', 'Carga una imagen');
                    return;
                }                    

                var formData = new FormData();
                formData.append('image', this.file);
                formData.append('message', this.form.message);
                formData.append('codigo', this.conversacion.codigo);

                axios
                    .post('send-message', formData)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal(true);
                        }
                        else {
                            this._showAlert('warning', response.data);
                        }
                    })
                    .catch(error => {
                        console.log(error)
                        this.btnDisabled = false;
                    })
            },            
            _updateList(update = true) {
                this.$emit('updateList', update);
            },                        
            _closeModal(update = false) {
                $("#modal-preview-image").modal('hide');
                this.form = {
                    message: '',
                };

                this._updateList(update);
            },            
            _getFile() {
                this.file_aux = this.file;
                if(this.file){
                    const validImageTypes = ['image/jpg', 'image/jpeg', 'image/png'];
                    if (!validImageTypes.includes(this.file.type)) {
                        this._showAlert('warning', 'Carga una imagen valida');
                        return;
                    }

                    var output = document.getElementById('img-preview');
                    output.src = URL.createObjectURL(this.file_aux);
                    output.onload = function() {
                        URL.revokeObjectURL(output.src);
                    }

                    $("#modal-preview-image").modal('toggle');                    
                }                
            },
            _getAttrib() {
                this.conversacion.codigo = this.attrib.codigo;
            },        
        },
        mounted() {
            
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
            file: [{
                handler: '_getFile'
            }],           
        },    
    }

</script>