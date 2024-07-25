<template>
    <div>
        <!--breadcrumb-->
        <div class="d-md-flex align-items-center mb-3">
            <div class="breadcrumb-title pr-3">Pagos</div>
            
            <div class="ml-auto">
                <public-service-modal-crear-editar-component @updateList="_updateList" :attrib="attrib" :asset="asset"></public-service-modal-crear-editar-component>
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
                                <label class="form-label">Año de administración</label>
                                <select class="form-control" v-model="busqueda.year" @change="_filtrar()">
                                    <option v-for="(year, key) in years" :key="key">{{ year }}</option>
                                </select>
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3" v-if="conjunto_residencial.tipo == 'Apartamento'">
                            <!-- Form Group -->
                            <div class="form-group">
                                <label class="form-label">Bloque</label>
                                <select class="form-control" v-model="busqueda.bloque" @change="_getApartamentos()">
                                    <option v-for="(item, key) in bloques" :key="key" :value="item.bloque">{{ item.bloque }}</option>
                                </select>
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3" v-if="conjunto_residencial.tipo == 'Apartamento'">
                            <!-- Form Group -->
                            <div class="form-group">
                                <label class="form-label">Apartamento</label>
                                <select class="form-control" v-model="busqueda.apartamento" @change="_filtrar()">
                                    <option v-for="(item, key) in apartamentos" :key="key" :value="item.id">{{ item.apartamento }}</option>
                                </select>
                            </div>
                            <!-- End Form Group -->
                        </div>

                        <div class="col-lg-3" v-if="conjunto_residencial.tipo == 'Casa'">
                            <!-- Form Group -->
                            <div class="form-group">
                                <label class="form-label">Casa</label>
                                <select class="form-control" v-model="busqueda.casa" @change="_filtrar()">
                                    <option v-for="(item, key) in casas" :key="key" :value="item.id">{{ item.numero }}</option>
                                </select>
                            </div>
                            <!-- End Form Group -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-body">            
                <hr/>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Residente</th>
                                <th scope="col" v-for="(item, key) in months" :key="key">
                                    {{ item }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(item, key) in data.data" :key="key">
                                <td>{{ item.name }}</td>

                                <td v-for="(i, k) in item.administraciones_pagos" :key="k">
                                    <div v-if="i.id" @click="_showDetallesAttrib(i.id)">
                                        <div v-if="i.pagado" title="Pagado">
                                            <img :src="asset + 'images/icon_check.png'" style="width: 28px;">
                                        </div>
                                        <div v-else title="En revisión">
                                            <img :src="asset + 'images/icon_pending.png'" style="width: 28px;">
                                        </div>
                                    </div>

                                    <div v-if="i.mora" title="En mora">
                                        <img :src="asset + 'images/icon_warning.png'" style="width: 20px;">
                                    </div>

                                    <!--<button class="btn btn-info btn-sm" v-if="!i.pagado && !i.id && i.correspondido"
                                    style="font-size: 11px; padding: 5px;">
                                        Pagar
                                    </button>-->
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

        <detalles-pagos-component :asset="asset" @updateList2="_updateList2" :attrib="attrib_detalles"></detalles-pagos-component>
    </div>    
</template>

<script>
    export default {
        props: ['asset', 'conjunto_residencial', 'years', 'year_actual', 'request'],
        data() {
            return {
                data: {},
                attrib: {},
                attrib_detalles: {},
                busqueda: {
                    year: !this.request.year ? this.year_actual : this.request.year,
                    bloque: '',
                    apartamento: '',
                    casa: ''
                },
                bloques: [],
                apartamentos: [],
                casas: [],
                months: [
                    'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul',
                    'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
                ]
            };
        },
        methods: {
            _getData(page = 1) {
                if(this.conjunto_residencial.tipo == 'Apartamento'){
                    if(!this.busqueda.year || !this.busqueda.bloque || !this.busqueda.apartamento){
                        return;
                    }
                }
                else{
                    if(!this.busqueda.year || !this.busqueda.casa){
                        return;
                    }
                }

                axios
                    .get("pagos/get-all-paginate?page=" + page, {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        if(response.status == 201){
                            this.data = response.data.residentes;
                        }
                        else{
                            this.data = {};
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getApartamentosBloques() {
                axios
                    .get("apartamentos/get_all_group_by_bloques")
                    .then((response) => {
                        this.bloques = response.data.apartamentos;
                        if(this.bloques.length > 0){
                            if(!this.request.bloque){
                                this.busqueda.bloque = this.bloques[0].bloque;
                            }
                            else{
                                this.busqueda.bloque = this.request.bloque;
                            }

                            this._getApartamentos();
                        }
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getCasas(){
                axios
                    .get("casas/get-all")
                    .then((response) => {
                        this.casas = response.data.casas;
                        if(this.casas.length > 0){
                            if(!this.request.casa){
                                this.busqueda.casa = this.casas[0].id;
                            }
                            else{
                                this.busqueda.casa = this.request.casa;
                            }
                            this._filtrar();
                        }
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
                this.attrib_detalles = {};
                this._getData();
            },
            _filtrar(){
                this.busqueda.id = null;
                this._getData(1);
            },
            _getApartamentos(){
                for (let index = 0; index < this.bloques.length; index++) {
                    if(this.busqueda.bloque == this.bloques[index].bloque){
                        this.apartamentos = this.bloques[index].apartamentos;
                    }
                }

                if(this.apartamentos.length > 0){
                    if(!this.request.apartamento){
                        this.busqueda.apartamento = this.apartamentos[0].id;
                    }
                    else{
                        this.busqueda.apartamento = this.request.apartamento;
                    }

                    this._filtrar();
                }
            },
            _showDetallesAttrib(id){
                this.attrib_detalles = {
                    id
                }
            }
        },
        mounted() {
            if(this.conjunto_residencial.tipo == 'Apartamento'){
                this._getApartamentosBloques();
            }
            else{
                this._getCasas();
            }
        },
    };
</script>