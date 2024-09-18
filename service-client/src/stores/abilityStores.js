import { defineStore } from "pinia";
import { ref } from "vue";
import { getRole } from "./getRole";
import { AbilityBuilder, Ability } from "@casl/ability";


export const storeAbility = defineStore('storeAbility', {
    state : () =>({
        ability : createAbi
    }),

    actions : {
        
    }
})