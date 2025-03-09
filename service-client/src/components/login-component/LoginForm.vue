<template>
    <v-row>
        <v-col cols="12" xl="3" lg="3" md="3" sm="6" style="margin-left: 50%;transform: translateX(-50%);">

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

import { requestorID } from '@/global/global';

/** Toast Notification */
import { useToast } from 'vue-toast-notification'
const toast = useToast()

/** Roles and Permissions */
import { getRole } from '@/stores/getRole';
const role = getRole()
import { abilityStore } from '@/stores/abilityStores';
const ability = abilityStore()

import { logout } from '@/stores/auth/logout';
import DOMPurify from 'dompurify';
const logMeOut = logout()

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

// Sanitize
const sanitizeAndTrim = (input) => {
    const trimmedInput = input.trim();
    
    const sanitizedInput = DOMPurify.sanitize(trimmedInput);
    
    return sanitizedInput;
}

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
            email: sanitizeAndTrim(auth.value.email),
            password: sanitizeAndTrim(auth.value.password),
        },
            {
                headers: {
                    'Accept': 'application/json'
                }
            }
        )

        if (response.data && response.data.isLogin) {
            router.push('/dashboard')
            if (response.data.user && response.data.accessToken) {
                userData.setUserData(response.data.user) //set User Data
                userData.setTokenData(response.data.accessToken) // set Tokens Data
                const userInfo = response.data.user
                /** Setting Default Role and permissions */
                const checkUserRoleRequestor = userInfo.user_roles.find(v => v.role_id === requestorID)
                if (checkUserRoleRequestor) {
                    role.setCurrentUserRole(checkUserRoleRequestor.role_id)
                    ability.buildAbility()
                } else {
                    const defaultRole = response.data.user.user_roles[0]
                    if (defaultRole) {
                        role.setCurrentUserRole(defaultRole.role_id)
                        ability.buildAbility()
                    }
                    else {
                        role.setCurrentUserRole(null)
                    }
                }
            }
            /** Store Token in LocalStorage */ // -> alternate  // localStorage.setItem('accessToken', response.data.accessToken)
            // localStorage.setItem('users', JSON.stringify(response.data.user))
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