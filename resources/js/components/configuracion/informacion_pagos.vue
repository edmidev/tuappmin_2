<template>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Año de administración</label>
                    <select class="form-control" name="year" v-model="form.year" @change="_getValores()">
                        <option v-for="(year, key) in years" :key="key">{{ year }}</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Valor administración</label>
                    <input type="number" class="form-control" name="valor_administracion" v-model="form.valor_administracion" 
                    step=".01" required>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Día límite de pago</label>
                    <select class="form-control" name="limite_pago" v-model="form.limite_pago" required>
                        <option value="">Elige una opción</option>
                        <option value="1">Primer día del mes</option>
                        <option value="2">Mediados del mes</option>
                        <option value="3">Último día del mes</option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Porcentaje interés mora(%)</label>
                    <input type="number" class="form-control" name="interes_mora" step="0.01" v-model="form.interes_mora">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Descuento pronto pago(%)</label>
                    <input type="number" class="form-control" name="descuento_pronto_pago" step="0.01" v-model="form.descuento_pronto_pago">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Límite de pronto pago</label>
                    <select name="limite_pronto_pago" v-model="form.limite_pronto_pago" class="form-control">
                        <option value="">Sin límite</option>
                        <option v-for="(item, key) in 26" :key="key" :value="item">
                            {{ item }} {{ item == 1 ? 'día' : 'días' }} antes de la fecha de pago
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Descuento por pago de semestre(%)</label>
                    <input type="number" class="form-control" name="descuento_pago_semestre" step="0.01" v-model="form.descuento_pago_semestre">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Descuento por pago anual(%)</label>
                    <input type="number" class="form-control" name="descuento_pago_anual" step="0.01" v-model="form.descuento_pago_anual">
                </div>
            </div>
        </div>
    </div>  
</template>

<script>
    export default {
        props: ['informacion_pagos', 'years', 'year_actual'],
        data() {
            return {
                form: {
                    year: this.year_actual,
                    limite_pronto_pago: ''
                }
            };
        },
        methods: {
            _getValores(){
                var existente = false;
                for (let index = 0; index < this.informacion_pagos.length; index++) {
                    if(this.form.year == this.informacion_pagos[index].year){
                        const item = this.informacion_pagos[index];
                        this.form = {
                            year: item.year,
                            valor_administracion: item.valor_administracion,
                            limite_pago: item.limite_pago,
                            interes_mora: item.interes_mora,
                            descuento_pronto_pago: item.descuento_pronto_pago,
                            limite_pronto_pago: item.limite_pronto_pago ? item.limite_pronto_pago : '',
                            descuento_pago_semestre: item.descuento_pago_semestre,
                            descuento_pago_anual: item.descuento_pago_anual,
                        }

                        existente = true;
                        break;
                    }
                }

                if(!existente){
                    const year = this.form.year;
                    this.form = {
                        year: year,
                        valor_administracion: '',
                        limite_pronto_pago: '',
                        limite_pago: '',
                        interes_mora: '',
                        descuento_pronto_pago: '',
                        limite_pronto_pago: '',
                        descuento_pago_semestre: '',
                        descuento_pago_anual: '',
                    }
                }
            }
        },
        mounted() {
            this._getValores();
        },
    };
</script>