<template>
    <v-row>
        <v-col cols="12">
            <v-row>
                <v-col>
                    <v-dialog width="400" scrollable v-model="isActive">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="primary" prepend-icon="mdi-shield-plus-outline" text="Add" class="text-none"
                                v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-shield-plus-outline" title="Role">

                                <v-form @submit.prevent="saveRole" ref="form">
                                    <v-divider class="mt-1"></v-divider>
                                    <v-card-text class="px-4">
                                        <v-text-field v-model="role_name" :rules="rule.role" density="compact"
                                            variant="outlined" label="Role Name"></v-text-field>
                                        <v-textarea v-model="description" density="compact" rows="5" label="Description"
                                            variant="outlined" color="primary" class="mt-5"></v-textarea>
                                    </v-card-text>

                                    <v-divider></v-divider>

                                    <v-card-actions>
                                        <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                            @click="isActive.value = false"></v-btn>
                                        <v-spacer></v-spacer>

                                        <v-btn type="submit" color="primary"
                                            prependIcon="mdi-content-save-check-outline" class="text-none" text="Save"
                                            variant="flat" :loading="loadingSave"></v-btn>
                                    </v-card-actions>
                                </v-form>
                            </v-card>
                        </template>
                    </v-dialog>


                    <v-btn color="primary" :disabled="btnDisable" prepend-icon="mdi-shield-edit-outline" text="Edit"
                        class="text-none ml-2" variant="tonal"></v-btn>


                    <v-dialog width="400" scrollable v-model="deleteRoleDialog">
                        <template v-slot:activator="{ props: activatorProps }">
                            <v-btn color="error" :disabled="btnDisable" prepend-icon="mdi-delete-empty" text="Delete"
                                class="text-none ml-2" variant="plain" v-bind="activatorProps"></v-btn>
                        </template>

                        <template v-slot:default="{ isActive }">
                            <v-card prepend-icon="mdi-trash-can" color="error" subtitle="Delete Role">
                                <p class="p3"></p>
                                <v-card-text>Are you sure you want to delete?</v-card-text>
                                <v-card-actions>
                                    <v-btn text="Close" prependIcon="mdi-arrow-left" class="text-none"
                                        @click="isActive.value = false"></v-btn>
                                    <v-spacer></v-spacer>

                                    <v-btn color="danger" prepend-icon="mdi-shield-remove-outline" text="Delete"
                                        class="text-none ml-2" variant="flat" @click="deleteRole" :loading="loadingSave"></v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                    
                </v-col>
                <v-spacer></v-spacer>
                <v-col>
                    <v-text-field color="primary" v-model="params.search" clearable density="compact"
                        label="Search all fields" variant="outlined"></v-text-field>
                </v-col>
            </v-row>
            <!-- <p class="text-danger" style="font-size: .8em;">* Select at least one</p> -->
            <vue3-datatable ref="datatable" :rows="rows" :columns="cols" :loading="loading" :search="params.search"
                :selectRowOnClick="true" :hasCheckbox="true" :sortable="true" skin="bh-table-compact bh-table-bordered"
                class="mt-5" @rowSelect="isChecked">
                <template #created_at="data">
                    <span>{{ pub_var.formatDate(data.value.created_at) }}</span>
                </template>
            </vue3-datatable>
        </v-col>
        <alertMessage v-if="messageDetails.show" :details="messageDetails" />
    </v-row>
</template>
<script setup>
import { ref, reactive, onMounted, watch, provide } from 'vue';
import { user_data } from '@/stores/auth/userData'
import * as pub_var from '@/global/global.js'
import alertMessage from '@/components/PopupMessage/alertMessage.vue'


/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

const user = user_data()
user.getUserData
const apiRequest = user.apiRequest()
const form = ref(false)
const role_name = ref('')
const description = ref('')
const datatable = ref(null)
const isActive = ref(false)
const deleteRoleDialog = ref(false)
const btnDisable = ref(true)
const loadingSave = ref(false)
const refreshKey = ref(0)
const messageDetails = ref({})
const rowId = ref(null)

const rule = ref({
    role: [
        v => !!v || 'Required'
    ],
})


/** Check - Selecting Users */
const isChecked = () => {
    const selectedRows = datatable.value.getSelectedRows()
    if (selectedRows && selectedRows.length > 0) {
        btnDisable.value = false
    } else {
        btnDisable.value = true
    }

    rowId.value = selectedRows.map(v => v.id)[0]
}

/** Add Role */
const saveRole = async () => {
    loadingSave.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        loadingSave.value = false
        return
    }
    try {
        const response = await apiRequest.post('add_role_name', {
            role_name: role_name.value,
            description: description.value,
        })
        if (response.data && response.data.success) {
            messageDetails.value = { show: true, color: 'success', text: 'Successfully created' }
            isActive.value = false
            getRoleName()
        } else {
            console.log(response.data.error)
        }
    } catch (error) {
        console.log(error);
    } finally {
        loadingSave.value = false
    }
}


/** Delete Role */
const deleteRole = async () => {
    try {
        const response = await apiRequest.delete('delete_role_name', {
            params: {
                id: rowId.value
            }
        })
        if (response.data && response.data.success) {
            messageDetails.value = { show: true, color: 'success', text: 'Successfully deleted' }
            deleteRoleDialog.value = false
            getRoleName()
        }
    } catch (error) {
        console.log(error)
    } finally {
        // loadingSave.value = false
    }
}







/** Fetching Data Users */
const loading = ref(true);
const total_rows = ref(0);


const params = reactive({ current_page: 1, pagesize: 10 });
const rows = ref(null);

const cols =
    ref([
        { field: 'id', title: 'ID', isUnique: true, type: 'number', hide: true },
        { field: 'role_name', title: 'Role' },
        { field: 'description', title: 'Description' },
        { field: 'created_at', title: 'Created_at' },
    ]) || [];

const getRoleName = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_role_name');
        const data = response.data.role_name

        rows.value = data
    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};
const changeServer = (data) => {
    params.current_page = data.current_page;
    params.pagesize = data.pagesize;

    getRoleName();
};


onMounted(() => {
    getRoleName();
});
</script>