<template>
    <div class="container">
        <v-alert closable color="warning" text="">
            <h4 class="mb-3">{{ $route.name === 'DashboardApprover' ? 'Welcome! Approver Dashboard' : 'Welcome' }}</h4>
            {{ user.user.first_name }} {{ user.user.last_name }}
        </v-alert>
    </div>
</template>
<script setup>
import { user_data } from '@/stores/auth/userData'
import {useRouter} from 'vue-router'

const router = useRouter()

const user = user_data();
user.getUserData

</script>