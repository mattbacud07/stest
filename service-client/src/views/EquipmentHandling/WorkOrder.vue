<template>
    <v-form @submit.prevent="submitWorkOrder" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
        <LayoutSinglePage>
            <template #topBarFixed>
                <v-col lg="6" md="6" sm="8" cols="8">
                    <v-breadcrumbs class="pt-7">
                        <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index"
                            :class="{ 'custom-pointer': !item.disabled }"
                            :style="{ 'display': width <= 768 ? item.display : '' }" @click="navigateTo(item)"
                            :disabled="item.disabled">
                            {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                        </v-breadcrumbs-item>
                    </v-breadcrumbs>
                </v-col>
                <v-spacer></v-spacer>
                <v-col lg="6" md="6" sm="4" cols="4" align-self="center" class="d-flex justify-end">
                    <v-btn :disabled="btnLoading" @click="discard" color="primary" variant="tonal"
                        class="text-none mr-2">
                        Cancel
                    </v-btn>
                    <v-btn type="submit" :loading="btnLoading" :disabled="btnDisable" color="primary" variant="flat"
                        class="text-none btnSubmit">
                        <v-icon class="mr-2">mdi-note-plus-outline</v-icon> Create
                    </v-btn>
                </v-col>
            </template>

            <template #default>
                <v-container class="container-form mt-10">
                    <v-card class="p-2 border-sm pt-7 border-dashed" elevation="0">
                        <v-row>
                            <v-col :cols="column">
                                <v-combobox color="primary" v-model="institutionValue" clearable label="Institution"
                                    density="compact" :items="institutionData" variant="outlined" itemValue="value"
                                    itemTitle="key" :rules="rule.ruleInstitution" :close-on-content-click="false"
                                    hide-details></v-combobox>
                            </v-col>
                            <v-col :cols="column">
                                <v-text-field color="primary" density="compact" label="Requested by"
                                    placeholder="Requested by" variant="outlined" readonly v-model="requested_by"
                                    :rules="rule.ruleRequestedBy" hide-details></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row class="mb-2">
                            <v-col :cols="column">
                                <v-text-field color="primary" v-model="address" label="Address" density="compact"
                                    variant="outlined" readonly
                                    onUpdate:Item="(newVal) => { address.value = newVal.key }" :rules="rule.ruleAddress"
                                    hide-details></v-text-field>
                            </v-col>
                            <v-col :cols="column">
                                <VueDatePicker v-model="proposed_delivery_date" auto-apply :min-date="new Date()"
                                    :enable-time-picker="false" placeholder="Propose Delivery Date" />
                                <!-- <input type="date" class="form-control" v-model="proposed_delivery_date" placeholder="Propose Delivery Date"> -->
                                <p class="text-danger" v-if="deliveryDateError !== ''">{{ deliveryDateError }}</p>
                            </v-col>
                        </v-row>
                        <v-divider></v-divider>

                        <!-- OTHER REQUEST DETAILS -->
                        <v-row class="mt-3">
                            <v-col :cols="column">
                                <h5 class="mb-1" style="font-weight: 500;color: #191970;">Other Request Details</h5>
                                <v-checkbox v-model="ocular" color="primary" label="Request for Ocular" value="1"
                                    class="vCheckbox" density="compact"></v-checkbox>
                                <v-checkbox v-model="bypass" color="primary"
                                    label="Bypass Internal Servicing Procedures" value="1" class="vCheckbox"
                                    :disabled="disableExternalCheckbox" density="compact"></v-checkbox>
                                <v-checkbox v-model="ship" color="primary"
                                    label="Ship & Deliver direct to customer immediately" class="vCheckbox" value="1"
                                    density="compact"></v-checkbox>
                            </v-col>
                            <v-col :cols="column">
                                <v-textarea color="primary" label="Endorsement" v-model="endorsement" row-height="25"
                                    rows="3" variant="outlined" auto-grow shaped>
                                </v-textarea>
                            </v-col>
                        </v-row>

                        <v-divider class="mb-3"></v-divider>
                        <!-- Internal External Request -->
                        <v-row class="mt-3">
                            <v-col cols="12" md="6" sm="6">
                                <h5 class="mb-2" style="font-weight: 500;color: #191970;">External Request</h5>
                                <v-radio-group v-model="externalRequest" :rules="rule.ruleExternal" column
                                    density="compact">
                                    <v-radio color="primary" label="For Demonstration" value="1"></v-radio>
                                    <v-radio color="primary" label="Reagent Tie-up" value="2"></v-radio>
                                    <v-radio color="primary" label="Purchased" value="3"></v-radio>
                                    <v-radio color="primary" label="Shipment / Delivery" value="4"></v-radio>
                                    <v-radio color="primary" label="Service Unit" value="5"></v-radio>
                                    <v-radio color="primary" label="Other" value="6"></v-radio>
                                </v-radio-group>
                                <v-text-field v-if="externalRequest === '6'" density="compact" variant="outlined"
                                    placeholder="Other External Request" w-90 v-model="other"
                                    :rules="rule.otherRule"></v-text-field>
                                <v-checkbox v-model="attached_gate" :disabled="disableExternalCheckbox"
                                    class="vCheckbox" color="primary" label="Attached gate/entry pass"
                                    value="1"></v-checkbox>
                                <v-checkbox v-model="with_contract" :disabled="disableExternalCheckbox"
                                    class="vCheckbox" color="primary" label="with contract/other docs"
                                    value="1"></v-checkbox>
                            </v-col>
                            <v-col cols="12" md="6" sm="6">
                                <h5 class="mb-2" style="font-weight: 500;color: #191970;">Internal Request</h5>
                                <v-radio-group v-model="internalRequest" :rules="rule.ruleInternal" column
                                    density="compact">
                                    <v-radio color="primary" label="For Corrective" value="7"></v-radio>
                                    <v-radio color="primary" label="For Refurbishment" value="8"></v-radio>
                                    <v-radio color="primary" label="For Quality Control" value="9"></v-radio>
                                    <v-radio color="primary" label="Training Purposes" value="10"></v-radio>
                                    <v-radio color="primary" label="For Disposal" value="11"></v-radio>
                                    <v-radio color="primary" label="Other" value="12"></v-radio>
                                </v-radio-group>
                                <v-text-field v-if="internalRequest === '12'" density="compact" variant="outlined"
                                    placeholder="Other External Request" w-90 v-model="other"
                                    :rules="rule.otherRule"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card>

                    <!-- Equipment & Peripherals -->
                    <v-card class="mt-3 p-3" elevation="0" style="border: 1px dashed #191970;">
                        <v-row>
                            <h5 class="p-3" style="font-weight: 500;color: #191970;">EQUIPMENT & PERIPHERALS</h5>
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
                                                <td colspan="5" class="text-center p-1" style="opacity: .2;">
                                                    <v-icon class="mb-3"
                                                        style="font-size: 50px">mdi-file-document-alert-outline</v-icon><br>
                                                    No records found
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Overlay box for Equipment Peripherals -->
                                <div class="d-flex justify-content-center">
                                    <v-btn color="primary" size="medium" flat class="text-none mt-3 p-2"
                                        @click="overlayMasterData = !overlayMasterData">
                                        <v-icon class="mr-3">mdi-database-cog</v-icon>
                                        Select Equipment & Peripherals
                                    </v-btn>
                                    <v-overlay v-model="overlayMasterData"
                                        class="d-flex align-items-center justify-content-center"
                                        :width="width <= 600 ? 400 : 600" location-strategy="connected">
                                        <v-card class="pa-7" min-height="500">
                                            <v-row>
                                                <v-col cols="10" xl="4" md="4" sm="5">
                                                    <v-text-field v-model="params.search" clearable density="compact"
                                                        label="Search all fields" variant="outlined"
                                                        color="primary"></v-text-field>
                                                </v-col>
                                                <v-spacer></v-spacer>
                                                <v-btn @click="overlayMasterData = false" icon variant="plain"
                                                    color="primary"><v-icon>mdi-close</v-icon></v-btn>
                                            </v-row>
                                            <vue3-datatable ref="datatable" :rows="rows" :columns="cols"
                                                :loading="loading" :selectRowOnClick="true" :sortable="true"
                                                :search="params.search" :isServerMode="true" :totalRows="total_rows"
                                                :pageSize="params.pagesize" :hide="true" :filter="true"
                                                skin="bh-table-compact bh-table-bordered bh-table-responsive" class=""
                                                @rowClick="rowClickEquipment" @change="changeServer"></vue3-datatable>
                                            <v-divider></v-divider>
                                            <p class="text-danger"><b>List of Selected Row</b><br>
                                                <span v-if="selectedEquipment.length > 0">
                                                    <span v-for="(itemRow, index) in selectedEquipment"
                                                        :key="itemRow.id">
                                                        [ {{ itemRow.item_code }} - <v-icon
                                                            @click="removeSelectedEquipment(index)"
                                                            class="text-danger">mdi-trash-can-outline</v-icon>] &nbsp;
                                                    </span>
                                                </span>
                                                <span v-else class="small">No data selected</span>
                                            </p>
                                        </v-card>
                                    </v-overlay>
                                </div>
                            </v-col>

                        </v-row>
                    </v-card>

                    <!-- Additional Peripherals -->
                    <v-card class="mt-3 p-3" elevation="0" style="border: .5px dashed #191970;">

                        <v-row>
                            <h5 class="p-3 mt-2" style="font-weight: 500;color: #191970;">ADDITIONAL PERIPHERALS <span
                                    style="font-size: .8em;font-weight: 400;color: #777;"><i><br v-if="width < 615">
                                        (Serial number to be filled by the IT Department)</i></span></h5>
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

                                <!-- Overlay box for Equipment Peripherals -->
                                <div class="d-flex justify-content-center">
                                    <v-btn color="primary" size="medium" flat class="text-none mt-3 p-2"
                                        @click="overlayMasterDataPeripherals = !overlayMasterDataPeripherals">
                                        <v-icon class="mr-3">mdi-database-plus</v-icon>
                                        Select Additional Peripherals
                                    </v-btn>
                                    <v-overlay v-model="overlayMasterDataPeripherals"
                                        class="d-flex align-items-center justify-content-center"
                                        :width="width <= 600 ? 400 : 600" location-strategy="connected">
                                        <v-card class="pa-7" min-height="600">
                                            <v-row>
                                                <v-col cols="10" xl="4" md="4" sm="5">
                                                    <v-text-field v-model="params.search" clearable density="compact"
                                                        label="Search all fields" variant="outlined"
                                                        color="primary"></v-text-field>
                                                </v-col>
                                                <v-spacer></v-spacer>
                                                <v-btn @click="overlayMasterDataPeripherals = false" icon
                                                    variant="plain" color="primary"><v-icon>mdi-close</v-icon></v-btn>
                                            </v-row>
                                            <vue3-datatable ref="datatable" :rows="rows" :columns="cols"
                                                :loading="loading" :selectRowOnClick="true" :sortable="true"
                                                :hide="true" :search="params.search" :isServerMode="true"
                                                :totalRows="total_rows" :pageSize="params.pagesize" :filter="true"
                                                skin="bh-table-compact bh-table-bordered" class=""
                                                @rowClick="rowClickPeripherals" @change="changeServer"></vue3-datatable>
                                            <v-divider></v-divider>
                                            <p class="text-danger"><b>List of Selected Row</b><br>
                                                <span v-if="selectedPeripherals.length > 0">
                                                    <span v-for="(itemRow, index) in selectedPeripherals"
                                                        :key="itemRow.id">
                                                        [ {{ itemRow.item_code }} - <v-icon
                                                            @click="removeSelectedPeripherals(index)"
                                                            class="text-danger">mdi-trash-can-outline</v-icon>] &nbsp;
                                                    </span>
                                                </span>
                                                <span v-else class="small">No data selected</span>
                                            </p>
                                        </v-card>
                                    </v-overlay>
                                </div>
                            </v-col>

                        </v-row>
                    </v-card>
                </v-container>
            </template>
        </LayoutSinglePage>
    </v-form>
</template>
<script setup>
import { ref, reactive, onMounted, watch, onBeforeUnmount, computed } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import moment from 'moment';
import { onBeforeRouteLeave, useRouter } from 'vue-router';
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

/** Lodash Debounce */
import debounce from 'lodash/debounce'

/** Import vuetify UseDisplay */
import { useDisplay } from 'vuetify'
const { name, width } = useDisplay()

// console.log(name.value)

import { user_data } from '@/stores/auth/userData';
import { apiRequestAxios } from '@/api/api';

/** data declarations */
const router = useRouter()
const user = user_data()
user.getUserData

const apiRequest = apiRequestAxios()

const datatable = ref(null)
const form = ref(false)
const overlayMasterData = ref(false)
const overlayMasterDataPeripherals = ref(false)
const dialog = ref(false)
const btnDisable = ref(false)
const disableExternalCheckbox = ref(false)
const btnLoading = ref(false)
const institutionData = ref([])
const errorMessage = ref('')
const deliveryDateError = ref('')

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

const column = ref(6)
watch(width, (val) => {
    if (val < 550) {
        column.value = 12
    } else if (val < 768) {
        cols.value.forEach((col) => {
            if (col.field === 'description') {
                col.hide = true
            }
        })
    }
    else {
        column.value = 6
        cols.value.forEach((col) => {
            if (col.field === 'description') {
                col.hide = false
            }
        })
    }
})


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
const other = ref('')
const request_type = ref('')
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

    ruleEquipmentSerial: [
        v => !!v || 'Required'
    ],
    otherRule: [
        v => !!v || 'Required'
    ],
})

watch(internalRequest, (val) => {
    if (val !== null) {
        externalRequest.value = null
        rule.value.ruleExternal = []
        disableExternalCheckbox.value = true
        attached_gate.value = ''
        with_contract.value = ''
        bypass.value = ''
    } else {
        disableExternalCheckbox.value = false
    }


    if (val !== 12) {
        other.value = ''
    }
})
watch(externalRequest, (val) => {
    if (val !== null) {
        internalRequest.value = null
        rule.value.ruleInternal = []
    }

    if (val !== 6) {
        other.value = ''
    }
})

requested_by.value = user.user.first_name + ' ' + user.user.last_name

/** Auto Populate Addresss base on Institution */
watch(institutionValue, (newVal) => {
    address.value = newVal ? newVal.value : ''
})



/** Submit Equipment Hnadling Job Order */
const countDown = ref(5)
const submitWorkOrder = async () => {
    const peripheral = Array.from(selectedPeripherals.value)
    const equipment = Array.from(selectedEquipment.value)
    const formatted_delivery_date = moment(proposed_delivery_date.value).format('YYYY-MM-DD')
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        toast.error('Please ensure that required fields are not left blank')
        btnLoading.value = false
        return
    }


    if (!proposed_delivery_date.value) {
        deliveryDateError.value = 'Required field'
        setTimeout(() => {
            deliveryDateError.value = ''
        }, 3000)
        toast.error('Please enter the proposed delivery date')
        btnLoading.value = false
    }
    else if (!selectedEquipment.value || selectedEquipment.value.length === 0) {
        toast.error('Please select at least one record to proceed')
        btnLoading.value = false
    }
    else {
        try {
            const response = await apiRequest.post('submit-work-order',
                {
                    requested_by: user.user.id,
                    institution: institutionValue.value.institutionId,
                    proposed_delivery_date: formatted_delivery_date,
                    with_contract: with_contract.value,
                    attach_gate: attached_gate.value,
                    ship: ship.value,
                    bypass: bypass.value,
                    ocular: ocular.value,
                    request_type: externalRequest.value ?? internalRequest.value,
                    other: other.value,
                    endorsement: endorsement.value,
                    equipments: equipment,
                    peripherals: peripheral,
                })
            if (response.data && response.data.success) {
                toast.success('Request successfully created')
                btnDisable.value = true
                router.push('/equipment-handling')
                // const countDownInterval = setInterval(() => {
                //     countDown.value--
                //     if (countDown.value === 0) {
                //         router.push('/equipment-handling')
                //     }
                // }, 1000);
            } else {
                toast.error('Something went wrong')
            }
        } catch (error) {
            console.log(error)
        }
        finally {
            btnLoading.value = false
        }
    }
}






/** Get Users table MASTER DATA */
const loading = ref(true);
const total_rows = ref(0);
const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'item_code', title: 'Item Code' },
        { field: 'description', title: 'Item Description' },
        { field: 'name', title: 'Category' },
    ]) || [];

const searchColumn = cols.value.map(f => f.field)
const params = reactive({ current_page: 1, pagesize: 10, search: '' });
const rows = ref(null);

const selectedEquipment = ref([])
const selectedPeripherals = ref([])




/*** Row Click Selection of Equipment and get Data */
const rowClickEquipment = (data) => {
    selectedEquipment.value.push({
        id: data.id,
        item_code: data.item_code,
        description: data.description,
        equipmentSerial: '',
        equipmentRemark: ''
    })
}

/*** Row Click Selection pf Peripherals and get Data */
const rowClickPeripherals = (data) => {
    selectedPeripherals.value.push({
        id: data.id,
        item_code: data.item_code,
        description: data.description,
        peripheralSerial: '',
        peripheralRemark: ''
    })
}
const removeSelectedEquipment = (index) => {
    selectedEquipment.value.splice(index, 1)
}
const removeSelectedPeripherals = (index) => {
    selectedPeripherals.value.splice(index, 1)
}




/** Methods Declared */
const item_category = ref([]) //Additional Peripheral - IT Dept Approver
watch(overlayMasterDataPeripherals, async (val) => {
    val === true ? item_category.value = [1, 5, 7] : item_category.value = []
    params.search = ''
    await getMasterData()
})
const getMasterData = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('master-data-equipments', {
            params: { ...params, searchColumn: searchColumn, item_category: item_category.value },
        });
        const data = response.data.equipments

        rows.value = data.data
        // console.log(searchColumn)
        total_rows.value = data.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

const get_institution = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_institution');

        institutionData.value = response.data.institutions.map(institution => {
            return {
                key: institution.name,
                value: institution.address,
                institutionId: institution.id,
            }
        })
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

// debounce initialization
const debounceSearch = debounce(getMasterData, 300)

/** Server Mode */
const changeServer = (data) => {
    params.current_page = data.current_page;
    params.pagesize = data.pagesize;
    params.search = data.search

    if (data.change_type === 'search') {
        debounceSearch()
    } else {
        getMasterData();
    }
};


const setSizeScreen = () => {
    column.value = width.value < 550 ? 12 : 6;
};

onBeforeRouteLeave(async (to, from, next) => {
    const { valid } = await form.value.validate()
    if (!valid) {
        const confirm = window.confirm('Discard Changes?')
        if (confirm) next()
        else next(false)
    }
    else next()

})

/** Discard button */
const discard = () => {
    router.push('/equipment-handling')
}


onMounted(() => {
    getMasterData();
    get_institution()
    setSizeScreen()
    // console.log(total_rows)
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

.v-label {
    color: #222;
}

/* .vCheckbox {
        height: 40px !important;
    } */
</style>