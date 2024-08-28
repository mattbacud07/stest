<template>
    <div>
        <v-row>
            <v-col cols="8" xl="5" md="5" sm="5">
                <v-text-field color="primary" v-model="params.search" clearable density="compact" label="Search all fields"
                    variant="outlined"></v-text-field>
            </v-col>
            <v-col cols="4" xl="7" md="7" sm="7"  style="text-align: right;">
                <v-btn color="error" align-self="end" :disabled="disableDelete"
                    :loading="deleteLoading" class="text-none" @click="deleteApprover">
                    <v-icon>mdi-trash-can-outline</v-icon> Delete</v-btn>
                <p class="text-body-1" color="success" v-if="deletedMessage">Record deleted successfully</p>
                </v-col>
        </v-row>
        <v-row>
            <v-col cols="12"> <!-- :hasCheckbox="true" -->
                <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                    :selectRowOnClick="true" :sortable="true" :hide="true" :filter="true" :hasCheckbox="true"
                    skin="bh-table-compact bh-table-bordered" class="set_approver_tables" @rowClick="rowClick">
                </vue3-datatable>
            </v-col>
        </v-row>
    </div>
</template>
<script setup>
import { onMounted, ref, reactive } from 'vue';

import { user_data } from '@/stores/auth/userData'
import { alertStore } from '@/stores/alert-popup'
// import * as designation from '@/global/global'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'
const datatable = ref(null)

const alert = alertStore()

/** Declaration of User Data */
const user = user_data();
user.getUserData
const apiRequest = user.apiRequest()

/** Button Delete */
const disableDelete = ref(true)
const deleteLoading = ref(false)
const deletedMessage = ref(false)

/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref(0);

const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'approval_level', sort_direction: 'asc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide : true },
        { field: 'user_id', title: 'User ID', isUnique: true, type: 'number' },
        { field: 'user_name', title: 'Name' },
        { field: 'name', title: 'Position' },
        { field: 'approval_level', title: 'Approval Level', hide: true },
        { field: 'approval_level_name', title: 'Designation', filterable: true },
    ]) || [];


/** RowClick Section */
const rowClick = () => {
    if (Object.keys(datatable.value.getSelectedRows()).length === 0) {
        disableDelete.value = true
    } else {
        disableDelete.value = false
    }
}


/** Delete Approver Section */
const deleteApprover = async () => {
    deleteLoading.value = true
    try {
        const selectedIds = datatable.value.getSelectedRows().map(row => row.id)
        // console.log(selectedIds)
        const response = await apiRequest.delete('delete-approver', {
            params: {
                ids: selectedIds
            }
        })
        if (response.data && response.data.isDeleted) {
            deletedMessage.value = true
            setTimeout(() =>{
                deletedMessage.value = false
            }, 5000)
            disableDelete.value = true
            getUsers()
        }else{
            alert('Something went wrong')
        }
    } catch (error) {
        console.log(error)
    }
    finally{
        deleteLoading.value = false
        // disableDelete.value = true
    }
}

const getUsers = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-approver');
        const data = response.data.users

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


<style>
.set_approver_table {
    padding: 3px;
    border-radius: 5px;
    border: 1px solid #99999934;
}
</style>