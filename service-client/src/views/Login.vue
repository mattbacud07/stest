<template>
    <div class="authentication">
        <v-container fluid class="pa-3">
            <v-row class="h-100vh d-flex justify-center align-center">
                <v-col cols="12" lg="4" xl="3" class="d-flex align-center">
                    <v-card rounded="md" elevation="0" class="px-sm-1 px-0 withbg mx-auto" max-width="500">
                        <v-card-item class="pa-sm-8">
                            <div class="d-flex justify-center py-4 mb-5">
                                <img :src="Logo" alt="SBSI Logo" style="height: 50px;width: auto;">
                            </div>
                            <LoginForm />
                        </v-card-item>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
    </div>
</template>
<script setup>
// Logo
import Logo from '@/assets/SBSI-Logo.png'
/* Login form */
import LoginForm from '@/components/login-component/LoginForm.vue'
</script>