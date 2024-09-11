<template>
    <!-- <div class="main-wrapper">
        <AdminSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <Welcome />
            </div>
        </div>
    </div> -->
    <BaseLayout>
        <Welcome />
    </BaseLayout>
</template>

<script setup>
// import Header from '@/components/layout/Header.vue'
// import AdminSidebar from '@/components/layout/Sidebars/AdminSidebar.vue';
import Welcome from '@/components/dashboard-content/Welcome.vue'
import BaseLayout from '@/components/layout/MainLayout/BaseLayout.vue';

</script>