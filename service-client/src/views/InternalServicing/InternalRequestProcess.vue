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
                                    class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                                <v-btn @click="dialogRedelegate = false" size="small"
                                    class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                            <v-btn @click="forStorageDialog = false" elevation="0"
                                class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                            <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="tonal"
                                class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                                Decline</v-btn>
                        </template>
                        <v-card text="" title="Decline">
                            <v-col cols="12">
                                <v-textarea class="mr-2 ml-2" v-model="declineRemark" clearable label="Reason"
                                    color="primary" variant="outlined"></v-textarea>
                                <!-- <p class="text-danger ml-3">{{ disApproveRemarkError }}</p> -->
                            </v-col>
                            <template v-slot:actions>
                                <v-row justify="end">
                                    <v-btn @click="dialog = false" background-color="red" color="#191970"
                                        class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                                        <!-- <p class="text-danger ml-3">{{ disApproveRemarkError }}</p> -->
                                    </v-col>
                                    <template v-slot:actions>
                                        <v-row justify="end">
                                            <v-btn @click="isActive.value = false" background-color="red"
                                                color="#191970" class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                    <v-card class="mt-5" elevation="0" v-if="!loading">
                        <v-chip class="ma-2" :color="pub_var.getInternalStatus(status).color" label>
                            <v-icon icon="mdi-circle-medium" start></v-icon>
                            Status:
                            <strong>&nbsp;{{ pub_var.getInternalStatus(status).text }}</strong>
                        </v-chip>
                    </v-card>
                </transition>
                <!-- Checklist Item Form -->
                <v-form @submit.prevent="SubmitMarkAsCompleted" ref="form">
                    <v-card text="" title="" class="mb-5" v-if="can('installer', IS) && completeTheAction">
                        <v-col cols="12">
                            <!-- Checklist Item Form -->
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
                                                v => !!v || 'Required',
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
                                            <vue3-datatable ref="datatable" :rows="rows" :columns="cols"
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

                    <v-card density="compact" class="p-3" elevation="1"
                        v-if="can('installer', IS) && completeTheAction">
                        <v-row>
                            <v-col cols="12">
                                <b class="text-primary">Status after service</b>
                                <v-radio-group v-model="status_after_service" :rules="[v => !!v || 'Required']" inline>
                                    <v-radio label="Operational" value="Operational"></v-radio>
                                    <v-radio label="For further monitoring" value="Further Monitoring"></v-radio>
                                    <v-radio label="Non-operational" value="Non-Operational"></v-radio>
                                </v-radio-group>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col class="d-flex justify-content-between align-items-center">
                                <b class="text-primary">Actions Taken</b>

                                <v-btn @click="addField" class="text-none" color="primary" variant="text"
                                    prepend-icon="mdi-comment-plus-outline">Add
                                    Field</v-btn>
                            </v-col>
                        </v-row>

                        <v-col cols="12">
                            <v-card elevation="0">
                                <v-row>
                                    <v-col cols="12" v-for="(field, index) in fields" v-if="fields.length > 0"
                                        :key="index">
                                        <v-combobox v-model="field.action" :items="actions_data" item-title="actions"
                                            item-value="actions" label="Select an option or enter your action manually"
                                            append-inner-icon="mdi-trash-can-outline" variant="outlined" color="primary"
                                            class="mt-3" density="compact" clearable :rules="[
                                                v => !!v || 'Required',
                                                v => (v && v.length <= 120) || 'Please limit your input to 120 characters'
                                            ]" @click:append-inner="removeField(index)">
                                            <template v-slot:item="{ item, props }">
                                                <p v-bind="props" class="pa-2 selecting_action">
                                                    <v-icon>mdi-circle-medium</v-icon> {{ item.title }}
                                                </p>
                                                <!-- <v-list-item v-bind="props" class="pa-2"></v-list-item> -->
                                            </template>
                                        </v-combobox>
                                    </v-col>
                                    <v-col v-else>
                                        <p>No records found</p>
                                    </v-col>
                                </v-row>
                            </v-card>
                        </v-col>
                    </v-card>
                </v-form>


                <!-- Service Report Tabs -->
                <v-card class="mt-10" v-if="showReport">
                    <v-tabs v-model="tabReport" density="compact" class="border-b-sm d-flex justify-between"
                        bg-color="grey-lighten-5">
                        <v-tab value="service_report" class="text-none" color="primary"><v-icon
                                class="mr-2">mdi-poll</v-icon> Service
                            Report</v-tab>
                        <v-tab value="item_report" class="text-none" color="primary"><v-icon
                                class="mr-2">mdi-chart-bar-stacked</v-icon>
                            Item Report</v-tab>
                    </v-tabs>

                    <v-skeleton-loader :loading="loading" type="article">
                        <v-card-text>
                            <v-window v-model="tabReport">
                                <v-window-item value="service_report">
                                    <v-row>
                                        <v-col cols="12">
                                            <p class="mb-3"><b class="text-grey">Actions Taken</b></p>
                                            <p v-for="action in actions_taken" :key="action"
                                                v-if="actions_taken?.length > 0">
                                                <span class="mt-3">
                                                    <v-icon color="primary">mdi-check</v-icon> {{ action.action }}
                                                </span>
                                            </p>
                                            <p v-else>
                                                <span class="mt-3">
                                                    <v-icon color="grey">mdi-text-box-search-outline</v-icon> <span
                                                        class="text-grey small">No records
                                                        found</span>
                                                </span>
                                            </p>
                                        </v-col>
                                        <v-col cols="12">
                                            <p class="mb-3"><b class="text-grey">Status after service</b></p>
                                            <v-chip label density="comfortable"
                                                v-if="task_delegation?.status_after_service" color="primary"
                                                variant="flat">
                                                {{ task_delegation?.status_after_service }}
                                            </v-chip>
                                            <p v-else>---</p>
                                        </v-col>
                                    </v-row>
                                </v-window-item>
                                <v-window-item value="item_report">
                                    <v-row>
                                        <v-col cols="12">
                                            <v-card v-if="Items.length > 0" class="mt-5">
                                                <v-row class="mb-3">
                                                    <v-col cols="12">
                                                        <p class="mb-2"><b class="text-grey ml-1">Equipment Management
                                                                Actions</b></p>
                                                        <v-chip label density="comfortable"
                                                            v-if="task_delegation?.option_type" color="primary"
                                                            variant="flat">
                                                            For {{ task_delegation?.option_type }}
                                                        </v-chip>
                                                        <p v-else>---</p>
                                                    </v-col>
                                                </v-row>

                                                <!-- Checklist Item Form -->
                                                <b class="text-grey ml-1">Checklist Items</b>
                                                <table class="table table-sm table-bordered mt-3">
                                                    <thead>
                                                        <tr class="text-none">
                                                            <td>Item</td>
                                                            <td>Qty</td>
                                                            <td>Remark</td>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr v-for="(item, index) in Items" :key="index">
                                                            <td>{{ item.item }}</td>
                                                            <td>{{ item.qty }}</td>
                                                            <td>{{ item.remarks }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </v-card>
                                            <v-card v-else>
                                                <span class="mt-3">
                                                    <v-icon color="grey">mdi-text-box-search-outline</v-icon> <span
                                                        class="text-grey">No
                                                        records
                                                        found</span>
                                                </span>
                                            </v-card>
                                        </v-col>
                                    </v-row>
                                </v-window-item>
                            </v-window>
                        </v-card-text>
                    </v-skeleton-loader>
                </v-card>

                <v-card class="mt-10">
                    <v-tabs v-model="tab" density="compact" class="border-b-sm d-flex justify-between"
                        bg-color="grey-lighten-5">
                        <div>
                            <v-tab value="delegation_details" :disabled="loading" class="text-none"
                                color="primary"><v-icon class="mr-2">mdi-gesture-tap-button</v-icon> Delegation
                                Details</v-tab>
                        </div>
                        <v-spacer></v-spacer>
                        <div class="d-flex">
                            <v-tab value="details" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-tooltip-text-outline</v-icon> Request Details</v-tab>
                            <v-tab value="equipments" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-hammer-screwdriver</v-icon> Requested Equipments</v-tab>
                        </div>
                    </v-tabs>

                    <v-card-text>
                        <v-window v-model="tab" :disabled="true">
                            <v-window-item value="details">
                                <RequestDetails :request_data="equipment_handling" :key="refreshKeyDetail" />
                                <!-- @set-status="getStatus"  -->
                            </v-window-item>
                            <v-window-item value="equipments">
                                <RequestedEquipments :service_id="parseInt(service_id)" :editSerial="false"
                                    :category="pub_var.EH" />
                            </v-window-item>
                            <v-window-item value="delegation_details">
                                <InternalServicingActivity :id="parseInt(id)" :key="refreshKey"
                                    :internalData="internalData" />
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
import { IS } from '@/global/modules';
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


const id = route.params.id ?? null
const service_id = ref(null)
const serialNumber = ref([])
const status = ref(null)


/** Servie Report Details */
const form = ref(false)
const option_type = ref('')
const status_after_service = ref('')
/** Actions Taken */
const fields = ref([]);
const addField = () => {
    if (fields.value.length < 8) {
        fields.value.push({
            action: ''
        });
    }
};

const removeField = (index) => {
    fields.value.splice(index, 1);
};



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
const task_delegation = ref({})
const equipment_handling = ref({})
const getInternalRowById = async () => {
    btnDisable.value = true
    try {
        const response = await apiRequest.get('getInternalRowById', {
            params: { id: id }
        })
        if (response.data?.internal_request) {
            btnDisable.value = true
            const data = response.data.internal_request
            status.value = data.status
            internalData.value = data
            actions_taken.value = data?.task_delegation[0]?.actions_taken
            task_delegation.value = data?.task_delegation[0]
            delegation_id.value = data?.task_delegation?.find(v => v.active === 1).id
            service_id.value = data.service_id
            equipment_handling.value = data.equipment_handling
        }
    } catch (error) {
        console.log(error)
    } finally {
        btnDisable.value = false
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
            refreshKey.value += 1;
            btnDisable.value = true
            dialogRedelegate.value = false
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
            tab.value = 'details'
            refreshKey.value += 1;
            btnDisable.value = true
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
            tab.value = 'details'
            refreshKey.value += 1;
            btnDisable.value = true
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
const markAsCompleted = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        btnDisable.value = false
        btnSubmitLoading.value = false
        return
    }

    if (fields.value.length === 0) {
        btnDisable.value = false
        btnSubmitLoading.value = false
        toast.error('Required actions taken')
        return
    }
    const checkIfTheresEmpty = fields.value.some(val => {
        return typeof val === 'string' ? val.trim() === '' : val === undefined || val === null
    })
    if (checkIfTheresEmpty) {
        toast.error('Please fill in required fields')
        btnDisable.value = false
        btnSubmitLoading.value = false
        return
    }

    SubmitMarkAsCompleted()
}
const SubmitMarkAsCompleted = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('internal-servicing-completed', {
            option_type: option_type.value,
            status_after_service: status_after_service.value,
            items: selectedItems.value,
            actions_taken: fields.value,
            delegation_id: delegation_id.value,
            internal_id: id,
            service_id: service_id.value
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            getInternalRowById()
            getItems()
            tab.value = 'details'
            refreshKey.value += 1;
            btnDisable.value = true
        } else {
            btnDisable.value = false
            toast.error(response.data.error)
        }
    } catch (error) {
        console.log(error)
    }
    finally {
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
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            getInternalRowById()
            tab.value = 'details'
            refreshKey.value += 1;
            btnDisable.value = true
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
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            getInternalRowById()
            tab.value = 'details'
            refreshKey.value += 1;
            btnDisable.value = true
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
const rows = ref(null)
const loading = ref(true);
const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'item', title: 'Item' },
    ]) || [];

const getChecklistItem = async () => {
    loading.value = true
    try {
        const response = await apiRequest.get('getChecklistItem');
        if (response.data && response.data.items) {
            rows.value = response.data.items
        }
    } catch (error) {
        console.log(error)
    }
    loading.value = false
};

const Items = ref([])
const getItems = async () => {
    loading.value = true
    try {
        const response = await apiRequest.get('getChecklistItemAcquired', {
            params: { service_id: id }
        });
        if (response.data && response.data.items) {
            Items.value = response.data.items
        }
    } catch (error) {
        console.log(error)
    }
    loading.value = false
};


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


onMounted(() => {
    getChecklistItem()
    getItems()
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
