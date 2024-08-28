<template>
    <v-row>
        <v-col cols="12" xl="4" lg="4" md="4" sm="6" style="margin-left: 50%;transform: translateX(-50%);">

            <p class="small">Hi! Welcome back.</p>
            <v-form @submit.prevent="loginProcess" ref="form">
                <v-text-field v-model="auth.email" :rules="rules.emailRules" single-line density="compact" label="Email"
                    variant="outlined" color="primary" class="mt-7" hide-details
                    prepend-inner-icon="mdi-email-outline"></v-text-field>


                <v-text-field v-model="auth.password" :rules="rules.passwordRules" label="Password" density="compact"
                    variant="outlined" type="password" color="primary" hide-details single-line class="mt-7"
                    prepend-inner-icon="mdi-lock"></v-text-field>


                <v-btn type="submit" density="default" color="primary" class="mt-7 pa-5" block :loading="loading">Sign
                    in</v-btn>
            </v-form>
        </v-col>
    </v-row>

</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useRouter } from 'vue-router';
import { user_data } from '@/stores/auth/userData'
import { BASE_URL } from '@/api/index';

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

axios.defaults.withCredentials = true
// axios.defaults.headers.common['Authorization'] = `Bearer ${accessToken}`;
// axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

const router = useRouter();
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
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (response.data && response.data.isLogin) {
            router.push('/dashboard')

            /** Store Token in LocalStorage */ // -> alternate  // localStorage.setItem('accessToken', response.data.accessToken)
            localStorage.setItem('users', JSON.stringify(response.data.user))
            localStorage.setItem('token', JSON.stringify(response.data.accessToken))
            localStorage.setItem('isAuthenticated', 'true')

        } else if (response.data.status === 'inactive') {
            /** If User is Inactive */
            toast.warning('Inactive Account')
        } else {
            /** If User Incorrect Credentails */
            toast.error('Incorrect username or password')
        }
    } catch (error) {
        toast.error('Something went wrong')
        console.log(error)
    }
    loading.value = false
}

</script>