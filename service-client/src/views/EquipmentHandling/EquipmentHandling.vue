<template>
    <LayoutWithActions>
        <template #default="{ searchText }">
            <v-card class="mx-auto">
                <vue3-datatable ref="datatable" class="tableLimitText" :rows="rows" :columns="cols" :loading="loading"
                    :search="searchText" @rowSelect="rowSelect" :columnFilter="false" :sortColumn="params.sortColumn"
                    :sortDirection="params.sortDirection" :sortable="true" noDataContent="No records found"
                    skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" :hasCheckbox="true"
                    :selectRowOnClick="true">
                    <template #id="data">
                        <span>{{ pub_var.setReportNumber(data.value.id, data.value.created_at) }}</span>
                    </template>
                    <template #request_type="data">
                        <span>{{ pub_var.requestType(data.value.request_type) }}</span>
                    </template>
                    <template #request_name="data">
                        <span>{{ pub_var.requestName(data.value.request_type, data.value.request_name, data.value.other)
                            }}</span>
                    </template>
                    <template #approver_name="data">
                        <span class="text-danger" v-if="parseInt(data.value.main_status) === pub_var.DISAPPROVED">Disapproved</span>
                        <span class="text-success" v-else-if="parseInt(data.value.main_status) === pub_var.COMPLETE">Completed</span>
                        <span v-else>{{ pub_var.pending_approval_status(data.value.status) }} 
                            {{ pub_var.INSTALLATION_TL === data.value.status || pub_var.INSTALLATION_ENGINEER === data.value.status ? data.value.ssu : '' }}
                        </span>
                    </template>
                    <template #main_status="data">
                        <span :style="{ color: pub_var.setJOStatus(data.value.main_status).color }">{{
            pub_var.setJOStatus(data.value.main_status).text }}</span>
                    </template>
                    <template #created_at="data">
                        <span>{{ moment(data.value.created_at).format('MM/DD/YYYY') }}</span>
                    </template>
                </vue3-datatable>

                <router-view />

            </v-card>
        </template>
    </LayoutWithActions>
</template>
<script setup>
import { onMounted, ref, reactive, provide, watch, defineProps, inject, toRefs } from 'vue';
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import * as pub_var from '@/global/global'
import moment from 'moment';


/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'



/** Declaration of User Data */
const user = user_data();
const role = getRole()
role.getRoleData
const currentUserRole = role.currentUserRole
const btnDisable = ref(true)
const datatable = ref(null)
const selectedId = ref(null)
const status = ref(null)


const apiRequest = user.apiRequest()



/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref('');

const params = reactive({ current_page: 1, pagesize: 10, sortColumn: 'created_at', sortDirection: 'desc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Report Number', isUnique: true, type: 'number', hide: false },
        { field: 'name', title: 'Institution' , hide: false },
        { field: 'address', title: 'Address', hide: false },
        { field: 'user_name', title: 'Requested by' },
        { field: 'request_type', title: 'Request Category', hide: false },
        { field: 'request_name', title: 'Type of Request' , hide: false },
        // { field: 'internal_name', title: 'Internal Request' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date', type: 'date' , hide: false },
        { field: 'created_at', title: 'Date Requested', type: 'date', hide: false  },
        { field: 'approver_name', title: 'Pending Approval', hide: false  }, //minWidth : '300px' 
        { field: 'main_status', title: 'Status', type: 'number' , hide: false }, //  minWidth : '200px',
    ]) || [];

    provide('column', cols)



/**
 * @ Row Select Table Event
 */
const rowSelect = () => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length === 1) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    const extractId = selectedRows.map((data) => { return data.id })
    const statusId = selectedRows.map((data) => { return data.status })
    selectedId.value = extractId[0]
    status.value = statusId[0]
}


/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref('')
const routeView = ref('ViewWorkOrder')
const addView = ref('WorkOrder')
const service_id = ref(null)
const enableCreate = ref(false)



if (currentUserRole === 'Requestor') {
    routeView.value = 'ViewWorkOrder'
    addView.value = 'WorkOrder'
    enableCreate.value = true
}
if (currentUserRole === pub_var.approverRole) {
    routeView.value = 'WorkOrderApprover'
    // actions.service_id = service_id
}
if (currentUserRole === pub_var.TLRole) {
    routeView.value = 'WorkOrderApprover'
}
// if (currentUserRole === pub_var.outboundPersonnel) {
//     routeView.value = 'WorkOrderApprover'
// }
if (currentUserRole === pub_var.engineerRole) {
    routeView.value = 'WorkOrderApprover'
}

const actions = {
    selectedId: selectedId,
    // service_id : service_id,
    // status: status,
    btnDisable: btnDisable,
    enableCreate: enableCreate,
    routeView: routeView,
    addView: addView,
}

provide('data', actions)


const getRequest = async () => {
    if (currentUserRole === pub_var.approverRole) category.value = pub_var.approverRole
    if (currentUserRole === pub_var.TLRole) category.value = pub_var.TLRole
    // if (currentUserRole === pub_var.outboundPersonnel) category.value = pub_var.outboundPersonnel
    if (currentUserRole === pub_var.engineerRole) category.value = pub_var.engineerRole
    try {
        loading.value = true;
        const response = await apiRequest.get('get-equipment-handling', {
            params: { user_id: user.user.id, category: category.value },
        });
        const data = response.data.equipment_handling

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

    getRequest();
};



provide('refresh', getRequest)



/** onMounted */
onMounted(() => {
    getRequest();
});
</script>


<style scoped>
table tbody tr td span:empty::before{
  content: '' !important;
}
</style>












<!-- IT Department
Miguel Alog
Ardison Pagulayan
James Rynne Pakino
Kenedy Kinahingan
08/25/2024 07:58 am

APM/NSM/SM
Valerie Yves Avila
00-00-00 00-00 00 0

Warehouse & Inventory Management
Perseveranda Ibea
Claudine Castaneda
00-00-00 00-00 00 0

Service Dept Team Leader
John Robert Besenio
Jess Rey Umadhay
Jefferson Villapando
Aljon Kim Rey
00-00-00 00-00 00 0

Service Dept Head / Service Engineer
Erick Lloyd Cerda
00-00-00 00-00 00 0

Billing & Invoicing Staff / WIM Personnel
John Elvin Elmedulan
Raul Balleta

Outbound
Abril Reyes -->