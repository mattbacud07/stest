import { defineStore } from "pinia";
import { BASE_URL } from "@/api";

export const user_data = defineStore('users',{
    state : () => ({
        user : {},
        tokenData : null,
    }),

    actions : {
        apiRequest(){
            const token = JSON.parse(localStorage.getItem('token'))
             return axios.create({
                baseURL : BASE_URL + "api/",
                timeout : 90000,
                headers : {
                    "Authorization" : `Bearer ${token}`,
                    "Content-Type" : "application/json",
                    Accept : "application/json",
                },
                withCredentials : true,
            })
        }
    },
    getters : {
        getUserData(){
            this.user = JSON.parse(localStorage.getItem('users'))
            this.tokenData = JSON.parse(localStorage.getItem('token'))
        }
    }
})
