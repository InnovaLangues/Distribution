/**
 * Exercise Root Application
 */

import angular from 'angular/index'

import 'angular-loading-bar'
import './question-bank/question-bank.component'

angular
    // Declare the new Application
    .module('BankApp', [
        'angular-loading-bar',
        'question-bank'
    ])

    // Configure application
    .config([
        'cfpLoadingBarProvider',
        function BankAppConfig(cfpLoadingBarProvider) {
            // Configure loader
            cfpLoadingBarProvider.latencyThreshold = 200;
            cfpLoadingBarProvider.includeBar       = false;
            cfpLoadingBarProvider.spinnerTemplate  = '<div class="loading">Loading&#8230;</div>';
        }
    ]);
