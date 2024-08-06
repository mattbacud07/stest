<template>
    <nav class="navbar">
        <a href="#" class="sidebar-toggler" @click="toggleSidebar">
            <!-- <i data-feather="menu"></i> -->
             <v-icon>mdi-menu</v-icon>
        </a>
        <div class="navbar-content">
            <form class="search-form">
                <div class="input-group">
                    <div class="input-group-text">
                        <i data-feather="search"></i>
                    </div>
                    <input type="text" class="form-control" id="navbarForm" placeholder="Search here...">
                </div>
            </form>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" ref="#" id="profileDropdown" role="button"
                        data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <v-icon color="primary" class="mr-3">mdi-account-settings</v-icon>
                        <span class="text-dark"><b>{{ user.user.first_name }} {{ user.user.last_name }}</b></span>
                    </a>
                    <a class="nav-link ml-5" ref="#" role="button" @click="logOut">
                        <v-icon color="primary" class="mr-3">mdi-logout</v-icon>
                        <v-tooltip activator="parent" location="bottom">Logout. Are you sure?</v-tooltip>
                    </a>
                    <div class="dropdown-menu p-0" aria-labelledby="profileDropdown"
                        style="box-shadow: inset 0 0 3px #999;z-index: 9999!important;">
                        <div class="d-flex flex-column align-items-center border-bottom px-5 py-3">
                            <div class="text-center">
                                <p class="tx-12 text-muted">{{ user.user.email }}</p>
                                <!-- <p class="tx-12 text-muted">{{ user.user.position_name }}</p> -->
                            </div>
                        </div>
                        <ul class="list-unstyled p-1">
                            <li class="dropdown-item py-2">
                                <v-btn @click="logOut" variant="tonal" block
                                    :loading="loading">
                                    <i class="me-2 icon-md" data-feather="log-out"></i>
                                    <span>Log Out</span>
                                </v-btn>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
</template>

<script setup>
import { ref } from 'vue';
import { user_data } from '@/stores/auth/userData';
import { logout } from '@/stores/auth/logout'
import { useRouter } from 'vue-router';

const user = user_data()
user.getUserData
const logMeOut = logout()
const router = useRouter()
const loading = ref(false)

const logOut = async () => {
    loading.value = true
    try {
        await logMeOut.log_me_out();
        window.location.href = '/'
    } catch (error) {
        alert(error)
    } finally {
        loading.value = false
    }
}


const toggleSidebar = () => {
    $('.sidebar-header .sidebar-toggler').toggleClass('active not-active');
    if (window.matchMedia('(min-width: 992px)').matches) {
        // $('body').toggleClass('sidebar-folded');
    } else if (window.matchMedia('(max-width: 991px)').matches) {
        $('body').toggleClass('sidebar-open');
    }
};
</script>