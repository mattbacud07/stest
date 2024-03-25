<template>
    <div>
        <v-card class="mx-auto p-4">
            <v-row>
                <v-col cols="4">
                    <v-text-field v-model="params.search" clearable density="compact" label="Search all fields"
                        variant="outlined"></v-text-field>
                </v-col>
                <v-col cols="4">
                    <v-checkbox v-model="enableFilter" color="primary" density="compact" label="Enable Filter"
                        value="primary"></v-checkbox>
                </v-col>
                <v-col cols="4" style="text-align: right;">

                    <router-link to="/work-order">
                        <v-btn type="button" color="primary" class="text-none">
                            <i class="fa fa-edit mr-3"></i>Submit Work Order
                        </v-btn>
                    </router-link>
                </v-col>
            </v-row>

            <vue3-datatable :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :columnFilter="enableFilter ?? false" :hasCheckbox="true" :selectRowOnClick="true">
                <template #approval_level_name="data">
                    <span style="font-weight: 600;color: #191970;">{{data.value.approval_level_name}}</span>
                </template>
            </vue3-datatable>
        </v-card>
    </div>
</template>
<script setup>
import { onMounted, ref, reactive } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import axios from 'axios'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

axios.defaults.withCredentials = true

/** Declaration of User Data */
const user = user_data();
// console.log(user.tokenData)

const uri = BASE_URL
// const config = useRuntimeConfig();

/** Enable Filter */
const enableFilter = ref(false)

const loading = ref(true);
const total_rows = ref(0);

const params = reactive({ current_page: 1, pagesize: 10 });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'Report Number', isUnique: true, type: 'number', hide : true, },
        // { field: 'first_name', title: 'Requested by' },
        { field: 'name', title: 'Institution' },
        { field: 'address', title: 'Address' , hide: true},
        { field: 'request_name', title: 'External Request' },
        { field: 'internal_name', title: 'Internal Request' },
        { field: 'proposed_delivery_date', title: 'Proposed Delivery Date', type: 'date' },
        { field: 'approval_level_name', title: 'Pending Approval', minWidth : '300px' },
        // { field: 'isActive', title: 'Active', type: 'bool' },
    ]) || [];

const getUsers = async () => {
    try {
        loading.value = true;
        const response = await axios.get(uri + 'api/get-equipment-handling-data', {
            params: { user_id: user.user.id },
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.dataItems

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


<style scoped>
a {
    color: aliceblue;
}
</style>