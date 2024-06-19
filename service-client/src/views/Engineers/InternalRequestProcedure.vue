<template>
    <div class="main-wrapper">
        <EngineersSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <InternalServicing />
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import EngineersSidebar from '@/components/layout/Sidebars/EngineersSidebar.vue';
import InternalServicing from '@/components/Approver/EH/InternalServicing.vue'
</script>