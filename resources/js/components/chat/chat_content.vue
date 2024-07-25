<template>
    <div class="chat-content">
        <div class="chat-content-leftside mb-3" v-for="(item, key) in messages" :key="key" v-if="auth.id != item.emisor_id">
            <div class="media">
                <img :src="asset + 'images/avatar.png'" width="48" height="48" class="rounded-circle" alt="" />
                <div class="media-body ml-2">
                    <p class="mb-0 chat-time"><strong>{{ item.emisor }}</strong>, <dateformat-component :date="item.created_at"></dateformat-component></p>                    

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
</template>

<script>
    export default {
        props: ['asset', 'auth', 'attrib', 'time'],
        data() {
            return {
                conversacion: {
                    codigo: ''
                },
                messages: []
            };
        },
        methods: {
            _getMessages() {
                axios.get('/messages', {
                    params: {
                        codigo: this.conversacion.codigo
                    }
                }).then(response => {
                    this.messages = response.data.messages;
                });
            },
            _getAttrib() {
                this.conversacion.codigo = this.attrib.codigo;
                this._getMessages();
            },
            _getTime() {
                this._getMessages();
            },
        },
        mounted() {
            
        },
        watch: {
            attrib: [{
                handler: '_getAttrib'
            }],
            time: [{
                handler: '_getTime'
            }],
        },
    };
</script>