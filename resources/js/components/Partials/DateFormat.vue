<template>
    <span v-if="date">{{date | moment}}</span>
</template>

<script>
    import moment from 'moment'

    export default {
        props: ['date'],
        data() {
            return {
                
            }
        },       
        filters: {
            moment: function (date) {
                return moment(date).calendar();
            }
        }
    }
</script>