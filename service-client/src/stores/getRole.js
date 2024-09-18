import { defineStore } from "pinia";


export const getRole = defineStore('getRoles', {
    state : () => {
        const stored =  localStorage.getItem('currentUserRole')
        const parseRole = stored ? JSON.parse(stored) : {}
        return{
            // activeRole : parseRole.role_id || 357,
            currentUserRole : {
                role_id : parseRole.role_id || 357, 
                role_name : parseRole.role_name || 'Requestor'}
        }
    },
    actions: {
        setCurrentUserRole(role){
            this.currentUserRole = role
            localStorage.setItem('currentUserRole', JSON.stringify(role))
        },
    },

})