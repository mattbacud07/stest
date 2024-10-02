import { abilityStore } from "@/stores/abilityStores";
import { useAbility } from '@casl/vue';
import { getRole } from "@/stores/getRole";
import { adminServiceRoleID } from "@/global/global";


/** general access permission */
export const canAccess = (action, resource) => {
    return (to, from, next) => {
        const myAbility = abilityStore()
        const ability = myAbility.abilities
        const { can } = useAbility(ability.value)
        if(can(action, resource)) {
            next()
        }
        else next('/forbidden')
    }
}



/** Admin Access Permission */
export const adminGuard = () => {
    return (to, from, next) => {
        const role = getRole()
        if(role.currentUserRole !== adminServiceRoleID){
            next('/forbidden')
        }
        else next()
    }
}