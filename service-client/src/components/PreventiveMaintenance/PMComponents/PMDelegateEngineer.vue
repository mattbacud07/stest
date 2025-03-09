<template>
    <v-btn type="button" :disabled="btnDisable" color="primary" variant="flat"
        class="text-none btnSubmit"><v-icon class="mr-2">mdi-account-arrow-left-outline</v-icon>
        Delegate Engineer

        <v-dialog v-model="engineerDialog" activator="parent" max-width="400" persistent>
            <v-card text="" title="Delegate Engineer" prepend-icon="mdi-account">
                <v-form @submit.prevent="delegateEngineer" ref="engineerForm">
                    <v-col cols="12">
                        <v-combobox color="primary" class="mt-5" v-model="Engineers" clearable
                            label="Delegate to" placeholder="Delegate to" density="compact"
                            :items="engineers" variant="outlined" itemValue="value" :rules="[
                                v => !!v || 'Required',
                                v => v.key !== undefined ? true : 'Select one of the options listed'
                            ]" itemTitle="key">
                            <template v-slot:item="{ item, props }">
                                <v-list-item v-bind="props">
                                    <v-list-item-title>
                                        <p class="small text-disabled">SBU {{ item.raw.sbu }}</p>
                                    </v-list-item-title>
                                </v-list-item>
                            </template>
                            <template v-slot:selection="{ item, index }">
                                <span class="small text-primary" style="font-size: 12px;"><b>{{
                                        item.title }}</b> <span class="text-disabled"
                                        v-if="item.title">- [SBU : {{ item.raw.sbu }}]</span></span>
                            </template>
                        </v-combobox>
                    </v-col>
                    <v-divider></v-divider>
                    <v-row justify="end" class="mt-7 mb-5 pr-3">
                        <v-btn variant="plain" @click="engineerDialog = false" color="primary"
                            class="text-none mr-2">
                            Cancel</v-btn>
                        <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable"
                            color="#191970" flat class="text-none bg-primary mr-5"><v-icon
                                class="mr-2">mdi-check</v-icon>
                            Submit</v-btn>
                    </v-row>
                </v-form>
            </v-card>
        </v-dialog>
    </v-btn>
</template>
<script setup>
import { ref } from 'vue';

import { apiRequestAxios } from '@/api/api';
const apiRequest = apiRequestAxios()

import { useToast } from 'vue-toast-notification';
const toast = useToast()

import { users_engineers } from '@/helpers/getUsers';
const { engineers } = users_engineers()


const Engineers = ref('')
const btnDisable = ref(false)
const btnLoading = ref(false)
const engineerDialog = ref(false)
const engineerForm = ref(false)


const emit = defineEmits(['refresh-data'])
const props = defineProps({
    id : {
        type : Number,
        default: null
    }
})

const delegateEngineer = async () => {
    btnLoading.value = true
    const { valid } = await engineerForm.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }
    try {
        const res = await apiRequest.post('pm_delegate_engineer', {
            id: props.id,
            engineer: Engineers.value?.value,
        })
        if (res.data && res.data.success) {
            toast.success('Successfully delegated')
            emit('refresh-data', true)
        } else if (res?.data?.exist) toast.error('Currently active')
        else {
            toast.error(response.data.errorE)
            btnLoading.value = false
        }
    } catch (error) {
        console.log(error)
        btnLoading.value = false
    } finally {
        btnLoading.value = false
    }
}
</script>