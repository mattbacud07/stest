<template>
    <div class="text-right pa-4">
        <v-btn @click="dialog = true" color="primary"  class="text-none" prepend-icon="mdi-account">
            <span class="text-dark"><b>{{ user.user.first_name }} {{ user.user.last_name }}</b></span>  | Logout
        </v-btn><br/>
        <span class="small mr-4 text-disabled">Logged in as : <b class="text-disabled">{{ currentUserROle }}</b></span>

        <v-dialog v-model="dialog" width="auto">
            <v-card min-width="300" prepend-icon="mdi-account"
                text="Are you sure you want to leave?"
                title="Logout">
                <template v-slot:actions>
                    <v-spacer></v-spacer>
                    <v-btn class="text-none" variant="tonal" color="primary" text="Cancel" @click="dialog = false"></v-btn>
                    <v-btn class="text-none" color="primary" min-width="150px" variant="flat" text="Yes" :loading="loading" @click="logOut"></v-btn>
                </template>
            </v-card>
        </v-dialog>
    </div>
</template>
<script setup>
import { computed, ref } from 'vue';
import { user_data } from '@/stores/auth/userData';
import { logout } from '@/stores/auth/logout'
import { useRouter } from 'vue-router';

const user = user_data()
const logMeOut = logout()
const router = useRouter()
const loading = ref(false)
const dialog = ref(false)

import { getRole } from '@/stores/getRole';
const role = getRole()

const currentUserROle = computed(() => user.user.user_roles.find(v => role.currentUserRole === v.role_id)?.role_name)

const logOut = async () => {
    loading.value = true
    try {
        await logMeOut.log_me_out();
        window.location.href = '/'
        loading.value = false
    } catch (error) {
        alert(error)
    } finally {
        loading.value = false
    }
}
</script>