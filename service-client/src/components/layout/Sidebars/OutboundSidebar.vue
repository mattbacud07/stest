<template>
    <nav class="sidebar">
        <div class="sidebar-header" @click="toggleSidebar">
            <div class="sidebar-toggler not-active">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <a href="#" class="sidebar-brand">
                WEB<span>App</span>
            </a>
        </div>
        <div class="sidebar-body" v-on:mouseover="hoverNav" v-on:mouseleave="hoverNavLeave">
            <ul class="nav">
                <router-link to="/dashboard">
                    <li class="nav-item" style="padding: 5px 0;">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-account-card</v-icon>
                            <span class="link-title">Switch Account<br><span
                                    style="color: #999;font-weight: 100;font-size: .8em;">as Requestor</span></span>
                        </a>
                    </li>
                </router-link>
                <router-link to="/outbound-dashboard" class="router-link-active">
                    <li class="nav-item">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-shield-home-outline</v-icon>
                            <span class="link-title">Dashboard</span>
                        </a>
                    </li>
                </router-link>
                <router-link to="/outbound-equipment-handling" class="router-link-active">
                    <li class="nav-item">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-file-document-edit</v-icon>
                            <span class="link-title">Equipment Handling</span>
                        </a>
                    </li>
                </router-link>
            </ul>
        </div>
    </nav>

    <v-icon icon="md:home" aria-hidden="false"></v-icon>
</template>



<script setup>
import { user_data } from '@/stores/auth/userData'
import * as pub_const from '@/global/global.js';

const user = user_data()
user.getrUserData

const toggleSidebar = () => {
    $('.sidebar-header .sidebar-toggler').toggleClass('active not-active');
    if (window.matchMedia('(min-width: 992px)').matches) {
        $('body').toggleClass('sidebar-folded');
    } else if (window.matchMedia('(max-width: 991px)').matches) {
        $('body').toggleClass('sidebar-open');
    }
};

const hoverNav = () => {
    if ($('body').hasClass('sidebar-folded')) {
        $('body').addClass("open-sidebar-folded");
    }
}
const hoverNavLeave = () => {
    if ($('body').hasClass('sidebar-folded')) {
        $('body').removeClass("open-sidebar-folded");
    }
}
</script>

<style scoped>
.sidebar li:hover {
    font-weight: 600 !important;
    /* background: #1919702c !important; */
    color: #191970 !important;
}

.nav {
    padding: 10px 0 !important;
}

.sidebar a {
    font-weight: 600 !important;
    color: #191970 !important;
    padding: 2px 0 2px 10px !important;
}

.sidebar .link-title {
    margin-left: 15px !important;
}

/* .sidebar .myIcon {
    font-size: 1.8em !important;
} */

.sidebar .router-link-exact-active {
    background: #eeeeee !important;
    /* border-left: 7px solid #191970; */
}
</style>