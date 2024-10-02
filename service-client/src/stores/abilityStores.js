import { defineStore } from "pinia";
import { user_data } from "./auth/userData";
import { getRole } from "./getRole";
import { AbilityBuilder, createMongoAbility } from "@casl/ability";
import { apiRequestAxios } from "@/api/api";



export const abilityStore = defineStore('abilityStored', {
    state: () => {
        const storedAbility = localStorage.getItem('userAbilities');
        const parseAbility = storedAbility ? JSON.parse(storedAbility) : [];
        return {
            ability: createMongoAbility([]),
            rules: []
        }
    },
    actions: {
        async buildAbility() {
            const userData = user_data()
            const role = getRole()
            const api = apiRequestAxios()

            // try {
            //     const response = await api.get('get_role_permissions')
            //     console.log(response.data.user_roles_permission)
            // } catch (error) {
                
            // }

            // this.rules = userData -> for testing
            const { can, build, rules } = new AbilityBuilder(createMongoAbility)

            // Check if user roles and permissions exist
            if (!userData?.user?.user_roles || !role.currentUserRole) {
                console.error('User roles or current user role not found');
                return;
            }

            const currentPermission = userData.user.user_roles.find(v => role.currentUserRole === v.role_id)
            // console.log(currentPermission)
            if (currentPermission) {
                let user_permission = currentPermission.permissions;

                try {
                    // Parse permissions if they are a string, or use them directly if they are already an array
                    user_permission = typeof user_permission === 'string' ? JSON.parse(user_permission) : user_permission;

                    // Loop through each permission and define the abilities
                    user_permission?.forEach((data) => {
                        if (data.create) can('create', data.module);
                        if (data.read) can('read', data.module);
                        if (data.edit) can('edit', data.module);
                        if (data.delete) can('delete', data.module);
                        if (data.approve) can('approve', data.module);
                        if (data.delegate) can('delegate', data.module);
                        if (data.installer) can('installer', data.module);
                        if (data.report) can('report', data.module);
                    });
                } catch (error) {
                    console.error('Error parsing permissions:', error);
                }
            } else {
                console.error('Current permission not found for the role');
            }

            this.rules = rules
            this.ability.update(rules)
        }
    },

    getters: {
        abilities: (state) => {
            return state.ability
        }
    },
    // persist : true
    persist: {
        paths: ['rules'],
        afterHydrate: (ctx) => {
            ctx.store.ability = createMongoAbility(ctx.store.rules)
        }
    }

})