<template>
    <v-form @submit.prevent="submitPullOut" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
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
                    <v-card class="pa-5 border-sm pt-7 border-dashed" elevation="0">
                        <v-row>
                            <v-col :cols="column">
                                <v-combobox color="primary" v-model="formData.institution" clearable
                                    :loading="loadingInstitution" label="Institution" density="compact"
                                    :items="institutionData" variant="outlined" itemValue="value" itemTitle="key"
                                    :rules="rule.ruleInstitution" :close-on-content-click="false"
                                    hide-details></v-combobox>
                            </v-col>
                            <v-col :cols="column">
                                <v-text-field color="primary" density="compact" label="Requested by"
                                    placeholder="Requested by" variant="outlined" readonly
                                    v-model="formData.requested_by" :rules="rule.ruleRequestedBy"
                                    hide-details></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row class="mb-2">
                            <v-col :cols="column">
                                <v-text-field color="primary" v-model="formData.address" label="Address"
                                    density="compact" variant="outlined" readonly
                                    onUpdate:Item="(newVal) => { address.value = newVal.key }" :rules="rule.ruleAddress"
                                    hide-details></v-text-field>
                            </v-col>
                            <v-col :cols="column">
                                <VueDatePicker v-model="formData.proposed_pullout_date" auto-apply
                                    :min-date="new Date()" :enable-time-picker="false" :teleport="true" :state="dateState"
                                    placeholder="Propose Pullout Date" />
                            </v-col>
                        </v-row>
                        <!-- Equipment & Peripherals -->
                        <v-card class="mt-3 p-3" elevation="0">
                            <v-row>
                                <h5 class="p-3" style="font-weight: 500;color: #191970;">EQUIPMENT</h5>
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
                                                    <td>{{ equipment.serial }}</td>
                                                    <td><v-text-field clearable density="compact" variant="plain"
                                                            placeholder="Remarks..." w-100
                                                            v-model="equipment.remarks"></v-text-field>
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
                                            Select Equipment
                                        </v-btn>
                                        <v-overlay v-model="overlayMasterData"
                                            class="d-flex align-items-center justify-content-center"
                                            :width="width <= 600 ? 400 : 1000" location-strategy="connected">
                                            <v-card class="pa-7" min-height="500">
                                                <v-row>
                                                    <v-col cols="5" xl="4" md="4" sm="5">
                                                        <v-text-field v-model="params_md.search" clearable
                                                            density="compact" label="Search all fields"
                                                            variant="outlined" color="primary"></v-text-field>
                                                    </v-col>
                                                    <v-spacer></v-spacer>
                                                    <v-btn @click="overlayMasterData = false" icon variant="plain"
                                                        color="primary"><v-icon>mdi-close</v-icon></v-btn>
                                                </v-row>
                                                <vue3-datatable ref="datatable" :rows="rows_md" :columns="cols_md"
                                                    :loading="loading_md" :selectRowOnClick="true" :sortable="true"
                                                    :search="params_md.search" :isServerMode="true"
                                                    :totalRows="total_rows_md" :pageSize="params_md.pagesize"
                                                    :hide="true" :filter="true"
                                                    skin="bh-table-compact bh-table-bordered" class=""
                                                    noDataContent="No data available for the selected institution"
                                                    @rowClick="rowClickEquipment"
                                                    @change="changeServer_md"></vue3-datatable>
                                                <v-divider></v-divider>
                                                <p class="text-danger"><b>List of Selected Row</b><br>
                                                    <span v-if="selectedEquipment.length > 0">
                                                        <span v-for="(itemRow, index) in selectedEquipment"
                                                            :key="itemRow.id">
                                                            [ {{ itemRow.item_code }} - <v-icon
                                                                @click="removeSelectedEquipment(index)"
                                                                class="text-danger">mdi-trash-can-outline</v-icon>]
                                                            &nbsp;
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
                        <v-divider></v-divider>
                        <!-- Reason of Pullout -->
                        <v-row>
                            <v-col cols='12'>

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
import { master_data_status } from '@/global/master_data';
import * as pub_var from '@/global/global'
import { satellite } from '@/global/satellite';

/** data declarations */
const router = useRouter()
const user = user_data()
user.getUserData

const apiRequest = apiRequestAxios()

const datatable = ref(null)
const form = ref(false)
const overlayMasterData = ref(false)
const btnDisable = ref(false)
const disableExternalCheckbox = ref(false)
const btnLoading = ref(false)
const institutionData = ref([])
const loadingInstitution = ref(false)

const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/pull-out-request', display: 'block' },
    { title: 'Pull Out Request', disabled: true, href: '', display: 'none' },
    { title: 'Pull Out Request Form', disabled: true, href: '', display: 'none' },
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

const formData = ref({
    // 1st
    requested_by: user.user.first_name + ' ' + user.user.last_name,
    institution: '',
    address: '',
    proposed_pullout_date: '',
})


/** Input Form Rules */
const rule = ref({
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
})



/** Auto Populate Addresss base on Institution */
const institution_id = ref(null)
watch(() => formData.value.institution, (newVal) => {
    institution_id.value = newVal?.institution_id ?? null
    formData.value.address = newVal ? newVal.value : ''
    getServiceMasterData()
})


const dateState = ref(null)
watch(() => formData.value.proposed_pullout_date, (newVal) => {
    dateState.value = newVal ? null : false
})


/** Submit PullOut Request */
const submitPullOut = async () => {
    btnLoading.value = true
    const equipment = Array.from(selectedEquipment.value)
    const formatted_delivery_date = moment(formData.value.proposed_pullout_date).format('YYYY-MM-DD')
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        if (!formData.value.proposed_pullout_date) dateState.value = false
        return
    }
    if (!formData.value.proposed_pullout_date) {
        dateState.value = false
        btnLoading.value = false
        return
    }
    if (!equipment || equipment.length <= 0) {
        toast.error('Select atleast one equipment')
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('create-pullout',
            {
                ...formData.value,
                proposed_pullout_date: formatted_delivery_date,
                equipments: equipment,
            })
        if (response.data && response.data.success) {
            toast.success('Request successfully created')
            btnDisable.value = true
            router.push('/pull-out-request')
        } else {
            toast.error('Something went wrong')
        }
    } catch (error) {
        toast.error(error)
        console.log(error)
    }
    finally {
        btnLoading.value = false
    }
}





/** Get Service MASTER DATA */
const loading_md = ref(true);
const total_rows_md = ref(0);
const cols_md =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'master_data_id', title: 'Master Data ID', type: 'number', hide: true },
        { field: 'name', title: 'Institution' },
        { field: 'item_code', title: 'Item Code' },
        { field: 'serial', title: 'Serial No.' },
        { field: 'description', title: 'Item Description' },
        { field: 'status', title: 'Status' },
    ]) || [];

const params_md = reactive({ current_page: 1, pagesize: 10, search: '', sort_column: 'id', sort_direction: 'desc' });
const rows_md = ref(null);

const selectedEquipment = ref([])




/*** Row Click Selection of Equipment and get Data */
const rowClickEquipment = (data) => {
    const checkIfExist = selectedEquipment.value.some(v => v.service_master_data_id === data.id)
    if (!checkIfExist) {
        selectedEquipment.value.push({
            service_master_data_id: data.id,
            item_id: data.master_data_id,
            item_code: data.item_code,
            description: data.description,
            serial: data.serial,
            remarks: ''
        })
    } else toast.info('Equipment is already in the list')
}


const removeSelectedEquipment = (index) => {
    selectedEquipment.value.splice(index, 1)
}



/** Methods Declared */
const getServiceMasterData = async () => {
    try {
        loading_md.value = true;
        const response = await apiRequest.get('get_master_data', {
            params: {
                ...params_md,
                category: 'pullout',
                item_in_institution_id: institution_id.value,
            },
        });
        const data = response.data.md_data

        rows_md.value = data.data
        // console.log(searchColumn)
        total_rows_md.value = data.total;
    } catch (error) {
        console.log(error)
    }

    loading_md.value = false;
};

/** Get Institution */
const get_institution = async () => {
    try {
        loadingInstitution.value = true;
        const response = await apiRequest.get('get_institution');

        institutionData.value = response.data.institutions.map(institution => {
            return {
                key: institution.name,
                value: institution.address,
                institution_id: institution.id,
            }
        })
    } catch (error) {
        console.log(error)
    }

    loadingInstitution.value = false;
};

// debounce initialization
const debounceSearch_md = debounce(getServiceMasterData, 300)



// Service Master Data
const changeServer_md = (data) => {
    params_md.current_page = data.current_page;
    params_md.pagesize = data.pagesize;
    params_md.search = data.search

    if (data.change_type === 'search') {
        debounceSearch_md()
    } else {
        getServiceMasterData();
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
    router.push('/pull-out-request')
}


onMounted(() => {
    getServiceMasterData()
    get_institution()
    setSizeScreen()
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