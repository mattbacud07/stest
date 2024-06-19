<template>
    <v-col cols="12" style="background: #3333330d;border-radius: 3px;">
        <v-row>
            <v-col cols="7">
                <v-btn color="primary" size="small" class="text-none mr-2"
                    :disabled="btnDisabledUpdate"><v-icon>mdi-file-document-edit</v-icon> Edit</v-btn>
                <v-btn color="error" @click="deleteRole" size="small" class="text-none" :disabled="btnDisabledDelete"
                    :loading="loadingDelete"><v-icon>mdi-delete-empty</v-icon> Delete</v-btn>
            </v-col>
            <v-col cols="5">
                <v-text-field color="primary" v-model="params.search" clearable density="compact"
                    label="Search all fields" variant="outlined"></v-text-field>
            </v-col>
        </v-row>
        <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
            :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :hide="true" :filter="true"
            skin="bh-table-compact bh-table-bordered" @rowSelect="rowSelect">
        </vue3-datatable>
        <v-snackbar color="success" v-model="infoSuccess" location="right bottom" :timeout="5000">
            <v-icon>mdi-alert-circle-outline</v-icon> Success
        </v-snackbar>
    </v-col>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import axios from 'axios'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const uri = BASE_URL
const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()
const datatable = ref(null)
const btnDisable = ref(true)
const loadingSave = ref(false)
const loadingDelete = ref(false)
const btnDisabledUpdate = ref(true)
const btnDisabledDelete = ref(true)
const infoSuccess = ref(false)




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
}



/** Delete Roles */
const deleteRole = async () => {
    loadingDelete.value = true
    try {
        const selectedIds = datatable.value.getSelectedRows().map(row => row.id)
        const response = await apiRequest.delete('delete-role',
            {
                params : { id: selectedIds }
            }
        )
        if (response.data && response.data.success) {
            infoSuccess.value = true
        } else {
            console.log(response.data.error)
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingDelete.value = false
        console.log(selectedIds.value)
    }
}














/** Fetching Data Users */
const loading = ref(true);
const total_rows = ref(0);
/** Enable Filter */
const enableFilter = ref(false)


const params = reactive({ current_page: 1, pagesize: 10 });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'first_name', title: 'First Name' },
        { field: 'last_name', title: 'Last Name' },
        { field: 'name', title: 'Department' },
        { field: 'position_name', title: 'Position', hide: true },
        { field: 'role_name', title: 'Role' },
    ]) || [];

const getUsers = async () => {
    try {
        loading.value = true;
        const response = await axios.get(uri + 'api/assigned-user-role', {
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.users_role

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};
const changeServer = (data) => {
    params.current_page = data.current_page;
    params.pagesize = data.pagesize;

    getUsers();
};


onMounted(() => {
    getUsers();
});
</script>