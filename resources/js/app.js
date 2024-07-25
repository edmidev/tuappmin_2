require('./bootstrap');

require('alpinejs');

import Vue from 'vue';
import axiosApi from 'axios';
import moment from "moment";
import { ToggleButton } from 'vue-js-toggle-button'

const axios = axiosApi.create({
    //baseURL: `http://127.0.0.1/`,
    baseURL: `https://tuappmin.com/sistema/`,
});

moment.locale('es');
window.Vue = require('vue');
window.axios = axios;
Vue.prototype.moment = moment;

/** Paginacion */
Vue.component('pagination', require('laravel-vue-pagination'));

/** Partials */
Vue.component('dateformat-component', require('./components/Partials/DateFormat.vue').default);
Vue.component('number-format-component', require('./components/Partials/NumberFormat.vue').default);
Vue.component('notification-header-component', require('./components/Partials/NotificacionHeader.vue').default);

/** Dashboard */
Vue.component('tarjetas-component', require('./components/dashboard/Tarjetas.vue').default);
Vue.component('grafica-conjuntos-anuales-component', require('./components/dashboard/GraficaConjuntosAnuales.vue').default);
Vue.component('grafica-residentes-anuales-component', require('./components/dashboard/GraficaResidentesAnuales.vue').default);
Vue.component('grafica-ingresos-parkings-component', require('./components/dashboard/GraficaIngresosParkings.vue').default);
Vue.component('grafica-ingresos-administracion-component', require('./components/dashboard/GraficaIngresosAdministracion.vue').default);

/** Configuracion */
Vue.component('informacion-pagos-component', require('./components/configuracion/informacion_pagos.vue').default);

/** Usuario */
Vue.component('users-component', require('./components/user/lista.vue').default);
Vue.component('user-modal-crear-editar-component', require('./components/user/modal/crear_editar.vue').default);

/** Residente */
Vue.component('residentes-component', require('./components/residente/lista.vue').default);
Vue.component('residente-modal-crear-editar-component', require('./components/residente/modal/crear_editar.vue').default);

/** Noticia */
Vue.component('noticias-component', require('./components/noticia/lista.vue').default);
Vue.component('noticia-modal-crear-editar-component', require('./components/noticia/modal/crear_editar.vue').default);

/** Notificaciones */
Vue.component('notificaciones-component', require('./components/notificacion/lista.vue').default);
Vue.component('notificacion-modal-crear-editar-component', require('./components/notificacion/modal/crear_editar.vue').default);

/** Chats */
Vue.component('chats-component', require('./components/chat/chat.vue').default);
Vue.component('chat-content-component', require('./components/chat/chat_content.vue').default);
Vue.component('nueva-conversacion-component', require('./components/chat/modal/nueva_conversacion.vue').default);
Vue.component('preview-image-component', require('./components/chat/modal/preview_image.vue').default);

/** Apartamento */
Vue.component('apartamentos-component', require('./components/administracion/apartamento/lista.vue').default);
Vue.component('apartamento-modal-crear-editar-component', require('./components/administracion/apartamento/modal/crear_editar.vue').default);

/** Casa */
Vue.component('casas-component', require('./components/administracion/casa/lista.vue').default);
Vue.component('casa-modal-crear-editar-component', require('./components/administracion/casa/modal/crear_editar.vue').default);

/** Zona comun */
Vue.component('zonas-comunes-component', require('./components/administracion/zona_comun/lista.vue').default);
Vue.component('zona-comun-modal-crear-editar-component', require('./components/administracion/zona_comun/modal/crear_editar.vue').default);
Vue.component('zonas-comunes-reservaciones-component', require('./components/administracion/zona_comun/reservacion/lista.vue').default);
Vue.component('zona-comun-reservacion-crear-editar-component', require('./components/administracion/zona_comun/reservacion/modal/crear_editar.vue').default);

/** Parking */
Vue.component('parking-component', require('./components/administracion/parking/lista.vue').default);

/** Servicio publico */
Vue.component('public-services-component', require('./components/public_service/lista.vue').default);
Vue.component('public-service-modal-crear-editar-component', require('./components/public_service/modal/crear_editar.vue').default);

/** Correspondencia */
Vue.component('correspondences-component', require('./components/correspondence/lista.vue').default);

/** Visitantes */
Vue.component('visitantes-component', require('./components/visitante/lista.vue').default);
Vue.component('visitante-detalles-component', require('./components/visitante/modal/detalles.vue').default);

/** Citofonias */
Vue.component('citofonias-component', require('./components/citofonia/lista.vue').default);
Vue.component('citofonia-detalles-component', require('./components/citofonia/modal/detalles.vue').default);

/** PQRS */
Vue.component('pqrs-component', require('./components/pqrs/lista.vue').default);
Vue.component('pqrs-detalles-component', require('./components/pqrs/modal/detalles.vue').default);
Vue.component('pqrs-chats-component', require('./components/pqrs/chat/chat.vue').default);

/** Comunicados */
Vue.component('comunicados-component', require('./components/comunicado/lista.vue').default);
Vue.component('comunicado-modal-crear-editar-component', require('./components/comunicado/modal/crear_editar.vue').default);
Vue.component('comunicado-comentarios-component', require('./components/comunicado/modal/comentarios.vue').default);

/** Pago */
Vue.component('pagos-component', require('./components/pago/lista.vue').default);
Vue.component('detalles-pagos-component', require('./components/pago/modal/detalles_pago.vue').default);

/** Minuta */
Vue.component('minutas-component', require('./components/minuta/lista.vue').default);

// Toggle Button
Vue.component('ToggleButton', ToggleButton)

Vue.mixin({
    methods: {
        _showAlert(status, message, duration = 5000) {
            Lobibox.notify(status, {
                pauseDelayOnHover: true,
                continueDelayOnInactiveTab: false,
                position: 'top right',
                icon: status == 'success' ? 'bx bx-check-circle' : 'bx bx-error-circle',
                title: status == 'success' ? 'Bien hecho' : 'Error',
                msg: message,
                sound: false,
                delay: duration
            });
        },
        _confirmAccion: async function (title, text, type = "warning") {
            return await Swal.fire({
                title: title ? title : "¿Estas seguro?",
                text: text,
                type: type,
                showCancelButton: !0,
                confirmButtonColor: "#673ab7",
                cancelButtonColor: "#5a7684",
                confirmButtonText: "Confirmar",
                confirmButtonClass: "btn long btn-primary",
                cancelButtonClass: "btn long btn-secondary ml-1",
                cancelButtonText: "Cancelar",
                buttonsStyling: !1,
            }).then((t) => {
                return t;
            });
        }
    },
})

const app = new Vue({
    el: '#app',
});
