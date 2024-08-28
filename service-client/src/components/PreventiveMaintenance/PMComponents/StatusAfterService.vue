<template>
    <v-card class="p-3 mt-3" elevation="0" style="border: 1px dashed #191970" title="Status After The Service">
        <v-row>
            <v-col cols="12">
                <v-radio-group v-model="formData.status_after_service" :disabled="textDisable"
                    @change="statusAfterService" color="primary" class="radioStatAfterService">
                    <v-radio value="Operational" label="Operational" true-icon="mdi-checkbox-marked" false-icon="mdi-checkbox-blank-outline"></v-radio>
                    <v-radio value="For Further Monitoring" label="For Further Monitoring" true-icon="mdi-checkbox-marked" false-icon="mdi-checkbox-blank-outline"></v-radio>
                    <v-radio value="Non-Operational" label="Non-Operational" true-icon="mdi-checkbox-marked" false-icon="mdi-checkbox-blank-outline"></v-radio>
                </v-radio-group>
            </v-col>
        </v-row>
    </v-card>
</template>

<script setup>
import { ref, inject, onMounted, watch, getCurrentInstance, defineEmits, defineProps, toRefs } from 'vue'
import * as m_var from '@/global/maintenance'
import * as pub_var from '@/global/global'
import { getRole } from '@/stores/getRole';
const instance = getCurrentInstance()
const emit = defineEmits(['stat']);

const role = getRole()
const currentRole = role.currentUserRole

const formData = ref({
    status_after_service: '',
})

const pm_data = ref(inject('pm_data'))

const props = defineProps({
    status: {
        type: String,
    }
})
const { status } = toRefs(props)

const textDisable = ref(currentRole === pub_var.engineerRole && status.value === m_var.InProgress ? false : true)


const statusAfterService = () => {
    emit('stat', formData.value.status_after_service)
}

watch(pm_data, (pm) => {
    if (pm) {
        const pm_data = pm
        formData.value = {
            status_after_service: pm_data.status_after_service || '',
        }
    }
}, { immediate: true })



</script>


<style scoped>
.radioStatAfterService .v-selection-control--disabled{
    color: #191970!important;
    opacity: 1!important;
}
</style>