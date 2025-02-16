<template>
    <v-col cols="12">
        <v-row>
            <v-col v-if="role === pub_var.approverRoleID" cols="12" class="d-flex justify-end">
                <!-- Approver Settings -->
                <v-dialog v-model="settings" transition="dialog-bottom-transition" fullscreen
                    style="z-index: 1080!important;">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="dark" variant="tonal" density="comfortable"
                            :loading="loading" @click="getApproverDetails" class="text-none"><v-icon
                                class="mr-2">mdi-cog</v-icon>
                            Setting</v-btn>
                    </template>

                    <v-card>
                        <v-toolbar dense style="position: fixed;z-index:999;">
                            <p><b class="ml-5 text-primary">Approval Configuration Settings</b></p>
                            <v-spacer></v-spacer>
                            <v-btn icon="mdi-close" class="mr-2" variant="text" @click="settings = false"></v-btn>
                            <v-btn text="Save" variant="flat" color="primary" :loading="loadingSubmit"
                                @click="saveChanges"></v-btn>
                        </v-toolbar>
                        <v-row class="mt-15 ma-4">
                            <v-col cols="12">
                                <v-row>
                                    <v-col cols="4">
                                        <v-text-field single-line density="compact" v-model="params_approver.search"
                                            clearable variant="outlined" placeholder="Seach..."></v-text-field>
                                    </v-col>
                                    <v-col cols="2">
                                        <v-btn @click="handleRefreshApprover" icon color="primary" size="small"
                                            variant="text" class="mr-1"><v-icon>mdi-refresh</v-icon></v-btn>
                                    </v-col>
                                    <v-col cols="6" class="d-flex justify-end align-center">
                                        <p class="small text-error">Don't forget to save after editing.</p>
                                    </v-col>
                                </v-row>
                                <vue3-datatable ref="datatable" :rows="approver_rows" :columns="approver_cols"
                                    :loading="loadingApprover" :search="params_approver.search"
                                    :selectRowOnClick="false" :hasCheckbox="false" :sortable="false"
                                    :sortColumn="params_approver.sort_column"
                                    :sortDirection="params_approver.sort_direction" :hide="true"
                                    :pageSize="params_approver.pagesize" :filter="false"
                                    skin="bh-table-compact bh-table-bordered" cellClass="assignedRoleCellClass">

                                    <template #users.fullname="data">
                                        <p class="text-primary"><b>{{ data.value.users.fullname }}</b></p>
                                        <p class="small textDown">{{ data.value.users.position_name }}</p>
                                    </template>
                                    <template #eh="data">
                                        <v-combobox color="primary" variant="plain" placeholder="Select level"
                                            density="compact" :items="approver_designation" itemValue="value"
                                            :disabled="loadingSubmit"
                                            @update:modelValue="updateEHApproverLevel(data.value.id, data.value.user_id, data.value.role_id, 'eh', $event)"
                                            itemTitle="title" multiple chips
                                            v-model="getEHSelectedItems[data.value.id]">
                                        </v-combobox>
                                    </template>
                                    <template #pullout="data">
                                        <v-combobox color="primary" variant="plain" placeholder="Select level"
                                            density="compact" :items="pullout_designation" itemValue="value"
                                            itemTitle="title" multiple chips :disabled="loadingSubmit"
                                            @update:modelValue="updateEHApproverLevel(data.value.id, data.value.user_id, data.value.role_id, 'pullout', $event)"
                                            v-model="getPulloutSelectedItems[data.value.id]"></v-combobox>
                                    </template>
                                    <template #sbu="data">
                                        <v-combobox color="primary" variant="plain" placeholder="Select SBU"
                                            density="compact" :items="pub_var.sbuArray" :disabled="loadingSubmit"
                                            v-model="assigned_sbu[data.value.id]" clearable chips
                                            @update:modelValue="updateEHApproverLevel(data.value.id, data.value.user_id, data.value.role_id, 'sbu', $event)"></v-combobox>
                                    </template>
                                    <template #satellite="data">
                                        <v-combobox color="primary" variant="plain" placeholder="Select Satellite"
                                            density="compact" :items="satellite" :disabled="loadingSubmit"
                                            v-model="assigned_satellite[data.value.id]" clearable chips
                                            @update:modelValue="updateEHApproverLevel(data.value.id, data.value.user_id, data.value.role_id, 'satellite', $event)"></v-combobox>
                                    </template>
                                </vue3-datatable>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-dialog>
            </v-col>


            <v-col v-if="role === pub_var.requestorID" cols="12" class="d-flex justify-end">
                <!-- Requestor Settings -->
                <v-dialog v-model="requestor_settings" transition="dialog-bottom-transition" fullscreen
                    style="z-index: 1080!important;">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="dark" variant="tonal" density="comfortable"
                            :loading="loading" class="text-none"><v-icon class="mr-2">mdi-cog</v-icon>
                            Setting</v-btn>
                    </template>

                    <v-card>
                        <v-toolbar dense style="position: fixed;z-index:999;">
                            <p><b class="ml-5 text-primary">Requestor Configuration Settings</b></p>
                            <v-spacer></v-spacer>
                            <v-btn icon="mdi-close" class="mr-2" variant="text"
                                @click="requestor_settings = false"></v-btn>
                            <v-btn text="Save" variant="flat" color="primary" :loading="loadingSubmit"
                                @click="assignSupervisor"></v-btn>
                        </v-toolbar>
                        <v-row class="mt-15 ma-4">
                            <v-col cols="12">
                                <v-row>
                                    <v-col cols="4">
                                        <v-text-field single-line density="compact" v-model="params.search" clearable
                                            variant="outlined" placeholder="Seach..."></v-text-field>
                                    </v-col>
                                    <v-col cols="2">
                                        <v-btn @click="handleRefresh" icon color="primary" size="small" variant="text"
                                            class="mr-1"><v-icon>mdi-refresh</v-icon></v-btn>
                                    </v-col>
                                    <v-col cols="6" class="d-flex justify-end align-center">
                                        <p class="small text-error">Don't forget to save after editing.</p>
                                    </v-col>
                                </v-row>
                                <vue3-datatable ref="datatable" :rows="rows" :columns="cols_requestor"
                                    :loading="loading" :search="params.search" skin="bh-table-compact bh-table-bordered"
                                    cellClass="assignedRoleCellClass">

                                    <template #users.fullname="data">
                                        <p class="text-primary"><b>{{ data.value.users.fullname }}</b></p>
                                        <p class="small textDown">{{ data.value.users.position_name }}</p>
                                    </template>
                                    <template #supervisor="data">
                                        <v-combobox color="primary" variant="plain" placeholder="Select Supervisor"
                                            density="compact" :items="users" itemValue="id" itemTitle="user_name"
                                            v-model="requestor_supervisor[data.value.id]" clearable chips
                                            @update:modelValue="selectSupervisor(data.value.id, $event)"></v-combobox>

                                    </template>
                                </vue3-datatable>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-dialog>
            </v-col>


            <v-col v-if="showServiceDeptSetting" cols="12" class="d-flex justify-end">
                <!-- Service Dept Settings -->
                <v-dialog v-model="service_settings" transition="dialog-bottom-transition" fullscreen
                    style="z-index: 1080!important;">
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn v-bind="activatorProps" color="dark" variant="tonal" density="comfortable"
                            :loading="loading" class="text-none"><v-icon class="mr-2">mdi-cog</v-icon>
                            Setting</v-btn>
                    </template>

                    <v-card>
                        <v-toolbar dense style="position: fixed;z-index:999;">
                            <p><b class="ml-5 text-primary">Requestor Configuration Settings</b></p>
                            <v-spacer></v-spacer>
                            <v-btn icon="mdi-close" class="mr-2" variant="text"
                                @click="service_settings = false"></v-btn>
                            <v-btn text="Save" variant="flat" color="primary" :loading="loadingSubmit"
                                @click="assignSBU"></v-btn>
                        </v-toolbar>
                        <v-row class="mt-15 ma-4">
                            <v-col cols="12">
                                <v-row>
                                    <v-col cols="4">
                                        <v-text-field single-line density="compact" v-model="params.search" clearable
                                            variant="outlined" placeholder="Seach..."></v-text-field>
                                    </v-col>
                                    <v-col cols="2">
                                        <v-btn @click="handleRefresh" icon color="primary" size="small" variant="text"
                                            class="mr-1"><v-icon>mdi-refresh</v-icon></v-btn>
                                    </v-col>
                                    <v-col cols="6" class="d-flex justify-end align-center">
                                        <p class="small text-error">Don't forget to save after editing.</p>
                                    </v-col>
                                </v-row>
                                <vue3-datatable ref="datatable" :rows="rows" :columns="cols_service_dept"
                                    :loading="loading" :search="params.search" skin="bh-table-compact bh-table-bordered"
                                    cellClass="assignedRoleCellClass">

                                    <template #users.fullname="data">
                                        <p class="text-primary"><b>{{ data.value.users.fullname }}</b></p>
                                        <p class="small textDown">{{ data.value.users.position_name }}</p>
                                    </template>
                                    <template #sbu="data">
                                        <v-combobox color="primary" variant="plain" placeholder="Select SBU"
                                            density="compact" :items="pub_var.sbuArray"
                                            v-model="service_sbu[data.value.id]" clearable chips
                                            @update:modelValue="selectSBU(data.value.id, $event)"></v-combobox>

                                    </template>
                                </vue3-datatable>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-dialog>
            </v-col>

            <v-col cols="12" xl="6" md="6" sm="6" :style="{ 'margin-top': width <= 550 ? '-15px' : '' }">
                <!-- Delete -->
                <v-btn color="error" class="text-none" :disabled="btnDisabledDelete" variant="tonal"
                    @click="deleteUserRole = true">
                    <v-icon>mdi-delete-empty</v-icon> {{ width <= 768 ? '' : ' Delete' }} </v-btn>
                        <v-dialog v-model="deleteUserRole" max-width="340">
                            <template v-slot:default="{ isActive }">
                                <v-card prepend-icon="mdi-trash-can-outline"
                                    text="Are you sure you want to delete assigned role?" title="Delete">
                                    <template v-slot:actions>
                                        <v-btn class="ml-auto text-none" variant="tonal" text="No"
                                            @click="deleteUserRole = false"></v-btn>
                                        <v-btn color="error" :loading="loadingDelete" variant="flat" class="text-none"
                                            text="Delete" @click="deleteRole"></v-btn>
                                    </template>
                                </v-card>
                            </template>
                        </v-dialog>

                        <v-btn @click="handleRefresh" icon color="primary" size="small" variant="text"
                            class="mr-1"><v-icon>mdi-refresh</v-icon></v-btn>
            </v-col>
            <v-col cols="12" xl="3" md="3" sm="3" :style="{ 'margin-top': width <= 550 ? '-15px' : '' }">
                <v-select color="primary" v-model="role" :items="get_role_name" clearable item-title="role_name"
                    item-value="role_id" density="compact" variant="outlined" label="Filter by role"></v-select>
            </v-col>
            <v-col cols="12" xl="3" md="3" sm="3" :style="{ 'margin-top': width <= 550 ? '-15px' : '' }">
                <v-text-field color="primary" v-model="params.search" clearable density="compact"
                    label="Search all fields" variant="outlined"></v-text-field>
            </v-col>
        </v-row>
        <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
            :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :sortColumn="params.sort_column"
            :sortDirection="params.sort_direction" :hide="true" :filter="true" skin="bh-table-compact bh-table-bordered"
            @rowSelect="rowSelect" cellClass="assignedRoleCellClass" :rowClass="getRowClass">

            <template #users.fullname="data">
                <p class="text-primary"><b>{{ data.value.users.fullname }}</b></p>
                <p class="small textDown">{{ data.value.users.position_name }}</p>
            </template>
        </vue3-datatable>
    </v-col>
</template>
<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { user_data } from '@/stores/auth/userData'
import * as pub_var from '@/global/global'
import { apiRequestAxios } from '@/api/api';
import { pullout_approver_designation } from '@/global/pullout';
import { satellite } from '@/global/satellite';

/** All users */
import { all_users } from '@/helpers/getUsers';
const { users } = all_users()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
import { assign, forEach } from 'lodash';
const toast = useToast()

const user = user_data()

const apiRequest = apiRequestAxios()
const datatable = ref(null)
const deleteUserRole = ref(false)
const loadingDelete = ref(false)
const btnDisabledDelete = ref(true)
const rowId = ref([])
const role = ref(null)


/** Data Approver Designation */
const loadingSubmit = ref(false);
const form = ref(false)



/** Requestor Settings Details ======================================================== */
const settings = ref(false)
const requestor_settings = ref(false)
const requestor_supervisor = ref({})
const selectedSupervisor = ref([])
const selectSupervisor = (id, selected) => {
    const selectedID = !selected ? null : selected.id
    const existToUpdate = selectedSupervisor.value.find((data) => data.id === id)
    if (existToUpdate) {
        existToUpdate.supervisor_id = selectedID
    } else {
        selectedSupervisor.value.push({
            id: id,
            supervisor_id: selectedID
        })
    }
}

const assignSupervisor = async () => {
    loadingSubmit.value = true
    if (selectedSupervisor.value.length === 0) {
        toast.info('No changes made')
        loadingSubmit.value = false
        return
    }
    try {
        const response = await apiRequest.post('assign_supervisor', {
            supervisor: selectedSupervisor.value
        })
        if (response.data.success) {
            toast.success('Successfully updated')
            selectedSupervisor.value = []
            getAssignedUserRole(role.value)
        } else console.log(response.data?.error)
    } catch (error) {
        selectedSupervisor.value = []
        getAssignedUserRole(role.value)
        console.log(error)
    }
    finally {
        loadingSubmit.value = false
    }
}



/** Service Dept Settings Details ======================================================== */
const service_settings = ref(false)
const service_dept_ids = [pub_var.TLRoleID, pub_var.engineerRoleID, pub_var.SBUServiceAssistantID]
const showServiceDeptSetting = computed(() => {
    return service_dept_ids.includes(role.value)
})
const service_sbu = ref({})
const selectedSBUs = ref([])
const selectSBU = (id, selected) => {
    const selectedSBU = !selected ? null : selected
    const existToUpdate = selectedSBUs.value.find((data) => data.id === id)
    if (existToUpdate) {
        existToUpdate.sbu = selectedSBU
    } else {
        selectedSBUs.value.push({
            id: id,
            sbu: selectedSBU
        })
    }
    console.log(selectedSBUs.value)
    console.log(selected)
}

const assignSBU = async () => {
    loadingSubmit.value = true
    if (selectedSBUs.value.length === 0) {
        toast.info('No changes made')
        loadingSubmit.value = false
        return
    }
    try {
        const response = await apiRequest.post('assign_sbu', {
            sbu: selectedSBUs.value
        })
        if (response.data.success) {
            toast.success('Successfully updated')
            selectedSBUs.value = []
            getAssignedUserRole(role.value)
        } else console.log(response.data?.error)
    } catch (error) {
        selectedSBUs.value = []
        getAssignedUserRole(role.value)
        console.log(error)
    }
    finally {
        loadingSubmit.value = false
    }
}


/** Setting of Approval Level Configuration - Approver Settings ======================================== */
const selectedLevels = ref({})
const updateEHApproverLevel = (id, user_id, roleID, field, selected) => {
    const currentLevel = selectedLevels.value[id] || {
        user_id: user_id,
        roleID: roleID,
        role_user_id: id,
        eh: [],
        pullout: [],
        sbu: '',
        satellite: '',
    }
    if (field === 'sbu' || field === 'satellite') {
        currentLevel[field] = selected
    } else {
        currentLevel[field] = selected.map(v => ({
            level: v.value,
            level_name: v.title
        }))
    }
    selectedLevels.value[id] = { ...currentLevel }
}

const saveChanges = async () => {
    const changesToSend = Object.values(selectedLevels.value).map((v) => ({
        role_user_id: v.role_user_id,
        user_id: v.user_id,
        roleID: v.roleID,
        eh_level: v.eh,
        pullout_level: v.pullout,
        sbu: v.sbu,
        satellite: v.satellite,
        approver_category: 1
    }))
    loadingSubmit.value = true
    try {
        const response = await apiRequest.post('update_approver_config', {
            update_approval: changesToSend
        })
        if (response.data.success) {
            toast.success('Successfully updated')
            selectedLevels.value = {}
            getApproverDetails()
        } else console.log(response.data?.error)
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingSubmit.value = false
    }
};





/** RowClick Table Fucntion */
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows();
    if (row && row.length > 0) {
        btnDisabledDelete.value = false
    } else {
        btnDisabledDelete.value = true
    }

    rowId.value = row.map(v => v.id)[0]
}
const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}


/** Approver designation with 'Level' */
const approver_designation = computed(() => {
    return pub_var.approver_designation.map(v => {
        return { title: 'Level : ' + v.key, value: v.value }
    })
})
const pullout_designation = computed(() => {
    return pullout_approver_designation.map(v => {
        return { title: 'Level : ' + v.key, value: v.value }
    })
})


/** Delete Roles */
const deleteRole = async () => {
    loadingDelete.value = true
    try {
        const response = await apiRequest.delete('delete-role',
            {
                params: { id: rowId.value }
            }
        )
        if (response.data && response.data.success) {
            toast.success('Successfully deleted')
            deleteUserRole.value = false
            getAssignedUserRole(role.value)

        } else {
            console.log(response.data.error)
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingDelete.value = false
    }
}


/** Fetching Data Users */
const EHSelectedItems = ref([])
const PulloutSelectedItems = ref([])
const getPulloutSelectedItems = ref([])
const getEHSelectedItems = ref([])
const assigned_sbu = ref({})
const assigned_satellite = ref({})
const loading = ref(true);
const loadingApprover = ref(false);
const total_rows = ref(0);
/** Enable Filter */
const enableFilter = ref(false)


const params = reactive({
    current_page: 1,
    pagesize: 10,
    sort_column: 'roles.role_name',
    sort_direction: 'desc',
    search: ''
});
const rows = ref(null);
const approver_rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'users.fullname', title: 'Name' },
        { field: 'roles.role_name', title: 'Role' },
        { field: 'sbu', title: 'SBU', hide: true },
        { field: 'users.approval_id', title: 'approval id', hide: true },
        { field: 'users.name', title: 'Department' },
        { field: 'users.position_name', title: 'Position', hide: true },
    ]) || [];

const approver_cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'user_id', title: 'Approver ID', isUnique: true, type: 'number', hide: true },
        { field: 'users.fullname', title: 'Approver' },
        { field: 'users.name', title: 'Department' },
        { field: 'eh', title: 'Equipment Handling Module', hide: false },
        { field: 'pullout', title: 'Pullout Module', hide: false },
        { field: 'sbu', title: 'SBU', hide: false, minWidth: '200px' },
        { field: 'satellite', title: 'Satellite', hide: false, minWidth: '200px' },
    ]) || [];

const cols_requestor =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'user_id', title: 'Approver ID', isUnique: true, type: 'number', hide: true },
        { field: 'users.fullname', title: 'Approver' },
        { field: 'users.name', title: 'Department' },
        { field: 'supervisor', title: 'Immediate Supervisor', minWidth: '300px', maxWidth: '300px' },
    ]) || [];

const cols_service_dept =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'user_id', title: 'Approver ID', isUnique: true, type: 'number', hide: true },
        { field: 'users.fullname', title: 'Approver' },
        { field: 'roles.role_name', title: 'Role' },
        { field: 'users.name', title: 'Department' },
        { field: 'sbu', title: 'SBU', minWidth: '300px', maxWidth: '300px' },
    ]) || [];

const params_approver = reactive({
    current_page: 1,
    pagesize: 500,
    sort_column: 'users.department',
    sort_direction: 'desc',
    search: ''
});
const getAssignedUserRole = async (role_id) => {
    try {
        loading.value = true;
        const response = await apiRequest.get('assigned-user-role', {
            params: { role: role_id }
        });
        const data = response.data?.users_role

        rows.value = data
        total_rows.value = data?.meta?.total;


        //Immediate Supervisor
        const supervisor_data = data.filter(f => f.role_id === pub_var.requestorID)
            .map(v => (
                {
                    id: v.id,
                    supervisor_id: v.supervisor_id,
                }
            ))
        supervisor_data.forEach(data => {
            requestor_supervisor.value[data.id] = data.supervisor_id
        })


        //SBU
        const get_sbu = data.filter(f => service_dept_ids.includes(f.role_id))
            .map(m => ({
                id: m.id,
                sbu: m.sbu,
            }))
            get_sbu.forEach(data => {
                service_sbu.value[data.id] = data.sbu
            })

    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

/** Specific Details for Approver Details - loads when settings clicks */
const getApproverDetails = async () => {
    try {
        loadingApprover.value = true;
        const response = await apiRequest.get('assigned-user-role', {
            params: { role: pub_var.approverRoleID }
        });
        const data = response.data?.users_role

        approver_rows.value = data

        data.forEach(data => {
            const selected = data.approver.filter(f => f.approver_category === 1) // 1 = Equipment Handling
                .map((v) => ({
                    level: v.approval_level,
                    level_name: v.approval_level_name
                }))
            EHSelectedItems.value[data.id] = selected
            getEHSelectedItems.value[data.id] = selected.map(v => v.level)
        })

        data.forEach(data => {
            const selectedData = data.approver.filter(f => f.approver_category === 2) // 2 = Pullout
                .map((v) => ({
                    level: v.approval_level,
                    level_name: v.approval_level_name
                }))
            PulloutSelectedItems.value[data.id] = selectedData
            getPulloutSelectedItems.value[data.id] = selectedData.map(v => v.level)
        })

        data.forEach(data => {
            assigned_sbu.value[data.id] = data.sbu
            assigned_satellite.value[data.id] = data.satellite
        })

        data.filter(f => f.role_id === pub_var.approverRoleID).forEach(data => {
            selectedLevels.value[data.id] = {
                role_user_id: data.id,
                user_id: data.user_id,
                roleID: data.role_id,
                eh: EHSelectedItems.value[data.id],
                pullout: PulloutSelectedItems.value[data.id],
                sbu: data.sbu,
                satellite: data.satellite,
            }
        })

    } catch (error) {
        console.log(error)
    }

    loadingApprover.value = false;
};

/** Dynamically Hide */
const updateColVisibility = (approverCol, TLCol) => {
    cols.value.forEach(col => {
        if (col.title === 'Equipment Handling' || col.title === 'Pullout') {
            col.hide = !approverCol; // Set hide to false if visible, true if not
        }
        if (col.title === 'SBU') {
            col.hide = !TLCol
        }
    });
}
watch(role, (val) => {
    getAssignedUserRole(val)
    if (val === 1) updateColVisibility(true, false)
    else if (val === 2 || val === 3 || val === 8) updateColVisibility(false, true)
    else updateColVisibility(false, false)
})


/** Get Roles [Role Name] */
const get_role_name = ref([])
const getRoleName = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_roles');
        const data = response.data.role_name

        get_role_name.value = data.map(v => ({ role_id: v.roleID, role_name: v.role_name }))

    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



/** Handle Refresh */
const handleRefresh = () => {
    getAssignedUserRole(role.value)
    getRoleName()
}
const handleRefreshApprover = () => {
    getApproverDetails()
    getRoleName()
}




onMounted(() => {
    getAssignedUserRole()
    getRoleName()
});
</script>


<style scoped>
.textDown {
    overflow: hidden;
    text-overflow: ellipsis !important;
    max-width: 300px;
}

.hideChip {
    display: none;
}

.comboBox {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.truncate-selected-item {
    display: inline-block;
    max-width: 100px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    vertical-align: middle;
}
</style>