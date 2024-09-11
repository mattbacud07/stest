<template>
    <v-row>
        <v-col cols="12">
            <v-row>
                <v-col cols="3" lg="2" md="2" sm="2">
                    <v-btn color="primary" :loading="saveLoading" :disabled="selected_role_id ? false : true" 
                    @click="savePermission" prepend-icon="mdi-check" text="SAVE" 
                    :stacked="width <= 425 ? true : false" :density="width <= 425 ? 'compact' : 'default'" 
                        class="text-none ml-2" variant="flat"></v-btn>
                </v-col>
                <v-col cols="9" lg="4" md="4" sm="4">
                    <v-select v-model="selected_role_id" :items="roles" item-title="role_name" item-value="roleID"
                        density="compact" color="primary" variant="outlined" label="Select Role"
                        @click:appendInner="refreshRoles" append-inner-icon="mdi-refresh"></v-select>
                </v-col>
            </v-row>
            <v-row>
                <v-col>
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Modules</th>
                                    <th>Create</th>
                                    <th>Read</th>
                                    <th>Edit</th>
                                    <th>Delete</th>
                                    <th>Approve</th>
                                    <th>Delegate</th>
                                    <th>Installer</th>
                                    <th>Report</th>
                                </tr>
                            </thead>
                            <tbody class="table-group-divider">
                                <tr v-for="(permission, index) in permissions" :key="permission.module" v-if="!loading">
                                    <td>{{ permission.module }}</td>
                                    <td><v-checkbox v-model="permission.create"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.read"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.edit"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.delete"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.approve"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.delegate"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.installer"></v-checkbox></td>
                                    <td><v-checkbox v-model="permission.report"></v-checkbox></td>
                                </tr>
                                <tr v-else>
                                    <td rowspan="9">
                                        <h1 style="opacity: .1;"><v-icon><v-progress-circular indeterminate></v-progress-circular></v-icon></h1>
                                        <p class="text-grey">Loading ...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </v-col>
            </v-row>
        </v-col>
    </v-row>
</template>
<script setup>
import { ref, reactive, onMounted, watch, provide } from 'vue';
import { user_data } from '@/stores/auth/userData'

import { useToast } from 'vue-toast-notification';
const toast = useToast()

import {useDisplay} from 'vuetify'
const { width } = useDisplay()

const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()
const form = ref(false)
const btnDisable = ref(false)
const loading = ref(false)
const saveLoading = ref(false)
const roles = ref([])
const selected_role_id = ref(null)


/** Permissions */
const permissions = ref([
    { "module": "EquipmentHandling", "create": false, "read": false, "edit": false, "delete": false, "approve": false, "delegate": false, "installer": false, "report": false, "name": "EquipmentHandling" },
    { "module": "InternalServicing", "create": false, "read": false, "edit": false, "delete": false, "approve": false, "delegate": false, "installer": false, "report": false, "name": "InternalServicing" },
    { "module": "PreventiveMaintenance", "create": false, "read": false, "edit": false, "delete": false, "approve": false, "delegate": false, "installer": false, "report": false, "name": "PreventiveMaintenance" },
    { "module": "CorrectiveMaintenance", "create": false, "read": false, "edit": false, "delete": false, "approve": false, "delegate": false, "installer": false, "report": false, "name": "CorrectiveMaintenance" },
])

watch(selected_role_id, async(newValID) => {
    await getPermission(newValID)
})



/** Save Permission */
const savePermission = async() =>{
    saveLoading.value = true
    try {
        const response = await apiRequest.put('set_permissions_per_role',{
            role_id : selected_role_id.value,
            permissions: JSON.stringify(permissions.value)
        })
        if(response.data && response.data.success){
            toast.success('Successfully save')
        }
    } catch (error) {
        alert(error)
    }finally{
        saveLoading.value = false
    }
}



/** Get Permission each Role */
const getPermission = async (selected_role) => {
    loading.value = true
    try {
        const response = await apiRequest.get('get_permission_per_role', {
            params: { role_id: selected_role }
        })
        if (response.data && response.data.permission) {
            const permissionData = response.data.permission
            if(permissionData && permissionData.permissions){
                permissions.value = JSON.parse(permissionData.permissions)
            }else{
                permissions.value.forEach(v => {
                    v.create = false
                    v.read = false
                    v.edit = false
                    v.delete = false
                    v.approve = false
                    v.delegate = false
                    v.installer = false
                    v.report = false
                }) 
            }
        }
    } catch (error) {
        console.log(error)
    }finally{
        loading.value = false
    }
}


/** Get Role Data */
const getRoleData = async () => {
    try {
        // loading.value = true;
        const response = await apiRequest.get('get_role_name');
        if (response.data && response.data.role_name) {
            roles.value = response.data.role_name.map(v => ({
                roleID: v.roleID,
                role_name: v.role_name
            }))
        }
    } catch (error) {
        console.log(error)
    }
    // loading.value = false;
};
const refreshRoles = () => {
    getRoleData()
}


onMounted(() => {
    getRoleData();
    getPermission();
});
</script>

<style scoped>
.v-input {
    font-size: 18px !important;
}
</style>