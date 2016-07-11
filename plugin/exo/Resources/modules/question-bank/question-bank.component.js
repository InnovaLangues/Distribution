import angular from 'angular/index'
import 'angular-bootstrap'
import '#/main/core/fos-js-router/module'
import 'angular-ui-translation/angular-translation'
import template from './question-bank.component.html'

angular
    .module('question-bank', [
        'ui.bootstrap',
        'ui.translation',
        'ui.fos-js-router'
    ])
    .component('questionBank', {
        template,
        bindings: {
            questions: '<'
        },
        controller: [
            function () {

            }
        ]
    })