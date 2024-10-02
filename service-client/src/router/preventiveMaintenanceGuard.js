import { abilityStore } from "@/stores/abilityStores";
import { useAbility } from '@casl/vue';

/** Accessing Work Order Main Request */
export const preventiveMaintenanceRequest = (actionsArray, resource) => {
    return (to, from, next) => {
        const myAbility = abilityStore()
        const ability = myAbility.abilities
        const { can } = useAbility(ability.value)

        const validWorkType = ['PM', 'CM']
        const work_type = to.params.work_type

        if (validWorkType.includes(work_type)) {
            const hasAccess = actionsArray.some(action => can(action, resource))
            if (hasAccess) {
                next()
            }
            else next('/forbidden')
        }
        else next(false)
    }
}