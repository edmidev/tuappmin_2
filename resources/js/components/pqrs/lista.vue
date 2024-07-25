<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">PQRS</div>
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
                                    <select class="form-control" v-model="busqueda.tipo" @change="_filtrar(1)">
                                        <option value="">Todos los tipos</option>
                                        <option v-for="(item, key) in tipos" :key="key" :value="key + 1">{{ item }}</option>
                                    </select>
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
                                <th scope="col">Tipo</th>
                                <th scope="col">Residente</th>
                                <th scope="col">Foto</th>
                                <th scope="col">Motivo</th>
                                <th scope="col">Fecha de creación</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">                            
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.bloque }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Apartamento'">{{ item.apartamento }}</td>
                                <td v-if="conjunto_residencial.tipo == 'Casa'">{{ item.numero }}</td>
                                <td>{{ tipos[item.tipo - 1] }}</td>
                                <td>{{ item.residente }}</td>
                                <td>
                                    <a :href="asset + 'storage/fotos_pqrs/' + item.image" target="_blank" v-if="item.image">
                                        <img :src="asset + 'storage/fotos_pqrs/' + item.image" style="width: 70px;">
                                    </a>
                                </td>
                                <td class="motivo">{{ item.motivo }}</td>
                                <td><dateformat-component :date="item.created_at"></dateformat-component></td>
                                <td>
                                    <span class="badge badge-warning" style="font-size: .8em;" v-if="item.estatus == 1">Pendiente</span>
                                    <span class="badge badge-success" style="font-size: .8em;" v-else>Finalizado</span>
                                </td>
                                <td>
                                    <button class="btn btn-warning btn-sm" @click="_verDetalles(item)" title="Ver detalles">
                                        Detalles
                                    </button>

                                    <form :action="url + '/pqrss/' + item.id + '/chat'" method="POST" class="d-inline-block">
                                        <input type="hidden" name="_token" :value="csrf">

                                        <button type="submit" class="btn btn-secondary btn-sm" title="Ver respuestas">
                                            <i class="bx bx-message-dots"></i>
                                        </button>
                                    </form>

                                    <button class="btn btn-outline-success btn-sm" @click="_finalizar(item.id)" title="Finalizar PQRS"
                                        v-if="item.estatus == 1">
                                        <i class="bx bx-check"></i>
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

                <pqrs-detalles-component @updateList="_updateList" :attrib="attrib" :asset="asset" :conjunto_residencial="conjunto_residencial"></pqrs-detalles-component>
            </div>
        </div>
    </div>    
</template>

<script>
    export default {
        props: ['url', 'asset', 'conjunto_residencial', 'id'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    name: '',
                    id: '',
                    tipo: ''
                },
                tipos: [
                    'Petición',
                    'Queja',
                    'Reclamo',
                    'Sugerencia',
                ],
                csrf: document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("pqrss/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.pqrs;
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
            _edit(object) {
                this.attrib = object;
            },
            _finalizar(id) {
                this._confirmAccion(null, "¿Quieres finalizar esta PQRS?").then(t => {
                    if (t.value) {
                        axios
                            .post('pqrss/finalizar/' + id)
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
            if(this.id){
                this.busqueda.id = this.id;
            }
            
            this._getData();
        },
    };
</script>