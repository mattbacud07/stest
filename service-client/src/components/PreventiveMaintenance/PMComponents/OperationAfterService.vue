<template>
    <!-- v-if="statusAfterServiceData === m_var.StatusAfterService.operational && currentRole === pub_var.TLRoleID && tag === m_var.pm_tag_under_observation" -->
    <!-- <v-skeleton-loader :loading="pm_data_loading" type="card"> -->
    <v-card class="bg-orange-lighten-5 p-2 mb-7" v-if="afterServiceOperational" transition="fab-transition"
        elevation="0">
        <v-col cols="12">
            <v-row>
                <!-- After Service - Operational -->
                <v-col cols="12">
                    <p>After {{ data?.task_delegation?.type === 'pm' ? '30' : '10' }} days with no issues, it will be tagged as completed.
                        If sent for CM or a problem occurs, it will be tagged as backjob</p>

                    <p class="mt-4 text-primary">Date montoring ends: <b>{{
                        moment(monitoring_end).format('MMMM DD, YYYY') }}</b></p>
                    <p class="mt-4 text-danger">Monitoring days left: <b>{{ set_days_observation() }}</b></p>

                    <v-dialog max-width="500">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" variant="tonal" text="Send for CM" class="text-none mt-4"
                                :loading="loading" prepend-icon="mdi-transfer-right" v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card title="Confirmation">
                                <v-card-text>
                                    Are you sure you want to proceed with this action?
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn text="No" color="primary" variant="tonal" class="text-none"
                                        @click="isActive.value = false"></v-btn>
                                    <v-btn color="primary" variant="flat" text="Yes, Proceed" class="text-none"
                                        :loading="loading" prepend-icon="mdi-transfer-right" @click="sendToCM"></v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                </v-col>
            </v-row>
        </v-col>
    </v-card>

    <!-- Set Observation Period -->
    <v-card class="bg-teal-lighten-5 p-2 mb-7" transition="fab-transition" elevation="0"
        v-if="afterServiceFurtherMonitoring">
        <v-col cols="12">
            <v-row>
                <!-- After Service - Further Monitoring -->
                <v-col cols="12">
                    <p>
                        Set the number of days for the observation period.
                    </p>
                    <v-dialog max-width="500">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" variant="tonal" text="Set days of Observation" class="text-none mt-4"
                                :loading="loading" prepend-icon="mdi-transfer-right" v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card title="Confirmation">
                                <v-card-text>
                                    <v-form ref="form">
                                        <v-text-field v-model="set_days" variant="outlined" color="primary"
                                            :rules="[v => !!v || 'Required', v => /^[0-9]+$/.test(v) || 'Must be a number', v => (v >= 1 && v <= 31) || 'Must be between 1 and 31']"
                                            label="Set days of Observation" density="compact"></v-text-field>
                                    </v-form>
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn text="No" color="primary" variant="tonal" class="text-none"
                                        @click="isActive.value = false"></v-btn>
                                    <v-btn color="primary" variant="flat" text="Yes, Proceed" class="text-none"
                                        :loading="loading" prepend-icon="mdi-transfer-right"
                                        @click="setDaysObservation"></v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                </v-col>
            </v-row>
        </v-col>
    </v-card>


    <!-- After Service - Further Monitoring -->
    <v-card class="bg-teal-lighten-5 p-2 mb-7" transition="fab-transition" elevation="0" v-if="afterServiceFurtherMonitoringObserve">
        <v-col cols="12">
            <v-row>
                <!-- After Service - Further Monitoring -->
                <v-col cols="12">
                    <p>
                        After <b class="text-danger">{{ moment(monitoring_end).format('MMMM DD, YYYY')
                        }}</b>
                        with no issues, it will be marked as completed.
                        If sent for CM or a problem occurs, it will be tagged as non-operational
                    </p>

                    <p class="mt-4 text-primary">Monitoring days left: <b class="text-danger">{{ set_days_observation()
                    }}</b></p>

                    <v-dialog max-width="500">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" variant="tonal" text="Send for CM" class="text-none mt-4"
                                :loading="loading" prepend-icon="mdi-transfer-right" v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card title="Confirmation">
                                <v-card-text>
                                    Are you sure you want to proceed with this action?
                                </v-card-text>

                                <v-card-actions>
                                    <v-spacer></v-spacer>
                                    <v-btn text="No" color="primary" variant="tonal" class="text-none"
                                        @click="isActive.value = false"></v-btn>
                                    <v-btn color="primary" variant="flat" text="Yes, Proceed" class="text-none"
                                        :loading="loading" prepend-icon="mdi-transfer-right" @click="sendToCM"></v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                </v-col>
            </v-row>
        </v-col>
    </v-card>
    <!-- </v-skeleton-loader> -->

</template>

<script setup>
import { ref, onMounted, defineProps, toRefs, inject, watch, computed } from 'vue'
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance'
import { user_data } from '@/stores/auth/userData'
import { apiRequestAxios } from '@/api/api'
import { useToast } from 'vue-toast-notification'
import { useRoute } from 'vue-router'
import moment from 'moment'
const toast = useToast()

const user = user_data()
const route = useRoute()

/** Permissions */
import { permit } from '@/castl/permitted'
import { PM } from '@/global/modules'
const { can } = permit()

const apiRequest = apiRequestAxios()
const loading = ref(false)
const pm_data_loading = ref(true)
const work_type = route.params.work_type

const props = defineProps({
    data: {
        type: Object,
        default: () => ({})
    }
})

const { data } = toRefs(props)
const emit = defineEmits(['refresh-data'])

const monitoring_end = computed(() => data.value?.task_delegation?.monitoring_end)
const delegation_id = computed(() => data.value?.task_delegation?.id)

const afterServiceOperational = computed(() => {
    return data.value?.task_delegation?.status_after_service === m_var.StatusAfterService.operational
        && (can('delegate', PM) || can('installer', PM))
        && data.value?.task_delegation?.tag === m_var.pm_tag_under_observation
})

const afterServiceFurtherMonitoring = computed(() => {
    return data.value?.task_delegation?.status_after_service === m_var.StatusAfterService.further_monitoring
        && (can('delegate', PM) || can('installer', PM))
        && data.value?.task_delegation?.tag === m_var.pm_tag_set_observation
})

const afterServiceFurtherMonitoringObserve = computed(() => {
    return data.value?.task_delegation?.status_after_service === m_var.StatusAfterService.further_monitoring
        && (can('delegate', PM) || can('installer', PM))
        && data.value?.task_delegation?.tag === m_var.pm_tag_under_observation
})


/** Send for CM */
const sendToCM = async () => {
    loading.value = true
    try {
        const response = await apiRequest.post('sendToCM', { 
            id: delegation_id.value,
            pm_id : data.value?.id,
            module : data.value?.task_delegation?.type
         })
        if (response?.data?.success) {
            toast.success('Successfully created for corrective maintenance')
            emit('refresh-data', true)
        }
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false
    }
}


const set_days_observation = () => {
    const date_monitoring_end = moment(monitoring_end.value, 'YYYY-MM-DD')
    const today = moment().startOf('day')

    const daysToWait = date_monitoring_end.diff(today, 'days')

    return daysToWait < 0 ? 'The waiting period has elapsed by ' + Math.abs(daysToWait) + ' days' : daysToWait
}

/** Set Days of Observation */
const form = ref(null)
const set_days = ref('')
const setDaysObservation = async () => {
    loading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        loading.value = false
        return
    }
    try {
        const response = await apiRequest.post('setDaysObservation',
            { id: delegation_id.value, set_days: parseInt(set_days.value) })
        if (response?.data?.success) {
            toast.success('Successfully set')
            emit('refresh-data', true)
        }
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false
    }
}




</script>