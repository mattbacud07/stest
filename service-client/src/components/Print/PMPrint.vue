<template>
    <v-container fluid class="print-wrapper">
      <!-- Logo and Title -->
      <v-row class="mb-4">
        <v-col class="text-center">
          <v-img
            src="/assets/docs-logo.cfd08dfe.png"
            alt="logo"
            max-width="300"
            class="mb-4"
          ></v-img>
          <h3 class="text-uppercase text-underline">
            Sample Print Preview {{ id }} {{ work_type }}
          </h3>
          <v-btn @click="printNow">Print Now</v-btn>
        </v-col>
      </v-row>
  
      <v-row>
        {{ pm_data }}
      </v-row>
      <!-- Details Section -->
      <v-row>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Name</v-card-title>
            <v-card-subtitle>{{pm_data.user}}</v-card-subtitle>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Date of Liquidation</v-card-title>
            <v-card-subtitle>Jul 17, 2024</v-card-subtitle>
          </v-card>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Position</v-card-title>
            <v-card-subtitle>PSR</v-card-subtitle>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Amount of CA</v-card-title>
            <v-card-subtitle>32,000.00</v-card-subtitle>
          </v-card>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Department</v-card-title>
            <v-card-subtitle>Sales & Marketing Department</v-card-subtitle>
          </v-card>
        </v-col>
        <v-col cols="12" md="6">
          <v-card>
            <v-card-title>Date of CA</v-card-title>
            <v-card-subtitle>Mar 18, 2024</v-card-subtitle>
          </v-card>
        </v-col>
      </v-row>
  
      <!-- Purpose of Cash Advance Request -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title>Purpose of Cash Advance Request</v-card-title>
            <v-card-text>
              FOC ITEM FROM SPMC - YUMIZEN H2500 BIDDING.
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Breakdown Expenses Table -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title>Breakdown Expenses</v-card-title>
            <table>
              <thead>
                <tr>
                  <th>Date</th>
                  <th>Description Of Expenses</th>
                  <th>Accommodation & Lodging</th>
                  <th>Transportation</th>
                  <th>Others</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>Jul 17, 2024</td>
                  <td>FUJIDENZO 12 CU FT HD INVERTER CHILLER</td>
                  <td class="text-right">0</td>
                  <td class="text-right">0</td>
                  <td class="text-right">30,950.00</td>
                  <td class="text-right">30,950.00</td>
                </tr>
                <tr>
                  <td>Jul 17, 2024</td>
                  <td>DIAL THERMOMETER DELTACHECK</td>
                  <td class="text-right">0</td>
                  <td class="text-right">0</td>
                  <td class="text-right">365.00</td>
                  <td class="text-right">365.00</td>
                </tr>
                <!-- Additional rows can be added here -->
                <tr>
                  <td colspan="5" class="text-right">Total</td>
                  <td class="text-right">31,315.00</td>
                </tr>
              </tbody>
            </table>
          </v-card>
        </v-col>
      </v-row>
      
      <!-- Liquidation Options -->
      <v-row>
        <v-col cols="12">
          <v-card>
            <v-card-title>I would like to liquidate through:</v-card-title>
            <v-checkbox label="Cash to Accounting" />
            <v-checkbox label="Credit to my BPI Account" />
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>
  
  <script setup>
  import { onMounted, ref } from 'vue';
  import { useRoute } from 'vue-router';
  import {user_data} from '@/stores/auth/userData'

  const user = user_data()
  const apiRequest = user.apiRequest()

  const route = useRoute()
  const id = route.params.id
  const work_type = route.params.work_type

  const pm_data = ref([])
  const getPMDetails = async () => {
    try {
        const response = await apiRequest.get('get_pm_record_details', {
            params: { id: id }
        });
        if (response.data && response.data.details) {
            pm_data.value = response.data.details[0]
            // console.log(pm_data.value.eh.ssu)
        } else toast.error('Something went wrong')
    } catch (error) {
        console.log(error)
    }
};




/* Print nOw*/
const printNow = () => {
    window.print()
}

onMounted(() => {
    getPMDetails()
})
  </script>
  
  <style scoped>
  .text-uppercase {
    text-transform: uppercase;
  }
  .text-underline {
    text-decoration: underline;
  }
  .mb-4 {
    margin-bottom: 16px;
  }
  .mt-xs {
    margin-top: 8px;
  }
  .font-weight-bold {
    font-weight: bold;
  }
  </style>
  