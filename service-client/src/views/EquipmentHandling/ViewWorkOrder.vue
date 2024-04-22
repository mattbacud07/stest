<template>
    <div class="main-wrapper">
        <Sidebar />
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
                                                <router-link to="/equipment-handling">
                                                    <v-icon>mdi-menu-left</v-icon> back
                                                </router-link></a></li>
                                        <li class="breadcrumb-item"><a href="#">Equipment Handling</a></li>
                                        <li class="breadcrumb-item active" aria-current="page">Work Order</li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="mt-3 mr-4">
                            </div>
                        </v-row>

                        <RequestDetails :service_id="parseInt(service_id)" @set-status="getStatus"/>

                        <RequestedEquipments :service_id="parseInt(service_id)" :status="status" :editSerial="false"/>

                        <ApproverHistoryLog :service_id="parseInt(service_id)" :status="status"/>
                    </v-container>
                </v-form>
            </div>
        </div>
    </div>
</template>
<script setup>
import { ref } from 'vue';
// import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';

// import { BASE_URL } from '@/api';
// import { user_data } from '@/stores/auth/userData';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue'
import ApproverHistoryLog from '@/components/Approver/EH/ApproverHistoryLog.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue'

import Header from '@/components/layout/Header.vue'
import Sidebar from '@/components/layout/Sidebars/Sidebar.vue';
import SubmitWorkOrder from '@/components/EquipmentHandling/SubmitWorkOrder.vue'

/** data declarations */
// const uri = BASE_URL
// const router = useRouter()
const route = useRoute()
// const user = user_data()
// user.getUserData

const id = route.params.id
const service_id = ref(id)
const serialNumber = ref([])
const status = ref(0)

const getSerialNumber = (serial) => {
    serialNumber.value = Array.from(serial)
    submmitApproveStatus.value = serialNumber.value.every(data => data.serial && data.serial.trim() !== '')
}
const getStatus = (data) => {
    status.value = data
}
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