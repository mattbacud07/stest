import { apiRequestAxios } from "@/api/api";
import { defineStore } from "pinia";
import { ref } from "vue";

const apiRequest = apiRequestAxios()

export const store_permission = defineStore('permission', {
    state : () => ({
        permissions : ref([]),
        selected_role_id : ref(null),
        sidebarConfig: {},
    }),

    actions : {
        async loadSidebarConfig(role) {
            this.sidebarConfig.role = { admin : ['data', 'ney']}
        }
    },

    // persist : {
    //     localStorage
    // }
})