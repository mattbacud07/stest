import moment from "moment";


/** Roles */
export const adminRole = 10
export const adminServiceRole = 'Admin'
export const approverRole = 'Approver'
export const engineerRole = 'Engineer'
export const serviceTLRole = 'Team Leader'
export const wimPersonnel = 'WIM Personnel'

export const rolesArray = [adminServiceRole, approverRole, engineerRole, serviceTLRole, wimPersonnel]




/**
 * Declared export variables for Equipment Handling Signatories - Job Order Form.
 */
export const IT_DEPARTMENT = 1;
export const APM_NSM_SM = 2;
export const WIM = 3;
export const SERVICE_TL = 4;
export const SERVICE_HEAD_ENGINEER = 5;
export const BILLING_WIM = 6;
export const INSTALLATION_TL = 7;
export const INSTALLATION_ENGINEER = 8;



/** 
 * Job Order Status
 */
export const ONGOING = 1;
export const PARTIAL_COMPLETE = 2;
export const COMPLETE = 3;
export const DISAPPROVED = 4;
export const RESCHEDULE = 5;
export const INSTALLING = 6;

/** 
 * Internal Servicing Status
 */
export const I_ONGOING = 1;
export const I_PARTIAL_COMPLETE = 2;
export const I_COMPLETE = 3;
export const I_DISAPPROVED = 4;
export const I_RESCHEDULE = 5;



//set job order form ID
export const setReportNumber = (id, date_created)=>{
    return 'JOF-' + String(id).padStart(3,0) + '-' + moment(date_created).format('YYYY')
}


/** format Date to MM/DD/YYYY */
export const formatDate = (date) => {
    return date ? moment(date).format('MM/DD/YYYY hh:mm a') : ''
    // if(date){
    //     return moment(date).format('MM/DD/YYYY hh:mm a')
    // }else{
    //     return ''
    // } 
}

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
            return {text: 'Pending', color : 'blue!important'}
    }
}

/** Set Internal Servicing Status */
export const setInternalStatus = (status) => {
    if(parseInt(status) === I_ONGOING){
        return {text: 'Pending', color : 'blue!important'}
    }else if(parseInt(status) === I_COMPLETE){
        return {text: 'Completed', color : 'green!important'}
    }
    return {text: 'Pending', color : 'red!important'}
}


