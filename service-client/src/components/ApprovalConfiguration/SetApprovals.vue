<template>
    <div>
        <v-row>
            <v-col cols="4">
                <v-text-field color="primary" v-model="params.search" clearable density="compact"
                    label="Search all fields" variant="outlined"></v-text-field>
            </v-col>
            <v-col cols="8" style="text-align: right;">
                <v-dialog v-model="dialog" max-width="400" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" density="compact" variant="outlined"
                            class="text-none mr-2"><v-icon class="mr-2">mdi-account-check-outline</v-icon>
                            Set Approver</v-btn>
                    </template>
                    <v-card text="" title="Set Approver">
                        <v-col cols="12" class="fix-item">
                            <v-form @submit.prevent="submitApprover" ref="form">
                                <v-combobox color="primary" v-model="selectedDesignation" :rules="rule.designation"
                                    clearable label="Designation" density="compact" :items="[
                                    { key: 'IT DEPARTMENT', value: 1 },
                                    { key: 'APM/NSM/SM', value: 2 },
                                    { key: 'WAREHOUSE & INVENTORY MANAGEMENT', value: 3 },
                                    { key: 'SERVICE DEPARTMENT TEAM LEADER', value: 4 },
                                    { key: 'SERVICE DEPARTMENT HEAD / SERVICE ENGINEER', value: 5 },
                                    { key: 'BILLING & INVOICING STAFF / WIM PERSONNEL', value: 6 },
                                ]" variant="outlined" itemValue="value" itemTitle="key"> </v-combobox>
                                <v-row class="mt-4">
                                    <v-col cols="8">
                                        <v-text-field variant="outlined" placeholder="Name" density="compact" :rules="rule.name"
                                    readonly v-model="userName" color="primary" label="User Name"></v-text-field>
                                    </v-col>
                                    <v-col cols="4">
                                        <v-text-field variant="outlined" placeholder="User ID" density="compact"
                                    :rules="rule.id" readonly v-model="userId" color="primary" label="User ID"></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-divider></v-divider>
                                <v-row justify="end" class="p-3">
                                    <v-btn elevation="0" @click="dialog = false" density="compact"
                                        class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                                        Cancel</v-btn>
                                    <v-btn type="submit" color="primary" density="compact"  title="Submit"
                                        class="text-none" :loading="loadingSubmit"><v-icon
                                            class="mr-2">mdi-file-send-outline</v-icon> Submit</v-btn>
                                </v-row>
                            </v-form>
                        </v-col>
                    </v-card>
                </v-dialog>
            </v-col>
        </v-row>
        <!-- <p style="color: #B00020;" class="font-weight-thin">* Select one record to set</p> -->
        <v-row>
            <v-col cols="12"> <!-- :hasCheckbox="true" -->
                <vue3-datatable ref="dataTable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                    :columnFilter="enableFilter ?? false" :hasCheckbox="true" :selectRowOnClick="true" :sortable="true"
                    :hide="true" :filter="true" skin="bh-table-compact bh-table-bordered"
                    @rowSelect="rowSelection">
                    <template #first_name="data">
                        <span>{{ userName }}</span>
                    </template>
                </vue3-datatable>
            </v-col>
        </v-row>
        <v-snackbar color="success" v-model="snackbarSuccess" location="right bottom" :timeout="5000">
            <v-icon>mdi-check-circle-outline</v-icon> Successfully Set.
        </v-snackbar>
        <v-snackbar color="error" v-model="snackbarError" location="right bottom" :timeout="5000">
            <v-icon>mdi-alert-circle-outline</v-icon> Already set as an approver.
        </v-snackbar>
    </div>
</template>
<script setup>
import { onMounted, ref, reactive } from 'vue';
import axios from 'axios'

import { user_data } from '@/stores/auth/userData'
import { alertStore } from '@/stores/alert-popup'
// import * as designation from '@/global/global'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const alert = alertStore()

/** Declaration of User Data */
const user = user_data();
user.getUserData
const apiRequest = user.apiRequest()

/** Enable Filter */
const enableFilter = ref(false)

/** Form Values */
const userId = ref('')
const userName = ref('')
const loadingSubmit = ref(false);
const form = ref(false)
const selectedDesignation = ref('')
const rule = ref({
    designation: [
        v => !!v || 'Required field',
        x => x.key !== undefined ? true : 'Select one of the options listed',
        r => r.value !== undefined ? true : 'Select one of the options listed',
    ],
    name: [
        v => !!v || 'Required field'
    ],
    id: [v => !!v || 'Required field'],
})

const btnLoading = ref(false)
const btnDisable = ref(true)
const dialog = ref(false)
const dataTable = ref(null)
const snackbarSuccess = ref(false)
const snackbarError = ref(false)


const loading = ref(true);
const total_rows = ref(0);


const params = reactive({ current_page: 1, pagesize: 10 });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Row ID', isUnique: true, type: 'number', hide : true, },
        { field: 'user_id', title: 'User ID', isUnique: true, type: 'number' },
        { field: 'users.first_name', title: 'Name' },
        // { field: 'users.last_name', title: 'Last Name', hide: false, },
        { field: 'users.position_name', title: 'Position' },
        { field: 'users.name', title: 'Department' },
    ]) || [];

/*** Row Click Selection and get Data */
const rowSelection = () => {
    const selectedRow = dataTable.value.getSelectedRows()
    if (selectedRow && selectedRow.length === 1) {
        btnDisable.value = false
        userName.value = selectedRow[0].users.first_name + ' ' + selectedRow[0].users.last_name
        userId.value = selectedRow[0].user_id
    } else {
        btnDisable.value = true
    }
    // console.log(selectedRow)
}


/** Submit Form */
const submitApprover = async () => {
    loadingSubmit.value = true
    const { valid } = await form.value.validate()


    if (!valid) {
        loadingSubmit.value = false
        return
    }
    try {
        const response = await apiRequest.post('submit-approver', {
            user_id: userId.value,
            approver_level: selectedDesignation.value.value,
            approver_level_name: selectedDesignation.value.key,
        })
        if (response.data && response.data.success) {
            form.value.reset()
            snackbarSuccess.value = true
        } else {
            form.value.reset()
            snackbarError.value = true
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingSubmit.value = false
        dialog.value = false
    }


    // console.log(selectedDesignation.value.key === undefined ? "None" : 'Meron')

}

const getUsers = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get-approver-roles');
        const data = response.data.approver_role

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};
const changeServer = (data) => {
    params.current_page = data.current_page;
    params.pagesize = data.pagesize;

    getUsers();
};


onMounted(() => {
    getUsers();
});
</script>
