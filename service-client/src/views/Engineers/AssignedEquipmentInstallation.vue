<template>
    <div class="main-wrapper">
        <EngineersSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <div>

                    <v-container class="container-form mt-10">
                        <v-row justify="space-between" class="topActions">
                            <div>
                                <nav class="mt-5 ml-3">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="#">
                                                <router-link to="/equipment-installation">
                                                    <v-icon>mdi-menu-left</v-icon> back
                                                </router-link></a></li>
                                        <li class="breadcrumb-item"><a href="#">Equipment Installation</a></li>
                                    </ol>
                                </nav>
                            </div>
                            <div class="mt-3 mr-4">
                                <!-- Assign Engineer -->
                                <v-btn type="button" :disabled="btnDisable" :loading="btnLoading" color="primary"
                                    class="text-none btnSubmit" @click="machineInstalled"><v-icon class="mr-2">mdi-package-check</v-icon>
                                    Installed</v-btn>
                            </div>
                        </v-row>

                        <RequestedEquipments :service_id="parseInt(service_id)" /><br>

                        <RequestDetails :service_id="parseInt(service_id)" />


                        <!-- <ApproverHistoryLog :service_id="service_id"/> -->

                        <v-snackbar color="success" v-model="snackbarSuccess" location="right bottom" :timeout="5000">
                            <v-icon>mdi-check-circle-outline</v-icon> Successfully installed.
                        </v-snackbar>
                    </v-container>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import Header from '@/components/layout/Header.vue'
import EngineersSidebar from '@/components/layout/Sidebars/EngineersSidebar.vue';
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
const submmitApproveStatus = ref(false)
const remark = ref('')



/**
 * Assign Engineer to Install
 */
const machineInstalled = async () => {
    btnLoading.value = true
    btnDisable.value = true
    try {
        const response = await apiRequest.post('approve-request',
            {
                service_id: id,
                status: parseInt(status),
                approval_level: 0,
            }
        )
        if (response.data && response.data.success) {
            snackbarSuccess.value = true
            dialogApprove.value = false
            setTimeout(() => {
                router.push('/equipment-installation')
            }, 3000)
        }
    } catch (error) {
        alert(error)
        btnDisable.value = false
    } finally {
        btnLoading.value = false
    }
}



// onMounted(() => {
//     getEngineersData()
// })
</script>