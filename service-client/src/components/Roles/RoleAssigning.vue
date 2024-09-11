<template>
    <v-row>
        <v-col cols="12">
            <v-row>
                <v-col lg="8" md="8" sm="6" cols="3">
                    <v-dialog width="400" scrollable v-model="isActive">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" :disabled="btnDisable" variant="tonal"
                                class="text-none" v-bind="activatorProps">
                                <v-icon class="mr-2">mdi-lock-plus</v-icon> {{ width <= 500 ?  '' : ' Assign Role' }}
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-account-search" title="Select Role">

                                <v-form @submit.prevent="saveRole" ref="form">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-select color="primary" v-model="selectedRole" :rules="rule.role" @click="refreshRoles"
                                            :items="get_role_name" item-title="role_name" item-value="roleID"  density="compact" variant="outlined"
                                            label="Roles" hint="Assign roles to the selected users"
                                            persistent-hint></v-select>
                                        <v-select v-if="showSSU" color="primary" v-model="SSU" :rules="rule.SSU"
                                            :items="convertSSUToArray(pub_var.ssu)" item-title="text" item-value="value"
                                            density="compact" variant="outlined" label="SSU" class="mt-5"></v-select>
                                    </v-card-text>

                                    <v-divider></v-divider>

                                    <v-card-actions>
                                        <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                            @click="isActive.value = false"></v-btn>
                                        <v-spacer></v-spacer>

                                        <v-btn type="submit" color="primary"
                                            prependIcon="mdi-content-save-check-outline" class="text-none" text="Save"
                                            variant="flat" :loading="loadingSave"></v-btn>
                                    </v-card-actions>
                                </v-form>
                            </v-card>
                        </template>
                    </v-dialog>
                </v-col>
                <v-spacer></v-spacer>
                <v-col lg="4" md="4" sm="6" cols="9">
                    <v-text-field color="primary" v-model="params.search" clearable density="compact"
                        label="Search all fields" variant="outlined"></v-text-field>
                </v-col>
            </v-row>
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :hide="true" :filter="true"
                skin="bh-table-compact bh-table-bordered" class="mt-4"
                @rowSelect="isChecked">

                <template #first_name="data">
                    <span>{{ data.value.first_name }} {{ data.value.last_name }}</span>
                </template>
            </vue3-datatable>
        </v-col>

        <v-snackbar color="error" v-model="snackbarErrorGeneral" location="right bottom" :timeout="3000">
            <v-icon class="ml-3">mdi-account-alert</v-icon> Please select one option
        </v-snackbar>
        <v-snackbar color="#19197000" variant="outlined" v-model="userExistDisplay" location="right bottom"
            :timeout="3000">
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
    </v-row>

</template>
<script setup>
import { ref, reactive, onMounted, watch, inject } from 'vue';
import { user_data } from '@/stores/auth/userData'
import * as pub_var from '@/global/global.js'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import {useDisplay} from 'vuetify'
const { width } = useDisplay()

const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()
const form = ref(false)
const selectedRole = ref(null)
const selectedRoleText = ref(null)
const SSU = ref(null)
const showSSU = ref(false)
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

const rule = ref({
    role: [
        v => !!v || 'Required'
    ],
    SSU: [
        v => !!v || 'Required'
    ]
})

const convertSSUToArray = (obj) => {
    return Object.keys(obj).map(data => ({ text: 'SSU' + obj[data], value: 'SSU' + obj[data] }))
}
watch(selectedRole, (val) => {
    selectedRoleText.value = get_role_name.value.find(v => v.roleID === val)
    if (selectedRoleText.value?.role_name === pub_var.engineerRole || selectedRoleText.value?.role_name === pub_var.TLRole) {
        showSSU.value = true
    } else {
        showSSU.value = false
        SSU.value = ''
    }
})

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
    loadingSave.value = true
    const selectedUser = Array.from(datatable.value.getSelectedRows())
    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSave.value = false
        return
    }
    try {
        const response = await apiRequest.post('assign-role', {
            user: selectedUser,
            role_id: selectedRole.value,
            // role_name: selectedRoleText.value.role_name,
            ssu: SSU.value
        })
        if (response.data && response.data.success) {
            userExist.value = response.data.userExist
            userSucceed.value = response.data.succeed
            userExistDisplay.value = true
            form.value.reset()
            // success.value= true
            datatable.value.clearSelectedRows()
            setTimeout(() => {
                isActive.value = false
                refreshKey.value += 1
            }, 500)
        } else {
            console.log(response.data.error)
        }
    } catch (error) {
        console.error('API Request Error:', error);
    } finally {
        loadingSave.value = false
    }
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
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'first_name', title: 'Name' },
        { field: 'email', title: 'Email' },
        { field: 'name', title: 'Department' },
    ]) || [];


/** Get All Users */
const getUsers = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('users')
        const data = response.data.users

        rows.value = data
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


/** Get Roles */
const get_role_name = ref([])
const getRoleName = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_role_name');
        const data = response.data.role_name

        get_role_name.value = data.map(v => ({ roleID : v.roleID, role_name : v.role_name}))
        
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

const refreshRoles = () => {
    getRoleName()
}


onMounted(() => {
    getUsers();
    getRoleName()
});
</script>



<style scoped>
.v-snackbar__content {
    font-weight: 100 !important;
    letter-spacing: 0 !important;
    padding: 0 !important;
}

.fixed-toolbar-roles {
    position: fixed;
    z-index: 100;
    right: 0;
}
</style>