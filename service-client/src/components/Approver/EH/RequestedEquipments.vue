<template>
    <!-- Equipment & Peripherals -->

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
                                    {{ equipment.general_master_data?.item_code }}
                                </td>
                                <td style="min-width: 250px;">{{ equipment.general_master_data?.description }}</td>
                                <td>{{ equipment.master_data.serial }}</td>
                                <td class="text-disabled">SBU {{ equipment.master_data?.sbu }}</td>
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

    <!-- Additional Peripherals ----- style="border: 1px dashed #191970;"-->
    <v-card class="mt-3 p-3 rounded-0" elevation="0" style="border-top: 1px dashed #191970;"
        v-if="peripherals.length > 0">

        <v-row>
            <h5 class="p-3 mt-2" style="font-weight: 500;color: #191970;">ADDITIONAL PERIPHERALS <span
                    style="font-size: .8em;font-weight: 400;color: #777;"><i><br v-if="width < 615">
                        (Serial number to be filled by the IT Department)</i></span></h5>
            <v-col cols="12">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered table-responsive">
                        <thead style="background: #afafaf2e;">
                            <tr>
                                <th scope="col">Item Code</th>
                                <th scope="col">Item Description</th>
                                <th scope="col">Serial Number</th>
                                <th scope="col">Remarks</th>
                            </tr>
                        </thead>
                        <tbody v-if="peripherals.length > 0">
                            <tr v-for="(peripheral, index) in peripherals" :key="index">
                                <td>
                                    {{ peripheral.general_master_data.item_code }}
                                </td>
                                <td style="min-width: 250px;">{{ peripheral.general_master_data?.description }}</td>
                                <td>
                                    <v-form ref="form" @prevent.submit="submitSerial"
                                        v-if="user.user.approval_level.includes(IT_DEPARTMENT) && editSerial === true">
                                        <v-text-field clearable density="compact" variant="plain"
                                            :rules="rulePeripheralSerial" placeholder="Serial number ...." w-100
                                            v-model="peripheral.serial_number"
                                            @input="setSerialNumber()"></v-text-field>
                                    </v-form>
                                    <span v-else>{{ peripheral.serial_number }}</span>
                                </td>
                                <td>{{ peripheral.remarks }}</td>
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

<script setup>
import { ref, toRefs } from 'vue';
import { useDisplay } from 'vuetify'
import { IT_DEPARTMENT } from '@/global/global';
import { user_data } from '@/stores/auth/userData';

const user = user_data()

const { width } = useDisplay()

const emit = defineEmits([
    'set-serial',
])

const submitSerial = () => {
    
}

const form = ref(null)
const rulePeripheralSerial = ref([
    v => !!v || 'Required field'
])
const peripheralSerial = ref(null)


const props = defineProps({
    equipments: {
        type: Array,
        default: () => ([])
    },
    peripherals: {
        type: Array,
        default: () => ([])
    },
    editSerial: {
        type: Boolean,
    },
})

const { equipments, editSerial, peripherals } = toRefs(props)



/** Get instance of parent to pass data from child to parent */
const setSerialNumber = () => {
    emit('set-serial', peripherals.value.map(v => ({ id: v.id, serial: v.serial_number })))
}
</script>