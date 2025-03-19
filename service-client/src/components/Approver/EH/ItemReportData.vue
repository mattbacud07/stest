<template>
    <v-row>
        <v-col cols="12">
            <v-card v-if="items_acquired?.length > 0" class="mt-5">
                <vue3-datatable :rows="items_acquired" :columns="cols" :pagination="false"
                    skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover">
                </vue3-datatable>
            </v-card>
            <v-card v-else>
                <span class="mt-3">
                    <v-icon color="grey">mdi-text-box-search-outline</v-icon> <span class="text-grey">No
                        records
                        found</span>
                </span>
            </v-card>
        </v-col>
    </v-row>
</template>
<script setup>
import { ref, toRefs } from 'vue';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'item', title: 'Item' },
        { field: 'qty', title: 'Qty' },
        { field: 'remarks', title: 'Remarks' },
    ]) || [];

const props = defineProps({
    task_delegation: {
        type: Object,
        default: () => ({})
    },
    items_acquired: {
        type: Array,
        default: () => ([])
    }
})

const { task_delegation, items_acquired } = toRefs(props)
</script>