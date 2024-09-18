import { AbilityBuilder, createMongoAbility } from "@casl/ability";
import { create } from "lodash";
import { defineStore } from "pinia";
import { user_data } from "./auth/userData";


export const getRole = defineStore('getRoles', {
    state: () => {
        const stored = localStorage.getItem('currentUserRole')
        const parseRole = stored ? JSON.parse(stored) : {}
        return {
            // activeRole : parseRole.role_id || 357,
            currentUserRole: {
                role_id: parseRole.role_id || 357,
                role_name: parseRole.role_name || 'Requestor'
            },
            ability : createMongoAbility([]),
        }
    },
    actions: {
        setCurrentUserRole(role) {
            this.currentUserRole = role
            localStorage.setItem('currentUserRole', JSON.stringify(role))
            this.ability = this.buildAbility()
        },
        buildAbility(){
            const user = user_data()
            const { can, cannot, build } = new AbilityBuilder(createMongoAbility)


            if (this.currentUserRole) {
                // if (this.currentUserRole.role_id === 1) {
                //     can('create', 'Post')
                // }
                if(this.currentUserRole.role_id === 1) can('create', 'Post')
            }

            return build()
        }
    },

    getters: {
        abilities: (state) => {
            return state.ability
        }
    }

})