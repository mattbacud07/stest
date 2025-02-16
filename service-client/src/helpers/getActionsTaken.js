import { apiRequestAxios } from "@/api/api";
import { ref, onMounted } from "vue";
const apiRequest = apiRequestAxios()



/** Get Standard Actions */
export const actions_data = ref([])
export const getStandardActions = async () => {
    try {
        const response = await apiRequest.get('getStandardActions')
        if (response.data && response.data.actions) {
            actions_data.value = response.data.actions.map(val => val.actions)
        }
    } catch (error) {
        console.error("Failed to fetch standard actions:", error);
    }
};

export const useActions = () => {
    onMounted(() => {
        if (!actions_data.value.length) {
            getStandardActions()
        }
    })
    return { actions_data }
}