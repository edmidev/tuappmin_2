<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Comunicados</div>
            
            <div class="ml-auto">
                <comunicado-modal-crear-editar-component @updateList="_updateList" :attrib="attrib" :asset="asset"></comunicado-modal-crear-editar-component>
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
                                    <input type="text" class="form-control" placeholder="Nombre" v-model="busqueda.titulo">
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
                                <th scope="col">Imagen</th>
                                <th scope="col">Documento</th>
                                <th scope="col">Título</th>
                                <th scope="col">Descripción</th>
                                <th scope="col">F. creación</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">
                                <th>
                                    <img :src="asset + 'storage/comunicados/' + item.image" style="width: 60px;" v-if="item.image">
                                </th>
                                <th>
                                    <a :href="asset + 'storage/comunicados/documentos/' + item.documento" class="btn btn-sm btn-primary"
                                    target="_blank" v-if="item.documento">
                                        Ver
                                    </a>
                                </th>
                                <td>{{ item.titulo }}</td>
                                <td class="descripcion">{{ item.description }}</td>
                                <td>
                                    <dateformat-component :date="item.created_at"></dateformat-component>
                                </td>
                                <td>
                                    <button class="btn btn-outline-warning btn-sm" @click="_edit(item)" title="Modificar">
                                        <i class="bx bx-edit-alt"></i>
                                    </button>

                                    <button class="btn btn-outline-primary btn-sm" @click="_showComments(item)" title="Ver comentarios">
                                        <i class="bx bx-comment"></i>
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

        <comunicado-comentarios-component @updateList2="_updateList2" 
        :attrib="attrib_comentario" :asset="asset"
        :auth="auth"></comunicado-comentarios-component>
    </div>    
</template>

<script>
    export default {
        props: ['asset', 'id', 'auth'],
        data() {
            return {
                data: {},
                attrib: {},
                busqueda: {
                    titulo: '',
                    id: ''
                },
                attrib_comentario: {}
            };
        },
        methods: {
            _getData(page = 1) {
                axios
                    .get("comunicados/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.data = response.data.comunicados;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _updateList() {
                this.attrib = {};
                this._getData();
            },
            _updateList2() {
                this.attrib_comentario = {};
            },
            _edit(object) {
                this.attrib = object;
            },
            _delete(id) {
                this._confirmAccion(null, "¿Quieres eliminar esta información?").then(t => {
                    if (t.value) {
                        axios
                            .delete('comunicado/' + id)
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
            },
            _showComments(object){
                this.attrib_comentario = object;
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