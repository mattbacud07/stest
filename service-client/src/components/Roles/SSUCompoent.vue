<template>
    <v-row>
        <v-col>
            <v-row class="pa-3">
                <v-col lg="8" md="8" sm="6" cols="12">
                    <v-dialog width="400" scrollable v-model="addActions">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" prepend-icon="mdi-plus" text="Create" class="text-none"
                                v-bind="activatorProps"></v-btn>
                        </template>
                        <v-card title="SSU">

                            <v-form @submit.prevent="saveAction" ref="form">
                                <v-divider class="mt-1"></v-divider>
                                <v-card-text class="px-4">
                                    <v-text-field v-model="formData.ssu" single-line prepend-inner-icon="mdi-badge-account-outline"
                                        :rules="[v => !!v || 'Required']" density="compact" variant="outlined"
                                        label="Set SSU" clearable></v-text-field>
                                </v-card-text>

                                <v-divider></v-divider>

                                <v-card-actions>
                                    <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                        @click="addActions = false"></v-btn>
                                    <v-spacer></v-spacer>

                                    <v-btn type="submit" color="primary" prependIcon="mdi-content-save-check-outline"
                                        class="text-none" text="Save" variant="flat" :loading="loadingSave"></v-btn>
                                </v-card-actions>
                            </v-form>
                        </v-card>
                    </v-dialog>


                    <v-dialog width="400" scrollable v-model="editActionsDialog">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" prepend-icon="mdi-file-edit-outline" text="Edit"
                                class="text-none ml-2" variant="tonal" v-bind="activatorProps"></v-btn>
                        </template>
                        <v-card title="SSU">

                            <v-form @submit.prevent="editActions" ref="form">
                                <v-divider class="mt-1"></v-divider>
                                <v-card-text class="px-4">
                                    <v-text-field v-model="formData.ssu" single-line prepend-inner-icon="mdi-badge-account-outline"
                                        :rules="[v => !!v || 'Required']" density="compact" variant="outlined"
                                        label="Set SSU" clearable></v-text-field>
                                </v-card-text>

                                <v-divider></v-divider>

                                <v-card-actions>
                                    <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                        @click="editActionsDialog = false"></v-btn>
                                    <v-spacer></v-spacer>

                                    <v-btn type="submit" color="primary" prependIcon="mdi-content-save-check-outline"
                                        class="text-none" text="Save" variant="flat" :loading="loadingSave"></v-btn>
                                </v-card-actions>
                            </v-form>
                        </v-card>
                    </v-dialog>


                    <!-- <v-btn color="primary" :disabled="btnDisable" prepend-icon="mdi-shield-edit-outline" text="Edit"
                    class="text-none ml-2" variant="tonal"></v-btn> -->


                    <v-dialog width="400" scrollable v-model="deleteRoleDialog">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="error" :disabled="btnDisable" prepend-icon="mdi-delete-empty" text="Delete"
                                class="text-none ml-2" variant="plain" v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-trash-can" subtitle="Delete">
                                <p class="p3"></p>
                                <v-card-text>Are you sure you want to delete?</v-card-text>
                                <v-card-actions>
                                    <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                        @click="isActive.value = false"></v-btn>
                                    <v-spacer></v-spacer>

                                    <v-btn color="danger" prepend-icon="mdi-shield-remove-outline" text="Delete"
                                        class="text-none ml-2" variant="outlined" @click="deleteAction"
                                        :loading="loadingSave"></v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>

                </v-col>
                <v-spacer></v-spacer>
                <v-col lg="4" md="4" sm="6" cols="12">
                    <v-text-field color="primary" v-model="params.search" clearable density="compact"
                        label="Search all fields" variant="outlined"></v-text-field>
                </v-col>
            </v-row>




            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" :sortColumn="params.sort_column"
                :sortDirection="params.sort_direction" skin="bh-table-compact bh-table-bordered" class="mt-5"
                @rowSelect="isChecked">
                <template #created_at='data'>
                    {{ pub_var.formatDate(data.value.created_at) }}
                </template>
                <template #updated_at='data'>
                    {{ pub_var.formatDate(data.value.updated_at) }}
                </template>
            </vue3-datatable>
        </v-col>
    </v-row>
</template>
<script setup>
import { ref, reactive, onMounted } from 'vue';
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'

/** ToastPlugin Notifcation */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const apiRequest = apiRequestAxios()


const form = ref(false)
const datatable = ref(null)
const addActions = ref(false)
const deleteRoleDialog = ref(false)
const editActionsDialog = ref(false)
const btnDisable = ref(false)
// const actions = ref('')
const loadingSave = ref(false)

/** Fetching Data Users */
const loading = ref(true);
const total_rows = ref(0);
const rowId = ref(null)

/** Check - Selecting Users */
const isChecked = (row) => {
    if (row && row.length > 0) {
        btnDisable.value = false
        formData.value.ssu = row[0].ssu
    } else {
        btnDisable.value = true
    }

    rowId.value = row.map(v => v.id)[0]
}

const params = reactive({ current_page: 1, pagesize: 10, sort_column: 'id', sort_direction: 'desc' });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: false },
        { field: 'ssu', title: 'SSU', },
        { field: 'created_at', title: 'Created_at' },
        { field: 'updated_at', title: 'Updated_at' },
    ]) || [];



const formData = ref({
    ssu: '',
})

const saveAction = async () => {
    loadingSave.value = true;

    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSave.value = false
        return
    }


    try {
        const response = await apiRequest.post('ssu/store', formData.value);
        if (response.data && response.data.success) {
            toast.success('Successfully added')
            getSSU()
            addActions.value = false
            form.value.reset()
        } 
        else if(response.data.exist) toast.error('SSU name exist')
        else toast.error('Something went wrong')
    } catch (error) {
        console.log(error)
    } finally {
        loadingSave.value = false;
    }

}


const editActions = async () => {
    loadingSave.value = true;

    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSave.value = false
        return
    }

    try {
        const response = await apiRequest.put('ssu/edit', {id : rowId.value, ...formData.value});
        if (response.data && response.data.success) {
            toast.success('Successfully updated')
            getSSU()
            editActionsDialog.value = false
            form.value.reset()
        } 
        else if(response.data.exist) toast.error('SSU name exist')
        else toast.error('Something went wrong')
    } catch (error) {
        console.log(error)
    } finally {
        loadingSave.value = false;
    }

}


const deleteAction = async () => {
    loadingSave.value = true;
    try {
        const response = await apiRequest.delete('ssu/delete', { params: { id: rowId.value } });
        if (response.data && response.data.success) {
            toast.success('Successfully deleted')
            getSSU()
            deleteRoleDialog.value = false
            form.value.reset()
        }
         else toast.error('Something went wrong')
    } catch (error) {
        console.log(error)
    } finally {
        loadingSave.value = false;
    }

}










/**
* Get RoleS
*/
const getSSU = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('ssu');
        const data = response.data.ssu

        rows.value = data
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};


onMounted(() => {
    getSSU()
})

</script>