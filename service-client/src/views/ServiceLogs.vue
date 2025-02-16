<template>
    <LayoutWithActions @searchText="getSearchText">
        <template #default>
            <!-- <v-card class="mx-auto p-4"> -->
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" @change="changeServer"
                @rowSelect="rowSelect" :columnFilter="false" :sortColumn="params.sort_column"
                :sortDirection="params.sort_direction" :sortable="true"
                style="border: 1px solid #99999926;border-radius: 3px;"
                skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" :hasCheckbox="true"
                :selectRowOnClick="true" :rowClass="getRowClass">
            </vue3-datatable>
            <!-- </v-card> -->
        </template>
    </LayoutWithActions>
</template>

<script setup>
import { onMounted, ref, reactive, provide, watch, computed } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { getRole } from '@/stores/getRole'
import { useRouter } from 'vue-router';
import { apiRequestAxios } from '@/api/api';
import debounce from 'lodash/debounce'
import { useDisplay } from 'vuetify';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import LayoutWithActions from '@/components/layout/MainLayout/LayoutWithActions.vue'


/** Toast PLugin */
import { useToast } from 'vue-toast-notification';
const toast = useToast()


const apiRequest = apiRequestAxios()


/**
 * @ Row Select Table Event
 */
const selectedRows = ref([])
const rowSelect = (row) => {
    selectedRows.value = datatable.value.getSelectedRows()
    if (row.length === 1) btnDisable.value = false
    else btnDisable.value = true

    selectedId.value = row.map(v => v.id)[0]
    // multiSelected.value = row.map(v => v.id)
}
const getRowClass = (row) => {
    const rowID = selectedRows.value.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}



/** Get specific PM  */
const getSearchText = (data) => {
    params.search = data
}
const datatable = ref(null)
const loading = ref(true);
const total_rows = ref(0);
const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'id', sort_direction: 'desc', search: '' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: false },
        { field: 'name', title: 'Institution', hide: false },
        { field: 'master_data_id', title: 'Item No', hide: false },
        { field: 'item_code', title: 'Item Code' },
        { field: 'serial', title: 'Serial No.' },
        { field: 'equipment', title: 'Equipment' },
        { field: 'description', title: 'Description' },
        { field: 'sbu', title: 'SBU' },
    ]) || [];


const colField = cols.value.reduce((acc, v) => {
    if (v.title && v.field) {
        acc[v.title] = v.field
    }
    return acc
}, {})

provide('column', cols)

const getLogs = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_logs', {
            params: { ...params, }
        });
        if (response.data?.md_data) {
            const result = response.data.md_data
            rows.value = result.data
            total_rows.value = result.total
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



provide('refresh', getLogs)



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