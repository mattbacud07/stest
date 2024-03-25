<template>
    <div class="main-wrapper">
        <AdminSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <RolesMain />
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import AdminSidebar from '@/components/layout/Sidebars/AdminSidebar.vue';
// import SideMain from '@/components/layout/SideMain.vue'
import RolesMain from '@/components/Roles/RolesMain.vue'

</script>