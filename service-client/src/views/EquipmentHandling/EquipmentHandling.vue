<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #filter>
            <v-row>
                <v-col cols="12" class="d-flex justify-space-between align-items-center">
                    <v-checkbox label="Advanced filter" color="primary" style="margin-left: -12px;"
                        v-model="showFilter"></v-checkbox>
                    <!-- <v-spacer></v-spacer> -->
                    <transition name="slide-x-transition">
                        <v-col v-if="showFilter" class="d-flex">
                            <!-- Filter by Institution  -->
                            <v-col cols="3" class="pa-0 mr-1">
                                <v-combobox transition="slide-x-transition" :loading="loadingInstitutionData"
                                    color="primary" v-model="filter.filterInstitution" label="Institution"
                                    density="compact" :items="institutionData" chips closable-chips single-line
                                    itemValue="institution_id" variant="outlined" itemTitle="key" multiple>
                                </v-combobox>
                            </v-col>

                            <v-col cols="3" class="pa-0 mr-1">
                                <VueDatePicker v-model="filter.delegation_date" auto-apply range model-type="yyyy-MM-dd"
                                    :enable-time-picker="false" placeholder="Delegation Date" />
                            </v-col>
                            <!-- Filter by Status  -->
                            <v-col cols="auto" class="pa-0 mr-1">
                                <v-menu transition="slide-x-transition" v-model="filterByStatusDropDown"
                                    :close-on-content-click="false" min-width="400" width="400" height="auto"
                                    location="bottom">
                                    <template v-slot:activator="{ props }">
                                        <v-btn color="primary" variant="tonal" v-bind="props" class="text-none">
                                            <span class="text-primary" v-if="filter.filterStatus.length > 0">{{
                                                filter.filterStatus.length + " " }} </span>
                                            Status <v-icon>mdi-chevron-down</v-icon> </v-btn>
                                    </template>

                                    <v-card width="auto" class="pr-3">
                                        <v-checkbox v-model="filter.filterStatus" class="columnChooserCheckBox"
                                            label="Pending" :value="pub_var.PENDING"></v-checkbox>
                                        <v-checkbox v-model="filter.filterStatus" class="columnChooserCheckBox"
                                            label="Disapproved" :value="pub_var.DISAPPROVED"></v-checkbox>
                                        <v-checkbox v-model="filter.filterStatus" class="columnChooserCheckBox"
                                            label="Completed" :value="pub_var.COMPLETE"></v-checkbox>
                                    </v-card>
                                </v-menu>
                            </v-col>
                        </v-col>
                    </transition>
                    <!-- Download Excel Data -->
                    <download-excel :data="rows" type="xlsx" :fields="colField"
                        :name="'EquipmentHandling - ' + moment().format('MM-DD-YYYY')">
                        <v-btn text="Export" prepend-icon="mdi-download" variant="text" color="primary"
                            class='text-none'></v-btn>
                    </download-excel>
                </v-col>
            </v-row>
            <v-row>

            </v-row>
        </template>
        <template #default="{ searchText }">
            <v-card class="mx-auto">
                <vue3-datatable ref="datatable" class="tableLimitText" :rows="rows" :columns="cols" :loading="loading"
                    :search="params.search" :totalRows="total_rows" :pageSize="params.pageSize" :isServerMode="true" @change="changeServer"
                    @rowSelect="rowSelect" :sortColumn="params.sort_column" :sortDirection="params.sort_direction"
                    :sortable="true" skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover"
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
                    <template #equipments="data">
                            <v-tooltip activator="parent" color="primary">
                                <p class="mb-1 mt-1" v-for="(item) in data.value.equipments">
                                    <v-icon>mdi-arrow-right-thin</v-icon> Serial : {{ item.serial_number }}</p>
                            </v-tooltip>
                    </template>
                    <template #approver_name="data">
                        <span class="text-danger"
                            v-if="parseInt(data.value.main_status) === pub_var.DISAPPROVED">Disapproved</span>
                        <span class="text-success"
                            v-else-if="parseInt(data.value.main_status) === pub_var.COMPLETE">Completed</span>
                        <span v-else>{{ pub_var.getApproverByLevel(data.value.level, 1) }}
                            {{ pub_var.INSTALLATION_TL === data.value.level || pub_var.INSTALLATION_ENGINEER ===
                                data.value.level ? data.value.SBU : '' }}
                        </span>
                    </template>
                    <template #main_status="data">
                            <span>
                                <v-chip label density="compact" :color="pub_var.setJOStatus(data.value.main_status).color">
                                    {{ pub_var.setJOStatus(data.value.main_status).text }}
                                </v-chip>
                            </span>
                    </template>
                    <template #created_at="data">
                        <span>{{ pub_var.formatDateNoTime(data.value.created_at) }}</span>
                    </template>
                    <template #final_installation_date="data">
                        <span>{{ pub_var.formatDateNoTime(data.value.final_installation_date) }}</span>
                    </template>
                </vue3-datatable>
            </v-card>
        </template>
    </LayoutWithActions>
</template>
<script setup>
import { onMounted, ref, reactive, provide, watch } from 'vue';
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'
import { requestorID } from '@/global/global';
import moment from 'moment';
import debounce from 'lodash/debounce'
import { useRouter } from 'vue-router';

/**Insitution Data */
import { useInstitutionData } from '@/helpers/getInstitution';
const { institutionData } = useInstitutionData()

const router = useRouter()

/** Permissions */
import { permit } from '@/castl/permitted';
const { can } = permit()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

//** Datepicker */
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'




/** Declaration of User Data */
const user = user_data();
const role = getRole()
const currentUserRoleID = role.currentUserRole
const btnDisable = ref(true)
const datatable = ref(null)
const selectedId = ref(null)
const status = ref(null)


const apiRequest = apiRequestAxios()


/** Filtering */
const showFilter = ref(false)
const filterByStatusDropDown = ref(null)
// const internalStatus = ref(pub_var.internalStat)

const filter = ref({
    filterStatus: [],
    filterInstitution: [],
    delegation_date: '',
})
watch(filter, (newFilter) => {
    localStorage.setItem('EHFilter', JSON.stringify(newFilter));
    getRequest()
}, { deep: true })


/** Data Tables Componenets */
const getSearchText = (data) => {
    params.search = data
}
const loading = ref(true);
const total_rows = ref('');

const params = reactive({ current_page: 1, pageSize: 10, sort_column: 'created_at', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Report Number', isUnique: true, type: 'number', hide: false },
        { field: 'name', title: 'Institution', hide: false },
        { field: 'address', title: 'Address', hide: false },
        { field: 'user_name', title: 'Requested by' },
        { field: 'request_type', title: 'Request Category', hide: false },
        { field: 'request_name', title: 'Type of Request', hide: false },
        { field: 'equipments', title: 'Equipment' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date', type: 'date', hide: false },
        { field: 'final_installation_date', title: 'Final Installation Date', hide: false },
        { field: 'created_at', title: 'Date Requested', type: 'date', hide: false },
        { field: 'approver_name', title: 'Pending Approval', hide: false }, //minWidth : '300px' 
        { field: 'main_status', title: 'Status', type: 'number', hide: false }, //  minWidth : '200px',
    ]) || [];

const colField = cols.value.reduce((acc, v) => {
    if (v.title && v.field) {
        acc[v.title] = v.field
    }
    return acc
}, {})

provide('column', cols)



/**
 * @ Row Select or Double Click Table Event
 */
const doubleClickViewData = (row) => {
    // if(

    //     user.user.id === row.requested_by &&

    // ){

    // }
    router.push({ name: 'WorkOrderApprover', params: { id: row.id } })
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
}
const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}




/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref(null)
const routeView = ref('WorkOrderApprover')
const createRequest = ref(null)
const service_id = ref(null)

if (can('create', 'Equipment Handling') && role.currentUserRole === requestorID) createRequest.value = 'WorkOrder'


const actions = {
    selectedId: selectedId,
    btnDisable: btnDisable,
    routeView: routeView,
    createRequest: createRequest.value,
}

provide('data', actions)

const getRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-equipment-handling', {
            params: {
                ...params,
                user_id: user.user.id,
                current_role: currentUserRoleID
            },
        });
        const result = response.data?.equipment_handling

        rows.value = result.data
        total_rows.value = result.total
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



// ChangeServer for Servre Mode
const debounceSearch = debounce(getRequest, 300)
const changeServer = (data) => {
    params.current_page = data.current_page
    params.pageSize = data.pageSize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if (data.change_type === 'search') {
        debounceSearch()
    } else getRequest()
}


provide('refresh', getRequest)



/** onMounted */
onMounted(() => {
    const EHFilter = localStorage.getItem('EHFilter');
    if (EHFilter) {
        filter.value = JSON.parse(EHFilter);
    }
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