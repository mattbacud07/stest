<?php

namespace App\Traits;

trait InfoMessage
{
    // Define a global array for messages
    const MESSAGES = [
        'ERROR' => [
            'DB_INSERT' => 'Error adding to the database.',
            'DB_UPDATE' => 'Error updating the record in the database.',
            'DB_DELETE' => 'Error deleting the record from the database.',
            'DB_FETCH' => 'Error fetching data from the database.',
            'NOT_FOUND' => 'Record not found.',
            'VALIDATION' => 'Validation failed. Please check the inputs.',
            'UNAUTHORIZED' => 'You are not authorized to perform this action.',
            'SERVER' => 'An unexpected server error occurred.',
            'FORBIDDEN' => 'You do not have permission to access this resource.',
            'CONFLICT' => 'The operation conflicts with existing data.',
        ],
        'SUCCESS' => [
            'DB_INSERT' => 'Successfully added to the database.',
            'DB_UPDATE' => 'Successfully updated the record.',
            'DB_DELETE' => 'Successfully deleted the record.',
            'DB_FETCH' => 'Data fetched successfully.',
            'VALIDATION' => 'Data validated successfully.',
            'AUTHENTICATION' => 'You are successfully logged in.',
            'LOGOUT' => 'You are successfully logged out.',
            'OPERATION' => 'Operation completed successfully.',
            'SUBMISSION' => 'Form submitted successfully.',
        ],
        'INFO' => [
            'PENDING' => 'Your request is pending approval.',
            'PROCESSING' => 'Your request is being processed.',
            'COMPLETED' => 'Your request has been completed.',
            'NO_CHANGE' => 'No changes were detected.',
            'IN_PROGRESS' => 'Operation is in progress. Please wait.',
        ]
    ];

    public function getMessage($type, $key){
        return self::MESSAGES[$type][$key];
    }
}
