<template>
    <BaseLayout>
            <v-card>
                <v-tabs v-model="tab" density="compact" class="border-b-sm">
                    <v-tab value="roles" class="text-none" color="primary"><v-icon class="mr-2">mdi-note-check-outline</v-icon> Standard Checklist Item</v-tab>
                    <!-- <v-tab value="role_assigning" class="text-none" color="primary"><v-icon class="mr-2">mdi-account-details</v-icon> Approval Designation</v-tab> -->
                </v-tabs>

                <v-card-text>
                    <v-window v-model="tab" :disabled="true">
                        <v-window-item value="roles">
                            <ChecklistComponent class="mt-3" />
                        </v-window-item>
                    </v-window>
                </v-card-text>
            </v-card>
    </BaseLayout>
</template>

<script setup>
import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';
import ChecklistComponent from '@/components/Roles/ChecklistComponent.vue';

import { ref } from 'vue'
const tab = ref('one')

</script>
