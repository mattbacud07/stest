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
                <span class="mt-3">
                    <v-icon color="grey">mdi-text-box-search-outline</v-icon> <span class="text-grey small">No records
                        found</span>
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
            <p class="mb-3"><b class="text-grey">Remarks</b></p>
            <p v-if="task_delegation?.remarks">{{ task_delegation?.remarks }}</p>
            <p v-else>---</p>
        </v-col>
        <v-col cols="12">
            <div class="table-responsive sparepartsField">
                <p class="mb-3"><b class="text-grey">Reagents / Spare Parts Used</b></p>
                <table class="table table-sm table-bordered table-responsive">
                    <thead style="background: #afafaf2e;">
                        <tr>
                            <th scope="col">Item Code</th>
                            <th scope="col">Item Description</th>
                            <th>Qty</th>
                            <th>DR#</th>
                            <th>SI#</th>
                            <th>Remarks</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody v-if="spareparts.length > 0">
                        <tr v-for="(item, index) in spareparts" :key="item.id">
                            <td>
                                {{ item.equipment.item_code }}
                            </td>
                            <td>{{ item.equipment.description }}</td>
                            <td>{{ item.qty }}</td>
                            <td>{{ item.dr }}</td>
                            <td>{{ item.si }}</td>
                            <td colspan="2">{{ item.remarks }}</td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="7" class="text-center p-3" style="opacity: .3;">
                                <v-icon class="mb-3"
                                    style="font-size: 50px">mdi-file-document-alert-outline</v-icon><br>
                                No records found
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </v-col>
    </v-row>
</template>
<script setup>
import { toRefs } from 'vue';

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
</script>