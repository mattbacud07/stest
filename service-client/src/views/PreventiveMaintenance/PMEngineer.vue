<template>
    <div class="main-wrapper">
        <EngineersSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <div>
                    <v-card class="mx-auto p-4">
                        <v-row>
                            <v-col cols="4">
                                <v-text-field clearable density="compact" label="Search all fields"
                                    variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="8" style="text-align: right;">
                                <!-- View PM request Button -->
                                <router-link :to="{ name: 'PMDetails', params: { id: selectedId ?? 0 } }">
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
                            <template #status="data">
                                <span :style="{ color: maintenance.status_pm(data.value.status).color }">{{
                                    maintenance.status_pm(data.value.status).text }}</span>
                            </template>
                            <!-- <template #created_at="data">
                                <span>{{ moment(data.value.created_at).format('MM/DD/YYYY') }}</span>
                            </template> -->
                        </vue3-datatable>
                    </v-card>
                    <alertMessage v-if="messageDetails.show" :details="messageDetails" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import EngineersSidebar from '@/components/layout/Sidebars/EngineersSidebar.vue';
// import EHMain from '@/components/EquipmentHandling/EHMain.vue'
import alertMessage from '@/components/PopupMessage/alertMessage.vue';

import { onMounted, ref, reactive } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { useRouter } from 'vue-router';
import * as pub_var from '@/global/global'
import * as maintenance from '@/global/maintenance.js'
import moment from 'moment';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'


/** User_data Store */
const user = user_data()
const apiRequest = user.apiRequest()
const router = useRouter()

const form = ref(false)
const datatable = ref(null)


/** Declaration */
const dialogCreatePM = ref(false)
const selectedId = ref(null)
const btnDisable = ref(false)
const btnLoading = ref(false)
const serial_number = ref('')
const messageDetails = ref({})

const rules = ref({
    serial_number: [
        v => !!v || 'Required field'
    ],
})

/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref('');

const params = reactive({ current_page: 1, pagesize: 10, sortColumn: 'id', sortDirection: 'desc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'id', isUnique: true, type: 'number', hide: false },
        // { field: 'first_name', title: 'Requested by' },
        { field: 'name', title: 'Institution' },
        // { field: 'address', title: 'Address'},
        // { field: 'address', title: 'Contact Details'},
        { field: 'equipment_model', title: 'Equipment Model' },
        { field: 'serial_number', title: 'Serial #' },
        // { field: 'customer_complaint', title: 'Customer Complaint' },
        // { field: 'data_received', title: 'Date & Time Received'},
        // { field: 'work_type', title: 'Work Type'},
        // { field: 'cs_actions', title: 'CS Action'},
        // { field: 'engineer', title: 'Engineer'},
        // { field: 'departed_date', title: 'Departed Date'},
        // { field: 'start_date', title: 'Start Date'},
        { field: 'status', title: 'Status' },
    ]) || [];

/**
 * @ Row Select Table Event
 */
const rowSelect = () => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length === 1) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    const extractId = selectedRows.map((data) => { return data.id })
    selectedId.value = extractId[0]
}

/** Get specific PM  */
const getPM = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-preventive-maintenance-engineer', {
            params: { user_id: user.user.id }
        });
        if (response.data && response.data.pm_data) {
            const data = response.data.pm_data
            rows.value = data
        }
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false;
    }

};


onMounted(() => {
    getPM()
})

</script>