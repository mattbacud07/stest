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
                                <v-text-field clearable density="compact" label="Search all fields"
                                    variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="8" style="text-align: right;">
                                <!-- <router-link :to="{ name: 'ViewWorkOrder', params: { id: selectedId ?? 0 } }">
                                    <v-btn :disabled="btnDisable" type="button" color="primary" class="text-none mr-2">
                                        <v-icon class="mr-2">mdi-eye-arrow-right</v-icon> View Request
                                    </v-btn>
                                </router-link> -->
                                <!-- <router-link to="/create-preventive-maintenance">
                                    <v-btn type="button" color="primary" class="text-none">
                                        <v-icon class="mr-2">mdi-file-edit-outline</v-icon> Create
                                    </v-btn>
                                </router-link> -->

                                <!-- Create PM request Button -->
                                <v-dialog v-model="dialogCreatePM" max-width="400" persistent>
                                    <template>
                                        <v-btn type="button" size="small" :disabled="btnDisable" color="primary"
                                            class="text-none btnSubmit">
                                            <v-icon class="mr-2">mdi-file-edit-outline</v-icon> Edit
                                        </v-btn>
                                    </template>
                                    <v-form @submit.prevent="createPM" ref="form">
                                        <v-card text="" title="Preventive Maintenance">
                                            <v-col cols="12">
                                                <v-text-field clearable density="compact" v-model="serial_number"
                                                    :rules="rules.serial_number" label="Serial Number"
                                                    variant="outlined"></v-text-field>
                                            </v-col>
                                            <template v-slot:actions>
                                                <v-row justify="end" class="mb-3">
                                                    <v-divider></v-divider>
                                                    <v-btn elevation="2" @click="dialogCreatePM = false"
                                                        background-color="red" size="small" color="#191970"
                                                        class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                                        Cancel</v-btn>
                                                    <v-btn type="submit" size="small" :loading="btnLoading"
                                                        :disabled="btnDisable" color="#191970"
                                                        class="text-none bg-primary mr-5">
                                                        <v-icon class="mr-2">mdi-file-edit-outline</v-icon> Save
                                                    </v-btn>
                                                </v-row>
                                            </template>
                                        </v-card>
                                    </v-form>
                                </v-dialog>
                            </v-col>
                        </v-row>
                        <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading"
                            :search="params.search" @rowSelect="rowSelect" :columnFilter="false"
                            :sortColumn="params.sortColumn" :sortDirection="params.sortDirection" :sortable="true"
                            skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover"
                            :hasCheckbox="true" :selectRowOnClick="true">
                            <template #serials="data">
                                <div>
                                    <v-dialog v-model="viewSerialDialog[data.value.item_id]" max-width="400" persistent>
                                        <template v-slot:activator="{ props: activatorProps }">
                                            <a href="#" v-bind="activatorProps">View</a>
                                        </template>
                                        <v-card text="" title="Serial Number">
                                            <v-col cols="12">
                                                <v-list lines="one">
                                                    <!-- Iterate through serialNumbers and show matching serials -->
                                                    <template v-for="serialData in serialNumbers"
                                                        :key="serialData.serial">
                                                        <v-list-item v-if="serialData.item_id === data.value.item_id">
                                                            <template v-slot:prepend>
                                                                <v-icon>mdi-square-medium</v-icon>
                                                            </template>
                                                            <v-list-item-title>{{ serialData.serial
                                                                }}</v-list-item-title>
                                                        </v-list-item>
                                                    </template>
                                                </v-list>
                                            </v-col>
                                            <template v-slot:actions>
                                                <v-row justify="end" class="mb-3">
                                                    <v-divider></v-divider>
                                                    <v-btn elevation="2"
                                                        @click="viewSerialDialog[data.value.item_id] = false"
                                                        background-color="red" size="small" color="#191970"
                                                        class="text-none mr-5"><v-icon>mdi-close</v-icon>
                                                        Close</v-btn>
                                                </v-row>
                                            </template>
                                        </v-card>
                                    </v-dialog>
                                </div>
                            </template>
                            <template #scheduled_at="data">
                                <div>
                                    <v-dialog v-model="viewScheduledDialog[data.value.item_id]" max-width="400"
                                        persistent>
                                        <template v-slot:activator="{ props: activatorProps }">
                                            <a href="#" v-bind="activatorProps"><v-icon
                                                    v-if="data.value.scheduled_at !== null"
                                                    color="primary">mdi-calendar-range</v-icon>
                                                <b>{{ pub_var.formatDateNoTime(data.value.scheduled_at) }}</b></a>
                                        </template>
                                        <v-card><p class="ml-3 mt-3 mb-0"><b>Scheduled Dates</b></p><v-divider></v-divider>
                                                <v-list lines="one">
                                                    <!-- <span v-for="list in data.value.list_scheduled.split(',')">{{  list }}</span> -->
                                                    <!-- Iterate through serialNumbers and show matching serials -->
                                                    <template v-for="list in data.value.list_scheduled.split(',')">
                                                        <v-list-item>
                                                            <template v-slot:prepend>
                                                                <v-icon>mdi-calendar-outline</v-icon>
                                                            </template>
                                                            <v-list-item-title color="primary">{{ moment(list).format('MMMM DD, YYYY')
                                                                }}</v-list-item-title>
                                                        </v-list-item>
                                                    </template>
                                                    
                                                </v-list>
                                                <span class="mt-5 ml-4">End of Contract : <b>{{ moment().endOf('year').format('YYYY-MM-DD') }}</b> </span>
                                            <template v-slot:actions>
                                                <v-row justify="end" class="mb-3">
                                                    <v-divider></v-divider>
                                                    <v-btn elevation="2"
                                                        @click="viewScheduledDialog[data.value.item_id] = false"
                                                        background-color="red" size="small" color="#191970"
                                                        class="text-none mr-5"><v-icon>mdi-close</v-icon>
                                                        Close</v-btn>
                                                </v-row>
                                            </template>
                                        </v-card>
                                    </v-dialog>
                                </div>
                            </template>
                            <template #service_id="data">
                                <span color="primary">{{ pub_var.setReportNumber(data.value.service_id) }}</span>
                            </template>
                            <template #status="data">
                                <span color="primary"><b>{{ data.value.status }}</b></span>
                            </template>
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
import TeamLeaderSidebar from '@/components/layout/Sidebars/TeamLeaderSidebar.vue';
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


/** Declaration */
const dialogCreatePM = ref(false)
const viewSerialDialog = ref([])
const viewScheduledDialog = ref([])
const btnDisable = ref(false)
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

/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref('');

const params = reactive({ current_page: 1, pagesize: 10, sortColumn: 'id', sortDirection: 'desc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID #', isUnique: true, type: 'number', hide: true },
        { field: 'service_id', title: 'Service #', hide: true, },
        { field: 'item_id', title: 'Item #' },
        { field: 'item_code', title: 'Item Code' },
        { field: 'serials', title: 'Serial #' },
        { field: 'description', title: 'Description' },
        { field: 'name', title: 'Institution' },
        { field: 'scheduled_at', title: 'Scheduled_at' },
        { field: 'list_scheduled', title: 'List Scheduled', hide : true, },
        { field: 'schedule', title: 'Frequency' },
        { field: 'status', title: 'Status' },
    ]) || [];

/**
 * @ Row Select Table Event
 */
const rowSelect = () => async () => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length === 1) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    const extractId = selectedRows.map((data) => { return data.id })
    selectedId.value = extractId[0]
}

/** Create PM service */
const createPM = async () => {
    // console.log('go')
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('create-pm', {
            serial_number: serial_number.value,
            user_id: user.user.id
        })
        if (response.data && response.data.success) {
            messageDetails.value = { show: true, color: 'success', text: 'Successfully created' }
        } else {
            console.log(response.data.error)
        }

    } catch (error) {
        console.log(error)
    } finally {
        btnLoading.value = false
        dialogCreatePM.value = false
        getPM()
    }
}

/** Get specific PM  */
const getPM = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-preventive-maintenance', {
            params: { groupByItemId: 1 }
        });
        if (response.data && response.data.pm_data) {
            const data = response.data.pm_data
            rows.value = data
            // list_scheduled.value = data.map((data) => { return data.list_scheduled.split(',') })
            viewSerialDialog.value = data.map((data) => { return data.item_id })
            viewScheduledDialog.value = data.map((data) => { return data.item_id })
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

/** Get specific PM Serials  */
const getPMSerials = async () => {
    try {
        const response = await apiRequest.get('get-preventive-maintenance');
        if (response.data && response.data.pm_data) {
            const data = response.data.pm_data
            const mapData = data.map((data) => ({ serial: data.serial, item_id: data.item_id, list_scheduled : data.list_scheduled }))
            serialNumbers.value = mapData
        }
    } catch (error) {
        console.log(error)
    }
};


onMounted(() => {
    getPM()
    getPMSerials()
})

</script>