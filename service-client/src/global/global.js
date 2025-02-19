import moment from "moment";


/** Main Statuses */
export const pending = 'Pending'
export const disapproved = 'Disapproved'
export const rejected = 'Rejected'
export const completed = 'Completed'
export const accepted = 'Accepted';
export const inProgress = 'In Progress';
export const declined = 'Declined';
export const cancelled = 'Cancelled';
export const delegated = 'Delegated';
export const installing = 'Installing';
export const uninstalling = 'Uninstalling';





/** Type of Request use in equipment_peripherals table*/
export const EH = 'eh'
export const PULLOUT = 'pullout'



/** Roles */
export const adminRole = 10
export const adminServiceRole = 'Admin'
export const approverRole = 'Approver'
export const engineerRole = 'Engineer'
export const TLRole = 'Team Leader'
export const wimPersonnel = 'WIM Personnel'


export const adminServiceRoleID = 6
export const approverRoleID = 1
export const TLRoleID = 2
export const engineerRoleID = 3
export const wimPersonnelID = 10
export const requestorID = 5
export const NationalServiceManagerID = 7
export const SBUServiceAssistantID = 8

export const rolesArray = [adminServiceRole, approverRole, engineerRole, TLRole, wimPersonnel]
export const rolesObject = [
    { title: 'Approver', value: 1 },
    { title: 'Team Leader', value: 2 },
    { title: 'Engineer', value: 3 },
    { title: 'WIM Personnel', value: 4 },
    { title: 'Requestor', value: 5 },
    { title: 'Administrator', value: 6 },
]

/** Modules */
export const eh_module = 'EH'
export const is_module = 'IS'
export const pm_module = 'PM'
export const cm_module = 'CM'
export const md_module = 'MD'

/** SSU */
export const sbu = {
    sbu1: 1,
    sbu2: 2,
    sbu3: 3,
    sbu4: 4,
    sbu5: 5,
    sbu6: 6,
    // ssu7: 7,
}

export const sbuArray = Object.values(sbu)




/**
 * Declared export variables for Equipment Handling Signatories - Job Order Form.
 */
export const IT_DEPARTMENT = 1;
export const SM_SER = 2;
export const WIM = 3;
export const SERVICE_TL = 4;
export const BILLING_WIM = 5;
export const OUTBOUND = 6;
export const S_IT_DEPARTMENT = 7;
export const S_SM_SER = 8;
export const S_WIM = 9;
export const S_OUTBOUND = 10;
export const S_BILLING_WIM = 11;

export const INSTALLATION_TL = 18;
export const INSTALLATION_ENGINEER = 19;

export const approverArray = [
    IT_DEPARTMENT, SM_SER, WIM, SERVICE_TL,
    BILLING_WIM, OUTBOUND,
    INSTALLATION_TL, INSTALLATION_ENGINEER,
    S_WIM, S_BILLING_WIM, S_OUTBOUND
];

/** Set Config Approver Designation - Installation */
export const approver_designation = [
    { key: 'IT Department', value: IT_DEPARTMENT },
    { key: 'Dept. Manager (S&M / SER)', value: SM_SER },
    { key: 'Warehouse / Inventory Custodian', value: WIM },
    { key: 'Service Manager - Strategic Business Unit', value: SERVICE_TL },
    { key: 'Billing & Invoicing', value: BILLING_WIM },
    { key: 'Operations - Outbound', value: OUTBOUND },
    { key: '[Satellite Office] - Warehouse Personnel (Receiving)', value: S_WIM },
    { key: '[Satellite Office] - Outbound Personnel (Receiving)', value: S_OUTBOUND },
    { key: '[Satellite Office] - Billing & Invoicing', value: S_BILLING_WIM },
    { key: 'Service Engineer Delegation', value: INSTALLATION_TL },
    { key: 'Installing', value: INSTALLATION_ENGINEER },
];
export const getApproverByLevel = (level, category) => {
    //EH
    switch (category) {
        case 1:
            const approver = approver_designation.find(item => item.value === level)
            if (approver) return approver.key
            return null
        case 2:
            const pullout = approver_designation.find(item => item.value === level)
            if (approver) return approver.key
            return null
    }
}


/** Set Config Approver Designation - Installation */
export const approver_pullout = [
    { key: 'IT Department', value: 1 },
    { key: 'Dept. Manager (S&M / SER)', value: 2 },
    { key: 'Warehouse / Inventory Custodian', value: 3 },
    { key: 'Service Coordinator', value: 4 },
    { key: 'Service Manager - Strategic Business Unit', value: 5 }
    // { key: 'Level 5 [Satellite Office] - Area Billing/WIM', value: 11 },
];


export const approver_category = [
    { key: 'Equipment Handling / Installation', value: 1 },
    { key: 'Equipment Handling / Pull-out', value: 2 },
]



/** 
 * Job Order Status
 */
export const PENDING = 1;
export const DISAPPROVED = 2;
export const INTERNAL_SERVICING = 3;
export const INSTALLING = 4;
export const COMPLETE = 5;


/** Set Job Order Status */
export const JOStatus = [
    { key: PENDING, text: 'Pending', color: 'blue' },
    { key: DISAPPROVED, text: 'Disapproved', color: 'red' },
    { key: INTERNAL_SERVICING, text: 'Internal Servicing Ongoing', color: 'orange' },
    { key: INSTALLING, text: 'Installing', color: 'violet' },
    { key: COMPLETE, text: 'Completed', color: 'green' }
]
export const setJOStatus = (key) => {
    return JOStatus.find(v => v.key === key) || { color: 'brown', text: ''}
}


// ===========================================================================================


/** 
 * Set Internal Servicing Status 
 */
    export const I_DELEGATED = 'Delegated'
    export const I_DECLINED = 'Declined'
    export const I_INPROGRESS = 'In Progress'
    export const I_COMPLETED = 'Completed'
    export const I_RETURNEDHEAD = 'Returned to Head'
    export const I_WAREHOUSE = 'Sent to Warehouse'

export const internalStatus = [
    { key: 1, text: I_DELEGATED, color: 'blue' },
    { key: 2, text: I_DECLINED, color: 'red' },
    { key: 3, text: I_INPROGRESS, color: 'orange' },
    { key: 4, text: I_COMPLETED, color: 'green' },
    { key: 5, text: I_RETURNEDHEAD, color: 'purple' },
    { key: 6, text: I_WAREHOUSE, color: 'indigo' },
];

/** Find Internal Servicing Status */
export const getInternalStatus = (text) => {
    return internalStatus.find(status => status.text === text) || { text: '', color: 'blue' };
};



/** Set Job Order Form ID */
export const setReportNumber = (id, date_created) => {
    return 'JOF-' + String(id).padStart(3, 0) + '-' + moment(date_created).format('YYYY')
}




/** format Date to MM/DD/YYYY */
export const formatDate = (date) => {
    return date ? moment(date).format('YYYY-MM-DD hh:mm a') : ''
}
export const formatDateFullMonthNoTime = (date) => {
    return date ? moment(date).format('MMMM DD, YYYY') : ''
}
export const FullMonthWithTime = (date) => {
    return date ? moment(date).format('MMMM DD, YYYY hh:mm a') : ''
}
export const formatDateNoTime = (date) => {
    return date ? moment(date).format('YYYY-MM-DD') : '--'
}




/** Set Request Name */
export const requestName = (rtype, rname, other) => {
    if (rtype === 6) {
        return "Other - " + other;
    } else if (rtype === 12) {
        return "Other - " + other;
    } else {
        return rname
    }
}

/** Set Request Type */
export const requestType = (rtype) => {
    if (rtype <= 6) {
        return "External Request";
    } else {
        return "Internal Request";
    }
}



/** Approval Category */
export const eh_approver = 'EH'



