<template>

    <BaseLayout>
        <v-card class="pa-5">
            <v-row class="mb-3">
                <v-col cols="12" md="4" sm="4">
                    <v-text-field v-model="params.search" label="Search" density="compact" single-line
                        variant="outlined" clearable></v-text-field>
                </v-col>
                <v-col cols="12" md="8" sm="8">
                    <v-btn dense variant="plain" @click="refresh"><v-icon>mdi-refresh</v-icon>
                        <v-tooltip activator="parent" location="bottom">Refresh Table</v-tooltip>
                    </v-btn>
                </v-col>
            </v-row>
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" @change="changeServer"
                :columnFilter="false" :sortColumn="params.sort_column" :sortDirection="params.sort_direction"
                :sortable="true" style="border: 1px solid #99999926;border-radius: 3px;"
                skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" :hasCheckbox="true"
                :selectRowOnClick="true" cellClass="internalCell">
                <template #changes="data">
                   <div>
                    <v-dialog max-width="700">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" dense variant="plain">
                                <v-icon>mdi-eye-arrow-left-outline</v-icon>
                                <v-tooltip activator="parent" location="bottom">Click to View Original Data</v-tooltip>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card class="pa-5">
                                <template v-slot:default>
                                    <pre>{{ JSON.stringify(JSON.parse(data.value.changes), null, 2) }}</pre>
                                </template>
                                <template v-slot:actions>
                                    <v-btn class="ml-auto text-none" text="Close" @click="isActive.value = false"></v-btn>
                                </template>
                            </v-card>
                        </template>
                    </v-dialog>
                   </div>
                </template>
                <template #original="data">
                   <div v-if="data.value.original !== null">
                    <v-dialog max-width="700">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn v-bind="activatorProps" dense variant="plain">
                                <v-icon>mdi-eye-arrow-left-outline</v-icon>
                                <v-tooltip activator="parent" location="bottom">Click to View Changes</v-tooltip>
                            </v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card class="pa-5">
                                <template v-slot:default>
                                    <pre>{{ JSON.stringify(JSON.parse(data.value.original), null, 2) }}</pre>
                                </template>
                                <template v-slot:actions>
                                    <v-btn class="ml-auto text-none" text="Close" @click="isActive.value = false"></v-btn>
                                </template>
                            </v-card>
                        </template>
                    </v-dialog>
                   </div>
                </template>
                <template #created_at="data">
                    <span>{{ FullMonthWithTime(data.value.created_at) }}</span>

                </template>
            </vue3-datatable>
        </v-card>
    </BaseLayout>
</template>

<script setup>
import { onMounted, ref, reactive, provide } from 'vue';
import { apiRequestAxios } from '@/api/api';
import debounce from 'lodash/debounce'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';
import { FullMonthWithTime } from '@/global/global';

const apiRequest = apiRequestAxios()


const datatable = ref(null)
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'id', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: false },
        { field: 'model', title: 'Model', hide: false },
        { field: 'model_name', title: 'Module Name', hide: false },
        { field: 'model_id', title: 'Records ID', hide: false },
        { field: 'action', title: 'Action', hide: false },
        { field: 'changes', title: 'Changes', hide: false },
        { field: 'original', title: 'Original', hide: false },
        { field: 'user', title: 'User', hide: false },
        { field: 'created_at', title: 'Created At', hide: false },
    ]) || [];

const getLogs = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_logs', {
            params: { ...params, }
        });
        if (response.data?.logs) {
            const result = response.data.logs
            rows.value = result.data
            total_rows.value = result.total
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};


const refresh = () => {
    getLogs()
}



// ChangeServer for Servre Mode
const debounceSearch = debounce(getLogs, 300)
const changeServer = (data) => {
    params.current_page = data.current_page
    params.pagesize = data.pagesize
    params.sort_column = data.sort_column
    params.sort_direction = data.sort_direction

    if (data.change_type === 'search') {
        debounceSearch()
    } else getLogs()
}



onMounted(() => {
    getLogs()
})
</script>