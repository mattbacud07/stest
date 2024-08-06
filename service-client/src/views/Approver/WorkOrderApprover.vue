<template>
    <LayoutSinglePage>
        <template #topBarFixed>
            <div>
                <v-breadcrumbs class="pt-7">
                    <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index"
                        :class="{ 'custom-pointer': !item.disabled }" @click="navigateTo(item)"
                        :disabled="item.disabled">
                        {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                    </v-breadcrumbs-item>
                </v-breadcrumbs>
            </div>
            <v-spacer></v-spacer>
            <div v-if="getInternalStatus === pub_var.internalStat.InProgress || getInternalStatus === pub_var.internalStat.Packed
                        || getInternalStatus === pub_var.internalStat.Completed">
                <p style="color: orange;">* Internal Processing Still in Progress</p>
            </div>
            <div v-else>
                <v-dialog v-model="dialog" max-width="400" persistent>
                    <template v-slot:activator="{ props: activatorProps }">
                        <v-btn :disabled="btnDisable" v-bind="activatorProps" size="small" color="white" variant="tonal"
                            elevation="1" class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                            Disapprove</v-btn>
                    </template>
                    <v-card text="" title="Disapprove">
                        <v-col cols="12">
                            <v-textarea class="mr-2 ml-2" v-model="disApproveRemark" clearable
                                label="Reason of Disapproval" color="primary" variant="outlined"></v-textarea>
                            <p class="text-danger ml-3">{{ disApproveRemarkError }}</p>
                        </v-col>
                        <template v-slot:actions>
                            <v-row justify="end">
                                <v-btn elevation="2" @click="dialog = false" background-color="red" size="small"
                                    color="#191970" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                    Cancel</v-btn>
                                <v-btn @click="disapproveRequest" color="primary" elevation="2" size="small"
                                    class="text-none mr-3"
                                    style="background-color: #191970;color: #fff!important;"><v-icon
                                        class="mr-1">mdi-text-box-remove-outline</v-icon> Disapprove</v-btn>
                            </v-row>
                        </template>
                    </v-card>
                </v-dialog>

                <!-- Approve Button -->
                <v-dialog v-model="dialogApprove" max-width="400" persistent>
                    <template v-slot:activator="{ props: activatorProps }">

                        <v-btn type="button" size="small" v-bind="activatorProps" :disabled="btnDisable" color="white"
                            variant="outlined" class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                            Approve</v-btn>
                    </template>
                    <v-card text="" title="Approve">
                        <v-form @submit.prevent="approveRequest" ref="form">
                            <v-col cols="12">
                                <v-textarea class="mr-2 ml-2" v-model="remark" clearable label="Remarks (optional)"
                                    color="primary" variant="outlined"></v-textarea>
                                <v-select color="primary" class="mr-2 ml-2 mt-5" label="Set SSU" density="compact"
                                    variant="outlined" :items="ssuToArray(pub_var.ssu)" item-title="text"
                                    item-value="value" item v-model="ssu" :rules="ssuRule"
                                    v-if="pub_var.SERVICE_HEAD_ENGINEER === user.user.approval_level"></v-select>
                            </v-col>
                            <!-- <template v-slot:actions> -->
                                <v-divider></v-divider>
                            <v-row justify="end" class="mt-7 mb-5 pr-3">
                                <v-btn variant="tonal" @click="dialogApprove = false" size="small"
                                    color="primary" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                    Cancel</v-btn>
                                <v-btn type="submit" size="small" :loading="btnLoading" :disabled="btnDisable"
                                    color="#191970" flat class="text-none bg-primary mr-5"><v-icon
                                        class="mr-2">mdi-check</v-icon>
                                    Approve</v-btn>
                            </v-row>
                            <!-- </template> -->
                        </v-form>
                    </v-card>
                </v-dialog>
            </div>
        </template>
        <!-- <v-form ref="form"> @submit.prevent="submitWorkOrder" ref="form" -->
        <v-container class="container-form mt-3">
            <RequestDetails :service_id="parseInt(service_id)"
                :showInternalRequest="user.user.approval_level === pub_var.SERVICE_TL ? true : false"
                @set-status="getStatus" />

            <RequestedEquipments :service_id="parseInt(service_id)" @set-serial="getSerialNumber" :editSerial="true" />

            <ApproverHistoryLog :service_id="parseInt(service_id)" :status="status" />


            <v-snackbar color="error" v-model="snackbarErrorGeneral" location="right bottom" :timeout="3000">
                <v-icon>mdi-information-box</v-icon> Please ensure all serial number fields are filled in.
            </v-snackbar>
            <v-snackbar color="success" v-model="snackbarSuccess" location="right bottom" :timeout="5000">
                <v-icon>mdi-check-circle-outline</v-icon> Approved successfully.
            </v-snackbar>
            <v-snackbar color="warning" v-model="snackbarSuccessDisapproved" location="right bottom" :timeout="5000">
                <v-icon>mdi-check-circle-outline</v-icon> Disapproved successfully.
            </v-snackbar>
            <v-snackbar color="error" v-model="snackbarError" location="right bottom" :timeout="5000">
                <v-icon>mdi-alert-circle-outline</v-icon> Something went wrong.
            </v-snackbar>
        </v-container>
        <!-- </v-form> -->
    </LayoutSinglePage>
</template>
<script setup>
import { ref, watch, onMounted, provide } from 'vue';
import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { BASE_URL } from '@/api';
import { user_data } from '@/stores/auth/userData';
import Axios from 'axios';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue';
// import RequestedEquipments from './RequestedEquipments.vue'
import ApproverHistoryLog from '@/components/Approver/EH/ApproverHistoryLog.vue'
// import RequestDetails from './RequestDetails.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue';
import * as pub_var from '@/global/global'

/** data declarations */
const uri = BASE_URL
const router = useRouter()
const route = useRoute()
const user = user_data()
const apiRequest = user.apiRequest()
user.getUserData
const form = ref(false)
const dialog = ref(false)
const dialogApprove = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const snackbarErrorGeneral = ref(false)
const snackbarSuccess = ref(false)
const snackbarSuccessDisapproved = ref(false)
const snackbarError = ref(false)
const id = route.params.id
const service_id = ref(id)
const serialNumber = ref([])
const submmitApproveStatus = ref(false)
const remark = ref('')
const ssu = ref('')
const getInternalStatus = ref(null)
const CurrentlyDelegatedTo = ref(null)
const status = ref(0)
const exist_service_id = ref(null)


/** ssuToArray */
const ssuToArray = (obj) => {
    return Object.keys(obj).map(k => ({ text: 'SSU' + obj[k], value: 'SSU' + obj[k] }))
}
// SSU Rule
const ssuRule = ref([
    v => !!v || 'Required'
])
watch(ssuRule, (val) => {
    console.log(val)
})
/**BreadCrumbs */
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/equipment-handling' },
    { title: 'Equipment Handling', disabled: true, href: '' },
    { title: 'Work Order', disabled: true, href: '' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};


/** Data functions */
const approveRequest = async () => {
    btnLoading.value = true
    if (user.user.approval_level === pub_var.IT_DEPARTMENT) {
        if (!submmitApproveStatus.value) {
            snackbarErrorGeneral.value = true
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
    // console.log(serialNumber.value.map(item => item.id))
    try {
        const response = await apiRequest.post('approve-request', {
            approval_level: user.user.approval_level,
            id: user.user.id,
            item: serialNumber.value,
            service_id: id,
            ssu : ssu.value,
            remark: remark.value,

        })
        if (response.data && response.data.success) {
            snackbarSuccess.value = true
            btnDisable.value = true
            setTimeout(() => {
                router.push('/equipment-handling')
            }, 2000)
        }
        else {
            console.log(response.data.error)
            snackbarError.value = true
        }
    } catch (error) {
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
const disApproveRemarkError = ref('')
const disapproveRequest = async () => {
    if (disApproveRemark.value === '') {
        disApproveRemarkError.value = 'Field is required'
        setTimeout(() => {
            disApproveRemarkError.value = ''
        }, 3000)
        return
    }

    try {
        const response = await apiRequest.post('disapprove-request', {
            id: user.user.id,
            service_id: id,
            remark: disApproveRemark.value,
            approval_level: user.user.approval_level,
        })

        if (response.data && response.data.success) {
            snackbarSuccessDisapproved.value = true
            setTimeout(() => {
                router.push('/equipment-handling')
            }, 2000)
        }
    } catch (error) {
        alert(error)
    }
    finally {
        snackbarSuccessDisapproved.value = false
    }
}

/** Check Status of Request if Delegated to an Engineer */
const checkIfDelegatedToEngineer = async () => {
    try {
        const response = await apiRequest.get('getInternalRequest', {
            params: { service_id: id, category: 'specificService' }
        })
        if (response.data && response.data.request && response.data.request.length > 0) {
            const result = response.data.request
            getInternalStatus.value = result.map((data) => data.status)[0]
            const fullname = result.map((data) => ({ fname: data.get_user.first_name, lname: data.get_user.last_name }))[0]
            CurrentlyDelegatedTo.value = fullname.fname + ' ' + fullname.lname
        }
    } catch (error) {
        alert(error)
    }
}
provide('getInternalStatus', getInternalStatus)
provide('CurrentlyDelegatedTo', CurrentlyDelegatedTo)


/**
 * Get Serial Number Input from Additional Peripheral Table
 */
const getSerialNumber = (serial) => {
    serialNumber.value = Array.from(serial)
    submmitApproveStatus.value = serialNumber.value.every(data => data.serial && data.serial.trim() !== '')
    // for (const data of serialNumber.value) {
    //     if (data.serial === undefined || data.serial === null || data.serial === '') {
    //         submmitApproveStatus.value = false
    //     } else {
    //         submmitApproveStatus.value = true
    //     }
    // }
}

/** Get Status */
const getStatus = (data) => {
    status.value = data
}


onMounted(() => {
    if (user.user.approval_level !== 1) {
        btnDisable.value = false
    }
    checkIfDelegatedToEngineer()
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
</style>