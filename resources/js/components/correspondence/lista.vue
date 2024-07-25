<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Correspondencias</div>

            <div class="ml-auto">
                <form :action="'correspondences/export'" method="POST">
                    <input type="hidden" name="_token" :value="csrf">
                    <input type="hidden" name="residente" :value="busqueda.residente">
                    <button type="submit" class="btn btn-primary">Exportar</button>
                </form>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card radius-15">        
            <div class="card-header">
                <form @submit.prevent="_filtrar(1)" method="GET" class="mt-3">
                    <div class="d-sm-flex justify-content-end align-items-center">
                        <div class="col-lg-5">
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
                                <th scope="col">Tipo</th>
                                <th scope="col">Servicio público</th>
                                <th scope="col">Residente</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Observación</th>
                                <th scope="col">Fecha de entrada</th>
                                <th scope="col">Fecha de entrega</th>
                                <th scope="col">Usuario ingreso</th>
                                <th scope="col">Usuario salida</th>
                                <th scope="col">Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">                            
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.bloque }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.apartamento }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Casa'">{{ item.numero }}</td>
                                <td>{{ tipos[item.correspondence_type - 1] }}</td>
                                <td>
                                    <div>
                                        <img :src="asset + 'storage/public_services/' + item.image"
                                        style="width: 50px;" class="mr-2" v-if="item.image">

                                        <span v-if="item.public_service_name">{{ item.public_service_name }}</span>
                                    </div>
                                </td>
                                <td>{{ item.residente }}</td>
                                <td>
                                    <a :href="asset + 'storage/fotos_paquetes/' + item.imagen" v-if="item.imagen" target="_blank">
                                        <img :src="asset + 'storage/fotos_paquetes/' + item.imagen" style="width: 70px;">
                                    </a>
                                </td>
                                <td>{{ item.observacion }}</td>
                                <td><dateformat-component :date="item.created_at"></dateformat-component></td>
                                <td><dateformat-component :date="item.fecha_entregado"></dateformat-component></td>
                                <td>{{ item.usuario_ingreso }}</td>
                                <td>{{ item.usuario_salida }}</td>
                                <td>
                                    <span class="badge badge-warning" style="font-size: .8em;" v-if="!item.fecha_entregado">En porteria</span>
                                    <span class="badge badge-success" style="font-size: .8em;" v-else>Entregado</span>
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
            </div>
        </div>
    </div>    
</template>

<script>
    export default {
        props: ['asset', 'conjunto_residencial'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    name: '',
                    id: ''
                },
                tipos: [
                    'Servicio público',
                    'Correspondencia',
                    'Paquetería'
                ],
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("correspondences/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.correspondences;
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
                            .delete('correspondences/' + id)
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