<template>
    <div>
        <!-- Modal -->
        <div class="modal fade" id="modal-comentarios" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Comentarios</h5>
                        <button type="button" class="close" @click="_closeModal()">	<span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <h6 v-if="data.length == 0" style="color: #868e96;" class="text-center">
                            Sin comentarios
                        </h6>

                        <div style="max-height: 65vh; overflow: auto;">
                            <div v-for="(item, key) in data" :key="key">
                                <div class="d-flex align-items-center mb-3 pb-3" style="border-bottom: 1px solid #dee2e6;">
                                    <div class="mr-3">
                                        <img :src="item.avatar ? item.avatar : asset + 'images/avatar.png'"
                                        style="width: 40px; height: 40px;">
                                    </div>

                                    <div>
                                        <h6 class="mb-1" style="font-size: 14px;">{{ item.name }} {{ item.user_id == auth.id ? '(Tu)' : '' }}</h6>
                                        <p class="m-0" style="font-size: 13px;">{{ item.rol }}</p>
                                        <p class="m-0">{{ item.message }}</p>
                                        <p class="m-0" style="font-size: 12px; color: #868e96;">
                                            <dateformat-component :date="item.created_at"></dateformat-component>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <form @submit.prevent="_sendComentario" method="post" class="w-100">
                            <div class="input-group">
                                <input type="text" class="form-control" v-model="message" placeholder="Escribe tu comentario">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="submit">Enviar</button>
                                </div>
                            </div>
                        </form>
                    </div>                  
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['attrib', 'asset', 'auth'],
        data() {
            return {
                form: {},
                errors: {},
                btnDisabled: false,
                data: [],
                message: ''
            }
        },
        methods: {                        
            _getData(page = 1) {
                axios
                    .get("comunicados/comentarios-get-all-paginate?page=" + page, {
                        params: {
                            comunicado_id: this.form.id
                        },
                    })
                    .then((response) => {
                        this.data = response.data.comentarios;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _updateList() {
                this.$emit('updateList2');
            },
            _getAttrib() {
                this.form = this.attrib;
                this._getData();

                window.Echo.channel('comunicadoChat.' + this.form.id).listen('.ComunicadoConversacionEvent', (e) => {
                    this._getData();       
                })

                $("#modal-comentarios").modal('toggle');
            },
            _crear() {
                this.form = {};
                this.errors = {};
            },
            _closeModal() {
                $("#btn-close-modal-comentarios").click();
                this.form = {};

                this._updateList();
            },
            _sendComentario(){
                if(!this.message){
                    return;
                }

                axios
                    .post('comunicados/send_message', {
                        message: this.message,
                        comunicado_id: this.form.id
                    })
                    .then(response => {
                        this.btnDisabled = false;
                        if (response.status == 201) {
                            this.message = '';
                        }
                        else {
                            this._showAlert('warning', response.data);
                        }
                    })
                    .catch(error => {
                        console.log(error)
                        this.btnDisabled = false;
                    })
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