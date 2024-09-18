import { defineStore } from "pinia";

export const user_data = defineStore('users', {
    state: () => ({
        user: {},
        tokenData: null,
    }),

    // actions: {
        
    // },
    getters: {
        getUserData() {
            this.user = JSON.parse(localStorage.getItem('users'))
            this.tokenData = JSON.parse(localStorage.getItem('token'))
        }
    }
})
