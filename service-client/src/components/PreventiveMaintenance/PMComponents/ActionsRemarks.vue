<template>

    <v-card class="p-3 mt-3" elevation="0" style="border: 1px dashed #191970" title="Actions Taken">
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
                <template v-slot:prepend>
                    <v-icon color="primary">mdi-file-document-check-outline</v-icon>
                </template>
                <v-list-item-title>
                    No records found
                </v-list-item-title>
            </v-list-item>
        </v-list>
        <template v-if="currentRole === pub_var.engineerRole && status === m_var.InProgress">
            <v-col cols="12">
                <v-card elevation="0">
                    <v-btn @click="addField" class="text-none" color="primary" variant="flat"
                        prepend-icon="mdi-comment-plus-outline">Add Field</v-btn>

                    <v-table class="mt-4" style="border: none!important;">
                        <tbody>
                            <tr v-for="(field, index) in fields" :key="index" >
                                <td class="d-flex align-items-center" style="border-bottom:none!important;">
                                    <!-- <v-text-field v-model="fields[index]" label="Actions Done"
                                        prepend-inner-icon="mdi-message-text-fast-outline"
                                        append-inner-icon="mdi-trash-can-outline"
                                         hide-details single-line
                                        variant="outlined" color="primary" density="compact"
                                        :rules="[v => !!v || 'Required']" @input="setActionsDone" @click:append-inner="removeField(index)" -->
                                        <!-- /> -->

                                    <v-combobox v-model="fields[index]" :items="actions_data" item-title="actions" item-value="actions"  label="Select an option or enter your action manually"
                                    
                                        append-inner-icon="mdi-trash-can-outline"
                                         hide-details 
                                    variant="outlined" color="primary" density="compact" clearable
                                    :rules="[v => !!v || 'Required']" @update:modelValue="setActionsDone"  @click:append-inner="removeField(index)" 
                                    ></v-combobox>
                                    
                                    <!-- <v-btn @click="removeField(index)" color="error" icon class="ml-3">
                                        <v-icon>mdi-close</v-icon>
                                    </v-btn> -->
                                </td>
                            </tr>
                        </tbody>
                    </v-table>
                </v-card>
            </v-col>
        </template>
    </v-card>

    <v-card class="p-3 mt-3" elevation="0" style="border: 1px dashed #191970" title="Remarks & Recommendation">
        <v-col col="12">
            <v-col cols="12">
                <v-textarea v-model="formData.remarks" @input="inputRemarks" color="primary"
                    :variant="currentRole === pub_var.engineerRole && status === m_var.InProgress ? 'underlined' : 'plain'"
                    placeholder="Type your remarks & recommendations here ..."
                    :readonly="currentRole === pub_var.engineerRole && status === m_var.InProgress ? false : true"></v-textarea>
            </v-col>
        </v-col>
    </v-card>

</template>

<script setup>
import { ref, inject, onMounted, watch, getCurrentInstance, defineEmits, defineProps, toRefs } from 'vue'
import { getRole } from '@/stores/getRole';
import { user_data } from '@/stores/auth/userData'
import * as pub_var from '@/global/global';
import * as m_var from '@/global/maintenance';


const user = user_data()
const apiRequest = user.apiRequest()

const role = getRole()
const currentRole = role.currentUserRole

const instance = getCurrentInstance()


const textDisable = ref(true)

const formData = ref({
    remarks: '',
})

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
    fields.value.push('');
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



/** Get Standard Actions */
const actions_data = ref([])
const getStandardActions = async () => {
    try {
        const response = await apiRequest.get('getStandardActions')
        if (response.data && response.data.actions) {
            actions_data.value = response.data.actions.map(val => val.actions)
        }
    } catch (error) {
        console.log(error)
    }
}

onMounted(() => {
    getStandardActions()
})
</script>


<style scoped>
.v-list-item-title{
    white-space: collapse;
}
</style>