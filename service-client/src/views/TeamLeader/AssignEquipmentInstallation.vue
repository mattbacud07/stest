<template>
    <LayoutSinglePage>
        <template #topBarFixed>
            <div>
                <v-breadcrumbs  class="pt-15"> 
                    <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index" :class="{'custom-pointer': !item.disabled}" @click="navigateTo(item)"
                        :disabled="item.disabled" >
                        {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                    </v-breadcrumbs-item>
                </v-breadcrumbs>
                <v-spacer></v-spacer>
                <!-- Assign Engineer -->
                <v-dialog v-model="dialogApprove" max-width="400" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn type="button" size="small" v-bind="activatorProps" :disabled="btnDisable" color="danger"
                            class="text-none btnSubmit"><v-icon class="mr-2">mdi-clipboard-account-outline</v-icon>
                            Assign</v-btn>
                    </template>

                    <v-card title="Assign for Installation">
                        <!-- <v-form @submit.prevent="assignEngineer" ref="form"> -->
                        <v-col cols="12">
                            <v-combobox color="primary" class="mt-5" v-model="Engineers" :rules="rule_engineers"
                                label="Assign" placeholder="Assign to" density="compact" :items="engineersData"
                                variant="outlined" itemValue="value" itemTitle="key"></v-combobox>

                            <v-row justify="end" class="mt-3 mb-3">
                                <v-btn elevation="2" @click="dialogApprove = false" background-color="red" size="small"
                                    color="default" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                    Cancel</v-btn>
                                <v-btn type="button" size="small" :loading="btnLoading" :disabled="btnDisable"
                                    color="#191970" class="text-none bg-primary mr-5" @click="assignEngineer"><v-icon
                                        class="mr-2">mdi-check</v-icon>
                                    Save</v-btn>
                            </v-row>
                        </v-col>
                        <!-- </v-form> -->
                    </v-card>
                </v-dialog>
            </div>
        </template>

        <template #default>
            <v-container class="container-form mt-3">
                <RequestedEquipments :service_id="parseInt(service_id)" /><br>

                <RequestDetails :service_id="parseInt(service_id)" />


                <!-- <ApproverHistoryLog :service_id="service_id"/> -->

                <v-snackbar color="success" v-model="snackbarSuccess" location="right bottom" :timeout="5000">
                    <v-icon>mdi-check-circle-outline</v-icon> Successfully assigned.
                </v-snackbar>
            </v-container>
        </template>
    </LayoutSinglePage>
</template>
<script setup>
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue'
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue'

import { ref, watch, onMounted } from 'vue';
import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';
import { user_data } from '@/stores/auth/userData';
// import ApproverHistoryLog from './ApproverHistoryLog.vue'

import * as pub_var from '@/global/global'

/** data declarations */
const router = useRouter()
const route = useRoute()
const user = user_data()
const apiRequest = user.apiRequest()
user.getUserData
const form = ref(false)
const dialogApprove = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const snackbarErrorGeneral = ref(false)
const snackbarSuccess = ref(false)
const snackbarSuccessDisapproved = ref(false)
const snackbarError = ref(false)
const id = route.params.id
const status = route.params.status
const service_id = ref(id)
const serialNumber = ref([])
const Engineers = ref('')
const submmitApproveStatus = ref(false)
const remark = ref('')
const engineersData = ref([])


/**BreadCrumbs */
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/equipment-handling' },
    { title: 'Equipment Handling', disabled: true, href: '' },
    { title: 'Internal Servicing', disabled: true, href: '' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};


/** Rules */
const rule_engineers = ref([
    v => !!v || 'Required field',
    k => k.key !== undefined ? true : 'Select an option from the list',
    r => r.value !== undefined ? true : 'Select an option from the list'
])

/**
 * Get Engineers Data - Use for Delegation
 */
const getEngineersData = async () => {
    try {
        const response = await apiRequest.get('get-engineers-data')
        if (response.data && response.data.engineers) {
            const engineersValue = response.data.engineers.map(data => {
                return {
                    key: data.first_name + ' ' + data.last_name,
                    value: data.user_id
                }
            })
            engineersData.value = engineersValue
        }
    } catch (error) {
        alert(error)
    }
}



/**
 * Assign Engineer to Install
 */
const assignEngineer = async () => {
    btnLoading.value = true
    if (!Engineers.value) {
        btnLoading.value = false
        return;
    }

    try {
        const response = await apiRequest.post('approve-request',
            {
                service_id: id,
                tl_assigned: user.user.id,
                installer: Engineers.value.value,
                status: parseInt(status),
                approval_level: 0,
            }
        )
        if (response.data && response.data.success) {
            snackbarSuccess.value = true
            dialogApprove.value = false
            setTimeout(() => {
                router.push('/set-schedule-equipment-installation')
            }, 4000)
        }
    } catch (error) {
        alert(error)
    } finally {
        btnLoading.value = false
    }
}



onMounted(() => {
    getEngineersData()
})
</script>