<template>
    <v-container fluid class="pa-3">
        <v-card rounded="md" style="box-shadow: 0 0 0 !important;background: #00000000!important;" class="px-sm-1 px-0 mx-auto" max-width="500">
            <v-card-item>
                <div class="d-flex justify-center py-4 mb-5">
                    <img :src="Logo" alt="SBSI Logo" style="height: 50px;width: auto;">
                </div>
            </v-card-item>
        </v-card>

            <LoginForm />

    </v-container>
</template>
<script setup>
// Logo
import Logo from '@/assets/SBSI-Logo.png'
/* Login form */
import LoginForm from '@/components/login-component/LoginForm.vue'
</script>