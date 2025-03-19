<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #default>
            <v-card class="mx-auto">
                <vue3-datatable ref="datatable" class="tableLimitText" :rows="rows" :columns="cols" :loading="loading"
                    @rowSelect="rowSelect" :columnFilter="false" :sortColumn="params.sort_column"
                    :sortDirection="params.sort_direction" :sortable="true" noDataContent="No records found"
                    :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" :search="params.search"
                    @change="changeServer" skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover"
                    :hasCheckbox="true" :rowClass="getRowClass" :selectRowOnClick="true" cellClass="internalCell"
                    @rowDBClick="doubleClickViewData">
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
                    <template #level="data">
                            <span>{{ pullout_approver(data.value.level) }}
                                <v-tooltip activator="parent" location="left" v-if="data.value.level === 1">
                                    {{ data.value.role_user?.supervisor?.supervisor_name ?? '---' }}
                                </v-tooltip>
                            </span>
                    </template>
                    <template #status="data">
                            <span>
                                <v-chip label density="compact" :color="pullOutStatus(data.value.status).color">{{ pullOutStatus(data.value.status).text}}</v-chip>
                            </span>
                    </template>
                    <template #created_at="data">
                        <span>{{ pub_var.formatDateNoTime(data.value.created_at) }}</span>
                    </template>
                    <template #final_installation_date="data">
                        <span>{{ pub_var.formatDateNoTime(data.value.final_installation_date) }}</span>
                    </template>
                </vue3-datatable>

                <!-- <router-view /> -->

            </v-card>
        </template>
    </LayoutWithActions>
</template>
<script setup>
import { onMounted, ref, reactive, provide, watch, defineProps, inject, toRefs } from 'vue';
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'
import { requestorID } from '@/global/global';
import moment from 'moment';
import debounce from 'lodash/debounce';
import { useRouter } from 'vue-router';
const router = useRouter()

/** Permissions */
import { permit } from '@/castl/permitted';
const { can } = permit()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'
import { pullout_approver, pullOutStatus } from '@/global/pullout';



/** Declaration of User Data */
const user = user_data();
const role = getRole()
const currentUserRoleID = role.currentUserRole
const btnDisable = ref(true)
const datatable = ref(null)
const selectedId = ref(null)
const status = ref(null)
const ehCount = ref(null)


const apiRequest = apiRequestAxios()


/** Data Tables Componenets */
const loading = ref(true);
const total_rows = ref('');

const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'created_at', sort_direction: 'desc', search : '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Report Number', isUnique: true, type: 'number', hide: false },
        { field: 'name', title: 'Institution', hide: false },
        { field: 'address', title: 'Address', hide: false },
        { field: 'requestedBy', title: 'Requested by' },
        { field: 'proposed_pullout_date', title: 'Proposed Pullout Date', type: 'date', hide: false },
        { field: 'created_at', title: 'Date Requested', type: 'date', hide: false },
        { field: 'level', title: 'Pending Approval', hide: false }, //minWidth : '300px' 
        { field: 'status', title: 'Status', hide: false }, //  minWidth : '200px',
    ]) || [];

/** SearchText */
const getSearchText = (data) => {
    params.search = data
}

provide('column', cols)



/**
 * @ Row Select or Double Click Table Event
 */
const doubleClickViewData = (row) => {
    const routeV = row.status === pub_var.uninstalling ? 'PulloutUninstallation' : 'PullOutRequestView'
    router.push({ name: routeV, params: { id: row.id } })
}
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows()
    if (row && row.length === 1) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    const extractId = row.map((data) => { return data.id })
    const statusId = row.map((data) => { return data.status })
    selectedId.value = extractId[0]
    status.value = statusId[0]
    routeView.value = row[0]?.status === pub_var.uninstalling ? 'PulloutUninstallation' : 'PullOutRequestView'
}
const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}




/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref(null)
const routeView = ref('PullOutRequestView')
const createRequest = ref(null)
const service_id = ref(null)

if (can('create', 'PullOut Request') && role.currentUserRole === requestorID) createRequest.value = 'PullOutRequestForm'


const actions = {
    selectedId: selectedId,
    // service_id : service_id,
    // status: status,
    btnDisable: btnDisable,
    routeView: routeView,
    createRequest: createRequest.value,
}

provide('data', actions)

const getRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('view_pullout', {
            params: { 
                ...params,
                user_id: user.user.id, 
                current_role: currentUserRoleID 
            },
        });
        if (response?.data?.pullout_request) {
            const result = response.data?.pullout_request
            rows.value = result.data
            total_rows.value = result.total
        }
    } catch (error) {
        console.log(error)
    }finally {
        loading.value = false;
    }
};


// ChangeServer for Servre Mode
const debounceSearch = debounce(getRequest, 300)
const changeServer = (data) => {
    params.current_page = data.current_page
    params.pagesize = data.pagesize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if (data.change_type === 'search') {
        debounceSearch()
    } else getRequest()
}


provide('refresh', getRequest)



/** onMounted */
onMounted(() => {
    getRequest();
});
</script>


<style scoped>
* {
    user-select: none;
}

table tbody tr td span:empty::before {
    content: '' !important;
}
</style>
