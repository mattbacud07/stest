<template>
    <v-card class="mt-5" elevation="0">
        <!-- <h6 class="text-primary mb-3 mt-2"><b>Approval Status</b></h6> -->
        <v-timeline density="compact" align="start" side="end" line-thickness="1">
            <v-timeline-item :dot-color="status > 0 ? '#555' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>IT Department</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.IT_DEPARTMENT">
                                <!-- <span :class="[getLatest(1).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span> -->
                                <span
                                    :class="[getLatest(pub_var.IT_DEPARTMENT).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 0" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.IT_DEPARTMENT).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.IT_DEPARTMENT).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.IT_DEPARTMENT).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 2 ? '#555' : '#d3d3d3'" size="extra-small">
                <!-- <template v-slot:opposite v-if="status > 2">
                    <span class="me-4 text-disabled">{{ getLatest(pub_var.APM_NSM_SM).date }}</span>
                </template> -->
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>APM/NSM/SM</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.APM_NSM_SM">
                                <span
                                    :class="[getLatest(pub_var.APM_NSM_SM).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > pub_var.APM_NSM_SM" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.APM_NSM_SM).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.APM_NSM_SM).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.APM_NSM_SM).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.WIM ? '#555' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Warehouse & Inventory Management</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.WIM">
                                <span
                                    :class="[getLatest(pub_var.WIM).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 3" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.WIM).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.WIM).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.WIM).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.SERVICE_TL ? '#555' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Service Dept Team Leader</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.SERVICE_TL">
                                <span
                                    :class="[getLatest(pub_var.SERVICE_TL).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > pub_var.SERVICE_TL" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.SERVICE_TL).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.SERVICE_TL).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.SERVICE_TL).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>

                <!-- Internal Servicing Tracks Details -->
                <v-row v-if="getInternalStatus">
                    <v-col cols="12">
                        <div class="d-flex justify-space-between">
                            <div class="mt-5">
                                Internal Status : <br /><span class="text-success">
                                    {{ getInternalStatus === pub_var.internalStat.Delegated ? 'Waiting for Acceptance'
                                        : pub_var.internalStatus(getInternalStatus).text }}
                                </span>
                            </div>
                            <div class="me-4 text-disabled text-right">{{ pub_var.formatDate(getInternalDateTimeUpdated)
                                }}<br />
                                <span class="small text-disabled"
                                    v-if="getInternalStatus === pub_var.internalStat.ConfirmedByWIM">Task completed by
                                    [<b>{{
                                        CurrentlyDelegatedTo }}</b>]</span>
                                <span class="small text-disabled" v-else>Task assigned to [<b>{{ CurrentlyDelegatedTo
                                        }}</b>]</span>
                            </div>
                        </div>

                        <span class="small text-grey"
                            v-if="getInternalStatus === pub_var.internalStat.ConfirmedByWIM">Internal
                            Process Completed</span>
                    </v-col>
                </v-row>

            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.SERVICE_HEAD_ENGINEER ? '#555' : '#d3d3d3'"
                size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Service Dept Head / Service Engineer</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.SERVICE_HEAD_ENGINEER">
                                <span
                                    :class="[getLatest(pub_var.SERVICE_HEAD_ENGINEER).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 5" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.SERVICE_HEAD_ENGINEER).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.SERVICE_HEAD_ENGINEER).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.SERVICE_HEAD_ENGINEER).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.BILLING_WIM ? '#555' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Billing & Invoicing Staff / WIM Personnel</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.BILLING_WIM">
                                <span
                                    :class="[getLatest(pub_var.BILLING_WIM).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > pub_var.BILLING_WIM" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.BILLING_WIM).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.BILLING_WIM).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.BILLING_WIM).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>

            <v-timeline-item :dot-color="status > pub_var.OUTBOUND ? '#555' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Outbound</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.OUTBOUND">
                                <span
                                    :class="[getLatest(pub_var.OUTBOUND).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                    approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > pub_var.OUTBOUND" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.OUTBOUND).date }}</span>
                        <span
                            :class="['me-4', getLatest(pub_var.OUTBOUND).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                getLatest(pub_var.OUTBOUND).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>


                <!-- Internal Servicing Tracks Details -->
                <v-row v-if="final_installation_date">
                    <v-col cols="12">
                        <div class="d-flex justify-space-between">
                            <div class="mt-5">
                                Final Installation Date : <span class="text-dark">{{  pub_var.formatDateNoTime(final_installation_date)  }} </span>
                            </div>
                        </div>
                        <span class="small text-grey">Driver : {{ driver }}</span>
                    </v-col>
                </v-row>
            </v-timeline-item>


            <!-- AREA APPROVERS -->
            <template v-if="area !== 'luzon' && area !== 'LUZON' && area !== 'Luzon'">
                <v-timeline-item :dot-color="status > pub_var.AREA_WIM ? '#555' : '#d3d3d3'" size="extra-small">
                    <div class="d-flex justify-space-between">
                        <div>
                            <strong>Area Warehouse & Inventory Management</strong>
                            <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                                <span v-if="approver.approval_level === pub_var.AREA_WIM">
                                    <span
                                        :class="[getLatest(pub_var.AREA_WIM).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                        approver.first_name }} {{ approver.last_name }}</span>
                                </span>
                            </div>
                        </div>
                        <span v-if="status > pub_var.AREA_WIM" class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4 text-disabled">{{ getLatest(pub_var.AREA_WIM).date }}</span>
                            <span
                                :class="['me-4', getLatest(pub_var.AREA_WIM).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                    getLatest(pub_var.AREA_WIM).remarks }}</span>
                        </span>
                        <span v-else>
                            <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                        </span>
                    </div>
                </v-timeline-item>
                <v-timeline-item :dot-color="status > pub_var.AREA_RSM_SPM_SNM_SM ? '#555' : '#d3d3d3'" size="extra-small">
                    <div class="d-flex justify-space-between">
                        <div>
                            <strong>Area RSM/APM/NSM/SM</strong>
                            <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                                <span v-if="approver.approval_level === pub_var.AREA_RSM_SPM_SNM_SM">
                                    <span
                                        :class="[getLatest(pub_var.AREA_RSM_SPM_SNM_SM).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                        approver.first_name }} {{ approver.last_name }}</span>
                                </span>
                            </div>
                        </div>
                        <span v-if="status > pub_var.AREA_RSM_SPM_SNM_SM" class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4 text-disabled">{{ getLatest(pub_var.AREA_RSM_SPM_SNM_SM).date }}</span>
                            <span
                                :class="['me-4', getLatest(pub_var.AREA_RSM_SPM_SNM_SM).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                    getLatest(pub_var.AREA_RSM_SPM_SNM_SM).remarks }}</span>
                        </span>
                        <span v-else>
                            <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                        </span>
                    </div>
                </v-timeline-item>
                <v-timeline-item :dot-color="status > pub_var.AREA_SERVICE_TL ? '#555' : '#d3d3d3'" size="extra-small">
                    <div class="d-flex justify-space-between">
                        <div>
                            <strong>Area Service TL & Service Engineer</strong>
                            <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                                <span v-if="approver.approval_level === pub_var.AREA_SERVICE_TL">
                                    <span
                                        :class="[getLatest(pub_var.AREA_SERVICE_TL).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                        approver.first_name }} {{ approver.last_name }}</span>
                                </span>
                            </div>
                        </div>
                        <span v-if="status > pub_var.AREA_SERVICE_TL" class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4 text-disabled">{{ getLatest(pub_var.AREA_SERVICE_TL).date }}</span>
                            <span
                                :class="['me-4', getLatest(pub_var.AREA_SERVICE_TL).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                    getLatest(pub_var.AREA_SERVICE_TL).remarks }}</span>
                        </span>
                        <span v-else>
                            <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                        </span>
                    </div>
                </v-timeline-item>
                <v-timeline-item :dot-color="status > pub_var.AREA_BILLING_WIM ? '#555' : '#d3d3d3'" size="extra-small">
                    <div class="d-flex justify-space-between">
                        <div>
                            <strong>Area Billing and Invoicing Staff</strong>
                            <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                                <span v-if="approver.approval_level === pub_var.AREA_BILLING_WIM">
                                    <span
                                        :class="[getLatest(pub_var.AREA_BILLING_WIM).userId === approver.user_id ? 'text-success' : 'text-black']">{{
                                        approver.first_name }} {{ approver.last_name }}</span>
                                </span>
                            </div>
                        </div>
                        <span v-if="status > pub_var.AREA_BILLING_WIM" class="d-flex flex-column text-right timeRemarks">
                            <span class="me-4 text-disabled">{{ getLatest(pub_var.AREA_BILLING_WIM).date }}</span>
                            <span
                                :class="['me-4', getLatest(pub_var.AREA_BILLING_WIM).checkIfApproved ? 'text-danger' : 'text-primary']">{{
                                    getLatest(pub_var.AREA_BILLING_WIM).remarks }}</span>
                        </span>
                        <span v-else>
                            <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                        </span>
                    </div>
                </v-timeline-item>


            </template>






            <!-- For Installation -->

            <v-timeline-item :dot-color="status > pub_var.INSTALLATION_TL ? '#555' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Team Leader <span v-if="SSU !== ''">({{ SSU }})</span> <span v-else>(Team Leader per
                                SSU)</span></strong><i class="small">
                            (Delegate installation)</i>
                        <div class="text-caption">
                            <span class="text-success">{{ tl_assigned_fname }} {{ tl_assigned_lname }}</span>
                        </div>
                    </div>
                    <span v-if="assigned_date !== ''" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ pub_var.formatDate(assigned_date) }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.INSTALLATION_ENGINEER ? '#555' : '#d3d3d3'"
                size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Completed <i>(Installed)</i></strong>
                        <div class="text-caption">
                            <!-- <span v-if="date_installed !== '' || date_installed !== null">(Assigned Engineer)</span> -->
                            <span class="text-primary"><i v-if="date_installed !== ''">Installer : </i>{{ fname }} {{
                                lname
                                }}</span>
                        </div>
                    </div>
                    <span v-if="date_installed !== ''" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ pub_var.formatDate(date_installed) }}</span>
                        <!-- <span :class="['me-4',getLatest(pub_var.INSTALLATION_ENGINEER).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(6).remarks }}</span> -->
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
        </v-timeline>
    </v-card>
</template>

<script setup>
import { ref, onMounted, inject } from 'vue'
import { user_data } from '@/stores/auth/userData';
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'
import moment from 'moment';

/** Declarations */
const approvers = ref([])
const get_approval_history = ref([])
const user = user_data()
user.getUserData
const apiRequest = apiRequestAxios()

const tl_assigned = ref('')
const tl_assigned_fname = ref('')
const tl_assigned_lname = ref('')
const assigned_date = ref('')
const installer = ref('')
const date_installed = ref('')
const fname = ref('')
const lname = ref('')
const SSU = ref('')

const props = defineProps({
    status: {
        type: Number,
        default: () => 0
    },
    service_id: {
        type: Number,
    }
})



/** Functions */
// Set active base on approval history
// const active = ref(false)
const setActive = (user_id) => {
    const foundApprover = !!get_approval_history.value.find(approver => {
        return approver.user_id === user_id && approver.status === parseInt(pub_var.ONGOING)
    });
    return foundApprover
}


const getLatest = (approval_level) => {
    const filtered = get_approval_history.value.filter(data => data.level === approval_level)
    const getTheLatest = filtered.reduce((latest, record) => {
        const latestDate = new Date(latest?.created_at)
        const recordDate = new Date(record.created_at)

        if (!record) return latest;
        if (!latest) return record
        return recordDate > latestDate ? record : latest
    }, null)

    if (getTheLatest) {
        const formattedDate = pub_var.formatDate(getTheLatest.created_at) //moment(getTheLatest.created_at).format('MM/D/YYYY, h:m a')
        return { date: formattedDate, remarks: getTheLatest.remarks, userId: getTheLatest.user_id, checkIfApproved: getTheLatest.status === parseInt(pub_var.DISAPPROVED) ? true : false }
    } else {
        return { date: '', remarks: '', userId: '', checkIfApproved: false }
    }
}

//Get Approvers
const getUsers = async () => {
    try {
        const response = await apiRequest.get('get-approver');
        const data = response.data.users

        approvers.value = data
    } catch (error) {
        alert(error)
    }
};

//Get Approval History
const getApprovalHistory = async () => {
    try {
        const response = await apiRequest.get('get-approval-history', {
            params: {
                service_id: props.service_id,
            }
        });
        const data = response.data.approval_history

        get_approval_history.value = data
    } catch (error) {
        alert(error)
    }
};


/** Get Job Order Details (installer, date installed) */
const area = ref(null)
const final_installation_date = ref(null)
const driver = ref('')
const getDetails = async () => {
    try {
        const response = await apiRequest.get('get-specific-equipment-handling', {
            params: {
                service_id: props.service_id,
            }
        });
        if (response.data && response.data.equipment_handling) {
            const data = response.data.equipment_handling

            const field = data[0]

            tl_assigned.value = field.tl_assigned
            tl_assigned_fname.value = field.assigned_tl_fname
            tl_assigned_lname.value = field.assigned_tl_lname
            assigned_date.value = field.assigned_date
            installer.value = field.installer
            fname.value = field.fname
            lname.value = field.lname
            date_installed.value = field.date_installed
            final_installation_date.value = field.final_installation_date
            driver.value = field.driver
            SSU.value = field.ssu
            area.value = field.area
        } else {
            alert('Something went wrong')
        }
    } catch (error) {
        console.log(error)
    }
};



/** Check Status of Request if Delegated to an Engineer */
const getInternalStatus = ref(null)
const getInternalDateTimeUpdated = ref(null)
const CurrentlyDelegatedTo = ref(null)
const checkIfDelegatedToEngineer = async () => {
    try {
        const response = await apiRequest.get('getInternalRequest', {
            params: { service_id: props.service_id, category: 'specificService' }
        })
        if (response.data && response.data.request && response.data.request.length > 0) {
            const result = response.data.request
            getInternalStatus.value = result.map((data) => data.status)[0]
            getInternalDateTimeUpdated.value = result.map((data) => data.updated_at)[0]
            const fullname = result.map((data) => ({ fname: data.get_user.first_name, lname: data.get_user.last_name }))[0]
            CurrentlyDelegatedTo.value = fullname.fname + ' ' + fullname.lname
        }
    } catch (error) {
        alert(error)
    }
}

onMounted(() => {
    getUsers()
    getApprovalHistory()
    getDetails()
    checkIfDelegatedToEngineer()
})
</script>


<style>
.v-timeline-item__body {
    width: 100% !important;
}

.timeRemarks {
    width: 60%;
}

.v-timeline-divider__dot .v-icon {
    width: 8px !important;
    min-width: 8px !important;
    height: 8px !important;
}
</style>