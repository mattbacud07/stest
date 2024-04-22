// import axios from "axios"
// import { user_data } from "@/stores/auth/userData"

// const user = user_data()
// user.getUserData
// const tokenData = user.tokenData
// const tokenData =  JSON.parse(localStorage.getItem('token')) ?? ''

// const getTokenData = () => JSON.parse(localStorage.getItem('token')) ?? ''

export const BASE_URL =
    import.meta.env.VITE_ENV == "production"
        ? import.meta.env.VITE_BASE_URI_PROD
        : import.meta.env.VITE_BASE_URI

// export const apiRequest = axios.create({
//     baseURL : BASE_URL + "api/",
//     timeout : 10000,
//     headers : {
//         // "Authorization" : `Bearer ${getTokenData}`,
//         "Content-Type" : "application/json",
//         Accept : "application/json",
//     },
//     withCredentials : true,
// })

// if (getTokenData) apiRequest.defaults.headers.common["Authorization"] = `Bearer ${getTokenData()}`

// window.addEventListener('storage', (event) => {
//     console.log('Storage event triggered:', event);
//     if(event.key === 'token'){
//         const tokenData = getTokenData()
//         apiRequest.defaults.headers.common['Authorization'] = `Bearer ${tokenData}`
//     }
// })