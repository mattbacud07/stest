<template>
    <v-skeleton-loader v-if="loadingSkeleton" type="list-item, list-item, button, table-row, table-tbody"
        class="mb-2"></v-skeleton-loader>
    <v-card v-else style="padding: 1em 1em;" elevation="1">
        <!-- Internal External Request -->
        <v-row class="mt-3">
            <v-col cols="12">
                <v-row>
                    <v-col cols="12" md="6" sm="6">
                        <h5 class="mb-2">Type of request</h5>
                        <p class="text-grey-darken-1">- {{ request_data.request_name }}</p>
                    </v-col>
                    <v-col cols="12" md="6" sm="6" v-if="request_data.request_type === 4">
                        <p>Satellite Office</p>
                        <p class="text-grey-darken-1">{{ request_data.satellite }}</p>
                    </v-col>
                </v-row>
                <v-row v-if="request_data.request_type === 6">
                    <p>Other request</p>
                    <p class="text-grey-darken-1">{{ request_data.other }}</p>
                </v-row>
                <v-row v-if="request_data.request_type === 12">
                    <p>Other request</p>
                    <p class="text-grey-darken-1">{{ request_data.other }}</p>
                </v-row>
                <v-row>
                    <v-col cols="12">
                        <span>
                            <v-icon class="mr-1 vIcon">{{ request_data.attach_gate === 1 ? 'mdi-checkbox-marked' :
                                'mdi-checkbox-blank-outline' }}</v-icon>
                            Attached gate/entry pass
                        </span>
                        <span class="ml-5">
                            <v-icon class="mr-1 vIcon">{{ request_data.with_contract === 1 ? 'mdi-checkbox-marked' :
                                'mdi-checkbox-blank-outline' }}</v-icon>
                            with contract/other docs
                        </span>
                    </v-col>
                </v-row>
            </v-col>
        </v-row>



        <v-divider class="mb-5 mt-5"></v-divider>
        <!-- OTHER REQUEST DETAILS -->
        <v-row>
            <v-col cols="12">
                <h5 class="font-weight-medium">Other Request Details</h5>
            </v-col>
            <v-col :cols="column">
                <p class="mb-2">
                    <v-icon class="mr-1 vIcon">{{ request_data.ocular === 1 ? 'mdi-checkbox-marked' :
                        'mdi-checkbox-blank-outline' }}</v-icon>
                    Request for Ocular
                </p>
                <p class="mb-2">
                    <v-icon class="mr-1 vIcon">{{ request_data.bypass === 1 ? 'mdi-checkbox-marked' :
                        'mdi-checkbox-blank-outline' }}</v-icon>
                    Bypass Internal Servicing Procedures
                </p>
                <p class="mb-2">
                    <v-icon class="mr-1 vIcon">{{ request_data.ship === 1 ? 'mdi-checkbox-marked' :
                        'mdi-checkbox-blank-outline' }}</v-icon>
                    Ship & Deliver direct to customer immediately
                </p>
            </v-col>
            <v-col :cols="column">
                <p class="mt-2">Endorsement:</p>
                <p>{{ request_data.endorsement || '---' }}</p>
            </v-col>
        </v-row>


    </v-card>
</template>


<script setup>
import { ref, onMounted, watch, toRefs } from 'vue';
import { useDisplay } from 'vuetify'
const { width } = useDisplay()

const props = defineProps({
    request_data: {
        type: Object,
        default: () => { }
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
    setSizeScreen()
});

</script>


<style scoped>
.vIcon {
    color: #191970;
    font-size: 14px;
}
</style>