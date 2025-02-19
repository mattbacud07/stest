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
            <template v-if="can('delegate', EH) && canDelegate">
                <!-- Delegate Button -->
                <v-dialog v-model="delegateDialog" max-width="600" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn type="button" v-bind="activatorProps" :loading="loading" :disabled="btnDisable"
                            color="primary" variant="flat" class="text-none btnSubmit"><v-icon
                                class="mr-2">mdi-account-arrow-left-outline</v-icon>
                            Delegate Engineer
                        </v-btn>
                    </template>
                    <v-card class="pa-5">
                        <v-form @submit.prevent="delegateEngineer" ref="form">
                            <v-row>
                                <v-col>
                                    <h5>Delegate Engineer</h5>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col class="d-flex justify-center">
                                    <v-combobox color="primary" class="mt-5" v-model="Engineers" clearable
                                        label="Delegate to" placeholder="Delegate to" density="compact"
                                        :items="engineers" variant="outlined" itemValue="value" :rules="[
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
                            <v-row justify="end" class="mt-7 mb-5">
                                <v-btn variant="plain" @click="delegateDialog = false" color="primary"
                                    class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                    Cancel</v-btn>
                                <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="#191970" flat
                                    class="text-none bg-primary mr-5"><v-icon class="mr-2">mdi-check</v-icon>
                                    Delegate</v-btn>
                            </v-row>
                        </v-form>
                    </v-card>
                </v-dialog>
            </template>
            <template v-if="can('installer', EH) && canAcceptDecline">
                <v-dialog v-model="AcceptDeclineDialog" max-width="600" persistent>
                    <template v-slot:activator="{ props: activatorProps }">

                        <!-- Decline Button -->
                        <v-btn type="button" v-bind="activatorProps" :loading="loading" :disabled="btnDisable"
                            color="primary" variant="tonal" class="text-none mr-2" @click="openDialog('declined')">
                            <v-icon class="mr-2">mdi-close</v-icon> Decline
                        </v-btn>

                        <v-btn type="button" v-bind="activatorProps" :loading="loading" :disabled="btnDisable"
                            color="primary" variant="flat" class="text-none" @click="openDialog('accepted')">
                            <v-icon class="mr-2">mdi-check</v-icon> Accept
                        </v-btn>


                    </template>
                    <v-card class="pa-5">
                        <v-form @submit.prevent="AcceptDeclineRequest" ref="formAcceptDecline">
                            <v-row>
                                <v-col class="d-flex justify-center">
                                    <v-textarea v-model="remarks"
                                        :rules="actionType === 'declined' ? [v => !!v || 'Required'] : []"
                                        label="Remarks" placeholder="Remarks" dense rows="3" variant="outlined"
                                        class="mt-5 w-100"></v-textarea>
                                </v-col>
                            </v-row>
                            <v-row justify="end" class="mt-7 mb-5">
                                <v-btn variant="plain" @click="AcceptDeclineDialog = false" color="primary"
                                    class="text-none mr-2">
                                    Cancel</v-btn>
                                <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="#191970" plain
                                    class="text-none bg-primary mr-5">
                                    {{ actionType === 'accepted' ? 'Accept' : 'Decline' }}</v-btn>
                            </v-row>
                        </v-form>
                    </v-card>
                </v-dialog>
            </template>
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
                        <!-- <v-chip class="ma-2" color="purple" label>
                            <strong>&nbsp;{{ pub_var.getApproverByLevel(request.level, 1) }}</strong>
                            <v-tooltip activator="parent" location="bottom">Pending approval</v-tooltip>
                        </v-chip> -->
                        <v-chip class="ma-2" color="purple" label>
                            <v-tooltip activator="parent" location="bottom">Status</v-tooltip>
                            <strong>&nbsp;{{ eh_installation?.status ?? 'Declined'}}</strong>
                        </v-chip>
                    </v-card>
                </transition>

                <RequestDetails :request_data="request" />

                <RequestType :request_data="request" />
            </template>

            <v-card class="mt-10">
                <v-tabs v-model="tab" density="compact" class="border-b-sm" bg-color="grey-lighten-5">
                    <div class="d-flex">
                        <v-tab value="equipments" class="text-none"><v-icon class="mr-2">mdi-hammer-screwdriver</v-icon>
                            Requested
                            Equipments</v-tab>
                        <v-tab value="history" class="text-none"><v-icon class="mr-2">mdi-account-details</v-icon>
                            Approval Requirements</v-tab>
                    </div>
                    <v-spacer></v-spacer>
                    <div>
                        <v-tab value="delegation_details" :disabled="loading" class="text-none" color="primary"><v-icon
                                class="mr-2">mdi-gesture-tap-button</v-icon> Delegation
                            Details</v-tab>
                    </div>
                </v-tabs>

                <v-card-text>
                    <v-window v-model="tab" :disabled="true">
                        <!-- <v-window-item value="request_type"></v-window-item> -->
                        <v-window-item value="equipments">
                            <RequestedEquipments :service_id="id" @get-equipment="getEquipments"
                                :editSerial="request.level !== IT_DEPARTMENT ? false : true" :category="pub_var.EH" />
                        </v-window-item>
                        <v-window-item value="history">
                            <ApproverHistoryLog :service_id="id" :status="request.main_status" />
                        </v-window-item>
                        <v-window-item value="delegation_details">
                            <InternalServicingActivity :id="id" :key="refreshKey"
                                :internalData="request" />
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
const { can } = permit()


/** data declarations */
const router = useRouter()
const route = useRoute()
const { width } = useDisplay()
const user = user_data()

const apiRequest = apiRequestAxios()

const tab = ref('equipments') //TAB

const form = ref(false)
const delegateDialog = ref(false)
const AcceptDeclineDialog = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const id = parseInt(route.params.id)
const remarks = ref('')
const actionType = ref('')  // Accept or Decline
const openDialog = (type) => {
    actionType.value = type;
    AcceptDeclineDialog.value = true;
};

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


/** ========================== Delegation of Engineers ======================== */
/** Fetch All Service Engineers */
import { users_engineers } from '@/helpers/getUsers';
import { EH } from '@/global/modules';
import InternalServicingActivity from '@/components/Approver/EH/InternalServicingActivity.vue';
const { engineers } = users_engineers()

/**
 * Submit || Delegate Internal Servicing
 */
const Engineers = ref('')
const delegateEngineer = async () => {
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }

    try {
        const response = await apiRequest.post('delegate-engineer', {
            service_id: id,
            assigned_to: Engineers.value.value
        })
        if (response.data?.success) {
            toast.success('Successfully delegated')
            if (typeof (disableButton) === 'function') disableButton()
            refreshData()
        } else if (response?.data?.exist) {
            toast.warning('Request already exist.')
        }
    } catch (error) {
        alert(error)
    }
    finally {
        btnLoading.value = false
        delegateDialog.value = false
        btnDisable.value = false
    }
}

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
        const response = await apiRequest.post('accept-decline-delegate', {
            service_id: id,
            delegation_id: eh_installation.value.id,
            status: actionType.value,
            remarks: remarks.value
        })
        if (response.data?.success) {
            toast.success('Successful')
            refreshData()
        }
    } catch (error) {
        alert(error)
    }
    finally {
        btnLoading.value = false
        AcceptDeclineDialog.value = false
    }
}



/** ================================================= Get Request Data ==================================== */
const loading = ref(false)
const request = ref({})
const eh_installation = ref({})
const getRequest = async () => {
    try {
        loading.value = true
        const response = await apiRequest.get('get-specific-equipment-handling', {
            params: {
                service_id: id,
            },
        });
        if (response?.data?.request) {
            request.value = response.data?.request
            eh_installation.value = response.data.request?.task_delegation
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


/** get Equipments in Requested Equipments Component */
const getEquipments = (data) => {
    equipment_sbu.value = data.map(v => v.sbu)
}

/** Show the Start of Internal Process = Delegation of Service Enginner */
const rolesToAccess = [pub_var.TLRoleID, pub_var.SBUServiceAssistantID, pub_var.engineerRoleID]
const user_sbu_as_approver = user.user.user_roles.find(v => rolesToAccess.includes(v.role_id))?.SBU
const equipment_sbu = ref([])

/** Delegate Engineer ->  */
const canDelegate = computed(() => {
    if (eh_installation.value) return false
    return request.value.main_status === pub_var.INSTALLING &&
        equipment_sbu.value.includes(user_sbu_as_approver)
})

/** Accept Decline the Delegation */
const canAcceptDecline = computed(() => {
    const assigned_to = eh_installation.value?.assigned_to
    const user_id = user.user.id
    return request.value.main_status === pub_var.INSTALLING && assigned_to === user_id
})


/** Refresh after Submitting Internal Servicing */
const refreshKey = ref(0)
const refreshData = () => {
    getRequest()
    refreshKey.value += 1
}

onMounted(() => {
    getRequest()
})
</script>