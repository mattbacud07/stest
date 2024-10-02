import { abilityStore } from "@/stores/abilityStores";
import { useAbility } from '@casl/vue';
import { getRole } from "@/stores/getRole";

/** Accessing Work Order Main Request */
export const internalServicingRequest = (actionsArray, resource) => {
    return (to, from, next) => {
        const myAbility = abilityStore()
        const ability = myAbility.abilities
        const { can } = useAbility(ability.value)

        const hasAccess = actionsArray.some(action => can(action, resource))
        if(hasAccess) {
            next()
        }
        else next('/forbidden')
    }
}