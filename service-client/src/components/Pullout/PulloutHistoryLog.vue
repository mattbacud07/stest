<template>
    <v-skeleton-loader v-if="loading" type="article"></v-skeleton-loader>
    <v-card v-else>
        <v-timeline density="comfortable" align="start" side="end" line-thickness="1">
            <template v-for="(detail, index) in details" :key="index">

                <v-timeline-item size="md" dot-color="grey" v-if="detail.level === OPERATION_SERVICE">
                    <div>
                        <strong>{{ pullout_approver(detail.level) }}</strong>
                    </div>
                </v-timeline-item>

                <v-timeline-item v-else size="md" dot-color="grey">
                    <div class="d-flex justify-space-between">
                        <div>
                            <strong>{{ pullout_approver(detail.level) }}</strong>
                            <div class="mb-2">
                                <p class="text-dark mt-2"><span class="text-disabled">{{ detail.status === 'Approved' ||
                                    detail.status === 'Pending' ? 'Approved by:' : 'Disapproved by:' }}</span> {{
                                            detail.approver ?? "pending" }}</p>
                            </div>
                            <div v-if="detail.remarks" class="mb-3">
                                <p><b>Remarks:</b></p>
                                <p class="text-remarks">{{ detail.remarks }}</p>
                            </div>
                            <v-chip v-if="detail.status === 'Approved'" color="success" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-check"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                            <v-chip v-if="detail.status === 'Disapproved'" color="error" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-alert-circle-outline"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                            <v-chip v-if="detail.status === 'Pending'" color="info" size="small" variant="tonal" label>
                                <v-icon icon="mdi-update"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                        </div>
                        <span v-if="detail.acted_at" class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4">{{ formatDate(detail.acted_at) }}</span>
                        </span>
                        <span v-else class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4 text-disabled">00 - 00:00:00 - 00</span>
                        </span>
                    </div>
                    <v-divider></v-divider>
                </v-timeline-item>
            </template>
        </v-timeline>
    </v-card>
</template>
<script setup>
import { onMounted, ref } from 'vue';

import { apiRequestAxios } from '@/api/api';
import { formatDate, PULLOUT } from '@/global/global';
import { pullout_approver } from '@/global/pullout';
const apiRequest = apiRequestAxios();
import { OPERATION_SERVICE } from '@/global/pullout';

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
                type: PULLOUT
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