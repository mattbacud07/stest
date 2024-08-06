import moment from "moment";


/** Roles */
export const adminRole = 10
export const adminServiceRole = 'Admin'
export const approverRole = 'Approver'
export const engineerRole = 'Engineer'
export const serviceTLRole = 'Team Leader'
export const wimPersonnel = 'WIM Personnel'
export const outboundPersonnel = 'Outbound Personnel'

export const rolesArray = [adminServiceRole, approverRole, engineerRole, serviceTLRole, wimPersonnel, outboundPersonnel]

export const ssu = {
    ssu1 : 1,
    ssu2 : 2,
    ssu3 : 3,
    ssu4 : 4,
    ssu5 : 5,
    ssu6 : 6,
    ssu7 : 7,
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
            return {text: 'Pending', color : 'blue!important'}
            break;
        case COMPLETE:
            return {text: 'Completed', color : 'green!important'}
            break;
        case DISAPPROVED:
            return {text: 'Disapproved', color : 'red!important'}
            break;
        case RESCHEDULE:
            return {text: 'Reschedule', color : 'black!important'}
            break;
        case INSTALLING:
            return {text: 'Installation Ready', color : 'orange!important'}
            break;
        default:
            return {text: 'Pending Approval', color : 'blue!important'}
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
    switch(status){
        case internalStat.Delegated:
            return {text: 'Delegated', color : 'blue!important'}
            break;
        case internalStat.Declined:
            return {text: 'Declined', color : 'red!important'}
            break;
        case internalStat.InProgress:
            return {text: 'In Progress', color : 'orange!important'}
            break;
        case internalStat.Completed:
            return {text: 'Task Completed', color : 'gray!important'}
            break;
        case internalStat.Packed:
            return {text: 'Packed & Endorsed', color : 'violet!important'}
            break;
        case internalStat.ConfirmedByWIM:
            return {text: 'Confirmed by WIM', color : 'green!important'}
            break;
        default:
            return {text: '', color : 'blue!important'}
    }

}
// export const I_ONGOING = 1;
// export const I_PACKED_ENDORSED = 2;
// export const I_COMPLETE = 3;
// export const I_DISAPPROVED = 4;
// export const I_RESCHEDULE = 5;
// Delegated: The request has been assigned to a specific engineer.
// Accepted: The engineer has accepted the request and will start processing it.
// Declined: The engineer has declined the request, and it needs to be re-delegated to another engineer.
// In Progress: The engineer has accepted the request and is currently processing it.
// Completed: The engineer has finished processing the request and has confirmed its completion.
// Packed: The request has been packed and is ready to be endorsed to the WIM personnel.
// Endorsed to WIM: The request has been handed over to the WIM personnel.
// Confirmed by WIM: The WIM personnel have confirmed receipt and completion of the request.





/** Set Job Order Form ID */
export const setReportNumber = (id, date_created)=>{
    return 'JOF-' + String(id).padStart(3,0) + '-' + moment(date_created).format('YYYY')
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
    if(rtype === 6){
        return "Other - " + other;
    }else if(rtype === 12){
        return "Other - " + other ;
    }else{
        return rname
    }
}




/** Set Request Type */
export const requestType = (rtype) => {
    if(rtype <= 6){
        return "External Request";
    }else{
        return "Internal Request";
    }
}

