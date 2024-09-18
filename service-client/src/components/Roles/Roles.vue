<template>
    <v-row>
        <v-col cols="12">
            <v-row>
                <v-col lg="8" md="8" sm="6" cols="12">
                    <v-dialog width="400" scrollable v-model="isActive">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" prepend-icon="mdi-shield-plus-outline" text="Add" class="text-none"
                                v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-shield-plus-outline" title="Role">

                                <v-form @submit.prevent="saveRole" ref="form">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-text-field v-model="role_name" :rules="rule.role" density="compact"
                                            variant="outlined" label="Role Name"></v-text-field>
                                            <v-select v-model="roleID" :items="roleOptions" class="mt-3" :rules="[v=> !!v || 'Required']" density="compact"
                                            variant="outlined" label="Role ID"></v-select>
                                        <v-textarea v-model="description" density="compact" rows="5" label="Description"
                                            variant="outlined" color="primary" class="mt-5"></v-textarea>
                                    </v-card-text>

                                    <v-divider></v-divider>

                                    <v-card-actions>
                                        <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                            @click="isActive.value = false"></v-btn>
                                        <v-spacer></v-spacer>

                                        <v-btn type="submit" color="primary"
                                            prependIcon="mdi-content-save-check-outline" class="text-none" text="Save"
                                            variant="flat" :loading="loadingSave"></v-btn>
                                    </v-card-actions>
                                </v-form>
                            </v-card>
                        </template>
                    </v-dialog>


                    <v-btn color="primary" :disabled="btnDisable" prepend-icon="mdi-shield-edit-outline" text="Edit"
                        class="text-none ml-2" variant="tonal"></v-btn>


                    <v-dialog width="400" scrollable v-model="deleteRoleDialog">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="error" :disabled="btnDisable" prepend-icon="mdi-delete-empty" text="Delete"
                                class="text-none ml-2" variant="plain" v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-trash-can" subtitle="Delete Role">
                                <p class="p3"></p>
                                <v-card-text>Are you sure you want to delete?</v-card-text>
                                <v-card-actions>
                                    <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                        @click="isActive.value = false"></v-btn>
                                    <v-spacer></v-spacer>

                                    <v-btn color="error" prepend-icon="mdi-shield-remove-outline" text="Delete"
                                        class="text-none ml-2" variant="flat" @click="deleteRole"
                                        :loading="loadingSave"></v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>

                </v-col>
                <v-spacer></v-spacer>
                <v-col lg="4" md="4" sm="6" cols="12">
                    <v-text-field color="primary" v-model="params.search" clearable density="compact"
                        label="Search all fields" variant="outlined"></v-text-field>
                </v-col>
            </v-row>
            <!-- <p class="text-danger" style="font-size: .8em;">* Select at least one</p> -->
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :sortColumn="params.sort_column" :sortDirection="params.sort_direction" skin="bh-table-compact bh-table-bordered"
                class="mt-5" @rowSelect="isChecked">
                <template #created_at="data">
                    <span>{{ pub_var.formatDate(data.value.created_at) }}</span>
                </template>
                <!-- <template #id="data">
                    <v-dialog width="400" scrollable>
                        <template v-slot:activator="{ props: showPermission }">
                            <v-btn prepend-icon="mdi-account-key-outline" color="primary" variant="tonal"
                                class="text-none rounded-0" density="compact" text="Permissions"
                                v-bind="showPermission"></v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card :title="data.value.role_name" subtitle="Set Permissions">
                                <v-form @submit.prevent="savePermission" ref="form">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-checkbox color="primary" v-model="permissionStatus[permission.id]"
                                            true-icon="mdi-checkbox-marked-outline"
                                            v-for="permission in getPermissionByRole(data.value.roleID)"
                                            :key="permission.id" :label="permission.access_type"
                                            @update:model-value="savePermission(permission.id, $event)"></v-checkbox>
                                    </v-card-text>
                                </v-form>
                            </v-card>
                        </template>
                    </v-dialog>
                    
                    <v-dialog width="400" scrollable>
                        <template v-slot:activator="{ props: showModule }">
                            <v-btn prepend-icon="mdi-file-account-outline" color="primary" variant="tonal"
                                class="text-none rounded-0 ml-3" density="compact" text="Modules"
                                v-bind="showModule"></v-btn>
                        </template>
                        <template v-slot:default="{ isActive }">
                            <v-card :title="data.value.role_name" subtitle="Set Module">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-checkbox color="primary" v-model="moduleStatus[modules.id]"
                                            true-icon="mdi-checkbox-marked-outline"
                                            v-for="modules in getModuleByRole(data.value.roleID)"
                                            :key="modules.id" :label="modules.title"
                                            @update:model-value="saveModules(modules.id, $event)"></v-checkbox>
                                    </v-card-text>
                            </v-card>
                        </template>
                    </v-dialog>
                </template> -->
            </vue3-datatable>
        </v-col>
        <alertMessage v-if="messageDetails.show" :details="messageDetails" />
    </v-row>
</template>
<script setup>
import { ref, reactive, onMounted, watch, provide } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global.js'
import alertMessage from '@/components/PopupMessage/alertMessage.vue'


/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** ToastPlugin Notifcation */
import {useToast} from 'vue-toast-notification'
const toast = useToast()


const user = user_data()
user.getUserData
const apiRequest = apiRequestAxios()
const form = ref(false)
const role_name = ref('')
const description = ref('')
const datatable = ref(null)
const isActive = ref(false)
const deleteRoleDialog = ref(false)
const btnDisable = ref(true)
const loadingSave = ref(false)
const refreshKey = ref(0)
const messageDetails = ref({})
const rowId = ref(null)
const roleID = ref('')
const roleOptions = ref(Array.from({length: 20}, (_, i) => i + 1))

const rule = ref({
    role: [
        v => !!v || 'Required'
    ],
})


/** Check - Selecting Users */
const isChecked = () => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length > 0) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    rowId.value = selectedRows.map(v => v.id)[0]
}

/** Add Role */
const saveRole = async () => {
    loadingSave.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSave.value = false
        return
    }
    try {
        const response = await apiRequest.post('add_role_name', {
            role_name: role_name.value,
            roleID: roleID.value,
            description: description.value,
        })
        if (response.data && response.data.success) {
            toast.success('Successfully created')
            isActive.value = false
            getRoleName()
            getPermission()
            getModules();
            form.value.reset()
        }else if(response.data && response.data.role_id_exist) {
            toast.error('Role ID or role name exist')
        }
    } catch (error) {
        console.log(error);
    } finally {
        loadingSave.value = false
    }
}


/** Delete Role */
const deleteRole = async () => {
    try {
        const response = await apiRequest.delete('delete_role_name', {
            params: {
                id: rowId.value
            }
        })
        if (response.data && response.data.success) {
            toast.success('Successfully deleted')
            deleteRoleDialog.value = false
            getRoleName()
        }
    } catch (error) {
        console.log(error)
    } finally {
        // loadingSave.value = false
    }
}







/** Fetching Data Users */
const loading = ref(true);
const total_rows = ref(0);


const params = reactive({ current_page: 1, pagesize: 10, sort_column : 'roleID', sort_direction : 'asc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'roleID', title: 'Role ID', sortable: true },
        { field: 'role_name', title: 'Role' },
        { field: 'description', title: 'Description' },
        { field: 'created_at', title: 'Created_at' },
        // { field: 'id', title: 'Permission' },
    ]) || [];


/**
 * Get RoleS
 */
const getRoleName = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_role_name');
        const data = response.data.role_name

        rows.value = data
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

/************************** Get Permissions and Save Permission ***********************************/
const permission = ref([])
const permissionStatus = ref({})
const getPermissionByRole = (role_id) => {
    return permission?.value.filter(data => data.role_id === role_id)
}
// Get Permission
// const getPermission = async () => {
//     try {
//         loading.value = true;
//         const response = await apiRequest.get('get_permissions');
//         permission.value = response.data.permission

//         permissionStatus.value = permission.value.reduce((collection, permission) => {
//             collection[permission.id] = permission.status === 1
//             return collection
//         }, {})


//     } catch (error) {
//         console.log(error)
//     }

//     loading.value = false;
// };

// Save Permission
// const savePermission = async (id, isChecked) => {
//     const value = isChecked ? 1 : 0
//     try {
//         loading.value = true;
//         const response = await apiRequest.put('set_permissions',{
//             id : id, status : value
//         });
//         if(response.data && response.data.success){
//             await getPermission()
//             messageDetails.value = { show: true, color: 'success', text: 'Successfully set permission' }
//         }
//     } catch (error) {
//         console.log(error)
//     }
// }



/************************** Get Modules and Save Modules ***********************************/
const modules = ref([])
const moduleStatus = ref({})
const getModuleByRole = (role_id) => {
    return modules?.value.filter(data => data.role_id === role_id)
}
// Get Modules
// const getModules = async () => {
//     try {
//         loading.value = true;
//         const response = await apiRequest.get('get_modules');
//         modules.value = response.data.modules

//         moduleStatus.value = modules.value.reduce((collection, modules) => {
//             collection[modules.id] = modules.status === 1
//             return collection
//         }, {})


//     } catch (error) {
//         console.log(error)
//     }

//     loading.value = false;
// };

// Save Modules
// const saveModules = async (id, isChecked) => {
//     const value = isChecked ? 1 : 0
//     try {
//         loading.value = true;
//         const response = await apiRequest.put('set_modules',{
//             id : id, status : value
//         });
//         if(response.data && response.data.success){
//             await getModules();
//             messageDetails.value = { show: true, color: 'success', text: 'Successfully set module' }
//         }
//     } catch (error) {
//         console.log(error)
//     }
// }



onMounted(() => {
    getRoleName();
    // getPermission();
    // getModules();
});
</script>