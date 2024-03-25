<template>
    <nav class="sidebar">
        <div class="sidebar-header" @click="toggleSidebar">
            <a href="#" class="sidebar-brand">
                Web<span>App</span>
            </a>
            <div class="sidebar-toggler not-active">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        <div class="sidebar-body" v-on:mouseover="hoverNav" v-on:mouseleave="hoverNavLeave">
            <ul class="nav">
                <li class="nav-item nav-category ml-3">Main Admin</li>

                <router-link to="/dashboard">
                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <v-icon class="myIcon">mdi-shield-home-outline</v-icon>
                            <span class="link-title">Dashboard</span>
                        </a>
                    </li>
                </router-link>
                <router-link to="/aprroval-config">
                    <li class="nav-item">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-tag-check</v-icon>
                            <span class="link-title">Approval Config</span>
                        </a>
                    </li>
                </router-link>
                <router-link to="/roles">
                    <li class="nav-item">
                        <a class="nav-link">
                            <v-icon class="myIcon">mdi-account-badge</v-icon>
                            <span class="link-title">Roles</span>
                        </a>
                    </li>
                </router-link>
                <!-- <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="collapse" href="#uiComponents" role="button">
                        <i class="mdi mdi-application-edit-outline"></i>
                        <span class="link-title">Request</span>
                    </a>
                    <div class="collapse" id="uiComponents">
                        <ul class="nav sub-menu">
                            <li class="nav-item">
                                <a href="#" class="nav-link">Equipment Handling</a>
                            </li>
                        </ul>
                    </div>
                </li> -->
            </ul>
        </div>
    </nav>
</template>

<script setup>
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
    background: #1919702c !important;
    color: #191970 !important;
}

.nav {
    padding: 10px 0 !important;
}

.sidebar a {
    font-weight: 600 !important;
    color: #191970 !important;
    padding: 5px 0 3px 10px !important;
}

.sidebar .link-title {
    margin-left: 15px !important;
}

.sidebar .myIcon {
    font-size: 1.8em !important;
}

.sidebar .router-link-exact-active {
    background: #eeeeee !important;

}
</style>
