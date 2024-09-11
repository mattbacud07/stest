<template>
    <v-card class="pt-3 pl-2 mt-3" elevation="0" style="border: 1px dashed #191970">
        <v-row>
            <v-col class="d-flex justify-content-between">
                <h5 class="text-primary">Status After The Service</h5>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="12">
                <v-radio-group v-model="formData.status_after_service" :disabled="textDisable"
                    @change="statusAfterService" color="primary" class="radioStatAfterService">
                    <v-radio value="Operational" label="Operational" true-icon="mdi-checkbox-marked"
                        false-icon="mdi-checkbox-blank-outline"></v-radio>
                    <v-radio value="For Further Monitoring" label="For Further Monitoring"
                        true-icon="mdi-checkbox-marked" false-icon="mdi-checkbox-blank-outline"></v-radio>
                    <v-radio value="Non-Operational" label="Non-Operational" true-icon="mdi-checkbox-marked"
                        false-icon="mdi-checkbox-blank-outline"></v-radio>
                </v-radio-group>
            </v-col>
        </v-row>
    </v-card>

    <!-- Spare Parts Used -->
    <v-card class="pt-3 pl-2 mt-3" elevation="0" style="border: 1px dashed #191970">
        <v-row>
            <v-col class="d-flex justify-content-between">
                <h5 class="text-primary">Reagents / Spare Parts Used</h5>
            </v-col>
        </v-row>
        <v-col col="12">
            <div class="table-responsive sparepartsField">
                <table class="table table-sm table-bordered table-responsive">
                    <thead style="background: #afafaf2e;">
                        <tr>
                            <th scope="col">Item Code</th>
                            <th scope="col">Item Description</th>
                            <th>Qty</th>
                            <th>DR#</th>
                            <th>SI#</th>
                            <th>Remarks</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody v-if="spareparts.length > 0">
                        <tr v-for="(item, index) in spareparts" :key="index">
                            <td>
                                {{ item.item_code }}
                                <!-- <v-text-field class="hideID">{{ item.id }}</v-text-field> -->
                            </td>
                            <td>{{ item.description }}</td>
                            <td><input placeholder="Required field ..." :required="true" v-model="item.qty">
                            </td>
                            <td><input type="text" placeholder="Required field ..." v-model="item.dr">
                            </td>
                            <td><input type="text" placeholder="Required field ..." v-model="item.si">
                            </td>
                            <td><input type="text" placeholder="Type here ..." v-model="item.remarks">
                            </td>
                            <td><v-icon @click="removeSpareparts(index)"
                                    class="text-danger">mdi-trash-can-outline</v-icon>
                            </td>
                        </tr>
                    </tbody>
                    <tbody v-if="sparepartsUsed.length > 0">
                        <tr v-for="(item, index) in sparepartsUsed" :key="item.id">
                            <td>
                                {{ item.equipment.item_code }}
                                <!-- <v-text-field class="hideID">{{ item.id }}</v-text-field> -->
                            </td>
                            <td>{{ item.equipment.description }}</td>
                            <td>{{ item.qty }}</td>
                            <td>{{ item.dr }}</td>
                            <td rowspan="2">{{ item.si }}</td>
                        </tr>
                    </tbody>
                    <tbody v-else>
                        <tr>
                            <td colspan="7" class="text-center p-3" style="opacity: .3;">
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
                <v-btn v-if="!textDisable" color="primary" size="medium" flat class="text-none mt-3 p-2"
                    @click="overlayMasterData = !overlayMasterData">
                    <v-icon class="mr-3">mdi-database-cog</v-icon>
                    Select Item
                </v-btn>
                <v-overlay v-model="overlayMasterData" class="d-flex align-items-center justify-content-center"
                    location-strategy="connected">
                    <v-card class="pa-7" min-height="500">
                        <v-row>
                            <v-col cols="4">
                                <v-text-field v-model="params.search" clearable density="compact"
                                    label="Search all fields" variant="outlined" color="primary"></v-text-field>
                            </v-col>
                            <v-spacer></v-spacer>
                            <v-btn @click="overlayMasterData = false" icon variant="plain"
                                color="primary"><v-icon>mdi-close</v-icon></v-btn>
                        </v-row>
                        <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading"
                            :selectRowOnClick="true" :sortable="true" :search="params.search" :isServerMode="true"
                            :totalRows="total_rows" :pageSize="params.pagesize" :hide="true" :filter="true"
                            skin="bh-table-compact bh-table-bordered bh-table-responsive" class=""
                            @rowClick="rowClickParts" @change="changeServer"></vue3-datatable>
                        <v-divider></v-divider>
                        <p class="text-danger"><b>List of Selected Row</b><br>
                            <span v-if="spareparts.length > 0">
                                <span v-for="(itemRow, index) in spareparts" :key="itemRow.id">
                                    [ {{ itemRow.item_code }} - <v-icon @click="removeSpareparts(index)"
                                        class="text-danger">mdi-trash-can-outline</v-icon>] &nbsp;
                                </span>
                            </span>
                            <span v-else class="small">No data selected</span>
                        </p>
                    </v-card>
                </v-overlay>
            </div>
        </v-col>
    </v-card>
</template>

<script setup>
import { ref, inject, onMounted, watch, defineEmits, defineProps, toRefs, reactive } from 'vue'
import * as m_var from '@/global/maintenance'
import * as pub_var from '@/global/global'
import { getRole } from '@/stores/getRole';
import debounce from 'lodash/debounce';
import { user_data } from '@/stores/auth/userData'
const user = user_data()
const apiRequest = user.apiRequest()
const emit = defineEmits(['status-after-service', 'sparePartsData']);

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'


const role = getRole()
const currentRole = role.currentUserRole

const formData = ref({
    status_after_service: '',
})

const pm_data = ref(inject('pm_data'))

const props = defineProps({
    status: {
        type: String,
    },
    pm_id: {
        type: Number,
        default: 0,
    }
})

const { status, pm_id } = toRefs(props)

const textDisable = ref(currentRole === pub_var.engineerRole && status.value === m_var.InProgress ? false : true)


/** SpareParts */
const overlayMasterData = ref(false)
const spareparts = ref([])
const datatable = ref(false)
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

const getMasterData = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('master-data-equipments', {
            params: { ...params, searchColumn: searchColumn, item_category: [5, 6] },
        });
        const data = response.data.equipments

        rows.value = data.data
        total_rows.value = data.total;
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

/*** Row Click Selection pf Peripherals and get Data */
const rowClickParts = (data) => {
    spareparts.value.push({
        item_id: data.id,
        item_code: data.item_code,
        description: data.description,
        qty: '',
        dr: '',
        si: '',
        remarks: '',
    })
}
const removeSpareparts = (index) => {
    spareparts.value.splice(index, 1)
}

/** Watch Spareparts (If > 0 then emit) */
watch(spareparts, (val) => {
    if (val.length > 0) {
        val.forEach((data) => {
            if (data.qty < 1 || isNaN(data.qty)) {
                data.qty = ''
            }
        })
        emit('sparePartsData', val)
    }
}, { deep: true })






const statusAfterService = () => {
    emit('status-after-service', formData.value.status_after_service)
}


/** =============== Get Spareparts for viewing the records ========================== */
const sparepartsUsed = ref([])
const getSparepartUsed = async () => {
    try {
        const response = await apiRequest.get('spareparts', { params: { pm_id: pm_id.value } })
        if (response.data && response.data.spareparts) {
            sparepartsUsed.value = response.data.spareparts
        }
    } catch (error) {
        alert(error)
    }
}



watch(pm_data, (pm) => {
    if (pm) {
        const pm_data = pm
        formData.value = {
            status_after_service: pm_data.status_after_service || '',
        }
    }
}, { immediate: true })



onMounted(() => {
    getMasterData();
    getSparepartUsed()
});

</script>


<style scoped>
.radioStatAfterService .v-selection-control--disabled {
    color: #191970 !important;
    opacity: 1 !important;
}

.sparepartsField input {
    outline: none;
    border: none;
    width: auto;
    padding: 5px 2px;
}
</style>