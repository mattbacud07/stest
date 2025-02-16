import { apiRequestAxios } from "@/api/api";
import { approverRoleID } from "@/global/global";
import { ref, onMounted } from "vue";
const apiRequest = apiRequestAxios()


/** Get All Users */
export const users = ref([])
export const getUsers = async () => {
    try {
        const response = await apiRequest.get('users');
        if (response.data?.users) {
            const data = response.data.users

            users.value = data
        }
    } catch (error) {
        console.error("Failed to fetch userse:", error);
    }
};

export const all_users = () => {
    onMounted(() => {
        if (!users.value.length) {
            getUsers()
        }
    })
    return { users }
}



/** Get All Users Role is Service Engineer */
export const engineers = ref([])
export const getUsersEngineer = async () => {
    try {
        const response = await apiRequest.get('get-engineers-data')
        if (response.data?.engineers) {
            const engineersValue = response.data.engineers.map(data => {
                return {
                    key: data.users.first_name + ' ' + data.users.last_name,
                    value: data.user_id,
                    sbu : data.sbu
                }
            })
            engineers.value = engineersValue
        }
    } catch (error) {
        console.log(error)
    }
};

export const users_engineers = () => {
    onMounted(() => {
        if (!engineers.value.length) {
            getUsersEngineer()
        }
    })
    return { engineers }
}






/** Get Approval Levels of Specific User */
export const users_approval_level = ref([])
export const getUsersApprovalConfig = async () => {
    try {
        const response = await apiRequest.get('get_approval_level_config',{
            params : {
                role : approverRoleID
            }
        });
        if (response.data?.users_role) {
            const data = response.data.users_role

            users_approval_level.value = data
        }
    } catch (error) {
        console.error("Failed to fetch user approval config:", error);
    }
};

export const user_approval_config = () => {
    onMounted(() => {
        if (!users_approval_level.value.length) {
            getUsersApprovalConfig()
        }
    })
    return { users_approval_level }
}