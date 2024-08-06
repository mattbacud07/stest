<template>
    <div class="main-wrapper">
        <TeamLeaderSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <Welcome />
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
// import Sidebar from '@/components/layout/Sidebars/Sidebar.vue';
import TeamLeaderSidebar from '@/components/layout/Sidebars/OutboundSidebar.vue';
import Welcome from '@/components/dashboard-content/Welcome.vue'

</script>