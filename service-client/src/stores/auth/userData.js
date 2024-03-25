import { defineStore } from "pinia";

export const user_data = defineStore('users',{
    state : () => ({
        user : {},
        tokenData : null,
    }),

    actions : {
        // getUserData(){
        //     this.users = JSON.parse(localStorage.getItem('users'))
        //     this.tokenDatas = JSON.parse(localStorage.getItem('token'))
        // }
    },
    getters : {
        getUserData(){
            this.user = JSON.parse(localStorage.getItem('users'))
            this.tokenData = JSON.parse(localStorage.getItem('token'))
        }
    }
})
