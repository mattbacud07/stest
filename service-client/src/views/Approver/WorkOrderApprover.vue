<template>
    <div class="main-wrapper">
        <ApproverSidebar />
        <div class="page-wrapper">
            <Header />
            <div class="page-content">
                <WorkOrderApproverMain />
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '@/components/layout/Header.vue'
import ApproverSidebar from '@/components/layout/Sidebars/ApproverSidebar.vue';
import WorkOrderApproverMain from '@/components/Approver/EH/WorkOrderApproverMain.vue'
</script>