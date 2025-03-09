<template>
    <v-dialog v-model="AcceptDeclineDialog" max-width="600" persistent>
        <template v-slot:activator="{ props: activatorProps }">

            <!-- Decline Button -->
            <v-btn type="button" v-bind="activatorProps" color="primary" variant="plain"
                class="text-none mr-2" @click="openDialog('declined')"> Decline
            </v-btn>

            <v-btn type="button" v-bind="activatorProps" color="primary" variant="flat"
                class="text-none" @click="openDialog('accepted')">
                <v-icon class="mr-2">mdi-check</v-icon> Accept
            </v-btn>


        </template>
        <v-card class="pa-5">
            <v-form @submit.prevent="AcceptDeclineRequest" ref="formAcceptDecline">
                <v-row>
                    <v-col class="d-flex justify-center">
                        <v-textarea v-model="remarks" :rules="actionType === 'declined' ? [v => !!v?.trim() || 'Required'] : []"
                            label="Remarks" placeholder="Remarks" dense rows="3" variant="outlined"
                            class="mt-5 w-100"></v-textarea>
                    </v-col>
                </v-row>
                <v-row justify="end" class="mt-7 mb-5">
                    <v-btn variant="plain" @click="AcceptDeclineDialog = false" color="primary" class="text-none mr-2">
                        Cancel</v-btn>
                    <v-btn type="submit" :loading="btnLoading" color="primary" variant="flat"
                        class="text-none mr-5">
                        {{ actionType === 'accepted' ? 'Accept' : 'Decline' }}</v-btn>
                </v-row>
            </v-form>
        </v-card>
    </v-dialog>
</template>

<script setup>
import { ref } from 'vue'
import { apiRequestAxios } from '@/api/api'
const apiRequest = apiRequestAxios()

import { useToast } from 'vue-toast-notification';
const toast = useToast()

const btnLoading = ref(false)
const AcceptDeclineDialog = ref(false)

const remarks = ref('')
const actionType = ref('')  // Accept or Decline

const openDialog = (type) => {
    actionType.value = type;
    AcceptDeclineDialog.value = true;
};



const emit = defineEmits(['refresh-data'])
const props = defineProps({
    id : {
        type : Number,
        default: null
    },
    delegation_id: {
        type : Number,
        default : null
    }
})


/** ========================== Accept Decline the Delegation ======================== */
const formAcceptDecline = ref(false)
const AcceptDeclineRequest = async () => {
    btnLoading.value = true
    const { valid } = await formAcceptDecline.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('accept-decline-delegated', {
            service_id: props.id,
            delegation_id: props.delegation_id,
            status: actionType.value,
            remarks: remarks.value
        })
        if (response.data?.success) {
            toast.success('Successful')
            emit('refresh-data', true)
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        btnLoading.value = false
        AcceptDeclineDialog.value = false
    }
}
</script>