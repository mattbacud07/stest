<template>

    <v-card density="compact" class="p-3 mt-3" elevation="1">
        <v-row>
            <v-col class="d-flex justify-content-between align-items-center">
                <h5 class="text-primary">Actions Taken</h5>
 
                <v-btn @click="addField" v-if="status === m_var.InProgress" class="text-none" color="primary" variant="text"
                    prepend-icon="mdi-comment-plus-outline">Add Field</v-btn>
            </v-col>
        </v-row>
        <v-list>

            <v-list-item v-for="data in actions" :key="data" v-if="actions.length > 0">
                <template v-slot:prepend>
                    <v-icon color="primary">mdi-file-document-check-outline</v-icon>
                </template>
                <v-list-item-title>
                    {{ data.action }}
                </v-list-item-title>
            </v-list-item>
            <v-list-item v-else>
                <v-list-item-title>
                    <v-icon color="grey">mdi-text-box-search-outline</v-icon> <span class="text-grey small">No records
                        found</span>
                </v-list-item-title>
            </v-list-item>
        </v-list>
        <template v-if="can('installer', 'Preventive Maintenance') && status === m_var.InProgress">
            <v-col cols="12">
                <v-card elevation="0">
                    <v-row>
                        <v-col cols="12" v-for="(field, index) in fields" :key="index">
                            <v-combobox v-model="fields[index]" :items="actions_data" item-title="actions"
                                item-value="actions" label="Select an option or enter your action manually"
                                append-inner-icon="mdi-trash-can-outline" variant="outlined" color="primary" class="mt-3"
                                density="compact" clearable :rules="[
                                    v => !!v || 'Required',
                                    v => (v && v.length <= 120) || 'Please limit your input to 120 characters'
                                ]" @update:modelValue="setActionsDone"
                                @click:append-inner="removeField(index)"></v-combobox>
                        </v-col>
                    </v-row>
                </v-card>
            </v-col>
        </template>
    </v-card>

    <v-card class="p-3 mt-3" elevation="1">
        <v-row>
            <v-col class="d-flex justify-content-between">
                <h5 class="text-primary">Remarks & Recommendation</h5>
            </v-col>
        </v-row>
        <v-col col="12">
            <v-textarea v-model="formData.remarks" @input="inputRemarks" color="primary"
                :variant="currentRole === pub_var.engineerRoleID && status === m_var.InProgress ? 'underlined' : 'plain'"
                placeholder="Type your remarks & recommendations here ..."
                :readonly="currentRole === pub_var.engineerRoleID && status === m_var.InProgress ? false : true"></v-textarea>

        </v-col>
    </v-card>

</template>

<script setup>
import { ref, inject, onMounted, watch, getCurrentInstance, defineEmits, defineProps, toRefs } from 'vue'
import { getRole } from '@/stores/getRole';
import { user_data } from '@/stores/auth/userData'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global';
import * as m_var from '@/global/maintenance';


const user = user_data()
const apiRequest = apiRequestAxios()

const role = getRole()
const currentRole = role.currentUserRole

const instance = getCurrentInstance()

import { useActions } from '@/helpers/getActionsTaken';
const { actions_data } = useActions()


const textDisable = ref(true)

const formData = ref({
    remarks: '',
})

/** Permissions */
import { permit } from '@/castl/permitted';
const { can } = permit()


const props = defineProps({
    status: {
        type: String
    }
})

defineEmits([
    'actions', 'remarks'
])

const { status } = toRefs(props)

const pm_data = ref(inject('pm_data'))
const actions = ref([])

watch(pm_data, (pm) => {
    if (pm) {
        const pm_data = pm
        actions.value = pm.actions || []

        formData.value = {
            remarks: pm_data.remarks || '',
        }
    }
}, { immediate: true })


const fields = ref([]);
const addField = () => {
    if (fields.value.length < 8) {
        fields.value.push('');
    }
};

const removeField = (index) => {
    fields.value.splice(index, 1);
};


const setActionsDone = () => {
    instance.emit('actions', fields.value)
    // console.log(fields.value)
}
const inputRemarks = () => {
    instance.emit('remarks', formData.value.remarks)
    // console.log(fields.value)
}
</script>


<style scoped>
.v-list-item-title {
    white-space: collapse;
}
</style>