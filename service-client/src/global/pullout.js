import { completed, disapproved, uninstalling, pending } from "./global";

/** Level of Approver */
export const SUPERVISOR = 1;
export const OPERATION_SERVICE = 2;
export const SERVICE_DEPT = 2;

export const pullout_approver = (approver) => {
    switch (approver) {
        case SUPERVISOR:
            return 'Immediate Supervisor'
            break
        case OPERATION_SERVICE:
            return 'Operations - Outbound Supervisor - SBU Service Head'
            break 
    }
}


/** Pullout Request Status */
export const pullOutStatus = (status) => {
    switch (status) {
        case pending:
            return { text: 'Pending', color: 'blue' }
            break;
        case disapproved:
            return { text: 'Disapproved', color: 'red' }
            break;
        case uninstalling:
            return { text: 'Pullout in Progress', color: 'warning' }
            break;
        case completed:
            return { text: 'Completed', color: 'green' }
            break;
        default:
            return { text: '', color: 'red' }
    }
}

/** Pullout Approver Designation */
export const pullout_approver_designation = [
    { key: 'Immediate Supervisor', value: 1 },
    { key: 'Operations - Outbound Supervisor - SBU Service Head', value: 2 }
];
