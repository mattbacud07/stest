import { defineStore } from "pinia";

export const user_data = defineStore('users', {
    state: () => ({
        user: {},
        tokenData: null,
    }),
    actions: {
        setUserData(userData) {
            this.user = userData
        },
        setTokenData(token){
            this.tokenData = token
        }
    },
    persist : true
})
