<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Parqueadero</div>
            
            <div class="ml-auto">
                <form :action="'parkings/export'" method="POST">
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="numero_parking" :value="busqueda.numero_parking">
                    <input type="hidden" name="placa" :value="busqueda.placa">
                    <button type="submit" class="btn btn-primary">Exportar</button>
                </form>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card radius-15">        
            <div class="card-header">
                <form @submit.prevent="_filtrar(1)" method="GET" class="mt-3">
                    <div class="d-sm-flex justify-content-end align-items-center">                    
                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Número de parqueadero" v-model="busqueda.numero_parking">
                                </div>                            
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Placas" v-model="busqueda.placa">
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
                                <th scope="col">Numero de Parqueadero</th>
                                <th scope="col" v-if="conjunto_residencial.tipo == 'Apartamento'">Bloque</th>
                                <th scope="col" v-if="conjunto_residencial.tipo == 'Apartamento'">Apartamento</th>
                                <th scope="col" v-if="conjunto_residencial.tipo == 'Casa'">Casa</th>
                                <th scope="col">Residente</th>
                                <th scope="col">Placa del vehículo</th>
                                <th scope="col">Tipo de vehículo</th>
                                <th scope="col">Jornada</th>
                                <th scope="col">Fecha y hora de ingreso</th>
                                <th scope="col">Fecha y hora de salida</th>
                                <th scope="col">Usuario ingreso</th>
                                <th scope="col">Usuario salida</th>
                                <th scope="col">Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">                            
                                <td>{{ item.numero_parking }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.bloque }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.apartamento }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Casa'">{{ item.numero }}</td>
                                <td>{{ item.residente }}</td>
                                <td>{{ item.placa }}</td>
                                <td>{{ item.tipo_vehiculo == '1' ? 'Moto' : 'Automóvil' }}</td>
                                <td>{{ jornadas[item.jornada - 1] }}</td>
                                <td><dateformat-component :date="item.fecha_ingreso"></dateformat-component></td>
                                <td><dateformat-component :date="item.fecha_salida"></dateformat-component></td>
                                <td>{{ item.usuario_ingreso }}</td>
                                <td>{{ item.usuario_salida }}</td>
                                <td v-if="item.fecha_salida">$<number-format-component :valor="item.total"></number-format-component></td>
                                <td v-else></td>
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
    </div>    
</template>

<script>
    export default {
        props: ['conjunto_residencial'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    name: '',
                    id: ''
                },
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                jornadas: [
                    'Por hora', 'Diurna', 'Nocturna',
                    'Completa'
                ]
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("parkings/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.parkings;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _updateList() {
                this.attrib = {};
                this._getData();
            },
            _edit(object) {
                this.attrib = object;
            },
            _delete(id) {
                this._confirmAccion(null, "¿Quieres eliminar esta información?").then(t => {
                    if (t.value) {
                        axios
                            .delete('parkings/' + id)
                            .then(response => {
                                this._getData();
                                this._showAlert('success', response.data);
                            })
                    }
                })
            },
            _filtrar(){
                this.busqueda.id = null;
                this._getData(1);
            }
        },
        mounted() {
            this._getData();
        },
    };
</script>