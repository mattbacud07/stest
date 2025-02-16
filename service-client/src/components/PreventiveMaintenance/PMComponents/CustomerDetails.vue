<template>
    <v-card class="mt-3" elevation="1">
        <v-col cols="12">
            <v-row>
                <v-col class="d-flex justify-content-between">
                    <h5 class="text-primary">Customer Details</h5>
                </v-col>
            </v-row>
            <v-row>
                <v-col cols="12" lg="6" md="6" sm="6">
                    <v-text-field v-model="formData.serial" color="primary" variant="outlined" density="compact"
                        label="Serial No." placeholder="Serial No." :readonly="textDisable"></v-text-field>
                </v-col>
                <v-col cols="12" lg="6" md="6" sm="6"
                    v-if="status === m_var.InProgress">
                    
                    <v-tooltip v-if="signature" activator="parent" location="top" color="primary">
                                    <v-img
                                    class="bg-white"
                                        :width="300"
                                        aspect-ratio="16/9"
                                        cover
                                        :src="signature"
                                        ></v-img>    
                                </v-tooltip>

                    <v-dialog v-model="clientSignature" full persistent min-height="320">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn type="button" v-bind="activatorProps" color="primary" class="text-none mr-2"><v-icon
                                    class="mr-2">mdi-draw-pen</v-icon>
                                Client Signatture
                            </v-btn> <span v-if="signature" class="text-success mr-7"><v-icon>mdi-check</v-icon>
                                    Signature set
                                </span>
                        </template>
                        <v-card>
                            <v-row>
                                <v-col class="p-4">
                                    <p class="text-red ml-2 mt-5 mb-3"><v-icon
                                            class="mr-1">mdi-information-outline</v-icon> Don't forget to
                                        save every after finalizing the
                                        signature.</p>
                                    <VueSignaturePad ref="signaturePad" height="400px"
                                        style="border: 1px solid #333;" />
                                </v-col>
                            </v-row>
                            <v-card-actions>
                                <v-row>
                                    <v-col cols="12" lg="8" md="8" sm="8" class="d-flex justify-content-end">
                                        <p v-if="signature" class="text-success mr-7"><v-icon>mdi-check</v-icon>
                                            Signature set</p>
                                    </v-col>
                                    <v-col cols="12" lg="4" md="4" sm="4" class="d-flex justify-content-end">
                                        <v-btn @click="saveSignature" color="primary" variant="flat">Save</v-btn>
                                        <v-btn @click="clearSignatureDrawing" color="primary"
                                            variant="tonal">Retry</v-btn>

                                        <v-btn @click="clientSignature = false" color="error"
                                            variant="tonal"><v-icon>mdi-close</v-icon>
                                            Close</v-btn>
                                    </v-col>
                                </v-row>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
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
import { ref, inject, onMounted, watch, defineProps, toRefs, nextTick } from 'vue'
import { getRole } from '@/stores/getRole';
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance'
import { useToast } from 'vue-toast-notification'
import { permit } from '@/castl/permitted';

const { can } = permit()



const role = getRole()
const currentRole = role.currentUserRole
const toast = useToast()

const emit = defineEmits(['set-confirm-serial', 'set-signature'])
const props = defineProps({
    status: {
        type: String,
        default: ''
    }
})
const { status } = toRefs(props)

const textDisable = ref(true)


const clientSignature = ref(false)
const signaturePad = ref(null)
const signature = ref(null)
const saveSignature = () => {
    const { isEmpty, data } = signaturePad.value.saveSignature()
    if (isEmpty) {
        alert('No signature to save')
    }
    signature.value = data
    emit('set-signature', data)
    clientSignature.value = false
}
const clearSignatureDrawing = () => {
    signaturePad.value.clearSignature()
    signature.value = null
    emit('set-signature', signature.value)
}
watch(clientSignature, (value) => {
    if (value && signature.value) {
        nextTick(() => {
            signaturePad.value.fromDataURL(signature.value)
        })
    }
})
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
        // const peripherals = pm.equipment_peripherals
        const equipment = pm.service_equipment
        // const institution = pm.eh
        const pm_data = pm
        formData.value = {
            serial: equipment.serial || '---',
            institution: pm_data.institution_name || '---',
            address: pm_data.address || '---',
            item_code: equipment.item_code || '---',
            item_description: equipment.description || '---'
        }
    }
}, { immediate: true })
</script>