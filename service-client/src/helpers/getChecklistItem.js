import { apiRequestAxios } from "@/api/api";
import { ref, onMounted } from "vue";
const apiRequest = apiRequestAxios()

export const checkListItems = ref([])
export const getChecklistItem = async () => {
    try {
        const response = await apiRequest.get('getChecklistItem');
        if (response.data && response.data.items) {
            checkListItems.value = response.data.items
        }
    } catch (error) {
        console.log(error)
    }
};
export const useChecklistItem = () => {
    onMounted(() => {
        if (!checkListItems.value.length) {
            getChecklistItem()
        }
    })
    return { checkListItems }
}

/** ================================================================= */

const ItemsAcquired = ref([])
export const getItemsAcquired = async (service_id) => {
    try {
        const response = await apiRequest.get('getChecklistItemAcquired', {
            params: { service_id: service_id }
        });
        if (response.data && response.data.items) {
            ItemsAcquired.value = response.data.items
        }
    } catch (error) {
        console.log(error)
    }
};

export const useItemsAcquired = () => {
    onMounted(() => {
        if (!ItemsAcquired.value.length) {
            getItemsAcquired()
        }
    })
    return { ItemsAcquired }
}