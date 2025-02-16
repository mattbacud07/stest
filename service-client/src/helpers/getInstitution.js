import { apiRequestAxios } from "@/api/api";
import { ref, onMounted } from "vue";
const apiRequest = apiRequestAxios()

export const institutionData = ref([])
export const getInstitution = async () => {
    try {
        const response = await apiRequest.get('get_institution');
        if (response.data?.institutions) {
            const data = response.data.institutions

            institutionData.value = response.data.institutions.map(({ name, address, id }) => {
                return {
                    key: name,
                    value: address,
                    institution_id: id,
                }
            })
        }
    } catch (error) {
        console.error("Failed to fetch institutions:", error);
    }
};

export const useInstitutionData = () => {
    onMounted(() => {
        if (!institutionData.value.length) {
            getInstitution()
        }
    })
    return { institutionData }
}






export const institutions = ref([])
export const getInstitutions = async () => {
    try {
        const response = await apiRequest.get('get_institution');
        if (response.data?.institutions) {
            const data = response.data.institutions

            institutions.value = data
        }
    } catch (error) {
        console.error("Failed to fetch institutions:", error);
    }
};

export const useInstitutions = () => {
    onMounted(() => {
        if (!institutions.value.length) {
            getInstitutions()
        }
    })
    return { institutions }
}