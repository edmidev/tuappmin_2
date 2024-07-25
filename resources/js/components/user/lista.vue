<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">{{ auth.rol_id == 1 ? 'Conjuntos residenciales' : 'Usuarios' }}</div>

            <div class="ml-auto">
                <user-modal-crear-editar-component @updateList="_updateList" :attrib="attrib" :auth="auth"></user-modal-crear-editar-component>
            </div>
        </div>
        <!--end breadcrumb-->

        <div class="card radius-15">
            <div class="card-header">
                <form @submit.prevent="_getData(1)" method="GET" class="mt-3">
                    <div class="d-sm-flex justify-content-end align-items-center">
                        <div class="col-lg-3">
                            <!-- Form Group -->
                            <div class="form-group">
                                <select class="form-control" v-model="busqueda.rol_id" v-if="auth.rol_id == 2">
                                    <option value="">Todos los roles</option>
                                    <option value="3">Administrador</option>
                                    <option value="4">Portero</option>
                                </select>
                            </div>
                        </div>

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
                                <th scope="col">Email</th>
                                <th scope="col" v-if="auth.rol_id == 1">NIT</th>
                                <th scope="col" v-if="auth.rol_id != 1">Rol</th>
                                <th scope="col" v-if="auth.rol_id == 1">Tipo</th>
                                <th scope="col">F. creación</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">
                                <th>{{ item.name }}</th>
                                <td>{{ item.email }}</td>
                                <td v-if="auth.rol_id == 1">{{ item.nit }}</td>
                                <td v-if="auth.rol_id != 1">{{ item.rol }}</td>
                                <td v-if="auth.rol_id == 1">{{ item.tipo }}</td>
                                <td>
                                    <dateformat-component :date="item.created_at"></dateformat-component>
                                </td>
                                <td>
                                    <button class="btn btn-outline-warning btn-sm" @click="_edit(item)" title="Modificar">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm" title="Desactivar usuario"
                                    v-if="item.status == 'Activo'" @click="_changeStatus(item.id)">
                                        <i class="bx bx-block"></i>
                                    </button>

                                    <button class="btn btn-outline-success btn-sm" title="Desactivar usuario"
                                    v-if="item.status == 'Desactivado'" @click="_changeStatus(item.id)">
                                        <i class="bx bx-check-circle"></i>
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
        props: ['auth'],
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
                    .get("usuarios/get-all-paginate?page=" + page, {
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
                            .post('usuarios/change-status/' + id)
                            .then(response => {
                                this._getData();
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
