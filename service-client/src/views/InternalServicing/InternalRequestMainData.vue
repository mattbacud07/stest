<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #filter>
            <v-row>
                <v-col cols="12" class="d-flex justify-space-between align-items-center pa-0">
                    <v-badge color="grey" dense :content="filterCount" :offset-y="5">
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
                            <v-col cols="3" class="pa-0 mr-1">
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
                                        min-width="400" width="400" height="auto" location="bottom" activator="parent">
                                        <v-card width="auto" class="pr-3">
                                            <v-checkbox v-model="filter.filterStatus"
                                                v-for="(status) in pub_var.internalStatus" class="columnChooserCheckBox"
                                                :label="status.text" :value="status.text"></v-checkbox>

                                        </v-card>
                                    </v-menu>
                                </v-btn>
                            </v-col>
                        </v-col>
                    </transition>
                </v-col>
            </v-row>
        </template>
        <template #default>
            <v-card class="mx-auto mt-2">
                <vue3-datatable ref="datatable" :rows="rows" :sort="true" :columns="cols" :loading="loading"
                    :search="params.search" :isServerMode="true" :pageSize="params.pagesize" :sortable="true"
                    :sortColumn="params.sort_column" :sortDirection="params.sort_direction"
                    skin="bh-table-hover bh-table-compact bh-table-bordered" :hasCheckbox="true"
                    :selectRowOnClick="true" @rowSelect="rowSelect" @rowDBClick="selectRowDBClick"
                    :rowClass="getRowClass" @change="changeServer" :totalRows="total_rows" cellClass="internalCell"
                    class="tableLimitText">
                    <template #service_id="data">
                        <span>{{ pub_var.setReportNumber(data.value.service_id, data.value.created_at) }}</span>
                    </template>
                    <template #equipments_data="data">
                        <v-tooltip activator="parent" color="primary">
                            <p class="mb-1 mt-1"
                                v-for="(item) in data.value.equipment_handling?.equipments?.filter(v => v.category === 'Equipment')">
                                <v-icon>mdi-circle-medium</v-icon> {{ item.master_data?.equipment }}
                                <span v-if="item.master_data?.sbu">[ SBU - {{ item.master_data?.sbu }} ] </span>
                            </p>
                        </v-tooltip>
                    </template>
                    <template #status="data">
                        <span>
                            <v-chip label density="compact" :color="pub_var.getInternalStatus(data.value.status).color">
                                {{ pub_var.getInternalStatus(data.value.status).text }}
                            </v-chip>
                        </span>
                    </template>
                </vue3-datatable>
            </v-card>
        </template>
    </LayoutWithActions>
</template>
<script setup>
import { onMounted, ref, reactive, computed, provide, watch } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'
import { useDisplay } from 'vuetify'
import { useRouter } from 'vue-router';

import debounce from 'lodash/debounce';

import { useInstitutionData } from '@/helpers/getInstitution';
const { institutionData } = useInstitutionData()
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';

const { width } = useDisplay()
const router = useRouter()

/** Declaration of User Data Store*/
const user = user_data();
const apiRequest = apiRequestAxios()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'
import { nonEmptyCountFilter } from '@/helpers/filters';

const role = getRole()
const currentUserRole = role.currentUserRole

const showFilter = ref(false)

const filter = ref({
    filterStatus: [],
    filterInstitution: [],
})

const filterCount = ref(0)
watch(filter, (newFilter) => {
    filterCount.value = nonEmptyCountFilter(newFilter)
    localStorage.setItem('ISFilter', JSON.stringify(newFilter));
    params.current_page = 1
    getInternalRequest()
}, { deep: true })

const btnDisabled = ref(true)
const datatable = ref(null)
const loadingInstitutionData = ref(false)
// const serviceId = ref(null)
const internalId = ref(null)
const status = ref(null)
const routeView = ref('InternalServicingProcess')

/** Sent to Topbar */
const actions = {
    selectedId: internalId,
    // service_id: internalId,
    status: status,
    btnDisable: btnDisabled,
    routeView: routeView,
}
provide('data', actions)

/** Check - Selecting Users */
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows()
    if (row && row.length === 1) btnDisabled.value = false
    else btnDisabled.value = true

    internalId.value = row.map(data => data.id)[0]
}

const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}

const selectRowDBClick = (row) => {
    router.push({ name: 'InternalServicingProcess', params: { id: row.id } }) //  - Change position -> { id: row.service_id, service_id : row.id } , service_id: row.id 
}






/** Data table Settings */
const getSearchText = (data) => {
    params.search = data
}
const loading = ref(true);
const total_rows = ref('');
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'created_at', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true, width: 'auto' },
        { field: 'service_id', title: 'JO' },
        { field: 'requested_by', title: 'Requested by' },
        { field: 'institution', title: 'Institution' },
        { field: 'equipments_data', title: 'Equipmetns' },
        { field: 'assigned_by', title: 'Delegated by' },
        { field: 'assigned_to', title: 'Delegated to' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date' },
        { field: 'status', title: 'Status' },
        // { field: 'created_at', title: 'Date Internal Request'},
    ]) || [];


provide('column', cols)

/** Get Internal Request Servicing */
const getInternalRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('getInternalRequest', {
            params: {
                current_role: currentUserRole,
                ...params,
                ...filter.value,
            }
        });
        const result = response.data?.internal_servicing_data

        rows.value = result.data
        total_rows.value = result.total
    } catch (error) {
        alert(error)
    }

    loading.value = false;
};
// ChangeServer for Servre Mode
const debounceSearch = debounce(getInternalRequest, 300)
const changeServer = (data) => {
    params.current_page = data.current_page
    params.pagesize = data.pagesize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if (data.change_type === 'search') {
        debounceSearch()
    } else getInternalRequest()
}

/** Provide for Refresh */
provide('refresh', getInternalRequest)


onMounted(() => {
    const ISFilter = localStorage.getItem('ISFilter');
    if (ISFilter) {
        filter.value = JSON.parse(ISFilter);
    }
    getInternalRequest()
});
</script>

<style scoped>
.btnsubmitText {
    color: #fff;
}
</style>
