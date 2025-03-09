<template>
    <v-card class="pa-3">
        <v-row>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Delegated to</p>
                <p class="text-grey-darken-1"> {{ task_delegation?.first_name ?? '--' }} {{ task_delegation?.last_name
                    ?? '--' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Date of delegation</p>
                <p class="text-grey-darken-1"> {{ delegation_date ?? '---' }}</p>
            </v-col>
        </v-row>
        <v-row>
            <!-- <v-col cols="12" lg="4" md="4" sm="6">
                <p>Acted at <span class="small text-disabled">(yyyy-mm-dd)</span></p>
                <p class="text-grey-darken-1"> {{ accepted_date ?? '---' }}</p>
            </v-col> -->
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Date Accepted <span class="small text-disabled">(yyyy-mm-dd)</span></p>
                <p class="text-grey-darken-1"> {{ accepted_date ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6" v-if="showPMCMActivity">
                <p>In Transit <span class="small text-disabled">(yyyy-mm-dd)</span></p>
                <p class="text-grey-darken-1"> {{ inTransit ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6" v-if="showPMCMActivity">
                <p>Travel Duration</p>
                <p class="text-grey-darken-1"> {{ task_delegation?.travel_duration ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>Start date <span class="small text-disabled">(yyyy-mm-dd)</span></p>
                <p class="text-grey-darken-1"> {{ start_date ?? '---' }}</p>
            </v-col>
            <v-col cols="12" lg="4" md="4" sm="6">
                <p>End date <span class="small text-disabled">(yyyy-mm-dd)</span></p>
                <p class="text-grey-darken-1"> {{ end_date ?? '---' }}</p>
            </v-col>
        </v-row>
        <v-divider class="mt-7 mb-7"></v-divider>
        <v-row class="mt-5">
            <p class="font-weight-bold">History logs: </p>
            <i class="text-small text-disabled subtitle"> (Click to show details per log)</i>
            <v-col cols="12" lg="12" md="12" sm="12">
                <v-card elevation="0" class="mt-3" v-if="task_delegation_all?.length > 0"
                    v-for="(task_log, index) in task_delegation_all" :key="index">
                    <div class="d-flex justify-content-between">
                        <v-dialog activator="parent" max-width="800" scrollable>
                            <template v-slot:default="{ isActive }">
                                <v-card class="pl-4 pr-4 pt-3">
                                    <div>
                                        <pre><b class=" bg-grey-lighten-3 pa-1  rounded">Servicing Activities:</b> {{JSON.stringify(task_log.task_activity?.map(v => {
                                            return { status: v.status, acted_at: pub_var.FullMonthWithTime(v.acted_at) }
                                        }), null, 2)}}</pre>
                                    </div>
                                    <div>
                                        <pre><b class=" bg-grey-lighten-3 pa-1  rounded">Actions Taken:</b> {{JSON.stringify(task_log.actions_taken?.map(({ action }) => ({
                                            action,
                                        })), null, 2)}}</pre>
                                    </div>
                                    <div>
                                        <pre><b class=" bg-grey-lighten-3 pa-1  rounded">Spareparts Used:</b> {{JSON.stringify(task_log.spareparts?.map(({ item_id, qty, dr, si, remarks }) => ({
                                             item_id, qty, dr, si, remarks
                                        })), null, 2)}}</pre>
                                    </div>
                                    <div>
                                        <pre><b class=" bg-grey-lighten-3 pa-1  rounded">Items Acquired:</b> {{JSON.stringify(task_log.items_acquired?.map(({ item, qty, remarks }) => ({
                                            item, qty, remarks
                                        })), null, 2)}}</pre>
                                    </div>
                                    <template v-slot:actions>
                                        <v-btn class="ml-auto position-fixed bottom-0" text="Close"
                                            @click="isActive.value = false"></v-btn>
                                    </template>
                                </v-card>
                            </template>
                        </v-dialog>
                        <div>
                            <p>Assigned to:</p>
                            <div class="mt-2">
                                <strong class="text-grey"><v-icon class="small text-disabled">mdi-account</v-icon> {{
                                    task_log.assigned_to }}
                                </strong>



                                <div v-if="task_log.remarks" class="mb-3 mt-4">
                                    <p class="text-primary font-weight-medium">Remarks:</p>
                                    <p class="text-remarks">{{ task_log.remarks }}</p>
                                </div>
                                <div v-if="task_log.status_after_service" class="mb-3 mt-4">
                                    <p><b>Status after service:</b></p>
                                    <p class="text-remarks">{{ task_log.status_after_service }}</p>
                                </div>
                                <div class="mt-3">
                                    <v-chip color="warning" size="small" variant="tonal" label>
                                        <v-icon icon="mdi-chevron-right"></v-icon> &nbsp; {{ task_log?.status }}</v-chip>
                                </div>
                            </div>
                        </div>
                        <div>
                            <p>{{ pub_var.formatDate(task_log.created_at) }}</p>
                        </div>
                    </div>
                    <v-divider v-if="task_delegation_all.length > 1"></v-divider>
                </v-card>
                <v-row class="mt-1" v-else>
                    <p class="text-center text-grey">No history logs found</p>
                </v-row>
            </v-col>
        </v-row>
    </v-card>
</template>
<script setup>
import { defineProps, toRefs, computed } from 'vue'
import * as pub_var from '@/global/global'
import { stubTrue } from 'lodash'
import { A_CM, A_PM } from '@/global/modules'

const props = defineProps({
    internalData: {
        type: Object,
        default: () => ({})
    }
})

const { internalData } = toRefs(props)

const task_delegation = computed(() => internalData.value?.task_delegation || {})
const task_delegation_all = computed(() => internalData.value?.task_delegation_all)

const getFormattedDate = (key) => computed(() => pub_var.formatDate(task_delegation.value[key]) || '---');

const showPMCMActivity = computed(() => {
    return task_delegation.value?.type === A_PM || task_delegation.value?.type === A_CM
})

// const delegated_to = computed(() => internalData.value?.assigned_to || '---')
const delegation_date = getFormattedDate('created_at')
const accepted_date = getFormattedDate('acted_at')
const inTransit = computed(() => {
    const startedTask = task_delegation.value?.task_activity?.find(v => v.status === 'In Transit')?.acted_at
    return pub_var.formatDate(startedTask)
})
const start_date = computed(() => {
    const startedTask = task_delegation.value?.task_activity?.find(v => v.status === 'Started')?.acted_at
    return pub_var.formatDate(startedTask)
})
const end_date = computed(() => {
    const endTask = task_delegation.value?.task_activity?.find(v => v.status === 'Ended')?.acted_at
    return pub_var.formatDate(endTask)
})
</script>