<template>

    <BaseLayout>
        <v-tabs v-model="tab" density="compact" class="border-b-sm">
            <v-tab value="SetApprovalDesignation" class="text-none" color="primary"> <v-icon
                    class="mr-2">mdi-file-account-outline</v-icon> Approver
                Designation</v-tab>
            <!-- <v-tab value="two" class="text-none" color="primary">View</v-tab> -->
        </v-tabs>

        <v-window v-model="tab" :disabled="true">
            <v-window-item value="SetApprovalDesignation">
                <SetApprovalDesignation class="mt-3" />
            </v-window-item>
        </v-window>
    </BaseLayout>
</template>

<script setup>
import { ref } from 'vue'
import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';
import SetApprovalDesignation from '@/components/ApprovalConfiguration/SetApprovalDesignation.vue';


const viewApproverKey = ref(0)

const tab = ref('SetApprovalDesignation')

</script>