<template>
    <div>
        <v-form @submit.prevent="submitWorkOrder" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
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
                        <v-dialog v-model="dialog" max-width="400" persistent>
                            <template v-slot:activator="{ props: activatorProps }">
                                <v-btn :disabled="btnLoading" v-bind="activatorProps" color="error" elevation="1"
                                    class="text-none mr-2"><v-icon class="mr-2">mdi-close</v-icon>
                                    DisApprove</v-btn>
                            </template>
                            <v-card text="" title="Disapprove">
                                <v-col cols="12">
                                    <v-textarea class="mr-2 ml-2" clearable label="Reason of Disapproval" color="primary"
                                        variant="outlined"></v-textarea>
                                </v-col>
                                <template v-slot:actions>
                                    <v-row justify="end">
                                        <v-btn elevation="2" @click="dialog = false" background-color="red" size="small"
                                            color="#191970" class="text-none mr-2"><v-icon>mdi-close</v-icon> Cancel</v-btn>
                                        <v-btn @click="dialog = false" color="primary" elevation="2" size="small"
                                            class="text-none mr-3"
                                            style="background-color: #191970;color: #fff!important;"><v-icon class="mr-1">mdi-text-box-remove-outline</v-icon> Disapprove</v-btn>
                                    </v-row>
                                </template>
                            </v-card>
                        </v-dialog>
                        <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="green-darken-3"
                            class="text-none btnSubmit"><v-icon class="mr-2">mdi-tag-check-outline</v-icon>
                            Approve</v-btn>
                    </div>
                </v-row>
                <v-card style="padding: 3em 1em;" elevation="1">
                    <v-row>
                        <v-col cols="6">
                            <v-combobox color="primary" v-model="institutionValue" clearable label="Institution"
                                density="compact" :items="institutionData" variant="outlined" itemValue="value"
                                itemTitle="key" :rules="rule.ruleInstitution"></v-combobox>
                        </v-col>
                        <v-col cols="6">
                            <v-text-field color="primary" density="compact" label="Requested by"
                                placeholder="Requested by" variant="outlined" readonly v-model="requested_by"
                                :rules="rule.ruleRequestedBy"></v-text-field>
                        </v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="6">
                            <v-text-field color="primary" v-model="address" label="Address" density="compact"
                                variant="outlined" readonly onUpdate:Item="(newVal) => { address.value = newVal.key }"
                                :rules="rule.ruleAddress"></v-text-field>
                        </v-col>
                        <v-col cols="6">
                            <VueDatePicker v-model="proposed_delivery_date" auto-apply :min-date="new Date()"
                                :enable-time-picker="false" placeholder="Propose Delivery Date" />
                            <!-- <input type="date" class="form-control" v-model="proposed_delivery_date" placeholder="Propose Delivery Date"> -->
                            <p class="text-danger" v-if="deliveryDateError !== ''">{{ deliveryDateError }}</p>
                        </v-col>
                    </v-row>
                    <v-divider></v-divider>

                    <!-- OTHER REQUEST DETAILS -->
                    <v-row>
                        <v-col cols="6">
                            <h5 class="mb-1" style="font-weight: 700;color: #191970;">Other Request Details</h5>
                            <v-checkbox v-model="ocular" color="primary" label="Request for Ocular" value="1"
                                class="vCheckbox"></v-checkbox>
                            <v-checkbox v-model="bypass" color="primary" label="Bypass Internal Servicing Procedures"
                                value="1" class="vCheckbox"></v-checkbox>
                            <v-checkbox v-model="ship" color="primary"
                                label="Ship & Deliver direct to customer immediately" class="vCheckbox"
                                value="1"></v-checkbox>
                        </v-col>
                        <v-col cols="6">
                            <v-textarea color="primary" label="Endorsement" v-model="endorsement" row-height="25"
                                rows="3" variant="outlined" auto-grow shaped>
                            </v-textarea>
                        </v-col>
                    </v-row>

                    <v-divider class="mb-3"></v-divider>
                    <!-- Internal External Request -->
                    <v-row>
                        <v-col cols="12" md="6" sm="6">
                            <h5 class="mb-2" style="font-weight: 700;color: #191970;">External Request</h5>
                            <v-radio-group v-model="externalRequest" :rules="rule.ruleExternal" column>
                                <v-radio color="primary" label="For Demonstration" value="1"></v-radio>
                                <v-radio color="primary" label="Reagent Tie-up" value="2"></v-radio>
                                <v-radio color="primary" label="Purchased" value="3"></v-radio>
                                <v-radio color="primary" label="Shipment / Delivery" value="4"></v-radio>
                                <v-radio color="primary" label="Service Unit" value="5"></v-radio>
                                <v-radio color="primary" label="Others" value="11"></v-radio>
                            </v-radio-group>
                            <v-checkbox v-model="attached_gate" class="vCheckbox" color="primary"
                                label="Attached gate/entry pass" value="1"></v-checkbox>
                            <v-checkbox v-model="with_contract" class="vCheckbox" color="primary"
                                label="with contract/other docs" value="1"></v-checkbox>
                        </v-col>
                        <v-col cols="12" md="6" sm="6">
                            <h5 class="mb-2" style="font-weight: 700;color: #191970;">Internal Request</h5>
                            <v-radio-group v-model="internalRequest" :rules="rule.ruleInternal" column>
                                <v-radio color="primary" label="For Corrective" value="6"></v-radio>
                                <v-radio color="primary" label="For Refurbishment" value="7"></v-radio>
                                <v-radio color="primary" label="For Quality Control" value="8"></v-radio>
                                <v-radio color="primary" label="Training Purposes" value="9"></v-radio>
                                <v-radio color="primary" label="For Disposal" value="10"></v-radio>
                                <v-radio color="primary" label="Other" value="11"></v-radio>
                            </v-radio-group>
                        </v-col>
                    </v-row>
                </v-card>

                <!-- Equipment & Peripherals -->
                <v-card class="mt-3 p-3">
                    <v-row>
                        <h5 class="p-3" style="font-weight: 700;color: #191970;">EQUIPMENT & PERIPHERALS</h5>
                        <v-col cols="12">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead style="background: #afafaf2e;">
                                        <tr>
                                            <th scope="col">Item Code</th>
                                            <th scope="col">Item Description</th>
                                            <th scope="col">Serial Number</th>
                                            <th scope="col">Remarks</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="selectedEquipment.length > 0">
                                        <tr v-for="(equipment, index) in selectedEquipment" :key="index">
                                            <td>
                                                {{ equipment.item_code }}
                                                <v-text-field class="hideID">{{ equipment.id }}</v-text-field>
                                                <!-- <input type="text" class="myInputText" v-model="equipment.id"> -->
                                            </td>
                                            <td>{{ equipment.description }}</td>
                                            <td><v-text-field clearable density="compact" variant="plain"
                                                    placeholder="Serial number ...." w-100
                                                    v-model="equipment.equipmentSerial"
                                                    :rules="rule.ruleEquipmentSerial"></v-text-field>
                                            </td>
                                            <td><v-text-field clearable density="compact" variant="plain"
                                                    placeholder="Remarks..." w-100
                                                    v-model="equipment.equipmentRemark"></v-text-field>
                                            </td>
                                            <td><v-icon @click="removeSelectedEquipment(index)"
                                                    class="text-danger">mdi-trash-can-outline</v-icon>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="5" class="text-center p-1" style="opacity: .3;">
                                                <v-icon class="mb-3"
                                                    style="font-size: 30px">mdi-file-document-alert-outline</v-icon><br>
                                                No records found
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </v-col>

                    </v-row>
                </v-card>

                <!-- Additional Peripherals -->
                <v-card class="mt-3 p-3" elevation="1">

                    <v-row>
                        <h5 class="p-3 mt-2" style="font-weight: 700;color: #191970;">ADDITIONAL PERIPHERALS <span
                                style="font-size: .8em;font-weight: 100;color: #777;"><i> (Serial number to be filled by
                                    the IT
                                    Department)</i></span></h5>
                        <v-col cols="12">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-responsive">
                                    <thead style="background: #afafaf2e;">
                                        <tr>
                                            <th scope="col">Item Code</th>
                                            <th scope="col">Item Description</th>
                                            <!-- <th scope="col">Serial Number</th> -->
                                            <th scope="col">Remarks</th>
                                            <th scope="col"></th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="selectedPeripherals.length > 0">
                                        <tr v-for="(peripheral, index) in selectedPeripherals" :key="index">
                                            <td>
                                                {{ peripheral.item_code }}
                                                <v-text-field class="hideID">{{ peripheral.id }}</v-text-field>
                                            </td>
                                            <td>{{ peripheral.description }}</td>
                                            <!-- <td><v-text-field clearable density="compact" variant="plain"
                                                    :rules="rule.rulePeripheralSerial" placeholder="Serial number ...."
                                                    w-100 v-model="peripheral.peripheralSerial"></v-text-field>
                                            </td> -->
                                            <td><v-text-field clearable density="compact" variant="plain"
                                                    placeholder="Remarks..." w-100
                                                    v-model="peripheral.peripheralRemark"></v-text-field>
                                            </td>
                                            <td><v-icon @click="removeSelectedPeripherals(index)"
                                                    class="text-danger">mdi-trash-can-outline</v-icon>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tbody v-else>
                                        <tr>
                                            <td colspan="5" class="text-center p-3" style="opacity: .3;">
                                                <v-icon class="mb-3"
                                                    style="font-size: 50px">mdi-file-document-alert-outline</v-icon><br>
                                                No records found
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </v-col>
                    </v-row>
                </v-card>
                <v-snackbar color="warning" v-model="snackbarErrorGeneral" location="right bottom" :timeout="3000">
                    <v-icon>mdi-information-box</v-icon> Please ensure that required fields are not left blank
                </v-snackbar>
                <v-snackbar color="success" v-model="snackbarSuccess" location="right bottom" :timeout="5000">
                    <v-icon>mdi-check-circle-outline</v-icon> Successfully submitted.
                </v-snackbar>
                <v-snackbar color="error" v-model="snackbarError" location="right bottom" :timeout="5000">
                    <v-icon>mdi-alert-circle-outline</v-icon> Something went wrong.
                </v-snackbar>
            </v-container>
        </v-form>
    </div>
</template>
<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import moment from 'moment';
import { useRoute, useRouter } from 'vue-router';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { BASE_URL } from '@/api';
import { user_data } from '@/stores/auth/userData';
import axios from 'axios';

/** data declarations */
const uri = BASE_URL
const router = useRouter()
const route = useRoute()
const user = user_data()
user.getUserData
const datatable = ref(null)
const form = ref(false)
const overlayMasterData = ref(false)
const overlayMasterDataPeripherals = ref(false)
const dialog = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const institutionData = ref([])
const errorMessage = ref('')
const deliveryDateError = ref('')
const snackbarErrorGeneral = ref(false)
const snackbarSuccess = ref(false)
const snackbarError = ref(false)
const modelValueProgress = ref(100)
const id = route.params.id


// 1st
const requested_by = ref('')
const institutionValue = ref('')
const address = ref('')
const proposed_delivery_date = ref('')

// 2nd
const ocular = ref('')
const bypass = ref('')
const ship = ref('')
// 3rd
const internalRequest = ref(null)
const externalRequest = ref(null)
const attached_gate = ref('')
const with_contract = ref('')
// 4th
const endorsement = ref('')

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

watch(internalRequest, (val) => {
    if (val !== null) {
        externalRequest.value = null
        rule.value.ruleExternal = []
    }
})
watch(externalRequest, (val) => {
    if (val !== null) {
        internalRequest.value = null
        rule.value.ruleInternal = []
    }
})

requested_by.value = user.user.first_name + ' ' + user.user.last_name

// Institution
watch(institutionValue, (newVal) => {
    address.value = newVal ? newVal.value : ''
})



/** Data functions */
const submitWorkOrder = async () => {
    const peripheral = Array.from(selectedPeripherals.value)
    const equipment = Array.from(selectedEquipment.value)
    const formatted_delivery_date = moment(proposed_delivery_date.value).format('YYYY-MM-DD')
    // console.log(formatted_delivery_date)
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        snackbarErrorGeneral.value = true
        btnLoading.value = false
        return
    }

    if (!selectedEquipment.value || selectedEquipment.value.length === 0) {
        snackbarErrorGeneral.value = true
        errorMessage.value = 'Please select at least one record to proceed'
        btnLoading.value = false
    }
    else if (!proposed_delivery_date.value) {
        deliveryDateError.value = 'Required field'
        setTimeout(() => {
            deliveryDateError.value = ''
        }, 3000)
        snackbarErrorGeneral.value = true
        btnLoading.value = false
    }
    else {
        try {
            const response = await axios.post(
                uri + 'api/submit-work-order',
                {
                    requested_by: user.user.id,
                    institution: institutionValue.value.institutionId,
                    proposed_delivery_date: formatted_delivery_date,
                    with_contract: with_contract.value,
                    attach_gate: attached_gate.value,
                    ship: ship.value,
                    bypass: bypass.value,
                    ocular: ocular.value,
                    externalRequest: externalRequest.value,
                    internalRequest: internalRequest.value,
                    endorsement: endorsement.value,
                    equipments: equipment,
                    peripherals: peripheral,
                },
                {
                    headers: {
                        'Authorization': `Bearer ${user.tokenData}`
                    }
                }
            )
            if (response.data && response.data.success) {
                snackbarSuccess.value = true
                btnDisable.value = true
                setTimeout(() => {
                    router.push('/equipment-handling')
                }, 4000)
            } else {
                snackbarError.value = true
            }
        } catch (error) {
            console.log(error)
        }
        finally {
            btnLoading.value = false
        }
    }
}









/** Returns */









/** Get Users table Equipment and master data */
const loading = ref(true);
const total_rows = ref(0);


const params = reactive({ current_page: 1, pagesize: 10 });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number' },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Item Description' },
    ]) || [];
const selectedEquipment = ref([])
const selectedPeripherals = ref([])




// Functions
const getUsers = async () => {
    try {
        loading.value = true;
        const response = await axios.get(uri + 'api/approver-get-equipment-handling-data', {
            service_id: id,
            headers: {
                'Authorization': `Bearer ${user.tokenData}`
            }
        }
        );
        const data = response.data.serviceData

        rows.value = data
        total_rows.value = data?.meta?.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};
const changeServer = (data) => {
    params.current_page = data.current_page;
    params.pagesize = data.pagesize;

    getUsers();
};


onMounted(() => {
    getUsers();
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