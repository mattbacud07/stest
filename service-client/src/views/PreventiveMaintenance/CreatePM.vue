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
                <v-container class="container-form mt-10">
                    <v-card>
                        <v-row>
                            <v-col cols="12" lg="6" md="6" sm="6">
                            <v-text-field color="primary" density="compact" label="Requested by"
                                placeholder="Requested by" variant="outlined" readonly v-model=""
                                hide-details></v-text-field>
                        </v-col>
                        <v-col cols="12" lg="6" md="6" sm="6">
                            <v-combobox color="primary" v-model="institutionValue" clearable label="Institution"
                                density="compact" :items="institutionData" variant="outlined" itemValue="value"
                                itemTitle="key" :rules="rule.institutionValue" :close-on-content-click="false"
                                hide-details></v-combobox>
                        </v-col>
                        </v-row>
                    </v-card>


                    <!-- Equipment & Peripherals -->
                    <v-card class="mt-3 p-3" elevation="0" style="border: 1px dashed #191970;">
                        <v-row>
                            <h5 class="p-3" style="font-weight: 500;color: #191970;">EQUIPMENTS</h5>
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
const apiRequest = apiRequestAxios()

const datatable = ref(null)
const form = ref(false)
const dialog = ref(false)
const overlayMasterData = ref(false)
const btnDisable = ref(false)
const btnLoading = ref(false)
const institutionData = ref([])

const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '#', display: 'block' },
    { title: 'Create Maintenance', disabled: true, href: '', display: 'none' },
    { title: '', disabled: true, href: '', display: 'none' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};

/** Declarations */
const institutionValue = ref('')


/** Input Form Rules */
const rule = ref({
    ruleEquipmentSerial: [
        v => !!v || 'Required'
    ],
    institutionValue: [
        v => !!v || 'Required'
    ],
})


const submitMaintenance = async () => {
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        toast.error('Please ensure that required fields are not left blank')
        btnLoading.value = false
        return
    }

    try {

    } catch (error) {

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
const selectedEquipment = ref([])
const rowClickEquipment = (data) => {
    selectedEquipment.value.push({
        id: data.id,
        item_code: data.item_code,
        description: data.description,
        equipmentSerial: '',
        equipmentRemark: ''
    })
}
const removeSelectedEquipment = (index) => {
    selectedEquipment.value.splice(index, 1)
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


const column = ref(6)
// watch(width, (val) => {
//     if (val < 550) {
//         column.value = 12
//     } else if (val < 768) {
//         cols.value.forEach((col) => {
//             if (col.field === 'description') {
//                 col.hide = true
//             }
//         })
//     }
//     else {
//         column.value = 6
//         cols.value.forEach((col) => {
//             if (col.field === 'description') {
//                 col.hide = false
//             }
//         })
//     }
// })

// onBeforeRouteLeave(async (to, from, next) => {
//     const { valid } = await form.value.validate()
//     if (!valid) {
//         const confirm = window.confirm('Discard Changes?')
//         if (confirm) next()
//         else next(false)
//     }
//     else next()

// })

/** Discard button */
const discard = () => {
    // router.push('/equipment-handling')
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