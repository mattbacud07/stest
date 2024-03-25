import { defineStore } from "pinia";

export const alertStore = defineStore('alert-popup', {
    state : () => ({
        alertMessage : null,
        type : null,
    }),
    actions : {
        showAlertMessage(message, type) {
            this.alertMessage = message
            this.type = type
            setTimeout(() => {
                this.alertMessage = null
            }, 6000)
        },
    },
    // getters : {
    //     alertMessage : state => state.alertMessage,
    // }


})
