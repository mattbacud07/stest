<template>
    <v-app dense>
        <v-app-bar>
            <v-app-bar-nav-icon  color="primary" @click="drawer = !drawer"></v-app-bar-nav-icon>

            <v-app-bar-title>Service <span style="color:#191970;font-weight:700;">webApp</span></v-app-bar-title>
            <v-spacer></v-spacer>
            <topBarUserProfile />
        </v-app-bar>

        <v-navigation-drawer v-model="drawer">
            <!--  -->
            <BaseSidebar />
        </v-navigation-drawer>

        <v-main>
            <!-- Fixed toolbar -->
            <v-toolbar class="fixed-toolbar border-b-sm" dense color="white" :style="{width : width < 1280 || drawer === false ? '100%' : 'calc(100% - 256px)'}">
                
                
                <v-spacer></v-spacer>

                <!-- Column Chooser  -->
                <v-menu v-model="columnChooser" :close-on-content-click="false" location="bottom">
                    <template v-slot:activator="{ props }">
                        <v-btn color="primary" variant="plain" icon v-bind="props" class="text-none"> <v-icon>mdi-chevron-down</v-icon> </v-btn>
                    </template>

                    <v-card min-width="auto" class="pr-3">
                        <v-checkbox class="columnChooserCheckBox" :label="col.title" v-for="col in column" :key="col.field" :model-value="!col.hide" @change="col.hide = !$event.target.checked"></v-checkbox>
                    </v-card>
                </v-menu>
                
                <!-- Search Master Data -->
                <div class="col-md-2"><v-text-field v-model="searchInput" density="compact"  class="ml-3" variant="plain" prepend-inner-icon="mdi-magnify" color="primary" placeholder="Search data" ></v-text-field></div>
               

                
                <v-btn @click="handleRefresh" icon color="primary" size="small" variant="text" class="mr-2 ml-2"><v-icon>mdi-refresh</v-icon></v-btn>
               
                <v-btn @click="handleView" prepend-icon="mdi-file-eye" variant="tonal" :disabled="btnDisable" color="primary" class="text-none">
                    View
                </v-btn>
                <v-btn color="primary" variant="tonal" prepend-icon="mdi-pencil" class="text-none mr-2 ml-2" :disabled="btnDisable">
                    Edit
                </v-btn>
                <v-btn @click="handleCreate" v-if="enableCreate" color="primary" variant="flat" prepend-icon="mdi-plus" class="text-none"> <!--- v-if="enableCreate"-->
                    Create
                </v-btn>
            </v-toolbar>

            <!-- Main content slot -->
            <div class="mainWrapper">
                <!-- Dynamic Filtering -->
                <slot name="filter"></slot>

                <slot :searchText='searchInput'></slot>
            </div>
        </v-main>

    </v-app>
</template>

<script setup>
import { ref, inject, provide, watch, onMounted } from 'vue'
import BaseSidebar from '../Sidebars/BaseSidebar.vue';
import topBarUserProfile from './LayoutParts/topBarUserProfile.vue'
import { useRouter, useRoute } from 'vue-router';
import {useDisplay} from 'vuetify'

const router = useRouter()
const route = useRoute()
const drawer = ref(null)
const {width} = useDisplay()
const columnChooser = ref(null)

const details = inject('data') //this is from equipmenthandling.vue table
const refresh = inject('refresh', null)
const column = inject('column', null)
const btnDisable = ref(details.btnDisable ?? true)
const enableCreate = ref(false)
const searchInput = ref('')
const service_id = 'service_id' in details ? details.service_id : null 
// const status = 'status' in details && details.status !== null ? details.status : null 
// console.log(service_id.value)
const enableCreateFunction = () => {
    if(route.name === 'EquipmentHandling'){
        return enableCreate.value = true
    }
}

const handleRefresh = () =>{
    if (typeof refresh === 'function') {
        refresh()
    }
}


const handleView = () => {
    const params = {
        id : details.selectedId.value
    }
    if(service_id !== null){
        params.service_id = service_id.value;
    }
    if(details.status.value !== null){
        params.status = details.status.value;
    }
   
    console.log(params)
    
    router.push({ name: details.routeView.value, params });
}


const handleCreate = () =>{
    router.push({name : details.addView.value})
}


onMounted(()=>{
    enableCreateFunction()
})
</script>


<style scoped>
.fixed-toolbar {
    position: fixed;
    z-index: 100;
    right: 0;
}
.mainWrapper {
  /* display: flex; */
  justify-content: center;
  align-items: center;
  padding-top: 60px; /* Adjust based on your toolbar height */
  margin: 20px!important;
}
.columnChooserCheckBox{
    height: 40px!important;
}
</style>