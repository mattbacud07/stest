<template>
    <div class="main-wrapper">
        <EngineersSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
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
                                                <v-btn :disabled="btnDisable" v-bind="activatorProps" size="small"
                                                    color="error" elevation="1" class="text-none mr-2"><v-icon
                                                        class="mr-2">mdi-close</v-icon>
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
                                                        <v-btn elevation="2" @click="dialog = false"
                                                            background-color="red" size="small" color="#191970"
                                                            class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                                            Cancel</v-btn>
                                                        <v-btn @click="disapproveRequest" color="primary" elevation="2"
                                                            size="small" class="text-none mr-3"
                                                            style="background-color: #191970;color: #fff!important;"><v-icon
                                                                class="mr-1">mdi-text-box-remove-outline</v-icon>
                                                            Disapprove</v-btn>
                                                    </v-row>
                                                </template>
                                            </v-card>
                                        </v-dialog>

                                        <!-- Approve Button -->
                                        <v-dialog v-model="dialogApprove" max-width="400" persistent>
                                            <template v-slot:activator="{ props: activatorProps }">
                                                <v-btn type="button" size="small" v-bind="activatorProps"
                                                    :disabled="btnDisable" color="primary"
                                                    class="text-none btnSubmit"><v-icon class="mr-2">mdi-check</v-icon>
                                                    Approve</v-btn>
                                            </template>
                                            <v-card text="" title="Approve">
                                                <v-col cols="12">
                                                    <v-textarea class="mr-2 ml-2" v-model="remark" clearable
                                                        label="Remarks (optional)" color="primary"
                                                        variant="outlined"></v-textarea>
                                                </v-col>
                                                <template v-slot:actions>
                                                    <v-row justify="end">
                                                        <v-btn elevation="2" @click="dialogApprove = false"
                                                            background-color="red" size="small" color="#191970"
                                                            class="text-none mr-2"><v-icon>mdi-close</v-icon>
                                                            Cancel</v-btn>
                                                        <v-btn type="button" size="small" :loading="btnLoading"
                                                            :disabled="btnDisable" color="#191970"
                                                            class="text-none bg-primary mr-5"
                                                            @click="approveRequest"><v-icon
                                                                class="mr-2">mdi-check</v-icon>
                                                            Approve</v-btn>
                                                    </v-row>
                                                </template>
                                            </v-card>
                                        </v-dialog>
                                    </div>
                                    <div v-if="exist_service_id === true">
                                        <p style="color: red;">* Internal Processing in Progress</p>
                                    </div>
                                </div>
                            </v-row>

                            <!-- <RequestDetails :service_id="parseInt(service_id)"
                    :showInternalRequest="user.user.approval_level === pub_var.SERVICE_TL ? true : false" /> -->

                            <RequestedEquipments :service_id="parseInt(service_id)" :editSerial="true" />

                        </v-container>
                    </v-form>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import Header from '@/components/layout/Header.vue'
import EngineersSidebar from '@/components/layout/Sidebars/EngineersSidebar.vue';
import alertMessage from '@/components/PopupMessage/alertMessage.vue';

import { ref, watch, onMounted } from 'vue';
import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { user_data } from '@/stores/auth/userData';
import RequestedEquipments from '@/components/Approver/RequestedEquipments.vue'
import * as pub_var from '@/global/global'

/** data declarations */
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
const service_id = route.params.service_id
const serialNumber = ref([])
const submmitApproveStatus = ref(false)
const remark = ref('')
const exist_service_id = ref(null)


/** Data functions */



onMounted(() => {

})
</script>
