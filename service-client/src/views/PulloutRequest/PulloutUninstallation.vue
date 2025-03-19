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

            <PulloutDelegateEngineer v-if="can('delegate', PR) && canDelegate" :id="id" @refresh-data="refreshData" />

        </template>

        <v-container class="container-form mt-3">
            <transition name="scale-transition">
                <v-card class="mt-5 statusVCard" elevation="0" v-if="!loading">
                    <v-chip class="ma-2" size="small" color="purple" label>
                        <strong>&nbsp;{{ pullout_approver(request.level) }}</strong>
                        <v-tooltip activator="parent" location="bottom">
                            Pending approval
                        </v-tooltip>
                    </v-chip>
                    <v-chip class="ma-2" variant="flat" size="small" :color="pullOutStatus(request.status).color"
                        label>
                        <strong>&nbsp; {{ pullOutStatus(request.status).text }}</strong>
                        <v-tooltip activator="parent" location="bottom">
                            Status
                        </v-tooltip>
                    </v-chip>
                </v-card>
            </transition>

            <v-skeleton-loader type="subtitle, list-item-three-line" v-if="loading"></v-skeleton-loader>
            <transition name="fade-transition" v-else>
                <v-card flat style="padding-top: 1em;" class="mb-7 pa-5 mt-5">
                    <v-row class="d-flex justify-space-between">
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Requested by</p>
                            <p class="text-grey-darken-1">{{ request?.requested_by }}</p>
                        </v-col>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Date Created</p>
                            <p class="text-grey-darken-1">{{ request?.created_at }}</p>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Institution</p>
                            <p class="text-grey-darken-1">{{ request?.institution }}</p>
                        </v-col>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Address</p>
                            <p class="text-grey-darken-1">{{ request?.address }}</p>
                        </v-col>
                        <v-col lg="4" md="4" sm="6" cols="12">
                            <p>Proposed Pullout Date</p>
                            <p class="text-grey-darken-1">{{ request?.proposed_pullout_date }}</p>
                        </v-col>
                    </v-row>
                </v-card>
            </transition>
            <v-card class="mt-10">
                <v-tabs v-model="tab" density="compact" class="border-b-sm">
                    <v-tab value="equipments" class="text-none"><v-icon class="mr-2">mdi-hammer-screwdriver</v-icon>
                        Requested Equipments</v-tab>
                    <v-tab value="service_report" class="text-none"><v-icon class="mr-2">mdi-account-details</v-icon>
                        Service Report</v-tab>
                </v-tabs>

                <v-card-text>
                    <v-window v-model="tab" :disabled="true">
                        <v-window-item value="equipments">
                            <PulloutRequestedEquipment :equipments="equipments" />
                        </v-window-item>
                        <v-window-item value="service_report">
                            <ServiceReportData :task_delegation="task_delegation" :actions_taken="actions_taken"
                            :spareparts="spareparts" />
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
import { A_PR, PR } from '@/global/modules';
import { operation_department, service_department } from '@/global/department';
const { can } = permit()


/** Fetch All Service Engineers */
import { users_engineers } from '@/helpers/getUsers';
import PulloutRequestedEquipment from '@/components/Pullout/PulloutRequestedEquipment.vue';
import ServiceReportData from '@/components/Approver/EH/ServiceReportData.vue';
import { Declined } from '@/global/maintenance';
import PMDelegateEngineer from '@/components/PreventiveMaintenance/PMComponents/PMDelegateEngineer.vue';
import PulloutDelegateEngineer from '@/components/Pullout/PulloutDelegateEngineer.vue';
const { engineers } = users_engineers()
const sort_engineers = computed(() => {
    return [...(engineers.value || [])].sort((a, b) => a.sbu - b.sbu)
})
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


/**BreadCrumbs */
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/pull-out-request', display: 'block' },
    { title: 'Pending Pullout', disabled: true, href: '', display: 'none' },
    { title: 'View', disabled: true, href: '', display: 'none' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};



/** Get Request */
const loading = ref(false)
const equipments = ref([])

const equipment_sbu = ref([])
const request = ref([])
const task_delegation = ref([])
const actions_taken = ref([])
const spareparts = ref([])
const getRequest = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('view-pullout-id', {
            params: {
                id: id,
                module_type: A_PR
            }
        });
        if (response?.data?.data) {
            const result = response.data?.data
            request.value = result

            equipments.value = result?.equipments

            equipment_sbu.value = result?.equipments?.map(v => v.master_data?.sbu)
        }
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false;
    }
};


/** Delegate Engineer ->  */
const rolesToAccess = [pub_var.TLRoleID, pub_var.SBUServiceAssistantID, pub_var.engineerRoleID]
const user_sbu = user.user?.user_roles?.find(v => rolesToAccess.includes(v.role_id))?.SBU
const canDelegate = computed(() => {
    if ((request.value?.status === Declined || request.value?.status === pub_var.uninstalling)
        && (request.value?.equipment?.sbu === user_sbu || user.user?.user_roles?.some(v => v.role_id === pub_var.NationalServiceManagerID))) return true
    return false //subject to change the scheduled status to waiting for schedule
})



const column = ref(6)
watch(width, (val) => {
    if (val < 550) {
        column.value = 12
    }
    else {
        column.value = 6
    }
})


const refreshData = (data) => {
    if (data === true) getRequest()
}




onMounted(() => {
    getRequest()
})
</script>