<template>
    <v-row>
        <v-col cols="12">
            <p class="mb-3"><b class="text-grey">Actions Taken</b></p>
            <p v-for="action in actions_taken" :key="action" v-if="actions_taken?.length > 0">
                <span class="mt-3">
                    <v-icon color="primary">mdi-check</v-icon> {{ action.action }}
                </span>
            </p>
            <p v-else>
                <span class="mt-3">---
                    <!-- <v-icon color="grey">mdi-text-box-search-outline</v-icon> <span class="text-grey small">No records
                        found</span> -->
                </span>
            </p>
        </v-col>
        <v-col cols="12">
            <p class="mb-3"><b class="text-grey">Status after service</b></p>
            <v-chip label density="comfortable" v-if="task_delegation?.status_after_service" color="primary"
                variant="tonal">
                {{ task_delegation?.status_after_service }}
            </v-chip>
            <p v-else>---</p>
        </v-col>
        <v-col cols="12">
           <v-row>
            <v-col cols="12" md="6" lg="6" sm="6">
                <p class="mb-3"><b class="text-grey">Customer Complaint</b></p>
                <p v-if="task_delegation?.complaint" class="textHTML">{{ task_delegation?.complaint }}</p>
            <p v-else>---</p>
            </v-col>
            <v-col cols="12" md="6" lg="6" sm="6">
                <p class="mb-3"><b class="text-grey">Cause of Problem</b></p>
                <p v-if="task_delegation?.problem" class="textHTML">{{ task_delegation?.problem }}</p>
            <p v-else>---</p>
            </v-col>
           </v-row>
        </v-col>
        <v-col cols="12">
            <p class="mb-3"><b class="text-grey">Remarks</b></p>
            <p v-if="task_delegation?.sr_remarks" class="textHTML">{{ task_delegation?.sr_remarks }}</p>
            <p v-else>---</p>
        </v-col>
        <v-col cols="12">
            <p class="mb-3"><b class="text-grey">Reagents / Spare Parts Used</b></p>
            <v-card v-if="spareparts?.length > 0" class="mt-5">
                <vue3-datatable :rows="spareparts" :columns="cols" :pagination="false"
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
import {ref, toRefs } from 'vue';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const props = defineProps({
    task_delegation : {
        type: Object,
        default: () => ({})
    },
    actions_taken : {
        type: Array,
        default: () => ([])
    },
    spareparts : {
        type: Array,
        default: () => ([])
    }
})

const { task_delegation, actions_taken, spareparts } = toRefs(props)

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'equipment.item_code', title: 'Item Code' },
        { field: 'equipment.description', title: 'Item Description' },
        { field: 'qty', title: 'Qty' },
        { field: 'dr', title: 'DR no.' },
        { field: 'si', title: 'SI no.' },
        { field: 'remarks', title: 'Remarks' },
    ]) || [];
</script>