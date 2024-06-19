/** 
 * Preventive Maintenance
 */

/** Work Type */
export const pm = 1
export const cm = 2

/** Status */
export const waiting_engineer = 1
export const backlog = 2
export const backjob = 3
export const status_pm = (data) => {
    // data = parseInt(value)
    switch(data){
        case 1:
            return {color: 'blue!important', text : 'Waiting for Engineer'}
        break;
        case 2:
            return {color: 'orange!important', text : 'Back Log'}
        break;
        case 3:
            return {color: 'red!important', text : 'Back Job'}
        break;
        default:
            return {color: 'purple!important', text : 'N'}
    }
}



/** Maintenance Schedule */
export const monthly = 'Monthly'
export const quarterly = 'Quarterly'
export const semiAnnually = 'Semi-Annually'
export const annually = 'Annually'
export const pmSchedule = [monthly, quarterly, semiAnnually, annually]


