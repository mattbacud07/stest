<template>
    <v-row class="ma-5">
        <v-col cols="12" lg="4" md="4" sm="4">
            <v-card class="pa-5" elevation="0">
                <h5 class="mb-5">Equipment Handling <span class="small" style="font-size: 10px;color: orangered;font-style: italic;">Please drag to rearrange</span></h5>
                <!-- <v-list lines="one"> -->
                <draggable v-model="eh_approvers" :group="{ name: 'eh_approver' }" :move="changeLevel">
                    <v-card v-for="data in eh_approvers" :key="data.id" elevation="0" 
                    color="primary" variant="tonal"
                        class="mb-3 pa-4 rounded-0">
                        <v-row>
                            <v-col>
                                <p><b>Level {{ data.approver_level }}</b></p>
                                <p class="small">{{ data.approver_name }}</p>
                            </v-col>
                        </v-row>
                    </v-card>
                </draggable>
                <!-- </v-list> -->
            </v-card>
        </v-col>
    </v-row>
</template>
<script setup>
import { onMounted, ref } from 'vue';

import { user_data } from '@/stores/auth/userData'
import { apiRequestAxios } from '@/api/api';
import * as pub_var from '@/global/global'

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** Draggable Plugins */
import { VueDraggableNext } from 'vue-draggable-next';
const draggable = VueDraggableNext
const enabled = ref(true)
const dragging = ref(false)

/** Toast Notification */
import { useToast } from 'vue-toast-notification';
const toast = useToast()


import { useDisplay } from 'vuetify'
const { width } = useDisplay()


/** Declaration of User Data */
const user = user_data();
user.getUserData
const apiRequest = apiRequestAxios()

const loadingSubmit = ref(false);
const loading = ref(false)




/** Submit Form */
const submitApprover = async () => {
    loadingSubmit.value = true
    try {
        const response = await apiRequest.post('submit-approver', {

        })
        if (response.data && response.data.success) {

        }
    } catch (error) {
        console.log(error)
    }
    finally {
        loadingSubmit.value = false
    }
}



/** Get all Approvers */
const eh_approvers = ref([])
const getDesignations = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('get_designations')
        if (response.data && response.data.designations) {
            const data = response.data.designations
            eh_approvers.value = data.filter(v => v.approver_category === pub_var.eh_approver)
        }
        // console.log(eh_approvers.value)

    } catch (error) {
        console.log(error)
    }

    loading.value = false;
};



/** Change level  */
const changeLevel = () => {
    // console.log(eh_approvers.value)
}



onMounted(() => {
    getDesignations();
});
</script>
