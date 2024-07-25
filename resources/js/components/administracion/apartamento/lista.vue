<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Apartamentos</div>
            
            <div class="ml-auto">
                <apartamento-modal-crear-editar-component @updateList="_updateList" :attrib="attrib" :asset="asset"></apartamento-modal-crear-editar-component>
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
                                    <input type="text" class="form-control" placeholder="Bloque o apartamento" v-model="busqueda.name">
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
                                <th scope="col">Bloque</th>
                                <th scope="col">Apartamento</th>
                                <th scope="col">Propietario</th>
                                <th scope="col">Teléfono</th>
                                <th scope="col">F. creación</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">                            
                                <td>{{ item.bloque }}</td>
                                <td>{{ item.apartamento }}</td>
                                <td>{{ item.nombre_propietario }}</td>
                                <td>{{ item.telefono }}</td>
                                <td>
                                    <dateformat-component :date="item.created_at"></dateformat-component>
                                </td>
                                <td>
                                    <button class="btn btn-outline-warning btn-sm" @click="_edit(item)" title="Modificar">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <button class="btn btn-outline-danger btn-sm" title="Eliminar" @click="_delete(item.id)">
                                        <i class="bx bx-trash-alt"></i>
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
        props: ['asset', 'auth'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    name: '',
                    id: ''
                }
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("apartamentos/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.apartamentos;
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
                            .delete('apartamento/' + id)
                            .then(response => {
                                if(response.status == 201){
                                    this._getData();
                                    this._showAlert('success', response.data);
                                }
                                else{
                                    this._showAlert('warning', response.data);
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