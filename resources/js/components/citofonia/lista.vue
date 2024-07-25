<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Citofonias</div>

            <div class="ml-auto">
                <form :action="'citofonias/export'" method="POST">
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
                                <th scope="col">Residente</th>
                                <th scope="col">Fecha de entrada</th>
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
                                <td><dateformat-component :date="item.fecha_ingreso"></dateformat-component></td>
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
                                        <i class="bx bx-eye"></i> Detalles
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

                <citofonia-detalles-component @updateList="_updateList" :attrib="attrib" :asset="asset"></citofonia-detalles-component>
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
                    .get("citofonias/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.citofonias;
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
            this._getData();
        },
    };
</script>