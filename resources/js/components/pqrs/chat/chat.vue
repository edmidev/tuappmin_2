<template>
    <div class="chat-wrapper">
        <div class="chat-header d-flex align-items-center" style="left: 0;">
            <div class="chat-toggle-btn"><i class='bx bx-menu-alt-left'></i>
            </div>
            <div>
                <h5 class="mb-1 font-weight-bold">{{ pqrs.residente }}</h5>
            </div>            
        </div>

        <div class="chat-content ml-0">
            <!-- Detalles del PQRS -->
            <div class="chat-content-leftside mb-3">
                <div class="media">
                    <img :src="pqrs.avatar ? pqrs.avatar : asset + 'images/avatar.png'" width="48" height="48" class="rounded-circle" alt="" />
                    <div class="media-body ml-2">
                        <p class="mb-0 chat-time"><strong>{{ pqrs.residente }}</strong>, <dateformat-component :date="pqrs.created_at"></dateformat-component></p>                    

                        <div class="chat-left-msg" style="background: #d0ebff;">
                            <p class="m-0" v-if="conjunto_residencial.tipo == 'Apartamento'"><strong>Bloque </strong>{{ pqrs.bloque }} <strong>Apartamento </strong>{{ pqrs.apartamento }}</p>
                            <p class="m-0" v-if="conjunto_residencial.tipo == 'Casa'"><strong>Casa </strong>{{ pqrs.numero }}</p>
                            <p class="m-0"><strong>Motivo</strong></p>
                            <p class="m-0">{{ pqrs.motivo }}</p>
                            <div style="max-width: 250px; background: #fff;" class="mt-2">
                                <img :src="asset + 'storage/fotos_pqrs/' + pqrs.image" v-if="pqrs.image" class="w-100">
                            </div>
                        </div>
                    </div>                
                </div>        
            </div>

            <!-- Mensajes -->
            <div class="chat-content-leftside mb-3" v-for="(item, key) in messages" :key="key" v-if="auth.id != item.user_id">
                <div class="media">
                    <img :src="asset + 'images/avatar.png'" width="48" height="48" class="rounded-circle" alt="" />
                    <div class="media-body ml-2">
                        <p class="mb-0"><strong>{{ item.name }}</strong> ({{ item.rol }})</p>
                        <p class="m-0 chat-time"><dateformat-component :date="item.created_at"></dateformat-component></p>

                        <div class="chat-left-msg">
                            <p v-if="item.message" class="m-0">{{ item.message }}</p>
                            <div style="max-width: 250px; background: #fff;" class="mt-2">
                                <img :src="asset + 'storage/images_chats/' + item.image" v-if="item.image" class="w-100">
                            </div>
                        </div>
                    </div>                
                </div>        
            </div>
            <div class="chat-content-rightside mb-3" v-else>
                <div class="media d-flex ml-auto">
                    <div class="media-body mr-2">
                        <p class="mb-0 chat-time text-right"><strong>Tu</strong>, <dateformat-component :date="item.created_at"></dateformat-component></p>                    

                        <div class="chat-right-msg">
                            <p v-if="item.message" class="m-0">{{ item.message }}</p>
                            <div style="max-width: 250px; background: #fff;" class="mt-2">
                                <img :src="asset + 'storage/images_chats/' + item.image" v-if="item.image" class="w-100">
                            </div>
                        </div>                    
                    </div>                
                </div>
            </div>               
        </div>

        <form @submit.prevent="_sendMessage()" v-if="pqrs.estatus == 1">
            <div class="chat-footer d-flex align-items-center" v-if="attrib" style="left: 0;">
                <div class="flex-grow-1 pr-2">
                    <div class="input-group">
                        <div class="input-group-prepend"> <span class="input-group-text bg-transparent"><i
                                    class='bx bx-chat'></i></span>
                        </div>
                        <input type="text" class="form-control border-left-0" maxlength="500" v-model="message" placeholder="Escribe un mensaje">
                    </div>
                </div>    
            </div>
        </form> 
        <!--start chat overlay-->
        <div class="overlay chat-toggle-btn-mobile"></div>
        <!--end chat overlay-->  
    </div>
</template>

<script>
    export default {
        props: ['asset', 'auth', 'conjunto_residencial', 'pqrs'],
        data() {
            return {
                messages: {},
                attrib: {},
                busqueda: {
                    name: '',
                    id: ''
                },
                message: null,
                file: null
            };
        },
        methods: {
            _getConversaciones(){
                axios.get('/pqrss/get-messages', {
                    params: {
                        pqrs_id: this.pqrs.id
                    }
                }).then(response => {
                    this.messages = response.data.messages;
                });
            },            
            _updateList(message) {
                if(message){
                    this.attrib = message;
                }

                this._getConversaciones();
            },
            _sendMessage() {
                if(!this.attrib && !this.message)
                    return; 

                axios
                    .post('pqrss/send_message', {
                        message: this.message,
                        pqrs_id: this.pqrs.id
                    })
                    .then(response => {
                        this.message = '';
                        if(response.status == 200){
                            this._showAlert('warning', response.data);
                        }            
                    })
                    .catch(error => {
                        console.log(error)
                    })
            },
            handleFileUpload() {
                this.file = this.$refs.file.files[0];          
            },                 
        },
        mounted() {
            window.Echo.channel('pqrsChat.' + this.pqrs.id).listen('.PqrsConversacionEvent', (e) => {
                this._getConversaciones();       
            })

            new PerfectScrollbar('.chat-content');
            
            this._getConversaciones();
        },
    };
</script>