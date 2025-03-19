<template>
    <LayoutSinglePage>
        <template #topBarFixed>
            <div>
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
            </div>
            <v-spacer></v-spacer>
            <div v-if="internalInProgress">
                <p class="text-error">* Internal Processing in Progress</p>
            </div>
            <div v-else>
                <template v-if="can('approve', 'Equipment Handling') && canApprove">
                    <v-dialog v-model="dialog" max-width="600" persistent>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="plain"
                                class="text-none mr-2">
                                Disapprove</v-btn>
                        </template>
                        <v-form @submit.prevent="disapproveRequest" ref="formDisapprove">
                            <v-card text="" title="Disapprove">
                                <v-col cols="12">
                                    <v-textarea class="mr-2 ml-2" v-model="disApproveRemark" :rules="[
                                        v => !!v?.trim() || 'Please provide a reason for disapproval'
                                    ]" clearable label="Reason of Disapproval" color="primary"
                                        variant="outlined"></v-textarea>
                                </v-col>
                                <template v-slot:actions>
                                    <v-row justify="end">
                                        <v-btn @click="dialog = false" class="text-none mr-2">
                                            Cancel</v-btn>
                                        <v-btn type="submit" color="primary" elevation="2" class="text-none mr-3"
                                            style="background-color: #191970;color: #fff!important;">Disapprove</v-btn>
                                    </v-row>
                                </template>
                            </v-card>
                        </v-form>
                    </v-dialog>

                    <!-- Approve Button -->
                    <v-dialog v-model="dialogApprove" max-width="600" persistent>
                        <template v-slot:activator="{ props: activatorProps }">

                            <v-btn type="button" v-bind="activatorProps" :disabled="btnDisable" color="primary"
                                variant="flat" class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                                Approve</v-btn>
                        </template>
                        <v-card text="" title="Approve">
                            <v-form @submit.prevent="approveRequest" ref="form">
                                <v-col cols="12">
                                    <v-textarea class="mr-2 ml-2" v-model="remark" clearable label="Remarks (optional)"
                                        color="primary" variant="outlined"></v-textarea>

                                    <template v-if="outboundSetting">
                                        <v-card class="mr-2 ml-2 mt-5">
                                            <v-radio-group v-model="outboundFinalization.receiving_option"
                                                :rules="[v => !!v || 'Required']" label="Set receiving option"
                                                color="primary">
                                                <v-radio label="Door to Door" value="door"></v-radio>
                                                <v-radio label="Pickup" value="pickup"></v-radio>
                                            </v-radio-group>
                                        </v-card>
                                        <v-text-field type="date" class="mr-2 ml-2 mt-5"
                                            v-model="outboundFinalization.final_installation_date" variant="outlined"
                                            color="primary" density="compact" :rules="[v => !!v || 'Required']"
                                            label="Final Installation Date"></v-text-field>

                                        <v-text-field class="mr-2 ml-2 mt-5" v-model="outboundFinalization.driver"
                                            variant="outlined" color="primary" density="compact"
                                            :rules="[v => !!v || 'Required']" label="Driver"></v-text-field>

                                    </template>
                                </v-col>
                                <!-- <template v-slot:actions> -->
                                <v-divider></v-divider>
                                <v-row justify="end" class="mt-7 mb-5 pr-3">
                                    <v-btn variant="plain" @click="dialogApprove = false" color="primary"
                                        class="text-none mr-2">
                                        Cancel</v-btn>
                                    <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="#191970"
                                        flat class="text-none bg-primary mr-5"><v-icon class="mr-2">mdi-check</v-icon>
                                        Approve</v-btn>
                                </v-row>
                                <!-- </template> -->
                            </v-form>
                        </v-card>
                    </v-dialog>
                </template>
            </div>
        </template>
        <!-- <v-form ref="form"> @submit.prevent="submitWorkOrder" ref="form" -->
        <v-container class="container-form mt-3">

            <template v-if="loading">
                <v-row>
                    <v-col cols="6"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    <v-col cols="6"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>

                    <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>

                    <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                    <v-col cols="4"><v-skeleton-loader type="list-item-two-line"></v-skeleton-loader></v-col>
                </v-row>
            </template>
            <template v-else>
                <transition name="slide-x-transition">
                    <v-card class="mt-5 statusVCard" elevation="0">
                        <v-chip class="ma-2" color="purple" label>
                            <i>&nbsp;{{ pub_var.getApproverByLevel(request.level, 1) ?? '---' }}</i>
                            <v-tooltip activator="parent" location="bottom">Pending approval</v-tooltip>
                        </v-chip>
                        <v-chip class="ma-2" :color="pub_var.setJOStatus(request.main_status).color" label>
                            <v-tooltip activator="parent" location="bottom">Status</v-tooltip>
                            <strong>&nbsp;{{ pub_var.setJOStatus(request.main_status).text }}</strong>
                        </v-chip>
                        <PMPrint :data="request" />
                    </v-card>
                </transition>
                <InternalServicingDelegation v-if="showInternalProcess" :service_id="id" @refresh="refreshData"
                    :key="refreshKey" />



                <RequestDetails :request_data="details" />

                <RequestType :request_data="request" />
            </template>

            <v-card class="mt-10">
                <v-tabs v-model="tab" density="compact" class="border-b-sm">
                    <v-tab value="equipments" class="text-none"><v-icon class="mr-2">mdi-hammer-screwdriver</v-icon>
                        Requested
                        Equipments</v-tab>
                    <v-tab value="history" class="text-none"><v-icon class="mr-2">mdi-account-details</v-icon>
                        Approval Requirements</v-tab>
                </v-tabs>

                <v-card-text>
                    <v-window v-model="tab" :disabled="true">
                        <!-- <v-window-item value="request_type"></v-window-item> -->
                        <v-window-item value="equipments">
                            <RequestedEquipments :equipments="equipments" :peripherals="peripherals"
                                @set-serial="getSerialNumber"
                                :editSerial="request.level !== IT_DEPARTMENT ? false : true" />
                        </v-window-item>
                        <v-window-item value="history">
                            <ApproverHistoryLog :history="history" :status="request.main_status" />
                        </v-window-item>
                    </v-window>
                </v-card-text>
            </v-card>

        </v-container>
        <!-- </v-form> -->
    </LayoutSinglePage>
</template>
<script setup>
import { ref, watch, onMounted, provide, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useDisplay } from 'vuetify'
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';

import { user_data } from '@/stores/auth/userData';
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import RequestType from '@/components/Approver/EH/RequestType.vue';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue';
import ApproverHistoryLog from '@/components/Approver/EH/ApproverHistoryLog.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue';
import * as pub_var from '@/global/global'
import { IT_DEPARTMENT } from '@/global/global';

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

/** Get Role Store */
const role = getRole()
const currentUserRole = role.currentUserRole


/** CAstl Permission */
import { permit } from '@/castl/permitted';
import InternalServicingDelegation from '@/components/Approver/EH/InternalServicingDelegation.vue';
import { service_department, sm_department } from '@/global/department';
import PMPrint from '@/components/Print/PMPrint.vue';
import { A_EH } from '@/global/modules';
const { can } = permit()


/** data declarations */
const router = useRouter()
const route = useRoute()
const { width } = useDisplay()
const user = user_data()

const apiRequest = apiRequestAxios()

const tab = ref('equipments') //TAB

const form = ref(false)
const dialog = ref(false)
const dialogApprove = ref(false)
const delegateDialog = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const id = parseInt(route.params.id)
// const service_id = ref(id)
const serialNumber = ref([])
const submmitApproveStatus = ref(false)
const remark = ref('')
const level = ref(null)
const main_status = ref(0)

const Engineers = ref('')

const outboundFinalization = ref({
    receiving_option: '',
    final_installation_date: '',
    driver: ''
})


/**BreadCrumbs */
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/equipment-handling', display: 'block' },
    { title: 'Equipment Handling', disabled: true, href: '', display: 'none' },
    { title: 'Work Order', disabled: true, href: '', display: 'none' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};


/** Data functions */
const approveRequest = async () => {
    btnLoading.value = true

    if (user.user.approval_level.includes(IT_DEPARTMENT) && request.value?.level === IT_DEPARTMENT) {
        if (!submmitApproveStatus.value) {
            toast.error('Please ensure all serial number fields are filled in')
            btnLoading.value = false
            return
        }
    }

    /**Check if Form is Valid */
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('approve-request', {
            id: user.user.id,
            serial: serialNumber.value,
            service_id: id,
            engineer: Engineers.value.value,
            level: request.value.level,
            remark: remark.value,
            ...outboundFinalization.value,
            // actionsDone: actionsDone ?? []

        })
        if (response.data && response.data.success) {
            btnDisable.value = true
            toast.success('Approved successfully')
            router.push('/equipment-handling')
        }
    } catch (error) {
        if (error.response.data && error.response.data.errorMisMatch) {
            toast.error(error.response.data.errorMisMatch)
            getRequest()
        }
        console.log(error)
    }
    finally {
        btnLoading.value = false
    }
}

/** 
 * Disapprove Request
 * */
const formDisapprove = ref(false)
const disApproveRemark = ref('')
const disapproveRequest = async () => {
    btnDisable.value = true
    btnLoading.value = true

    const { valid } = formDisapprove.value.validate()
    if (!valid) {
        btnDisable.value = false
        btnLoading.value = false

        return
    }
    try {
        const response = await apiRequest.post('disapprove-request', {
            service_id: id,
            remark: disApproveRemark.value,
        })

        if (response.data && response.data.success) {
            toast.success('Disapproval completed successfully')
            router.push('/equipment-handling')
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        btnDisable.value = false
        btnLoading.value = false

    }
}



/** ================================================= Get Request Data ==================================== */
const loading = ref(false)
const request = ref({})
const details = ref({})
const request_type = ref({})
const equipments = ref([])
const peripherals = ref([])
const history = ref([])
const requestor_data = ref([])
const getRequest = async () => {
    try {
        loading.value = true
        const response = await apiRequest.get('get-specific-equipment-handling', {
            params: {
                service_id: id,
                module_type: A_EH
            },
        });
        if (response?.data?.request) {
            const result = response.data.request
            request.value = result

            details.value = {
                full_name: result.users?.full_name,
                institution: result.institution?.name,
                address: result.institution?.address,
                proposed_delivery_date: result.proposed_delivery_date,
                created_at: result.created_at,
            }

            equipments.value = result.equipments
            peripherals.value = result.peripherals

            history.value = result.approval_logs?.filter(v => ![18, 19, 20].includes(v.level) && v.acted_at !== null)
                .map(data => {
                    const serviceApprover = data?.approvers?.filter(v => v.users?.department === service_department)
                    const salesApprovers = data?.approvers?.filter(v => v.users?.department === sm_department)
                    const approverBySatellite = data?.approvers?.filter(v => v.satellite === result?.satellite)
                    const getEquipmentsSbu = result?.equipments.map(d => d.master_data?.sbu) || []
                    const filteredByServiceDepartmentSBU = serviceApprover.filter(f => getEquipmentsSbu.includes(f.sbu))

                    if (data.level === pub_var.OUTBOUND) {
                        return { ...data, driver: result?.driver }
                    }
                    if (data.level === pub_var.SM_SER) {
                        if (result.users?.department === service_department) {
                            return { ...data, approvers: filteredByServiceDepartmentSBU }
                        }
                        return { ...data, approvers: salesApprovers }
                    }
                    if (data.level === pub_var.SERVICE_TL) {
                        return { ...data, approvers: filteredByServiceDepartmentSBU }
                    }
                    if (pub_var.satelliteOfficesLevel.includes(data.level)) {
                        return { ...data, approvers: approverBySatellite }
                    }

                    return data
                });

            requestor_data.value = result.users


        } else {
            alert('Something went wrong')
        }

    } catch (error) {
        console.log(error)
    }
    finally {
        loading.value = false
    }
};

/**
 * Get Serial Number Input from Additional Peripheral Table
 */
const getSerialNumber = (serial) => {
    serialNumber.value = Array.from(serial)
    submmitApproveStatus.value = serialNumber.value?.every(data => data.serial && data.serial.trim() !== '')
}


/** Show the Start of Internal Process = Delegation of Service Enginner */
const user_sbu_as_approver = user.user.user_roles.find(v => v.role_id === pub_var.approverRoleID)?.SBU
const user_satellite = user.user.user_roles.find(v => v.role_id === pub_var.approverRoleID)?.satellite
const equipment_sbu = computed(() => {
    return equipments.value.map(v => v.master_data?.sbu)
})
const showInternalProcess = computed(() => { //logic for Internal process or Delegation to start servicing
    if (request.value.level === pub_var.SERVICE_TL && request.value.main_status !== pub_var.INTERNAL_SERVICING && currentUserRole === pub_var.approverRoleID) {
        return (equipment_sbu.value.includes(user_sbu_as_approver)
            || user.user?.user_roles?.some(v => v.role_id === pub_var.NationalServiceManagerID))
            && !request.value.bypass
    }
    return false
})


/** Check if Department is [Sales or Service] ->  */
const canApprove = computed(() => {
    if (user.user.approval_level.includes(request.value.level) && currentUserRole === pub_var.approverRoleID && request.value?.main_status !== pub_var.DISAPPROVED) {
        if (request.value.level === pub_var.SM_SER) {
            if (user.user?.department === service_department) { //for service dept.
                return equipment_sbu.value.includes(user_sbu_as_approver) || user.user?.user_roles?.some(v => v.role_id === pub_var.NationalServiceManagerID)
            }
            return user.user?.department === requestor_data.value?.department // for sales or other department
        }
        if (request.value.level === pub_var.SERVICE_TL) {
            return equipment_sbu.value.includes(user_sbu_as_approver) || user.user?.user_roles?.some(v => v.role_id === pub_var.NationalServiceManagerID)
        }
        if (pub_var.satelliteOfficesLevel.includes(request.value.level)) { //check if the user satellite(declared in roles management) is equal to the declared satellite office
            return request.value?.satellite === user_satellite
        }
        return true
    }
    return false
})

/** Check if Status is [Internal Servicing In Progress] ->  */
const internalInProgress = computed(() => {
    if (request.value.main_status === pub_var.INTERNAL_SERVICING) return true
    return false
})

/** Outbound Settings [Driver, Receiving Options, Final Installation Date]  */
const outboundSetting = computed(() => {
    if (request.value.level === pub_var.OUTBOUND
        && currentUserRole === pub_var.approverRoleID
        && request.value?.request_type === pub_var.requestTypeShipment
    ) {
        return true
    }
    return false
})

/** Refresh after Submitting Internal Servicing */
const refreshKey = ref(0)
const refreshData = () => {
    getRequest()
    refreshKey.value += 1
}




/** Disable Approver Button */
const disableButton = () => {
    btnDisable.value = true
}
provide('disableButton', disableButton)

onMounted(async () => {
    btnDisable.value = true
    if (user.user.approval_level !== 1) {
        btnDisable.value = false
    }
    btnDisable.value = false


    getRequest()
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

.v-label {
    color: #222;
}
</style>