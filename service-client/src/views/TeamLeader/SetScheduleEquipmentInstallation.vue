<template>
    <div class="main-wrapper">
        <TeamLeaderSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <div>
                    <v-card class="mx-auto p-4">
                        <v-row>
                            <v-col cols="4">
                                <v-text-field v-model="params.search" clearable density="compact"
                                    label="Search all fields" variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="8" style="text-align: right;">
                                <!-- <router-link :to="{name : 'WorkOrderApprover', params : {id : selectedId ?? 0}}"> -->
                                <router-link :to="{ name: 'AssignEquipmentInstallation', params: { id: selectedId ?? 0, status : status ?? 0 } }">
                                    <v-btn :disabled="btnDisable" type="button" color="primary" class="text-none mr-2">
                                        <v-icon class="mr-2">mdi-file-eye-outline</v-icon> View
                                    </v-btn>
                                </router-link>
                            </v-col>
                        </v-row>

                        <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading"
                            :search="params.search" @rowSelect="rowSelect" :columnFilter="false"
                            :sortColumn="params.sortColumn" :sortDirection="params.sortDirection" :sortable="true"
                            skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover"
                            :hasCheckbox="true" :selectRowOnClick="true">
                            <template #id="data">
                                <span>{{ pub_var.setReportNumber(data.value.id, data.value.created_at) }}</span>
                                <!-- <span>JOF-{{ String(data.value.id).padStart(3,0) }}-{{moment(data.value.created_at).format('YYYY')}}</span>  -->
                            </template>
                            <template #approver_name="data">
                                <span>{{ parseInt(data.value.main_status) === pub_var.DISAPPROVED ? '' :
                                    data.value.approver_name }}</span>
                            </template>
                            <template #main_status="data">
                                <span :style="{ color: pub_var.setJOStatus(data.value.main_status).color }">{{
                                    pub_var.setJOStatus(data.value.main_status).text }}</span>
                            </template>
                            <template #created_at="data">
                                <span>{{ moment(data.value.created_at).format('MM/DD/YYYY') }}</span>
                            </template>
                        </vue3-datatable>
                    </v-card>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, reactive } from 'vue';
import { user_data } from '@/stores/auth/userData'
import Header from '@/components/layout/Header.vue'
import TeamLeaderSidebar from '@/components/layout/Sidebars/TeamLeaderSidebar.vue';
import alertMessage from '@/components/PopupMessage/alertMessage.vue';
import * as pub_var from '@/global/global'
import moment from 'moment';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** Declaration of User Data */
const user = user_data();
const apiRequest = user.apiRequest()
const btnDisable = ref(true)
const datatable = ref(null)
const selectedId = ref(0)
const status = ref(0)

/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref('');

const params = reactive({ current_page: 1, pagesize: 10, sortColumn : 'id', sortDirection : 'desc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Report Number', isUnique: true, type: 'number', hide : false },
        // { field: 'first_name', title: 'Requested by' },
        { field: 'name', title: 'Institution' },
        { field: 'address', title: 'Address' , hide: true},
        { field: 'request_name', title: 'External Request' },
        { field: 'internal_name', title: 'Internal Request' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date', type: 'date' },
        { field: 'created_at', title: 'Date Requested', type: 'date' },
        { field: 'approver_name', title: 'Pending Approval'}, //minWidth : '300px' 
        { field: 'main_status', title: 'Status', type : 'number'}, //  minWidth : '200px',
    ]) || [];

/**
 * @ Row Select Table Event
 */
const rowSelect = () => {
    const selectedRows = datatable.value.getSelectedRows()
    if(selectedRows && selectedRows.length === 1){
        btnDisable.value = false
    }else{
        btnDisable.value = true
    }

    const extractId = selectedRows.map((data) => {return data.id})
    const statusId = selectedRows.map((data) => {return data.status})
    selectedId.value = extractId[0]
    status.value = statusId[0]
}

/** getStatus Text */
// const getStatusText = (status) => {
//     if(pub_var.DISAPPROVED == status){
//         return { text: 'Disapproved', color : 'red' }
//     }
//     if(pub_var.ONGOING == status){
//         return { text: 'Ongoing', color : '#0249ff' }
//     }
//     if(pub_var.COMPLETE == status){
//         return { text: 'Completed', color : 'green' }
//     }
//     if(pub_var.PARTIAL_COMPLETE == status){
//         return { text: 'Ongoing', color : '#0249ff' }
//     }
// }


const getRequestToAssign = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-request-to-assign-installation');
        
        if(response.data && response.data.request_to_assign){
            const data = response.data.request_to_assign
            rows.value = data
        }else{
            alert(response.data.error)
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};


onMounted(() => {
    getRequestToAssign();
});
</script>

