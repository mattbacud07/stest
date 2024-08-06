<template>
<v-skeleton-loader v-if="loadingSkeleton" type="article"
    class="mb-2"></v-skeleton-loader>
    <v-card v-else style="border: dashed thin #19197023" flat class="pl-2 pr-2 pt-5 pb-4 mb-7">

        <h5 class="pb-5 text-primary">Internal Servicing Details</h5>
        <v-row>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Delegated to" v-model="delegated_to"
                    placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Type of Avtivity" v-model="type_of_activity"
                    placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Type of Avtivity (Other)" v-model="other"
                    v-if="type_of_activity_val === 12" placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Delegation Date" v-model="delegation_date"
                    placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Date Started" v-model="date_started"
                    placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Date Accomplished" v-model="accomplished_date"
                    placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Packed & Endorsed to Warehouse"
                    v-model="packed_endorse_to_warehouse" placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Endorsement Date" v-model="endorsement_date"
                    placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
            <v-col cols="4">
                <v-text-field color="primary" density="compact" label="Confirmed by Warehouse"
                    v-model="accepted_by_warehouse" placeholder="" variant="outlined" readonly></v-text-field>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="6">
                <b class="text-primary ml-1">Actions Done</b>
                <div v-if="rowData.actions_done && rowData.actions_done.length > 0">
                    <v-card v-for="action in rowData.actions_done" :key="action.key" :text="action.action ?? 'No remarks'" prepend-icon="mdi-playlist-check"
                        class="mt-3" elevation2>
                        <template v-slot:subtitle>
                            <v-row class="p-3">
                                <span>{{ getEquipmentSerialNumber(action).code }}</span>
                            <v-spacer></v-spacer>
                            <span>{{ getEquipmentSerialNumber(action).serialNo }}</span>
                            </v-row>
                        </template>

                    </v-card>
                </div>
                <v-card v-else lines="two" title="No Actions Done" text="In Progress" prepend-icon="mdi-playlist-check">
                </v-card>

            </v-col>
        </v-row>
    </v-card>
</template>
<script setup>
import { onMounted, defineProps, toRefs, ref, reactive } from 'vue'
import { user_data } from '@/stores/auth/userData'
// import TextField from '@/components/GeneralComponents/TextField.vue'
import * as pub_var from '@/global/global'

const user = user_data()

const apiRequest = user.apiRequest()
const loadingSkeleton = ref(false)
const rowData = ref({})
const actionDone = reactive({})

const getEquipmentSerialNumber = (action) =>{
    const e = rowData.value.equipments.find(e => e.id === action.equipment_id)
    if(e){
        return {code: `Item Code -  ${e.item_code}`, serialNo : `Serial No. -  ${e.serial_number}`}
    }
    return ''
}

/** Delare field Variable */
const received_by = ref('')
const type_of_activity = ref('')
const type_of_activity_val = ref('')
const other = ref('')
const delegation_date = ref('')
const delegated_to = ref('')
const date_started = ref('')
const accomplished_date = ref('')
const packed_endorse_to_warehouse = ref('')
const packed_date = ref('')
const checklist_number = ref('')
const endorsement_date = ref('')
const remarks = ref('')
const accepted_by_warehouse = ref('')
const status = ref('')

const props = defineProps({
    id: {
        type: Number,
        default: 0
    }
})


const { id } = toRefs(props)
/** Get Internal Request Servicing */
const getInternalRequest = async () => {
    loadingSkeleton.value = true
    try {
        const response = await apiRequest.get('getInternalRequest', {
            params: {
                id: id.value, category: 'specificRowServicing'
            }
        });
        if (response.data && response.data.request) {
            const data = response.data.request

            rowData.value = data[0]
            // console.log(rowData.value)

            received_by.value = rowData.value.received_by
            type_of_activity.value = rowData.value.internal_external_name.name ?? '.'
            type_of_activity_val.value = rowData.value.type_of_activity
            other.value = rowData.value.other
            delegation_date.value = pub_var.formatDate(rowData.value.delegation_date)
            delegated_to.value = rowData.value.get_user.first_name + ' ' + rowData.value.get_user.last_name
            date_started.value = rowData.value.date_started ? pub_var.formatDate(rowData.value.date_started) : '.'
            accomplished_date.value = rowData.value.accomplished_date ? pub_var.formatDate(rowData.value.accomplished_date) : '.'
            packed_endorse_to_warehouse.value = rowData.value.packed_endorse_to_warehouse === 1 ? 'Yes' : '.'
            packed_date.value = rowData.value.packed_date
            checklist_number.value = rowData.value.checklist_number
            endorsement_date.value = rowData.value.endorsement_date ? pub_var.formatDate(rowData.value.endorsement_date) : '.'
            remarks.value = rowData.value.remarks
            accepted_by_warehouse.value = rowData.value.accepted_by_warehouse ? pub_var.formatDate(rowData.value.accepted_by_warehouse) : '.'
            status.value = rowData.value.status

            actionDone.value = { ...rowData.value.actions_done, ...rowData.value.equipments }
            // console.log(actionDone.value)
        }


    } catch (error) {
        console.log(error)
    }

    loadingSkeleton.value = false
};


onMounted(() => {
    getInternalRequest()
})
</script>