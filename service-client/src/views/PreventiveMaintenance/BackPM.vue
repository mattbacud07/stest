<template>
    <LayoutSinglePage>
        <template #topBarFixed class="d-print-none">
            <div>
                <v-breadcrumbs class="pt-7">
                    <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index"
                        :class="{ 'custom-pointer': !item.disabled }" @click="navigateTo(item)"
                        :disabled="item.disabled">
                        {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                    </v-breadcrumbs-item>
                </v-breadcrumbs>

                <!-- <v-btn @click="printPreview(isPrintPreview)" class="text-none" prepend-icon="mdi-printer-eye" color="primary" density="compact">{{ isPrintPreview ? 'Back' : 'Print Preview'}}</v-btn> -->
                <!-- <v-btn v-print="'#printThisA4'" class="text-none" prepend-icon="mdi-printer-eye" color="primary"
                    density="compact">Print Preview</v-btn> -->
            </div>
            <v-spacer></v-spacer>

            <!-- <v-skeleton-loader type="button" :loading="skeleton">
                <div>
                    <template v-if="can('delegate', PM) && getUserSSU() === pm_ssu">
                        <v-dialog v-model="delegateEngineerDialog" max-width="400" persistent
                            v-if="status === m_var.Scheduled">
                            <template v-slot:activator="{ props: activatorProps }">

                                <v-btn type="button" v-bind="activatorProps" :disabled="btnDisable" color="primary"
                                    variant="flat" class="text-none btnSubmit"><v-icon
                                        class="mr-2">mdi-account-arrow-left-outline</v-icon>
                                    Delegate Engineer</v-btn>
                            </template>
                            <v-card text="" title="Delegate Engineer" prepend-icon="mdi-account">
                                <v-form @submit.prevent="delegateEngineer" ref="form">
                                    <v-col cols="12">
                                        <v-select color="primary" v-model="selectedEngineer" class="mr-2 ml-2 mt-5"
                                            label="Service Engineer" placeholder="" density="compact"
                                            variant="outlined" :items="engineersData" item-title="title"
                                            item-value="value" :rules="[v => !!v || 'Required']" clearable></v-select>
                                    </v-col>
                                    <v-divider></v-divider>
                                    <v-row justify="end" class="mt-7 mb-5 pr-3">
                                        <v-btn variant="tonal" @click="delegateEngineerDialog = false" color="primary"
                                            class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                            Cancel</v-btn>
                                        <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable"
                                            color="#191970" flat class="text-none bg-primary mr-5"><v-icon
                                                class="mr-2">mdi-check</v-icon>
                                            Submit</v-btn>
                                    </v-row>
                                </v-form>
                            </v-card>
                        </v-dialog>
                    </template>

                    <template v-if="can('installer', PM) && getUserSSU() === pm_ssu">
                        <div v-if="status === m_var.Delegated">
                            <v-dialog v-model="pm_decline_dialog" max-width="400" persistent>
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn type="button" :disabled="btnDisable" v-bind="activatorProps" color="primary"
                                        variant="tonal" class="text-none mr-2"><v-icon class="mr-2">mdi-check</v-icon>
                                        Decline</v-btn>
                                </template>
                                <v-card text="" title="Decline Task">
                                    <v-form @submit.prevent="pm_decline" ref="form">
                                        <v-col cols="12">
                                            <v-textarea v-model="reason_to_decline" color="primary" label="Reason"
                                                variant="outlined" :rules="[v => !!v || 'Required']"></v-textarea>
                                        </v-col>
                                        <v-divider></v-divider>
                                        <v-row justify="end" class="mt-7 mb-5 pr-3">
                                            <v-btn variant="tonal" @click="pm_decline_dialog = false" color="primary"
                                                class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                                Cancel</v-btn>
                                            <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable"
                                                color="#191970" flat class="text-none bg-primary mr-5"><v-icon
                                                    class="mr-2">mdi-check</v-icon>
                                                Submit</v-btn>
                                        </v-row>
                                    </v-form>
                                </v-card>
                            </v-dialog>
                            <v-btn type="button" :loading="btnLoading" :disabled="btnDisable" @click="pm_accepted"
                                color="primary" variant="flat" class="text-none "><v-icon
                                    class="mr-2">mdi-check</v-icon> Accept</v-btn>
                        </div>

                        <div v-if="status === m_var.Accepted && getUserSSU() === pm_ssu">
                            <v-btn type="button" :loading="btnLoading" :disabled="btnDisable"
                                @click="pm_task_processing" color="primary" variant="flat" class="text-none "><v-icon
                                    class="mr-2">mdi-check</v-icon> In Transit</v-btn>
                        </div>
                        <div v-if="status === m_var.InTransit && getUserSSU() === pm_ssu">
                            <v-btn type="button" :loading="btnLoading" :disabled="btnDisable"
                                @click="pm_task_processing" color="primary" variant="flat" class="text-none "><v-icon
                                    class="mr-2">mdi-check</v-icon> Start PM Task</v-btn>
                        </div>
                        <div v-if="status === m_var.InProgress && getUserSSU() === pm_ssu">
                            <v-btn type="button" :loading="btnLoading" :disabled="btnDisableDone"
                                @click="clear" color="primary" variant="text" class="text-none "><v-icon
                                    class="mr-2">mdi-close</v-icon> Clear</v-btn>

                            <v-btn type="button" :loading="btnLoading" :disabled="btnDisableDone"
                                @click="pm_task_processing" color="primary" variant="flat" class="text-none "><v-icon
                                    class="mr-2">mdi-check</v-icon> Mark as Done</v-btn>
                        </div>
                    </template>

                </div>
            </v-skeleton-loader> -->
        </template>

        <template #default>
                <v-container class="mt-10">
                    <!-- <PMPrint />
                    <OperationAfterService :pm_data="pm_data" :pm_id="parseInt(id)" :currentRole="currentRole" />
                    <ServiceProvider />
                    <CustomerDetails  @set-signature="setSignature"
                        :status="status" />
                    <ActionsRemarks @actions="actions_done" @remarks="getRemarks" :status="status" />
                    <StatusAfterService @status-after-service="status_after_service" :status="status"
                        :pm_id="parseInt(id)" @sparePartsData="getSparePartsData" /> -->
                </v-container>
        </template>
    </LayoutSinglePage>
</template>
<script setup>
import { ref, reactive, onMounted, watch, provide } from 'vue';
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';
import moment from 'moment';
import { useRouter, useRoute } from 'vue-router';
import * as m_var from '@/global/maintenance'
import * as pub_var from '@/global/global'

/** Import Components */
// import CustomerDetails from '@/components/PreventiveMaintenance/PMComponents/CustomerDetails.vue'
// import StatusAfterService from '@/components/PreventiveMaintenance/PMComponents/StatusAfterService.vue'
// import ActionsRemarks from '@/components/PreventiveMaintenance/PMComponents/ActionsRemarks.vue'
// import ServiceProvider from '@/components/PreventiveMaintenance/PMComponents/ServiceProvider.vue'
// import OperationAfterService from '@/components/PreventiveMaintenance/PMComponents/OperationAfterService.vue'
// import PMPrint from '@/components/Print/PMPrint.vue';

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()



import { user_data } from '@/stores/auth/userData';
import { getRole } from '@/stores/getRole';
import { apiRequestAxios } from '@/api/api';

/** data declarations */
const router = useRouter()
const route = useRoute()
const user = user_data()

const apiRequest = apiRequestAxios()

/** Role Stores and Permissions */
const role = getRole()
const currentRole = role.currentUserRole

/** Permissions */
import { permit } from '@/castl/permitted';
const { can } = permit()

import { PM } from '@/global/modules';


const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/preventive-maintenance' },
    { title: 'Preventive Maintenance', disabled: true, href: '' },
    { title: 'PM-Details', disabled: true, href: '' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};

const form = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const loading = ref(false)
const delegateEngineerDialog = ref(false)
const pm_decline_dialog = ref(false)
const id = parseInt(route.params.id)

const selectedEngineer = ref('')

/** Signature */
const signature = ref(null)
const setSignature = (data) => {
    signature.value = data
}




/** Get PM Data By Row ID */
const getPMDataByRowID = async () => {
    loading.value = true
    try {
        const response = await apiRequest.get('get_pm_record_details', {
            params: { id: id }
        });
        if (response?.data?.pm) {

        } else toast.error('Something went wrong')
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false
    }
};




// const delegateEngineer = async () => {
//     btnLoading.value = true
//     const { valid } = await form.value.validate()
//     if (!valid) {
//         btnLoading.value = false
//         return
//     }
//     try {
//         const res = await apiRequest.post('pm_process', {
//             id: id,
//             engineer: selectedEngineer.value,
//             // serial: serial.value,
//         })
//         if (res.data && res.data.success) {
//             toast.success('Successfully delegated')
//             form.value.reset()
//             getPMDetails()
//         } else if (res.data.delegated_exist) toast.error('Currently delegated')
//         else {
//             toast.error(response.data.errorE)
//             btnLoading.value = false
//         }
//     } catch (error) {
//         alert(error)
//         btnLoading.value = false
//     } finally {
//         btnLoading.value = false
//         form.value.reset()
//         delegateEngineerDialog.value = false
//     }
// }



// /** Decline PM */
// const reason_to_decline = ref('')
// const pm_decline = async () => {
//     btnLoading.value = true

//     const { valid } = await form.value.validate()


//     if (!valid) {
//         btnLoading.value = false
//         return
//     }
//     try {
//         const res = await apiRequest.post('pm_decline', {
//             pm_id: id,
//             reason: reason_to_decline.value
//         })
//         if (res.data && res.data.success) {
//             toast.success('Reason submitted')
//             form.value.reset()
//             router.push(currentWorkType.value)
//         }
//         else {
//             toast.error('Something went wrong')
//             btnLoading.value = false
//         }
//     } catch (error) {
//         alert(error)
//         btnLoading.value = false
//     } finally {
//         btnLoading.value = false
//         form.value.reset()
//         pm_decline_dialog.value = false
//     }
// }



// /** Accept PM */
// const pm_accepted = async () => {
//     btnLoading.value = true

//     try {
//         const response = await apiRequest.post('pm_accepted', {
//             id: id,
//         })
//         if (response.data && response.data.success) {
//             toast.success('PM Task Accepted')
//             getPMDetails();
//         }
//         else {
//             toast.error('Something went wrong')
//             btnLoading.value = false
//         }
//     } catch (error) {
//         alert(error)
//         btnLoading.value = false
//     } finally {
//         btnLoading.value = false
//     }
// }


/** PM Task Proccessing*/
// const pm_task_processing = async () => {
//     btnLoading.value = true
//     if (status.value === m_var.InProgress) {
//         const checkIfTheresEmpty = actions.value.some(val => {
//             return typeof val === 'string' ? val.trim() === '' : val === undefined || val === null
//         })
//         const checkIfTheresOverText = actions.value.every(val => {
//             return val?.length <= 120 ? true : false
//         })

//         const checkSparePartsEmpty = spareParts.value.every(data => {
//             return data.item_id && data.item_code && data.description && data.qty && data.dr && data.si
//         })


//         if (!signature.value) {
//             toast.error('Client signature is required')
//             btnLoading.value = false
//             return
//         }

//         if (actions.value.length === 0) {
//             toast.error('Please submit actions taken')
//             btnLoading.value = false
//             return
//         }
//         if (checkIfTheresEmpty) {
//             toast.error('Please fill in required fields')
//             btnLoading.value = false
//             return
//         }
//         if (!checkIfTheresOverText) {
//             toast.error('Please limit your input to 120 characters')
//             btnLoading.value = false
//             return
//         }

//         if (statusAfterService.value === '') {
//             toast.error('Please choose one option what is the status after service')
//             btnLoading.value = false
//             return
//         }

//         if (spareParts.value.length > 0 && !checkSparePartsEmpty) {
//             toast.error('Please fill in all required fields in spareparts')
//             btnLoading.value = false
//             return
//         }
//     }
//     try {
//         const response = await apiRequest.post('pm_task_processing', {
//             id: id, 
//             actions: actions.value,
//             spareParts: spareParts.value,
//             status_after_service: statusAfterService.value,
//             remarks: remarks.value,
//             work_type: route.params.work_type,
//             signature : signature.value
//         })
//         if (response.data && response.data.success) {
//             toast.success('Successfully Save')
//             getPMDetails();
//         }
//         else {
//             toast.error(response.data.error)
//             btnLoading.value = false
//         }
//     } catch (error) {
//         alert(error)
//         btnLoading.value = false
//     } finally {
//         btnLoading.value = false
//     }
// }




// Provide PM_data
// provide('pm_data', pm_data)
// provide('refreshPMData', getPMDetails)



onMounted(async () => {
    getPMDataByRowID();
});
</script>


<style scoped>
.v-list-item-title {
    white-space: pre-wrap !important;
}
</style>