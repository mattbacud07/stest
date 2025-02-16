<template>
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
        </template>
        <template #default>
            <v-container class="container-form mt-15">
                <!-- Details 1 -->
                <v-row class="pa-7 mb-5" v-if="!loading">
                    <v-chip label size="small" color="primary" variant="tonal">
                        Status: <b>{{ formData.status ?? '---' }}</b>
                    </v-chip>
                </v-row>
                <v-hover v-slot="{ isHovering, props }">
                    <transition name="scale-transition">
                        <v-card :class="['pa-7', 'mb-5']" :elevation="isHovering ? 7 : 1" v-bind="props"
                            v-if="!loading">
                            <v-row>
                                <v-col cols="12" lg="4" md="4" sm="4">
                                    <p>Institution</p>
                                    <p class="text-grey-darken-1">{{ institution.name ?? '---' }}</p>
                                </v-col>
                                <v-col cols="12" lg="4" md="4" sm="4">
                                    <p>Address</p>
                                    <p class="text-grey-darken-1">{{ institution.address ?? '---' }}</p>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" lg="4" md="4" sm="4">
                                    <p>Item Code</p>
                                    <p class="text-grey-darken-1">{{ master_data.item_code ?? '---' }}</p>
                                </v-col>
                                <v-col cols="12" lg="4" md="4" sm="4">
                                    <p>Equipment</p>
                                    <p class="text-grey-darken-1">{{ formData.equipment ?? '---' }}</p>
                                </v-col>
                                <v-col cols="12" lg="4" md="4" sm="4">
                                    <p>Serial No.</p>
                                    <p class="text-grey-darken-1">{{ formData.serial ?? '---' }}</p>
                                </v-col>
                            </v-row>
                        </v-card>
                    </transition>
                </v-hover>

                <!-- Details 2 -->
                <v-hover v-slot="{ isHovering, props }">
                    <v-card :class="['pa-7', 'mb-5']" :elevation="isHovering ? 7 : 1" v-bind="props">
                        <v-row>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Mode</p>
                                <p class="text-grey-darken-1">{{ formData.mode ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Supplier</p>
                                <p class="text-grey-darken-1">{{ supplier.name ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Dealer Name</p>
                                <p class="text-grey-darken-1">{{ formData.dealer_name ?? '---' }}</p>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>SBU</p>
                                <p class="text-grey-darken-1"><span v-if="formData.sbu">SBU - </span> {{
                                    formData.sbu ??
                                    '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Area</p>
                                <p class="text-grey-darken-1">{{ formData.area ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Operation Time</p>
                                <p class="text-grey-darken-1">{{ formData.operation_time ?? '---' }}</p>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Software Version</p>
                                <p class="text-grey-darken-1">{{ formData.software_version ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Frequency</p>
                                <p class="text-grey-darken-1">{{ formData.frequency ?? '---' }}</p>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-hover>

                <!-- Details 3 -->
                <v-hover v-slot="{ isHovering, props }">
                    <v-card :class="['pa-7', 'mb-5']" :elevation="isHovering ? 7 : 1" v-bind="props">
                        <v-row>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Admission Date</p>
                                <p class="text-grey-darken-1">{{ formatDateNoTime(formData.admission_date) }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Date Installed</p>
                                <p class="text-grey-darken-1">{{ formatDateNoTime(formData.date_installed) }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Contract due date</p>
                                <p class="text-grey-darken-1">{{ formatDateNoTime(formData.contract_due_date) }}</p>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Region</p>
                                <p class="text-grey-darken-1">{{ formData.region ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Analyzer Type</p>
                                <p class="text-grey-darken-1">{{ formData.analyzer_type ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Class</p>
                                <p class="text-grey-darken-1">{{ formData.class ?? '---' }}</p>
                            </v-col>
                        </v-row>
                        <v-row>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Initially Installed</p>
                                <p class="text-grey-darken-1">{{ formData.initially_installed ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4">
                                <p>Reason Equipment Status</p>
                                <p class="text-grey-darken-1">{{ formData.reason_equipment_status ?? '---' }}</p>
                            </v-col>
                            <v-col cols="12" lg="4" md="4" sm="4" class="mb-5">
                                <p>Email</p>
                                <p class="text-grey-darken-1">{{ formData.email ?? '---' }}</p>
                            </v-col>
                        </v-row>
                    </v-card>
                </v-hover>
            </v-container>
        </template>
    </LayoutSinglePage>

</template>
<script setup>
import { ref, onMounted } from 'vue';
import '@vuepic/vue-datepicker/dist/main.css'
import { useRouter, useRoute } from 'vue-router';
import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';

/** Vuue3 DataTable */
import Vue3Datatable from '@bhplugin/vue3-datatable'
import '@bhplugin/vue3-datatable/dist/style.css'

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

/** Import vuetify UseDisplay */
import { useDisplay } from 'vuetify'
const { name, width } = useDisplay()

import { apiRequestAxios } from '@/api/api';
import { formatDateNoTime } from '@/global/global';

/** data declarations */
const router = useRouter()
const apiRequest = apiRequestAxios()


const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/master-data' },
    { title: 'Master Data', disabled: true, href: '' },
    { title: 'View-Master-Data', disabled: true, href: '' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};

const route = useRoute()
const id = route.params.id

/** Declarations */
const loading = ref(false)
const formData = ref({})
const institution = ref({})
const supplier = ref({})
const master_data = ref({})
const users = ref({})
const getMasterDataByID = async () => {
    try {
        loading.value = true;
        const response = await apiRequest.get('viewMasterDataByRowID', {
            params: { id: id },
        });
        const data = response?.data.data
        formData.value = data
        institution.value = data.institution_data
        supplier.value = data.suppliers_data
        users.value = data.users_data
        master_data.value = data.master_data
    } catch (error) {
        console.log(error)
    } finally {
        loading.value = false;
    }
};



onMounted(() => {
    getMasterDataByID();
});
</script>