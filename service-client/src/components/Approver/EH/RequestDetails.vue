<template>
    <v-card flat class="mb-5 mt-7">
        <v-row>
            <v-skeleton-loader v-if="loadingSkeleton" type="list-item" class="mb-2">
            </v-skeleton-loader>
            <!-- Internal Request -->
            <!--- Optional || (showInternalRequest && internalRequest === null && !bypass) -->
            <template v-else>
                <v-col cols="12" v-if="showInternalRequest && !bypass && ehStatus === user.user.approval_level">
                    <InternalRequest 
                        :service_id="props.service_id"
                        :getSelectedRequest="selectedRequestType >= 7 ? selectedRequestType : 0" :other="other ?? ''"
                        :internalStatus = "internalStatus"
                        :internalDelegatedTo = "internalDelegatedTo"
                        :internalID = "internalID"
                        :internalServicing = "internalServicing"
                        />
                </v-col>
            </template>

        </v-row>
    </v-card>
    <v-skeleton-loader v-if="loadingSkeleton" type="list-item, list-item, button, table-row, table-tbody"
        class="mb-2"></v-skeleton-loader>
    <v-card v-else flat style="padding-top: 1em;">
        <v-row>
            <v-col :cols="column">
                <v-text-field color="primary" density="compact" label="Institution" placeholder="Institution"
                    variant="outlined" readonly v-model="institution"></v-text-field>
            </v-col>
            <v-col :cols="column">
                <v-text-field color="primary" density="compact" label="Requested by" placeholder="Requested by"
                    variant="outlined" readonly v-model="requested_by"></v-text-field>
            </v-col>
        </v-row>
        <v-row>
            <v-col :cols="column">
                <v-text-field color="primary" v-model="address" label="Address" density="compact" variant="outlined"
                    readonly></v-text-field>
            </v-col>
            <v-col :cols="column">
                <v-text-field color="primary" v-model="proposed_delivery_date" label="Propose Delivery Date"
                    density="compact" variant="outlined" readonly></v-text-field>
            </v-col>
        </v-row>
    </v-card>
</template>


<script setup>
import { ref, onMounted, getCurrentInstance, defineEmits, watch, toRefs, toRef } from 'vue';
import { user_data } from '@/stores/auth/userData';
import * as pub_var from '@/global/global'
import { apiRequestAxios } from '@/api/api';
import InternalRequest from './InternalRequest.vue'
import { useDisplay } from 'vuetify'
const { width } = useDisplay()


/** data declarations */
const user = user_data()
const apiRequest = apiRequestAxios()
const institutionData = ref([])
const instance = getCurrentInstance()
const loadingSkeleton = ref(false)
// const internalStatus = ref(inject('getInternalStatus'), null)
const ehStatus = ref('')
const main_status = ref('')

// 1st
const requested_by = ref('')
const institution = ref('')
const address = ref('')
const proposed_delivery_date = ref('')

const request_type = ref('')
const bypass = ref(false)


const props = defineProps({
    service_id: {
        type: Number
    },
    showInternalRequest: {
        type: Boolean,
        default: () => false,
    },
    internalStatus : {
        type : Number,
        default : 0,
    },
    internalDelegatedTo : {
        type : String,
        default : '',
    },
    internalID : {
        type : Number,
        default : null,
    },
    internalServicing : {
        type : Object,
        default : null
    }
})

const { internalStatus, internalDelegatedTo, internalID, internalServicing } = toRefs(props)
const column = ref(6)
watch(width, (val) => {
    if (val < 550) {
        column.value = 12
    }
    else {
        column.value = 6
    }
})

// Functions
defineEmits(['set-status', 'set-updated-ssu', 'get-installation-engineer'])

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

            // requestedData.value = data
            const field = data[0]
            // console.log(field.approver_name)

            institution.value = field.name ?? 0
            address.value = field.address
            requested_by.value = field.first_name + ' ' + field.last_name
            proposed_delivery_date.value = field.proposed_delivery_date
            request_type.value = field.request_type.toString()
            bypass.value = field.bypass === 1 ? true : false
            ehStatus.value = field.status
            main_status.value = field.main_status
            instance.emit('set-status', field.status)
            instance.emit('set-updated-ssu', field.ssu)
            instance.emit('get-installation-engineer', field.installer)
        } else {
            alert('Something went wrong')
        }

        // rows.value = data
        // total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingSkeleton.value = false
    }
};
// provide('getDetails', getDetails)

const selectedRequestType = ref(null);
watch(request_type, (newValue) => {
    selectedRequestType.value = parseInt(newValue);
});

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