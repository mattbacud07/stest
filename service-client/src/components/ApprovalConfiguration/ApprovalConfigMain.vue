<template>
    <div>
        <v-card>
        <v-tabs v-model="tab" density="compact" class="border-b-sm">
            <v-tab value="one" class="text-none" color="primary">Set Approver</v-tab>
            <v-tab value="two" class="text-none" color="primary">View</v-tab>
        </v-tabs>

        <v-card-text>
            <v-window v-model="tab">
                <v-window-item value="one">
                    <SetApprovalDesignation class="mt-3" />
                </v-window-item>
                
                <!-- <v-window-item value="one">
                    <SetApprovals class="mt-3" />
                </v-window-item>

                <v-window-item value="two">
                    <ViewApprover class="mt-3" :key="viewApproverKey"/>
                </v-window-item> -->
            </v-window>
        </v-card-text>
    </v-card>
    </div>
</template>

<script setup>
import SetApprovals from './SetApprovals.vue';
import ViewApprover from './ViewApprover.vue';
import { ref, watch } from 'vue'

const viewApproverKey = ref(0)

const tab = ref('one')

watch (tab, (newValue) => {
    viewApproverKey.value++
})
</script>