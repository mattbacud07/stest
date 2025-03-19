<template>
    <v-card flat style="padding-top: 1em;" class="mb-7 pa-5 mt-5">
        <v-row class="d-flex justify-space-between">
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Institution</p>
                <p class="text-grey-darken-1 textTransform">{{ institution?.name?.toLowerCase() ?? '---' }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Address</p>
                <p class="text-grey-darken-1 textTransform">{{ institution?.address.toLowerCase() }}</p>
            </v-col>
        </v-row>
        <v-row>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Equipment</p>
                <p class="text-grey-darken-1">{{ equipment?.equipment }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Serial</p>
                <p class="text-grey-darken-1">{{ equipment?.serial }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Frequency</p>
                <p class="text-grey-darken-1">{{ equipment?.frequency }}</p>
            </v-col>
        </v-row>
        <v-row>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Scheduled</p>
                <p class="text-grey-darken-1">{{ customer_details?.scheduled_at }}</p>
            </v-col>
        </v-row>
    </v-card>
</template>


<script setup>
import { toRefs, computed } from 'vue';

const props = defineProps({
    customer_details : {
        type : Object,
        default : () => ({})
    }
})

const { customer_details } = toRefs(props)

const equipment = computed(() => {
    return customer_details.value?.equipment
})
const institution = computed(() => {
    return customer_details.value?.institution
})

</script>


<style scoped>
.vCheckbox {
    height: 50px !important;
}
</style>