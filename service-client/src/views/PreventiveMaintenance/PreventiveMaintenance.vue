<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #default="{ searchText }">
            <v-card class="mx-auto p-4">
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" @change="changeServer"
                @rowSelect="rowSelect" :columnFilter="false" :sortColumn="params.sortColumn"
                :sortDirection="params.sortDirection" :sortable="true"
                skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" :hasCheckbox="true"
                :selectRowOnClick="true">
                <template #scheduled_at="data">
                    <div>
                        <v-dialog v-model="viewScheduledDialog[data.value.item_id]" max-width="400" persistent>
                            <template v-slot:activator="{ props: activatorProps }">
                                <a href="#" v-bind="activatorProps"><v-icon v-if="data.value.scheduled_at !== null"
                                        color="primary">mdi-calendar-range</v-icon>
                                    <b>{{ pub_var.formatDateNoTime(data.value.scheduled_at) }}</b></a>
                            </template>
                            <v-card>
                                <p class="ml-3 mt-3 mb-0"><b>Scheduled Dates</b></p><v-divider></v-divider>
                                <v-list lines="one">
                                    <!-- <span v-for="list in data.value.list_scheduled.split(',')">{{  list }}</span> -->
                                    <!-- Iterate through serialNumbers and show matching serials -->
                                        <v-list-item v-for="list in data.value.list_scheduled.split(',').map(dateString => moment(dateString.trim()).format('MMMM DD, YYYY'))">
                                            <template v-slot:prepend>
                                                <v-icon>mdi-calendar-outline</v-icon>
                                            </template>
                                            <v-list-item-title color="primary">{{ list}}</v-list-item-title>
                                        </v-list-item>

                                </v-list>
                                <span class="mt-5 ml-4">End of the Year : <b>{{
                        moment().endOf('year').format('YYYY-MM-DD') }}</b> </span>
                                <template v-slot:actions>
                                    <v-row justify="end" class="mb-3">
                                        <v-divider></v-divider>
                                        <v-btn elevation="2" @click="viewScheduledDialog[data.value.item_id] = false"
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
                    <span :style="{ color : m_var.status_pm(data.value.status).color }"><b>{{ m_var.status_pm(data.value.status).text  }}</b></span><br/>
                    <span class="small" v-if="data.value.status === 'Not Set'">Contact the admin to configure the frequency</span>
                </template>
                <template #engineer="data">
                    {{ data.value.first_name }} {{ data.value.last_name }}
                </template>
            </vue3-datatable>
        </v-card>
        </template>
    </LayoutWithActions>
</template>

<script setup>
import { onMounted, ref, reactive, provide, watch, getCurrentInstance } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { useRouter } from 'vue-router';
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance.js'
import moment from 'moment';
import debounce from 'lodash/debounce'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue'

const role = getRole()
const currentUserRole = role.currentUserRole

const datatable = ref(null)

/** Sent to Topbar */  // Subject to Change [Set Roles and Permission Dynamically]
const category = ref('')
const routeView = ref('PMView')
const service_id = ref(null)
const btnDisable = ref(true)
const selectedId = ref(null)

const actions = {
    selectedId: selectedId,
    btnDisable: btnDisable,
    routeView: routeView,
}

provide('data', actions)

/** User_data Store */
const user = user_data()
const apiRequest = user.apiRequest()
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
const multiSelected = ref([])
const rowSelect = () => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length === 1) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    const extractId = selectedRows.map((data) => { return data.id })
    selectedId.value = extractId[0]
    multiSelected.value = extractId
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
const getSearchText = (data) =>{
    params.search = data
}
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sortColumn: 'scheduled_at', sortDirection: 'desc', search : '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID #', isUnique: true, type: 'number', hide: true },
        { field: 'service_id', title: 'Service #', hide: true, },
        { field: 'item_id', title: 'Item #', hide: true },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Item Description', hide : true,},
        { field: 'serial_number', title: 'Serial #' },
        { field: 'institution_name', title: 'Institution' },
        { field: 'institution_address', title: 'Address', hide : true, },
        { field: 'date_installed', title: 'Date Installed', hide : true, },
        { field: 'scheduled_at', title: 'Scheduled_at' },
        { field: 'schedule', title: 'Frequency' },
        { field: 'ssu', title: 'SSU' },
        { field: 'engineer', title: 'Delegated to' },
        { field: 'status_after_service', title: 'Status After Service' },
        { field: 'status', title: 'Status' },
    ]) || [];

// const searchColumnMap = cols.value.map(v => v.field);
// const searchColumn = searchColumnMap.filter(v => v !== 'id')
    provide('column', cols)

const getPM = async () => {
    if (currentUserRole === pub_var.TLRole) category.value = pub_var.TLRole
    if (currentUserRole === pub_var.engineerRole) category.value = pub_var.engineerRole
    try {
        loading.value = true;
        const response = await apiRequest.get('get-preventive-maintenance',{
            params : {...params, category : category.value }
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
// ChangeServer for Servre Mode
const debounceSearch = debounce(getPM, 300)
const changeServer = (data) =>{
    params.current_page = data.current_page
    params.pagesize = data.pagesize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if(data.change_type === 'search'){
        debounceSearch()
    }else getPM()
}



onMounted(() => {
    getPM()
    // getPMSerials()
})

</script>