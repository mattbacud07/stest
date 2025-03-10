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
                                        <v-select v-model="roleID" :items="roleOptions" class="mt-3"
                                            :rules="[v => !!v || 'Required']" density="compact" variant="outlined"
                                            label="Role ID"></v-select>
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

                    <!-- Edit Role -->
                    <v-dialog width="400" scrollable v-model="isUpdate">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" variant="tonal" :disabled="btnDisable"
                                prepend-icon="mdi-shield-edit-outline" text="Edit" class="text-none ml-2"
                                v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-shield-edit-outline" title="Edit Role">

                                <v-form @submit.prevent="editRole" ref="form">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-text-field v-model="role_name" :rules="rule.role" density="compact"
                                            variant="outlined" label="Role Name"></v-text-field>
                                        <v-select v-model="roleID" disabled :items="roleOptions" class="mt-3"
                                            :rules="[v => !!v || 'Required']" density="compact" variant="outlined"
                                            label="Role ID"></v-select>
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
            <p class="text-danger" style="font-size: .8em;">* Please consult your IT administrator before making any
                changes</p>
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :sortColumn="params.sort_column"
                :sortDirection="params.sort_direction" skin="bh-table-compact bh-table-bordered" class="mt-5"
                @rowSelect="isChecked" @rowClick="rowClickEvent">
                <template #created_at="data">
                    <span>{{ pub_var.formatDate(data.value.created_at) }}</span>
                </template>
            </vue3-datatable>
        </v-col>
    </v-row>
</template>
<script setup>
import { ref, reactive, onMounted, watch, provide } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global.js'


/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** ToastPlugin Notifcation */
import { useToast } from 'vue-toast-notification'
const toast = useToast()


const user = user_data()
user.getUserData
const apiRequest = apiRequestAxios()
const form = ref(false)
const role_name = ref('')
const description = ref('')
const datatable = ref(null)
const isActive = ref(false)
const isUpdate = ref(false)
const deleteRoleDialog = ref(false)
const btnDisable = ref(true)
const loadingSave = ref(false)
const refreshKey = ref(0)
const rowId = ref(null)
const roleID = ref('')
const roleOptions = ref(Array.from({ length: 20 }, (_, i) => i + 1))

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

/** RowCllick Event */
const id = ref(null)
const rowClickEvent = (data) => {
    id.value = data.id
    role_name.value = data.role_name
    roleID.value = data.roleID
    description.value = data.description
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
            form.value.reset()
            toast.success('Successfully created')
            isActive.value = false
            getRoles()
        } else if (response.data && response.data.role_id_exist) {
            toast.error('Role ID or role name exist')
        }
    } catch (error) {
        console.log(error);
    } finally {
        loadingSave.value = false
    }
}


/** Edit Role */
const editRole = async () => {
    loadingSave.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSave.value = false
        return
    }
    try {
        const response = await apiRequest.put('edit_role_name', {
            id: id.value,
            role_name: role_name.value,
            // roleID: roleID.value,
            description: description.value,
        })
        if (response.data && response.data.success) {
            form.value.reset()
            toast.success('Updated successfully')
            isUpdate.value = false
            getRoles()
        } else if (response.data && response.data.role_id_exist) {
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
            getRoles()
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


const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'id', sort_direction: 'asc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'roleID', title: 'Role ID' },
        { field: 'role_name', title: 'Role' },
        { field: 'description', title: 'Description' },
        { field: 'created_at', title: 'Created_at' },
    ]) || [];


/**
 * Get RoleS
 */
const getRoles = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_roles');
        const data = response.data.role_name

        rows.value = data
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



onMounted(() => {
    getRoles();
});
</script>