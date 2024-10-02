<template>
    <!-- Equipment & Peripherals -->
    <v-card class="mt-3 p-3" elevation="0" >
        <v-row>
            <h5 class="p-3" style="font-weight: 700;color: #191970;">EQUIPMENT & PERIPHERALS</h5>
            <v-col cols="12">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead style="background: #afafaf2e;">
                            <tr>
                                <th scope="col">Item Code</th>
                                <th scope="col">Item Description</th>
                                <th scope="col">Serial Number</th>
                                <th scope="col">Remarks</th>
                            </tr>
                        </thead>
                        <tbody v-if="equipments.length > 0">
                            <tr v-for="(equipment, index) in equipments" :key="index">
                                <td>
                                    {{ equipment.item_code }}
                                </td>
                                <td style="min-width: 250px;">{{ equipment.description }}</td>
                                <td>{{ equipment.serial_number }}</td>
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
    <v-card class="mt-3 p-3 rounded-0" elevation="0" style="border-top: 1px dashed #191970;" v-if="peripherals.length > 0">

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
                                    {{ peripheral.item_code }}
                                    <!-- <v-text-field class="hideID">{{ peripheral.id }}</v-text-field> -->
                                </td>
                                <td style="min-width: 250px;">{{ peripheral.description }}</td>
                                <td>
                                    <v-form ref="form" @prevent.submit="submitSerial"
                                        v-if="user.user.approval_level === 1 && editSerial === true">
                                        <v-text-field clearable density="compact" variant="plain"
                                            :rules="rulePeripheralSerial" placeholder="Serial number ...." w-100
                                            v-model="peripheral.peripheralSerial"
                                            @input="setSerialNumber"></v-text-field>
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
import { ref, onMounted, watch, getCurrentInstance, provide } from 'vue';
import { BASE_URL } from '@/api';
import { user_data } from '@/stores/auth/userData';
import axios from 'axios'
import { useDisplay } from 'vuetify'

const { width } = useDisplay()

defineEmits(['set-serial', 'get-serials', 'get-serial-numbers', 'get-equipments-data'])
const instance = getCurrentInstance()

const uri = BASE_URL
const user = user_data()

const equipments = ref([])
const peripherals = ref([])
const form = ref(null)
const rulePeripheralSerial = ref([
    v => !!v || 'Required field'
])
const peripheralSerial = ref('')


const props = defineProps({
    service_id: {
        type: Number
    },
    status: {
        type: Number,
    },
    editSerial: {
        type: Boolean,
        // default : () => false 
    },
})
const submitSerial = () => {

}

const getDetailsEquipment = async () => {
    const response = await axios.get(uri + 'api/get-equipments', {
        params: {
            service_id: props.service_id,
        },
        headers: {
            'Authorization': `Bearer ${user.tokenData}`,
        }
    })
    if (response.data && response.data.equipments) {
        const allEquipments = response.data.equipments
        equipments.value = allEquipments.filter(equipment => equipment.category === 'Equipment')
        peripherals.value = allEquipments.filter(equipment => equipment.category === 'Peripheral')

        instance.emit('get-serials', equipments.value)
    } else {
        alert('Something went wrong')
    }
}


/** Get instance of parent to pass data from child to parent */
const setSerialNumber = () => {
    watch(() => peripherals.value.map(peripheral => ({ id: peripheral.equipment_id, serial: peripheral.peripheralSerial })), (newValue) => {
        instance.emit('set-serial', newValue)
    })
}


onMounted(() => {
    getDetailsEquipment()
})

</script>