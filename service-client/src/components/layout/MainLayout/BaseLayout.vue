<template>
    <v-app dense>
        <v-app-bar>
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

            <v-app-bar-title :style="{ display: width <= 426  ? 'none' : '' }">Service <span style="color:#191970;font-weight:700;">webApp</span></v-app-bar-title>
            <p :style="{ display: width <= 426  ? 'block' : 'none' }">service<span style="color:#191970;font-weight:700;">App</span></p>
            
        <v-spacer></v-spacer><topBarUserProfile />
        </v-app-bar>

        <v-navigation-drawer v-model="drawer">
            <BaseSidebar />
        </v-navigation-drawer>

        <v-main>
            <!-- Main content slot -->
            <div class="mainWrapper">
                <slot></slot>
            </div>
        </v-main>

    </v-app>
</template>

<script setup>
import { ref } from 'vue'
import BaseSidebar from '../Sidebars/BaseSidebar.vue';
import topBarUserProfile from './LayoutParts/topBarUserProfile.vue';

import { useDisplay } from 'vuetify'
const { width } = useDisplay()

const drawer = ref(null)
</script>


<style scoped>
/* .fixed-toolbar {
    position: fixed;
    z-index: 100;
    right: 0;
    display: flex;
    align-items: center;
    height: 60px !important;
    width: calc(100% - 256px);
} */


/* .main-wrapper {
    padding-top: 10px;
    /* Adjust based on your toolbar height */
.mainWrapper {
    /* display: flex; */
    justify-content: center;
    align-items: center;
    padding-top: 10px;
    /* Adjust based on your toolbar height */
}
</style>