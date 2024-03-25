<template>
    <div class="main-wrapper">
        <AdminSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <ApprovalConfigMain />
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import AdminSidebar from '@/components/layout/Sidebars/AdminSidebar.vue';
// import SideMain from '@/components/layout/SideMain.vue'
import ApprovalConfigMain from '@/components/ApprovalConfiguration/ApprovalConfigMain.vue'

</script>