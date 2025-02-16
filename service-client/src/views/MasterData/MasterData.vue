<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #default>
            <v-row>
                <v-spacer></v-spacer>
                <v-col cols="4" class="d-flex justify-content-end">
                    <!-- Download Excel Data -->
                    <download-excel :data="rows" type="xlsx" :fields="colField"
                        :name="'ServiceMasterData - ' + moment().format('MM-DD-YYYY')">
                        <v-btn text="Export" prepend-icon="mdi-download" variant="tonal" color="primary"
                            class='text-none mb-5'></v-btn>
                    </download-excel>
                </v-col>
            </v-row>
            <!-- <v-card class="mx-auto p-4"> -->
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" @change="changeServer"
                @rowSelect="rowSelect" :columnFilter="false" :sortColumn="params.sort_column"
                :sortDirection="params.sort_direction" :sortable="true"
                style="border: 1px solid #99999926;border-radius: 3px;"
                skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" :hasCheckbox="true"
                :selectRowOnClick="true" @rowDBClick="doubleClickViewPM" :rowClass="getRowClass">
                <template #admission_date="data">
                    <span>{{  pub_var.formatDateNoTime(data.value.admission_date) }}</span>
                </template>
                <template #date_installed="data">
                    <span>{{  pub_var.formatDateNoTime(data.value.date_installed) }}</span>
                </template>
                <template #contract_due_date="data">
                    <span>{{  pub_var.formatDateNoTime(data.value.contract_due_date) }}</span>
                </template>
            </vue3-datatable>
            <!-- </v-card> -->
        </template>
    </LayoutWithActions>
</template>

<script setup>
import { onMounted, ref, reactive, provide, watch, computed } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { useRouter } from 'vue-router';
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance.js'
import moment from 'moment';
import debounce from 'lodash/debounce'
import { useDisplay } from 'vuetify';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

//** Datepicker */
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue'


/** Permissions */
import { permit } from '@/castl/permitted';
import { MD } from '@/global/modules';
const { can } = permit()


/** Toast PLugin */
import { useToast } from 'vue-toast-notification';
const toast = useToast()

/** User_data Store */
const user = user_data()
const router = useRouter()

const apiRequest = apiRequestAxios()

const role = getRole()
const currentUserRole = role.currentUserRole

const { width } = useDisplay()


/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref('')
const routeView = ref('ViewMasterData')
const createRequest = ref(null)
const editRequest = ref(null)
const deleteRequest = ref(null)
const service_id = ref(null)
const btnDisable = ref(true)
const selectedId = ref(null)

if (can('create', MD)) createRequest.value = 'CreateMasterData'
if(can('edit', MD)) editRequest.value = 'EditMasterData'

/** Delete Data from Master Data of Service */
const deleteData = async() => {
    try {
        const response = await apiRequest.delete('delete_master_data', {
            params:{
                id : selectedId.value
            }
        })
        if(response.data?.success){
            toast.success('Deleted successfully')
            getMasterData()
            btnDisable.value = true
        }
    } catch (error) {
       console.log(error) 
    }
}


const actions = {
    selectedId: selectedId,
    btnDisable: btnDisable,
    routeView: routeView,
    createRequest: createRequest.value,
    editRequest: editRequest.value,
    deleteFunction: can('delete', MD) ? deleteData : null
}

provide('data', actions)



/**
 * @ Row Select Table Event
 */
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows()
    if (row.length === 1) btnDisable.value = false
    else btnDisable.value = true

    selectedId.value = row.map(v => v.id)[0]
    // multiSelected.value = row.map(v => v.id)
}
const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}
/**
 * Double Click View PM
 */
const doubleClickViewPM = (row) => {
    router.push({ name: 'ViewMasterData', params: { id: row.id  } })
}




/** Get specific PM  */
const getSearchText = (data) => {
    params.search = data
}
const datatable = ref(null)
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'id', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: false },
        { field: 'name', title: 'Institution', hide: false },
        { field: 'master_data_id', title: 'Item No', hide: false },
        { field: 'item_code', title: 'Item Code' },
        { field: 'serial', title: 'Serial No.' },
        { field: 'equipment', title: 'Equipment' },
        { field: 'description', title: 'Description' },
        { field: 'sbu', title: 'SBU' },
        { field: 'dealer_name', title: 'Dealer Name' },
        { field: 'area', title: 'Area Categorization' },
        { field: 'operation_time', title: 'Operation Time' },
        { field: 'software_version', title: 'Software Version' },
        { field: 'admission_date', title: 'Admission Date' },
        { field: 'date_installed', title: 'Date Installed' },
        { field: 'contract_due_date', title: 'Contract Due Date' },
        { field: 'region', title: 'Region' },
        { field: 'frequency', title: 'Frequency' },
        { field: 'analyzer_type', title: 'Analyzer Type' },
        { field: 'class', title: 'Class' },
        { field: 'initially_installed', title: 'Initially Installed' },
        { field: 'reason_equipment_status', title: 'Reason for Equipment Status' },
        { field: 'email', title: 'Email' },
        { field: 'status', title: 'Status' },
        { field: 'added_by', title: 'Added by' },
    ]) || [];


const colField = cols.value.reduce((acc, v) => {
    if (v.title && v.field) {
        acc[v.title] = v.field
    }
    return acc
}, {})
// console.log(colField)

provide('column', cols)

const getMasterData = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_master_data', {
            params: { ...params, }
        });
        if (response.data && response.data.md_data) {
            const result = response.data.md_data
            rows.value = result.data
            total_rows.value = result.total
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



provide('refresh', getMasterData)


/** Get All Institutions */
const institutionData = ref([])
const loadingInstitutionData = ref(false)
const getInstitution = async () => {
    try {
        loadingInstitutionData.value = true;
        const response = await apiRequest.get('get_institution');
        if (response.data && response.data.institutions) {
            const data = response.data.institutions

            institutionData.value = data.map((institution) => {
                return {
                    // institution.name
                    iName: institution.name,
                    iId: institution.id,
                }
            })
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingInstitutionData.value = false;
    }
};







// ChangeServer for Servre Mode
const debounceSearch = debounce(getMasterData, 300)
const changeServer = (data) => {
    params.current_page = data.current_page
    params.pagesize = data.pagesize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if (data.change_type === 'search') {
        debounceSearch()
    } else getMasterData()
}



onMounted(() => {
    getMasterData()
    getInstitution()
})

</script>



<style scoped>
* {
    user-select: none;
}

.v-chip--label {
    font-size: 12px !important;
}

/* Calendar Vue Dtewpicker */
.dp--menu-wrapper {
    position: static !important;
}
</style>