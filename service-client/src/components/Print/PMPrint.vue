<template>
  <v-dialog v-model="dialog" transition="dialog-bottom-transition" fullscreen>
    <template v-slot:activator="{ props: activatorProps }">
      <v-btn prepend-icon="mdi-printer" class="text-none" variant="plain" text="Print Preview"
        v-bind="activatorProps"></v-btn>
    </template>

    <v-card>
      <v-toolbar dense style="position: fixed;z-index:999;">
        <v-btn icon="mdi-close" @click="dialog = false"></v-btn>

        <!-- <p><v-icon>mdi-printer</v-icon> Print Preview</p> -->

        <v-spacer></v-spacer>
        <v-btn v-print="'#printThisA4'" prepend-icon="mdi-printer" class="text-none" variant="plain"
          text="Print"></v-btn>
        <!-- <v-toolbar-items>
            <v-btn text="Save" variant="text" @click="dialog = false"></v-btn>
          </v-toolbar-items> -->
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
                            <span class="printText">Date In:</span> {{ pub_var.formatDateNoTime(pm_data.created_at) }}
                          </v-col>
                          <v-col cols="5">
                            <span class="printText">Report No:</span> {{ 'SR-' + pm_data.id }}
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
              <td class="w-50"><span class="printText">Institution:</span> {{ pm_data.institution_name }}</td>
              <td><span class="printText">Equipment:</span> {{ pm_data.service_equipment.item_code }}</td>
              <td><span class="printText">ID/IR No:</span></td>
            </tr>
            <tr>
              <td><span class="printText">Address: </span>{{ pm_data.address }}</td>
              <td><span class="printText">Serial Number: </span>{{ pm_data.serial }}</td>
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
                <input disabled type="checkbox" /> Equipment Handling<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Pack/Move/Transfer<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Installation<br />
                &nbsp;&nbsp;<input disabled type="checkbox" /> Pull-out <br />
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
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
              <td></td>
            </tr>
            <tr>
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
              <td></td>
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
                      :checked="pm_data.status_after_service === m_var.StatusAfterService.operational" /> <label
                      class="ml-2">Operational</label>
                  </v-col>
                  <v-col cols="4" class="d-flex align-items-center">
                    <input disabled type="checkbox"
                      :checked="pm_data.status_after_service === m_var.StatusAfterService.further_monitoring" /> <label
                      class="ml-2">For Further Monitoring</label>
                  </v-col>
                  <v-col cols="4" class="d-flex align-items-center">
                    <input disabled type="checkbox"
                      :checked="pm_data.status_after_service === m_var.StatusAfterService.non_operational" /> <label
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
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
              <td></td>
            </tr>
            <tr>
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
                  <td></td>
                </tr>
                <tr>
                  <td>Designation</td>
                  <td></td>
                </tr>
                <tr>
                  <td>Signature</td>
                  <td></td>
                </tr>
                <tr>
                  <td>Remarks</td>
                  <td></td>
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
                  <td class="w-50"></td>
                  <td>Time In:</td>
                </tr>
                <tr>
                  <td>Travel Time</td>
                  <td></td>
                  <td>Time Out:</td>
                </tr>
                <tr>
                  <td>Name</td>
                  <td colspan="2"></td>
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
import { ref, inject, watch, computed } from 'vue';
import * as pub_var from '@/global/global'
import * as m_var from '@/global/maintenance'
import header_logo from '@/assets/logo_header.jpg'
import { useRoute } from 'vue-router';
const route = useRoute()

const pm_data = inject('pm_data')
const pm_data_sched = ref(null)


// Watch for changes to pm_data.schedule
watch(pm_data, (newValue) => {
  if (newValue) {

    pm_data_sched.value = newValue.service_equipment.frequency
  }
},
  { immediate: true } // Ensures the watcher runs immediately and checks the initial value
)

const pm_frequency = (frequency) => {
  const work_type_pm = route.params.work_type
  // const work_type_cm = route.params.work_type === 'CM'
  if (work_type_pm === 'PM') {
    if (frequency === pm_data_sched.value) {
      return true;
    } else {
      return false;
    }
  }
  return false
}


const pm_checkbox = computed(() => route.params.work_type === 'PM')
const cm_checkbox = computed(() => route.params.work_type === 'CM')

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