/** CAstl Permission */
import { abilityStore } from '@/stores/abilityStores';
import { useAbility } from '@casl/vue';
import { computed } from 'vue';


export const permit = () => {
    const myAbility = abilityStore()
    const ability = computed(() => myAbility.abilities)
    const { can } = useAbility(ability.value)

    return {can}
}