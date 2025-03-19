<template>
    <v-card elevation="0" flat style="padding-top: 1em;" class="mb-7 pa-5 mt-5">
        <v-row class="d-flex justify-space-between">
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Requested by</p>
                <p class="text-grey-darken-1">{{ request_data?.full_name ?? '---' }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Date Created <span class="text-disabled small">(yyyy-mm-dd)</span></p>
                <p class="text-grey-darken-1">{{ pub_var.formatDate(request_data?.created_at) }}</p>
            </v-col>
        </v-row>
        <v-row>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Institution</p>
                <p class="text-grey-darken-1 textTransform">{{ request_data?.institution?.toLowerCase() }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Address</p>
                <p class="text-grey-darken-1 textTransform">{{ request_data?.address?.toLowerCase() }}</p>
            </v-col>
            <v-col lg="4" md="4" sm="6" cols="12">
                <p>Proposed Delivery Date</p>
                <p class="text-grey-darken-1">{{ request_data?.proposed_delivery_date }}</p>
            </v-col>
        </v-row>
    </v-card>
</template>


<script setup>
import { ref, onMounted, watch, toRefs } from 'vue';
import * as pub_var from '@/global/global'
import { getRole } from '@/stores/getRole';
import { useDisplay } from 'vuetify'
const { width } = useDisplay()



/** Get Role Store */
const role = getRole()

const props = defineProps({
    request_data : {
        type : Object,
        default : () => ({})
    }
})

const { request_data } = toRefs(props)

const column = ref(6)
watch(width, (val) => {
    if (val < 550) {
        column.value = 12
    }
    else {
        column.value = 6
    }
})

const setSizeScreen = () => {
    column.value = width.value < 550 ? 12 : 6;
};

onMounted(() => {
    // getDetails();
    setSizeScreen()
});

</script>


<style scoped>
.vCheckbox {
    height: 50px !important;
}
</style>