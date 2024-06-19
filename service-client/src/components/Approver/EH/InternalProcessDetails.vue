<template>
        <v-card class="mx-auto p-4 mt-10">
            <v-row>
            </v-row>
        </v-card>
</template>
<script setup>
import { onMounted, ref, reactive } from 'vue';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index'
import * as pub_var from '@/global/global'
import moment from 'moment';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

// axios.defaults.withCredentials = true

const btnDisabled = ref(true)
const datatable = ref(null)
const mapSelected = ref([])
const selectedId = ref(null)
const requestedId = ref(null)
const selectJustOneMessage = ref('')

/** Declaration of User Data Store*/
const user = user_data();
const apiRequest = user.apiRequest()




onMounted(() => {
    getInternalRequest();
});
</script>
