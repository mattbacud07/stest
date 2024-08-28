<template>
    <LayoutWithActions>
        <template #filter>
                    <v-menu v-model="filterMenu" :close-on-content-click="false" location="bottom"
                        transition="slide-x-reverse-transition">
                        <template v-slot:activator="{ props }">
                            <v-btn color="primary" prepend-icon="mdi-filter-outline" variant="tonal"
                                text="Advanced filtering" class="text-none" v-bind="props"></v-btn>
                        </template>

                        <v-card min-width="350" :width="width < 500 ? '350' : '500'" max-width="500" min-height="500" style="overflow-x: hidden!important;">
                            <v-row>
                                <v-col cols="12">
                                    <v-col cols="12">
                                        <!-- Filter by Status  -->
                                        <v-menu v-model="filterByStatusDropDown" :close-on-content-click="false"
                                            min-width="400" width="400" height="auto" location="bottom">
                                            <template v-slot:activator="{ props }">
                                                <v-btn color="primary" variant="tonal" v-bind="props" class="text-none">
                                                    <v-badge color="primary" :content="filter.filterStatus.length"
                                                        v-if="filter.filterStatus.length > 0"
                                                        class="mr-3 ml-2"></v-badge>
                                                    Status <v-icon>mdi-chevron-down</v-icon> </v-btn>
                                            </template>

                                            <v-card width="auto" class="pr-3">
                                                <v-checkbox v-model="filter.filterStatus" class="columnChooserCheckBox"
                                                    :label="pub_var.internalStatus(stat).text" :value="stat"
                                                    v-for="(stat, index) in internalStatus" :key="stat"></v-checkbox>
                                            </v-card>
                                            <!-- @change="filterByStatusAction" @click:prependInner="filterByStatusAction" -->
                                        </v-menu>
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
                                        <VueDatePicker v-model="filter.delegation_date" auto-apply range model-type="yyyy-MM-dd"
                                            :enable-time-picker="false" placeholder="Delegation Date" />
                                            {{ filter.delegation_date }}
                                    </v-col>


                                    <v-col cols="12">
                                        <div style="position: absolute;bottom: 0!important;left:0;width: 100%;"
                                            class="d-flex justify-content-center">
                                            <v-spacer></v-spacer>

                                            <v-btn variant="tonal" @click="filterMenu = false" class="text-none rounded-0"
                                                style="width: 50%;">
                                                Close
                                            </v-btn>
                                            <v-btn color="primary" variant="flat" prepend-icon="mdi-close"
                                                @click="clearFilter" class="text-none rounded-0" style="width: 50%;">
                                                Clear
                                            </v-btn>
                                        </div>
                                    </v-col>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-menu>
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
                        <span class="small" v-if="data.value.status === pub_var.internalStat.Packed">Warehouse
                            Confirmation Pending</span>

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
import * as pub_var from '@/global/global'
import moment from 'moment';
import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue';
import {useDisplay} from 'vuetify'

const {width} = useDisplay()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'
//** Datepicker */
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

const role = getRole()
const currentUserRole = role.currentUserRole

const filterByStatusDropDown = ref(null)
const internalStatus = ref(pub_var.internalStat)

const filter = ref({
    filterStatus: [],
    filterInstitution: [],
    delegation_date: '',
})

// Method to clear the filter
const clearFilter = () => {
    filter.value = {
        filterStatus: [],
        filterInstitution: [],
        delegation_date: '',
    };
}

const filterMenu = ref(false)
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
const category = ref('')
const getInternalRequest = async () => {
    if (currentUserRole === pub_var.engineerRole) category.value = 'delegated_to'
    if (currentUserRole === pub_var.wimPersonnel) category.value = pub_var.wimPersonnel
    try {
        loading.value = true;
        const response = await apiRequest.get('getInternalRequest', {
            params: {
                user_id: user.user.id, ...filter.value, category: category.value,
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
