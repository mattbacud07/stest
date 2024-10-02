import { ref } from "vue"
// import { apiRequestAxios } from "@/api/api"
// const apiRequest = apiRequestAxios()

/**
 * Admin Sidebar Config
 */
export const adminSidebarConfig = [
    { name: 'AdminDashboard', module: 'Dashboard', icong: 'mdi-tablet-dashboard' },
    {
        name: 'hehehe', module: 'Account Management',
        icong: 'mdi-account-multiple',
        children: [
            { name: 'RolesMain', module: 'Roles & Permission', icong: 'mdi-account-badge' },
            { name: 'SSUDesignation', module: 'Set SSU & Approver Designation', icong: 'mdi-file-document-plus' },
            { name: 'ApprovalConfiguration', module: 'Aprover Config', icong: 'mdi-checkbox-multiple-outline' },
        ],
    },
    {
        name: '#', module: 'Maintenance Settings',
        icong: 'mdi-file-document',
        children: [
            { name: 'PMSettings', module: 'PM Settings',  icong: 'mdi-file-cog' },
            { name: 'SetStandardActions', module: 'Set Standard Actions',  icong: 'mdi-text-box-check-outline' },
            { name: '', module: 'Client Contact', icong: 'mdi-card-account-mail-outline' },
        ],
    },
]





//**Get Updated User Roles and Data */
// const updatedUserData = ref([])
// export const sidebarConfig = ref([])
// export const get_auth_user = async(role) => {
//     try {
//     const response = await apiRequest.get('get_auth_user')  
//     if(response.data && response.data.user){
//         updatedUserData.value = response.data.user.roles
//         // console.log(updatedUserData.value)

//         const dashboard = {"module":"Dashboard","create":true,"read":true,"edit":true,"delete":true,"approve":true,"delegate":true,"installer":true,"report":false,"name":"dashboard","icong" : "mdi-view-dashboard-variant"}
//         const requestor = [
//             {"module":"Equipment Handling","create":true,"read":true,"edit":false,"delete":false,"approve":false,"delegate":false,"installer":false,"report":false,"name":"EquipmentHandling","icong" : "mdi-file-document-edit"},
//         ]
//         // Find the current user's role data
//         const currentRoleData = updatedUserData.value.find(roleData => role === roleData.role_name);
//         const permissions = [dashboard]
//         if(currentRoleData){
//             const rolePermission = JSON.parse(currentRoleData.permissions)
//             const filteredPermission = rolePermission.filter(permission => permission.read)
//             // getPermission.value = [...permissions, ...filteredPermission]
//             return [...permissions, ...filteredPermission]
//             // sidebarConfig.value = [...permissions, ...filteredPermission]
//         }else{
//             return [...permissions, ...requestor,]
//             // sidebarConfig.value = [...permissions, ...requestor,]
//         } 
//     }
//     } catch (error) {
//         console.log(error)
//     }
// }
// get_auth_user()


// const dashboard = {"module":"Dashboard","create":true,"read":true,"edit":true,"delete":true,"approve":true,"delegate":true,"installer":true,"report":false,"name":"dashboard","icong" : "mdi-view-dashboard-variant"}
// const requestor = [
//     {"module":"Equipment Handling","create":true,"read":true,"edit":false,"delete":false,"approve":false,"delegate":false,"installer":false,"report":false,"name":"EquipmentHandling","icong" : "mdi-file-document-edit"},
// ]
//  // Find the current user's role data
//  const currentRoleData = updatedUserData.value.find(roleData => role.currentUserRole === roleData.role_name);
// const permissions = [dashboard]
//  if(currentRoleData){
//     const rolePermission = JSON.parse(currentRoleData.permissions)
//     const filteredPermission = rolePermission.filter(permission => permission.read)
//     // getPermission.value = [...permissions, ...filteredPermission]
//     return [...permissions, ...filteredPermission]
//  }else{
//     return [...permissions, ...requestor,]
//  }


































// export const sidebarConfig = {
    
//     /**
//      * Guest Sidebar Config
//      */
//     Requestor: [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
//     ],

    
//     /**
//      * Admin Sidebar Config
//      */
//     Administrator: [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         {
//             name: 'Account Management',
//             icon: 'mdi-account-multiple',
//             children: [
//                 { name: 'Roles & Permission', route: '/roles', icon: 'mdi-account-badge' },
//                 { name: 'Aprover Config', route: '/aprroval-config', icon: 'mdi-tag-check' },
//             ],
//         },
//         {
//             name: 'Maintenance Settings',
//             icon: 'mdi-file-document',
//             children: [
//                 { name: 'PM Settings', route: '/pm-settings', icon: 'mdi-file-cog' },
//                 { name: 'Set Standard Actions', route: '/set-standard', icon: 'mdi-text-box-check-outline' },
//                 { name: 'Client Contact', route: '/', icon: 'mdi-card-account-mail-outline' },
//             ],
//         },
//     ],

    
//     /**
//      * Approver Sidebar Config
//      */
//     Approver: [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
//         // { name: 'Equipment Handling', route: '/approver-equipment-handling', icon: 'mdi-file-document-edit' },
//     ],
    
    
//     /**
//      * Outbound Personnel Sidebar Config
//      */
//     'Outbound Personnel': [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
//     ],
    
    
//     /**
//      * Outbound Personnel Sidebar Config
//      */
//     'WIM Personnel': [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-document-edit' },
//     ],

    
//     /**
//      * Engineer Sidebar Config
//      */
//     Engineer: [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
//         { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-compare' },
//         { name: 'PM Schedule', route: '/preventive-maintenance', icon: 'mdi-calendar-cursor-outline' },
//         { name: 'CM Schedule', route: '/corrective-maintenance', icon: 'mdi-calendar-clock' },
//     ],
    
    
    
//     /**
//      * Team Leader Sidebar Config
//      */
//     'Team Leader': [
//         { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//         { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
//         // { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-compare' },
//         { name: 'PM Schedule', route: '/preventive-maintenance', icon: 'mdi-calendar-cursor-outline' },
//         { name: 'CM Schedule', route: '/corrective-maintenance', icon: 'mdi-calendar-clock' }, 
//     ],
// };
