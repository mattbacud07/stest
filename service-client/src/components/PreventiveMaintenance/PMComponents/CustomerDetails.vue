<template>
    <v-card class="mt-3" elevation="0" style="border: 1px dotted #191970">
        <v-col cols="12">
            <v-row>
                <v-col class="d-flex justify-content-between">
                    <h5 class="text-primary">Customer Details</h5>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.serial" @input="confirmSerial" color="primary" variant="outlined"
                        density="compact" label="Serial No." placeholder="Serial No."
                        :readonly="currentRole === pub_var.TLRole && status === m_var.Scheduled ? false : textDisable"></v-text-field>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.institution" color="primary" variant="outlined" density="compact"
                        label="Institution" placeholder="Institution" :readonly="textDisable"></v-text-field>
                </v-col>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.address" color="primary" variant="outlined" density="compact"
                        label="Address" placeholder="Address" :readonly="textDisable"></v-text-field>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.item_code" color="primary" variant="outlined" density="compact"
                        label="Item Code" placeholder="Item Code" :readonly="textDisable"></v-text-field>
                </v-col>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.item_description" color="primary" variant="outlined"
                        density="compact" label="Item Description" placeholder="Item Description"
                        :readonly="textDisable"></v-text-field>
                </v-col>
            </v-row>
        </v-col>
    </v-card>
</template>

<script setup>
import { ref, inject, onMounted, watch, defineProps, toRefs } from 'vue'
import { getRole } from '@/stores/getRole';
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance'
import { useToast } from 'vue-toast-notification'



const role = getRole()
const currentRole = role.currentUserRole
const toast = useToast()

const emit = defineEmits(['set-confirm-serial'])
const props = defineProps({
    status: {
        type: String,
        default: ''
    }
})
const { status } = toRefs(props)

const textDisable = ref(true)

const formData = ref({
    serial: '',
    institution: '',
    address: '',
    item_code: '',
    item_description: '',
})

const pm_data = ref(inject('pm_data'))

watch(pm_data, (pm) => {
    if (pm) {
        // console.log(pm)
        const peripherals = pm.equipment_peripherals
        const equipment = pm.equipment
        const institution = pm.eh
        const pm_data = pm
        formData.value = {
            serial: peripherals.serial_number || '---',
            institution: institution.name || '---',
            address: institution.address || '---',
            item_code: equipment.item_code || '---',
            item_description: equipment.description || '---'
        }
    }
}, { immediate: true })



/** Reconfirm Serial */
const confirmSerial = () => {
    formData.value.serial === '' || formData.value.serial.trim() === '' ? emit('set-confirm-serial', null) : emit('set-confirm-serial', formData.value.serial)
}

onMounted(() => {
    if (status.value === m_var.Scheduled && currentRole === pub_var.TLRole) {
        toast.warning('Please confirm the serial number before proceeding', { duration: 7000 })
    }
})
</script>