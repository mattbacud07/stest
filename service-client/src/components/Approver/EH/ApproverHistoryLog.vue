<template>
    <v-skeleton-loader v-if="loading" type="article"></v-skeleton-loader>
    <v-card v-else>
        <v-timeline density="comfortable" align="start" side="end" line-thickness="1">
            <v-timeline-item v-for="(detail, index) in details" :key="index" size="md" dot-color="grey">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>{{ pub_var.getApproverByLevel(detail.level, 1) }}</strong>
                        <div class="mb-2">
                            <p class="text-dark mt-2"><span class="text-disabled">{{ detail.status === 'Approved' ||
                                detail.status === 'Pending' ? 'Approved by:' : 'Disapproved by:' }}</span> {{
                                        detail.approver ?? "pending" }}</p>
                        </div>
                        <div v-if="detail.remarks" class="mb-3">
                            <p><b>Remarks:</b></p>
                            <p class="text-remarks">{{ detail.remarks }}</p>
                        </div>
                        <v-chip v-if="detail.status === 'Approved'" color="success" size="small" variant="tonal" label>
                            <v-icon icon="mdi-check"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                        <v-chip v-if="detail.status === 'Disapproved'" color="error" size="small" variant="tonal" label>
                            <v-icon icon="mdi-alert-circle-outline"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                        <v-chip v-if="detail.status === 'Pending'" color="info" size="small" variant="tonal" label>
                            <v-icon icon="mdi-update"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                    </div>
                    <span v-if="detail.acted_at" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4">{{ pub_var.formatDate(detail.acted_at) }}</span>
                    </span>
                    <span v-else class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">00 - 00:00:00 - 00</span>
                    </span>
                </div>
                <!-- Internal Processing -->
                <div v-if="detail.level === pub_var.SERVICE_TL && props.status === pub_var.INTERNAL_SERVICING" class="mt-4">
                    <v-row>
                        <v-col>
                            <v-alert color="warning" class="small" closable density="compact" variant="tonal" prominent>
                                <v-icon class="mr-2">mdi-information-outline</v-icon> The Internal Servicing process has
                                started and is being handled. 
                            </v-alert>
                        </v-col>
                    </v-row>
                </div>
                <v-divider></v-divider>
            </v-timeline-item>
        </v-timeline>
    </v-card>
</template>
<script setup>
import { onMounted, ref } from 'vue';
import * as pub_var from '@/global/global';

import { apiRequestAxios } from '@/api/api';
import { EH } from '@/global/global';
const apiRequest = apiRequestAxios();

const props = defineProps({
    service_id: {
        type: Number,
        default: () => 0,
    },
    status: {
        type: Number,
        default: 0,
    }
})

/** Details */
const details = ref([])
const loading = ref(false)
const get_logs = async () => {
    loading.value = true
    try {
        const data = await apiRequest.get('get-approvals', {
            params: {
                service_id: props.service_id,
                type: EH
            }
        })
        if (data.data && data.data.approvals) {
            details.value = data.data.approvals
        }
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    get_logs()
})
</script>




<style>
.v-timeline-item__body {
    width: 100% !important;
}

.timeRemarks {
    width: 60%;
}

.v-timeline-divider__dot .v-icon {
    width: 8px !important;
    min-width: 8px !important;
    height: 8px !important;
}

.v-timeline--vertical.v-timeline--density-comfortable {
    font-size: 13px !important;
}
</style>