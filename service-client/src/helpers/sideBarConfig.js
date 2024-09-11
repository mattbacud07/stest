import * as pub_var from '@/global/global'

export const sidebarConfig = {
    
    /**
     * Guest Sidebar Config
     */
    Requestor: [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
    ],

    
    /**
     * Admin Sidebar Config
     */
    Administrator: [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        {
            name: 'Account Management',
            icon: 'mdi-account-multiple',
            children: [
                { name: 'Roles & Permission', route: '/roles', icon: 'mdi-account-badge' },
                { name: 'Aprover Config', route: '/aprroval-config', icon: 'mdi-tag-check' },
            ],
        },
        {
            name: 'Maintenance Settings',
            icon: 'mdi-file-document',
            children: [
                { name: 'PM Settings', route: '/pm-settings', icon: 'mdi-file-cog' },
                { name: 'Set Standard Actions', route: '/set-standard', icon: 'mdi-text-box-check-outline' },
                { name: 'Client Contact', route: '/', icon: 'mdi-card-account-mail-outline' },
            ],
        },
    ],

    
    /**
     * Approver Sidebar Config
     */
    Approver: [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
        // { name: 'Equipment Handling', route: '/approver-equipment-handling', icon: 'mdi-file-document-edit' },
    ],
    
    
    /**
     * Outbound Personnel Sidebar Config
     */
    'Outbound Personnel': [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
    ],
    
    
    /**
     * Outbound Personnel Sidebar Config
     */
    'WIM Personnel': [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-document-edit' },
        // {
        //     name: 'Equipment Handling',
        //     icon: 'mdi-folder-wrench',
        //     children: [
        //         { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-document-edit' },
        //     ],
        // },
    ],

    
    /**
     * Engineer Sidebar Config
     */
    Engineer: [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
        { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-compare' },
        { name: 'PM Schedule', route: '/preventive-maintenance', icon: 'mdi-calendar-cursor-outline' },
        { name: 'CM Schedule', route: '/corrective-maintenance', icon: 'mdi-calendar-clock' },
    ],
    
    
    
    /**
     * Team Leader Sidebar Config
     */
    'Team Leader': [
        { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
        // { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-compare' },
        { name: 'PM Schedule', route: '/preventive-maintenance', icon: 'mdi-calendar-cursor-outline' },
        { name: 'CM Schedule', route: '/corrective-maintenance', icon: 'mdi-calendar-clock' },
        // {
        //     name: 'Equipment Handling',
        //     icon: 'mdi-folder-wrench',
        //     children: [
        //         { name: 'Equipment Installation', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
        //         // { name: 'Equipment Installation', route: '/set-schedule-equipment-installation', icon: 'mdi-wrench-cog' },
        //     ],
        // },
        // {
        //     name: 'Preventive Maintenance',
        //     icon: 'mdi-file-cog',
        //     children: [
        //         { name: 'PM Schedule', route: '/preventive-maintenance', icon: 'mdi-calendar-clock' },
        //     ],
        // },
        
    ],
};




// export const sideBarConfig = [
//     { name: 'Dashboard', route: '/', icon: 'mdi-tablet-dashboard' },
//     { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit', access_module: pub_var.eh_module, module_type : 'EH' },
//     { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-document-edit', access_module: pub_var.is_module, module_type : 'EH' },
//     { name: 'PM Schedule', route: '/pm-sched', icon: 'mdi-wrench-clock', access_module: pub_var.pm_module, module_type : 'PM' },
// ]