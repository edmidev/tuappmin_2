<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="modal-nueva-conversacion" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo chat</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <form @submit.prevent="_onSubmit" method="POST">
                        <div class="modal-body">
                            <div class="row">                                
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Email {{ auth.rol_id != 1 ? 'del administrador' : '' }}</label>
                                        <input type="email" class="form-control" v-model="form.email" :readonly="auth.rol_id != 1" required>                                        
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label">Mensaje</label>
                                        <input type="text" class="form-control" maxlength="500" v-model="form.message" placeholder="Escribe un mensaje" required>                                        
                                    </div>
                                </div>                                                                                                                                
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button id="btn-close-modal" type="button" class="btn btn-secondary" @click="_closeModal()">Cancelar</button>
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
        props: ['asset', 'auth'],
        data() {
            return {
                form: {
                    email: '',
                    message: 'Hola como estas?',
                },
                errors: {},
                btnDisabled: false,
            }
        },
        methods: {                        
            _onSubmit() {
                this.errors = {};
                this.btnDisabled = true;

                this._store();
            },
            _store() {                
                axios
                    .post('nuevo-chat', this.form)
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this._closeModal(response.data.message);
                            this._showAlert('success', 'Mensaje enviado');
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
            _updateList(message = null) {
                this.$emit('updateList', message);
            },                        
            _closeModal(message) {
                $("#btn-close-modal").click();
                this.form = {
                    email: this.auth.rol_id != 1 ? 'admin@admin.com' : '',
                    message: 'Hola como estas?',
                };

                this._updateList(message);
            },                  
        },
        mounted() {
            if(this.auth.rol_id != 1){
                this.form.email = 'admin@admin.com';
            }
        }        
    }

</script>