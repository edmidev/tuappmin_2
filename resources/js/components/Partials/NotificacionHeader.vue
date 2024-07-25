<template>
    <li class="nav-item dropdown dropdown-lg">
        <a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="javascript:;" data-toggle="dropdown"
        @click="_getData()">	<i class="bx bx-bell vertical-align-middle"></i>
            <span class="msg-count">{{ count }}</span>
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="javascript:;">
                <div class="msg-header">
                    <h6 class="msg-header-title">{{ count }} {{ count > 1 ? 'nuevas' : 'nueva' }}</h6>
                    <p class="msg-header-subtitle">Notificaciones</p>
                </div>
            </a>
            <div class="header-notifications-list" style="overflow: auto;">
                <a class="dropdown-item" href="javascript:;" v-for="(item, key) in data" :key="key" @click="_marcarVista(item)"
                style="white-space: inherit;">
                    <div class="media align-items-center">
                        <div class="notify" :class="[_getClass(item.class)]"><i :class="[_getIcon(item.icon)]"></i>
                        </div>
                        <div class="media-body">
                            <h6 class="msg-name">{{ item.title }}</h6>
                            <p class="msg-info">{{ _replaceText(item) }}</p>
                            <span class="msg-time float-right mt-1">
                                <dateformat-component :date="item.created_at"></dateformat-component>
                            </span>
                        </div>
                    </div>
                </a>
            </div>
            <!--<a href="javascript:;" >
                <div class="text-center msg-footer">Ver todas</div>
            </a>-->
        </div>
    </li>
</template>

<script>
    export default {
        props: ['asset', 'url'],
        data() {
            return {
                data: {},
                count: 0
            }
        },
        methods: {
            _getData() {
                axios
                    .get('notificaciones/get-take')
                    .then(response => {
                        this.count = response.data.count;
                        this.data = response.data.notificaciones;
                    })
            },
            configureNotification: async (messaging) => {
                await Notification.requestPermission()
            },
            _replaceText(data) {
                var message = data.message_notificacion ? data.message_notificacion : data.message;

                if (data.user) {
                    var text = message.replace(':name', data.user);
                }
                else {
                    var text = message.replace(':name', 'Desconocido');
                }

                return text;
            },
            _marcarVista(item){
                axios
                    .post('notificaciones/marcar-vista', {
                        notificacion_id: item.id
                    })
                    .then(response => {
                        if(!item.accion)
                            return;

                        window.location.href = this.url + '/' + item.accion;
                    })
            },
            _getIcon(icon){
                return icon ? icon : 'bx bx-bell';
            },
            _getClass(clases){
                return clases ? clases : '';
            }
        },
        mounted() {
            this._getData();

            var firebaseConfig = {
                apiKey: "AIzaSyCL0m8D9ZUR29mKiaYbZXjbbWJaQxeJtlE",
                authDomain: "pruebas-33641.firebaseapp.com",
                projectId: "pruebas-33641",
                storageBucket: "pruebas-33641.appspot.com",
                messagingSenderId: "688870553131",
                appId: "1:688870553131:web:e064e35fe33b06978fdc6e",
                measurementId: "G-YRHSYZXRYX"
            };
            // Initialize Firebase
            !firebase.apps.length ? firebase.initializeApp(firebaseConfig) : firebase.app();

            var messaging = null;
            if (firebase.messaging.isSupported()) {
                messaging = firebase.messaging();
            }

            this.configureNotification(messaging).then(function () {
                return messaging.getToken();
            })
                .then(token => {
                    $("#token_firebase").val(token);
                    return token;
                })
                .catch(function (err) {
                    console.log(err);
                    return err;
                })

            messaging.onMessage((payload) => {
                var notification = new Notification(payload.notification.title, {
                    icon: this.asset + 'images/logo.png',
                    image: payload.notification.icon,
                    body: payload.notification.body                    
                });

                this._getData();
            });
        }
    }

</script>