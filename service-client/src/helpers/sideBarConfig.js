/**
 * Admin Sidebar Config
 */
export const adminSidebarConfig = [
    { name: 'AdminDashboard', module: 'Dashboard', icong: 'mdi-tablet-dashboard' },
    {
        name: '#', module: 'Account Management',
        icong: 'mdi-account-multiple',
        children: [
            { name: 'RolesMain', module: 'Roles & Permission', icong: 'mdi-account-badge' },
            { name: 'ApprovalConfiguration', module: 'Aprover Config', icong: 'mdi-checkbox-multiple-outline' },
        ],
    },
    {
        name: '#', module: 'General Settings',
        icong: 'mdi-file-document',
        children: [
            // { name: 'PMSettings', module: 'PM Settings',  icong: 'mdi-file-cog' },
            { name: 'SetStandardActions', module: 'Set Standard Actions',  icong: 'mdi-text-box-check-outline' },
            { name: 'ChecklistItem', module: 'Checklist Item', icong: 'mdi-file-document-plus' },
            // { name: '', module: 'Client Contact', icong: 'mdi-card-account-mail-outline' },
        ],
    },
    
    { name: 'ServiceLogs', module: 'General Logs', icong: 'mdi-list-status' },
]
