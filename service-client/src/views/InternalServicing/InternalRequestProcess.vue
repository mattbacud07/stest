<template>
    <LayoutSinglePage>
        <template #topBarFixed>
            <v-breadcrumbs class="pt-7">
                        <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index"
                            :class="{ 'custom-pointer': !item.disabled} " :style="{'display' : width <= 768 ? item.display : ''}" @click="navigateTo(item)"
                            :disabled="item.disabled">
                            {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                        </v-breadcrumbs-item>
                    </v-breadcrumbs>
            <v-spacer></v-spacer>

            <!-- Actions for Engineers -->
            <div v-if="role.currentUserRole === pub_var.engineerRole">
                <span v-if="status === pub_var.internalStat.Delegated">
                    <v-dialog v-model="dialog" max-width="400" persistent>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn :disabled="btnDisable" v-bind="activatorProps"  color="primary"
                                variant="tonal" class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                                Decline</v-btn>
                        </template>
                        <v-card text="" title="Decline">
                            <v-col cols="12">
                                <v-textarea class="mr-2 ml-2" v-model="disApproveRemark" clearable label="Reason"
                                    color="primary" variant="outlined"></v-textarea>
                                <!-- <p class="text-danger ml-3">{{ disApproveRemarkError }}</p> -->
                            </v-col>
                            <template v-slot:actions>
                                <v-row justify="end">
                                    <v-btn elevation="2" @click="dialog = false" background-color="red" 
                                        color="#191970" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                        Cancel</v-btn>
                                    <v-btn @click="declineTask" color="primary" elevation="2" 
                                        class="text-none mr-3"
                                        style="background-color: #191970;color: #fff!important;"><v-icon
                                            class="mr-1">mdi-close</v-icon>
                                        Decline</v-btn>
                                </v-row>
                            </template>
                        </v-card>
                    </v-dialog>

                    <!-- Approve Button -->
                    <v-btn type="button" :loading="btnSubmitLoading"  @click="acceptInternalRequest"
                        :disabled="btnDisable" color="primary" variant="flat" class="text-none btnSubmit"><v-icon
                            class="mr-2">mdi-check</v-icon>
                        Accept</v-btn>
                </span>

                <span v-if="status === pub_var.internalStat.InProgress">
                    <!-- Approve Button -->
                    <v-dialog v-model="dialog" max-width="400" persistent>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn type="button" v-bind="activatorProps" :loading="btnSubmitLoading" 
                                :disabled="btnDisable" color="primary" variant="flat"
                                class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                                Set as Done</v-btn>
                        </template>
                        <v-card text="" title="Actions Done">
                            <v-col cols="12">
                                <v-textarea class="mr-2 ml-2 mb-3" v-for="item in serialNumber" :key="item.key"
                                    v-model="item.actions_done" clearable
                                    :label="'Actions Done in Serial -' + item.serial_number" color="primary"
                                    variant="outlined"></v-textarea>
                                <!-- <v-text-field :label="'Actions Done in Serial -'+ item.serial_number" density="compact" color="primary" variant="outlined" v-for="item in serialNumber" :key="item.key"></v-text-field> -->
                            </v-col>
                            <template v-slot:actions>
                                <v-row justify="end">
                                    <v-btn elevation="2" @click="dialog = false" background-color="red" 
                                        color="#191970" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                        Cancel</v-btn>
                                    <v-btn @click="markAsCompleted" color="primary"
                                        class="text-none mr-3" :loading="btnSubmitLoading"
                                        style="background-color: #191970;color: #fff!important;"><v-icon
                                            class="mr-1">mdi-check</v-icon>
                                        Set as Done</v-btn>
                                </v-row>
                            </template>
                        </v-card>
                    </v-dialog>
                </span>
                <span v-if="status === pub_var.internalStat.Completed">
                    <!-- Approve Button -->
                    <v-btn type="button" :loading="btnSubmitLoading"  @click="markAsPackedEndorsed"
                        :disabled="btnDisable" color="primary" variant="flat" class="text-none btnSubmit"><v-icon
                            class="mr-2">mdi-check</v-icon>
                        Packed & Sent to Warehouse</v-btn>
                </span>
            </div>
            <!-- Actions for WIM Personnel -->
            <div v-else>
                <span v-if="status === pub_var.internalStat.Packed">
                    <!-- Approve Button -->
                    <v-btn type="button" :loading="btnSubmitLoading"  @click="markAsPackedEndorsed"
                        :disabled="btnDisable" color="primary" variant="flat" class="text-none btnSubmit"><v-icon
                            class="mr-2">mdi-check</v-icon>
                            Mark as Confirmed</v-btn>
                </span>
            </div>
        </template>
        <v-form ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->

            <template #default>
                <v-container class="container-form mt-7">
                    <InternalServicingActivity :id="parseInt(internalId)" :key="refreshKey"/>
                    <h4 class="text-primary mt-5">Request Details</h4>
                    <RequestDetails :service_id="parseInt(service_id)" /> <!-- @set-status="getStatus"  -->

                    <RequestedEquipments :service_id="parseInt(service_id)" :status="status" :editSerial="false"
                        @get-serials="getSerials" />

                    <alertMessage v-if="messageDetails.show" :details="messageDetails" />
                </v-container>
            </template>
        </v-form>
    </LayoutSinglePage>
</template>
<script setup>
import { ref, onMounted, inject } from 'vue';
// import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';
import { getRole } from '@/stores/getRole'

const role = getRole()
role.getRoleData

import { user_data } from '@/stores/auth/userData';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue'
import ApproverHistoryLog from '@/components/Approver/EH/ApproverHistoryLog.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue'
import alertMessage from '@/components/PopupMessage/alertMessage.vue'
import * as pub_var from '@/global/global.js'

import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';
import InternalServicingActivity from '@/components/Approver/EH/InternalServicingActivity.vue'

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

/** Toast Notification */
import {useToast} from 'vue-toast-notification'
const toast = useToast()


const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/internal-servicing', display : 'block' },
    { title: 'Equipment Handling', disabled: true, href: '', display : 'none' },
    { title: 'Internal Servicing', disabled: true, href: '', display : 'none' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};

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
const refreshKey = ref(0)
const messageDetails = ref({})
const internalRequestDetails = ref([])
// const getStatus = ref(null)

const type_of_activity = ref('')


const id = route.params.id ?? 0
const internalId = route.params.service_id ?? 0
const service_id = ref(id)
const requested_id = ref(internalId)
const serialNumber = ref([])
const status = ref(null)

/** get Serial Numbers Input */
const getSerials = (serials) => {
    serialNumber.value = Array.from(serials)
}




const getInternalRequest = async () => {
    try {
        const response = await apiRequest.get('getInternalRequest', {
            params: { id: internalId, category: 'specificRow' }
        })
        if (response.data && response.data.request && response.data.request.length > 0) {
            btnDisable.value = true
            const data = response.data.request[0]
            status.value = data.status
            type_of_activity.value = data.type_of_activity_name
        }
    } catch (error) {
        console.log(error)
    } finally {
        btnDisable.value = false
    }
}

/** Decline Internal Request Servicing */
const declineTask = async () => {

}


/** Accept Internal Request Servicing */
const acceptInternalRequest = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    try {
        const response = await apiRequest.post('accept-internal-request', {
            service_id: id,
            requested_id: internalId
        })
        if (response.data && response.data.success) {
            toast.success('Successfully accepted')
            getInternalRequest()
            refreshKey.value++
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

/** Mark Internal Request Servicing as Completed */
const markAsCompleted = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    const dataToSubmit = serialNumber.value.map((data) => ({
        service_id: data.service_id,
        equipment_id: data.equipment_id,
        work_type_id: internalId,
        serial: data.serial_number,
        action: data?.actions_done ?? '',
        work_type: 'Internal'
    }))
    // console.log('Serial Number Data:', dataToSubmit);
    try {
        const response = await apiRequest.post('internal-servicing-completed', {
            arrayOfData: dataToSubmit, requested_id: internalId
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            getInternalRequest()
            refreshKey.value++
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




/** Mark Internal Request Servicing as Packed and Endorsed to Warehouse [Update or Confirmed by WIM] */
const markAsPackedEndorsed = async () => {
    btnDisable.value = true
    btnSubmitLoading.value = true
    // console.log('Serial Number Data:', dataToSubmit);
    try {
        const response = await apiRequest.post('markPackedEndorsedToWarehouse', {
            requested_id: internalId, status : status.value, service_id : id
        })
        if (response.data && response.data.success) {
            toast.success('Operation completed successfully')
            toast.success('Send to warehouse for confirmation',{
                autoClose : 7000
            })
            getInternalRequest()
            refreshKey.value++
        } else {
            btnDisable.value = false
            toast.error(response.data.error)
        }
    } catch (error) {
        console.log(error)
    }
    finally {
        btnSubmitLoading.value = false
    }
}




onMounted(() => {
    getInternalRequest()
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
