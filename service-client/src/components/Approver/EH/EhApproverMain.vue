<template>
    <div>
        <v-row justify="space-between" class="topActions">
            <div>
                <nav class="mt-5 ml-3">
                    <ol class="breadcrumb">
                        <!-- <li class="breadcrumb-item"><a href="#">
                                <router-link to="/approver-equipment-handling">
                                    <v-icon>mdi-menu-left</v-icon> back
                                </router-link></a></li> -->
                        <li class="breadcrumb-item"><a href="#">Equipment Handling</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Work Order</li>
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mr-4">
                
                <router-link :to="{name: 'WorkOrderApprover', params : {id : selectedId ?? 0}}">
                    <v-btn type="submit" color="primary" :disabled="btnDisabled"
                    class="text-none btnSubmit"><v-icon class="mr-2">mdi-file-eye</v-icon>
                    View Request</v-btn>
                </router-link>
            </div>
        </v-row>
        <v-card class="mx-auto p-4 mt-10">
            <v-row>
                <v-col cols="9" style="text-align: left;">
                    <h6 class="text-primary"><b>For Approval</b></h6>
                <span class="text-red" style="font-size: 12px;"><i>{{ selectJustOneMessage }}</i></span>
                </v-col>
                <v-col cols="3">
                    <v-text-field v-model="params.search" clearable density="compact" label="Search all fields"
                        variant="outlined"></v-text-field>
                </v-col>
            </v-row>
            <vue3-datatable ref="datatable" :rows="rows" :sort="true" :columns="cols" :loading="loading" :search="params.search" :sortable="true" :sortColumn="params.sort_column" :sortDirection="params.sort_direction" 
                skin="bh-table-hover bh-table-compact" :hasCheckbox="true" :selectRowOnClick="true" @rowSelect="rowSelect">
                <template #requested_by="data">
                    <span>{{ data.value.first_name }} {{ data.value.last_name }}</span>
                </template>
                <template #id="data">
                    <span>JOF-{{ String(data.value.id).padStart(3,0) }}-{{moment(data.value.created_at).format('YYYY')}}</span>
                </template>
                <template #created_at="data">
                    <span>{{ moment(data.value.created_at).format('MM/DD/YYYY') }}</span>
                </template>
            </vue3-datatable>
            <!-- <vue3-datatable :rows="rows" :columns="cols" :loading="loading" :totalRows="total_rows" :isServerMode="true"
            :pageSize="params.pagesize" @change="changeServer"> </vue3-datatable> -->
        </v-card>
    </div>
</template>
<script setup>
import { onMounted, ref, reactive } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import axios from 'axios'
import moment from 'moment';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

// axios.defaults.withCredentials = true

const btnDisabled = ref(true)
const datatable = ref(null)
const mapSelected = ref([])
const selectedId = ref(null)
const selectJustOneMessage = ref('')

/** Check - Selecting Users */
const rowSelect = (data) => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length  === 1) {
        btnDisabled.value = false
        selectJustOneMessage.value = ''
    } else {
        btnDisabled.value = true
        selectJustOneMessage.value = "Select one record"
    }
    mapSelected.value = selectedRows.map((element)=>{
       return element.id
    })
    selectedId.value = mapSelected.value[0] //SelectedId to pass in other routes for work order approval
}

/** Declaration of User Data */
const user = user_data();

const uri = BASE_URL


/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref(0);

const params = reactive({ current_page: 1, pagesize: 10, sort_column : 'id', sort_direction : 'asc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Report Number', isUnique: true, type: 'number', hide: false, width : 'auto'},
        // { field: 'first_name', title: 'Requested by' },
        { field: 'name', title: 'Institution', filter : true, width : 'auto'},
        { field: 'address', title: 'Address', width : 'auto' },
        { field: 'request_name', title: 'External Request', width : 'auto' },
        { field: 'internal_name', title: 'Internal Request', width : 'auto' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date', type: 'date', width : 'auto' },
        { field: 'requested_by', title: 'Requestor', type: 'date', width : 'auto' },
        { field: 'created_at', title: 'Date Requested', type: 'date', width : 'auto' },
        { field: 'approver_name', title: 'Pending Approval', hide: false, width : 'auto' },
    ]) || [];

const getUsers = async () => {
    try {
        loading.value = true;
        const response = await axios.get(uri + 'api/approver-get-equipment-handling-data', {
            params: { user_id: user.user.id },
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.workOrder

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        alert(error)
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


<!-- <style scoped>
a {
    color: aliceblue;
}
</style> -->
<style scoped>


</style>
