import axios from "axios"


export const BASE_URL =
    import.meta.env.VITE_ENV == "production"
        ? import.meta.env.VITE_BASE_URI_PROD
        : import.meta.env.VITE_BASE_URI

// export const api = axios.create({
//     BASE_URL + "api/",
//     headers : {
//         "Content-Type" : "application/json",
//         Accept : "application/json",
//     },
//     withCredentials : true,
// })