<template>
    <div class="main-wrapper">
        <EngineersSidebar />
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
import EngineersSidebar from '@/components/layout/Sidebars/EngineersSidebar.vue';
import Welcome from '@/components/dashboard-content/Welcome.vue'

</script>