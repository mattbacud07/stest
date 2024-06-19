<template>
    <v-form @submit.prevent="loginProcess" ref="form" cols="12">
        <v-row class="d-flex mb-3">
            <v-col cols="12">
                <!-- <v-label class="font-weight-bold mb-1">email</v-label> -->
                <v-text-field v-model="auth.email" :rules="rules.emailRules" density="compact" label="Email"
                    variant="outlined" hide-details="auto" color="primary" required></v-text-field>
            </v-col>
            <v-col cols="12">
                <!-- <v-label class="font-weight-bold mb-1">Password</v-label> -->
                <v-text-field v-model="auth.password" :rules="rules.passwordRules" label="Password" density="compact"
                    variant="outlined" type="password" hide-details="auto" color="primary"></v-text-field>
            </v-col>
            <v-col cols="12" class="mt-5">
                <v-btn type="submit" density="compact" color="primary" size="large" block rounded="0"
                    class="text-none" :loading="loading">Sign in</v-btn>
            </v-col>
        </v-row>
        <v-row>
            <v-col cols="12">
                <v-alert v-if="alert.alertMessage" :type="alert.type" transition="fade-transition" variant="outlined" closable>
                    {{ alert.alertMessage }}
                </v-alert>
            </v-col>
        </v-row>
    </v-form>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { alertStore } from '@/stores/alert-popup'
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index';

axios.defaults.withCredentials = true
// axios.defaults.headers.common['Authorization'] = `Bearer ${accessToken}`;
// axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

const router = useRouter();
const alert = alertStore()
const userData = user_data()
const uri = BASE_URL
const form = ref(null)
const auth = ref({
    email: '',
    password: '',
})
const loading = ref(false)

/** Checking Rules */
const rules = ref({
    emailRules: [
        v => !!v || 'Email is required'
    ],
    passwordRules: [
        v => !!v || 'Password is required'
    ],
})

/** Form Submit */
const loginProcess = async () => {
    loading.value = true
    const { valid } = await form.value.validate()

    if (!valid) {
        loading.value = false
        return
    }

    try {
        const response = await axios.post(uri + 'api/authentication', {
            email: auth.value.email,
            password: auth.value.password,
        },
        {
            headers : {
                'Accept' : 'application/json'
            }
        }
        )

        if (response.data && response.data.isLogin) {
            router.push('/dashboard')

            /** Store Token in LocalStorage */ // -> alternate  // localStorage.setItem('accessToken', response.data.accessToken)
            localStorage.setItem('users', JSON.stringify(response.data.user))
            localStorage.setItem('token', JSON.stringify(response.data.accessToken))
            localStorage.setItem('isAuthenticated', 'true')

        }else if(response.data.status === 'inactive'){
            /** If User is Inactive */
            alert.showAlertMessage('Inactive account', 'warning')
        }else{
            /** If User Incorrect Credentails */
            alert.showAlertMessage('Incorrect username or password', 'error')
        }
    } catch (error) {
        alert.showAlertMessage('Something went wrong', 'error')
        console.log(error)
    }
    loading.value = false
}

</script>