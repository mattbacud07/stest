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
                    
                <v-icon color="grey" @click="refreshData(true)"><v-tooltip activator="parent" location="bottom">Refresh</v-tooltip> mdi-refresh</v-icon>
                </v-breadcrumbs>

                <!-- <v-btn @click="printPreview(isPrintPreview)" class="text-none" prepend-icon="mdi-printer-eye" color="primary" density="compact">{{ isPrintPreview ? 'Back' : 'Print Preview'}}</v-btn> -->
                <!-- <v-btn v-print="'#printThisA4'" class="text-none" prepend-icon="mdi-printer-eye" color="primary"
                    density="compact">Print Preview</v-btn> -->
            </div>
            <v-spacer></v-spacer>

            <div>
                <PMDelegateEngineer v-if="can('delegate', PM) && canDelegate" :id="id" @refresh-data="refreshData" />

                <PMAcceptDecline
                    v-if="can('installer', PM) && canAcceptDecline && task_delegation?.status === m_var.Delegated"
                    :id="id" :delegation_id="task_delegation?.id" @refresh-data="refreshData" />

                <div v-if="can('installer', PM) && canAcceptDecline && task_delegation?.status === m_var.Accepted">

                    <VDialog v-model="dialogSure" title="Information" text="Are you sure to this action?"
                        @confirm="inTransit" />
                    <v-btn type="button" :loading="btnLoading" :disabled="btnDisable" @click="dialogSure = true"
                        color="primary" variant="flat" class="text-none "><v-icon class="mr-2">mdi-check</v-icon>In
                        Transit
                    </v-btn>
                </div>
                <div v-if="can('installer', PM) && canAcceptDecline && task_delegation?.status === m_var.InTransit">
                    <VDialog v-model="dialogSure" title="Information" text="Are you sure to this action?"
                        @confirm="startPMTask" />
                    <v-btn type="button" :loading="btnLoading" :disabled="btnDisable" @click="dialogSure = true"
                        color="primary" variant="flat" class="text-none "><v-icon class="mr-2">mdi-check</v-icon> Start
                        PM Task</v-btn>
                </div>

                <div v-if="can('installer', PM) && canAcceptDecline && task_delegation?.status === m_var.InProgress">
                    <v-btn @click="markAsCompleted" text="Mark as Completed" :disabled="btnDisable" color="primary"
                        variant="flat" class="text-none mr-5" />
                </div>

            </div>
        </template>

        <template #default>
            <v-container class="mt-10">
                <template v-if="loading">
                    <v-row>
                        <v-col cols="6"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                        <v-col cols="6"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    </v-row>
                </template>
                <template v-else>
                    <transition name="slide-x-transition">
                        <div>
                            <v-chip class="ma-2 mt-5" :color="m_var.setPMStatus(pm_data?.status).color" label>
                                <v-tooltip activator="parent" location="bottom">Status</v-tooltip>
                                &nbsp;{{ m_var.setPMStatus(pm_data?.status).text ?? '---' }}
                            </v-chip>
                            <v-chip class="ma-2 mt-5" color="purple" label
                                v-if="latestTaskDelegation?.status_after_service">
                                <v-tooltip activator="parent" location="bottom">Status after service</v-tooltip>
                                &nbsp;{{ latestTaskDelegation?.status_after_service ?? '---' }}
                            </v-chip>
                            <v-chip class="ma-2 mt-5" v-if="pm_data?.tag" color="purple" label>
                                <v-tooltip activator="parent" location="bottom">Tag</v-tooltip>
                                &nbsp;{{ pm_data?.tag ?? '---' }}
                            </v-chip>
                        </div>
                    </transition>

                    <ServiceReportForm v-model="service_report_data" ref="serviceFormRef" class="mt-7"
                    v-if="can('installer', PM) && canAcceptDecline && task_delegation?.status === m_var.InProgress" />

                    <PMCustomerDetails :customer_details="pm_data" />
               
                <!-- <PMPrint /> -->
                <v-card class="mt-10">
                    <v-tabs v-model="tab" density="compact" class="border-b-sm">
                        <div>
                            <v-tab value="delegation_details" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-gesture-tap-button</v-icon> Delegation
                                Details</v-tab>
                            <v-tab value="service_report" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-poll</v-icon>
                                Service Report</v-tab>
                        </div>
                        <v-spacer></v-spacer>
                        <v-tab value="customer_details" class="text-none" color="primary"><v-icon
                                class="mr-2">mdi-badge-account</v-icon>
                            Customer Details</v-tab>
                        <div>

                        </div>
                    </v-tabs>

                    <v-card-text>
                        <v-window v-model="tab">
                            <v-window-item value="delegation_details">
                                <InternalServicingActivity :internalData="pm_data" />
                            </v-window-item>
                            <v-window-item value="service_report">
                                <ServiceReportData :task_delegation="task_delegation" :actions_taken="actions_taken"
                                    :spareparts="spareparts" />
                            </v-window-item>
                            <v-window-item value="customer_details">

                            </v-window-item>
                        </v-window>
                    </v-card-text>
                </v-card>
            </template>
            </v-container>
        </template>
    </LayoutSinglePage>
</template>
<script setup>
import { ref, reactive, onMounted, watch, provide, computed } from 'vue';
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';
import moment from 'moment';
import { useRouter, useRoute } from 'vue-router';
import * as m_var from '@/global/maintenance'
import * as pub_var from '@/global/global'

/** Import Components */
// import PMPrint from '@/components/Print/PMPrint.vue';
import PMCustomerDetails from '@/components/PreventiveMaintenance/PMComponents/PMCustomerDetails.vue';
import InternalServicingActivity from '@/components/Approver/EH/InternalServicingActivity.vue';
import ServiceReportData from '@/components/Approver/EH/ServiceReportData.vue';
import PMDelegateEngineer from '@/components/PreventiveMaintenance/PMComponents/PMDelegateEngineer.vue';

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
const tab = ref('delegation_details')

/** Permissions */
import { permit } from '@/castl/permitted';
const { can } = permit()

import { A_EH, A_PM, PM } from '@/global/modules';
import PMAcceptDecline from '@/components/PreventiveMaintenance/PMComponents/PMAcceptDecline.vue';
import PMPrint from '@/components/Print/PMPrint.vue';
import VDialog from '@/components/common/VDialog.vue';
import ServiceReportForm from '@/components/Approver/EH/ServiceReportForm.vue';


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
const id = parseInt(route.params.id)

/** Signature */
const signature = ref(null)
const setSignature = (data) => {
    signature.value = data
}




/** Get PM Data By Row ID */
const pm_data = ref({})
const task_delegation = ref({})
const latestTaskDelegation = ref({})
const actions_taken = ref([])
const spareparts = ref([])
const getPMDataByRowID = async () => {
    loading.value = true
    try {
        const response = await apiRequest.get('get_pm_record_details', {
            params: {
                id: id,
                module_type: A_PM
            }
        });
        if (response?.data?.pm) {
            const result = response.data.pm
            pm_data.value = response.data.pm

            task_delegation.value = result.task_delegation
            actions_taken.value = result.task_delegation?.actions_taken
            spareparts.value = result.task_delegation?.spareparts
            latestTaskDelegation.value = result.latest_task_delegation

        } else toast.error('Something went wrong')
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false
    }
};


/** Delegate Engineer ->  */  //subject to edit later tonight -------------------->>>>>>
const rolesToAccess = [pub_var.TLRoleID, pub_var.SBUServiceAssistantID, pub_var.engineerRoleID]
const user_sbu = user.user?.user_roles?.find(v => rolesToAccess.includes(v.role_id))?.SBU
const canDelegate = computed(() => {
    if ((pm_data.value.status === m_var.Scheduled || pm_data.value?.status === m_var.Declined)
        && pm_data.value?.equipment?.sbu === user_sbu) return true
    return false //subject to change the scheduled status to waiting for schedule
})

/** In Transit */
const dialogSure = ref(false)
const inTransit = async () => {
    btnLoading.value = true
    btnDisable.value = true
    try {
        const res = await apiRequest.post('in-transit', {
            id: id,
            delegation_id: task_delegation.value?.id,
        })
        if (res?.data?.success) {
            getPMDataByRowID()
            toast.success('Successfull')
        }
    } catch (error) {
        console.log(error)
        btnLoading.value = false
        btnDisable.value = false
    } finally {
        btnLoading.value = false
        btnDisable.value = false
    }
}


/** Start Task */
const startPMTask = async () => {
    btnLoading.value = true
    try {
        const res = await apiRequest.post('start-task', {
            id: id,
            delegation_id: task_delegation.value?.id,
            in_transit: task_delegation.value?.task_activity?.find(v => v.status === 'In Transit')?.acted_at
        })
        if (res.data && res.data.success) {
            toast.success('Successfull')
            getPMDataByRowID()
        }
    } catch (error) {
        console.log(error)
        btnLoading.value = false
    } finally {
        btnLoading.value = false
    }
}

/** Servie Report Details */
// const markAsCompletedForm = ref(false)
const serviceFormRef = ref(null)
const service_report_data = ref({
    status_after_service: '',
    fields: [],
    spareparts: [],
    remarks : '',
    complaint : '',
    problem : '',
})


/** ========================== Set as Completed ======================== */
const markAsCompleted = async () => {
    btnLoading.value = true
    btnDisable.value = true
    const valid = await serviceFormRef.value?.validateServiceForm()
    if (!valid) {
        btnLoading.value = false
        btnDisable.value = false
        return
    }
    if (service_report_data.value.fields?.length === 0) {
        toast.error('Required actions taken')
        btnLoading.value = false
        btnDisable.value = false
        return
    }
    try {
        const response = await apiRequest.post('mark_as_completed', {
            id: id,
            delegation_id: task_delegation.value?.id,
            ...service_report_data.value
        })
        if (response?.data?.success) {
            toast.success('Completed successfully')
            getPMDataByRowID()
        } else {
            toast.error(response.data.error)
        }
    } catch (error) {
        console.log(error)
    } finally {
        btnLoading.value = false
        btnDisable.value = false
    }
}









/** Accept Decline the Delegation */
const canAcceptDecline = computed(() => {
    const assigned_to = task_delegation.value?.assigned_to
    // const isDelegated = task_delegation.value?.status === 'Delegated'
    const user_id = user.user.id
    return assigned_to === user_id
})


const refreshData = (data) => {
    if (data === true) getPMDataByRowID()
}



onMounted(async () => {
    getPMDataByRowID();
});
</script>


<style scoped>
.v-list-item-title {
    white-space: pre-wrap !important;
}
</style>