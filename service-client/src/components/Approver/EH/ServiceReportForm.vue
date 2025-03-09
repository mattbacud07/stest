<template>
    <!-- ========================== Checklist Item and Actions Taken  Form ======================================== -->

    <v-form ref="serviceForm">
        <v-card density="compact" class="pa-7" elevation="1">
            <v-row>
                <h4 class="text-center text-grey"><v-icon>mdi-chart-bar-stacked</v-icon> Service Report</h4>
            </v-row>
            <v-row>
                <v-col cols="12">
                    <b class="text-primary"> Status after service</b>
                    <v-radio-group v-model="formData.status_after_service" :rules="[v => !!v || 'Required']" inline>
                        <v-radio label="Operational" value="Operational"></v-radio>
                        <v-radio label="For further monitoring" value="Further Monitoring"></v-radio>
                        <v-radio label="Non-operational" value="Non-Operational"></v-radio>
                    </v-radio-group>
                </v-col>
            </v-row>


            <v-row>
                <v-col cols="12" md="6" sm="6" lg="6">
                    <p class="text-primary">Customer Complaint</p>
                    <v-textarea v-model="formData.complaint" color="primary" variant="outlined"
                        placeholder="Type your remarks & recommendations here ..."
                        :rules="[v => (v && v.length <= 120) || 'Please limit your input to 120 characters']">
                    </v-textarea>
                </v-col>
                <v-col cols="12" md="6" sm="6" lg="6">
                    <p class="text-primary">Cause of Problem</p>
                    <v-textarea v-model="formData.problem" color="primary" variant="outlined"
                        placeholder="Type your remarks & recommendations here ..."
                        :rules="[v => (v && v.length <= 120) || 'Please limit your input to 120 characters']">
                    </v-textarea>
                </v-col>
            </v-row>

            <v-row>
                <v-col cols="12">
                    <b class="text-primary">Remarks and Recommendation</b>
                    <v-textarea v-model="formData.remarks" color="primary" variant="outlined"
                        placeholder="Type your remarks & recommendations here ..."
                        :rules="[v => (v && v.length <= 120) || 'Please limit your input to 120 characters']">
                    </v-textarea>
                </v-col>
            </v-row>


            <v-row>
                <v-col class="d-flex justify-content-between align-items-center">
                    <b class="text-primary">Actions Taken</b>

                    <v-btn @click="addField" class="text-none" color="primary" variant="text"
                        prepend-icon="mdi-comment-plus-outline">Add
                        Field</v-btn>
                </v-col>
            </v-row>
            <v-col cols="12">
                <v-card elevation="0">
                    <v-row>
                        <v-col cols="12" v-for="(field, index) in formData.fields" v-if="formData.fields.length > 0"
                            :key="index">
                            <v-combobox v-model="field.action" :items="actions_data" item-title="actions"
                                item-value="actions" label="Select an option or enter your action manually"
                                append-inner-icon="mdi-trash-can-outline" variant="outlined" color="primary"
                                class="mt-3" density="compact" clearable :rules="[
                                    v => !!v?.trim() || 'Required',
                                    v => (v && v.length <= 120) || 'Please limit your input to 120 characters'
                                ]" @click:append-inner="removeField(index)">
                                <template v-slot:item="{ item, props }">
                                    <p v-bind="props" class="pa-2 selecting_action">
                                        <v-icon>mdi-circle-medium</v-icon> {{ item.title }}
                                    </p>
                                    <!-- <v-list-item v-bind="props" class="pa-2"></v-list-item> -->
                                </template>
                            </v-combobox>
                        </v-col>
                        <v-col v-else>
                            <p>No records found</p>
                        </v-col>
                    </v-row>
                </v-card>
            </v-col>
        </v-card>




        <v-card density="compact" class="p-3 mt-4" elevation="0">
            <v-row>
                <v-col cols="12">
                    <v-row>
                        <v-col class="d-flex justify-content-between mb-3">
                            <h5 class="text-primary">Reagents / Spare Parts Used</h5>
                        </v-col>
                    </v-row>
                    <div class="table-responsive">
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
                            <tbody v-if="formData.spareparts?.length > 0">
                                <tr v-for="(item, index) in formData.spareparts" :key="index">
                                    <td>
                                        {{ item.item_code }}
                                    </td>
                                    <td>{{ item.description }}</td>
                                    <td>
                                        <v-text-field v-model="item.qty" placeholder="..." :rules="[v => !!v?.trim() || 'required',
                                        v => !isNaN(v) || 'Mus tbe a number'
                                        ]" density="compact" variant="plain"></v-text-field>
                                    </td>

                                    <td>
                                        <v-text-field v-model="item.dr" placeholder="..." :rules="[v => !!v?.trim() || 'required',
                                        v => !isNaN(v) || 'Mus tbe a number'
                                        ]" density="compact" variant="plain"></v-text-field>
                                    </td>

                                    <td>
                                        <v-text-field v-model="item.si" placeholder="..." :rules="[v => !!v?.trim() || 'required',
                                        v => !isNaN(v) || 'Mus tbe a number'
                                        ]" density="compact" variant="plain"></v-text-field>
                                    </td>

                                    <td>
                                        <v-text-field v-model="item.remarks" placeholder="Type here ..."
                                            variant="plain"></v-text-field>
                                    </td>

                                    <td><v-icon @click="removeSpareparts(index)"
                                            class="text-danger">mdi-trash-can-outline</v-icon>
                                    </td>
                                </tr>
                            </tbody>
                            <tbody v-else>
                                <tr>
                                    <td colspan="7" class="text-center p-3" style="opacity: .3;">
                                        <v-icon class="mb-3"
                                            style="font-size: 30px;color:grey;">mdi-file-document-alert-outline</v-icon><br>
                                        No records found
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <!-- Overlay box for Equipment Peripherals -->
                    <div class="d-flex justify-content-start">
                        <v-btn color="primary" size="medium" flat class="text-none mt-3 p-2"
                            @click="masterDataDialog = !masterDataDialog">
                            <v-icon class="mr-3">mdi-database-cog</v-icon>
                            Select Item
                        </v-btn>
                        <v-overlay v-model="masterDataDialog" class="d-flex align-items-center justify-content-center"
                            location-strategy="connected">
                            <v-card class="pa-7" min-height="500" width="700">
                                <v-row>
                                    <v-col cols="4">
                                        <v-text-field v-model="params.search" clearable density="compact"
                                            label="Search all fields" variant="outlined" color="primary"></v-text-field>
                                    </v-col>
                                    <v-spacer></v-spacer>
                                    <v-btn @click="masterDataDialog = false" icon variant="plain"
                                        color="primary"><v-icon>mdi-close</v-icon></v-btn>
                                </v-row>
                                <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading"
                                    :selectRowOnClick="true" :sortable="true" :search="params.search"
                                    :isServerMode="true" :totalRows="total_rows" :pageSize="params.pagesize"
                                    :hide="true" :filter="true"
                                    skin="bh-table-compact bh-table-bordered bh-table-responsive" class=""
                                    @rowClick="rowClickParts" @change="changeServer"></vue3-datatable>
                                <v-divider></v-divider>
                                <p class="text-danger"><b>List of Selected Row</b><br>
                                    <span v-if="formData.spareparts.length > 0">
                                        <span v-for="(itemRow, index) in formData.spareparts" :key="itemRow.id">
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
            </v-row>
        </v-card>
    </v-form>
</template>
<script setup>
import { ref, watch, reactive, onMounted } from 'vue';

import { useActions } from '@/helpers/getActionsTaken';
const { actions_data } = useActions()

import { debounce } from 'lodash';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

import { apiRequestAxios } from '@/api/api';
const apiRequest = apiRequestAxios()


const props = defineProps({
    modelValue: Object
})

const emit = defineEmits(['update:modelValue'])


// const option_type = ref('')
const formData = ref({
    status_after_service: '',
    fields: [],
    spareparts: [],
    remarks: '',
    complaint: '',
    problem: '',
});

watch(formData, (newVal) => {
    emit('update:modelValue', newVal)
}, { deep: true })


// ===================================== Sparepart Used
/** SpareParts */
const masterDataDialog = ref(false)
const spareparts = ref([])
const datatable = ref(false)
const loading = ref(false);
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
        const data = response?.data?.equipments

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
/*** Row Click Selection 0f Peripherals and get Data */
const rowClickParts = (data) => {
    formData.value.spareparts?.push({
        item_id: data.id,
        item_code: data.item_code,
        description: data.description,
        qty: null,
        dr: null,
        si: null,
        remarks: null,
    })
}
const removeSpareparts = (index) => {
    formData.value.spareparts?.splice(index, 1)
}









// ===================================== Actions Taken
const addField = () => {
    if (formData.value.fields.length < 8) {
        formData.value.fields.push({
            action: ''
        });
    }
};
const removeField = (index) => {
    formData.value.fields.splice(index, 1);
};
const serviceForm = ref(false)
const validateServiceForm = async () => {
    const { valid } = await serviceForm.value.validate()
    return valid
}

defineExpose({ validateServiceForm })



onMounted(() => {
    getMasterData()
})
</script>