<template>
    <div class="chat-wrapper">
        <div class="chat-sidebar">
            <div class="chat-sidebar-header">
                <div class="media align-items-center">
                    <div class="chat-user-online">
                        <img :src="asset + 'images/avatar.png'" width="45" height="45" class="rounded-circle"
                            alt="" />
                    </div>
                    <div class="media-body ml-2">
                        <p class="mb-0">{{ auth.name }}</p>
                    </div>                    
                </div>
                <!--<div class="mb-3"></div>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend"> <span class="input-group-text bg-transparent"><i
                                class='bx bx-search'></i></span>
                    </div>
                    <input type="text" class="form-control border-left-0" placeholder="Contactos">
                    <div class="input-group-append"> <span class="input-group-text bg-transparent"><i
                                class='bx bx-dialpad'></i></span>
                    </div>
                </div>    -->            
            </div>
            <div class="chat-sidebar-content">
                <div class="tab-content" id="pills-tabContent">
                    <div class="tab-pane fade show active" id="pills-Chats">
                        <div class="p-15">
                            <div class="meeting-button d-flex justify-content-between">                                
                                <!-- Button trigger modal -->
                                <a id="btn-close-modal" class="btn btn-white btn-sm radius-30 dropdown-toggle dropdown-toggle-nocaret"
                                data-toggle="modal" data-target="#modal-nueva-conversacion"><i class='bx bxs-edit mr-2'></i>Nuevo chat</a>                                
                            </div>
                            <div class="dropdown mt-3"> <a href="javascript:;"
                                    class="text-uppercase text-secondary dropdown-toggle dropdown-toggle-nocaret"
                                    data-toggle="dropdown">Recientes</a>                                
                            </div>
                        </div>
                        <div class="chat-list">
                            <div class="list-group list-group-flush">
                                <a class="list-group-item" v-for="(item, key) in messages" style="cursor: pointer;" :key="key" @click="_changeConversacion(item, key)">
                                    <div class="media">
                                        <div class="chat-user">
                                            <img :src="asset + 'images/avatar.png'" width="42" height="42"
                                                class="rounded-circle" alt="" />
                                        </div>
                                        <div class="media-body ml-2">
                                            <h6 class="mb-0 chat-title">{{ auth.id == item.emisor_id ? item.receptor : item.emisor }}</h6>
                                            <div class="d-flex align-items-center">
                                                <i class="bx bx-image mr-2" v-if="item.last_message.image" style="font-size: 1.3em;"></i>
                                                <div class="d-flex justify-content-between w-100">
                                                    <p class="mb-0 chat-msg" style="height: 23px; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ item.last_message.message }}                                                
                                                    </p>
                                                    <div v-if="item.count_no_vistos > 0" class="d-flex justify-content-center align-items-center" 
                                                    style="border-radius: 100px; background-color: darkgreen; color: #fff; width: 22px;
                                                    height: 22px; font-size: 12px;">
                                                        {{ item.count_no_vistos }}  
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="chat-time"><dateformat-component :date="item.last_message.created_at"></dateformat-component></div>
                                    </div>
                                </a>                                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="chat-header d-flex align-items-center">
            <div class="chat-toggle-btn"><i class='bx bx-menu-alt-left'></i>
            </div>
            <div>
                <h5 class="mb-1 font-weight-bold">{{ attrib && auth.id == attrib.emisor_id ? attrib.receptor : attrib.emisor }}</h5>
                <!--<div class="list-inline d-sm-flex mb-0 d-none"> <a href="javascript:;"
                        class="list-inline-item d-flex align-items-center text-secondary"><small
                            class='bx bxs-circle mr-1 chart-online'></small>Active Now</a>
                    <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary">|</a>
                    <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary"><i
                            class='bx bx-images mr-1'></i>Gallery</a>
                    <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary">|</a>
                    <a href="javascript:;" class="list-inline-item d-flex align-items-center text-secondary"><i
                            class='bx bx-search mr-1'></i>Find</a>
                </div>-->
            </div>            
        </div>

        <chat-content-component :asset="asset" :auth="auth" :attrib="attrib" :time="time"></chat-content-component>

        <form @submit.prevent="_sendMessage()">
            <div class="chat-footer d-flex align-items-center" v-if="attrib && attrib.codigo">
                <div class="flex-grow-1 pr-2">
                    <div class="input-group">
                        <div class="input-group-prepend"> <span class="input-group-text bg-transparent"><i
                                    class='bx bx-chat'></i></span>
                        </div>
                        <input type="text" class="form-control border-left-0" maxlength="500" v-model="message" placeholder="Escribe un mensaje">
                    </div>
                </div>
                <div class="chat-footer-menu">
                    <a style="cursor: pointer;" title="Enviar imagen"><label for="input-image"><i class='bx bxs-image'></i></label></a>
                    <input id="input-image" type="file" accept="image/png,image/jpeg,image/jpg" v-on:change="handleFileUpload()" ref="file"
                    style="display: none;">
                </div>        
            </div>
        </form> 
        <!--start chat overlay-->
        <div class="overlay chat-toggle-btn-mobile"></div>
        <!--end chat overlay-->        

        <preview-image-component @updateList="_updateList2" :auth="auth" :attrib="attrib" :file="file"></preview-image-component>
        <nueva-conversacion-component @updateList="_updateList" :auth="auth"></nueva-conversacion-component>
    </div>
</template>

<script>
    export default {
        props: ['asset', 'auth'],
        data() {
            return {
                messages: {},
                attrib: {},
                busqueda: {
                    name: '',
                    id: ''
                },
                message: null,
                time: null,
                file: null
            };
        },
        methods: {
            _getConversaciones(){
                axios.get('/get-conversaciones').then(response => {
                    this.messages = response.data.messages;
                });
            },            
            _updateList(message) {
                if(message){
                    this.attrib = message;
                }

                this._getConversaciones();
            },
            _updateList2(update) {
                this.file = null;
                if(update){
                    this._getConversaciones();
                }
            },
            _changeConversacion(data, key){
                this.messages[key].count_no_vistos = 0;
                this.attrib = data;
            },
            _sendMessage() {
                if(!this.attrib && !this.attrib.codigo && !this.message)
                    return; 

                axios
                    .post('send-message', {
                        message: this.message,
                        codigo: this.attrib.codigo
                    })
                    .then(response => {
                        this.message = '';
                        this.time = new Date().getTime();                    
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
            window.Echo.channel('chat').listen('.ConversacionEvent', (e) => {
                this.time = new Date().getTime();
                this._getConversaciones();       
            })

            new PerfectScrollbar('.chat-list');
            new PerfectScrollbar('.chat-content');
            
            this._getConversaciones();
        },
    };
</script>