<template>
    <v-form @submit.prevent="createMasterData" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
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
                <v-container class="container-form mt-15">
                    <!-- Details 1 -->
                    <v-card class="pa-7 mb-5">
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-combobox color="primary" v-model="institutionValue" clearable label="Institution *"
                                    density="compact" :items="institutionData" variant="outlined" itemValue="value"
                                    itemTitle="key" :rules="rule.institutionValue" :close-on-content-click="false"
                                    hide-details></v-combobox>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.address" label="Address *"
                                    density="compact" variant="outlined" readonly
                                    onUpdate:Item="(newVal) => { address.value = newVal.key }" :rules="rule.address"
                                    hide-details></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.item_code" label="Item Code *" clearable
                                    density="compact" variant="outlined" readonly :rules="rule.item_code"
                                    @click="overlayMasterData = !overlayMasterData"></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.equipment" label="Equipment *" clearable
                                    density="compact" variant="outlined" :rules="rule.equipment"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.serial" label="Serial Number *"
                                    clearable density="compact" variant="outlined" :rules="rule.serial"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card>

                    <!-- Details 2 -->
                    <v-card class="pa-7 mb-5">
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.mode" label="Mode *" clearable
                                    density="compact" variant="outlined" :rules="rule.mode" :items="mode"></v-select>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-combobox color="primary" v-model="formData.supplier" clearable label="Supplier *"
                                    density="compact" :items="supplierData" variant="outlined" itemValue="value"
                                    itemTitle="key" :rules="rule.institutionValue" :close-on-content-click="false"
                                    hide-details></v-combobox>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.sbu" label="SBU *" clearable
                                    density="compact" variant="outlined" :rules="[v => !!v || 'Required']"
                                    :items="sbuArray"></v-select>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.status" label="Status *" clearable
                                    density="compact" variant="outlined" :rules="rule.status"
                                    :items="master_data_status"></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.dealer_name" label="Dealer Name"
                                    clearable density="compact" variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.area" label="Area Categorization" clearable
                                    density="compact" variant="outlined" :items="area_categorization"></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.operation_time" label="Operation Time"
                                    clearable density="compact" variant="outlined" :items="operation_time"></v-select>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.software_version"
                                    label="Software Version" clearable density="compact"
                                    variant="outlined"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card>

                    <!-- Details 3 -->
                    <v-card class="pa-7 mb-5">
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <VueDatePicker v-model="formData.admission_date" auto-apply :enable-time-picker="false"
                                    :teleport="true" placeholder="Admission Date *" :rules="[v => !!v || 'Required']" />
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <VueDatePicker v-model="formData.date_installed" auto-apply :enable-time-picker="false"
                                    :teleport="true" placeholder="Date Installed" />
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <VueDatePicker v-model="formData.contract_due_date" auto-apply
                                    :enable-time-picker="false" :teleport="true" placeholder="Contract Due Date" />
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.region" label="Region" clearable
                                    density="compact" variant="outlined" :items="region"></v-select>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-select color="primary" v-model="formData.frequency" label="Frequency for PM *"
                                    clearable density="compact" variant="outlined" :rules="[v => !!v || 'Required']"
                                    :items="pmFrequency"></v-select>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.analyzer_type" label="Analyzer Type *"
                                    clearable density="compact" variant="outlined"
                                    :rules="[v => !!v || 'Required']"></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.class" label="Class" clearable
                                    density="compact" variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-combobox color="primary" v-model="formData.initially_installed" clearable
                                    label="Initially Installed" density="compact" :items="users" variant="outlined"
                                    itemValue="value" itemTitle="key" :close-on-content-click="false"
                                    hide-details></v-combobox>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.reason_equipment_status"
                                    label="Reason for Equipment Status" clearable density="compact"
                                    variant="outlined"></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6" class="mb-5">
                                <v-text-field color="primary" v-model="formData.email" label="Email" clearable
                                    density="compact" variant="outlined"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-container>


                <v-col cols="12">
                    <!-- Overlay box for Equipment Peripherals -->
                    <v-overlay v-model="overlayMasterData" class="d-flex align-items-center justify-content-center"
                        :width="width <= 600 ? 400 : 1000" location-strategy="connected">
                        <v-card class="pa-7" min-height="500">
                            <v-row>
                                <v-col cols="10" xl="4" md="4" sm="5">
                                    <v-text-field v-model="params.search" clearable density="compact"
                                        label="Search all fields" variant="outlined" color="primary"></v-text-field>
                                </v-col>
                                <v-spacer></v-spacer>
                                <v-btn @click="overlayMasterData = false" icon variant="plain"
                                    color="primary"><v-icon>mdi-close</v-icon></v-btn>
                            </v-row>
                            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading"
                                :selectRowOnClick="true" :sortable="true" :hasCheckbox="false" :search="params.search"
                                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" :hide="true"
                                :filter="true" skin="bh-table-compact bh-table-bordered bh-table-responsive" class=""
                                @rowSelect="rowClickEquipment" @change="changeServer"
                                :rowClass="getRowClass"></vue3-datatable>
                                <p class="text-danger small">Select one equipment</p>
                            <p class="text-danger">Selected Item: <b>[{{ formData.item_code }}]</b></p>
                        </v-card>
                    </v-overlay>
                </v-col>
            </template>
        </LayoutSinglePage>
    </v-form>

</template>
<script setup>
import { ref, reactive, onMounted, watch, onBeforeUnmount, computed } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import moment from 'moment';
import { useRouter, useRoute } from 'vue-router';
import { mode, master_data_status, area_categorization, region, operation_time } from '@/global/master_data';
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
import { sbuArray } from '@/global/global';
import { pmFrequency } from '@/global/maintenance';

/** data declarations */
const router = useRouter()
const route = useRoute()
const user = user_data()
const apiRequest = apiRequestAxios()

const datatable = ref(null)
const form = ref(false)
const dialog = ref(false)
const overlayMasterData = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const institutionData = ref([])

const work_type = ref(null)
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/master-data' },
    { title: 'Master Data', disabled: true, href: '' },
    { title: 'Create-Master-Data', disabled: true, href: '' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};


const institutionValue = ref('')
/** Declarations */
const formData = ref({
    institutionValue: '',
    address: '',
    item_code: '',
    item_id: '',
    equipment: '',
    serial: '',
    sbu: '',
    dealer_name: '',
    area: '',
    operation_time: '',
    software_version: '',
    admission_date: '',
    date_installed: '',
    contract_due_date: '',
    region: '',
    frequency: '',
    analyzer_type: '',
    class: '',
    initially_installed: '',
    reason_equipment_status: '',
    email: '',
    mode: '',
    supplier: '',
    status: '',
})

/** Auto Populate Addresss base on Institution */
watch(institutionValue, (newVal) => {
    formData.value.address = newVal ? newVal.value : ''
})


/** Input Form Rules */
const rule = ref({
    institutionValue: [
        v => !!v || 'Required'
    ],
    address: [
        v => (v && v.trim() !== '') || 'Required'
    ],
    item_code: [
        v => (v && v.trim() !== '') || 'Required'
    ],
    equipment: [
        v => (v && v.trim() !== '') || 'Required'
    ],
    serial: [
        v => (v && v.trim() !== '') || 'Required'
    ],
    mode: [
        v => (v && v.trim() !== '') || 'Required'
    ],
    supplier: [
        v => !!v || 'Required'
    ],
    status: [
        v => (v && v.trim() !== '') || 'Required'
    ],
})



const createMasterData = async () => {
    btnLoading.value = true
    // console.log(formData.value)
    const { valid } = await form.value.validate()
    if (!valid) {
        toast.error('Please ensure that required fields are not left blank')
        btnLoading.value = false
        return
    }

    try {
        const response = await apiRequest.post('createMasterData',
            {
                ...formData.value,
                institution: institutionValue.value
            })
        if (response.data && response.data.success) {
            toast.success('Item successfully added')
            btnDisable.value = true
            router.push('/master-data')
        } else {
            toast.error('Something went wrong')
        }
    } catch (error) {
        console.error(error)
    } finally {
        btnLoading.value = false
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

/*** Row Click Selection of Equipment and get Data */
// const selectedRows = ref([])
const rowClickEquipment = (data) => {
    if (data.length > 0) {
        formData.value.item_id = data[0].id
        formData.value.item_code = data[0].item_code
    } else {
        formData.value.item_id = ''
        formData.value.item_code = ''
    }
}

/** Highlight Row in Table */
const getRowClass = (row) => {
    const selectedRows = datatable.value.getSelectedRows()
    const rowID = selectedRows.map(v => v.id)
    return rowID.includes(row.id) ? 'highlightRow' : ''
}

const getMasterData = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('master-data-equipments', {
            params: { ...params, searchColumn: searchColumn },
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

// Instittuition
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

// Supplier
const supplierData = ref([])
const get_supplier = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_supplier');

        supplierData.value = response.data.supplier.map(v => {
            return {
                supplier_id: v.id,
                value: v.id,
                key: v.name,
            }
        })
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

// Users
const users = ref([])
const get_users = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('/users');

        users.value = response.data.users
            .filter(v => v.name === 'Service Department')
            .map(v => {
                return {
                    user_id: v.id,
                    value: v.id,
                    key: v.user_name,
                }
            })
        // console.log(users.value)
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

/** Discard button */
const discard = () => {
    router.push('/master-data')
}


onMounted(() => {
    getMasterData();
    get_institution()
    get_supplier()
    get_users()
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