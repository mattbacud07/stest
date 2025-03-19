<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #filter>
            <v-row>
                <v-col cols="12" md="6" sm="6" class="d-flex justify-space-between align-items-center pa-0">
                    <v-badge color="warning" dense :content="filterCount" :offset-y="5">
                        <v-checkbox false-icon="mdi-filter-menu" true-icon="mdi-close" color="primary"
                            v-model="showFilter">
                            <v-tooltip activator="parent" v-if="!showFilter">Expanded Filter</v-tooltip>
                        </v-checkbox>
                    </v-badge>
                    <!-- <v-spacer></v-spacer> -->
                    <transition name="slide-x-transition">
                        <v-col class="d-flex"
                            :style="{ visibility: showFilter ? 'visible' : 'hidden', opacity: showFilter ? '1' : '0' }">
                            <!-- Filter by Institution  -->
                            <v-col cols="12" md="6" sm="6" class="pa-0 mr-1">
                                <v-combobox transition="slide-x-transition" color="primary"
                                    v-model="filter.filterInstitution" label="Institution" density="compact"
                                    :items="institutionData" chips closable-chips single-line itemValue="institution_id"
                                    variant="outlined" itemTitle="key" multiple>
                                </v-combobox>
                            </v-col>
                            <!-- Filter by Status  -->
                            <v-col cols="auto" class="pa-0 mr-1">
                                <v-btn color="primary" variant="plain" class="text-none">
                                    <template v-slot:prepend>
                                        <v-badge color="primary" :content="filter.filterStatus?.length"
                                            inline></v-badge>
                                    </template>
                                    Status <v-icon>mdi-chevron-down</v-icon>
                                    <v-menu transition="slide-x-transition" :close-on-content-click="false"
                                        height="auto" location="bottom" activator="parent">
                                        <v-card width="auto" class="pr-3">
                                            <v-checkbox v-model="filter.filterStatus"
                                                v-for="(status) in m_var.status_cm" class="columnChooserCheckBox"
                                                :label="status.text" :value="status.key"></v-checkbox>

                                        </v-card>
                                    </v-menu>
                                </v-btn>
                            </v-col>
                        </v-col>
                    </transition>
                </v-col>
                <v-col cols="12" md="6" sm="6"
                    class="d-flex d-inline-flex align-center align-content-center justify-end">
                    <v-col cols="auto">
                        <!-- Download Excel Data -->
                        <download-excel :data="rows" type="xlsx" :fields="colField"
                            :name="'PreventiveMaintenance - ' + moment().format('MM-DD-YYYY')">
                            <v-btn text="Export" prepend-icon="mdi-download" density="compact" variant="tonal"
                                color="primary" class='text-none'></v-btn>
                        </download-excel>
                    </v-col>
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
                <template #id="data">{{ 'CM-' + data.value.id }}</template>
                <template #sbu="data">
                    <span><i v-if="data.value.sbu">SBU - </i>{{ data.value.sbu }}</span>
                </template>
                <template #created_at="data">
                    <span>
                        {{ pub_var.formatDate(data.value.created_at) }}
                    </span>
                </template>
                <template #status="data">
                    <span>
                        <v-chip :color="m_var.setPMStatus(data.value.status).color" label size="small">
                            {{ m_var.setPMStatus(data.value.status).text }}
                        </v-chip>
                    </span>
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

import { useInstitutionData } from '@/helpers/getInstitution';
const { institutionData } = useInstitutionData()

/** Permissions */
import { permit } from '@/castl/permitted';
import { nonEmptyCountFilter } from '@/helpers/filters';
import { CM, PM } from '@/global/modules';
const { can } = permit()

/** User_data Store */
const user = user_data()
const router = useRouter()

const apiRequest = apiRequestAxios()

const role = getRole()
const currentUserRole = role.currentUserRole

const datatable = ref(null)
const { width } = useDisplay()


/** Filtering */
const showFilter = ref(false)

const filter = ref({
    filterStatus: [],
    filterInstitution: [],
    assignment: false,
})
const filterCount = ref(0)
watch(filter, (newFilter) => {
    filterCount.value = nonEmptyCountFilter(newFilter)
    localStorage.setItem('CMFilter', JSON.stringify(newFilter));
    params.current_page = 1
    getPM()
}, { deep: true })


/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref('')
const routeView = ref('CMView')
const service_id = ref(null)
const btnDisable = ref(true)
const selectedId = ref(null)
const createRequest = ref(null)
if (can('create', CM)) createRequest.value = 'CreateCM'

/** Delete Data */
const deleteData = async () => {
    try {
        const response = await apiRequest.delete(`delete_cm/${selectedId.value}`)
        if (response.data?.success) {
            toast.success('Deleted successfully')
            getPM()
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
    deleteFunction: can('delete', CM) ? deleteData : null
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
}

const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}
/**
 * Double Click View PM
 */
const doubleClickViewPM = (row) => {
    router.push({ name: 'CMView', params: { id: row.id } })
}




/** Get specific PM  */
const getSearchText = (data) => {
    params.search = data
}
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'created_at', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: false },
        { field: 'service_id', title: 'Service No', hide: true, },
        { field: 'item_id', title: 'Item No', hide: true },
        { field: 'item_code', title: 'Item Code' },
        { field: 'equipment', title: 'Equipment' },
        { field: 'description', title: 'Item Description', hide: true, },
        { field: 'serial', title: 'Serial No.' },
        { field: 'institution_name', title: 'Institution' },
        { field: 'address', title: 'Address', hide: true, },
        { field: 'frequency', title: 'Frequency' },
        { field: 'sbu', title: 'SBU', hide: false },
        { field: 'delegated_by', title: 'Delegated by' },
        { field: 'delegated_to', title: 'Delegated to' },
        { field: 'created_at', title: 'Date Created' },
        { field: 'status', title: 'Status', cellClass: "sticky-last", headerClass: "sticky-last-header", },
    ]) || [];


const colField = cols.value.reduce((acc, v) => {
    if (v.title && v.field) {
        acc[v.title] = v.field
    }
    return acc
}, {})

provide('column', cols)

const getPM = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-corrective-maintenance', {
            params: {
                ...params,
                ...filter.value,
                current_role: currentUserRole,
            }
        });
        if (response?.data?.cm_data) {
            const result = response.data.cm_data
            rows.value = result.data
            total_rows.value = result.total
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};
provide('refresh', getPM)



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
    const PMFilter = localStorage.getItem('CMFilter');
    if (PMFilter) {
        filter.value = JSON.parse(PMFilter);
    }
    getPM()
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
    position: inherit !important;
}
</style>