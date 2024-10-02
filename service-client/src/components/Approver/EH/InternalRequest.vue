<template>
    <!-- <v-skeleton-loader v-if="loadingSkeletonInternalServicing" type="article"
     class="mb-2"></v-skeleton-loader> -->
    <div class="bg-grey-lighten-5 rounded p-4 border-dashed border-sm">
        <v-row>
            <v-col cols="12">
                <h5 style="font-weight: 500;color: #191970;"><span>Service Department - Internal
                        Servicing</span></h5>
            </v-col>
        </v-row>
        <!-- Delegate Button Button -->
        <v-col cols="12" v-if="internalServicing && !internalServicing?.get_user?.id">
            <v-dialog v-model="dialogInternalRequest" max-width="500" persistent>
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn type="button" variant="tonal" v-bind="activatorProps" color="primary"
                        class="text-none btnSubmit mt-3">
                        <v-icon class="mr-2">mdi-transfer-right</v-icon>
                        Start</v-btn>
                </template>

                <v-card>
                    <v-form @submit.prevent="delegateInternalServicing" ref="form">
                        <v-col cols="12">
                            <h5 class="mt-3" style="font-weight: 500;color: #191970;">Internal Servicing</h5>
                            <v-divider></v-divider>
                            <v-radio-group v-model="type_of_activity" :rules="rule_type_of_activity"
                                :readonly="disable_type_of_activity">
                                <v-radio color="primary" label="For Corrective" value="7"></v-radio>
                                <v-radio color="primary" label="For Refurbishment" value="8"></v-radio>
                                <v-radio color="primary" label="For Quality Control" value="9"></v-radio>
                                <v-radio color="primary" label="Training Purposes" value="10"></v-radio>
                                <v-radio color="primary" label="For Disposal" value="11"></v-radio>
                                <v-radio color="primary" label="Other" value="12"></v-radio>
                            </v-radio-group>
                            <v-text-field v-if="showOther" :rules="rule_other" v-model="other"
                                :readonly="disable_type_of_activity" density="compact" variant="outlined"
                                label="Other Request"></v-text-field>

                            <v-combobox color="primary" class="mt-5" v-model="Engineers" :rules="rule_engineers"
                                clearable label="Delegate to" placeholder="Delegate to" density="compact"
                                :items="engineersData" variant="outlined" itemValue="value"
                                itemTitle="key"></v-combobox>
                        </v-col>

                        <v-divider class="mb-5"></v-divider>
                        <!-- <template v-slot:actions> -->
                        <v-row justify="end" class="mb-3">
                            <v-btn elevation="2" @click="dialogInternalRequest = false" background-color="red"
                                size="small" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                Cancel</v-btn>
                            <v-btn type="submit" size="small" :loading="btnLoading" :disabled="btnDisable"
                                color="#191970" class="text-none bg-primary mr-5"><v-icon
                                    class="mr-2">mdi-check</v-icon>
                                Delegate</v-btn>
                        </v-row>
                        <!-- </template> -->
                    </v-form>
                </v-card>
            </v-dialog>
        </v-col>
        <v-row v-if="internalServicing && internalServicing.status">
            <v-col cols="12" xl="8" md="8" sm="8">
                <p><span class="text-dark mr-2">Status:</span>
                    <span :style="{ color: pub_var.internalStatus(internalServicing.status).color }">
                        {{ internalServicing.status === pub_var.internalStat.Delegated ? 'Waiting for Acceptance' :
                            pub_var.internalStatus(internalServicing.status).text }}
                    </span>
                </p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="4">
                <p class="d-flex align-items-center"><span class="text-grey">Delegated to:</span> <span
                        class="ml-2 text-dark">{{ internalServicing.get_user.first_name }} {{
                            internalServicing.get_user.last_name }}</span></p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="4">
                <label class="text-grey">Delegation Date</label>
                <p class="mt-2">{{ formatDate(internalServicing.delegation_date) }}</p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="4">
                <label class="text-grey">Date Started</label>
                <p class="mt-2">{{ formatDate(internalServicing.date_started) }}</p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="4">
                <label class="text-grey">Date Accomplished</label>
                <p class="mt-2">{{ formatDate(internalServicing.accomplished_date) }}</p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="8">
                <label class="text-grey">Packed & Endorsed to Warehouse</label>
                <p class="mt-2">{{ internalServicing.packed_endorse_to_warehouse ? 'Yes' : '--' }}</p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="4">
                <label class="text-grey">Endorsement Date</label>
                <p class="mt-2">{{ formatDate(internalServicing.endorsement_date) }}</p>
            </v-col>
            <v-col cols="12" xl="4" md="4" sm="4">
                <label class="text-grey">Confirmed by Warehouse</label>
                <p class="mt-2">{{ formatDate(internalServicing.accepted_by_warehouse) }}</p>
            </v-col>
        </v-row>

    </div>
</template>



<script setup>
// import { apiRequest } from '@/api';
import { ref, onMounted, watch, inject, toRefs } from 'vue'
import { user_data } from '@/stores/auth/userData'
import * as pub_var from '@/global/global'
import { apiRequestAxios } from '@/api/api'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
import moment from 'moment';
const toast = useToast()

const dialogInternalRequest = ref(false)
const type_of_activity = ref(null)
const Engineers = ref('')
const engineersData = ref([])
const form = ref(false)
const btnLoading = ref(false)
const btnDisable = ref(false)
const showOther = ref(false)
const other = ref('')
const disable_type_of_activity = ref(false)
const disableButton = inject('disableButton', null)

const auth = user_data()
auth.getUserData
const apiRequest = apiRequestAxios()

const props = defineProps({
    service_id: {
        type: Number
    },
    getSelectedRequest: {
        type: Number,
    },
    other: {
        type: String,
    },
    internalStatus: {
        type: Number,
        default: 0
    },
    internalDelegatedTo: {
        type: String,
        default: '',
    },
    internalID: {
        type: Number,
        default: null
    },
    internalServicing: {
        type: Object,
        default: null
    }
})
const { internalServicing } = toRefs(props)
const formatDate = (data) => {
    return data !== null ? moment(data).format('MM/DD/YYYY hh:mm a') : '--'
}
watch(type_of_activity, (val, oldVal) => {
    showOther.value = val === '12' // Compare val to the string '12'
    if (!showOther.value) {
        other.value = ''
    }
})


/** Rules */
const rule_type_of_activity = ref([
    v => !!v || 'Select an option from the list'
])
const rule_other = ref([
    v => !!v || 'Required field'
])
const rule_engineers = ref([
    v => !!v || 'Required field',
    k => k.key !== undefined ? true : 'Select an option from the list',
    r => r.value !== undefined ? true : 'Select an option from the list'
])


/**
 * Submit || Delegate Internal Servicing
 */
const delegateInternalServicing = async () => {
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('internal-process', {
            received_by: auth.user.id,
            service_id: props.service_id,
            type_of_activity: type_of_activity.value,
            other: other.value,
            delegated_to: Engineers.value.value
        })
        if (response.data && response.data.success) {
            toast.success('Successfully delegated')
            if (typeof (disableButton) === 'function') disableButton()
            setTimeout(() => {
                window.location.reload()
            }, 1000)
        } else if (response.data && response.data.exist_service_id) {
            toast.warning('Request currently delegated')
        }
    } catch (error) {
        alert(error)
    }
    finally {
        btnLoading.value = false
        dialogInternalRequest.value = false
    }
}

/**
 * Get Engineers Data - Use for Delegation
 */
const getEngineeresData = async () => {
    try {
        const response = await apiRequest.get('get-engineers-data')
        if (response.data && response.data.engineers) {
            const engineersValue = response.data.engineers.map(data => {
                return {
                    key: data.users.first_name + ' ' + data.users.last_name,
                    value: data.user_id
                }
            })
            engineersData.value = engineersValue
        }
    } catch (error) {
        alert(error)
    }
}


// Type of Activity Disable
const disable_typeOfActivity = () => {
    if (props.getSelectedRequest === 0) {
        disable_type_of_activity.value = false
    } else {
        disable_type_of_activity.value = true
        type_of_activity.value = String(props.getSelectedRequest)
        other.value = String(props.other)
    }
}




onMounted(() => {
    getEngineeresData()
    disable_typeOfActivity()
})
</script>