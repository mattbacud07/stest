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
export const pm_status_array = [
    Scheduled,
    NotSet,
    Delegated,
    Accepted,
    InTransit,
    InProgress,
    Completed,
    Closed
];
export const status_pm = (data) => {
    switch (data) {
        case 'Scheduled':
            return { color: 'blue!important', text: 'Pending Assignment' }
        case 'Not Set':
            return { color: 'gray!important', text: 'Not Set' }
        case 'Delegated':
            return { color: 'orange!important', text: 'Waiting for Acceptance' }
        case 'Accepted':
            return { color: '#BF360C!important', text: 'Job Accepted' }
        case 'In Transit':
            return { color: 'indigo!important', text: 'On the Way' }
        case 'In Progress':
            return { color: 'teal!important', text: 'In Progress' }
        case 'Completed':
            return { color: 'green!important', text: 'Job Completed' }
        case 'Closed':
            return { color: 'red!important', text: 'Closed' }
        default:
            return { color: 'gray!important', text: 'Unknown Status' }
    }
}






/** Status after Service */
export const StatusAfterService = {
    operational: 'Operational',
    further_monitoring: 'For Further Monitoring',
    non_operational: 'Non-Operational',
}

/** PM Tag */
export const pm_tag_under_observation = 'Under Observation';
export const pm_tag_set_observation = 'Set Observation Period';
export const pm_tag_backlog = 'BackLog';
export const pm_tag_backjob = 'BackJob';

export const tag_array = [pm_tag_under_observation, pm_tag_backlog, pm_tag_backjob]



/** Maintenance Schedule */
export const monthly = 'Monthly'
export const quarterly = 'Quarterly'
export const semiAnnually = 'Semi-Annually'
export const annually = 'Annually'
export const pmFrequency = [monthly, quarterly, semiAnnually, annually]


