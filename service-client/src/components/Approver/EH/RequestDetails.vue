<template>
    <v-skeleton-loader v-if="loadingSkeleton" type="list-item, list-item, button, table-row, table-tbody" class="mb-2"></v-skeleton-loader>
    <v-card v-else style="padding: 3em 1em;" elevation="1">
        <v-row>
            <v-col cols="6">
                <v-text-field color="primary" density="compact" label="Institution" placeholder="Institution"
                    variant="outlined" readonly v-model="institution"></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-text-field color="primary" density="compact" label="Requested by" placeholder="Requested by"
                    variant="outlined" readonly v-model="requested_by"></v-text-field>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="6">
                <v-text-field color="primary" v-model="address" label="Address" density="compact" variant="outlined"
                    readonly></v-text-field>
            </v-col>
            <v-col cols="6">
                <v-text-field color="primary" v-model="proposed_delivery_date" label="Propose Delivery Date"
                    density="compact" variant="outlined" readonly></v-text-field>
            </v-col>
        </v-row>
        <v-divider></v-divider>

        <!-- OTHER REQUEST DETAILS -->
        <v-row>
            <v-col cols="6">
                <h5 class="mb-1" style="font-weight: 700;color: #191970;">Other Request Details</h5>
                <v-checkbox v-model="ocular" color="primary" label="Request for Ocular" class="vCheckbox"
                    readonly></v-checkbox>
                <v-checkbox v-model="bypass" color="primary" label="Bypass Internal Servicing Procedures"
                    class="vCheckbox" readonly></v-checkbox>
                <v-checkbox v-model="ship" color="primary" label="Ship & Deliver direct to customer immediately"
                    class="vCheckbox" readonly></v-checkbox>
            </v-col>
            <v-col cols="6">
                <v-textarea color="primary" label="Endorsement" v-model="endorsement" row-height="25" rows="3"
                    variant="outlined" readonly auto-grow shaped>
                </v-textarea>
            </v-col>
        </v-row>

        <v-divider class="mb-3"></v-divider>
        <!-- Internal External Request -->
        <v-row class="mt-3">
            <v-col cols="12" md="6" sm="6" v-if="externalRequest !== null">
                <h5 class="mb-2" style="font-weight: 700;color: #191970;">External Request</h5>
                <v-row>
                    <v-col cols="6">
                        <v-radio-group v-model="externalRequest" column readonly>
                            <v-radio color="primary" label="For Demonstration" value="1"></v-radio>
                            <v-radio color="primary" label="Reagent Tie-up" value="2"></v-radio>
                            <v-radio color="primary" label="Purchased" value="3"></v-radio>
                            <v-radio color="primary" label="Shipment / Delivery" value="4"></v-radio>
                            <v-radio color="primary" label="Service Unit" value="5"></v-radio>
                            <v-radio color="primary" label="Others" value="11"></v-radio>
                        </v-radio-group>
                    </v-col>
                    <v-col cols="6">
                        <v-checkbox v-model="attached_gate" class="vCheckbox" color="primary"
                            label="Attached gate/entry pass" readonly></v-checkbox>
                        <v-checkbox v-model="with_contract" class="vCheckbox mt-3" color="primary"
                            label="with contract/other docs" readonly></v-checkbox>
                    </v-col>
                </v-row>
            </v-col>
            <v-col cols="12" md="6" sm="6" v-if="internalRequest !== null">
                <h5 class="mb-2" style="font-weight: 700;color: #191970;">Internal Request</h5>
                <v-radio-group v-model="internalRequest" column readonly>
                    <v-radio color="primary" label="For Corrective" value="6"></v-radio>
                    <v-radio color="primary" label="For Refurbishment" value="7"></v-radio>
                    <v-radio color="primary" label="For Quality Control" value="8"></v-radio>
                    <v-radio color="primary" label="Training Purposes" value="9"></v-radio>
                    <v-radio color="primary" label="For Disposal" value="10"></v-radio>
                    <v-radio color="primary" label="Other" value="11"></v-radio>
                </v-radio-group>
            </v-col>
            
            
            <!-- Internal Request - Optional -->
            <v-col cols="12" md="6" sm="6" 
            v-if="(showInternalRequest && !bypass) || (showInternalRequest && internalRequest === null && !bypass)">
                <InternalRequest :service_id = "props.service_id" :getSelectedRequest="internalRequest !== null ? parseInt(internalRequest) : 0"/>
            </v-col>

        </v-row>
    </v-card>
</template>


<script setup>
import { ref, onMounted, getCurrentInstance, defineEmits } from 'vue';
import { BASE_URL } from '@/api';
import { user_data } from '@/stores/auth/userData';
import axios from 'axios';
import InternalRequest from './InternalRequest.vue'

/** data declarations */
const uri = BASE_URL
const user = user_data()
user.getUserData
const institutionData = ref([])
const instance = getCurrentInstance()
const loadingSkeleton = ref(false)

// 1st
const requested_by = ref('')
const institution = ref('')
const address = ref('')
const proposed_delivery_date = ref('')

// 2nd
const ocular = ref(false)
const bypass = ref(false)
const ship = ref(false)
// 3rd
const internalRequest = ref(null)
const externalRequest = ref(null)
const attached_gate = ref('')
const with_contract = ref('')
// 4th
const endorsement = ref('')


const props = defineProps({
    service_id : {
        type: Number
    },
    showInternalRequest : {
        type : Boolean,
        default : () => false,
    }
})

// Functions
defineEmits(['set-status'])
const getDetails = async () => {
    try {
        loadingSkeleton.value = true
        const response = await axios.get(uri + 'api/approver-get-equipment-handling-data', {
            params: {
                service_id: props.service_id,
            },
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        if (response.data && response.data.serviceData) {
            const data = response.data.serviceData

            // requestedData.value = data
            const field = data[0]
            // console.log(field.approver_name)

            institution.value = field.name ?? 0
            address.value = field.address
            requested_by.value = field.first_name + ' ' + field.last_name
            proposed_delivery_date.value = field.proposed_delivery_date
            ocular.value = field.occular === 1 ? true : false
            bypass.value = field.bypass === 1 ? true : false
            ship.value = field.ship === 1 ? true : false
            endorsement.value = field.endorsement ?? '.'
            internalRequest.value = field.internal_request
            externalRequest.value = field.external_request
            attached_gate.value = field.attached_gate === 1 ? true : false
            with_contract.value = field.with_contract === 1 ? true : false
            

            
            instance.emit('set-status', field.status)

            // console.log(field)
        } else {
            alert('Something went wrong')
        }

        // rows.value = data
        // total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }
    finally{
        loadingSkeleton.value = false
    }
};



onMounted(() => {
    getDetails();
});

</script>


<style scoped>
.vCheckbox {
    height: 50px !important;
}
</style>