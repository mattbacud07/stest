import { defineStore } from "pinia";


export const getRole = defineStore('getRoles', {
    state : () => ({
        currentUserRole : localStorage.getItem('currentUserRole') || 'Requestor',
    }),
    actions: {
        setCurrentUserRole(role){
            this.currentUserRole = role
            localStorage.setItem('currentUserRole', role)
        },
    },
    // getters:{
    //     getRoleData(){
    //         return this.currentUserRole = localStorage.getItem('currentUserRole')
    //     }
    // }

})