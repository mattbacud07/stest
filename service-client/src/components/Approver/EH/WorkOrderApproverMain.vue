<template>
    <div>
        <v-form ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
            <v-container class="container-form mt-10">
                <v-row justify="space-between" class="topActions">
                    <div>
                        <nav class="mt-5 ml-3">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">
                                        <router-link to="/approver-equipment-handling">
                                            <v-icon>mdi-menu-left</v-icon> back
                                        </router-link></a></li>
                                <li class="breadcrumb-item"><a href="#">Equipment Handling</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Work Order</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="mt-3 mr-4">
                        <div v-if="exist_service_id === false">
                            <v-dialog v-model="dialog" max-width="400" persistent>
                                <template v-slot:activator="{ props: activatorProps }">
                                    <v-btn :disabled="btnDisable" v-bind="activatorProps" size="small" color="error"
                                        elevation="1" class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                                        Disapprove</v-btn>
                                </template>
                                <v-card text="" title="Disapprove">
                                    <v-col cols="12">
                                        <v-textarea class="mr-2 ml-2" v-model="disApproveRemark" clearable
                                            label="Reason of Disapproval" color="primary"
                                            variant="outlined"></v-textarea>
                                        <p class="text-danger ml-3">{{ disApproveRemarkError }}</p>
                                    </v-col>
                                    <template v-slot:actions>
                                        <v-row justify="end">
                                            <v-btn elevation="2" @click="dialog = false" background-color="red"
                                                size="small" color="#191970"
                                                class="text-none mr-2"><v-icon>mdi-close</v-icon>
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
                                    <v-btn type="button" size="small" v-bind="activatorProps" :disabled="btnDisable"
                                        color="primary" class="text-none btnSubmit"><v-icon
                                            class="mr-2">mdi-check</v-icon>
                                        Approve</v-btn>
                                </template>
                                <v-card text="" title="Approve">
                                    <v-col cols="12">
                                        <v-textarea class="mr-2 ml-2" v-model="remark" clearable
                                            label="Remarks (optional)" color="primary" variant="outlined"></v-textarea>
                                    </v-col>
                                    <template v-slot:actions>
                                        <v-row justify="end">
                                            <v-btn elevation="2" @click="dialogApprove = false" background-color="red"
                                                size="small" color="#191970"
                                                class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                                Cancel</v-btn>
                                            <v-btn type="button" size="small" :loading="btnLoading"
                                                :disabled="btnDisable" color="#191970" class="text-none bg-primary mr-5"
                                                @click="approveRequest"><v-icon class="mr-2">mdi-check</v-icon>
                                                Approve</v-btn>
                                        </v-row>
                                    </template>
                                </v-card>
                            </v-dialog>
                        </div>
                        <div v-if="exist_service_id === true"><p style="color: red;">* Internal Processing in Progress</p></div>
                    </div>
                </v-row>

                <RequestDetails :service_id="parseInt(service_id)"
                    :showInternalRequest="user.user.approval_level === pub_var.SERVICE_TL ? true : false" />

                <RequestedEquipments :service_id="parseInt(service_id)" @set-serial="getSerialNumber"
                    :editSerial="true" />

                <!-- <ApproverHistoryLog :service_id="service_id"/> -->


                <v-snackbar color="error" v-model="snackbarErrorGeneral" location="right bottom" :timeout="3000">
                    <v-icon>mdi-information-box</v-icon> Please ensure all serial number fields are filled in.
                </v-snackbar>
                <v-snackbar color="success" v-model="snackbarSuccess" location="right bottom" :timeout="5000">
                    <v-icon>mdi-check-circle-outline</v-icon> Approved successfully.
                </v-snackbar>
                <v-snackbar color="warning" v-model="snackbarSuccessDisapproved" location="right bottom"
                    :timeout="5000">
                    <v-icon>mdi-check-circle-outline</v-icon> Disapproved successfully.
                </v-snackbar>
                <v-snackbar color="error" v-model="snackbarError" location="right bottom" :timeout="5000">
                    <v-icon>mdi-alert-circle-outline</v-icon> Something went wrong.
                </v-snackbar>
            </v-container>
        </v-form>
    </div>
</template>
<script setup>
import { ref, watch, onMounted } from 'vue';
import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { BASE_URL } from '@/api';
import { user_data } from '@/stores/auth/userData';
import Axios from 'axios';
import RequestedEquipments from './RequestedEquipments.vue'
// import ApproverHistoryLog from './ApproverHistoryLog.vue'
import RequestDetails from './RequestDetails.vue'
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
const exist_service_id = ref(null)


/** Data functions */
const approveRequest = async () => {
    btnLoading.value = true
    if (user.user.approval_level === 1) {
        if (!submmitApproveStatus.value) {
            snackbarErrorGeneral.value = true
            btnLoading.value = false
            return
        }
    }
    // console.log(serialNumber.value.map(item => item.id))
    try {
        const response = await Axios.post(
            uri + 'api/approve-request',
            {
                approval_level: user.user.approval_level,
                id: user.user.id,
                item: serialNumber.value,
                service_id: id,
                remark: remark.value,

            },
            {
                withCredentials: true,
                headers: {
                    'Authorization': `Bearer ${user.tokenData}`,
                }
            }
        )
        if (response.data && response.data.success) {
            snackbarSuccess.value = true
            btnDisable.value = true
            setTimeout(() => {
                router.push('/approver-equipment-handling')
            }, 2000)
        }
        else {
            alert(response.data.error)
            snackbarError.value = true
        }
    } catch (error) {
        alert(error)
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
                router.push('/approver-equipment-handling')
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
const CheckDelegateInternalServicing = async () => {
    try {
        const response = await apiRequest.get('check_service_id_exist_internal', {
            params : { service_id: id, }
        })
        if(response.data && response.data.exist_service_id){
            exist_service_id.value = true
        }else{
            exist_service_id.value = false
        }
    } catch (error) {
        alert(error)
    }
}


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


onMounted(() => {
    if (user.user.approval_level !== 1) {
        btnDisable.value = false
    }
    CheckDelegateInternalServicing()
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