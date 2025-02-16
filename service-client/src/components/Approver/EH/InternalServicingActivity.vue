<template>

    <v-skeleton-loader v-if="loadingSkeleton" type="article" class="mb-2"></v-skeleton-loader>
    <v-card v-else class="pa-3">
        <v-row>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Delegated to</p>
                <p class="text-grey-darken-1"> {{ delegated_to ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Date of delegation</p>
                <p class="text-grey-darken-1"> {{ delegation_date ?? '---' }}</p>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Acted at</p>
                <p class="text-grey-darken-1"> {{ accepted_date ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Start date</p>
                <p class="text-grey-darken-1"> {{ start_date ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>End date</p>
                <p class="text-grey-darken-1"> {{ end_date ?? '---' }}</p>
            </v-col>
        </v-row>
        <v-divider class="mt-7 mb-7"></v-divider>
        <v-row class="mt-5">
            <p><b>History logs:</b></p>
            <v-col cols="12" lg="12" md="12" sm="12">
                <v-card elevation="0" class="mt-3" v-if="task_delegation_all?.length > 0" v-for="(task_log, index) in task_delegation_all" :key="index">
                  <div class="d-flex justify-content-between">
                    <div>
                        <p>Assigned to:</p>
                        <div class="mt-2">
                            <strong class="text-grey"><v-icon class="small text-disabled">mdi-account</v-icon> {{ task_log.assigned_to }}</strong>
                           
                            <div v-if="task_log.remarks" class="mb-3 mt-4">
                                <p><b>Remarks:</b></p>
                                <p class="text-remarks">{{ task_log.remarks }}</p>
                            </div>
                            <div v-if="task_log.status_after_service" class="mb-3 mt-4">
                                <p><b>Status after service:</b></p>
                                <p class="text-remarks">{{ task_log.status_after_service }}</p>
                            </div>
                            <v-chip v-if="task_log.status === 'Completed'" color="success" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-check"></v-icon> &nbsp; {{ task_log.status }}</v-chip>
                            <v-chip v-if="task_log.status === 'Declined'" color="error" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-alert-circle-outline"></v-icon> &nbsp; {{ task_log.status }}</v-chip>
                            <v-chip v-if="task_log.status === 'Delegated'" color="info" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-update"></v-icon> &nbsp; {{ task_log.status }}</v-chip>
                            <v-chip v-if="task_log.status === 'Accepted'" color="primary" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-check-bold"></v-icon> &nbsp; {{ task_log.status }}</v-chip>
                        </div>
                    </div>
                    <div>
                        <p>{{ pub_var.formatDate(task_log.created_at) }}</p>
                    </div>
                  </div>
                <v-divider v-if="task_delegation_all.length > 1"></v-divider>
                </v-card>
            </v-col>
        </v-row>
    </v-card>
</template>
<script setup>
import { onMounted, defineProps, toRefs, ref, computed, watch } from 'vue'
import { user_data } from '@/stores/auth/userData'
import { apiRequestAxios } from '@/api/api'
import * as pub_var from '@/global/global'


import { useDisplay } from 'vuetify'
import TextField from '@/components/layout/FormsComponent/TextField.vue'
const { width } = useDisplay()

const user = user_data()

const apiRequest = apiRequestAxios()
const loadingSkeleton = ref(false)


const props = defineProps({
    internalData: {
        type: Object,
        default: () => ({})
    }
})

const { internalData } = toRefs(props)

const task_delegation = computed(() => internalData.value?.task_delegation?.find(v => v.active === 1) || []);
const task_delegation_all = computed(() => internalData.value?.task_delegation_all)

const getFormattedDate = (key) => computed(() => pub_var.formatDate(task_delegation.value[key]) || '---');

const taskActivity = (status) => computed(() => {
    return pub_var.formatDate(task_delegation.value?.task_activity?.find(v => v.status === status)?.acted_at) || '---';
});

const delegated_to = computed(() => internalData.value?.assigned_to || '---')
const delegation_date = getFormattedDate('created_at')
const accepted_date = getFormattedDate('acted_at')
const start_date = taskActivity('Started')
const end_date = taskActivity('Ended')
</script>