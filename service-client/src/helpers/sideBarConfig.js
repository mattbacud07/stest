export const sidebarConfig = {
    
    /**
     * Guest Sidebar Config
     */
    Requestor: [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
    ],

    
    /**
     * Admin Sidebar Config
     */
    Administrator: [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        {
            name: 'Account Management',
            icon: 'mdi-account-multiple-outline',
            children: [
                { name: 'Roles & Permission', route: '/roles', icon: 'mdi-account-badge' },
                { name: 'Aprover Config', route: '/aprroval-config', icon: 'mdi-tag-check' },
            ],
        },
        {
            name: 'Maintenance Settings',
            icon: 'mdi-cogs',
            children: [
                { name: 'PM Settings', route: '/pm-settings', icon: 'mdi-file-cog' },
                { name: 'CM Settings', route: '/pm-settings', icon: 'mdi-file-edit' },
            ],
        },
    ],

    
    /**
     * Approver Sidebar Config
     */
    Approver: [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
        // { name: 'Equipment Handling', route: '/approver-equipment-handling', icon: 'mdi-file-document-edit' },
    ],
    
    
    /**
     * Outbound Personnel Sidebar Config
     */
    'Outbound Personnel': [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
    ],
    
    
    /**
     * Outbound Personnel Sidebar Config
     */
    'WIM Personnel': [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        {
            name: 'Equipment Handling',
            icon: 'mdi-folder-wrench',
            children: [
                { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-document-edit' },
            ],
        },
    ],

    
    /**
     * Engineer Sidebar Config
     */
    Engineer: [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        {
            name: 'Equipment Handling',
            icon: 'mdi-folder-wrench',
            children: [
                { name: 'Internal Servicing', route: '/internal-servicing', icon: 'mdi-file-document-edit' },
                { name: 'Equipment Installation', route: '/equipment-installation', icon: 'mdi-wrench-cog-outline' },
            ],
        },
        {
            name: 'Preventive Maintenance',
            icon: 'mdi-file-cog',
            children: [
                { name: 'PM Schedule', route: '/pm-sched-engineer', icon: 'mdi-wrench-clock' },
            ],
        },
        
    ],
    
    
    
    /**
     * Team Leader Sidebar Config
     */
    'Team Leader': [
        { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
        {
            name: 'Equipment Handling',
            icon: 'mdi-folder-wrench',
            children: [
                { name: 'Equipment Installation', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
                // { name: 'Equipment Installation', route: '/set-schedule-equipment-installation', icon: 'mdi-wrench-cog-outline' },
            ],
        },
        {
            name: 'Preventive Maintenance',
            icon: 'mdi-file-cog',
            children: [
                { name: 'PM Schedule', route: '/pm-sched', icon: 'mdi-wrench-clock' },
            ],
        },
        
    ],
};




export const sideBarConfig = [
    { name: 'Dashboard', route: '/', icon: 'mdi-shield-home-outline' },
    { name: 'Equipment Handling', route: '/equipment-handling', icon: 'mdi-file-document-edit' },
]