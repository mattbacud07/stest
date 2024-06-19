<template>
    <div class="main-wrapper">
        <EngineersSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <div>
                    <v-form @submit.prevent="" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
                        <v-container class="container-form mt-10">
                            <v-row justify="space-between" class="topActions">
                                <div>
                                    <nav class="mt-5 ml-3">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="#">
                                                    <router-link to="/preventive-maintenance-engineer">
                                                        <v-icon>mdi-menu-left</v-icon> back
                                                    </router-link></a></li>
                                            <li class="breadcrumb-item"><a href="#">Preventive Maintenance</a></li>
                                            <!-- <li class="breadcrumb-item active" aria-current="page">Work Order</li> -->
                                        </ol>
                                    </nav>
                                </div>
                                <div class="mt-3 mr-4">
                                    <v-dialog v-model="dialog" max-width="400" persistent>
                                        <template v-slot:activator="{ props: activatorProps }">
                                            <v-btn :disabled="btnLoading" v-bind="activatorProps" color="default"
                                                elevation="1" class="text-none mr-2"><v-icon
                                                    class="mr-2">mdi-close</v-icon>
                                                Cancel</v-btn>
                                        </template>
                                        <v-card text="Discard Changes?" title="Discard">
                                            <template v-slot:actions>
                                                <v-row justify="end">
                                                    <router-link to="/preventive-maintenance-engineer"><v-btn
                                                            elevation="2" background-color="red" size="small"
                                                            color="#191970" class="text-none mr-2">Yes,
                                                            Discard</v-btn></router-link>
                                                    <v-btn @click="dialog = false" color="primary" elevation="2"
                                                        size="small" class="text-none mr-3"
                                                        style="background-color: #191970;color: #fff!important;">Keep
                                                        Editing</v-btn>
                                                </v-row>
                                            </template>
                                        </v-card>
                                    </v-dialog>
                                    <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="primary"
                                        class="text-none btnSubmit"><v-icon class="mr-2">mdi-note-plus-outline</v-icon>
                                        Create</v-btn>
                                </div>
                            </v-row>
                            <v-card style="padding: 3em 1em;" elevation="1">
                                <v-row>
                                    Serial Number : {{ serial_number }}
                                </v-row>
                            </v-card>
                            <!-- <v-card style="padding: 3em 1em;" elevation="1">
                                <v-row>
                                    <v-col cols="6">
                                        <v-combobox color="primary" v-model="institutionValue" clearable
                                            label="Institution" density="compact" :items="institutionData"
                                            variant="outlined" itemValue="value" itemTitle="key"
                                            :rules="rule.ruleInstitution"></v-combobox>
                                    </v-col>
                                    <v-col cols="6">
                                        <v-text-field color="primary" density="compact" label="Requested by"
                                            placeholder="Requested by" variant="outlined" readonly
                                            v-model="requested_by" :rules="rule.ruleRequestedBy"></v-text-field>
                                    </v-col>
                                </v-row>
                                <v-row>
                                    <v-col cols="6">
                                        <v-text-field color="primary" v-model="address" label="Address"
                                            density="compact" variant="outlined" readonly
                                            onUpdate:Item="(newVal) => { address.value = newVal.key }"
                                            :rules="rule.ruleAddress"></v-text-field>
                                    </v-col>
                                    <v-col cols="6">
                                        <VueDatePicker v-model="proposed_delivery_date" auto-apply
                                            :min-date="new Date()" :enable-time-picker="false"
                                            placeholder="Propose Delivery Date" />
                                        <p class="text-danger" v-if="deliveryDateError !== ''">{{ deliveryDateError }}
                                        </p>
                                    </v-col>
                                </v-row>
                                <v-divider></v-divider>
                            </v-card> -->

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
// import EHMain from '@/components/EquipmentHandling/EHMain.vue'
import alertMessage from '@/components/PopupMessage/alertMessage.vue';


import { ref, reactive, onMounted, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import moment from 'moment';
import { useRouter, useRoute } from 'vue-router';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { user_data } from '@/stores/auth/userData';

/** data declarations */
const router = useRouter()
const route = useRoute()
const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()
const datatable = ref(null)
const form = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const dialog = ref(false)
const id = route.params.id

const serial_number = ref(null)

/** Input Form Rules */
const rule = ref({
    ruleInternal: [
        v => !!v || 'Choose one option',
    ],
    ruleExternal: [
        v => !!v || 'Choose one option',
    ],
    ruleInstitution: [
        v => !!v || 'Required field',
        x => x.key !== undefined ? true : 'Select one of the options listed',
        r => r.value !== undefined ? true : 'Select one of the options listed',
    ],
    ruleAddress: [
        v => !!v || 'Address is required',
    ],
    ruleRequestedBy: [
        v => !!v || 'Field is required',
    ],

    // rulePeripheralSerial: [
    //     v => !!v || 'Required'
    // ],
    ruleEquipmentSerial: [
        v => !!v || 'Required'
    ],
})





// Functions
const getPMDetails = async () => {
    try {
        const response = await apiRequest.get('get-pm-record-details', {
            params: {id : id}
        });
        const data = response.data.pm_details[0]

        serial_number.value = data.serial_number

        // institutionData.value = response.data.institutions.map(institution => {
        //     return {
        //         key: institution.name,
        //         value: institution.address,
        //         institutionId: institution.id,
        //     }
        // })
    } catch (error) {
        console.log(error)
    } finally {
        
    }

};


onMounted(() => {
    getPMDetails();
});
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
    z-index: 999;
}
</style>