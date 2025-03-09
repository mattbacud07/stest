<template>
    <v-row>
        <v-col cols="12">
            <v-card v-if="items_acquired?.length > 0" class="mt-5">
                <!-- <v-row class="mb-3">
                <v-col cols="12">
                    <p class="mb-2"><b class="text-grey ml-1">Equipment Management
                            Actions</b></p>
                    <v-chip label density="comfortable"
                        v-if="task_delegation?.option_type" color="primary"
                        variant="flat">
                        For {{ task_delegation?.option_type }}
                    </v-chip>
                    <p v-else>---</p>
                </v-col>
            </v-row> -->

                <!-- Checklist Item Form -->
                <b class="text-grey ml-1">Checklist Items</b>
                <table class="table table-sm table-bordered mt-3">
                    <thead>
                        <tr class="text-none">
                            <td>Item</td>
                            <td>Qty</td>
                            <td>Remark</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(item, index) in items_acquired" :key="index">
                            <td>{{ item.item }}</td>
                            <td>{{ item.qty }}</td>
                            <td>{{ item.remarks }}</td>
                        </tr>
                    </tbody>
                </table>
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
import { toRefs } from 'vue';

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