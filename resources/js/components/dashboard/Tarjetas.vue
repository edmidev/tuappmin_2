<template>
    <div class="row">
        <div class="col-12 col-lg-3" v-if="auth.rol_id == 1">
            <div class="card radius-15 bg-voilet">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h2 class="mb-0 text-white" 
                            v-if="cantidad_conjuntos_apartamentos && cantidad_conjuntos_casas">
                                {{ cantidad_conjuntos_apartamentos + cantidad_conjuntos_casas }}
                            </h2>
                        </div>
                        <div class="ml-auto font-35 text-white"><i class="bx bxs-home-circle"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Conjuntos residenciales</p>
                        </div>

                        <div class="ml-auto font-14 text-white">Totales</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3" v-if="auth.rol_id == 1">
            <div class="card radius-15 bg-rose">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h2 class="mb-0 text-white">
                                {{ cantidad_conjuntos_apartamentos }}
                            </h2>
                        </div>
                        <div class="ml-auto font-35 text-white"><i class="bx bxs-city"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Conjuntos residenciales</p>
                        </div>
                        <div class="ml-auto font-14 text-white">Aptos</div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-12 col-lg-3" v-if="auth.rol_id == 1">
            <div class="card radius-15 bg-sunset">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h2 class="mb-0 text-white">
                                {{ cantidad_conjuntos_casas }}
                            </h2>
                        </div>
                        <div class="ml-auto font-35 text-white"><i class="bx bx-home-smile"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Conjuntos residenciales</p>
                        </div>
                        <div class="ml-auto font-14 text-white">Casas</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-3">
            <div class="card radius-15 bg-primary-blue">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div>
                            <h2 class="mb-0 text-white">{{ cantidad_residentes }} </h2>
                        </div>
                        <div class="ml-auto font-35 text-white"><i class="bx bx-user"></i>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <div>
                            <p class="mb-0 text-white">Residentes</p>
                        </div>
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
                cantidad_conjuntos_apartamentos: null,
                cantidad_conjuntos_casas: null,
                cantidad_residentes: null,
            };
        },
        methods: {
            _getCantidadConjuntosApartamentos() {
                axios
                    .get("dashboard/count_conjuntos_residenciales", {
                        params: {
                            tipo: 'Apartamento'
                        },
                    })
                    .then((response) => {
                        this.cantidad_conjuntos_apartamentos = response.data.cantidad_conjuntos_residenciales;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getCantidadConjuntosCasas() {
                axios
                    .get("dashboard/count_conjuntos_residenciales", {
                        params: {
                            tipo: 'Casa'
                        },
                    })
                    .then((response) => {
                        this.cantidad_conjuntos_casas = response.data.cantidad_conjuntos_residenciales;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _getCantidadResidentes() {
                axios
                    .get("dashboard/count_cantidad_residentes", {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        this.cantidad_residentes = response.data.cantidad_residentes;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
        },
        mounted() {
            if(this.auth.rol_id == 1){
                this._getCantidadConjuntosApartamentos();
                this._getCantidadConjuntosCasas();
            }

            this._getCantidadResidentes();
        },
    };
</script>