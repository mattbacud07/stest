<template>

    <!-- Delegate Button Button -->
    <v-card flat class="mb-5 mt-7 pt-3 pb-1" color="info" variant="tonal">
        <!-- Delegate Button Button -->
        <v-col cols="12">
            <v-row>
                <v-col cols="12">
                    <h5 style="font-weight: 500;color: #191970;"><span>Service Department - Internal
                            Servicing</span></h5>
                            <p class="small mt-2 mb-3">
                                If you select Start the Internal Servicing, 
                                the process will pause and wait until
                                 the internal servicing is completed before proceeding further
                            </p>
                </v-col>
            </v-row>
            <v-dialog v-model="dialogInternalRequest" max-width="500" persistent>
                <template v-slot:activator="{ props: activatorProps }">
                    <v-btn type="button" variant="tonal" v-bind="activatorProps" color="primary"
                        class="text-none btnSubmit mt-3">
                        <v-icon class="mr-2">mdi-transfer-right</v-icon>
                        Start Internal Servicing</v-btn>
                </template>

                <v-card>
                    <v-form @submit.prevent="delegateInternalServicing" ref="form">
                        <v-col cols="12">
                            <h5 class="mt-3" style="font-weight: 500;color: #191970;">Internal Servicing - Delegation of Service</h5>
                            <v-divider></v-divider>

                            <v-combobox color="primary" class="mt-5" v-model="Engineers" clearable label="Delegate to"
                                placeholder="Delegate to" density="compact" :items="engineers" variant="outlined"
                                itemValue="value" 
                                :rules="[
                                    v => !!v || 'Required',
                                    v => v.key !== undefined ? true : 'Select one of the options listed'
                                ]" 
                                itemTitle="key">
                                <template v-slot:item="{ item, props }">
                                    <v-list-item v-bind="props">
                                        <v-list-item-title>
                                            <p class="small text-disabled">SBU {{ item.raw.sbu }}</p>
                                        </v-list-item-title>
                                    </v-list-item>
                                </template>
                                <template v-slot:selection="{item, index}">
                                    <span class="small text-primary" style="font-size: 12px;"><b>{{ item.title }}</b> <span class="text-disabled" v-if="item.title">- [SBU : {{ item.raw.sbu }}]</span></span>
                                </template>
                            </v-combobox>
                        </v-col>

                        <v-divider class="mb-5"></v-divider>
                        <!-- <template v-slot:actions> -->
                        <v-row justify="end" class="mb-3">
                            <v-btn @click="dialogInternalRequest = false" elevation="0" size="small" class="text-none mr-2">
                                Cancel</v-btn>
                            <v-btn type="submit" size="small" :loading="btnLoading" :disabled="btnDisable"
                                color="#191970" class="text-none bg-primary mr-5"><v-icon
                                    class="mr-2">mdi-check</v-icon>
                                Delegate</v-btn>
                        </v-row>
                        <!-- </template> -->
                    </v-form>
                </v-card>
            </v-dialog>
        </v-col>
    </v-card>
</template>



<script setup>
// import { apiRequest } from '@/api';
import { ref, onMounted, watch, inject, toRefs, defineEmits } from 'vue'
import { apiRequestAxios } from '@/api/api'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
import moment from 'moment';
const toast = useToast()

const dialogInternalRequest = ref(false)
const Engineers = ref('')
const form = ref(false)
const btnLoading = ref(false)
const btnDisable = ref(false)
const disableButton = inject('disableButton', null)


const emit = defineEmits(['refresh'])

const apiRequest = apiRequestAxios()

const props = defineProps({
    service_id: {
        type: Number
    },
})
const formatDate = (data) => {
    return data !== null ? moment(data).format('YYYY-MM-DD hh:mm a') : '--'
}


/** Fetch All Service Engineers */
import { users_engineers } from '@/helpers/getUsers';
const { engineers } = users_engineers()

/**
 * Submit || Delegate Internal Servicing
 */
const delegateInternalServicing = async () => {
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }

    try {
        const response = await apiRequest.post('internal-process', {
            service_id: props.service_id,
            assigned_to: Engineers.value.value
        })
        if (response.data && response.data.success) {
            toast.success('Successfully delegated')
            if (typeof (disableButton) === 'function') disableButton()
            emit('refresh')
        } else if (response.data && response.data.exist_service_id) {
            toast.warning('Request already exist.')
        }
    } catch (error) {
        alert(error)
    }
    finally {
        btnLoading.value = false
        dialogInternalRequest.value = false
    }
}

</script>