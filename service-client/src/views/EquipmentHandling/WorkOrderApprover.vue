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
            <div v-if="getInternalStatus === pub_var.internalStat.Delegated || getInternalStatus === pub_var.internalStat.InProgress || getInternalStatus === pub_var.internalStat.Packed
                || getInternalStatus === pub_var.internalStat.Completed">
                <p style="color: orange;">* Internal Processing Still in Progress</p>
            </div>
            <div v-else>
                <template v-if="can('approve', 'Equipment Handling') && user.user.approval_level === status">
                    <v-dialog v-model="dialog" max-width="400" persistent>
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn :disabled="btnDisable" v-bind="activatorProps" color="primary" variant="tonal"
                                class="text-none mr-2">
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
                    <v-dialog v-model="dialogApprove" max-width="400" persistent>
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
                                    <v-select color="primary" class="mr-2 ml-2 mt-5" label="Set SSU" density="compact"
                                        variant="outlined" :items="ssuToArray(pub_var.ssu)" item-title="text"
                                        item-value="value" item v-model="ssu" :rules="ssuRule"
                                        v-if="pub_var.SERVICE_HEAD_ENGINEER === user.user.approval_level"></v-select>

                                    <template v-if="pub_var.OUTBOUND === user.user.approval_level">
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


                <!-- dialogApproveNonApprover Button -->
                <template
                    v-if="(pub_var.INSTALLATION_ENGINEER === status && user_current_ssu === updatedSSU && can('installer', 'Equipment Handling')) || (pub_var.INSTALLATION_TL === status && user_current_ssu === updatedSSU && can('delegate', 'Equipment Handling'))">
                    <v-dialog v-model="dialogApproveNonApprover" max-width="600" persistent>
                        <template v-slot:activator="{ props: activatorProps }">

                            <v-btn type="button" v-bind="activatorProps" :disabled="btnDisable" color="primary"
                                variant="flat" class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                                {{ can('delegate', 'Equipment Handling') ? 'Delegate Installation' : 'Installation Complete'}}
                            </v-btn>
                            <!-- {{ pub_var.INSTALLATION_ENGINEER }} {{  status  }}   {{  user_current_ssu }} {{  updatedSSU }} -->
                        </template>
                        <v-card text="" title="Approve">
                            <v-form @submit.prevent="approveRequest" ref="form">
                                <v-col cols="12">
                                    <v-textarea class="mr-2 ml-2" v-model="remark" clearable label="Remarks (optional)"
                                        color="primary" variant="outlined"></v-textarea>

                                    <template
                                        v-if="pub_var.INSTALLATION_TL === status && user_current_ssu === updatedSSU">
                                        <v-combobox color="primary" class="ml-2 mr-2 mt-5" v-model="Engineers"
                                            :rules="[v => !!v || 'Required']" label="Assign Engineer"
                                            placeholder="Assign to" density="compact" :items="engineersData"
                                            variant="outlined" itemValue="value" itemTitle="key"></v-combobox>
                                    </template>

                                    <template
                                        v-if="pub_var.INSTALLATION_ENGINEER === status && user_current_ssu === updatedSSU">
                                        <v-textarea class="mr-2 ml-2 mt-3" v-for="item in serialNumberDone"
                                            :key="item.key" v-model="item.actions_done" clearable
                                            :label="'Actions Done in Serial -' + item.serial_number" color="primary"
                                            variant="outlined"></v-textarea>
                                    </template>

                                </v-col>

                                <v-divider></v-divider>
                                <v-row justify="end" class="mt-7 mb-5 pr-3">
                                    <v-btn variant="tonal" @click="dialogApproveNonApprover = false" size="small"
                                        color="primary" class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                        Cancel</v-btn>
                                    <v-btn type="submit" size="small" :loading="btnLoading" :disabled="btnDisable"
                                        color="#191970" flat class="text-none bg-primary mr-5"><v-icon
                                            class="mr-2">mdi-check</v-icon>
                                        Approve</v-btn>
                                </v-row>

                            </v-form>
                        </v-card>
                    </v-dialog>
                </template>
            </div>
        </template>
        <!-- <v-form ref="form"> @submit.prevent="submitWorkOrder" ref="form" -->
        <v-container class="container-form mt-3">
            <RequestDetails :service_id="parseInt(service_id)"
                :showInternalRequest="user.user.approval_level === pub_var.SERVICE_TL ? true : false"
                @set-status="getStatus" @set-updated-ssu="getUpdatedSSU"
                @get-installation-engineer="getInstallationEngineer" :internalStatus="getInternalStatus"
                :internalDelegatedTo="CurrentlyDelegatedTo" :internalID="internalID"
                :internalServicing="internalServicing"/>

                <v-card class="mt-10">
                        <v-tabs v-model="tab" density="compact" class="border-b-sm" bg-color="grey-lighten-5">
                            <v-tab value="request_type" class="text-none" color="primary"><v-icon class="mr-2">mdi-tooltip-text-outline</v-icon> Request Type</v-tab>
                            <v-tab value="equipments" class="text-none" color="primary"><v-icon
                                    class="mr-2">mdi-hammer-screwdriver</v-icon> Requested Equipments</v-tab>
                            <v-tab value="history" class="text-none" color="primary"><v-icon class="mr-2">mdi-account-details</v-icon> Approval Requirements</v-tab>
                        </v-tabs>

                    <v-card-text>
                        <v-window v-model="tab" :disabled="true">
                            <v-window-item value="request_type">
                                <RequestType :service_id="parseInt(service_id)" />
                            </v-window-item>
                            <v-window-item value="equipments">
                                <RequestedEquipments :service_id="parseInt(service_id)" @set-serial="getSerialNumber"
                                    @get-serials="getSerials" :editSerial="status !== 1 ? false : true" />
                            </v-window-item>
                            <v-window-item value="history">
                                <ApproverHistoryLog :service_id="parseInt(service_id)" :status="status" />
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

const user_current_ssu = ref(user.user.user_roles?.filter(d => d.role_id === currentUserRole)[0]?.SSU, null)

const tab = ref('equipments') //TAB

const form = ref(false)
const dialog = ref(false)
const dialogApprove = ref(false)
const dialogApproveNonApprover = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const id = route.params.id
const service_id = ref(id)
const serialNumber = ref([])
const submmitApproveStatus = ref(false)
const remark = ref('')
const ssu = ref('')
const getInternalStatus = ref(null)
const getInternalDateTimeUpdated = ref(null)
const CurrentlyDelegatedTo = ref(null)
const internalID = ref(null)
const internalServicing = ref({})
const status = ref(0)
const exist_service_id = ref(null)
const Engineers = ref('')
const serialNumberDone = ref([])

const updatedSSU = ref('')


/** get Serial Numbers Input */
const getSerials = (serials) => {
    serialNumberDone.value = Array.from(serials)
    // console.log(serialNumberDone.value)
}

const outboundFinalization = ref({
    final_installation_date: '',
    driver: ''
})


/** ssuToArray */
const ssuToArray = (obj) => {
    return Object.keys(obj).map(k => ({ text: 'SSU' + obj[k], value: 'SSU' + obj[k] }))
}
// SSU Rule
const ssuRule = ref([
    v => !!v || 'Required'
])
watch(ssuRule, (val) => {
    // console.log(val)
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
    const actionsDone = serialNumberDone.value.map((data) => ({
        service_id: data.service_id,
        equipment_id: data.equipment_id,
        serial: data.serial_number,
        action: data?.actions_done ?? '',
        work_type: 'EquipmentHandling'
    }))

    if (user.user.approval_level === pub_var.IT_DEPARTMENT) {
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
    // console.log(serialNumber.value.map(item => item.id))
    try {
        const response = await apiRequest.post('approve-request', {
            approval_level: user.user.approval_level,
            id: user.user.id,
            item: serialNumber.value,
            service_id: id,
            ssu: ssu.value,
            engineer: Engineers.value.value,
            status: status.value,
            remark: remark.value,
            ...outboundFinalization.value,
            actionsDone: actionsDone ?? []

        })
        if (response.data && response.data.success) {
            btnDisable.value = true
            toast.success('Approved successfully')
            router.push('/equipment-handling')
        }
        else {
            console.log(response.data.error)
            toast.error('Something went wrong')
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
            toast.success('Disapproval successful')
            router.push('/equipment-handling')
        }
    } catch (error) {
        alert(error)
    }
    finally {

    }
}


/** Check Status of Request if Delegated to an Engineer */
const checkIfDelegatedToEngineer = async () => {
    try {
        const response = await apiRequest.get('getInternalRequest', {
            params: { service_id: id, category: 'specificService' }
        })
        if (response.data && response.data.request && response.data.request.length > 0) {
            const result = response.data.request[0]
            getInternalStatus.value = result.status
            internalID.value = result.id
            getInternalDateTimeUpdated.value = result.updated_at
            CurrentlyDelegatedTo.value = result.get_user.first_name + ' ' + result.get_user.last_name
            internalServicing.value = result
        }
    } catch (error) {
        alert(error)
    }
}


/**
 * Get Engineers Data - Use for Delegation
 */
const engineersData = ref([])
const getEngineersData = async () => {
    try {
        const response = await apiRequest.get('get-engineers-data')
        if (response.data && response.data.engineers) {
            const filteredEngineersData = response.data.engineers.filter(data => data.SSU === updatedSSU.value)
            const engineersValue = filteredEngineersData.map(data => {
                return {
                    key: data.users.first_name + ' ' + data.users.last_name,
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
 * Get Serial Number Input from Additional Peripheral Table
 */
const getSerialNumber = (serial) => {
    serialNumber.value = Array.from(serial)
    submmitApproveStatus.value = serialNumber.value.every(data => data.serial && data.serial.trim() !== '')
}

/** Get Status */
const getStatus = (data) => {
    status.value = data
}

/** Get Updated SSU */
const getUpdatedSSU = (data) => {
    updatedSSU.value = data
}

/** Get Installation Engineer */
const installationEngineer = ref(null)
const getInstallationEngineer = (installer) => {
    installationEngineer.value = installer
}

/** Disable Approver Button */
const disableButton = () => {
    btnDisable.value = true
}
provide('disableButton', disableButton)

onMounted(async () => {
    btnDisable.value = true
    await checkIfDelegatedToEngineer()
    await getEngineersData()
    if (user.user.approval_level !== 1) {
        btnDisable.value = false
    }
    btnDisable.value = false
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