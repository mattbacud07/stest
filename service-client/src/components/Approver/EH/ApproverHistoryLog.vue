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
                            <span v-if="approver.approval_level === 1">
                                <!-- <span :class="[getLatest(1).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span> -->
                                <span :class="[getLatest(1).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <div v-if="status > 0" class="d-flex flex-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(1).date }}</span>
                        <span :class="['me-4',getLatest(1).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(1).remarks }}</span>
                    </div>
                    <div v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </div>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 2 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>APM/NSM/SM</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === 2">
                                <span :class="[getLatest(2).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 2" class="d-flex flex-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(2).date }}</span>
                        <span :class="['me-4',getLatest(2).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(2).remarks }}</span>
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
                            <span v-if="approver.approval_level === 3">
                                <span :class="[getLatest(3).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 3" class="d-flex flex-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(3).date }}</span>
                        <span :class="['me-4',getLatest(3).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(3).remarks }}</span>
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
                            <span v-if="approver.approval_level === 4">
                                <span :class="[getLatest(4).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 4" class="d-flex flex-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(4).date }}</span>
                        <span :class="['me-4',getLatest(4).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(4).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 5 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>SERVICE DEPARTMENT HEAD / SERVICE ENGINEER</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === 5">
                                <span :class="[getLatest(5).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 5" class="d-flex flex-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(5).date }}</span>
                        <span :class="['me-4',getLatest(5).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(5).remarks }}</span>
                    </span>
                    <span v-else>
                        <span class="me-4 text-disabled">00-00-00 00-00 00 0</span>
                    </span>
                </div>
            </v-timeline-item>
            <v-timeline-item :dot-color="status > 6 ? 'primary' : '#d3d3d3'" size="extra-small">
                <div class="d-flex justify-space-between">
                    <div>
                        <strong>BILLING & INVOICING STAFF / WIM PERSONNEL</strong>
                        <div class="text-caption" v-for="(approver, index) in approvers" :key="index">
                            <span v-if="approver.approval_level === 6">
                                <span :class="[getLatest(6).userId === approver.user_id ? 'text-success' : 'text-black']">{{ approver.first_name }} {{ approver.last_name }}</span>
                            </span>
                        </div>
                    </div>
                    <span v-if="status > 6" class="d-flex flex-column text-right">
                        <span class="me-4 text-disabled">{{ getLatest(6).date }}</span>
                        <span :class="['me-4',getLatest(6).checkIfApproved ? 'text-danger' : 'text-primary']">{{ getLatest(6).remarks }}</span>
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
        const formattedDate = moment(getTheLatest.created_at).format('MM/D/YYYY, h:m a')
        return { date : formattedDate, remarks : getTheLatest.remarks, userId : getTheLatest.user_id, checkIfApproved : getTheLatest.status === parseInt(pub_var.DISAPPROVED) ? true : false}
    }else{
        return { date : '', remarks : '', userId : '', checkIfApproved : false}
    }
    // return getTheLatest ? moment(getTheLatest.created_at).format('MM/D/YYYY, h:m a') : ''
}

//Get Approvers
const getUsers = async () => {
    try {
        const response = await axios.get(uri + 'api/get-approver', {
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.users

        approvers.value = data
    } catch (error) {
        alert(error)
    }
};

//Get Approval History
const getApprovalHistory = async () => {
    try {
        const response = await axios.get(uri + 'api/get-approval-history', {
            params: {
                service_id: props.service_id,
            },
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.approval_history

        get_approval_history.value = Array.from(data)
    } catch (error) {
        alert(response.data.error ?? error)
    }
};

onMounted(() => {
    getUsers()
    getApprovalHistory()
})
</script>


<style>
.v-timeline-item__body{
    width: 80%!important;
}
</style>