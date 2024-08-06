<template>
    <!-- <div class="main-wrapper">
        <AdminSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
            </div>
        </div>
    </div> -->
    <BaseLayout>
        <div class="row">
            <div class="col-6">
                <v-row>
                    <v-col cols="4">
                        <v-text-field color="primary" v-model="params.search" clearable density="compact"
                            label="Search all fields" variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="8" style="text-align: right;">
                        <v-dialog v-model="dialog" max-width="400" persistent>
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" elevation="1"
                                    class="text-none mr-2"><v-icon class="mr-2">mdi-calendar-arrow-right</v-icon>
                                    Set</v-btn>
                            </template>
                            <v-card text="" title="Set PM Schedule">
                                <v-col cols="12" class="fix-item">
                                    <v-form @submit.prevent="saveSched" ref="form">
                                        <v-select v-model="pmSched" :rules="rules.pmSched" color="primary" clearable
                                            label="Schedule" variant="outlined" density="compact"
                                            :items="pub_maintenance.pmSchedule"></v-select>
                                        <v-text-field variant="filled" placeholder="Item Code" density="compact"
                                            readonly v-model="item_code" color="primary"></v-text-field>
                                        <v-text-field variant="filled" placeholder="Equipment #" density="compact"
                                            :rules="rules.id" readonly v-model="equipmentId"
                                            color="primary"></v-text-field>
                                        <v-divider></v-divider>
                                        <v-row justify="end" class="p-3">
                                            <v-btn elevation="0" @click="dialog = false" class="text-none mr-2">
                                                Cancel</v-btn>
                                            <v-btn type="submit" color="primary" title="Submit" class="text-none"
                                                :loading="loadingSubmit">
                                                Save</v-btn>
                                        </v-row>
                                    </v-form>
                                </v-col>
                            </v-card>
                        </v-dialog>
                    </v-col>
                </v-row>
                <p style="color: #B00020;" class="font-weight-thin">* Select one record to set</p>
                <v-row>
                    <v-col cols="12"> <!-- :hasCheckbox="true" -->
                        <vue3-datatable ref="dataTable" :rows="rows" :columns="cols" :loading="loading"
                            :search="params.search" :hasCheckbox="true" :selectRowOnClick="true" :sortable="true"
                            skin="bh-table-compact bh-table-bordered" class="set_approver_tables"
                            @rowSelect="rowSelection">
                            <template #first_name="data">
                                {{ data.value.first_name }} {{ data.value.last_name }}
                            </template>
                        </vue3-datatable>
                    </v-col>
                </v-row>
            </div>
            <div class="col-6">
                <v-row>
                    <v-col cols="4">
                        <v-text-field color="primary" v-model="params.searchEdit" clearable density="compact"
                            label="Search all fields" variant="outlined"></v-text-field>
                    </v-col>
                    <v-col cols="8" style="text-align: right;">
                        <v-dialog v-model="dialogEdit" max-width="400" persistent>
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn :disabled="btnDisableEdit" v-bind="activatorProps" color="primary" elevation="1"
                                    class="text-none mr-2"><v-icon class="mr-2">mdi-file-edit</v-icon>
                                    Edit</v-btn>
                            </template>
                            <v-card text="" title="Set PM Schedule">
                                <v-col cols="12" class="fix-item">
                                    <v-form @submit.prevent="saveSchedEdit" ref="formEdit">
                                        <v-select v-model="pmSchedEdit" :rules="rules.pmSchedEdit" color="primary"
                                            clearable label="Schedule" variant="outlined" density="compact"
                                            :items="pub_maintenance.pmSchedule"></v-select>
                                        <v-text-field variant="filled" placeholder="Item Code" density="compact"
                                            readonly v-model="item_code_edit" color="primary"></v-text-field>
                                        <v-text-field variant="filled" placeholder="Equipment #" density="compact"
                                            :rules="rules.idEdit" readonly v-model="equipmentIdEdit"
                                            color="primary"></v-text-field>
                                        <v-divider></v-divider>
                                        <v-row justify="end" class="p-3">
                                            <v-btn elevation="0" @click="dialogEdit = false" class="text-none mr-2">
                                                Cancel</v-btn>
                                            <v-btn type="submit" color="primary" title="Submit" class="text-none"
                                                :loading="loadingSubmit">
                                                Save</v-btn>
                                        </v-row>
                                    </v-form>
                                </v-col>
                            </v-card>
                        </v-dialog>
                    </v-col>
                </v-row>
                <p style="color: #B00020;" class="font-weight-thin">Set Scheduled</p>
                <v-row>
                    <v-col cols="12"> <!-- :hasCheckbox="true" -->
                        <vue3-datatable ref="dataTableEdit" :rows="rowsEdit" :columns="colsEdit" :loading="loadingEdit"
                            :search="params.searchEdit" :hasCheckbox="true" :sortDirection="params.sortDirection"
                            :selectRowOnClick="true" :sortable="true" skin="bh-table-compact bh-table-bordered"
                            class="set_approver_tables" @rowSelect="rowSelectionEdit">
                            <template #first_name="data">
                                {{ data.value.first_name }} {{ data.value.last_name }}
                            </template>
                        </vue3-datatable>
                    </v-col>
                </v-row>
            </div>
        </div>
        <v-snackbar color="success" v-model="success" location="right bottom" :timeout="5000">
            <v-icon>mdi-check-circle-outline</v-icon> Successfully Set.
        </v-snackbar>
        <v-snackbar color="error" v-model="error" location="right bottom" :timeout="5000">
            <v-icon>mdi-check-circle-outline</v-icon> Already set.
        </v-snackbar>

        <v-snackbar color="success" v-model="successEdit" location="right bottom" :timeout="5000">
            <v-icon>mdi-check-circle-outline</v-icon> Successfully updated.
        </v-snackbar>
    </BaseLayout>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import AdminSidebar from '@/components/layout/Sidebars/AdminSidebar.vue';
import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';
import * as pub_maintenance from '@/global/maintenance'

import { ref, reactive, onMounted } from 'vue';
import { user_data } from '@/stores/auth/userData'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()

const dataTable = ref(null)
const dataTableEdit = ref(null)
const dialog = ref(false)
const dialogEdit = ref(false)
const btnDisable = ref(false)
const btnDisableEdit = ref(false)
const form = ref(false)
const formEdit = ref(false)
const loadingSubmit = ref(false)
const loadingSubmitEdit = ref(false)
const success = ref(false)
const successEdit = ref(false)
const error = ref(false)
const errorEdit = ref(false)

/** Data */
const pmSched = ref('')
const item_code = ref('')
const equipmentId = ref('')

const pmSchedEdit = ref('')
const item_code_edit = ref('')
const equipmentIdEdit = ref('')


const rules = ref({
    pmSchedEdit: [
        v => !!v || 'Required field'
    ],
    idEdit: [
        v => !!v || 'Required field'
    ]
})



const loading = ref(false);
const loadingEdit = ref(false);
const total_rows = ref(0);


const params = reactive({ current_page: 1, pagesize: 10, sortDirection: 'desc' });
const rows = ref(null);
const rowsEdit = ref(null);

const cols =
    ref([
        { field: 'id', title: '', isUnique: true, type: 'number', hide: true, },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Description', hide: false, },
    ]) || [];


const colsEdit =
    ref([
        { field: 'id', title: '', isUnique: true, type: 'number', hide: true, },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Description', hide: true, },
        { field: 'schedule', title: 'Schedule', hide: false, },
    ]) || [];

/** Get all the List of Master Data */
const getAllEquipments = async () => {
    loading.value = true
    try {
        const response = await apiRequest.get('master-data-equipments')
        if (response.data && response.data.equipments) {
            const data = response.data.equipments.map(data => ({ id: data.id, item_code: data.item_code, description: data.description, item_category: data.item_category, }))
            // const item_category = 3 // item category
            // const reduceData = data.filter(data => (data.item_category === item_category))
            rows.value = data
            loading.value = false
        }
    } catch (error) {
        alert(error)
    }
}

/** Get all the List of Master Data that was set */
const getAllEquipmentsEdit = async () => {
    loadingEdit.value = true
    try {
        const response = await apiRequest.get('get-pm-sched')
        if (response.data && response.data.equipments_set_sched) {
            // const data = response.data.equipments.map(data => ({ id: data.id, item_code: data.item_code, description: data.description, schedule: data.schedule, }))
            rowsEdit.value = response.data.equipments_set_sched
            loadingEdit.value = false
        }
    } catch (error) {
        alert(error)
    }
}

/*** Row Click Selection and get Data */
const rowSelection = (data) => {
    const selectedRow = dataTable.value.getSelectedRows()
    if (selectedRow && selectedRow.length === 1) {
        btnDisable.value = false
        item_code.value = selectedRow[0].item_code
        equipmentId.value = selectedRow[0].id
    } else {
        btnDisable.value = true
    }
}

/*** Row Click Selection and get Data to Edit */
const rowSelectionEdit = (data) => {
    const selectedRow = dataTableEdit.value.getSelectedRows()
    if (selectedRow && selectedRow.length === 1) {
        btnDisableEdit.value = false
        item_code_edit.value = selectedRow[0].item_code
        equipmentIdEdit.value = selectedRow[0].id
        pmSchedEdit.value = selectedRow[0].schedule
    } else {
        btnDisableEdit.value = true
    }
}

/** Save Schedule */
const saveSched = async () => {
    loadingSubmit.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSubmit.value = false
        return
    }
    try {
        const response = await apiRequest.post('save-pm-sched', {
            equipmentId: equipmentId.value,
            pmSched: pmSched.value,
        })
        if (response.data && response.data.success) {
            success.value = true
            dialog.value = false
            getAllEquipmentsEdit()
        }
        if (response.data && response.data.set) {
            error.value = true
            dialog.value = false
        }
    } catch (error) {
        alert(error)
    } finally {
        loadingSubmit.value = false
    }
}

/** Save Schedule */
const saveSchedEdit = async () => {
    loadingSubmitEdit.value = true
    const { valid } = await formEdit.value.validate()
    if (!valid) {
        loadingSubmitEdit.value = false
        return
    }
    try {
        const response = await apiRequest.put('edit-pm-sched', {
            id: equipmentIdEdit.value,
            pmSched: pmSchedEdit.value,
        })
        if (response.data && response.data.success) {
            successEdit.value = true
            dialogEdit.value = false
            getAllEquipmentsEdit()
        }
    } catch (error) {
        alert(error)
    } finally {
        loadingSubmitEdit.value = false
    }
}

onMounted(() => {
    getAllEquipments()
    getAllEquipmentsEdit()
})

</script>