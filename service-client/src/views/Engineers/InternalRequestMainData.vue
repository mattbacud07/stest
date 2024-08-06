<template>
    <LayoutWithActions>
        <template #filter>
            <v-row>
                <v-col>
                    <v-card style="border: dashed thin #191970;" flat class="p-3">
                        <v-row class="align-items-center pt-3 pb-3 pl-2">
                            <span class="mr-3">Filter by:</span>
                            <!-- Filter by Status  -->
                            <v-menu v-model="filterByStatusDropDown" :close-on-content-click="false" location="bottom">
                                <template v-slot:activator="{ props }">
                                    <v-btn color="primary" variant="tonal" v-bind="props" class="text-none">
                                        <v-badge color="primary" :content="filter.filterStatus.length"
                                            v-if="filter.filterStatus.length > 0" class="mr-3 ml-2"></v-badge>
                                        Status <v-icon>mdi-chevron-down</v-icon> </v-btn>
                                </template>

                                <v-card width="auto" class="pr-3">
                                    <v-checkbox v-model="filter.filterStatus" class="columnChooserCheckBox"
                                        :label="pub_var.internalStatus(stat).text" :value="stat"
                                        v-for="(stat, index) in internalStatus" :key="stat"></v-checkbox>
                                </v-card>
                                <!-- @change="filterByStatusAction" @click:prependInner="filterByStatusAction" -->
                            </v-menu>

                            <!-- Filter by Institution  -->
                            <v-combobox :loading="loadingInstitutionData" color="primary" class="ml-3 mt-1"
                                v-model="filter.filterInstitution" label="Institution" density="compact"
                                :items="institutionData" chips closable-chips single-line style="max-width: 30%;"
                                itemValue="iId" itemTitle="iName" multiple>
                            </v-combobox>

                            <v-text-field density="compact" variant="outlined" style="max-width: 20%;" class="ml-2 mt-1"
                                clearable type="date" label="Delegation Date"></v-text-field>
                        </v-row>
                    </v-card>
                </v-col>
            </v-row>
        </template>
        <template #default="{ searchText }">
            <v-card class="mx-auto mt-10">
                <vue3-datatable ref="datatable" :rows="rows" :sort="true" :columns="cols" :loading="loading"
                    :search="searchText" :sortable="true" :sortColumn="params.sort_column"
                    :sortDirection="params.sort_direction" skin="bh-table-hover bh-table-compact bh-table-bordered"
                    :hasCheckbox="true" :selectRowOnClick="true" @rowSelect="rowSelect" class="tableLimitText">

                    <template #equipment_handling.id="data">
                        <span>{{ pub_var.setReportNumber(data.value.equipment_handling.id, data.value.created_at)
                            }}</span>
                        <!-- <span>JOF-{{ String(data.value.id).padStart(3,0) }}-{{moment(data.value.created_at).format('YYYY')}}</span>  -->
                    </template>
                    <template #equipment_handling.created_at="data">
                        <span>{{ pub_var.formatDate(data.value.created_at) }}</span>
                    </template>
                    <template #equipment_handling.proposed_delivery_date="data">
                        <span>{{ pub_var.formatDate(data.value.equipment_handling.proposed_delivery_date) }}</span>
                    </template>
                    <template #delegation_date="data">
                        <span>{{ pub_var.formatDate(data.value.delegation_date) }}</span>
                    </template>
                    <template #date_started="data">
                        <span>{{ pub_var.formatDate(data.value.date_started) }}</span>
                    </template>
                    <template #accomplished_date="data">
                        <span>{{ pub_var.formatDate(data.value.accomplished_date) }}</span>
                    </template>
                    <template #packed_endorse_to_warehouse="data">
                        <span v-if="data.value.packed_endorse_to_warehouse"><v-icon
                                color="primary">mdi-check</v-icon></span>
                    </template>
                    <template #endorsement_date="data">
                        <span>{{ pub_var.formatDate(data.value.endorsement_date) }}</span>
                    </template>
                    <template #status="data">
                        <span class="text-dark"
                            :style="{ fontWeight: '700', color: pub_var.internalStatus(data.value.status).color }">{{
                                pub_var.internalStatus(data.value.status).text }}</span>
                        <br><span class="small"
                            v-if="data.value.status === pub_var.internalStat.ConfirmedByWIM">Internal Process
                            Completed</span>
                    </template>
                </vue3-datatable>
            </v-card>
        </template>
    </LayoutWithActions>
</template>
<script setup>
import { onMounted, ref, reactive, computed, provide, watch } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import * as pub_var from '@/global/global'
import moment from 'moment';
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const filterByStatusDropDown = ref(null)
// const filterStatus = ref([])
// const filterInstitution = ref([])
// const filterInstitutionSelected = ref([])
const internalStatus = ref(pub_var.internalStat)

const filter = ref({
    filterStatus: [],
    filterInstitution: [],
})

const btnDisabled = ref(true)
const datatable = ref(null)
const loadingInstitutionData = ref(false)
const mapSelected = ref([])
const serviceId = ref(null)
const internalId = ref(null)
const selectJustOneMessage = ref('')
const status = ref(null)
const routeView = ref('InternalServicingProcess')

/** Sent to Topbar */
const actions = {
    selectedId: serviceId,
    service_id: internalId,
    status: status,
    btnDisable: btnDisabled,
    routeView: routeView,
}
provide('data', actions)

/** Check - Selecting Users */
const rowSelect = (data) => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length === 1) {
        btnDisabled.value = false
        selectJustOneMessage.value = ''
    } else {
        btnDisabled.value = true
        selectJustOneMessage.value = "Select one record"
    }
    serviceId.value = selectedRows.map((data) => { return data.equipment_handling.id })[0] //SelectedId to pass in other routes for work order approval
    internalId.value = selectedRows.map((data) => { return data.id })[0]
}



/** Filter by Status */
// const filterByStatusAction = () => {
//     getInternalRequest()
// }
// watch(filterInstitution, (newVal) => {
//     filterInstitutionSelected.value = Object.values(newVal).map((data) => data.iId)
//     getInternalRequest()
// })

watch(filter, (newFilter) => {
    getInternalRequest()
}, { deep: true })

/** Declaration of User Data Store*/
const user = user_data();
const apiRequest = user.apiRequest()


/** Enable Filter */
const enableFilter = ref(false)
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'id', sort_direction: 'asc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true, width: 'auto' },
        // { field: 'first_name', title: 'Requested by' },
        { field: 'equipment_handling.id', title: 'Report Number', filter: true, width: 'auto', hide: false, },
        { field: 'equipment_handling.institution_name', title: 'Institution', filter: true, width: 'auto', hide: false, },
        { field: 'equipment_handling.proposed_delivery_date', title: 'Proposed Delivery Date', filter: true, width: 'auto', hide: false, },
        { field: 'equipment_handling.created_at', title: 'Date Requested', filter: true, width: 'auto', hide: true, },
        { field: 'internal_external_name.name', title: 'Type of Activity', filter: true, width: 'auto', hide: false, },
        { field: 'delegation_date', title: 'Delegation Date', width: 'auto', hide: false, },
        { field: 'date_started', title: 'Date Started', width: 'auto', hide: false, },
        { field: 'accomplished_date', title: 'Date Accomplished', width: 'auto', hide: false, },
        { field: 'packed_endorse_to_warehouse', title: 'Packed & Endorsed to Warehouse', minWidth: '200px', hide: false, },
        { field: 'endorsement_date', title: 'Endorsement Date', width: 'auto', hide: false, },
        { field: 'accepted_by_warehouse', title: 'Confirmed by Warehouse', minWidth: '200px', hide: false, },
        { field: 'status', title: 'Status', width: 'auto', hide: false, },
    ]) || [];


provide('column', cols)

/** Get Internal Request Servicing */
const getInternalRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('getInternalRequest', {
            params: {
                user_id: user.user.id, category: 'specificUser', ...filter.value
            }
        });
        const data = response.data.request

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

    getInternalRequest();
};

/** Provide for Refresh */
provide('refresh', getInternalRequest)


/** Get All Institutions */
const institutionData = ref([])
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
onMounted(() => {
    getInternalRequest();
    getInstitution()
});
</script>


<!-- <style scoped>
a {
    color: aliceblue;
}
</style> -->
<style scoped>
.btnsubmitText {
    color: #fff;
}

.columnChooserCheckBox {
    height: 40px !important;
}
</style>
