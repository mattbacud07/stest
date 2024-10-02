import { defineStore } from "pinia";

export const getRole = defineStore('getRoles', {
    state: () => ({
        currentUserRole: null,
    }),
    actions: {
        setCurrentUserRole(role) {
            this.currentUserRole = role
        },
    },
    persist: true
})