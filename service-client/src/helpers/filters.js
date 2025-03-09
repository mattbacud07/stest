
export const nonEmptyCountFilter = (filterData) => {
    return Object.values(filterData).filter(value => 
        value !== "" 
        && value !== null 
        && value !== undefined 
        && value.length !== 0 
        && value !== false
    ).length;
}