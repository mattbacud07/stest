<template>
    <div class="main-wrapper">
        <EngineersSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <v-form ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
                    <v-container class="container-form mt-10">
                        <v-row justify="space-between" class="topActions">
                            <div>
                                <nav class="mt-5 ml-3">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">
                                                <router-link to="/internal-servicing">
                                                    <v-icon>mdi-menu-left</v-icon> back
                                                </router-link></a></li>
                                        <li class="breadcrumb-item"><a href="#">Internal Servicing Process</a></li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="mt-3 mr-4" v-if="status === null">
                                <v-dialog v-model="dialog" max-width="400" persistent>
                                    <template v-slot:activator="{ props: activatorProps }">
                                        <v-btn :disabled="btnDisable" v-bind="activatorProps" size="small" color="error"
                                            elevation="1" class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                                            Decline</v-btn>
                                    </template>
                                    <v-card text="" title="Decline">
                                        <v-col cols="12">
                                            <v-textarea class="mr-2 ml-2" v-model="disApproveRemark" clearable
                                                label="Reason" color="primary" variant="outlined"></v-textarea>
                                            <!-- <p class="text-danger ml-3">{{ disApproveRemarkError }}</p> -->
                                        </v-col>
                                        <template v-slot:actions>
                                            <v-row justify="end">
                                                <v-btn elevation="2" @click="dialog = false" background-color="red"
                                                    size="small" color="#191970"
                                                    class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                                    Cancel</v-btn>
                                                <v-btn @click="disapproveRequest" color="primary" elevation="2"
                                                    size="small" class="text-none mr-3"
                                                    style="background-color: #191970;color: #fff!important;"><v-icon
                                                        class="mr-1">mdi-text-box-remove-outline</v-icon>
                                                    Decline</v-btn>
                                            </v-row>
                                        </template>
                                    </v-card>
                                </v-dialog>

                                <!-- Approve Button -->
                                <v-btn type="button" :loading="btnSubmitLoading" size="small"
                                    @click="acceptInternalRequest" :disabled="btnDisable" color="primary"
                                    class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                                    Accept</v-btn>
                            </div>
                            <div class="mt-3 mr-4" v-if="status === 1">
                                <!-- Approve Button -->
                                <v-btn type="button" :loading="btnSubmitLoading" size="small"
                                    @click="markAsCompleted" :disabled="btnDisable" color="primary"
                                    class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                                    Mark as Completed</v-btn>
                            </div>
                        </v-row>

                        <RequestDetails :service_id="parseInt(service_id)" />  <!-- @set-status="getStatus"  -->

                        <RequestedEquipments :service_id="parseInt(service_id)" :status="status" :editSerial="false" />

                        <!-- <ApproverHistoryLog :service_id="parseInt(service_id)" :status="status" /> -->

                        <alertMessage v-if="messageDetails.show" :details="messageDetails" />
                    </v-container>
                </v-form>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
// import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';

// import { BASE_URL } from '@/api';
import { user_data } from '@/stores/auth/userData';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue'
import ApproverHistoryLog from '@/components/Approver/EH/ApproverHistoryLog.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue'
import alertMessage from '@/components/PopupMessage/alertMessage.vue'

import Header from '@/components/layout/Header.vue'
import EngineersSidebar from '@/components/layout/Sidebars/EngineersSidebar.vue';


/** data declarations */
// const uri = BASE_URL
// const router = useRouter()
const route = useRoute()
const router = useRouter()
const user = user_data()
const apiRequest = user.apiRequest()
// user.getUserData

const dialog = ref(false)
const btnDisable = ref(false)
const btnSubmitLoading = ref(false)
const disApproveRemark = ref(null)
const messageDetails = ref({})
const internalRequestDetails = ref([])
// const getStatus = ref(null)


const id = route.params.id ?? 0
const request_id = route.params.requestedId ?? 0
const service_id = ref(id)
const requested_id = ref(request_id)
const serialNumber = ref([])
const status = ref(0)

const getInternalRequestDetails = async()=> {
    try{
        const response = await apiRequest.get('get-internal-request-details',{
            params : {
                requested_id : request_id,
            }
        })
        if(response.data && response.data.details){
            btnDisable.value = true
            internalRequestDetails.value = response.data.details[0]
            status.value = internalRequestDetails.value.status
        }
    }catch(error){
        console.log(error)
    }finally{
        btnDisable.value = false
    }
}


/** Accept Internal Request Servicing */
const acceptInternalRequest = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('accept-internal-request', {
            service_id: id,
            requested_id: request_id
        })
        if (response.data && response.data.success) {
            messageDetails.value = { show: true, color: 'success', text: 'Successfully accepted' }
            setTimeout(() => {
                router.push('/internal-servicing')
            }, 3000)
            btnDisable.value = true
        } else {
            btnDisable.value = false
            messageDetails.value = { show: true, color: 'error', text: response.data.error }
        }
    } catch (error) {
        btnDisable.value = false
        messageDetails.value = { show: true, color: 'error', text: 'Something went wrong' }
    } finally {
        btnSubmitLoading.value = false
        setTimeout(() => {
            messageDetails.value = { show: false }
        }, 3000)
    }
}

/** Mark Internal Request Servicing as Completed */
const markAsCompleted = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('internal-servicing-completed',  {
            requested_id: request_id,
            service_id: id,
        })
        if(response.data && response.data.success){
            messageDetails.value = { show: true, color: 'success', text : 'Success'}
            setTimeout(()=> {
                router.push('/internal-servicing')
            }, 3000)
        }else{
            btnDisable.value = false
            messageDetails.value = { show: true, color: 'error', text : response.data.error}
        }
    } catch (error) {
        console.log(error)
    }
    finally{
        btnSubmitLoading.value = false
        setTimeout(() => {
            messageDetails.value = { show: false }
        }, 3000)
    }
}



// const getSerialNumber = (serial) => {
//     serialNumber.value = Array.from(serial)
//     submmitApproveStatus.value = serialNumber.value.every(data => data.serial && data.serial.trim() !== '')
// }
// const getStatus = (data) => {
//     status.value = data
// }


onMounted(() => {
    getInternalRequestDetails()
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

<style>
.container-form {
    padding: 10px 10em !important;
}

.topActions {
    /* position: fixed;
    padding: 7px 2em;
    z-index: 9999;
    left: 255px;
    right: 0;
    top: 73px; */

    width: calc(100% - 240px);
    height: 60px;
    padding: 0;
    position: fixed;
    right: 0;
    left: 252px;
    top: 72px;
    background: #fff;
    border-bottom: 1px solid #3333331c;
    z-index: 99;
}
</style>