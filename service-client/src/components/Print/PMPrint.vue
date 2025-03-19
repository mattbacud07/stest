<template>

  <v-btn prepend-icon="mdi-printer" class="text-none" variant="plain" text="Service Report"
    @click="dialog = true"></v-btn>
  <v-dialog v-model="dialog" transition="dialog-bottom-transition" fullscreen>
    <v-card>
      <v-toolbar dense style="position: fixed;z-index:999;">
        <v-btn icon="mdi-close" @click="dialog = false"></v-btn>

        <v-spacer></v-spacer>
        <v-btn v-print="'#printThisA4'" prepend-icon="mdi-printer" class="text-none" variant="plain"
          text="Print"></v-btn>
      </v-toolbar>


      <v-container class="mt-15" id="printThisA4">
        <!-- Header -->
        <table class="table mt-5" style="border: 0!important;">
          <tbody>
            <tr>
              <td class="w-50">
                <!-- <img :src="header_logo" alt="header_logo" class="img fluid"> -->
                <v-img :src="header_logo" width="90%" class="mb-1"></v-img>
              </td>
              <td class="w-50">
                <h5 class="bg-primary text-center mb-2">SERVICE REPORT</h5>
                <table class="table table-bordered">
                  <tbody>
                    <tr>
                      <td>
                        <v-row>
                          <v-col cols="7">
                            <span class="printText">Date In:</span> {{ pub_var.formatDateNoTime(sr?.created_at) }}
                          </v-col>
                          <v-col cols="5">
                            <span class="printText">Report No:</span> {{ 'SR-' + sr?.id }}
                          </v-col>
                        </v-row>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>
          </tbody>
        </table>


        <!-- CUSTOMER DETAILS Table -->
        <table class="table table-bordered">
          <tbody>
            <tr>
              <td colspan="3" style="background: #191970;" class="bg-primary text-center">
                <h5>CUSTOMER DETAILS</h5>
              </td>
            </tr>
            <tr>
              <td class="w-50"><span class="printText">Institution:</span> {{ is ? data?.equipment_handling?.institution?.name : data?.institution?.name }}</td>
              <td><span class="printText">Equipment:</span> {{ equipment }}</td>
              <td><span class="printText">ID/IR No:</span></td>
            </tr>
            <tr>
              <td><span class="printText">Address: </span>{{ is ? data?.equipment_handling?.institution?.address : data?.institution?.address }}</td>
              <td><span class="printText">Serial Number: </span>{{ serial }}</td>
              <td></td>
            </tr>
          </tbody>
        </table>



        <!-- Job Type Section -->
        <table class="table mt-3" style="border: none!important;">
          <tbody>
            <tr>
              <td colspan="5" style="background: #191970;" class="bg-primary text-center">
                <h5>JOB TYPE</h5>
              </td>
            </tr>
            <tr style="border-color: transparent!important;">
              <td>
                <input disabled type="checkbox" v-model="pm_checkbox" /> Preventive Maintenance<br />
                &nbsp;&nbsp;<input disabled type="checkbox" :checked="pm_frequency(m_var.monthly)" /> Monthly
                PM<br />
                &nbsp;&nbsp;<input disabled type="checkbox" :checked="pm_frequency(m_var.quarterly)" /> Quarterly
                PM<br />
                &nbsp;&nbsp;<input disabled type="checkbox" :checked="pm_frequency(m_var.semiAnnually)" /> Six-Month
                PM<br />
                &nbsp;&nbsp;<input disabled type="checkbox" :checked="pm_frequency(m_var.annually)" /> Yearly PM
              </td>
              <td>
                <input disabled v-model="cm_checkbox" type="checkbox" /> Corrective Maintenance<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Repair 1 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <input disabled
                  type="checkbox" /> 1a <input disabled type="checkbox" /> 1b <br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Repair 2<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Repair 3<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Repair 4
              </td>
              <td>
                <input disabled type="checkbox" :checked="eh || pullout || is" /> Equipment Handling<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Pack/Move/Transfer<br />
                &nbsp;&nbsp;<input v-model="eh_is" disabled type="checkbox" /> Installation<br />
                &nbsp;&nbsp;<input v-model="pullout" disabled type="checkbox" /> Pull-out <br />
                <b>MODE: <span
                    style="border-bottom: 2px solid #222;width: 12px;color: transparent!important;">_________</span></b>
              </td>
              <td>
                <input disabled type="checkbox" /> Result Validation<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Precision/Parallel<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Evaluation<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Recertification<br />
                <b>OTHERS: <span
                    style="border-bottom: 2px solid #222;width: 12px;color: transparent!important;">_________</span></b>
              </td>
              <td>
                <input disabled type="checkbox" /> Updates/Training<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Equipment Updates<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> End User Training<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Monitoring Visit<br />
                <b>OTHERS: <span
                    style="border-bottom: 2px solid #222;width: 12px;color: transparent!important;">_________</span></b>
              </td>
            </tr>
          </tbody>
        </table>




        <!-- Customer Complaint & Cause of Problem Section -->
        <table class="table table-bordered mt-3">
          <tbody>
            <tr class="bg-grey-lighten-3">
              <td class="text-center w-50">Customer Complaint</td>
              <td class="text-center w-50">Cause of Problem</td>
            </tr>
            <tr>
              <td>{{ sr?.complaint }}</td>
              <td> {{ sr?.problem }}</td>
            </tr>
            <tr>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>

        <!-- Actions Taken Section -->
        <table class="table table-bordered mt-2">
          <tbody>
            <tr>
              <td class="text-center bg-grey-lighten-3">Actions Taken</td>
            </tr>
            <tr v-for="action in sr?.actions_taken">
              <td>{{ action?.action }}</td>
            </tr>
            <tr v-for="n in Math.max(7 - (sr?.actions_taken?.length || 0), 0)">
              <td></td>
            </tr>
          </tbody>
        </table>

        <!-- Remarks & Recommendations Section -->
        <table class="table table-bordered mt-3">
          <tbody>
            <tr>
              <td class="text-center bg-grey-lighten-3">Remarks & Recommendations</td>
            </tr>
            <tr>
              <td>{{ sr?.sr_remarks }}</td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
          </tbody>
        </table>

        <!-- Status After Service Section -->
        <table class="table table-bordered mt-2">
          <tbody>
            <tr>
              <td class="text-center">Status after this service</td>
              <td class="text-center">
                <v-row>
                  <v-col cols="4" class="d-flex align-items-center">
                    <input disabled type="checkbox"
                      :checked="sr?.status_after_service === m_var.StatusAfterService.operational" /> <label
                      class="ml-2">Operational</label>
                  </v-col>
                  <v-col cols="4" class="d-flex align-items-center">
                    <input disabled type="checkbox"
                      :checked="sr?.status_after_service === m_var.StatusAfterService.further_monitoring" /> <label
                      class="ml-2">For Further Monitoring</label>
                  </v-col>
                  <v-col cols="4" class="d-flex align-items-center">
                    <input disabled type="checkbox"
                      :checked="sr?.status_after_service === m_var.StatusAfterService.non_operational" /> <label
                      class="ml-2">Non-Operational</label>
                  </v-col>
                </v-row>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Reagents / Spare Parts Used Section -->
        <table class="table table-bordered mt-2">
          <thead>
            <tr style="background: #191970; color: white;">
              <th colspan="6" class="text-center text-white">REAGENTS / SPARE PARTS USED</th>
            </tr>
          </thead>
          <tbody>
            <tr class="text-center">
              <td>Reference No.</td>
              <td class="w-50">Description</td>
              <td>Qty</td>
              <td>DR#</td>
              <td>SI#</td>
              <td>Remarks</td>
            </tr>
            <tr v-for="part in sr?.spareparts">
              <td>{{ part?.equipment?.id }}</td>
              <td>{{ part?.equipment?.description }}</td>
              <td>{{ part?.qty }}</td>
              <td>{{ part?.dr }}</td>
              <td>{{ part?.si }}</td>
              <td>{{ part?.remarks }}</td>
            </tr>
            <tr v-for="n in Math.max(6 - (sr?.spareparts?.length || 0), 0)">
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
          </tbody>
        </table>


        <!-- Customer and Service Provider Details -->
        <v-row class="mt-1">
          <v-col cols="6">
            <table class="table table-bordered">
              <thead>
                <tr style="background: #191970;">
                  <th colspan="2" class="text-center text-white">CUSTOMER DETAILS</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="w-25">Name</td>
                  <td>{{ sr?.customer?.name }}</td>
                </tr>
                <tr>
                  <td>Designation</td>
                  <td>{{ sr?.customer?.designation }}</td>
                </tr>
                <tr>
                  <td>Signature</td>
                  <td><v-img :src="'/' + sr?.customer?.signature" alt="Signature" max-width="80" class="rounded-lg" />
                  </td>
                </tr>
                <tr>
                  <td>Remarks</td>
                  <td>{{ sr?.customer?.remarks }}</td>
                </tr>
              </tbody>
            </table>

          </v-col>
          <v-col cols="6">
            <table class="table table-bordered">
              <thead>
                <tr style="background: #191970;">
                  <th colspan="3" class="text-center text-white">SERVICE PROVIDER</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="w-25">Date Out</td>
                  <td class="w-50">{{ inTransit }}</td>
                  <td>Time In: {{ started }}</td>
                </tr>
                <tr>
                  <td>Travel Time</td>
                  <td>{{ sr?.travel_duration }}</td>
                  <td>Time Out: {{ ended }}</td>
                </tr>
                <tr>
                  <td>Name</td>
                  <td colspan="2">{{ sr?.first_name }} {{ sr?.last_name }}</td>
                </tr>
                <tr>
                  <td>Signature</td>
                  <td colspan="2"></td>
                </tr>
              </tbody>
            </table>
          </v-col>
        </v-row>

        <v-row class="mt-4">
          <v-col cols="9">
            <table class="table" style="border: none!important;font-size: 10px;">
              <tbody>
                <tr>
                  <td style="border: none!important">6023 Sacred Heart cor. Kamagong Sts., San Antonio Makati City,
                    Philippines 1203</td>
                </tr>
                <tr>
                  <td style="border: none!important">Tel. No. +632 8824 - 7731 / +632 8824 - 4551 loc 201 Fax No. +632
                    8896-9382 / Facebook Page:
                    SBSI.Philippines</td>
                </tr>
                <tr>
                  <td style="border: none!important">Service Mobile: +639176372800 / +69338514979 Email Address:
                    customercare@sbsi.com.ph</td>
                </tr>
              </tbody>
            </table>
          </v-col>
          <v-col cols="3">
            <table class="table">
              <tbody>
                <tr>
                  <td>FM-SER-05</td>
                </tr>
                <tr>
                  <td>Revision No.</td>
                </tr>
                <tr>
                  <td>Effective Date:</td>
                </tr>
              </tbody>
            </table>
          </v-col>
        </v-row>
      </v-container>

    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, inject, watch, computed, toRefs } from 'vue';
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance'
import header_logo from '@/assets/logo_header.jpg'
import { useRoute } from 'vue-router';
import { A_CM, A_EH, A_IS, A_PM, A_PR } from '@/global/modules';
import { pull } from 'lodash';
const route = useRoute()



const props = defineProps({
  data: {
    type: Object,
    default: () => ({})
  }
})

const { data } = toRefs(props)

const sr = computed(() => data.value?.task_delegation)

const pm_frequency = (frequency) => {
  return data.value?.equipment?.frequency === frequency && sr.value?.type === A_PM
}

const pm_checkbox = computed(() => sr.value?.type === A_PM)
const cm_checkbox = computed(() => sr.value?.type === A_CM)
const eh = computed(() => sr.value?.type === A_EH)
const eh_is = computed(() => sr.value?.type === A_EH || sr.value?.type === A_IS)
const pullout = computed(() => sr.value?.type === A_PR)
const is = computed(() => sr.value?.type === A_IS)


const started = computed(() => pub_var.formatDate(sr.value?.task_activity?.find(v => v.status === 'Started')?.acted_at))
const inTransit = computed(() => pub_var.formatDate(sr.value?.task_activity?.find(v => v.status === 'In Transit')?.acted_at))
const ended = computed(() => pub_var.formatDate(sr.value?.task_activity?.find(v => v.status === 'Ended')?.acted_at))


const equipment = computed(() => {
  if (pm_checkbox.value || cm_checkbox.value) {
    return data.value?.equipment?.equipment
  }else if(eh.value || pullout.value){
    return data.value?.equipments?.map(e => e.master_data?.equipment).join(', ')
  } 
  else {
    return data.value?.equipment_handling?.equipments?.map(e => e.master_data?.equipment).join(', ')
  }
})
const serial = computed(() => {
  if (pm_checkbox.value || cm_checkbox.value) {
    return data.value?.equipment?.serial
  }else if(eh.value || pullout.value){
    return data.value?.equipments?.map(e => e.master_data?.serial).join(', ')
  }
  else {
    return data.value?.equipment_handling?.equipments?.map(e => e.master_data?.serial).join(', ')

  }
})

const dialog = ref(false)
</script>












<style scoped>
.text-center {
  text-align: center;
}

.table> :not(caption)>*>* {
  padding: 0 5px !important;
}

.table {
  border: 1px solid #111 !important;
}

.table tr td {
  color: #222;
}

table tbody tr td:empty::before {
  content: '.' !important;
  color: transparent !important;
}

table tbody tr td span:empty::before {
  content: '' !important;
}

.printText {
  font-weight: 600;
}



/** Checkboxexs */
/* Remove the default appearance of the checkbox */
input[type="checkbox"] {
  appearance: none;
  -webkit-appearance: none;
  /* For Safari */
  -moz-appearance: none;
  /* For Firefox */
  width: 10px;
  height: 10px;
  border-radius: 1px;
  border: 1px solid #333;
  background-color: #00000000;
  position: relative;
}

/* Checkmark style */
input[type="checkbox"]:checked {
  background-color: #191970;
}
</style>