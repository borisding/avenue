<?php
/**
 * Validation configuration for field inputs.
 * {label} is replaceable placeholder for field's label based on the 'label' value.
 * 
 * Example of configurations for email and password field inputs.
 */
return [
    'email' => [
        'label' => 'Email',
        'isRequired' => '{label} is required!',
        'isEmail' => '{label} is invalid email format!'
    ],
    'password' => [
        'label' => 'Password',
        'isRequired' => '{label} is required!'
    ]
];