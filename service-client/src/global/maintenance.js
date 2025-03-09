/** 
 * Preventive Maintenance
 */

/** Work Type */
export const pm = 1
export const cm = 2

/** Status */
export const Scheduled = 'Scheduled';
export const ReadyForDelegation = 'ReadyForDelegation';
export const NotSet = 'Not Set';
export const Delegated = 'Delegated';
export const Accepted = 'Accepted';
export const InTransit = 'In Transit';
export const InProgress = 'In Progress';
export const Completed = 'Completed';
export const Closed = 'Closed';
export const Declined = 'Declined';
// export const pm_status_array = [
//     Scheduled,
//     NotSet,
//     Delegated,
//     Accepted,
//     InTransit,
//     InProgress,
//     Completed,
//     Closed
// ];
export const status_pm = [
    {key: Scheduled, color: 'blue', text: 'Pending Assignment' },
    {key: ReadyForDelegation, color: 'brown-darken-4', text: 'Ready For Delegation' },
    {key: NotSet, color: 'grey', text: 'Not Set' },
    {key: Delegated, color: 'orange', text: 'Waiting for Acceptance' },
    {key: Accepted, color: 'purple', text: 'Request Accepted' },
    {key: InTransit, color: 'indigo', text: 'On the Way' },
    {key: InProgress, color: 'deep-orange', text: 'In Progress' },
    {key: Completed, color: 'green', text: 'Request Completed' },
    {key: Closed, color: 'grey', text: 'Closed' },
    {key: Declined, color: 'red', text: 'Declined' },
]
export const setPMStatus = (key) => {
    return status_pm.find(v => v.key === key) || { color: 'brown', text: '' }
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


