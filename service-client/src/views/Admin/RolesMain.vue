<template>
    <BaseLayout>
            <v-card>
                <v-tabs v-model="tab" density="compact" class="border-b-sm">
                    <v-tab value="roles" class="text-none" color="primary"><v-icon class="mr-2">mdi-account-file</v-icon> Roles</v-tab>
                    <v-tab value="role_assigning" class="text-none" color="primary"><v-icon class="mr-2">mdi-account-details</v-icon> Role Assigning</v-tab>
                    <v-tab value="assigned_roles" class="text-none" color="primary"><v-icon class="mr-2">mdi-account-multiple-outline</v-icon> Assigned Roles</v-tab>
                    <v-tab value="permission" class="text-none" color="primary"><v-icon class="mr-2">mdi-key-alert-outline</v-icon> Permissions</v-tab>
                </v-tabs>

                <v-card-text>
                    <v-window v-model="tab" :disabled="true">
                        <v-window-item value="roles">
                            <Roles class="mt-3" />
                        </v-window-item>

                        <v-window-item value="role_assigning">
                            <RoleAssigning class="mt-3" />
                        </v-window-item>
                        
                        
                        <v-window-item value="assigned_roles">
                            <AssignedRoles class="mt-3" />
                        </v-window-item>
                        
                        <v-window-item value="permission">
                            <Permission class="mt-3" />
                        </v-window-item>
                    </v-window>
                </v-card-text>
            </v-card>
    </BaseLayout>
</template>

<script setup>
import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';

import RoleAssigning from '@/components/Roles/RoleAssigning.vue'
import AssignedRoles from '@/components/Roles/AssignedRoles.vue'
import Roles from '@/components/Roles/Roles.vue'
import Permission from '@/components/Roles/Permission.vue';


import { ref, watch } from 'vue'

// const refreshKey = ref(0)

const tab = ref('one')

// watch(tab, (newValue) => {
//     refreshKey.value++
// })
</script>
