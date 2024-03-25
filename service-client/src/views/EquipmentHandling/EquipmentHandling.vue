<template>
    <div class="main-wrapper">
        <Sidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <EHMain />
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import Sidebar from '@/components/layout/Sidebars/Sidebar.vue';
// import SideMain from '@/components/layout/SideMain.vue'
import Footer from '@/components/layout/Footer.vue'
import EHMain from '@/components/EquipmentHandling/EHMain.vue'

</script>