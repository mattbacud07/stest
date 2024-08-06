<template>
    <!-- <v-expansion-panels class="mt-4">
        <v-expansion-panel>
            <v-expansion-panel-title><h6 class="text-primary mb-3"><b>Approval Status Tracking</b></h6></v-expansion-panel-title>
            <v-expansion-panel-text>
                
            </v-expansion-panel-text>
        </v-expansion-panel>
    </v-expansion-panels> -->
    <v-card class="mt-5" elevation="0">
        <h6 class="text-primary mb-3 mt-7"><b>Approval Status Tracking</b></h6>
        <v-timeline density="compact" align="start" side="end">
            <v-timeline-item :dot-color="status > 0 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>IT DEPARTMENT</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.IT_DEPARTMENT">
                                <!-- <span :class="[getLatest(1).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span> -->
                                <span :class="[getLatest(pub_var.IT_DEPARTMENT).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 0" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.IT_DEPARTMENT).date }}</span>
                        <span :class="['me-4',getLatest(pub_var.IT_DEPARTMENT).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(1).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 2 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>APM/NSM/SM</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.APM_NSM_SM">
                                <span :class="[getLatest(pub_var.APM_NSM_SM).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 2" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.APM_NSM_SM).date }}</span>
                        <span :class="['me-4',getLatest(pub_var.APM_NSM_SM).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(2).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 3 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>WAREHOUSE & INVENTORY MANAGEMENT</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.WIM">
                                <span :class="[getLatest(pub_var.WIM).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 3" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.WIM).date }}</span>
                        <span :class="['me-4',getLatest(pub_var.WIM).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(3).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 4 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>SERVICE DEPARTMENT TEAM LEADER</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.SERVICE_TL">
                                <span :class="[getLatest(pub_var.SERVICE_TL).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 4" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.SERVICE_TL).date }}</span>
                        <span :class="['me-4',getLatest(pub_var.SERVICE_TL).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(4).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.SERVICE_HEAD_ENGINEER ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>SERVICE DEPARTMENT HEAD / SERVICE ENGINEER</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.SERVICE_HEAD_ENGINEER">
                                <span :class="[getLatest(pub_var.SERVICE_HEAD_ENGINEER).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 5" class="d-flex flex-column text-right timeRemarks">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.SERVICE_HEAD_ENGINEER).date }}</span>
                        <span :class="['me-4',getLatest(pub_var.SERVICE_HEAD_ENGINEER).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(5).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.BILLING_WIM ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>BILLING & INVOICING STAFF / WIM PERSONNEL</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === pub_var.BILLING_WIM">
                                <span :class="[getLatest(pub_var.BILLING_WIM).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > pub_var.BILLING_WIM" class="d-flex flex timeRemarks-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(pub_var.BILLING_WIM).date }}</span>
                        <span :class="['me-4',getLatest(pub_var.BILLING_WIM).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(6).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > pub_var.INSTALLATION_TL ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>SERVICE DEPARTMENT TEAM LEADER <i>(Delegate installation)</i></strong>
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
            <v-timeline-item :dot-color="status > pub_var.INSTALLATION_ENGINEER ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>Completed <i>(Installed)</i></strong>
                        <div class="text-caption">
                            <span class="text-success">{{ fname }} {{ lname }} <span v-if="date_installed !== ''">(Assigned Engineer)</span></span>
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
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { BASE_URL } from '@/api'
import { user_data } from '@/stores/auth/userData';
import moment from 'moment'
import * as pub_var from '@/global/global'

/** Declarations */
const approvers = ref([])
const get_approval_history = ref([])
const uri = BASE_URL
const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()

const tl_assigned = ref('')
const tl_assigned_fname = ref('')
const tl_assigned_lname = ref('')
const assigned_date = ref('')
const installer = ref('')
const date_installed = ref('')
const fname = ref('')
const lname = ref('')

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
    const history = Array.from(get_approval_history.value)
    const foundApprover = !!history.find(approver => {
        return approver.user_id === user_id && approver.status === parseInt(pub_var.ONGOING)
    });
    return foundApprover
}

const getLatest = (approval_level) => {
    const historyLog = Array.from(get_approval_history.value)
    const filtered = historyLog.filter(data => data.level === approval_level)
    const getTheLatest = filtered.reduce((latest, record) => {
        const latestDate = new Date(latest)
        const recordDate = new Date(record)
        if (!latest || recordDate > latestDate) {
            return record
        } else {
            return latest
        }
    }, null)

    // console.log(pub_var.ONGOING)

    if(getTheLatest){
        const formattedDate = pub_var.formatDate(getTheLatest.created_at) //moment(getTheLatest.created_at).format('MM/D/YYYY, h:m a')
        return { date : formattedDate, remarks : getTheLatest.remarks, userId : getTheLatest.user_id, checkIfApproved : getTheLatest.status === parseInt(pub_var.DISAPPROVED) ? true : false}
    }else{
        return { date : '', remarks : '', userId : '', checkIfApproved : false}
    }
    // return getTheLatest ? moment(getTheLatest.created_at).format('MM/D/YYYY, h:m a') : ''
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

        get_approval_history.value = Array.from(data)
    } catch (error) {
        alert(error)
    }
};


/** Get Job Order Details (installer, date installed) */
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
        } else {
            alert('Something went wrong')
        }
    } catch (error) {
        console.log(error)
    }
};

onMounted(() => {
    getUsers()
    getApprovalHistory()
    getDetails()
})
</script>


<style>
.v-timeline-item__body{
    width: 100%!important;
}
.timeRemarks{
    width: 60%;
}
</style>