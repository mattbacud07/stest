<template>
    <BaseLayout>
        <v-card class="p-4">
            <v-tabs v-model="tab" density="compact" class="border-b-sm" bg-color="grey-lighten-5">
                <v-tab value="set" class="text-none" color="primary"><v-icon class="mr-2">mdi-set-split</v-icon>
                    Set Frequency</v-tab>
                <v-tab value="edit" class="text-none" color="primary"><v-icon
                        class="mr-2">mdi-archive-edit-outline</v-icon> Edit Frequency</v-tab>
            </v-tabs>

            <v-card-text>
                <v-window v-model="tab" :disabled="true">
                    <v-window-item value="set">
                        <v-row>
                            <v-col cols="4" xl="8" md="8" sm="8" style="text-align: left;">
                                <v-dialog v-model="dialog" max-width="400" persistent>
                                    <template v-slot:activator="{ props: activatorProps }">
                                        <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary"
                                            variant="tonal" class="text-none mr-2"><v-icon
                                                class="mr-2">mdi-calendar-arrow-right</v-icon>
                                            Set</v-btn>
                                    </template>
                                    <v-card text="" title="Set PM Frequency">
                                        <v-col cols="12" class="fix-item">
                                            <v-form @submit.prevent="saveSched" ref="form">
                                                <v-select v-model="frequency" :rules="rules.frequency" color="primary"
                                                    clearable label="Schedule" variant="outlined" density="compact"
                                                    :items="pub_maintenance.pmFrequency"></v-select>
                                                <v-row>
                                                    <v-col cols="8">
                                                        <v-text-field variant="filled" placeholder="Item Code"
                                                            density="compact" readonly v-model="item_code"
                                                            color="primary"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="4">
                                                        <v-text-field variant="filled" placeholder="Equipment #"
                                                            density="compact" :rules="rules.id" readonly
                                                            v-model="equipmentId" color="primary"></v-text-field>
                                                    </v-col>
                                                </v-row>
                                                <v-divider></v-divider>
                                                <v-row justify="end" class="p-3">
                                                    <v-btn elevation="0" @click="dialog = false" class="text-none mr-2">
                                                        Cancel</v-btn>
                                                    <v-btn type="submit" color="primary" title="Submit"
                                                        class="text-none" :loading="loadingSubmit">
                                                        Save</v-btn>
                                                </v-row>
                                            </v-form>
                                        </v-col>
                                    </v-card>
                                </v-dialog>
                            </v-col>
                            <v-col cols="8" xl="4" md="4" sm="4">
                                <v-text-field color="primary" v-model="params.search" clearable density="compact"
                                    label="Search all fields" variant="outlined"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12"> <!-- :hasCheckbox="true" -->
                                <vue3-datatable ref="dataTable" :rows="rows" :columns="cols" :loading="loading"
                                    :search="params.search" :isServerMode="true" :totalRows="total_rows"
                                    :pageSize="params.pagesize" :hasCheckbox="true" :selectRowOnClick="true"
                                    :sortable="true" skin="bh-table-compact bh-table-bordered"
                                    class="set_approver_tables" @rowSelect="rowSelection" @change="changeServer">
                                    <template #first_name="data">
                                        {{ data.value.first_name }} {{ data.value.last_name }}
                                    </template>
                                </vue3-datatable>
                            </v-col>
                        </v-row>
                    </v-window-item>

                    <v-window-item value="edit">
                        <v-row>
                            <v-col cols="4" xl="8" md="8" sm="8" style="text-align: left;">
                                <v-dialog v-model="dialogEdit" max-width="400" persistent>
                                    <template v-slot:activator="{ props: activatorProps }">
                                        <v-btn :disabled="btnDisableEdit" v-bind="activatorProps" color="primary"
                                            variant="tonal" class="text-none mr-2"><v-icon
                                                class="mr-2">mdi-file-edit</v-icon>
                                            Edit</v-btn>
                                    </template>
                                    <v-card text="" title="Set PM Frequency">
                                        <v-col cols="12" class="fix-item">
                                            <v-form @submit.prevent="saveSchedEdit" ref="formEdit">
                                                <v-select v-model="frequencyEdit" :rules="rules.frequencyEdit"
                                                    color="primary" clearable label="Schedule" variant="outlined"
                                                    density="compact" :items="pub_maintenance.pmFrequency"></v-select>
                                                <v-row class="mt-3">
                                                    <v-col cols="8">
                                                        <v-text-field variant="filled" placeholder="Item Code"
                                                            density="compact" readonly v-model="item_code_edit"
                                                            color="primary"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="4">
                                                        <v-text-field variant="filled" placeholder="Equipment #"
                                                            density="compact" :rules="rules.idEdit" readonly
                                                            v-model="equipmentIdEdit" color="primary"></v-text-field>
                                                    </v-col>
                                                </v-row>

                                                <v-divider></v-divider>
                                                <v-row justify="end" class="p-3">
                                                    <v-btn elevation="0" @click="dialogEdit = false"
                                                        class="text-none mr-2">
                                                        Cancel</v-btn>
                                                    <v-btn type="submit" color="primary" title="Submit"
                                                        class="text-none" :loading="loadingSubmit">
                                                        Save</v-btn>
                                                </v-row>
                                            </v-form>
                                        </v-col>
                                    </v-card>
                                </v-dialog>
                            </v-col>
                            <v-col cols="8" xl="4" md="4" sm="4">
                                <v-text-field color="primary" v-model="params.searchEdit" clearable density="compact"
                                    label="Search all fields" variant="outlined"></v-text-field>
                            </v-col>
                        </v-row>

                        <v-row>
                            <v-col cols="12"> <!-- :hasCheckbox="true" -->
                                <vue3-datatable ref="dataTableEdit" :rows="rowsEdit" :columns="colsEdit"
                                    :loading="loadingEdit" :search="params.searchEdit" :hasCheckbox="true"
                                    :sortDirection="params.sortDirection" :selectRowOnClick="true" :sortable="true"
                                    skin="bh-table-compact bh-table-bordered" class="set_approver_tables"
                                    @rowSelect="rowSelectionEdit">
                                    <template #first_name="data">
                                        {{ data.value.first_name }} {{ data.value.last_name }}
                                    </template>
                                </vue3-datatable>
                            </v-col>
                        </v-row>
                    </v-window-item>
                </v-window>
            </v-card-text>
        </v-card>
        <v-tabs>

        </v-tabs>
    </BaseLayout>
</template>

<script setup>
import { ref, reactive, onMounted, provide } from 'vue';
import { user_data } from '@/stores/auth/userData'


import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';
// import Dialog from '@/components/dialog/Dialog.vue'
import * as pub_maintenance from '@/global/maintenance'
import debounce from 'lodash/debounce'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

const tab = ref(null)
/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

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
const frequency = ref('')
const item_code = ref('')
const equipmentId = ref('')

const frequencyEdit = ref('')
const item_code_edit = ref('')
const equipmentIdEdit = ref('')


const rules = ref({
    frequency: [
        v => !!v || 'Required field'
    ],
    frequencyEdit: [
        v => !!v || 'Required field'
    ],
    idEdit: [
        v => !!v || 'Required field'
    ]
})



const loading = ref(false);
const loadingEdit = ref(false);
const total_rows = ref(0);


const params = reactive({ current_page: 1, pagesize: 10, sortDirection: 'desc', search: '' });
const rows = ref(null);
const rowsEdit = ref(null);

const cols =
    ref([
        { field: 'id', title: '', isUnique: true, type: 'number', hide: true, },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Description', hide: false, },
    ]) || [];
const searchColumn = cols.value.map(data => data.field)


const colsEdit =
    ref([
        { field: 'id', title: '', isUnique: true, type: 'number', hide: true, },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Description', hide: true, },
        { field: 'schedule', title: 'Schedule', hide: false, },
    ]) || [];

/** Get all the List of Master Data */
const item_category = ref('')
// const item_category = ref([1, 5, 7])
const getAllEquipments = async () => {
    loading.value = true
    try {
        const response = await apiRequest.get('master-data-equipments', { params: { ...params, item_category: item_category.value, searchColumn: searchColumn } })
        if (response.data && response.data.equipments) {
            const data = response.data.equipments
            rows.value = data.data
            total_rows.value = data.total;

            loading.value = false
        }
    } catch (error) {
        alert(error)
    }
}
/** Server Mode */
const debounceSearch = debounce(getAllEquipments, 300)
const changeServer = (data) => {
    params.current_page = data.current_page;
    params.pagesize = data.pagesize;
    params.search = data.search

    if (data.change_type === 'search') {
        debounceSearch()
    } else {
        getAllEquipments();
    }
};

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
        frequencyEdit.value = selectedRow[0].schedule
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
            frequency: frequency.value,
        })
        if (response.data && response.data.success) {
            toast.success('Successfully Set')
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
            frequency: frequencyEdit.value,
        })
        if (response.data && response.data.success) {
            toast.success('Edited uccessfully')
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


<style scoped>
.v-tab-item--selected{
    background: red!important;
}
</style>