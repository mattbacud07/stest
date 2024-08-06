

<template>
    <LayoutWithActions>
        <template #default="{searchText}">
            <v-card class="mx-auto border border-opacity p-1 mt-3" flat>
            <vue3-datatable ref="datatable" min-height="300px" class="tableLimitText" :rows="rows" :sort="true" :columns="cols" :loading="loading" :search="searchText" :sortable="true" :sortColumn="params.sort_column" :sortDirection="params.sort_direction" 
                skin="bh-table-hover bh-table-compact bh-table-bordered" :hasCheckbox="true" :selectRowOnClick="true" @rowSelect="rowSelect">
                <template #requested_by="data">
                    <span>{{ data.value.first_name }} {{ data.value.last_name }}</span>
                </template>
                <template #id="data">
                    <span>JOF-{{ String(data.value.id).padStart(3,0) }}-{{moment(data.value.created_at).format('YYYY')}}</span>
                </template>
                <template #request_type="data">
                    <span>{{ pub_var.requestType(data.value.request_type) }}</span>
                </template>
                <template #request_name="data">
                    <span>{{ pub_var.requestName(data.value.request_type, data.value.request_name, data.value.other) }}</span>
                </template>
                <template #created_at="data">
                    <span>{{ moment(data.value.created_at).format('MM/DD/YYYY') }}</span>
                </template>
            </vue3-datatable>
            <!-- <vue3-datatable :rows="rows" :columns="cols" :loading="loading" :totalRows="total_rows" :isServerMode="true"
            :pageSize="params.pagesize" @change="changeServer"> </vue3-datatable> -->
        </v-card>
        </template>
    </LayoutWithActions>
</template>
<script setup>
import { onMounted, ref, reactive, provide } from 'vue';
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import axios from 'axios'
import moment from 'moment';
import * as pub_var from '@/global/global'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

// axios.defaults.withCredentials = true

const btnDisabled = ref(true)
const datatable = ref(null)
const mapSelected = ref([])
const selectedId = ref(null)
const selectJustOneMessage = ref('')


/** Sent to Topbar */
const detailsOfData = {
    selectedId: selectedId,
    btnDisable: btnDisabled,
    routeView: 'WorkOrderApprover',
}
provide('data', detailsOfData)


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
const apiRequest = user.apiRequest()


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
        { field: 'request_type', title: 'Request Category', hide: false },
        { field: 'request_name', title: 'Type of Request' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date', type: 'date', width : 'auto' },
        { field: 'requested_by', title: 'Requestor', type: 'date', width : 'auto' },
        { field: 'created_at', title: 'Date Requested', type: 'date', width : 'auto' },
        { field: 'approver_name', title: 'Pending Approval', hide: false, width : 'auto' },
    ]) || [];

const getEquipmentHandling = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-equipment-handling', {
            params: { user_id: user.user.id, category : 'approverData', }
        });
        const data = response.data.equipment_handling

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        alert(error)
    }

    loading.value = false;
};

provide('refresh', getEquipmentHandling)

onMounted(() => {
    getEquipmentHandling();
});
</script>
