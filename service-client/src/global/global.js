import moment from "moment";


/** Roles */
export const adminRole = 10
export const adminServiceRole = 'Admin'
export const approverRole = 'Approver'
export const engineerRole = 'Engineer'
export const TLRole = 'Team Leader'
export const wimPersonnel = 'WIM Personnel'

export const rolesArray = [adminServiceRole, approverRole, engineerRole, TLRole, wimPersonnel]
export const rolesObject = [
    { title: 'Approver', value: 1 },
    { title: 'Team Leader', value: 2 },
    { title: 'Engineer', value: 3 },
    { title: 'WIM Personnel', value: 4 },
    { title: 'Outbound Personnel', value: 5 },
    { title: 'Administrator', value: 6 },
]

/** Modules */
export const eh_module = 'EH'
export const is_module = 'IS'
export const pm_module = 'PM'
export const cm_module = 'CM'

/** SSU */
export const ssu = {
    ssu1: 1,
    ssu2: 2,
    ssu3: 3,
    ssu4: 4,
    ssu5: 5,
    ssu6: 6,
    ssu7: 7,
}





/**
 * Declared export variables for Equipment Handling Signatories - Job Order Form.
 */
export const IT_DEPARTMENT = 1;
export const APM_NSM_SM = 2;
export const WIM = 3;
export const SERVICE_TL = 4;
export const SERVICE_HEAD_ENGINEER = 5;
export const BILLING_WIM = 6;
export const OUTBOUND = 7;
export const INSTALLATION_TL = 8;
export const INSTALLATION_ENGINEER = 9;

export const approverArray = [IT_DEPARTMENT, APM_NSM_SM, WIM, SERVICE_TL, SERVICE_HEAD_ENGINEER, BILLING_WIM, OUTBOUND, INSTALLATION_TL, INSTALLATION_ENGINEER];

export const pending_approval_status = (approver) => {
    switch (approver) {
        case IT_DEPARTMENT:
            return 'IT Department'
            break
        case APM_NSM_SM:
            return 'APM/NSM/SM'
            break
        case WIM:
            return 'WIM'
            break
        case SERVICE_TL:
            return 'Service Team Leader'
            break
        case SERVICE_HEAD_ENGINEER:
            return 'Service Head Engineer'
            break
        case BILLING_WIM:
            return 'Billing / WIM'
            break
        case OUTBOUND:
            return 'Outbound Personnel'
            break
        case INSTALLATION_TL:
            return 'Installation - Team Leader -'
            break
        case INSTALLATION_ENGINEER:
            return 'Installation - Engineer'
            break
    }
}

/** Set Config Approver Designation */
export const approver_designation = [
    { key: 'IT DEPARTMENT', value: 1 },
    { key: 'APM/NSM/SM', value: 2 },
    { key: 'WAREHOUSE & INVENTORY MANAGEMENT', value: 3 },
    { key: 'SERVICE DEPARTMENT TEAM LEADER', value: 4 },
    { key: 'SERVICE DEPARTMENT HEAD / SERVICE ENGINEER', value: 5 },
    { key: 'BILLING & INVOICING STAFF / WIM PERSONNEL', value: 6 },
    { key: 'OUTBOUND PERSONNEL', value: 7 },
    // { key: 'TEAM LEADER (SSU)', value: 8 },
    // { key: 'ENGINEER (SSU)', value: 9 },
]




/** 
 * Job Order Status
 */
export const ONGOING = 1;
export const PARTIAL_COMPLETE = 2;
export const COMPLETE = 3;
export const DISAPPROVED = 4;
export const RESCHEDULE = 5;
export const INSTALLING = 6;

/** Set Job Order Status */
export const setJOStatus = (status) => {
    switch (parseInt(status)) {
        case PARTIAL_COMPLETE:
            return { text: 'Pending', color: 'blue!important' }
            break;
        case COMPLETE:
            return { text: 'Completed', color: 'green!important' }
            break;
        case DISAPPROVED:
            return { text: 'Disapproved', color: 'red!important' }
            break;
        case RESCHEDULE:
            return { text: 'Reschedule', color: 'black!important' }
            break;
        case INSTALLING:
            return { text: 'Installation Ready', color: 'orange!important' }
            break;
        default:
            return { text: 'Pending Approval', color: 'blue!important' }
    }
}





/** 
 * Internal Servicing Status
 */
export const internalStat = {
    'Delegated': 1,
    // 'Accepted': 2,
    'Declined': 3,
    'InProgress': 4,
    'Completed': 5,
    'Packed': 6,
    // 'Endorsed': 7,
    'ConfirmedByWIM': 8,
};

/** Set Internal Servicing Status */
export const internalStatus = (status) => {
    switch (status) {
        case internalStat.Delegated:
            return { text: 'Delegated', color: 'blue!important' }
            break;
        case internalStat.Declined:
            return { text: 'Declined', color: 'red!important' }
            break;
        case internalStat.InProgress:
            return { text: 'In Progress', color: 'orange!important' }
            break;
        case internalStat.Completed:
            return { text: 'Task Completed', color: 'gray!important' }
            break;
        case internalStat.Packed:
            return { text: 'Packed & Endorsed to Warehouse', color: 'violet!important' }
            break;
        case internalStat.ConfirmedByWIM:
            return { text: 'Confirmed by WIM', color: 'green!important' }
            break;
        default:
            return { text: '', color: 'blue!important' }
    }

}


/** Set Job Order Form ID */
export const setReportNumber = (id, date_created) => {
    return 'JOF-' + String(id).padStart(3, 0) + '-' + moment(date_created).format('YYYY')
}




/** format Date to MM/DD/YYYY */
export const formatDate = (date) => {
    return date ? moment(date).format('MM/DD/YYYY hh:mm a') : ''
}
export const formatDateNoTime = (date) => {
    return date ? moment(date).format('MM/DD/YYYY') : ''
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

