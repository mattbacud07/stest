<template>
    <!-- Equipment & Peripherals -->
    <v-skeleton-loader v-if="loading" type="list-item-two-line"></v-skeleton-loader>
    <template v-else>
        <v-card class="mt-3 p-3" elevation="0">
            <v-row>
                <h6 class="p-3" style="font-weight: 700;color: #191970;">EQUIPMENT & PERIPHERALS</h6>
                <v-col cols="12">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead style="background: #afafaf2e;">
                                <tr>
                                    <th scope="col">Item Code</th>
                                    <th scope="col">Item Description</th>
                                    <th scope="col">Serial Number</th>
                                    <th scope="col">SBU</th>
                                    <th scope="col">Remarks</th>
                                </tr>
                            </thead>
                            <tbody v-if="equipments.length > 0">
                                <tr v-for="(equipment, index) in equipments" :key="index">
                                    <td>
                                        {{ equipment?.general_master_data?.item_code  ?? '---'}}
                                    </td>
                                    <td style="min-width: 250px;">{{ equipment?.general_master_data?.description ?? '---' }}</td>
                                    <td>{{ equipment?.master_data?.serial }}</td>
                                    <td class="text-disabled">SBU {{ equipment?.master_data?.sbu }}</td>
                                    <td>{{ equipment.remarks }}</td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="5" class="text-center p-3" style="opacity: .3;">
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
        </v-card>
    </template>

</template>

<script setup>
import { toRefs } from 'vue';

const props = defineProps({
    equipments: {
        type: Array,
        default: () => ([])
    }
})

const { equipments } = toRefs(props)
</script>