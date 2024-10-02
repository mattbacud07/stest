import { AbilityBuilder, createMongoAbility } from "@casl/ability";
import { getActivePinia } from "pinia";
import { getRole } from "@/stores/getRole";
import { computed } from "vue";

export const defineAblity = () => {
    const buildAbility = () => {
        const pinia = getActivePinia()
        const role = getRole(pinia)

        const { can, cannot, build } = new AbilityBuilder(createMongoAbility)

        if (role.currentUserRole.role_id === 1) {
            can('create', 'Post')
        }
        return build()
    }

    return buildAbility()

}