import { BASE_URL } from ".";
import axios from "axios";
import router from "@/router";



export const apiRequestAxios = () => {
    const token = JSON.parse(localStorage.getItem('token'))
    const instance = axios.create({
        baseURL: BASE_URL + "api/",
        timeout: 90000,
        headers: {
            "Authorization": `Bearer ${token}`,
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        withCredentials: true,
        withXSRFToken: true,
    })


    /** Reposnse Interceptor */
    instance.interceptors.response.use(
        response => response,
        error => {
            if (error.response) {
                if (error.response.status === 401) {
                    localStorage.clear()
                    router.replace({ name: 'login' })
                }
                if(error.response.status === 403){
                    router.replace({ name: 'forbidden'})
                }
            }

            return Promise.reject(error)
        }
    )

    return instance
}