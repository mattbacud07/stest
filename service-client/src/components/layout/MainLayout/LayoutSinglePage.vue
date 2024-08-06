<template>
    <v-app dense>
        <v-app-bar>
            <v-app-bar-nav-icon @click="drawer = !drawer"></v-app-bar-nav-icon>

            <v-app-bar-title>Service <span style="color:#191970;font-weight:700;">webApp</span></v-app-bar-title>
            <v-spacer></v-spacer>
            <topBarUserProfile />
        </v-app-bar>

        <v-navigation-drawer v-model="drawer">
            <BaseSidebar />
        </v-navigation-drawer>

        <v-main>
            <v-toolbar class="fixed-toolbar border-b-sm pr-3" dense color="primary" :style="{width : width < 1280 || drawer === false ? '100%' : 'calc(100% - 256px)'}">
                <slot name="topBarFixed"></slot>
            </v-toolbar>


            <!-- Main content slot -->
            <div class="mainWrapper" :style="{ padding : width < 2560 && width > 980 ? '5% 10%' : '7% 1%'}">
                <slot></slot>
            </div>
        </v-main>

    </v-app>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import {useDisplay} from 'vuetify'
import BaseSidebar from '../Sidebars/BaseSidebar.vue';
import topBarUserProfile from './LayoutParts/topBarUserProfile.vue';

const drawer = ref(null)

const { width } = useDisplay()
</script>


<style scoped>
.fixed-toolbar {
    position: fixed;
    z-index: 100;
    right: 0;
    display: flex;
    align-items: center;
    height: 60px !important;
    width: calc(100% - 256px);
}


/* .main-wrapper {
    padding-top: 10px;
    /* Adjust based on your toolbar height */
.mainWrapper {
    /* display: flex; */
    justify-content: center;
    align-items: center;
    /* padding-top: 60px; */
    padding: 5% 15%;
    /* Adjust based on your toolbar height */
}
</style>