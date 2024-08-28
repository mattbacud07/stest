<template>
    <v-card class="p-3 mt-3" elevation="0" style="border: 1px dotted #191970" title="Customer Details">
        <v-col cols="12">
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.serial" color="primary" variant="outlined" density="compact"
                        label="Serial No." placeholder="Serial No." :readonly="textDisable"></v-text-field>
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
import { ref, inject, onMounted, watch } from 'vue'

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
}, {immediate : true})
onMounted(() => {

})
</script>