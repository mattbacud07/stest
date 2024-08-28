<template>
    <v-col cols="12">
        <v-row>
            <v-col cols="2">
                <v-select color="primary" v-model="role" :items="get_role_name" clearable
                    item-title="role_name" item-value="role_id" density="compact" variant="plain" placeholder="Select Role"></v-select>
            </v-col>
            <v-col cols="7">
                <v-btn color="primary" size="small" class="text-none mr-2"
                    :disabled="btnDisabledUpdate"><v-icon>mdi-file-document-edit</v-icon> Edit</v-btn>
                <v-btn color="error" size="small" class="text-none" :disabled="btnDisabledDelete"
                    @click="deleteUserRole = true"><v-icon>mdi-delete-empty</v-icon> Delete</v-btn>
                <v-col cols="12" md="6">

                    <v-dialog v-model="deleteUserRole" max-width="340">
                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-trash-can-outline" text="Are you sure you want to delete?"
                                title="Delete">
                                <template v-slot:actions>
                                    <v-btn class="ml-auto text-none" variant="tonal" text="No"
                                        @click="deleteUserRole = false"></v-btn>
                                    <v-btn color="error" :loading="loadingDelete" variant="flat" class="text-none"
                                        text="Delete" @click="deleteRole"></v-btn>
                                </template>
                            </v-card>
                        </template>
                    </v-dialog>
                </v-col>

            </v-col>
            <v-col cols="3">
                <v-text-field color="primary" v-model="params.search" clearable density="compact"
                    label="Search all fields" variant="outlined"></v-text-field>
            </v-col>
        </v-row>
        <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
            :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :sortColumn="params.sort_column"
            :sortDirection="params.sort_direction" :hide="true" :filter="true"
            skin="bh-table-compact bh-table-bordered" @rowSelect="rowSelect">
        </vue3-datatable>
        <v-snackbar color="success" v-model="infoSuccess" location="right bottom" :timeout="5000">
            <v-icon>mdi-alert-circle-outline</v-icon> Success
        </v-snackbar>
    </v-col>
</template>
<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { user_data } from '@/stores/auth/userData'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()
const datatable = ref(null)
const deleteUserRole = ref(false)
const btnDisable = ref(true)
const loadingSave = ref(false)
const loadingDelete = ref(false)
const btnDisabledUpdate = ref(true)
const btnDisabledDelete = ref(true)
const infoSuccess = ref(false)
const rowId = ref([])
const role = ref(null)




/** RowClick Table Fucntion */
const rowSelect = () => {
    const rowSelected = datatable.value.getSelectedRows();
    if (rowSelected.length > 1) {
        btnDisabledUpdate.value = true
    } else if (rowSelected && rowSelected.length > 0) {
        btnDisabledUpdate.value = false
        btnDisabledDelete.value = false
    } else {
        btnDisabledUpdate.value = true
        btnDisabledDelete.value = true
    }

    rowId.value = rowSelected.map(v => v.id)[0]
}



/** Delete Roles */
const deleteRole = async () => {
    loadingDelete.value = true
    try {
        const response = await apiRequest.delete('delete-role',
            {
                params: { id: rowId.value }
            }
        )
        if (response.data && response.data.success) {
            infoSuccess.value = true
            deleteUserRole.value = false
            getAssignedUserRole(role.value)

        } else {
            console.log(response.data.error)
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingDelete.value = false
    }
}














/** Fetching Data Users */
const loading = ref(true);
const total_rows = ref(0);
/** Enable Filter */
const enableFilter = ref(false)


const params = reactive({ 
    current_page: 1, 
    pagesize: 10,
    sort_column : 'roles.role_name',
    sort_direction: 'desc'
});
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'users.first_name', title: 'First Name' },
        { field: 'users.last_name', title: 'Last Name' },
        { field: 'users.name', title: 'Department' },
        { field: 'users.position_name', title: 'Position', hide: true },
        { field: 'roles.role_name', title: 'Role' },
        { field: 'SSU', title: 'SSU' },
    ]) || [];

const getAssignedUserRole = async (role_id) => {
    try {
        loading.value = true;
        const response = await apiRequest.get('assigned-user-role', {
            params: { role : role_id }
        });
        const data = response.data.users_role

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};


watch(role, (val) =>{
    getAssignedUserRole(val)
})


/** Get Roles [Role Name] */
const get_role_name = ref([])
const getRoleName = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_role_name');
        const data = response.data.role_name

        get_role_name.value = data.map(v => ({ role_id: v.roleID, role_name: v.role_name }))

    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



onMounted(() => {
    getAssignedUserRole()
    getRoleName()
});
</script>