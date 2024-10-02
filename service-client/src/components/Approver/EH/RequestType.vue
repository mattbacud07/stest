<template>
    <v-skeleton-loader v-if="loadingSkeleton" type="list-item, list-item, button, table-row, table-tbody"
        class="mb-2"></v-skeleton-loader>
    <v-card v-else style="padding: 1em 1em;" elevation="1">
        <!-- OTHER REQUEST DETAILS -->
        <v-row>
            <v-col :cols="column">
                <h5 class="mb-1" style="font-weight: 700;color: #191970;">Other Request Details</h5>
                <v-checkbox v-model="ocular" color="primary" label="Request for Ocular" class="vCheckbox"
                    readonly></v-checkbox>
                <v-checkbox v-model="bypass" color="primary" label="Bypass Internal Servicing Procedures"
                    class="vCheckbox" readonly></v-checkbox>
                <v-checkbox v-model="ship" color="primary" label="Ship & Deliver direct to customer immediately"
                    class="vCheckbox" readonly></v-checkbox>
            </v-col>
            <v-col :cols="column">
                <v-textarea color="primary" label="Endorsement" v-model="endorsement" row-height="25" rows="3"
                    variant="outlined" readonly auto-grow shaped>
                </v-textarea>
            </v-col>
        </v-row>

        <v-divider class="mb-3"></v-divider>
        <!-- Internal External Request -->
        <v-row class="mt-3">
            <v-col cols="12" md="6" sm="6"> <!--v-if="externalRequest !== null"-->
                <h5 class="mb-2" style="font-weight: 700;color: #191970;">External Request</h5>
                <v-row>
                    <v-col cols="12" md="6" sm="6">
                        <v-radio-group v-model="request_type" column readonly>
                            <v-radio color="primary" label="For Demonstration" value="1"></v-radio>
                            <v-radio color="primary" label="Reagent Tie-up" value="2"></v-radio>
                            <v-radio color="primary" label="Purchased" value="3"></v-radio>
                            <v-radio color="primary" label="Shipment / Delivery" value="4"></v-radio>
                            <v-radio color="primary" label="Service Unit" value="5"></v-radio>
                            <v-radio color="primary" label="Others" value="6"></v-radio>
                        </v-radio-group>
                    </v-col>
                    <v-col cols="12" md="6" sm="6">
                        <v-checkbox v-model="attached_gate" class="vCheckbox" color="primary"
                            label="Attached gate/entry pass" readonly></v-checkbox>
                        <v-checkbox v-model="with_contract" class="vCheckbox mt-3" color="primary"
                            label="with contract/other docs" readonly></v-checkbox>
                    </v-col>
                </v-row>
                <v-row>
                    <v-text-field v-if="request_type === '6'" readonly color="primary" density="compact"
                        variant="outlined" placeholder="Other External Request" v-model="other"
                        class="ml-5"></v-text-field>
                </v-row>
            </v-col>
            <v-col cols="12" md="6" sm="6"> <!-- v-if="internalRequest !== null" -->
                <h5 class="mb-2" style="font-weight: 700;color: #191970;">Internal Request</h5>
                <v-radio-group v-model="request_type" column readonly>
                    <v-radio color="primary" label="For Corrective" value="7"></v-radio>
                    <v-radio color="primary" label="For Refurbishment" value="8"></v-radio>
                    <v-radio color="primary" label="For Quality Control" value="9"></v-radio>
                    <v-radio color="primary" label="Training Purposes" value="10"></v-radio>
                    <v-radio color="primary" label="For Disposal" value="11"></v-radio>
                    <v-radio color="primary" label="Other" value="12"></v-radio>
                </v-radio-group>
                <v-text-field v-if="request_type === '12'" readonly density="compact" variant="outlined"
                    placeholder="Other External Request" w-90 v-model="other"></v-text-field>
            </v-col>
        </v-row>
    </v-card>
</template>


<script setup>
import { ref, onMounted, watch } from 'vue';
import { user_data } from '@/stores/auth/userData';
import * as pub_var from '@/global/global'
import { apiRequestAxios } from '@/api/api';
import { useDisplay } from 'vuetify'
const { width } = useDisplay()


/** data declarations */
const user = user_data()
const apiRequest = apiRequestAxios()
const loadingSkeleton = ref(false)

const main_status = ref('')

// 2nd
const ocular = ref(false)
const bypass = ref(false)
const ship = ref(false)
// 3rd
const request_type = ref(null)
const other = ref('')
const attached_gate = ref(null)
const with_contract = ref(null)
// 4th
const endorsement = ref('')


const props = defineProps({
    service_id: {
        type: Number
    },
})

const column = ref(6)
watch(width, (val) => {
    if (val < 550) {
        column.value = 12
    }
    else {
        column.value = 6
    }
})

const getDetails = async () => {
    try {
        loadingSkeleton.value = true
        const response = await apiRequest.get('get-specific-equipment-handling', {
            params: {
                service_id: props.service_id,
            },
        });
        if (response.data && response.data.equipment_handling) {
            const data = response.data.equipment_handling

            const field = data[0]

            ocular.value = field.occular === 1 ? true : false
            bypass.value = field.bypass === 1 ? true : false
            ship.value = field.ship === 1 ? true : false
            endorsement.value = field.endorsement ?? '.'
            request_type.value = field.request_type.toString()
            other.value = field.other
            attached_gate.value = field.attach_gate === 1 ? true : false
            with_contract.value = field.with_contract === 1 ? true : false
            main_status.value = field.main_status
        } else {
            alert('Something went wrong')
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingSkeleton.value = false
    }
};
const setSizeScreen = () => {
    column.value = width.value < 550 ? 12 : 6;
};
onMounted(() => {
    getDetails();
    setSizeScreen()
});

</script>


<style scoped>
.vCheckbox {
    height: 50px !important;
}
</style>