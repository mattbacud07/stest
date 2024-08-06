<template>
    <div>
        <v-row justify="space-between" class="topActions">
            <div>
                <nav class="mt-5 ml-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="#">Internal Servicing</a></li>
                        <!-- <li class="breadcrumb-item active" aria-current="page">Work Order</li> -->
                    </ol>
                </nav>
            </div>
            <div class="mt-3 mr-4">

                <v-btn type="submit" color="primary" :disabled="btnDisabled" class="text-none btnSubmit"><router-link
                        :to="{ name: 'InternalServicingProcess', params: { id: selectedId ?? 0, requestedId: requestedId ?? 0 } }">
                        <v-icon class="mr-2 text-white">mdi-file-eye-outline</v-icon>
                        <span class="btnsubmitText">View</span>
                    </router-link></v-btn>
            </div>
        </v-row>
        <v-card class="mx-auto p-4 mt-10">
            <v-row>
                <!-- <v-col cols="9" style="text-align: left;">
                    <h6 class="text-primary"><b>For Approval</b></h6>
                <span class="text-red" style="font-size: 12px;"><i>{{ selectJustOneMessage }}</i></span>
                </v-col> -->
                <v-col cols="3">
                    <v-text-field v-model="params.search" clearable density="compact" label="Search all fields"
                        variant="outlined"></v-text-field>
                </v-col>
            </v-row>
            <vue3-datatable ref="datatable" :rows="rows" :sort="true" :columns="cols" :loading="loading"
                :search="params.search" :sortable="true" :sortColumn="params.sort_column"
                :sortDirection="params.sort_direction" skin="bh-table-hover bh-table-compact" :hasCheckbox="true"
                :selectRowOnClick="true" @rowSelect="rowSelect" class="tableLimitText">

                <template #equipment_handling.id="data">
                    <span>{{ pub_var.setReportNumber(data.value.equipment_handling.id, data.value.created_at) }}</span>
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
                <template #status="data">
                    <span class="text-dark"
                        :style="{ fontWeight: '700', color: pub_var.setInternalStatus(data.value.status).color }">{{
                    pub_var.setInternalStatus(data.value.status).text }}</span>
                </template>
            </vue3-datatable>
        </v-card>
    </div>
</template>
<script setup>
import { onMounted, ref, reactive, computed } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import * as pub_var from '@/global/global'
import moment from 'moment';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

// axios.defaults.withCredentials = true

const btnDisabled = ref(true)
const datatable = ref(null)
const mapSelected = ref([])
const selectedId = ref(null)
const requestedId = ref(null)
const selectJustOneMessage = ref('')

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
    mapSelected.value = selectedRows.map((element) => {
        return {
            service_id: element.equipment_handling.id,
            request_id: element.id
        }
    })
    const dataMap = mapSelected.value[0]
    if (dataMap) {
        selectedId.value = dataMap.service_id //SelectedId to pass in other routes for work order approval
        requestedId.value = dataMap.request_id
    }
}

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
        { field: 'equipment_handling.id', title: 'Report Number', filter: true, width: 'auto' },
        { field: 'equipment_handling.institution_name', title: 'Institution', filter: true, width: 'auto' },
        { field: 'equipment_handling.proposed_delivery_date', title: 'Proposed Delivery Date', filter: true, width: 'auto' },
        { field: 'equipment_handling.created_at', title: 'Date Requested', filter: true, width: 'auto' },
        { field: 'internal_external_name.name', title: 'Type of Activity', filter: true, width: 'auto' },
        { field: 'delegation_date', title: 'Delegation Date', width: 'auto' },
        { field: 'date_started', title: 'Date Started', width: 'auto' },
        { field: 'accomplished_date', title: 'Date Accomplished', width: 'auto', slotMode: true },
        { field: 'status', title: 'Status', width: 'auto', },
    ]) || [];



const getInternalRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('internal-request', {
            params: {
                user_id: user.user.id
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


onMounted(() => {
    getInternalRequest();
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
</style>
