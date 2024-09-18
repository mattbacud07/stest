<template>
    <LayoutSinglePage>
        <template #topBarFixed>
            <div>
                <v-breadcrumbs  class="pt-7"> 
                    <v-breadcrumbs-item v-for="(item, index) in breadcrumbItems" :key="index" :class="{'custom-pointer': !item.disabled}" @click="navigateTo(item)"
                        :disabled="item.disabled" >
                        {{ item.title }} <v-icon class="ml-1" icon="mdi-chevron-right"></v-icon>
                    </v-breadcrumbs-item>
                </v-breadcrumbs>
            </div>
            <v-spacer></v-spacer>
            <!-- <v-btn color="primary">Approve</v-btn> -->
        </template>
        <template #default>
            <v-form ref="form"> <!--@submit.prevent="submitWorkOrder" ref="form" -->
            <v-container class="mt-10">

                <RequestDetails :service_id="parseInt(service_id)" @set-status="getStatus" />

                <RequestedEquipments :service_id="parseInt(service_id)" :status="status" :editSerial="false" />

                <ApproverHistoryLog :service_id="parseInt(service_id)" :status="status" />
            </v-container>
        </v-form>
        </template>
    </LayoutSinglePage>
</template>
<script setup>
import { ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import RequestedEquipments from '@/components/Approver/EH/RequestedEquipments.vue'
import ApproverHistoryLog from '@/components/Approver/EH/ApproverHistoryLog.vue'
import RequestDetails from '@/components/Approver/EH/RequestDetails.vue'

import LayoutSinglePage from '@/components/layout/MainLayout/LayoutSinglePage.vue';
import { useDisplay } from 'vuetify'

const router = useRouter()

const breadcrumbItems = [
    { title: 'Back', disabled: false, href: '/equipment-handling' },
    { title: 'Equipment Handling', disabled: true, href: '' },
    { title: 'Work Order', disabled: true, href: '' },
]
const navigateTo = (item) => {
    if (!item.disabled && item.href) {
        router.push(item.href);
    }
};


const getInternalStatus = ref(null)
const getInternalDateTimeUpdated = ref(null)
const CurrentlyDelegatedTo = ref(null)


/** data declarations */
// const uri = BASE_URL
// const router = useRouter()
const route = useRoute()
// const user = user_data()
// user.getUserData

const id = route.params.id
const service_id = ref(id)
const serialNumber = ref([])
const status = ref(0)

const getSerialNumber = (serial) => {
    serialNumber.value = Array.from(serial)
    submmitApproveStatus.value = serialNumber.value.every(data => data.serial && data.serial.trim() !== '')
}
const getStatus = (data) => {
    status.value = data
}

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

.vCheckbox {
    height: 40px !important;
}
.v-label{
    color : #222;
}
</style>