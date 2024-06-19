<template>
    <div class="bg-grey-lighten-3 rounded p-3 border-dashed border-sm border-primary">
        <h5 class="mb-2" style="font-weight: 700;color: #191970;">Service Department - Internal Servicing Procedures
        </h5>
        <!-- Approve Button -->

        <v-dialog v-model="dialogInternalRequest" max-width="500" persistent>
            <template v-slot:activator="{ props: activatorProps }">
                <v-btn type="button" elevation="0" size="small" v-bind="activatorProps" :disabled="btnDisable"
                    color="primary" class="text-none btnSubmit mt-3 rounded-0"><v-icon class="mr-2">mdi-check</v-icon>
                    Start</v-btn>
            </template>

            <v-card>
                <v-form @submit.prevent="delegateInternalServicing" ref="form">
                    <v-col cols="12">
                        <h5 class="mt-3" style="font-weight: 700;color: #191970;">Internal Servicing Procedures</h5>
                        <v-divider></v-divider>
                        <v-radio-group v-model="type_of_activity" :rules="rule_type_of_activity" :readonly="disable_type_of_activity">
                            <v-radio color="primary" label="For Corrective" value="6"></v-radio>
                            <v-radio color="primary" label="For Refurbishment" value="7"></v-radio>
                            <v-radio color="primary" label="For Quality Control" value="8"></v-radio>
                            <v-radio color="primary" label="Training Purposes" value="9"></v-radio>
                            <v-radio color="primary" label="For Disposal" value="10"></v-radio>
                            <v-radio color="primary" label="Other" value="11"></v-radio>
                        </v-radio-group>
                        <v-text-field v-if="showOther" :rules="rule_other" v-model="other" density="compact" variant="outlined" label="Other Request"></v-text-field>

                        <v-combobox color="primary" class="mt-5" v-model="Engineers" :rules="rule_engineers" clearable
                            label="Delegate to" placeholder="Delegate to" density="compact" :items="engineersData"
                            variant="outlined" itemValue="value" itemTitle="key"></v-combobox>
                    </v-col>

                    <v-divider class="mb-5"></v-divider>
                    <!-- <template v-slot:actions> -->
                    <v-row justify="end" class="mb-3">
                        <v-btn elevation="2" @click="dialogInternalRequest = false" background-color="red" size="small"
                            class="text-none mr-2"><v-icon>mdi-close</v-icon>
                            Cancel</v-btn>
                        <v-btn type="submit" size="small" :loading="btnLoading" :disabled="btnDisable" color="#191970"
                            class="text-none bg-primary mr-5"><v-icon class="mr-2">mdi-check</v-icon>
                            Delegate</v-btn>
                    </v-row>
                    <!-- </template> -->
                </v-form>
            </v-card>
        </v-dialog>
        <v-snackbar color="success" v-model="infoSuccess" location="right bottom" :timeout="5000">
            <v-icon>mdi-alert-circle-outline</v-icon> Successfully delegated.
        </v-snackbar>
        <v-snackbar color="error" v-model="exist_service_id" location="right bottom" :timeout="5000">
            <v-icon>mdi-alert-circle-outline</v-icon> Request currently delegated.
        </v-snackbar>
    </div>
</template>



<script setup>
// import { apiRequest } from '@/api';
import { ref, onMounted, watch } from 'vue'
import { user_data } from '@/stores/auth/userData'

const dialogInternalRequest = ref(false)
const type_of_activity = ref(null)
const Engineers = ref('')
const engineersData = ref([])
const form = ref(false)
const btnLoading = ref(false)
const btnDisable = ref(false)
const infoSuccess = ref(false)
const exist_service_id = ref(false)
const showOther = ref(false)
const other = ref('')
const disable_type_of_activity = ref(false)

const auth = user_data()
auth.getUserData
const apiRequest = auth.apiRequest()

const props = defineProps({
    service_id: {
        type: Number
    },
    getSelectedRequest : {
        type : Number,
    }
})

watch(type_of_activity, (val, oldVal) => {
    showOther.value = val === '11' // Compare val to the string '11'
    if(!showOther.value){
        other.value = ''
    }
})


/** Rules */
const rule_type_of_activity = ref([
    v => !!v || 'Select an option from the list'
])
const rule_other = ref([
    v => !!v || 'Required field'
])
const rule_engineers = ref([
    v => !!v || 'Required field',
    k => k.key !== undefined ? true : 'Select an option from the list',
    r => r.value !== undefined ? true : 'Select an option from the list'
])


/**
 * Submit || Delegate Internal Servicing
 */
const delegateInternalServicing = async () => {
    btnLoading.value = true
    const { valid } = await form.value.validate()
    if (!valid) {
        btnLoading.value = false
        return
    }
    try {
        const response = await apiRequest.post('internal-process', {
            received_by: auth.user.id,
            service_id: props.service_id,
            type_of_activity: type_of_activity.value,
            other : other.value,
            delegated_to: Engineers.value.value
        })
        if (response.data && response.data.success) {
            infoSuccess.value = true
        }else if(response.data && response.data.exist_service_id){
            exist_service_id.value = true
        }
    } catch (error) {
        alert(error)
    }
    finally{
        btnLoading.value = false
        dialogInternalRequest.value = false
    }
}

    /**
     * Get Engineers Data - Use for Delegation
     */
    const getEngineeresData = async () => {
        try {
            const response = await apiRequest.get('get-engineers-data')
            if (response.data && response.data.engineers) {
                const engineersValue = response.data.engineers.map(data => {
                    return {
                        key: data.first_name + ' ' + data.last_name,
                        value: data.user_id
                    }
                })
                engineersData.value = engineersValue
            }
        } catch (error) {
            alert(error)
        }
    }


// Type of Activity Disable
const disable_typeOfActivity = () => {
    if(props.getSelectedRequest === 0){
        disable_type_of_activity.value = false
    }else{
        type_of_activity.value = String(props.getSelectedRequest)
        disable_type_of_activity.value = true
    }
}




onMounted(() => {
    getEngineeresData()
    disable_typeOfActivity()
})
</script>