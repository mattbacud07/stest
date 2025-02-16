<template>
    <v-form @submit.prevent="submitMaintenance" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
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
                    <v-card class="p-3 mb-5" elevation="1">
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-combobox color="primary" v-model="institutionValue" clearable label="Institution"
                                    density="compact" :items="institutionData" variant="outlined" itemValue="value"
                                    itemTitle="key" :rules="rule.institutionValue" :close-on-content-click="false" :loading="loadingInstitution"
                                    hide-details></v-combobox>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.address" label="Address"
                                    density="compact" variant="outlined" readonly
                                    onUpdate:Item="(newVal) => { address.value = newVal.key }" :rules="rule.address"
                                    hide-details></v-text-field>
                            </v-col>

                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.item_code" label="Item Code" clearable
                                    density="compact" variant="outlined" readonly :rules="rule.item_code"
                                    @click="overlayMasterData = !overlayMasterData"></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.equipment" label="Equipment" clearable
                                    density="compact" variant="outlined" :rules="[v => !!v || 'Required']" readonly></v-text-field>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.serial" label="Serial Number" clearable
                                    density="compact" variant="outlined" :rules="rule.serial" readonly></v-text-field>
                            </v-col>
                            <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.IRNo" label="ID/IR No." clearable
                                    density="compact" variant="outlined" :rules="rule.IRNo"></v-text-field>
                            </v-col>
                        </v-row>
                    </v-card>
                    <v-card class="p-3" elevation="0" style="border: 1px dashed #191970;">
                        <v-row>
                            <!-- Customer Complain -->
                            <v-col cols="12" lg="12" md="12" sm="12">
                                <v-col class="d-flex justify-space-between align-items-center">
                                    <p class="text-primary"><b>Customer Complaint</b></p>
                                    <v-btn @click="addComplaintField" class="text-none" color="primary" variant="text"
                                        prepend-icon="mdi-comment-plus">Add Field</v-btn>
                                </v-col>

                                <v-table class="mt-4" style="border: none!important;">
                                    <tbody>
                                        <tr v-if="complaint_fields.length > 0"
                                            v-for="(field, index) in complaint_fields" :key="index">
                                            <td class="d-flex align-items-center" style="border-bottom:none!important;">

                                                <v-text-field v-model="complaint_fields[index]" label="Complaint"
                                                    append-inner-icon="mdi-trash-can-outline" hide-details
                                                    variant="outlined" color="primary" density="compact" clearable
                                                    :rules="[v => !!v || 'Required']"
                                                    @click:append-inner="removeComplaintField(index)"></v-text-field>
                                            </td>
                                        </tr>
                                        <tr v-else>
                                            <td class="text-grey text-center">
                                                <v-icon
                                                    style="font-size: 5em;opacity: .3;">mdi-file-search-outline</v-icon><br />
                                                No records found
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
                            </v-col>
                            <v-divider></v-divider>
                            <!-- Cause of Problem -->
                            <v-col cols="12" lg="12" md="12" sm="12">
                                <v-col class="d-flex justify-space-between align-items-center">
                                    <p class="text-primary"><b>Cause of Problem</b></p>
                                    <v-btn @click="addProblemField" class="text-none" color="primary" variant="text"
                                        prepend-icon="mdi-comment-plus">Add Field</v-btn>
                                </v-col>
                                <v-table class="mt-4" style="border: none!important;">
                                    <tbody>
                                        <tr v-if="problem_fields.length > 0" v-for="(field, index) in problem_fields"
                                            :key="index">
                                            <td class="d-flex align-items-center" style="border-bottom:none!important;">

                                                <v-text-field v-model="problem_fields[index]" label="Cause of Problem"
                                                    append-inner-icon="mdi-trash-can-outline" hide-details
                                                    variant="outlined" color="primary" density="compact" clearable
                                                    :rules="[v => !!v || 'Required']"
                                                    @click:append-inner="removeProblemField(index)"></v-text-field>
                                            </td>
                                        </tr>
                                        <tr v-else>
                                            <td class="text-grey text-center">
                                                <v-icon
                                                    style="font-size: 5em;opacity: .3;">mdi-file-search-outline</v-icon><br />
                                                No records found
                                            </td>
                                        </tr>
                                    </tbody>
                                </v-table>
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
                                :selectRowOnClick="true" :sortable="true" :hasCheckbox="true" :search="params.search"
                                :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize" :hide="true" skin="bh-table-compact bh-table-bordered bh-table-striped bh-table-hover" class=""
                                @rowClick="rowClickEquipment" @change="changeServer"></vue3-datatable>

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
import { onBeforeRouteLeave, useRouter, useRoute } from 'vue-router';
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
const currentWorkType = ref(route.params.work_type === 'PM' ? '/preventive-maintenance' : '/corrective-maintenance')
const breadcrumbItems = [
    { title: 'Back', disabled: false, href: currentWorkType.value },
    { title: route.params.work_type === 'PM' ? 'Preventive Maintenance' : 'Corrective Maintenance', disabled: true, href: '' },
    { title: route.params.work_type === 'PM' ? 'PM-Details' : 'CM-Details', disabled: true, href: '' },
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
    equipment: '',
    item_id: '',
    serial: '',
    IRNo: '',
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
    serial: [
        v => (v && v.trim() !== '') || 'Required'
    ],
    IRNo: [
        // v => !!v || 'Required'
    ],
})

/**Complaint Fields */
const complaint_fields = ref([]);
const addComplaintField = () => {
    complaint_fields.value.push('');
};
const removeComplaintField = (index) => {
    complaint_fields.value.splice(index, 1);
};

/** Problem Fields */
const problem_fields = ref([]);
const addProblemField = () => {
    problem_fields.value.push('');
};
const removeProblemField = (index) => {
    problem_fields.value.splice(index, 1);
};

/** Wathc Item Code */
watch(() => formData.value.item_code,(newVal) => {
    if(!newVal){
        formData.value.serial = ''
        formData.value.equipment = ''
        formData.value.item_id = ''
    }
})

const submitMaintenance = async () => {
    // console.log(complaint_fields.value)
    btnLoading.value = true
    btnDisable.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        toast.error('Please ensure that required fields are not left blank')
        btnLoading.value = false
        btnDisable.value = false
        return
    }

    try {
        const response = await apiRequest.post('createPMRequest',
            {
                ...formData.value,
                complaints: complaint_fields.value,
                problems: problem_fields.value,
                work_type: work_type.value,
                institution: institutionValue.value.institutionId,
            })
        if (response.data && response.data.success) {
            toast.success('Request successfully created')
            btnDisable.value = false
            router.push(currentWorkType.value)
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
        { field: 'master_data_id', title: 'Master Data ID', type : 'number', hide : true },
        { field: 'item_code', title: 'Item Code' },
        { field: 'equipment', title: 'Equipment' },
        { field: 'serial', title: 'Serial No.' },
        { field: 'description', title: 'Item Description' },
        { field: 'status', title: 'Status' },
        // { field: 'name', title: 'Category' },
    ]) || [];

// const searchColumn = cols.value.map(f => f.field)
const params = reactive({ current_page: 1, pagesize: 10, search: '', sort_column: 'id', sort_direction: 'desc', serviceStatus : '' });

const searchColumn = cols.value.map(f => f.field)
const rows = ref(null);

/*** Row Click Selection of Equipment and get Data */
const rowClickEquipment = (data) => {
    formData.value.item_id = data.id
    formData.value.item_code = data.item_code
    formData.value.equipment = data.equipment
    formData.value.serial = data.serial
}

const getMasterData = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_master_data', {
            params: { ...params, searchColumn: searchColumn },
        });
        const data = response.data.md_data

        rows.value = data.data
        // console.log(searchColumn)
        total_rows.value = data.total;
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

// Instittuition
const loadingInstitution = ref(false)
const get_institution = async () => {
    try {
        loadingInstitution.value = true;
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

    loadingInstitution.value = false;
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
    router.push(currentWorkType)
}


onMounted(() => {
    getMasterData();
    get_institution()
    work_type.value = route.params.work_type
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