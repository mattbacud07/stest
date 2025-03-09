<template>
    <v-form @submit.prevent="editMasterData" ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
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
                    <v-btn type="submit" :loading="loading" :disabled="!isChanged" color="primary" variant="flat"
                        class="text-none btnSubmit">
                        <v-icon class="mr-2">mdi-content-save-check-outline</v-icon> Save
                    </v-btn>
                </v-col>
            </template>

            <template #default>
                <template v-if="loading">
                    <v-row>
                        <v-col cols="6"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="6"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="6"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="6"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                    </v-row>
                    <v-row>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                        <v-col cols="4"><v-skeleton-loader type="list-item-three-line"></v-skeleton-loader></v-col>
                    </v-row>
                </template>
                <transition name="slide-x-transition" v-else>
                    <v-container class="container-form mt-15">
                        <!-- Details 1 -->
                        <v-card class="pa-7 mb-5">
                            <v-row>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-combobox v-model="formData.institution" clearable label="Institution *"
                                        density="compact" :items="institutions" variant="outlined" itemValue="id"
                                        itemTitle="name" :rules="rule.institutionValue" :close-on-content-click="false"
                                        hide-details @update:modelValue="getAddress($event)">
                                    </v-combobox>
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
                                    <v-text-field color="primary" v-model="formData.item_code" label="Item Code"
                                        readonly density="compact" variant="outlined"></v-text-field>
                                </v-col>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-text-field color="primary" v-model="formData.description" label="Description"
                                        readonly density="compact" variant="outlined"></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <!-- <v-col cols="12" lg="6" md="6" sm="6">
                                <v-text-field color="primary" v-model="formData.item_code" label="Item Code *" clearable
                                    density="compact" variant="outlined" readonly :rules="rule.item_code"
                                    @click="overlayMasterData = !overlayMasterData"></v-text-field>
                            </v-col> -->
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-text-field color="primary" v-model="formData.equipment" label="Equipment *"
                                        clearable density="compact" variant="outlined"
                                        :rules="rule.equipment"></v-text-field>
                                </v-col>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-text-field color="primary" v-model="formData.serial" label="Serial Number *"
                                        clearable density="compact" variant="outlined"
                                        :rules="rule.serial"></v-text-field>
                                </v-col>
                            </v-row>
                        </v-card>

                        <!-- Details 2 -->
                        <v-card class="pa-7 mb-5">
                            <v-row>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-select color="primary" v-model="formData.mode" label="Mode *" clearable
                                        density="compact" variant="outlined" :rules="rule.mode"
                                        :items="mode"></v-select>
                                </v-col>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-combobox v-model="formData.supplier" clearable label="Supplier *"
                                        density="compact" :items="supplierData" variant="outlined" itemValue="id"
                                        itemTitle="name" :rules="rule.institutionValue" :close-on-content-click="false"
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
                                    <v-select color="primary" v-model="formData.area" label="Area Categorization"
                                        clearable density="compact" variant="outlined"
                                        :items="area_categorization"></v-select>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-select color="primary" v-model="formData.operation_time" label="Operation Time"
                                        clearable density="compact" variant="outlined"
                                        :items="operation_time"></v-select>
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
                                    <v-select color="primary" v-model="formData.frequency" label="Frequency for PM *"
                                        clearable density="compact" variant="outlined" :rules="[v => !!v || 'Required']"
                                        :items="pmFrequency"></v-select>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <p class="mb-2 text-grey-darken-1">Admission Date</p>
                                    <VueDatePicker v-model="formData.admission_date" auto-apply :teleport="true"
                                        :enable-time-picker="false" placeholder="Admission Date *"
                                        :rules="[v => !!v || 'Required']" />

                                </v-col>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <p class="mb-2 text-grey-darken-1">Date Installed</p>
                                    <VueDatePicker v-model="formData.date_installed" auto-apply :teleport="true"
                                        :enable-time-picker="false" placeholder="Date Installed" />
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <p class="mb-2 text-grey-darken-1">Contract Due Date</p>
                                    <VueDatePicker v-model="formData.contract_due_date" auto-apply :teleport="true"
                                        :enable-time-picker="false" placeholder="Contract Due Date" />
                                </v-col>
                            </v-row>
                        </v-card>
                        <v-card class="pa-7 mb-5">
                            <v-row>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-select color="primary" v-model="formData.region" label="Region" clearable
                                        density="compact" variant="outlined" :items="region"></v-select>
                                </v-col>
                                <v-col cols="12" lg="6" md="6" sm="6">
                                    <v-text-field color="primary" v-model="formData.analyzer_type"
                                        label="Analyzer Type *" clearable density="compact" variant="outlined"
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
                                        itemValue="id" itemTitle="first_name" :close-on-content-click="false"
                                        hide-details>
                                        <template v-slot:item="{ item, props }">
                                            <p v-bind="props">
                                                <v-hover v-slot="{ isHovering, props }">
                                                    <p v-bind="props"
                                                        :style="{ background: isHovering ? '#f2f2f2' : 'white' }"
                                                        class="pa-2">{{ item.raw.first_name }} {{ item.raw.last_name }}
                                                    </p>
                                                </v-hover>
                                            </p>
                                        </template>
                                        <template v-slot:selection="{ item, index }">
                                            <span>{{ item.raw.first_name }} {{ item.raw.last_name }}</span>
                                        </template>
                                    </v-combobox>
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
                        <VDialog v-model="leaveDialog" title="Discard Changes" text="Are you sure you want to leave?"
                            @confirm="leave" />
                    </v-container>
                </transition>
            </template>
        </LayoutSinglePage>
    </v-form>

</template>
<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'
import { useRouter, useRoute } from 'vue-router';
import { mode, master_data_status, area_categorization, region, operation_time } from '@/global/master_data';
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';
import TextField from '@/components/layout/FormsComponent/TextField.vue';
import _ from 'lodash'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

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

const id = route.params.id
/** Declarations */
const formData = ref({
    institution: '',
    address: '',
    item_code: '',
    description: '',
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
const getAddress = ((data) => {
    formData.value.address = data.address
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



const editMasterData = async () => {
    btnLoading.value = true
    // console.log(formData.value)
    const { valid } = await form.value.validate()

    if (!isChanged.value) {
        toast.error('No changes made')
        btnLoading.value = false
        return
    }

    if (!valid) {
        toast.error('Please ensure that required fields are not left blank')
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.put('editMasterData',
            {
                id: id,
                ...formData.value,
                institution: typeof formData.value.institution === 'object'
                    ? formData.value.institution?.id
                    : formData.value.institution,

                initially_installed: typeof formData.value.initially_installed === 'object'
                    ? formData.value.initially_installed?.id
                    : formData.value.initially_installed,

                supplier: typeof formData.value.supplier === 'object'
                    ? formData.value.supplier?.id
                    : formData.value.supplier
            })
        if (response.data && response.data.success) {
            toast.success('Item successfully updated')
            btnDisable.value = true
            getMasterDataByID()
            // router.push('/master-data')
        } else {
            toast.error('Something went wrong')
        }
    } catch (error) {
        console.error(error)
    } finally {
        btnLoading.value = false
        btnDisable.value = false
    }
}





/** Get Users table MASTER DATA */
const loading = ref(true);


// Instittuition
import { useInstitutions } from '@/helpers/getInstitution';
const { institutions } = useInstitutions()

// Supplier
const supplierData = ref([])
const get_supplier = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_supplier');
        if (response.data?.supplier) {
            supplierData.value = response.data.supplier
        }
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};

// Users
import { all_users } from '@/helpers/getUsers';
import VDialog from '@/components/common/VDialog.vue';
const { users } = all_users()
const getMasterDataByID = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('viewMasterDataByRowID', {
            params: { id: id },
        });
        const data = response?.data.data
        Object.entries(data).forEach(([key, value]) => {
            formData.value[key] = value
        })
        formData.value.address = data.institution_data?.address
        formData.value.item_code = data.master_data?.item_code
        formData.value.description = data.master_data?.description

        originalFormData.value = _.cloneDeep(formData.value)
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false;
    }
};
const originalFormData = ref({})
const isChanged = computed(() => {
    return !_.isEqual(formData.value, originalFormData.value)
})

/** Discard button */
const leaveDialog = ref(false)
const discard = () => {
    if (isChanged.value) {
        leaveDialog.value = true
        return
    }
    leave()
}
const leave = () => {
    leaveDialog.value = false
    router.push('/master-data')
}

onMounted(() => {
    getMasterDataByID()
    get_supplier()
});
</script>