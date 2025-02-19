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
                            <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="tonal"
                                class="text-none mr-2">
                                Disapprove</v-btn>
                        </template>
                        <v-card text="" title="Disapprove">
                            <v-col cols="12">
                                <v-textarea class="mr-2 ml-2" v-model="disApproveRemark" clearable
                                    label="Reason of Disapproval" color="primary" variant="outlined"></v-textarea>
                            </v-col>
                            <template v-slot:actions>
                                <v-row justify="end">
                                    <v-btn elevation="2" @click="dialog = false" background-color="red" color="#191970"
                                        class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                        Cancel</v-btn>
                                    <v-btn @click="disapproveRequest" color="primary" elevation="2"
                                        class="text-none mr-3"
                                        style="background-color: #191970;color: #fff!important;"><v-icon
                                            class="mr-1">mdi-text-box-remove-outline</v-icon> Disapprove</v-btn>
                                </v-row>
                            </template>
                        </v-card>
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
                                    <v-btn variant="tonal" @click="dialogApprove = false" color="primary"
                                        class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                    <v-card class="mt-5" elevation="0">
                        <v-chip class="ma-2" color="purple" label>
                            <strong>&nbsp;{{ pub_var.getApproverByLevel(request.level, 1) }}</strong>
                            <v-tooltip activator="parent" location="bottom">Pending approval</v-tooltip>
                        </v-chip>
                        <v-chip class="ma-2" :color="pub_var.setJOStatus(request.main_status).color" label>
                            <v-tooltip activator="parent" location="bottom">Status</v-tooltip>
                            <strong>&nbsp;{{ pub_var.setJOStatus(request.main_status).text }}</strong>
                        </v-chip>
                    </v-card>
                </transition>
                <InternalServicingDelegation v-if="showInternalProcess" :service_id="id"
                    @refresh="refreshData" :key="refreshKey" />



                <RequestDetails :request_data="request" />

                <RequestType :request_data="request" />
            </template>

            <v-card class="mt-10">
                <v-tabs v-model="tab" density="compact" class="border-b-sm" bg-color="grey-lighten-5">
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
                            <RequestedEquipments :service_id="id" @set-serial="getSerialNumber"
                                @get-equipment="getEquipments"
                                :editSerial="request.level !== IT_DEPARTMENT ? false : true" :category="pub_var.EH" />
                        </v-window-item>
                        <v-window-item value="history">
                            <ApproverHistoryLog :service_id="id" :status="request.main_status" />
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
import { service_department } from '@/global/department';
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

    if (user.user.approval_level.includes(IT_DEPARTMENT)) {
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
const disApproveRemark = ref('')
const disapproveRequest = async () => {
    if (disApproveRemark.value === '') {
        toast.error('Please provide a reason for disapproval')
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

    }
}


/** ========================== Delegation of Engineers ======================== */
/** Fetch All Service Engineers */
import { users_engineers } from '@/helpers/getUsers';
const { engineers } = users_engineers()

/**
 * Submit || Delegate Internal Servicing
 */
// const Engineers = ref('')
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



    /** ================================================= Get Request Data ==================================== */
    const loading = ref(false)
    const request = ref({})
    const getRequest = async () => {
        try {
            loading.value = true
            const response = await apiRequest.get('get-specific-equipment-handling', {
                params: {
                    service_id: id,
                },
            });
            if (response?.data?.request) {
                request.value = response.data.request

                // emit('get-EH-details', field)

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
        submmitApproveStatus.value = serialNumber.value.every(data => data.serial && data.serial.trim() !== '')
    }


    /** get Equipments in Requested Equipments Component */
    const getEquipments = (data) => {
        equipment_sbu.value = data.map(v => v.sbu)
    }

    /** Show the Start of Internal Process = Delegation of Service Enginner */
    const user_sbu_as_approver = user.user.user_roles.find(v => v.role_id === pub_var.approverRoleID)?.SBU
    const equipment_sbu = ref([])
    const showInternalProcess = computed(() => { //logic for Internal process or Delegation to start servicing
        if (request.value.level === pub_var.SERVICE_TL && request.value.main_status !== pub_var.INTERNAL_SERVICING) {
            return equipment_sbu.value.includes(user_sbu_as_approver) && !request.value.bypass
        }
        return false
    })


    /** Check if Department is [Sales or Service] ->  */
    const canApprove = computed(() => {
        if (user.user.approval_level.includes(request.value.level)) {
            if (request.value.level === pub_var.SM_SER) {
                if (user.user?.department === service_department) { //for service dept.
                    return equipment_sbu.value.includes(user_sbu_as_approver)
                }
                return user.user?.department === request.value.department // for sales or other department
            }
            if (request.value.level === pub_var.SERVICE_TL) {
                return equipment_sbu.value.includes(user_sbu_as_approver)
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
        if (request.value.level === pub_var.OUTBOUND) {
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