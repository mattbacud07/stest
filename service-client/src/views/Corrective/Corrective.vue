<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #filter>
            <v-row>
                <v-col cols="12" class="d-flex justify-content-between">
                    <v-menu v-model="filterMenu" :close-on-content-click="false" location="bottom"
                        transition="slide-x-reverse-transition">
                        <template v-slot:activator="{ props }">
                            <v-btn color="primary" prepend-icon="mdi-filter-outline" variant="tonal"
                                text="Advanced filtering" class="text-none mb-4" v-bind="props">
                                <template v-slot:append>
                                    <v-badge v-if="nonEmptyCountFilter > 0" color="warning"
                                        :content="nonEmptyCountFilter" inline></v-badge>
                                </template>
                            </v-btn>
                        </template>

                        <v-card min-width="350" :width="width < 500 ? '350' : '450'" max-width="450" min-height="500"
                            style="overflow-x: hidden!important;">
                            <v-row>
                                <v-col cols="12">
                                    <v-col cols="12">
                                        <!-- Filter by Status  -->
                                        <v-menu v-model="filterByStatusDropDown" :close-on-content-click="false"
                                             height="auto" location="bottom" min-width="auto">
                                            <template v-slot:activator="{ props }">
                                                <v-btn color="primary" variant="tonal" v-bind="props" class="text-none w-50 text-left">
                                                    <v-badge color="primary" :content="filter.filterStatus.length"
                                                        v-if="filter.filterStatus.length > 0"
                                                        class="mr-3 ml-2"></v-badge>
                                                    Status <v-icon>mdi-chevron-down</v-icon> </v-btn>
                                            </template>

                                            <v-card width="auto" class="pr-3">
                                                <v-checkbox v-model="filter.filterStatus" class="columnChooserCheckBox"
                                                    :label="m_var.status_pm(stat).text" :value="stat"
                                                    v-for="(stat, index) in m_var.pm_status_array"
                                                    :key="stat"></v-checkbox>
                                            </v-card>
                                        </v-menu>
                                        <span class="ml-2"></span>

                                         <!-- Filter by Tag as -->
                                         <v-menu v-model="filterByTag" :close-on-content-click="false" min-width="400"
                                            width="400" height="auto" location="bottom" class="ml-3">
                                            <template v-slot:activator="{ props }">
                                                <v-btn color="primary" variant="tonal" v-bind="props" class="text-none">
                                                    <v-badge color="primary" :content="filter.filterTag.length"
                                                        v-if="filter.filterTag.length > 0" class="mr-3"></v-badge>
                                                    Tag as<v-icon>mdi-chevron-down</v-icon> </v-btn>
                                            </template>

                                            <v-card width="auto" class="pr-3">
                                                <v-checkbox v-model="filter.filterTag" class="columnChooserCheckBox"
                                                    :label="tag" :value="tag" v-for="(tag, key) in m_var.tag_array"
                                                    :key="key"></v-checkbox>
                                            </v-card>
                                        </v-menu>

                                        <!-- Filter by Status  After Service -->
                                        <v-menu v-model="filterByStatusAfterService" :close-on-content-click="false"
                                            min-width="400" width="400" height="auto" location="bottom" class="ml-3">
                                            <template v-slot:activator="{ props }">
                                                <v-btn color="primary" variant="tonal" v-bind="props" class="text-none w-100 mt-5">
                                                    <v-badge color="primary"
                                                        :content="filter.filterStatusAfterService.length"
                                                        v-if="filter.filterStatusAfterService.length > 0"
                                                        class="mr-3"></v-badge>
                                                    Status After Service <v-icon>mdi-chevron-down</v-icon> </v-btn>
                                            </template>

                                            <v-card width="auto" class="pr-3">
                                                <v-checkbox v-model="filter.filterStatusAfterService"
                                                    class="columnChooserCheckBox" :label="stat" :value="stat"
                                                    v-for="(stat, key) in m_var.StatusAfterService"
                                                    :key="key"></v-checkbox>
                                            </v-card>
                                        </v-menu>
                                        <span class="ml-2"></span>

                                       

                                    </v-col>

                                    <v-col cols="12">
                                        <!-- Filter by Institution  -->
                                        <v-combobox :loading="loadingInstitutionData" color="primary"
                                            v-model="filter.filterInstitution" label="Institution" density="compact"
                                            :items="institutionData" chips closable-chips single-line itemValue="iId"
                                            variant="outlined" itemTitle="iName" multiple>
                                        </v-combobox>
                                    </v-col>

                                    <v-col cols="12">
                                        <VueDatePicker v-model="filter.filterSchedule_at" auto-apply range
                                            model-type="yyyy-MM-dd" :enable-time-picker="false" :month-change-on-scroll="false"
                                            placeholder="Schedule at" />
                                    </v-col>


                                    <v-col cols="12">
                                        <div style="position: absolute;bottom: 0!important;left:0;width: 100%;"
                                            class="d-flex justify-content-center">
                                            <v-spacer></v-spacer>

                                            <v-btn variant="tonal" @click="filterMenu = false"
                                                class="text-none rounded-0" style="width: 50%;">
                                                Close
                                            </v-btn>
                                            <v-btn color="primary" variant="flat" prepend-icon="mdi-close"
                                                @click="clearFilter" class="text-none rounded-0" style="width: 50%;">
                                                Clear
                                            </v-btn>
                                        </div>
                                    </v-col>

                                    <v-col cols="12">

                                    </v-col>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-menu>
                    <!-- Download Excel Data -->
                     <v-spacer></v-spacer>
                    <download-excel :data="rows" type="xlsx" :fields="colField"
                        :name="'PreventiveMaintenance - ' + moment().format('MM-DD-YYYY')">
                        <v-btn text="Export" prepend-icon="mdi-download" variant="tonal" color="primary"
                            class='text-none ml-2'></v-btn>
                    </download-excel>
                </v-col>
            </v-row>

        </template>
        <template #default="{ searchText }">
            <!-- <v-card class="mx-auto p-4"> -->
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" @change="changeServer"
                @rowSelect="rowSelect" :columnFilter="false" :sortColumn="params.sort_column"
                :sortDirection="params.sort_direction" :sortable="true"
                style="border: 1px solid #99999926;border-radius: 3px;"
                skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" :hasCheckbox="true"
                :selectRowOnClick="true" @rowDBClick="doubleClickViewPM" :rowClass="getRowClass">
                <template #id="data">{{ 'PM-' + data.value.id }}</template>
                <template #scheduled_at="data">
                    <div>
                        <!-- <v-dialog v-model="viewScheduledDialog[data.value.id]" max-width="400" persistent>
                                <template v-slot:activator="{ props: activatorProps }">
                                    <a href="#" v-bind="activatorProps"><v-icon v-if="data.value.scheduled_at !== null"
                                            color="primary">mdi-calendar-range</v-icon>
                                        <b>{{ pub_var.formatDateNoTime(data.value.scheduled_at) }}</b></a>
                                </template>
<v-card>
    <p class="ml-3 mt-3 mb-0"><b>Scheduled Dates</b></p><v-divider></v-divider>
    <v-list lines="one">
        <v-list-item
            v-for="list in data.value.list_scheduled.split(',').map(dateString => moment(dateString.trim()).format('MMMM DD, YYYY'))">
            <template v-slot:prepend>
                                                <v-icon>mdi-calendar-outline</v-icon>
                                            </template>
            <v-list-item-title color="primary">{{ list }}</v-list-item-title>
        </v-list-item>

    </v-list>
    <span class="mt-5 ml-4">End of the Year : <b>{{
            moment().endOf('year').format('YYYY-MM-DD') }}</b> </span>
    <template v-slot:actions>
                                        <v-row justify="end" class="mb-3">
                                            <v-divider></v-divider>
                                            <v-btn elevation="2" @click="viewScheduledDialog[data.value.id] = false"
                                                background-color="red" size="small" color="#191970"
                                                class="text-none mr-5"><v-icon>mdi-close</v-icon>
                                                Close</v-btn>
                                        </v-row>
                                    </template>
</v-card>
</v-dialog> -->
                        {{ moment(data.value.scheduled_at).format('MMMM DD, YYYY') }}
                    </div>
                </template>
                <template #service_id="data">
                    <span color="primary">{{ pub_var.setReportNumber(data.value.service_id) }}</span>
                </template>
                <template #status="data">
                    <span :style="{ color: m_var.status_pm(data.value.status).color }"><b>{{
                        m_var.status_pm(data.value.status).text }}</b></span><br />
                    <span class="small" v-if="data.value.status === 'Not Set'">Contact the admin to configure the
                        frequency</span>
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

const role = getRole()
const currentUserRole = role.currentUserRole

const datatable = ref(null)
const { width } = useDisplay()

/** Permissions */
import { permit } from '@/castl/permitted';
const {can} = permit()


/**Advanced Filtering of Data */
const filterMenu = ref(false)
const filterByStatusDropDown = ref(false)
const filterByStatusAfterService = ref(false)
const filterByTag = ref(false)
const downloadExcelMenu = ref(false)


const filterChip = ref({
    filterOptions: [], //'assign-next-month' ->default
})
watch(filterChip, (newFilter) => {
    localStorage.setItem('filterChip', JSON.stringify(newFilter));
    getPM()
}, { deep: true })

const filter = ref({
    filterStatus: [],
    filterInstitution: [],
    filterSchedule_at: '',
    filterStatusAfterService: [],
    filterTag: [],
})
watch(filter, (newFilter) => {
    localStorage.setItem('PMFilterState', JSON.stringify(newFilter));
    getPM()
}, { deep: true })
const disableOptions = ref(false)
const filterStatusClick = (option) => {
    const value = filterChip.value.filterOptions
    if (!value.includes(option)) {
        value.push(option)
        if (value.includes('assign-next-month')) {
            disableOptions.value = true
            value.splice(0, value.length)
            value.push('assign-next-month')
        }
    } else {
        value.includes('assign-next-month') ? disableOptions.value = false : ''
        const index = value.indexOf(option)
        index > -1 ? value.splice(index, 1) : '';
    }
}

// Computed property to count non-empty fields
const nonEmptyCountFilter = computed(() => {
    return Object.values(filter.value).filter(value => {
        // Check if the value is not empty
        return (Array.isArray(value) && value.length > 0) || (typeof value === 'string' && value.trim() !== '');
    }).length;
});

// Method to clear the filter
const clearFilter = () => {
    filter.value = {
        filterOptions: [],
        filterStatus: [],
        filterInstitution: [],
        filterSchedule_at: '',
        filterStatusAfterService: [],
        filterTag: [],
    };
    localStorage.removeItem('PMFilterState');
    localStorage.removeItem('filterChip');
}


/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref('')
const routeView = ref('PMView')
const service_id = ref(null)
const btnDisable = ref(true)
const selectedId = ref(null)
const createRequest = ref(null)
if(can('create','Preventive Maintenance')) createRequest.value='CreatePM'

const actions = {
    selectedId: selectedId,
    btnDisable: btnDisable,
    routeView: routeView,
    work_type: 'CM',
    createRequest : createRequest.value
}

provide('data', actions)

/** User_data Store */
const user = user_data()
const apiRequest = apiRequestAxios()
const router = useRouter()

const form = ref(false)


/** Declaration */
const dialogCreatePM = ref(false)
const viewSerialDialog = ref([])
const viewScheduledDialog = ref([])
const btnLoading = ref(false)
const serial_number = ref('')
const messageDetails = ref({})
const serialNumbers = ref([])
const list_scheduled = ref([])

const rules = ref({
    serial_number: [
        v => !!v || 'Required field'
    ],
})


/**
 * @ Row Select Table Event
 */
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows()
    if (row.length === 1) btnDisable.value = false
    else btnDisable.value = true

    selectedId.value = row.map(v => v.id)[0]
}

const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}
/**
 * Double Click View PM
 */
const doubleClickViewPM = (row) => {
    router.push({ name: 'PMView', params: { id: row.id, work_type: 'CM' } })
}




/** Get specific PM  */
const getSearchText = (data) => {
    params.search = data
}
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'scheduled_at', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: false },
        // { field: 'service_id', title: 'Service No', hide: true, },
        { field: 'item_id', title: 'Item No', hide: true },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Item Description', hide: true, },
        { field: 'serial', title: 'Serial No.' },
        { field: 'institution_name', title: 'Institution' },
        { field: 'address', title: 'Address', hide: true, },
        // { field: 'date_installed', title: 'Date Installed', hide: true, },
        // { field: 'scheduled_at', title: 'Scheduled at' },
        // { field: 'schedule', title: 'Frequency' },
        { field: 'ssu', title: 'SSU' },
        { field: 'username', title: 'Delegated to' },
        { field: 'delegation_date', title: 'Delegation Date' },
        { field: 'date_accepted', title: 'Date Accepted' },
        { field: 'departed_date', title: 'Date Out' },
        { field: 'travel_duration', title: 'Travel Time' },
        { field: 'start_date', title: 'Time In' },
        { field: 'end_date', title: 'Time Out' },
        { field: 'status_after_service', title: 'Status After Service' },
        { field: 'status', title: 'Status' },
        { field: 'tag', title: 'Tag' },
    ]) || [];


const colField = cols.value.reduce((acc, v) => {
    if (v.title && v.field) {
        acc[v.title] = v.field
    }
    return acc
}, {})
// console.log(colField)

// const searchColumnMap = cols.value.map(v => v.field);
// const searchColumn = searchColumnMap.filter(v => v !== 'id')

provide('column', cols)

const getPM = async () => {
    if (currentUserRole === pub_var.TLRoleID) category.value = pub_var.TLRoleID
    if (currentUserRole === pub_var.engineerRoleID) category.value = pub_var.engineerRoleID
    try {
        loading.value = true;
        const response = await apiRequest.get('get-preventive-maintenance', {
            params: { ...params, category: category.value, ...filter.value, ...filterChip.value, work_type: 'CM' }
        });
        if (response.data && response.data.pm_data) {
            const result = response.data.pm_data
            rows.value = result.data
            total_rows.value = result.total
            // list_scheduled.value = data.map((data) => { return data.list_scheduled.split(',').map(dateString => moment(dateString.trim()).format('MMMM DD, YYYY')) })
            viewSerialDialog.value = result.data.map((data) => { return data.item_id })
            viewScheduledDialog.value = result.data.map((data) => { return data.item_id })
            // console.log(list_scheduled.value)
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};
provide('refresh', getPM)


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
const debounceSearch = debounce(getPM, 300)
const changeServer = (data) => {
    params.current_page = data.current_page
    params.pagesize = data.pagesize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if (data.change_type === 'search') {
        debounceSearch()
    } else getPM()
}



onMounted(() => {
    const pmSavedFilter = localStorage.getItem('PMFilterState');
    const filterChipItem = localStorage.getItem('filterChip');
    if (pmSavedFilter) {
        filter.value = JSON.parse(pmSavedFilter);
    }
    if (filterChipItem) {
        filterChip.value = JSON.parse(filterChipItem);
    }

    getPM()
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
.dp--menu-wrapper{
  position: inherit!important;
}
</style>