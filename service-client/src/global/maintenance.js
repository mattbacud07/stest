/** 
 * Preventive Maintenance
 */

/** Work Type */
export const pm = 1
export const cm = 2

/** Status */
/** Status */
export const Scheduled = 'Scheduled';
export const NotSet = 'Not Set';
export const Delegated = 'Delegated';
// export const PendingAcceptance = 'Pending Acceptance';
export const Accepted = 'Accepted';
export const InTransit = 'In Transit';
export const InProgress = 'In Progress';
// export const Backlog = 'Backlog';
// export const Backjob = 'Backjob';
export const Completed = 'Completed';
export const Closed = 'Closed';
export const status_pm = (data) => {
    switch (data) {
        case 'Scheduled':
            return { color: 'blue!important', text: 'Pending Assignment' }
        case 'Delegated':
            return { color: 'orange!important', text: 'Waiting for Acceptance' }
        case 'Accepted':
            return { color: 'yellow!important', text: 'Job Accepted' }
        case 'In Transit':
            return { color: 'lightblue!important', text: 'On the Way' }
        case 'In Progress':
            return { color: 'teal!important', text: 'In Progress' }
        case 'Completed':
            return { color: 'green!important', text: 'Job Completed' }
        case 'Back Job':
            return { color: 'red!important', text: 'Back Job' }
        default:
            return { color: 'gray!important', text: 'Unknown Status' }
    }
}




/** Maintenance Schedule */
export const monthly = 'Monthly'
export const quarterly = 'Quarterly'
export const semiAnnually = 'Semi-Annually'
export const annually = 'Annually'
export const pmFrequency = [monthly, quarterly, semiAnnually, annually]


