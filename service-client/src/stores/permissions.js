import { apiRequestAxios } from "@/api/api";
import { defineStore } from "pinia";
import { ref } from "vue";

const apiRequest = apiRequestAxios()

export const store_permission = defineStore('permission', {
    state : () => ({
        permissions : ref([]),
        selected_role_id : ref(null),
        sidebarConfig: {},
    }),

    actions : {
        async loadSidebarConfig(role) {
            this.sidebarConfig.role = { admin : ['data', 'ney']}
        }
    },

    // persist : {
    //     localStorage
    // }
})




 // Only fetch if sidebarConfig for the role is not yet loaded
//  if (!this.sidebarConfig[role]) {
//     try {
//         const response = await apiRequest.get('get_auth_user')
//         if (response.data && response.data.user) {
//             updatedUserData.value = response.data.user.roles
//             // console.log(updatedUserData.value)

//             const dashboard = { "module": "Dashboard", "create": true, "read": true, "edit": true, "delete": true, "approve": true, "delegate": true, "installer": true, "report": false, "name": "dashboard", "icong": "mdi-view-dashboard-variant" }
//             const requestor = [
//                 { "module": "Equipment Handling", "create": true, "read": true, "edit": false, "delete": false, "approve": false, "delegate": false, "installer": false, "report": false, "name": "EquipmentHandling", "icong": "mdi-file-document-edit" },
//             ]
//             // Find the current user's role data
//             const currentRoleData = updatedUserData.value.find(roleData => role === roleData.role_name);
//             const permissions = [dashboard]
//             if (currentRoleData) {
//                 const rolePermission = JSON.parse(currentRoleData.permissions)
//                 const filteredPermission = rolePermission.filter(permission => permission.read)
//                 // getPermission.value = [...permissions, ...filteredPermission]
//                 this.sidebarConfig[role] = [...permissions, ...filteredPermission]
//                 console.log(this.sidebarConfig[role])
//             } else {
//                 this.sidebarConfig[role] = [...permissions, ...requestor,]
//             }
//         }
//     } catch (error) {
//         console.log('Failed to load config', error)
//     }
// }