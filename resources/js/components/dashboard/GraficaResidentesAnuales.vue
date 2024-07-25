<template>
    <div class="card radius-15">
        <div class="card-header border-bottom-0">
            <div class="d-lg-flex align-items-center">
                <div>
                    <h5 class="mb-2 mb-lg-0">Registros de residentes</h5>
                </div>
                <div class="ml-lg-auto mb-2 mb-lg-0">
                    
                </div>
            </div>
        </div>

        <div class="card-body">
            <div id="grafica_registros_residentes"></div>
        </div>
    </div>
</template>

<script>
    export default {
        props: [],
        data() {
            return {
                data: {}
            };
        },
        methods: {
            async _getData() {
                return await axios
                    .get("dashboard/get_residentes_anuales", {
                        params: this.busqueda,
                    })
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            async _inicializar(){
                var response = await this._getData();
                this.data = response.data.data;
                this._renderGrafica();
            },
            _renderGrafica(){
                var data = [];
                var serie = {
                    name: '',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                };

                this.data.map((item, index) => {
                    if(index == 0){
                        serie.name = item.year;
                    }

                    if(index > 0 && item.year != this.data[index - 1].year){
                        data.push(serie);
                        serie = {
                            name: item.year,
                            data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                        };

                        serie.data[item.month - 1] = item.cantidad_registros;
                    }
                    else{
                        serie.data[item.month - 1] = item.cantidad_registros;
                    }

                    if(this.data.length == index + 1){
                        data.push(serie);
                    }
                })
                
                var options = {
                    series: data,
                    chart: {
                        foreColor: '#9ba7b2',
                        type: 'area',
                        height: 340,
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        dropShadow: {
                            enabled: false,
                            top: 3,
                            left: 14,
                            blur: 4,
                            opacity: 0.10,
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'left',
                        offsetX: -25
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2.3,
                        curve: 'smooth'
                    },
                    tooltip: {
                        theme: 'dark',
                        y: {
                            formatter: function (val) {
                                return val + " "
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'light',
                            shadeIntensity: 1,
                            type: 'vertical',
                            inverseColors: false,
                            opacityFrom: 0.4,
                            opacityTo: 0.1,
                            //stops: [0, 50, 65, 91]
                        },
                    },
                    grid: {
                        show: true,
                        borderColor: '#f8f8f8',
                        strokeDashArray: 5,
                    },
                    yaxis: {
                        labels: {
                            formatter: function (value) {
                                return value;
                            }
                        },
                    },
                    xaxis: {
                        categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                    }
                };

                var chart = new ApexCharts(document.querySelector("#grafica_registros_residentes"), options);
                chart.render();
            }
        },
        mounted() {
            this._inicializar();
        },
    };
</script>