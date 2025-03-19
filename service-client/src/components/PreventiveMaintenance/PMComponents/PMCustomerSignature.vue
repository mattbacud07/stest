<template>
    <v-card flat style="padding-top: 1em;" class="mb-7 pa-5 mt-5">
        <v-row class="d-flex justify-space-between">
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Name</p>
                <p class="text-grey-darken-1 textTransform">{{ customer?.name?.toLowerCase() ?? '---' }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Desgination</p>
                <p class="text-grey-darken-1 textTransform">{{ customer?.designation?.toLowerCase() ?? '---' }}</p>
            </v-col>
        </v-row>
        <v-row>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Signature</p>
                
                <v-img :src="'/'+ customer?.signature" alt="Signature" max-width="150" class="rounded-lg" />
            </v-col>
        </v-row>
    </v-card>
</template>


<script setup>
import { toRefs, computed } from 'vue';

const props = defineProps({
    customer_details: {
        type: Object,
        default: () => ({})
    }
})

const { customer_details } = toRefs(props)

const customer = computed(() => {
    // console.log(`${window.location.origin}/signatures/135-10-02-24.png`);

    return customer_details.value?.task_delegation?.customer
})
</script>


<style scoped>
.vCheckbox {
    height: 50px !important;
}
</style>