<template>
    
    <Sidebar v-if="user.user.role_name !== pub_const.approverRole" />
    <AdminSidebar v-if="user.user.role === adminRole" />
    <ApproverSidebar v-if="user.user.role_name === approverRole" />

</template>

<script setup>
import Sidebar from './Sidebars/Sidebar.vue';
import AdminSidebar from './Sidebars/AdminSidebar.vue';
import ApproverSidebar from './Sidebars/ApproverSidebar.vue';
import { user_data } from '@/stores/auth/userData';
import * as pub_const from '@/global/global.js';

const user = user_data()
user.getUserData
const adminRole = pub_const.adminRole
const approverRole = pub_const.approverRole

</script>
