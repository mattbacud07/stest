<template>
    <v-row>
        <v-col cols="12" class="d-flex justify-content-end">
            <v-chip variant="tonal" color="grey lighten-5" label><span class="text-dark mr-2">Status:</span>
                <span :style="{ color: m_var.status_pm(status).color }">{{ m_var.status_pm(status).text
                    }}</span></v-chip>
        </v-col>
    </v-row>
    <v-card class="mt-3" elevation="0" style="border: 1px dashed #191970">
        <v-col cols="12">
            <v-row>
                <v-col cols="12">
                    <h5 class="text-primary">Service Provider Details</h5>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.engineer" color="primary" variant="outlined" density="compact"
                        label="Delegated to" placeholder="Delegated to" :readonly="textDisable"></v-text-field>
                </v-col>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.delegation_date" color="primary" variant="outlined"
                        density="compact" label="Delegation Date" placeholder="Delegation Date"
                        :readonly="textDisable"></v-text-field>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.date_accepted" color="primary" variant="outlined" density="compact"
                        label="Date Accepted" placeholder="Date Accepted" :readonly="textDisable"></v-text-field>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.date_out" color="primary" variant="outlined" density="compact"
                        label="Date Out" placeholder="Date Out" :readonly="textDisable"></v-text-field>
                </v-col>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.travel_time" color="primary" variant="outlined" density="compact"
                        label="Travel Time" placeholder="Travel Time" :readonly="textDisable"></v-text-field>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.time_in" color="primary" variant="outlined" density="compact"
                        label="Time In" placeholder="Time In" :readonly="textDisable"></v-text-field>
                </v-col>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.time_out" color="primary" variant="outlined" density="compact"
                        label="Time Out" placeholder="Time Out" :readonly="textDisable"></v-text-field>
                </v-col>
            </v-row>
        </v-col>
    </v-card>


</template>

<script setup>
import { ref, inject, onMounted, watch } from 'vue'
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance'

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

const textDisable = ref(true)

const formData = ref({
    engineer: '',
    delegation_date: '',
    date_accepted: '',
    date_out: '',
    travel_time: '',
    time_in: '',
    time_out: '',
});
const status = ref('')

const pm_data = ref(inject('pm_data'))

watch(pm_data, (pm) => {
    if (pm) {
        const pm_data = pm
        const user = pm_data.user || {}

        formData.value = {
            engineer: `${user.first_name || ''} ${user.last_name || ''}`.trim() || '--',
            delegation_date: pub_var.formatDate(pm_data.delegation_date) || '--',
            date_accepted: pub_var.formatDate(pm_data.date_accepted) || '--',
            date_out: pub_var.formatDate(pm_data.departed_date) || '--',
            travel_time: pm_data.travel_duration || '--',
            time_in: pub_var.formatDate(pm_data.start_date) || '--',
            time_out: pub_var.formatDate(pm_data.end_date) || '--',
        };
        status.value = pm_data.status || '---'
    }
}, { immediate: true })
onMounted(() => {

})
</script>