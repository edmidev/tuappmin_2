<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Residentes</div>
            
            <div class="ml-auto">
                <residente-modal-crear-editar-component @updateList="_updateList" :attrib="attrib" 
                :auth="auth" :residencia="residencia"></residente-modal-crear-editar-component>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card radius-15">        
            <div class="card-header">
                <form @submit.prevent="_getData(1)" method="GET" class="mt-3">
                    <div class="d-sm-flex justify-content-end align-items-center">
                        <div class="col-lg-5">
                            <!-- Form Group -->
                            <div class="form-group">
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" placeholder="Nombre" v-model="busqueda.name">
                                    <div class="input-group-append" @click="_filtrar(1)">
                                        <button class="btn btn-outline-secondary" type="button" id="button-addon2" title="Filtrar">
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
                <hr/>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col" v-if="residencia.tipo == 'Apartamento'">Bloque</th>
                                <th scope="col" v-if="residencia.tipo == 'Apartamento'">Apartamento</th>
                                <th scope="col" v-if="residencia.tipo == 'Casa'">Número de casa</th>
                                <th scope="col">Email</th>
                                <th scope="col" v-if="auth.rol_id == 1">Residencia</th>
                                <th scope="col">F. creación</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">
                                <th>{{ item.name }}</th>
                                <th v-if="residencia.tipo == 'Apartamento'">{{ item.bloque }}</th>
                                <th v-if="residencia.tipo == 'Apartamento'">{{ item.apartamento }}</th>
                                <th v-if="residencia.tipo == 'Casa'">{{ item.numero }}</th>
                                <td>{{ item.email }}</td>
                                <td v-if="auth.rol_id == 1">{{ item.residencia }}</td>
                                <td>
                                    <dateformat-component :date="item.created_at"></dateformat-component>
                                </td>
                                <td>
                                    <button class="btn btn-outline-warning btn-sm" @click="_edit(item)" title="Modificar"
                                    v-if="auth.rol_id != 1">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <button class="btn btn-outline-primary btn-sm" @click="_edit(item)" title="Ver detalles"
                                    v-else>
                                        <i class="bx bx-show"></i>
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm" title="Eliminar usuario"
                                    @click="_eliminar(item.id)" v-if="auth.rol_id != 1">
                                        <i class="bx bx-trash"></i>
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
            </div>
        </div>
    </div>    
</template>

<script>
    export default {
        props: ['auth', 'residencia'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    name: '',
                    rol_id: '',
                    id: ''
                }
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("residentes/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => { 
                        this.data = response.data.users;
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
            _changeStatus(id) {
                this._confirmAccion(null, "¿Quieres modificar esta información?").then(t => {
                    if (t.value) {
                        axios
                            .post('residentes/change-status/' + id)
                            .then(response => {
                                this._getData();
                            })
                    }
                })
            },
            _eliminar(id) {
                this._confirmAccion(null, "¿Quieres eliminar este residente?").then(t => {
                    if (t.value) {
                        axios
                            .delete('residente/' + id)
                            .then(response => {
                                if (response.status == 201) {
                                    this._showAlert('success', response.data);
                                    this._getData();
                                }
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