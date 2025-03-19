<template>
    <LayoutSinglePage>
        <template #topBarFixed>
            <v-breadcrumbs class="pt-7">
                <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index"
                    :class="{ 'custom-pointer': !item.disabled }"
                    :style="{ 'display': width <= 768 ? item.display : '' }" @click="navigateTo(item)"
                    :disabled="item.disabled">
                    {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                </v-breadcrumbs-item>
                <v-btn @click="refreshData" variant="plain" color="grey"><v-icon>mdi-refresh</v-icon>
                    <v-tooltip activator="parent" location="bottom">Refresh</v-tooltip>
                </v-btn>
            </v-breadcrumbs>
            <v-spacer></v-spacer>

            <!-- Actions for Warehouse Personnel -->
            <v-progress-circular indeterminate size="20" width="1" color="primary" v-if="loading"></v-progress-circular>
            <div v-if="canApproveByWIM">
                <v-dialog v-model="dialog" max-width="500" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="flat"
                            class="text-none mr-2"><v-icon class="mr-2">mdi-check-bold</v-icon>
                            Approve</v-btn>
                    </template>
                    <v-card text="" title="">
                        <v-col cols="12">
                            <v-textarea class="mr-2 ml-2" v-model="declineRemark" clearable label="Remarks(optional)"
                                color="primary" variant="outlined"></v-textarea>
                        </v-col>
                        <template v-slot:actions>
                            <v-row justify="end">
                                <v-btn @click="dialog = false" background-color="red" color="#191970"
                                    class="text-none mr-2">
                                    Cancel</v-btn>
                                <v-btn @click="approveByWIM" :loading="btnSubmitLoading" :disabled="btnDisable"
                                    color="primary" elevation="2" class="text-none mr-3"
                                    style="background-color: #191970;color: #fff!important;"><v-icon
                                        class="mr-1">mdi-check</v-icon>
                                    Approve</v-btn>
                            </v-row>
                        </template>
                    </v-card>
                </v-dialog>
            </div>

            <!-- Actions for SBU Head -->
            <div v-if="canRedelegate">
                <v-dialog v-model="dialogRedelegate" max-width="400" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="flat"
                            class="text-none mr-2"><v-icon class="mr-2">mdi-account-sync-outline</v-icon>
                            Redelegate</v-btn>
                    </template>
                    <v-card>
                        <v-form @submit.prevent="delegateInternalServicing" ref="formRedelegate">
                            <v-col cols="12">
                                <h5 class="mt-3" style="font-weight: 500;color: #191970;">Internal Servicing -
                                    Delegation of Service
                                </h5>
                                <v-divider></v-divider>

                                <v-combobox color="primary" class="mt-5" v-model="Engineers" clearable
                                    label="Delegate to" placeholder="Delegate to" density="compact" :items="engineers"
                                    variant="outlined" itemValue="value" :rules="[
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
                                        <span class="small text-primary" style="font-size: 12px;"><b>{{ item.title
                                                }}</b> <span class="text-disabled" v-if="item.title">- [SBU : {{
                                                    item.raw.sbu }}]</span></span>
                                    </template>
                                </v-combobox>
                            </v-col>

                            <v-divider class="mb-5"></v-divider>
                            <v-row justify="end" class="mb-3">
                                <v-btn @click="dialogRedelegate = false" size="small" class="text-none mr-2">
                                    Cancel</v-btn>
                                <v-btn type="submit" size="small" :loading="btnSubmitLoading" :disabled="btnDisable"
                                    color="#191970" class="text-none bg-primary mr-5"><v-icon
                                        class="mr-2">mdi-check</v-icon>
                                    Redelegate</v-btn>
                            </v-row>
                        </v-form>
                    </v-card>
                </v-dialog>
                <v-dialog v-model="forStorageDialog" v-if="canRedelegateSentWIM" max-width="400" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="tonal"
                            class="text-none mr-2"><v-icon class="mr-2">mdi-server</v-icon>
                            For Storage</v-btn>
                    </template>
                    <v-card text="Send to the warehouse for storage?" title="">
                        <template v-slot:actions>
                            <v-spacer></v-spacer>
                            <v-btn @click="forStorageDialog = false" elevation="0" class="text-none mr-2">
                                Cancel</v-btn>
                            <v-btn size="small" @click="forStorage" :loading="btnSubmitLoading" :disabled="btnDisable"
                                color="#191970" class="text-none bg-primary"><v-icon class="mr-1">mdi-check</v-icon>
                                Yes, for storage</v-btn>
                        </template>
                    </v-card>
                </v-dialog>
            </div>

            <!-- Actions for Engineers -->
            <div v-if="can('installer', IS)">
                <span v-if="canAcceptDecline">
                    <v-dialog v-model="dialog" max-width="400" persistent>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="plain"
                                class="text-none mr-2">
                                Decline</v-btn>
                        </template>
                        <v-card text="" title="Decline">
                            <v-col cols="12">
                                <v-textarea class="mr-2 ml-2" v-model="declineRemark" clearable label="Reason"
                                    color="primary" variant="outlined"></v-textarea>
                            </v-col>
                            <template v-slot:actions>
                                <v-row justify="end">
                                    <v-btn @click="dialog = false" background-color="red" color="#191970"
                                        class="text-none mr-2">
                                        Cancel</v-btn>
                                    <v-btn @click="declineTask" :loading="btnSubmitLoading" :disabled="btnDisable"
                                        color="primary" class="text-none mr-3"
                                        style="background-color: #191970;color: #fff!important;">
                                        Decline</v-btn>
                                </v-row>
                            </template>
                        </v-card>
                    </v-dialog>

                    <!-- Approve Button -->
                    <v-btn type="button" :loading="btnSubmitLoading" :disabled="btnDisable" color="primary"
                        variant="flat" class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                        Accept
                        <v-dialog activator="parent" width="500">
                            <template v-slot:default="{ isActive }">
                                <v-card text="" title="">
                                    <v-col cols="12">
                                        <v-textarea class="mr-2 ml-2" v-model="acceptRemark" clearable
                                            label="Remark (optional)" color="primary" variant="outlined"></v-textarea>
                                    </v-col>
                                    <template v-slot:actions>
                                        <v-row justify="end">
                                            <v-btn @click="isActive.value = false" background-color="red"
                                                color="#191970" class="text-none mr-2">
                                                Cancel</v-btn>
                                            <v-btn @click="acceptInternalRequest" :loading="btnSubmitLoading"
                                                :disabled="btnDisable" color="primary" elevation="2"
                                                class="text-none mr-3"
                                                style="background-color: #191970;color: #fff!important;">Accept</v-btn>
                                        </v-row>
                                    </template>
                                </v-card>
                            </template>
                        </v-dialog>
                    </v-btn>
                </span>

                <span v-if="completeTheAction">
                    <!-- Approve Button -->
                    <v-btn type="button" @click="markAsCompleted" :loading="btnSubmitLoading" :disabled="btnDisable"
                        color="primary" variant="flat" class="text-none btnSubmit"><v-icon
                            class="mr-2">mdi-check-bold</v-icon>
                        Set as Completed</v-btn>
                </span>
            </div>
        </template>

        <template #default>
            <v-container class="container-form mt-7">
                <v-alert v-if="canRedelegateSentWIM" closable density="compact" color="warning" variant="tonal">
                    You have the option to either redelegate the machine for internal servicing or move it to storage
                </v-alert>
                <transition name="slide-x-transition">
                    <v-card class="mt-5 statusVCard" elevation="0" v-if="!loading">
                        <v-chip class="ma-2" :color="pub_var.getInternalStatus(status).color" label>
                            <v-icon icon="mdi-circle-medium" start></v-icon>
                            Status:
                            <strong>&nbsp;{{ pub_var.getInternalStatus(status).text }}</strong>
                        </v-chip>
                        <v-chip class="ma-2" color="red" label
                            v-if="internalData?.option_type && status === pub_var.I_WAREHOUSE">
                            <strong>&nbsp;For {{ _.upperFirst(internalData?.option_type) ?? '---' }}</strong>
                        </v-chip>

                        <PMPrint :data="internalData" />
                    </v-card>
                </transition>
                <!-- Checklist Item Form -->
                <v-form ref="formItem">
                    <v-card text="" title="" class="mb-5" v-if="can('installer', IS) && completeTheAction">
                        <v-col cols="12">
                            <b class="text-primary ml-1">Checklist Items</b>

                            <v-radio-group inline v-model="option_type" :rules="[v => !!v || 'Required']">
                                <v-radio label="For Storage" value="storage"></v-radio>
                                <v-radio label="For Installation" value="installation"></v-radio>
                            </v-radio-group>

                            <table class="table table-sm table-bordered mt-3">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(selected, index) in selectedItems" v-if="selectedItems.length > 0"
                                        :key="index">
                                        <td>{{ selected.item }}</td>
                                        <td class="pa-0 w-25">
                                            <v-text-field class="ml-2" :rules="[
                                                v => !!v?.trim() || 'Required',
                                                v => !isNaN(v) || 'Mus tbe a number'
                                            ]" variant="plain" v-model="selected.qty" label="qty" density="compact"
                                                single-line></v-text-field>
                                        </td>
                                        <td class="pa-0 w-25"><v-text-field class="ml-2" variant="plain"
                                                v-model="selected.remarks" label="remarks" density="compact"
                                                single-line></v-text-field>
                                        </td>
                                    </tr>
                                    <tr v-else>
                                        <td colspan="2">No records found</td>
                                    </tr>
                                </tbody>
                            </table>
                            <v-dialog v-model="dialogItems" max-width="500">
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn type="button" v-bind="activatorProps" :disabled="btnDisable"
                                        density="compact" color="primary" variant="tonal" class="text-none mt-5">Select
                                        Items</v-btn>
                                </template>
                                <v-card class="pa-5">
                                    <v-row>
                                        <v-col cols="12" md="12" sm="12">
                                            <b class="text-primary ml-1">Select Items</b>
                                            <vue3-datatable ref="datatable" :rows="checkListItems" :columns="cols"
                                                :loading="loading" :selectRowOnClick="true" :hasCheckbox="true"
                                                :pagination="false"
                                                skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover"
                                                class="" @rowSelect="rowSelect">
                                            </vue3-datatable>
                                        </v-col>
                                    </v-row>
                                </v-card>
                            </v-dialog>
                        </v-col>
                    </v-card>
                </v-form>

                <ServiceReportForm v-model="service_report_data" ref="serviceFormRef" v-if="canComplete" />

                <v-card class="mt-10">
                    <v-tabs v-model="tab" density="compact" class="border-b-sm d-flex justify-between">
                        <div>
                            <v-tab value="delegation_details" :disabled="loading" class="text-none"
                                color="primary"><v-icon class="mr-2">mdi-gesture-tap-button</v-icon> Delegation
                                Details</v-tab>
                            <v-tab value="service_report" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-poll</v-icon>ServiceReport</v-tab>
                            <v-tab value="item_report" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-chart-bar-stacked</v-icon>
                                Checklist Item</v-tab>
                        </div>
                        <v-spacer></v-spacer>
                        <div class="d-flex">
                            <v-tab value="details" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-tooltip-text-outline</v-icon> Request Details</v-tab>
                            <v-tab value="equipments" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-hammer-screwdriver</v-icon> Requested Equipments</v-tab>
                        </div>
                    </v-tabs>

                    <v-card-text class="vCardText">
                        <v-window v-model="tab" :disabled="true">
                            <v-window-item value="details">
                                <RequestDetails :request_data="equipment_handling" :key="refreshKeyDetail" />
                            </v-window-item>
                            <v-window-item value="equipments">
                                <RequestedEquipments :equipments="equipments" :editSerial="false" />
                            </v-window-item>
                            <v-window-item value="delegation_details">
                                <InternalServicingActivity :id="id" :key="refreshKey" :internalData="internalData" />
                            </v-window-item>
                            <v-window-item value="item_report">
                                <ItemReportData :task_delegation="task_delegation" :items_acquired="items_acquired" />
                            </v-window-item>
                            <v-window-item value="service_report">
                                <ServiceReportData :task_delegation="task_delegation" :actions_taken="actions_taken" :spareparts="task_delegation?.spareparts" />
                            </v-window-item>
                        </v-window>
                    </v-card-text>
                </v-card>
            </v-container>
        </template>
    </LayoutSinglePage>
</template>
<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import _ from 'lodash';

const role = getRole()
const currentUserRoleID = role.currentUserRole

import { user_data } from '@/stores/auth/userData';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue'
import * as pub_var from '@/global/global.js'

import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';
import InternalServicingActivity from '@/components/Approver/EH/InternalServicingActivity.vue'

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

import { useActions } from '@/helpers/getActionsTaken';
const { actions_data } = useActions()

import { permit } from '@/castl/permitted';
import { A_IS, EH, IS } from '@/global/modules';
const { can } = permit()


const tab = ref('details')
const tabReport = ref('service_report')


const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/internal-servicing', display: 'block' },
    { title: 'Equipment Handling', disabled: true, href: '', display: 'none' },
    { title: 'Internal Servicing', disabled: true, href: '', display: 'none' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};

/** data declarations */
const route = useRoute()
const router = useRouter()
const user = user_data()
const apiRequest = apiRequestAxios()

const dialog = ref(false)
const dialogItems = ref(false)
const dialogRedelegate = ref(false)
const forStorageDialog = ref(false)
const btnDisable = ref(false)
const btnSubmitLoading = ref(false)
const declineRemark = ref('')
const acceptRemark = ref(null)
const refreshKey = ref(0)
const refreshKeyDetail = ref(0)
// const getStatus = ref(null)

const type_of_activity = ref('')


const id = parseInt(route.params.id)
const service_id = ref(null)
const serialNumber = ref([])
const status = ref(null)


/** Servie Report Details */
const formItem = ref(false)
const option_type = ref('')

/** Selected Row */
const selectedItems = ref([])
const rowSelect = (data) => {
    selectedItems.value = data.map((row) => {
        return {
            id: row.id,
            service_id: row.service_id,
            item: row.item,
            qty: '',
            remarks: ''
        }
    })
}

/** Get Internal Row by ID */
const internalData = ref([])
const actions_taken = ref([])
const items_acquired = ref([])
const task_delegation = ref({})
const equipment_handling = ref({})
const equipments = ref({})
const getInternalRowById = async () => {
    btnDisable.value = true
    loading.value = true
    try {
        const response = await apiRequest.get('getInternalRowById', {
            params: {
                id: id,
                module_type: A_IS
            }
        })
        if (response?.data?.internal_request) {
            btnDisable.value = true
            const data = response.data?.internal_request
            status.value = data?.status
            internalData.value = data
            actions_taken.value = data?.task_delegation?.actions_taken
            task_delegation.value = data?.task_delegation
            items_acquired.value = data?.task_delegation?.items_acquired
            delegation_id.value = data?.task_delegation?.id
            service_id.value = data?.service_id


            const eh = data.equipment_handling
            equipment_handling.value = {
                full_name: eh?.users?.full_name,
                institution: eh?.institution?.name,
                address: eh?.institution?.address,
                proposed_delivery_date: eh?.proposed_delivery_date,
                created_at: eh?.created_at,
            }
            equipments.value = data?.equipment_handling?.equipments
        }
    } catch (error) {
        console.log(error)
    } finally {
        btnDisable.value = false
        loading.value = false
    }
}



/** Fetch All Service Engineers */
import { users_engineers } from '@/helpers/getUsers';
import TextField from '@/components/layout/FormsComponent/TextField.vue';
import { InProgress } from '@/global/maintenance';
const { engineers } = users_engineers()
const Engineers = ref('')

/**
 * Submit || Delegate Internal Servicing
 */
const formRedelegate = ref(false)
const delegateInternalServicing = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    const { valid } = await formRedelegate.value.validate()
    if (!valid) {
        btnSubmitLoading.value = false
        btnDisable.value = false
        return
    }

    try {
        const response = await apiRequest.post('redelegation', {
            internal_id: id,
            delegation_id: delegation_id.value,
            assigned_to: Engineers.value.value
        })
        if (response.data && response.data.success) {
            toast.success('Successfully delegated')
            getInternalRowById()
            tab.value = 'details'
        } else {
            btnDisable.value = false
            dialogRedelegate.value = false
            toast.error(response.data.error)
        }
    } catch (error) {
        dialogRedelegate.value = false
        btnDisable.value = false
        alert(error)
    }
    finally {
        btnDisable.value = false
        dialogRedelegate.value = false
        btnSubmitLoading.value = false
    }
}



/** Decline Internal Request Servicing */
const declineTask = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    if (!declineRemark.value.trim()) {
        toast.error('Please indicate your reason')
        btnDisable.value = false
        btnSubmitLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('decline-internal-request', {
            // service_id: id,
            internal_id: id,
            remarks: declineRemark.value
        })
        if (response.data && response.data.success) {
            toast.success('Successfully declined')
            getInternalRowById()
        } else {
            btnDisable.value = false
            toast.error(response.data.error)
        }
    } catch (error) {
        btnDisable.value = false
        toast.error('Something went wrong')
    } finally {
        btnSubmitLoading.value = false
    }
}


/** Accept Internal Request Servicing */
const delegation_id = ref(null)
const acceptInternalRequest = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('accept-internal-request', {
            service_id: service_id.value,
            internal_id: id,
            delegation_id: delegation_id.value,
            remarks: acceptRemark.value
        })
        if (response.data && response.data.success) {
            toast.success('Successfully accepted')
            getInternalRowById()
        } else {
            btnDisable.value = false
            toast.error(response.data.error)
        }
    } catch (error) {
        btnDisable.value = false
        console.log(error)
    } finally {
        btnSubmitLoading.value = false
    }
}

/** Mark Internal Request Servicing as Completed */

const serviceFormRef = ref(null)
const service_report_data = ref({
    status_after_service: '',
    fields: [],
    spareparts: [],
    remarks: '',
    complaint: '',
    problem: '',
})

const markAsCompleted = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    const validForm = await serviceFormRef.value?.validateServiceForm()
    const { valid } = await formItem.value?.validate()
    if (!validForm || !valid) {
        btnSubmitLoading.value = false
        btnDisable.value = false
        return
    }
    if (service_report_data.value.fields?.length === 0) {
        toast.error('Required actions taken')
        btnSubmitLoading.value = false
        btnDisable.value = false
        return
    }

    try {
        const response = await apiRequest.post('internal-servicing-completed', {
            internal_id: id,
            service_id: service_id.value,
            delegation_id: delegation_id.value,
            items: selectedItems.value,
            option_type: option_type.value,
            ...service_report_data.value
        })
        if (response?.data?.success) {
            toast.success('Completed successfully')
            getInternalRowById()
        } else {
            btnDisable.value = false
            toast.error(response.data.error)
        }
    } catch (error) {
        console.log(error)
    } finally {
        btnSubmitLoading.value = false
        btnDisable.value = false
    }
}

/** Send to warehouse - For Storage */
const forStorage = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('for_storage', {
            internal_id: id,
            service_id: service_id.value,
            delegation_id: delegation_id.value,
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            getInternalRowById()
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        btnSubmitLoading.value = false
        btnDisable.value = false
    }
}



/** Approve by WIM */
const approveByWIM = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('approve_by_wim', {
            internal_id: id,
            service_id: service_id.value,
            option_type: internalData.value?.option_type
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            getInternalRowById()
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        btnSubmitLoading.value = false
        btnDisable.value = false
    }
}


/** Get Checklist Items */
// const rows = ref(null)
const loading = ref(true);
const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'item', title: 'Item' },
    ]) || [];

/** CHecklist Items */
import { useChecklistItem } from '@/helpers/getChecklistItem';
import ItemReportData from '@/components/Approver/EH/ItemReportData.vue';
import ServiceReportData from '@/components/Approver/EH/ServiceReportData.vue';
import ServiceReportForm from '@/components/Approver/EH/ServiceReportForm.vue';
import PMPrint from '@/components/Print/PMPrint.vue';
const { checkListItems } = useChecklistItem()
// const { ItemsAcquired } = useItemsAcquired(id)


/** Show Checklist Item Form */
const completeTheAction = computed(() => {
    return status.value === pub_var.I_INPROGRESS
})




/** Can Accept Decline Logic */
const canAcceptDecline = computed(() => {
    return can('installer', IS)
        && internalData.value.status === pub_var.I_DELEGATED
        && internalData.value.current_assigned_to === user.user.id
})
const canComplete = computed(() => {
    return can('installer', IS)
        && internalData.value.status === pub_var.I_INPROGRESS
        && internalData.value.current_assigned_to === user.user.id
})

/** Can Redelegate Logic */
const canRedelegate = computed(() => {
    return can('delegate', IS)
        && internalData.value.current_assigned_by === user.user.id
        && (internalData.value.status === pub_var.I_RETURNEDHEAD
            || internalData.value.status === pub_var.I_DECLINED);
})

/** Can Redelegate or Sent to WIM Logic */
const canRedelegateSentWIM = computed(() => {
    return can('delegate', IS)
        && internalData.value.current_assigned_by === user.user.id
        && internalData.value.status === pub_var.I_RETURNEDHEAD
})


/** Can Approve by WIM */
const canApproveByWIM = computed(() => {
    return currentUserRoleID === pub_var.wimPersonnelID && status.value === pub_var.I_WAREHOUSE
})

/** Show Report */
const showReport = computed(() => {
    return status.value !== InProgress
})


/** Referesh */
const refreshData = () => {
    getInternalRowById()
}


onMounted(() => {
    getInternalRowById()
})

</script>









<style scoped>
.dp--menu-wrapper {
    position: absolute !important;
}

.hideID {
    visibility: hidden;
    position: absolute;
}

.myInputText {
    position: absolute !important;
}

.vCheckbox {
    height: 40px !important;
}

.selecting_action {
    cursor: pointer;
}

.selecting_action:hover {
    color: #191970;
}
</style>
