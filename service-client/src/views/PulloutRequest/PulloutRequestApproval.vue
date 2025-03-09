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
            <v-progress-circular indeterminate size="20" width="1" color="primary" v-if="loading"></v-progress-circular>
            <!-- Approval Template -->
            <template v-if="can('approve', PR) && canApprove">
                <v-dialog v-model="dialog_disapprove" max-width="500" persistent>
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
                                <v-btn elevation="2" @click="dialog_disapprove = false" background-color="red"
                                    color="#191970" class="text-none mr-2">
                                    Cancel</v-btn>
                                <v-btn @click="disapproveRequest" color="primary" elevation="2" class="text-none mr-3"
                                    style="background-color: #191970;color: #fff!important;"><v-icon
                                        class="mr-1">mdi-text-box-remove-outline</v-icon> Disapprove</v-btn>
                            </v-row>
                        </template>
                    </v-card>
                </v-dialog>

                <!-- Approve Button -->
                <v-dialog v-model="dialog_approve" max-width="500" persistent>
                    <template v-slot:activator="{ props: activatorProps }">

                        <v-btn type="button" v-bind="activatorProps" :disabled="btnDisable" color="primary"
                            variant="flat" class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                            Approve</v-btn>
                    </template>
                    <v-card text="" title="Approve">
                        <v-form @submit.prevent="approveRequest" ref="form">
                            <v-col cols="12">
                                <v-card class="" elevation="0" v-if="showSettingSchedule">
                                    <!-- Service Dept. Schedule -->
                                    <v-text-field type="datetime-local" class="mr-2 ml-2 mt-5"
                                        v-model="sched.scheduled_date" variant="outlined" color="primary"
                                        density="compact" :rules="[v => !!v || 'Required']"
                                        label="Schedule"></v-text-field>

                                    <p class="text-error mt-5">* In case of a scheduling mismatch, set the preferred
                                        schedule below</p>
                                    <v-text-field type="datetime-local" class="mr-2 ml-2 mt-5"
                                        v-model="sched.preferred_schedule" variant="outlined" color="primary"
                                        density="compact" :rules="[v => !!v || 'Required']"
                                        label="Preferred Schedule"></v-text-field>
                                    <v-textarea class="mr-2 ml-2 mt-3" v-model="sched.remarks" clearable
                                        label="Remarks (optional)" color="primary" variant="outlined"></v-textarea>
                                </v-card>

                                <v-textarea v-else class="mr-2 ml-2" v-model="remark" clearable
                                    label="Remarks (optional)" color="primary" variant="outlined"></v-textarea>
                            </v-col>
                            <!-- <template v-slot:actions> -->
                            <v-divider></v-divider>
                            <v-row justify="end" class="mt-7 mb-5 pr-3">
                                <v-btn variant="tonal" @click="dialog_approve = false" color="primary"
                                    class="text-none mr-2">
                                    Cancel</v-btn>
                                <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="#191970" flat
                                    class="text-none bg-primary mr-5"><v-icon class="mr-2">mdi-check</v-icon>
                                    Approve</v-btn>
                            </v-row>
                            <!-- </template> -->
                        </v-form>
                    </v-card>
                </v-dialog>
            </template>
        </template>

        <v-container class="container-form mt-3">
            <transition name="scale-transition">
                <v-card class="mt-5 statusVCard" elevation="0" v-if="!loading">
                    <v-chip class="ma-2" size="small" color="purple" label>
                        <strong>&nbsp;{{ pullout_approver(formData.level.value) }}</strong>
                        <v-tooltip activator="parent" location="bottom">
                            Pending approval
                        </v-tooltip>
                    </v-chip>
                    <v-chip class="ma-2" variant="flat" size="small" :color="pullOutStatus(formData.status.value).color" label>
                        <strong>&nbsp; {{ pullOutStatus(formData.status.value).text }}</strong>
                        <v-tooltip activator="parent" location="bottom">
                            Status
                        </v-tooltip>
                    </v-chip>
                </v-card>
            </transition>
            <transition name="slide-y-transition">
                <v-card class="mt-5 mb-15" elevation="0" v-if="can('approve', PR) && showSettingSchedule"
                    title="Setting Schedule">
                    <div v-if="has_schedule" class="d-flex justify-end mb-5">
                        <v-chip label variant="tonal" :color="dateMatch.color"><v-icon
                                class="mr-2">mdi-calendar-badge-outline</v-icon> <b>{{ dateMatch.text }}</b></v-chip>
                    </div>
                    <v-row>
                        <v-col cols="6">
                            <v-card elevation="0" class="pa-2 border-sm pa-3">
                                <b class="text-primary">Service Dept. Set Schedule</b>
                                <p class="mt-4 mb-4">Set Schedule: <span class="font-weight-bold">{{
                                    pub_var.FullMonthWithTime(serviceData?.scheduled_date) }}</span></p>

                                <v-alert dense color="danger" variant="tonal" class="pa-1">
                                    * If the schedule does not match, please refer to this as the suggested schedule.
                                </v-alert>
                                <p class="mt-4 mb-4">Preferred Schedule: <span class="font-weight-bold">{{
                                    pub_var.FullMonthWithTime(serviceData?.preferred_schedule) }}</span></p>

                                <p class="mt-4">Remarks: </p>
                                <p class="font-weight-bold text-primary">{{ serviceData?.remarks ?? '---' }}</p>
                            </v-card>
                        </v-col>

                        <v-col cols="6">
                            <v-card elevation="0" class="pa-2 border-sm pa-3">
                                <b class="text-primary">Outbound Dept. Set Schedule</b>
                                <p class="mt-4 mb-4">Set Schedule: <span class="font-weight-bold">{{
                                    pub_var.FullMonthWithTime(outboundData?.scheduled_date) }}</span></p>

                                <v-alert dense color="danger" variant="tonal" class="pa-1">
                                    * If the schedule does not match, please refer to this as the suggested schedule.
                                </v-alert>
                                <p class="mt-4 mb-4">Preferred Schedule: <span class="font-weight-bold">{{
                                    pub_var.FullMonthWithTime(outboundData?.preferred_schedule) }}</span></p>

                                <p class="mt-4">Remarks: </p>
                                <p class="font-weight-bold text-primary">{{ outboundData?.remarks ?? '---' }}</p>
                            </v-card>
                        </v-col>
                    </v-row>
                </v-card>
            </transition>

            <transition name="slide-x-transition" v-if="can('approve', PR) && showDelegationSetting">
                <v-card flat style="padding-top: 1em;" class="mb-7 pa-5 mt-5">
                    <v-row>
                        <v-col :cols="column">
                            <p class="mb-3">Set by Operation and Service Dept.</p>
                            <v-alert variant="tonal" color="primary" dense class="pa-1"><b>Final Schedule: </b> <span>
                                    {{
                                        finalSchedule }}</span></v-alert>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col :cols="column">
                            <v-combobox color="primary" class="mt-5" v-model="selected_engineer" clearable
                                label="Delegate to" placeholder="Delegate to" density="compact" :items="sort_engineers"
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
                    </v-row>
                </v-card>
            </transition>

            <v-skeleton-loader type="subtitle, list-item-three-line" v-if="loading"></v-skeleton-loader>
            <transition name="fade-transition" v-else>
                <v-card flat style="padding-top: 1em;" class="mb-7 pa-5 mt-5">
                    <v-row class="d-flex justify-space-between">
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Requested by</p>
                            <p class="text-grey-darken-1">{{ formData.requested_by.value }}</p>
                        </v-col>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Date Created</p>
                            <p class="text-grey-darken-1">{{ pub_var.formatDate(formData.created_at.value) }}</p>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Institution</p>
                            <p class="text-grey-darken-1">{{ formData.institution.value }}</p>
                        </v-col>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Address</p>
                            <p class="text-grey-darken-1">{{ formData.address.value }}</p>
                        </v-col>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Proposed Pullout Date</p>
                            <p class="text-grey-darken-1">{{ formData.proposed_pullout_date.value }}</p>
                        </v-col>
                    </v-row>
                </v-card>
            </transition>
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
                        <v-window-item value="equipments">
                            <PulloutRequestedEquipment :equipments="equipments"/>
                        </v-window-item>
                        <v-window-item value="history">
                            <PulloutHistoryLog :service_id="id" :status="formData.level.value" />
                        </v-window-item>
                    </v-window>
                </v-card-text>
            </v-card>

        </v-container>
        <!-- </v-form> -->
    </LayoutSinglePage>
</template>
<script setup>
import { ref, watch, onMounted, provide, computed, toRaw } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useDisplay } from 'vuetify'
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';

import { user_data } from '@/stores/auth/userData';
import { getRole } from '@/stores/getRole'
import { apiRequestAxios } from '@/api/api';
import PulloutHistoryLog from '@/components/Pullout/PulloutHistoryLog.vue';
import * as pub_var from '@/global/global'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

import moment from 'moment';

/** Get Role Store */
const role = getRole()
const currentUserRole = role.currentUserRole


/** CAstl Permission */
import { permit } from '@/castl/permitted';
import { OPERATION_SERVICE, pullout_approver, pullOutStatus, SUPERVISOR } from '@/global/pullout';
import { PR } from '@/global/modules';
import { operation_department, service_department } from '@/global/department';
const { can } = permit()

/** Fetch All Service Engineers */
import { users_engineers } from '@/helpers/getUsers';
import PulloutRequestedEquipment from '@/components/Pullout/PulloutRequestedEquipment.vue';
const { engineers } = users_engineers()
const sort_engineers = computed(() => {
    return [...(engineers.value || [])].sort((a, b) => a.sbu - b.sbu)
})
const selected_engineer = ref('')


/** data declarations */
const router = useRouter()
const route = useRoute()
const { width } = useDisplay()
const user = user_data()

const apiRequest = apiRequestAxios()

const tab = ref('equipments') //TAB

const form = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const id = parseInt(route.params.id)

/** Form Fields */
const formData = {
    institution: ref(''),
    requested_by: ref(''),
    address: ref(''),
    proposed_pullout_date: ref(''),
    level: ref(null),
    status: ref(''),
    final_schedule: ref(''),
    driver: ref(''),
    supervisor: ref(''),
    created_at: ref(''),
};

const sched = ref({
    scheduled_date: null,
    preferred_schedule: null,
    remarks: null,
    // driver: '',
})

const remark = ref('')


/**BreadCrumbs */
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/pull-out-request', display: 'block' },
    { title: 'Pullout', disabled: true, href: '', display: 'none' },
    { title: 'View', disabled: true, href: '', display: 'none' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};



/** Disapprove Details */
const dialog_disapprove = ref(false)

/** Disapprove Details */
const dialog_approve = ref(false)



/** show Settings for Outbound and Service */
const user_sbu_as_approver = user.user.user_roles.find(v => v.role_id === pub_var.approverRoleID)?.SBU
const showSettingSchedule = computed(() => {
    if (formData.level.value === OPERATION_SERVICE && (!has_schedule.value || pending_decisions.value !== 0)) {
        if (user.user?.department === operation_department) {
            return true
        }
        if (user.user?.department === service_department) {
            return equipment_sbu.value.includes(user_sbu_as_approver)
        }
        return false
    }
    return false
})
// Check if Schedule Match
const dateMatch = computed(() => {
    if (outboundData.value?.scheduled_date !== serviceData.value?.scheduled_date) {
        return { color: 'error', text: 'Schedules do not match' }
    }
    else return { color: 'success', text: 'Schedules match' }
})
/** Show Delegation Settings for Service */
const showDelegationSetting = computed(() => {
    if (formData.level.value === OPERATION_SERVICE && (!has_schedule.value || pending_decisions.value === 0)) {
        if (user.user?.department === operation_department) {
            return true
        }
        if (user.user?.department === service_department) {
            return equipment_sbu.value.includes(user_sbu_as_approver)
        }
        return false
    }
    return false
})


/** If Can Approve */
const canApprove = computed(() => {
    const allowedStatuses = [pub_var.pending]; // Allowed statuses for approval
    const userApprovalLevels = user.user?.approval_level_pullout || [];
    const userID = user.user?.id;
    const userDepartment = user.user?.department;
    
    if (userApprovalLevels.includes(formData.level.value)) {
        if (!allowedStatuses.includes(formData.status.value)) return false
        if (formData.level.value === SUPERVISOR) {
            return userID === formData.supervisor.value
        }
        if (formData.level.value === OPERATION_SERVICE) {
            if (userDepartment === operation_department) return true
            if (userDepartment === service_department) {
                return equipment_sbu.value.includes(user_sbu_as_approver)
            }
        }
        return true
    }
    return false
})


/** Data functions */
const approveRequest = async () => {
    btnLoading.value = true

    /**Check if Form is Valid */
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }

    try {
        const response = await apiRequest.post('approve-pullout', {
            service_id: id,
            level: formData.level.value,
            remark: remark.value,
            ...sched.value
        })
        if (response?.data?.success) {
            if(response?.data?.matched){
                toast.success('Schedule Matched')
                getRequest()
            }
            else if(response?.data?.under_operation_service){
                toast.success('You set schedule successfully')
                getRequest()
            }else{
                toast.success('Approved successfully')
                router.push('/pull-out-request')
            }
        }
        else {
            console.log(response.data.errorLog)
            toast.error(response.data.errorLog)
            getRequest()
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        btnDisable.value = false
        btnLoading.value = false
        dialog_approve.value = false
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
        const response = await apiRequest.post('pullout-disapprove-request', {
            service_id: id,
            remark: disApproveRemark.value,
        })

        if (response.data && response.data.success) {
            toast.success('Disapproval completed successfully')
            router.push('/pull-out-request')
        }
    } catch (error) {
        console.log(error)
    }
    finally {

    }
}

/** Get Request */
const loading = ref(false)
const pullout_decision_outbound = ref([])
const pullout_decision_service = ref([])
const pending_decisions = ref('')
const has_schedule = ref('')
const equipments = ref([])
const finalSchedule = computed(() => {
    const schedule = formData.final_schedule.value
    return schedule ? moment(schedule.trim(), 'YYYY-MM-DD HH:mm:ss').format('MMMM DD, YYYY hh:mm a') : ''
})
const outboundData = computed(() => pullout_decision_outbound?.value.find(v => v.type === 'outbound'))
const serviceData = computed(() => pullout_decision_service?.value.find(v => v.type === 'service'))

const equipment_sbu = ref([])
const getRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('view-pullout-id', {
            params: {
                id: id,
                // current_role: currentUserRole,
            }
        });
        if (response.data && response.data.data) {
            const result = response.data.data

            Object.keys(formData).forEach((key) => {
                if (result[key] !== undefined) {
                    formData[key].value = result[key]
                    // console.log(formData[key].value)
                }
            })

            pullout_decision_outbound.value = result?.pullout_decision_outbound
            pullout_decision_service.value = result?.pullout_decision_service
            pending_decisions.value = result?.pending_decisions
            has_schedule.value = result?.has_schedule
            equipments.value = result?.equipments

            equipment_sbu.value = result?.equipments?.map(v => v.master_data?.sbu)
        }
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false;
    }
};








/** Disable Approver Button */
const disableButton = () => {
    btnDisable.value = true
}
provide('disableButton', disableButton)



const column = ref(6)
watch(width, (val) => {
    if (val < 550) {
        column.value = 12
    }
    else {
        column.value = 6
    }
})




onMounted(() => {
    getRequest()
})
</script>