<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Minutas</div>
        </div>
        <!--end breadcrumb-->

        <div class="card radius-15">        
            <div class="card-header">
                <form @submit.prevent="_filtrar(1)" method="GET" class="mt-3">
                    <div class="d-sm-flex justify-content-end align-items-center flex-wrap">
                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <label>Fecha inicio</label>
                                <div class="input-group mb-3">
                                    <input type="date" class="form-control" v-model="busqueda.fecha_inicio"
                                    @change="_filtrar(1)">
                                </div>                            
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <label>Fecha fin</label>
                                <div class="input-group mb-3">
                                    <input type="date" class="form-control" v-model="busqueda.fecha_fin"
                                    @change="_filtrar(1)">
                                </div>                            
                            </div>
                            <!-- End Form Group -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Portero</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Audio</th>
                                <th scope="col">Fecha de creación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">                            
                                <td>{{ item.portero }}</td>
                                <td>{{ item.descripcion }}</td>
                                <td>
                                    <div v-if="item.foto">
                                        <a :href="asset + 'storage/minuta/fotos/' + item.foto" target="_blank">
                                            <img :src="asset + 'storage/minuta/fotos/' + item.foto"
                                            style="width: 50px;" class="mr-2">
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div class="player-cl" v-if="item.audio">
                                        <div class='player'>
                                            <div class="botones-player">
                                                <div v-show="!isPlay || isKeyPlay != key">
                                                    <img :src="asset + 'images/icon_play.webp'"
                                                    title="Reproducir audio" @click="play_audio(item.audio, key)" />
                                                </div>
                                                <div v-show="isPlay && isKeyPlay == key">
                                                    <img :src="asset + 'images/icon_pause.webp'" 
                                                    @click="pause_audio(key)" />
                                                </div>
                                                <div v-show="isPlay && isKeyPlay == key">
                                                    <img :src="asset + 'images/icon_stop.webp'" @click="stop_audio(key)" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td><dateformat-component :date="item.created_at"></dateformat-component></td>
                            </tr>                    
                        </tbody>
                    </table>                
                </div>

                <div class="card-body">
                    <div class="d-sm-flex justify-content-end align-items-center">
                        <pagination :data="data" @pagination-change-page="_getData"></pagination>
                    </div>
                </div>
            </div>
        </div>

        <audio id="music_player">

        </audio>
    </div>    
</template>

<script>
    import moment from 'moment';
    export default {
        props: ['asset'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    fecha_inicio: null,
                    fecha_fin: moment(new Date()).format('YYYY-MM-DD'),
                    name: '',
                    id: '',
                },
                isPlay: false,
                isKeyPlay: null,
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("minutas/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.minutas;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _updateList() {
                this.attrib = {};
                this._getData();
            },
            _verDetalles(object) {
                this.attrib = object;
            },
            _filtrar(){
                this.busqueda.id = null;
                this._getData(1);
            },
            play_audio(audio, key){
                $("#music_player").attr('src', this.asset + 'storage/minuta/audios/' + audio);
                $("#music_player")[0].play();
                this.isKeyPlay = key;
                this.isPlay = true;
            },
            pause_audio(key){
                $("#music_player")[0].pause();
                this.isPlay = false;
            },
            stop_audio(key){
                $("#music_player")[0].pause();
                $("#music_player")[0].currentTime = 0;
                this.isPlay = false;
            },
        },
        mounted() {
            var audio = $("#music_player")[0];
            audio.onended = () => {
                this.isKeyPlay = null;
                this.isPlay = false;
            };

            var date = new Date();
            this.busqueda.fecha_inicio = date.getFullYear() + "-" + ("00" + (date.getMonth() + 1)).slice(-2) + "-01";

            this._getData();
        },
    };
</script>