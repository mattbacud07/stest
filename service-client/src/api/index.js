
export const BASE_URL =
    import.meta.env.VITE_ENV == "production"
        ? import.meta.env.VITE_BASE_URI_PROD
        : import.meta.env.VITE_BASE_URI

