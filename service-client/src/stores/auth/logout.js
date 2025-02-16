import { defineStore } from 'pinia';
import axios from 'axios'
import { BASE_URL } from '@/api/index.js'
import { user_data } from '@/stores/auth/userData';

const uri = BASE_URL
export const logout = defineStore('Logout', {
    actions: {
        async log_me_out() {

            const user = user_data()
            try {
                const response = await axios.post(
                    uri + 'api/log-me-out',
                    null,
                    {
                        headers: {
                            'Authorization': `Bearer ${user.tokenData}`
                        }
                    },
                )
                if (response.data && response.data.isLogout) {
                    localStorage.clear()
                } else {
                    localStorage.clear()
                    return 'Something wrong'
                }
            } catch (error) {
                return 'Something went wrong' + error
            }
        }
    }
})