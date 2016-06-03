/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import 'angular/index'

import bootstrap from 'angular-bootstrap'
import colorpicker from 'angular-bootstrap-colorpicker'
import translation from 'angular-ui-translation/angular-translation'

import clarolineAPI from '../services/module'
import DesktopWidgetInstanceCreationModalCtrl from './Controller/DesktopWidgetInstanceCreationModalCtrl'
import DesktopWidgetInstanceEditionModalCtrl from './Controller/DesktopWidgetInstanceEditionModalCtrl'
import AdminWidgetInstanceCreationModalCtrl from './Controller/AdminWidgetInstanceCreationModalCtrl'
import AdminWidgetInstanceEditionModalCtrl from './Controller/AdminWidgetInstanceEditionModalCtrl'
import WidgetService from './Service/WidgetService'
import WidgetsDirective from './Directive/WidgetsDirective'
import AdminWidgetsDirective from './Directive/AdminWidgetsDirective'

//import Interceptors from '../interceptorsDefault'
//import HtmlTruster from '../html-truster/module'
//import bootstrap from 'angular-bootstrap'

angular.module('WidgetsModule', [
  'ui.bootstrap',
  'ui.bootstrap.tpls',
  'colorpicker.module',
  'ui.translation',
  'ClarolineAPI',
  'gridster'
])
.controller('DesktopWidgetInstanceCreationModalCtrl', DesktopWidgetInstanceCreationModalCtrl)
.controller('DesktopWidgetInstanceEditionModalCtrl', DesktopWidgetInstanceEditionModalCtrl)
.controller('AdminWidgetInstanceCreationModalCtrl', AdminWidgetInstanceCreationModalCtrl)
.controller('AdminWidgetInstanceEditionModalCtrl', AdminWidgetInstanceEditionModalCtrl)
.service('WidgetService', WidgetService)
.directive('widgets', () => new WidgetsDirective)
.directive('adminWidgets', () => new AdminWidgetsDirective)