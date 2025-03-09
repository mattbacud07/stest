<template>
    <v-card>
        <v-timeline density="comfortable" align="start" side="end" line-thickness="1">
            <template v-for="(detail, index) in history" :key="index">
                <v-timeline-item size="md" dot-color="grey">
                    <div class="d-flex justify-space-between">
                        <div>   
                            <p class="font-weight-bold text-grey-darken-1 cursor-pointer">{{ pub_var.getApproverByLevel(detail.level, 1) }}
                                <v-tooltip activator="parent" min-width="200px" bottom>
                                    <p><v-icon>mdi-account-arrow-down-outline</v-icon> Approvers:</p>
                                    <p class="mb-1 mt-1 text-grey" v-for="(approver, index) in detail?.approvers" :key="index">
                                        <v-icon>mdi-circle-small</v-icon>
                                        {{ approver?.users?.full_name }}</p>
                                </v-tooltip>
                            </p>
                            <div class="mb-2">
                                <p class="text-dark mt-2" v-if="detail.users?.full_name"><span class="text-disabled">{{ detail.status === 'Approved' ||
                                    detail.status === 'Pending' ? 'Approved by:' : 'Disapproved by:' }}</span> {{
                                            detail.users?.full_name ?? "pending" }}</p>
                            </div>
                            
                            <div v-if="detail.level === pub_var.OUTBOUND" class="mb-3">
                                <p class="text-disabled">Driver: {{ detail.driver ?? '---' }} {{ detail.receiving_option }}</p>
                            </div>

                            <div v-if="detail.remarks" class="mb-3">
                                <p><b>Remarks:</b></p>
                                <p class="text-remarks text-disabled">{{ detail.remarks }}</p>
                            </div>
                            <div v-if="detail.users?.full_name">
                                <v-chip v-if="detail.status === 'Approved'" color="success" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-check"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                            <v-chip v-if="detail.status === 'Disapproved'" color="error" size="small" variant="tonal"
                                label>
                                <v-icon icon="mdi-alert-circle-outline"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                            <v-chip v-if="detail.status === 'Pending'" color="info" size="small" variant="tonal" label>
                                <v-icon icon="mdi-update"></v-icon> &nbsp; {{ detail.status }}</v-chip>
                            </div>
                        </div>
                        <span v-if="detail.acted_at" class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4">{{ pub_var.formatDate(detail.acted_at) }}</span>
                        </span>
                        <span v-else class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4 text-disabled">00 - 00:00:00 - 00</span>
                        </span>
                    </div>
                    <!-- Internal Processing -->
                    <div v-if="detail.level === pub_var.SERVICE_TL && status === pub_var.INTERNAL_SERVICING"
                        class="mt-4">
                        <v-row>
                            <v-col>
                                <v-alert color="warning" class="small" closable density="compact" variant="tonal"
                                    prominent>
                                    <v-icon class="mr-2">mdi-information-outline</v-icon> The Internal Servicing process
                                    has
                                    started and is being handled.
                                </v-alert>
                            </v-col>
                        </v-row>
                    </div>
                    <v-divider></v-divider>
                </v-timeline-item>
            </template>
                <v-timeline-item size="md" dot-color="primary" v-if="status === pub_var.COMPLETE || status === pub_var.STORAGE || status === pub_var.INSTALLING">
                    <strong>{{ pub_var.setJOStatus(status).text }}</strong>
                </v-timeline-item>
        </v-timeline>
    </v-card>
</template>
<script setup>
import { computed, toRefs } from 'vue';
import * as pub_var from '@/global/global';

const props = defineProps({
    history: {
        type: Array,
        default: () => ([]),
    },
    status: {
        type: Number,
        default: null,
    },
})
const { history, status } = toRefs(props);


const listOFApprovers = computed(() => {
    const approvers = detail?.approvers
    
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
    font-size: 12px !important;
}
</style>