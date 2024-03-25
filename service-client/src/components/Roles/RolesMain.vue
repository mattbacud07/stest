<template>
    <v-container>
        <v-row>
            <v-col cols="5">
                <v-row>
                    <v-col cols="7">
                        <v-dialog width="400" scrollable v-model="isActive">
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn color="primary" :disabled="btnDisable" prepend-icon="mdi-account-search"
                                    text="Assign Role" class="text-none" v-bind="activatorProps"></v-btn>
                            </template>

                            <template v-slot:default="{ isActive }">
                                <v-card prepend-icon="mdi-account-search" title="Select Role">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-radio-group v-model="selectedRole"
                                            messages="Assign roles to the selected users">
                                            <v-radio color="primary" label="Approver" value="Approver"></v-radio>
                                            <v-radio color="primary" label="Engineers" value="Engineers"></v-radio>
                                        </v-radio-group>
                                    </v-card-text>

                                    <v-divider></v-divider>

                                    <v-card-actions>
                                        <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                            @click="isActive.value = false"></v-btn>
                                        <v-spacer></v-spacer>

                                        <v-btn color="primary" prependIcon="mdi-content-save-check-outline"
                                            class="text-none" text="Save" variant="flat" @click="saveRole"
                                            :loading="loadingSave"></v-btn>
                                    </v-card-actions>
                                </v-card>
                            </template>
                        </v-dialog>
                    </v-col>
                    <v-col cols="5">
                        <v-text-field color="primary" v-model="params.search" clearable density="compact"
                            label="Search all fields" variant="outlined"></v-text-field>
                    </v-col>
                </v-row>
                <p class="text-danger" style="font-size: .8em;">* Select at least one</p>
                <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                 :selectRowOnClick="true" :hasCheckbox="true" :sortable="true"
                    :hide="true" :filter="true" skin="bh-table-compact bh-table-bordered" paginationInfo="{0} to {1} of {2}" class="set_approver_table"
                    @rowSelect="isChecked">
                </vue3-datatable>
            </v-col>
            <v-col cols="7">
                <AssignedRoles  :key="refreshKey"/>
            </v-col>
        </v-row>
    </v-container>
    <v-snackbar color="error" v-model="snackbarErrorGeneral" location="right bottom" :timeout="3000">
        <v-icon class="ml-3">mdi-account-alert</v-icon> Please select one option
    </v-snackbar>
    <v-snackbar color="#19197000" variant="outlined" v-model="userExistDisplay" location="right bottom" :timeout="3000">
        <v-card v-if="userExist.length > 0" color="error" class="p-3">
            <div v-for="(userName, index) in userExist" :key="index" class="mt-4">
                <v-icon class="ml-3">mdi-account-tag</v-icon> {{ userName }} already has a role
            </div>
        </v-card>
        <v-card v-if="userSucceed.length > 0" color="success" class="p-3">
            <div v-for="(userNameSucceed, index) in userSucceed" :key="index" class="mt-4">
                <v-icon class="ml-3">mdi-check</v-icon> {{ userNameSucceed }} successfully assigned role
            </div>
        </v-card>
    </v-snackbar>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import axios from 'axios'
import AssignedRoles from './AssignedRoles.vue'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const uri = BASE_URL
const user = user_data()
user.getUserData
const selectedRole = ref(null)
const getUserId = ref([])
const datatable = ref(null)
const isActive = ref(false)
const btnDisable = ref(true)
const userExist = ref([])
const userSucceed = ref([])
const snackbarErrorGeneral = ref(false)
const userExistDisplay = ref(false)
const loadingSave = ref(false)
const refreshKey = ref(0)


/** Check - Selecting Users */
const isChecked = (data) => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length > 0) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }
}

/** Check - Selecting Users */
const saveRole = async () => {
    const selectedUser = Array.from(datatable.value.getSelectedRows())
    if (selectedRole.value !== null) {
        // console.log(selectedUser.value.id)
        loadingSave.value = true
        const response = await axios.post(
            uri + 'api/assign-role',
            {
                user: selectedUser,
                role_name: selectedRole.value
            },
            {
                headers: {
                    'Authorization': `Bearer ${user.tokenData}`
                }
            }
        )
        if (response.data && response.data.success) {
            userExist.value = response.data.userExist
            userSucceed.value = response.data.succeed
            userExistDisplay.value = true
            datatable.value.clearSelectedRows()
            setTimeout(()=>{
                isActive.value = false
                refreshKey.value += 1
            }, 500)
        }
    }
    else {
        snackbarErrorGeneral.value = true
    }
    loadingSave.value = false
}


















/** Fetching Data Users */
const loading = ref(true);
const total_rows = ref(0);
/** Enable Filter */
const enableFilter = ref(false)


const params = reactive({ current_page: 1, pagesize: 10 });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide:true },
        { field: 'user_name', title: 'Name' },
        { field: 'name', title: 'Position' },
    ]) || [];

const getUsers = async () => {
    try {
        loading.value = true;
        const response = await axios.get(uri + 'api/users', {
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.users

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
    // saveRole();
});
</script>



<style scoped>
.v-snackbar__content {
    font-weight: 100 !important;
    letter-spacing: 0 !important;
    padding: 0 !important;
}
</style>