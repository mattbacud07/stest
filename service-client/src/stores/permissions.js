import { user_data } from "@/stores/auth/userData";
import { defineStore } from "pinia";


const user = user_data()
const apiRequest = user.apiRequest()

export let permissions = []

export const permission = defineStore('permission', {
    state : {
        permissions : []
    },
    actions : {
        async getPermissionFromModel(){
            try {
                const response = await apiRequest.get()
            } catch (error) {
                
            }
        }
    },
    persist : {
        localStorage
    }
})

