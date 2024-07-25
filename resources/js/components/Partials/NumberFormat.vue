<template>
    <span>{{numberWithPuntos(valor)}}</span>
</template>

<script>
    export default {
        props: ['valor'],
        data() {
            return {
                
            }
        },
        methods: {
            numberWithPuntos(valor) {
                if(valor > 0){
                    return valor.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                }

                return 0;
            }
        },
        mounted() {
            
        }
    }
</script>