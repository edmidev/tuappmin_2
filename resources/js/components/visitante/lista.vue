<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Visitantes</div>

            <div class="ml-auto">
                <form :action="'visitantes/export'" method="POST">
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="fecha_inicio" :value="busqueda.fecha_inicio">
                    <input type="hidden" name="fecha_fin" :value="busqueda.fecha_fin">
                    <input type="hidden" name="tipo" :value="busqueda.tipo">
                    <input type="hidden" name="name" :value="busqueda.name">
                    <input type="hidden" name="numero_documento" :value="busqueda.numero_documento">
                    <input type="hidden" name="residente" :value="busqueda.residente">
                    <button type="submit" class="btn btn-primary">Exportar</button>
                </form>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card radius-15">        
            <div class="card-header">
                <form @submit.prevent="_filtrar(1)" method="GET" class="mt-3">
                    <div class="d-sm-flex justify-content-end align-items-center flex-wrap">
                        <div class="col-lg-6">
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

                        <div class="col-lg-6">
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

                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <select class="form-control" v-model="busqueda.tipo" @change="_filtrar(1)">
                                        <option value="">Todos</option>
                                        <option v-for="(value, key) in tipos" :key="key" :value="key + 1">
                                            {{ value }}
                                        </option>
                                    </select>
                                </div>                            
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Nombre del visitante" v-model="busqueda.name">
                                </div>                            
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Número de documento" v-model="busqueda.numero_documento">
                                </div>                            
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Residente" v-model="busqueda.residente">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="submit" id="button-addon2" title="Filtrar"
                                        @click="_filtrar(1)">
                                            <i class="bx bx-search"></i>
                                        </button>
                                    </div>
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
                                <th scope="col" v-if="conjunto_residencial.tipo == 'Apartamento'">Bloque</th>
                                <th scope="col" v-if="conjunto_residencial.tipo == 'Apartamento'">Apartamento</th>
                                <th scope="col" v-if="conjunto_residencial.tipo == 'Casa'">Casa</th>
                                <th scope="col">Residente</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Visitante</th>
                                <th scope="col">Fecha de entrada</th>
                                <th scope="col">Fecha de salida</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">                            
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.bloque }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.apartamento }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Casa'">{{ item.numero }}</td>
                                <td>{{ item.residente }}</td>
                                <td>{{ tipos[item.tipo - 1] }}</td>
                                <td>
                                    <div>
                                        <a :href="asset + 'storage/fotos_visitantes/' + item.imagen" target="_blank" v-if="item.imagen">
                                            <img :src="asset + 'storage/fotos_visitantes/' + item.imagen"
                                            style="width: 50px;" class="mr-2">
                                        </a>

                                        <span v-if="item.name">{{ item.name }}</span>
                                    </div>
                                </td>
                                <td><dateformat-component :date="item.fecha_ingreso"></dateformat-component></td>
                                <td><dateformat-component :date="item.fecha_salida"></dateformat-component></td>
                                <td>
                                    <span class="badge badge-secondary" style="font-size: .8em;" v-if="item.estatus_acceso == 1">
                                        {{ estatus[item.estatus_acceso - 1] }}
                                    </span>

                                    <span class="badge badge-danger" style="font-size: .8em;" v-if="item.estatus_acceso == 2">
                                        {{ estatus[item.estatus_acceso - 1] }}
                                    </span>

                                    <span class="badge badge-success" style="font-size: .8em;" v-if="item.estatus_acceso == 3">
                                        {{ estatus[item.estatus_acceso - 1] }}
                                    </span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm" @click="_verDetalles(item)" title="Ver detalles">
                                        Detalles
                                    </button>
                                </td>
                            </tr>                    
                        </tbody>
                    </table>                
                </div>

                <div class="card-body">
                    <div class="d-sm-flex justify-content-end align-items-center">
                        <pagination :data="data" @pagination-change-page="_getData"></pagination>
                    </div>
                </div>

                <visitante-detalles-component @updateList="_updateList" :attrib="attrib" :asset="asset"></visitante-detalles-component>
            </div>
        </div>
    </div>    
</template>

<script>
    import moment from 'moment';
    export default {
        props: ['asset', 'conjunto_residencial'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    fecha_inicio: null,
                    fecha_fin: moment(new Date()).format('YYYY-MM-DD'),
                    name: '',
                    id: '',
                    tipo: '',
                },
                tipos: [
                    'Visitante', 'Domiciliario', 'Técnico'
                ],
                estatus: [
                    'Esperando autorización',
                    'Denegado', 'Permitido'
                ],
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("visitantes/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.visitantes;
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
            }
        },
        mounted() {
            var date = new Date();
            this.busqueda.fecha_inicio = date.getFullYear() + "-" + ("00" + (date.getMonth() + 1)).slice(-2) + "-01";

            this._getData();
        },
    };
</script>