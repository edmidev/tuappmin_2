<template>
    <div class="card-deck">
        <div class="card radius-15">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div>
                        <h5 class="mb-0">($) Ingresos de administracion</h5>
                    </div>
                    <div class="dropdown ml-auto">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                            <button type="button" class="btn btn-secondary" @click="_minYear"><i
                                    class="bx bx-chevron-left"></i></button>
                            <button type="button" class="btn btn-default" disabled>{{ year }}</button>
                            <button type="button" class="btn btn-secondary" @click="_addYear"><i
                                    class="bx bx-chevron-right"></i></button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3" v-if="prop_data.year_actual == year">
                    <div class="col-12 col-lg-6" v-if="prop_data.month_actual > 1">
                        <div class="card radius-15 mx-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0">{{ months[prop_data.month_actual - 2] }}</p>
                                    </div>
                                </div>
                                <h4 class="mb-0">
                                    $<number-format-component :valor="total_year_anterior"></number-format-component>
                                </h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="card radius-15 mx-0">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <p class="mb-0">{{ months[prop_data.month_actual - 1] }}</p>
                                    </div>
                                    <div class="ml-auto" :class="[{'text-danger': _getIncremento() < 0, 'text-success': _getIncremento() > 0}]"
                                        v-if="Number.isFinite(_getIncremento())">
                                        <span>{{ _getIncremento() > 0 ? '+' : '' }}{{ _getIncremento() }}%</span>
                                    </div>
                                </div>
                                <h4 class="mb-0">
                                    $<number-format-component :valor="total_year_actual"></number-format-component>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="grafica_ingresos_administracion"></div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['prop_data'],
        data() {
            return {
                data: {},
                year: this.prop_data.year_actual,
                total_year_actual: 0,
                total_year_anterior: 0,
                months: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                chart: null
            };
        },
        methods: {
            async _getData() {
                return await axios
                    .get("dashboard/get_ingresos_administracion", {
                        params: {
                            year: this.year
                        },
                    })
                    .then((response) => {
                        return response;
                    })
                    .catch((err) => {
                        console.log(err);
                    });
            },
            _addYear() {
                this.year++;
                this._inicializar();
            },
            _minYear() {
                this.year--;
                this._inicializar();
            },
            _getIncremento(){
                return parseFloat((((this.total_year_actual - this.total_year_anterior) / this.total_year_anterior) * 100).toFixed(2));
            },
            async _inicializar() {
                var response = await this._getData();
                this.data = response.data.data;
                this._renderGrafica();
            },
            _renderGrafica() {
                var data = [{
                    name: 'Total',
                    data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                }];

                for (let index = 0; index < 12; index++) {
                    for (let j = 0; j < this.data.length; j++) {
                        if (index + 1 == parseInt(this.data[j].month)) {
                            data[0].data[index] = this.data[j].total;
                            break;
                        }
                    }
                }

                /** Obtenemos el total del mes actual */
                for (let index = 0; index < this.data.length; index++) {
                    if (parseInt(this.prop_data.month_actual) == parseInt(this.data[index].month)) {
                        this.total_year_actual = this.data[index].total;
                        break;
                    }
                }

                /** Obtenemos el total del mes anterior */
                for (let index = 0; index < this.data.length; index++) {
                    if (parseInt(this.prop_data.month_actual - 1) == parseInt(this.data[index].month)) {
                        this.total_year_anterior = this.data[index].total;
                        break;
                    }
                }

                this.chart.updateSeries(data);
            }
        },
        mounted() {
            var options1 = {
                chart: {
                    foreColor: '#9a9797',
                    type: 'area',
                    //width: 170,
                    height: 200,
                    sparkline: {
                        enabled: false
                    },
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
                dataLabels: {
                    enabled: false
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        shade: 'light',
                        gradientToColors: ['#623cea'],
                        shadeIntensity: 1,
                        type: 'vertical',
                        opacityFrom: 0.4,
                        opacityTo: 0.1,
                        //stops: [0, 100, 100, 100]
                    },
                },
                colors: ["#623cea"],
                series: [],
                stroke: {
                    width: 2.5,
                    curve: 'smooth',
                    dashArray: [0]
                },
                grid: {
                    show: true,
                    borderColor: '#f8f8f8',
                    strokeDashArray: 5,
                },
                yaxis: {
                    show: false,
                },
                xaxis: {
                    categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                },
                tooltip: {
                    theme: 'dark',
                    fixed: {
                        enabled: false
                    },
                    x: {
                        show: false
                    },
                    y: {
                        title: {
                            formatter: function (seriesName) {
                                return ''
                            }
                        }
                    },
                    marker: {
                        show: false
                    }
                }
            }

            this.chart = new ApexCharts(document.querySelector("#grafica_ingresos_administracion"), options1);
            this.chart.render();

            this._inicializar();
        },
    };
</script>