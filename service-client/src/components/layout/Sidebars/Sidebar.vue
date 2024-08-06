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
            <ul class="nav" style="padding: 0 0 !important;" v-if="user.user.role !== pub_const.adminRole">
                <!-- <li class="nav-item nav-category ml-3">Switch Account</li> -->
                <router-link to="/approver-dashboard" class="router-link-active"
                    v-if="user.user.role_name === pub_const.approverRole">
                    <li class="nav-item" style="padding: 5px 0;">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-account-card</v-icon>
                            <span class="link-title">Switch Account<br><span
                                    style="color: #999;font-weight: 100;font-size: .8em;">as Approver</span></span>
                        </a>
                    </li>
                </router-link>


                <a href="#" class="" v-if="user.user.user_roles.length !== 0">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <v-icon class="myIcon">mdi-account-card</v-icon>
                            <select v-model="changeRole" @change="handleChangeRole" class="form-select w-100 rounded-0 link-title"
                                placeholder="Switch Account">
                                <option disabled value="">Switch Account</option>
                                <option v-for="role in data_roles" :key="role" :value="role">{{ role }}</option>
                            </select>
                        </a>
                    </li>
                </a>
                <router-link to="/tl-dashboard" class="router-link-active"
                    v-if="user.user.role_name === pub_const.serviceTLRole">
                    <li class="nav-item" style="padding: 5px 0;">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-account-card</v-icon>
                            <span class="link-title">Switch Account<br><span
                                    style="color: #999;font-weight: 100;font-size: .8em;">as Team Leader</span></span>
                        </a>
                    </li>
                    <v-divider></v-divider>
                </router-link>
                <router-link to="/">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <v-icon class="myIcon">mdi-shield-home-outline</v-icon>
                            <span class="link-title">Dashboard</span>
                        </a>
                    </li>
                </router-link>

                <a href="#"><li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#requestLink" role="button">
                        <v-icon>mdi-file-plus</v-icon>
                        <span class="link-title">Request</span>
                        <v-icon class="link-arrow mr-3">mdi-chevron-down</v-icon>
                    </a>
                    <div class="collapse" id="requestLink">
                        <ul class="nav sub-menu">
                            <router-link to="/equipment-handling" class="router-link-active">
                                <li class="nav-item">
                                    <a class="nav-link">
                                        <v-icon class="myIcon">mdi-file-document-edit</v-icon>
                                        <span class="link-title">Equipment Handling</span>
                                    </a>
                                </li>
                            </router-link>
                        </ul>
                    </div>
                </li></a>
            </ul>
        </div>
    </nav>

    <v-icon icon="md:home" aria-hidden="false"></v-icon>
</template>



<script setup>
import { computed, ref, onMounted } from 'vue';
import { user_data } from '@/stores/auth/userData'
import * as pub_const from '@/global/global.js';
import { useRouter } from 'vue-router';

const router = useRouter()

const user = user_data()
user.getUserData
const changeRole = ref('')


/** Hande Change of Role */
const handleChangeRole = () => {
    switch (changeRole.value) {
        case pub_const.adminServiceRole:
            router.push('/admin-dashboard')
            break;
        case pub_const.approverRole:
            router.push('/approver-dashboard')
            break;
        case pub_const.engineerRole:
            router.push('/engineer-dashboard')
            break;
        case pub_const.serviceTLRole:
            router.push('/tl-dashboard')
            break;
        case pub_const.outboundPersonnel:
            router.push('/outbound-dashboard')
            break;
        default:
            break;
    }
}


const data_roles = ref([])

onMounted(() => {
    data_roles.value = user.user.user_roles
})


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
/* 
.myIcon {
    font-size: 1.8em !important;
    padding: 5px 0;
} */

.sidebar .router-link-exact-active {
    background: #eeeeee !important;
    /* border-left: 7px solid #191970; */
}
</style>