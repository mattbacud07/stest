import { user_data } from "@/stores/auth/userData";
import { defineStore } from "pinia";
import { ref } from "vue";


const user = user_data()
const apiRequest = user.apiRequest()

export const store_permission = defineStore('permission', {
    state : () => ({
        permissions : ref([]),
        selected_role_id : ref(null)
    }),

    actions : {
        async getPermissionFromModel(roleID){
            try {
                const response = await apiRequest.get('get_permission_per_role',{
                    params : { role_id : roleID}
                })
            } catch (error) {
                
            }
        }
    },

    persist : {
        localStorage
    }
})

