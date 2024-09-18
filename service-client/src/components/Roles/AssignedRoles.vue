<template>
    <v-col cols="12">
        <v-row>
            <v-col cols="12" xl="6" md="6" sm="6" :style="{ 'margin-top': width <= 550 ? '-15px' : '' }">

                <!-- Set Designation of Approver -->
                <v-btn v-if="role === 1" color="warning" :disabled="btnDisableApproverButton" class="text-none mr-2"
                    variant="tonal" @click="designateApprover = true">
                    <v-icon>mdi-account-arrow-right</v-icon> {{ width <= 768 ? '' : ' Approver Designation' }} </v-btn>
                        <v-dialog v-model="designateApprover" max-width="400">
                            <template v-slot:default="{ isActive }">
                                <v-card prepend-icon="mdi-account-arrow-right" title="Set Approver Designation">
                                    <!-- Start of Designation -->
                                    <v-card-text class="pt-3">
                                        <v-form ref="form">
                                            <v-combobox color="primary" v-model="selectedDesignation"
                                                :rules="rule.designation" clearable label="Designation"
                                                density="compact" :items="pub_var.approver_designation"
                                                variant="outlined" itemValue="value" itemTitle="key" class="mt-5">
                                            </v-combobox>
                                            <v-select v-model="approver_category" :items="pub_var.approver_category"
                                                item-title="value" item-value="value" clearable
                                                label="Approver Category" color="primary" variant="outlined"
                                                density="compact" class="mt-5" :rules="rule.approver_category">
                                            </v-select>
                                            <v-row class="mt-4">
                                                <v-col cols="8">
                                                    <v-text-field variant="outlined" placeholder="Name"
                                                        density="compact" :rules="rule.name" disabled v-model="userName"
                                                        color="primary" label="User Name"></v-text-field>
                                                </v-col>
                                                <v-col cols="4">
                                                    <v-text-field variant="outlined" placeholder="User ID"
                                                        density="compact" :rules="rule.id" disabled v-model="userId"
                                                        color="primary" label="User ID"></v-text-field>
                                                </v-col>
                                            </v-row>
                                        </v-form>
                                    </v-card-text>
                                    <template v-slot:actions>
                                        <v-btn class="ml-auto text-none" variant="tonal" text="Close"
                                            @click="designateApprover = false"></v-btn>
                                        <v-btn @click="submitApprover" color="primary" :loading="loadingSave"
                                            variant="flat" class="text-none" text="Save"></v-btn>
                                    </template>
                                </v-card>
                            </template>
                        </v-dialog>

                        <v-btn color="primary" class="text-none mr-2" variant="tonal" :disabled="btnDisabledUpdate">
                            <v-icon>mdi-file-document-edit</v-icon> {{ width <= 768 ? '' : ' Edit' }} </v-btn>

                                <!-- Delete -->
                                <v-btn color="error" class="text-none" :disabled="btnDisabledDelete" variant="tonal"
                                    @click="deleteUserRole = true">
                                    <v-icon>mdi-delete-empty</v-icon> {{ width <= 768 ? '' : ' Delete' }} </v-btn>
                                        <v-dialog v-model="deleteUserRole" max-width="340">
                                            <template v-slot:default="{ isActive }">
                                                <v-card prepend-icon="mdi-trash-can-outline"
                                                    text="Are you sure you want to delete assigned role?"
                                                    title="Delete">
                                                    <template v-slot:actions>
                                                        <v-btn class="ml-auto text-none" variant="tonal" text="No"
                                                            @click="deleteUserRole = false"></v-btn>
                                                        <v-btn color="error" :loading="loadingDelete" variant="flat"
                                                            class="text-none" text="Delete" @click="deleteRole"></v-btn>
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
            @rowSelect="rowSelect"
            cellClass="assignedRoleCellClass"
            :rowClass="getRowClass">
            
            <template #users.fullname="data">
                <p class="text-primary"><b>{{ data.value.users.fullname }}</b></p>
                <p class="small textDown">{{ data.value.users.position_name }}</p>
            </template>
            <template #users.first_name="data">
                <!-- Delete Designation -->
                <v-tooltip :text="'Delete designation of ' + data.value.users.fullname" location="top"
                    v-if="data.value.users.approval_level_name">
                    <template v-slot:activator="{ props }">
                        <!-- <v-btn >Hover Over Me</v-btn> -->
                        <v-btn v-bind="props" color="error" class="text-none" variant="tonal" density="compact" icon>
                            <v-icon>mdi-trash-can-outline</v-icon>

                            <v-dialog activator="parent" max-width="400">
                                <template v-slot:default="{ isActive }">
                                    <v-card prepend-icon="mdi-trash-can-outline"
                                        text="Are you sure you want to remove user designation?" title="Delete">
                                        <v-col class="ma-3">
                                            <p class="mb-2">{{ data.value.users.fullname }}</p>
                                            <p><b>Current designation: </b></p>
                                            <p>{{ data.value.users.approval_level_name }}</p>
                                        </v-col>
                                        <template v-slot:actions>
                                            <v-btn class="ml-auto text-none" variant="tonal" text="No"
                                                @click="isActive.value = false"></v-btn>
                                            <v-btn color="error"
                                                @click="removeDesignation(data.value.users.approval_id)" variant="tonal"
                                                class="text-none"><v-icon>mdi-trash-can-outline</v-icon>
                                                Remove</v-btn>
                                        </template>
                                    </v-card>
                                </template>
                            </v-dialog>
                        </v-btn>
                    </template>
                </v-tooltip>

                <!-- Update Designation -->
                <v-tooltip :text="'Update designation of ' + data.value.users.fullname" location="top"
                    v-if="data.value.users.approval_level_name">
                    <template v-slot:activator="{ props }">
                        <!-- <v-btn >Hover Over Me</v-btn> -->
                        <v-btn v-bind="props" color="primary" class="text-none ml-1" variant="tonal" density="compact" icon>
                            <v-icon>mdi-pencil-box-outline</v-icon>

                            <v-dialog activator="parent" max-width="400">
                                <template v-slot:default="{ isActive }">
                                    <v-card prepend-icon="mdi-pencil-box-outline" title="Update">
                                        <v-col class="pa-3">
                                            <v-form ref="form">
                                                <v-combobox color="primary" v-model="selectedDesignation"
                                                    :rules="rule.designation" clearable label="Designation"
                                                    density="compact" :items="pub_var.approver_designation"
                                                    variant="outlined" itemValue="value" itemTitle="key" class="mt-5">
                                                </v-combobox>
                                                <v-select v-model="approver_category" :items="pub_var.approver_category"
                                                    item-title="value" item-value="value" clearable
                                                    label="Approver Category" color="primary" variant="outlined"
                                                    density="compact" class="mt-5" :rules="rule.approver_category">
                                                </v-select>
                                                <v-row class="mt-4">
                                                    <v-col cols="8">
                                                        <v-text-field variant="outlined" placeholder="Name"
                                                            density="compact" :rules="rule.name" disabled
                                                            v-model="userName" color="primary"
                                                            label="User Name"></v-text-field>
                                                    </v-col>
                                                    <v-col cols="4">
                                                        <v-text-field variant="outlined" placeholder="User ID"
                                                            density="compact" :rules="rule.id" disabled v-model="userId"
                                                            color="primary" label="User ID"></v-text-field>
                                                    </v-col>
                                                </v-row>
                                            </v-form>
                                        </v-col>
                                        <template v-slot:actions>
                                            <v-btn class="ml-auto text-none" variant="tonal" text="No"
                                                @click="isActive.value = false"></v-btn>
                                            <v-btn color="primary"
                                                @click="updateDesignation(data.value.users.approval_id)" variant="tonal"
                                                class="text-none"><v-icon>mdi-pencil-box-outline</v-icon>
                                                Update</v-btn>
                                        </template>
                                    </v-card>
                                </template>
                            </v-dialog>
                        </v-btn>
                    </template>
                </v-tooltip>
            </template>
        </vue3-datatable>
    </v-col>
</template>
<script setup>
import { ref, reactive, onMounted, watch, toDisplayString } from 'vue';
import { user_data } from '@/stores/auth/userData'
import * as pub_var from '@/global/global'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

import { useToast } from 'vue-toast-notification';
const toast = useToast()

const user = user_data()
user.getUserData
import { apiRequestAxios } from '@/api/api';
const apiRequest = apiRequestAxios()
const datatable = ref(null)
const deleteUserRole = ref(false)
const deleteUserDesignation = ref(false)
const designateApprover = ref(false)
const btnDisable = ref(true)
const loadingSave = ref(false)
const loadingDelete = ref(false)
const btnDisableApproverButton = ref(true)
const btnDisabledUpdate = ref(true)
const btnDisabledDelete = ref(true)
const rowId = ref([])
const role = ref(null)


/** Data Approver Designation */
const userId = ref('')
const userName = ref('')
const loadingSubmit = ref(false);
const form = ref(false)
const selectedDesignation = ref('')
const approver_category = ref('')
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
    approver_category: [v => !!v || 'Required field'],
})




/** RowClick Table Fucntion */
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows();
    if (row.length > 1) {
        btnDisabledUpdate.value = true
    } else if (row && row.length > 0) {
        btnDisabledUpdate.value = false
        btnDisabledDelete.value = false
        btnDisableApproverButton.value = false
        userName.value = row[0].users.fullname
        userId.value = row[0].users.id
    } else {
        btnDisabledUpdate.value = true
        btnDisabledDelete.value = true
        btnDisableApproverButton.value = true
    }

    rowId.value = row.map(v => v.id)[0]
}
const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}


/** Assign Approver Designation */
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
            roleID: role.value,
            role_user_id: rowId.value,
            approver_level: selectedDesignation.value.value,
            approver_level_name: selectedDesignation.value.key,
            approver_category: approver_category.value,
        })
        if (response.data && response.data.success) {
            form.value.reset()
            getAssignedUserRole(role.value)
            toast.success('Designation assigned successfully')
        } else {
            form.value.reset()
            toast.error('Designation has already been set!')
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingSubmit.value = false
        designateApprover.value = false
    }
}

/** Remove Designation */
const removeDesignation = (approval_id) => {
    console.log(approval_id)
}



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
const loading = ref(true);
const total_rows = ref(0);
/** Enable Filter */
const enableFilter = ref(false)


const params = reactive({
    current_page: 1,
    pagesize: 10,
    sort_column: 'roles.role_name',
    sort_direction: 'desc'
});
const rows = ref(null);

const cols =
    ref([
        { field: 'users.first_name', title: 'Action', hide: true },
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'users.fullname', title: 'Name' },
        { field: 'roles.role_name', title: 'Role' },
        { field: 'SSU', title: 'SSU', hide: true },
        { field: 'users.approval_level_name', title: 'Approver Designation', hide: true },
        { field: 'users.approval_id', title: 'approval id', hide: true },
        // { field: 'users.approver_category', title: 'Approver Category', hide: true },
        { field: 'users.name', title: 'Department' },
        { field: 'users.position_name', title: 'Position', hide: true },
    ]) || [];

const getAssignedUserRole = async (role_id) => {
    try {
        loading.value = true;
        const response = await apiRequest.get('assigned-user-role', {
            params: { role: role_id }
        });
        const data = response.data.users_role

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

/** Dynamically Hide */
const updateColVisibility = (approverCol, TLCol) => {
    cols.value.forEach(col => {
        if (col.title === 'Approver Designation' || col.title === 'Approver Category' || col.title === 'Action') {
            col.hide = !approverCol; // Set hide to false if visible, true if not
        }
        if (col.title === 'SSU') {
            col.hide = !TLCol
        }
    });
}
watch(role, (val) => {
    getAssignedUserRole(val)
    if (val === 1) updateColVisibility(true, false)
    else if (val === 2 || val === 3) updateColVisibility(false, true)
    else updateColVisibility(false, false)
})


/** Get Roles [Role Name] */
const get_role_name = ref([])
const getRoleName = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_role_name');
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




onMounted(() => {
    getAssignedUserRole()
    getRoleName()
});
</script>


<style scoped>
.bh-table-responsive table.bh-table-compact tbody tr td,
.bh-table-responsive table.bh-table-compact thead tr th {
    padding: 0 7px !important;
}
.textDown{
    overflow: hidden;
    text-overflow: ellipsis!important;
    max-width: 300px;
}
</style>