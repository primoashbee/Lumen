/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');
global.currency = '₱';


global.$ = global.jQuery = require('jquery');
global.round = (x)=>{
    return Math.round((x + Number.EPSILON) * 100) / 100
}

global.moneyFormat = (number, locale='fil-PH', style='currency', currency='php')=>{
    var input = isNaN(number) ? 0 : number;
    return new Intl.NumberFormat(locale, { style: style, currency: currency }).format(input).replace(/^(\D+)/, '$1 ');
}
import { VueMaskDirective } from 'v-mask';
import { BootstrapVue, IconsPlugin } from 'bootstrap-vue';
window.numeral = require('numeral');


window.Vue = require('vue');

// window.flatten = require('flat')
import 'bootstrap-vue/dist/bootstrap-vue.css'

window.Swal = require('sweetalert2');
window.moment = require('moment');
// import VuePaginate from 'vue-paginate'
// Vue.use(VuePaginate)
/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

// const files = require.context('./', true, /\.vue$/i)
// files.keys().map(key => Vue.component(key.split('/').pop().split('.')[0], files(key).default))
import Money from './plugins/money.js';
import VueNoty from 'vuejs-noty'
window.Noty = require('noty')

Vue.use(VueNoty)
Vue.use(Money)
Vue.use(BootstrapVue)
Vue.use(IconsPlugin)

Vue.prototype.can = function(value){
    return window.Laravel.userPermissions.permissions.includes(value);
}
Vue.prototype.is = function(value){
    return window.Laravel.userPermissions.roles.includes(value);
}
Vue.directive('mask', VueMaskDirective);
Vue.component('products', require('./components/ProductsComponent.vue').default);
Vue.component('notifications', require('./components/NotificationsComponent.vue').default);
Vue.component('step-form',require('./components/StepperComponent.vue').default);
Vue.component('org-structure', require('./components/OfficeStructureComponent.vue').default);
Vue.component('structure-filter', require('./components/StructureFilterComponent.vue').default);
Vue.component('date-picker', require('./components/DatePickerComponent.vue').default);
Vue.component('v2-select', require('./components/SelectComponentV2.vue').default);

// Vue.component('create-client', require('./components/CreateClientComponent.vue').default);
// Vue.component('create-cluster', require('./components/CreateClusterComponent.vue').default);
// Vue.component('create-client-form', require('./components/ClientCreateFormComponent.vue').default);
Vue.component('update-client-form', require('./components/ClientUpdateFormComponent.vue').default);
Vue.component('create-client-form', require('./components/CreateClientFormComponent.vue').default);
Vue.component('test-create-client-form', require('./components/TestClientCreateFormComponent.vue').default);
Vue.component('update-client-form', require('./components/UpdateClientFormComponent.vue').default);
Vue.component('client-list', require('./components/ClientListComponent.vue').default);
// Vue.component('paginator', require('./components/PaginatorComponent.vue').default);
Vue.component('paginator', require('./components/PaginatorComponentV2.vue').default);
Vue.component('upload-file', require('./components/UploadSampleComponent.vue').default);
Vue.component('create-office', require('./components/CreateOfficeComponent.vue').default);
Vue.component('light-modal', require('./components/ModalComponent.vue').default);
Vue.component('example-component', require('./components/ExampleComponent.vue').default);
Vue.component('office-list', require('./components/OfficeListComponent.vue').default);

Vue.component('deposit-dashboard', require('./components/DepositAccountDashboardComponent.vue').default);
Vue.component('payment-methods', require('./components/PaymentMethodComponent.vue').default);
Vue.component('payment-methods-dashboard', require('./components/PaymentMethodDashboardComponent.vue').default);
Vue.component('product-component', require('./components/ProductSelectComponent.vue').default);
Vue.component('bulk-deposit-transaction', require('./components/BulkDepositTransactionComponent.vue').default);
Vue.component('bulk-create-loan-account', require('./components/BulkCreateLoanAccountComponent.vue').default);
Vue.component('bulk-transaction-loan-accounts', require('./components/BulkTransactionLoanAccountComponent.vue').default);


Vue.component('bulk-writeoff-loan-accounts', require('./components/BulkWriteoffComponent.vue').default);
Vue.component('bulk-repayment-v2', require('./components/BulkRepaymentComponentV2.vue').default);
Vue.component('bulk-repayment', require('./components/BulkRepaymentComponent.vue').default);

Vue.component('amount-input', require('./components/AmountInputComponent.vue').default);
Vue.component('multi-search', require('./components/MultiSearchComponent.vue').default);
Vue.component('loan-product-list', require('./components/LoanProductSelectComponent.vue').default);


Vue.component('actions-notification', require('./components/Dashboard/ActionsNotificationComponent.vue').default);
Vue.component('chart-par-movement', require('./components/Dashboard/ParMovementComponent.vue').default);
Vue.component('chart-repayment-trend', require('./components/Dashboard/RepaymentTrendComponent.vue').default);
Vue.component('chart-disbursement-trend', require('./components/Dashboard/DisbursementTrendComponent.vue').default);
Vue.component('chart-client-loans-trend', require('./components/Dashboard/ClientLoansTrendComponent.vue').default);
Vue.component('chart-clients', require('./components/Dashboard/ClientsComponent.vue').default);
Vue.component('chart-summary', require('./components/Dashboard/SummaryTableComponent.vue').default);

Vue.component('create-loan-product', require('./components/CreateLoanProductComponent.vue').default);
Vue.component('multi-select', require('./components/MultiSelectComponent.vue').default);


Vue.component('loan-products-list', require('./components/Settings/LoanProducts.vue').default);
Vue.component('loan-product', require('./components/Settings/LoanProduct.vue').default);
Vue.component('create-client-dependents', require('./components/ClientDependentCreateComponent.vue').default);
Vue.component('client-dependents-list', require('./components/ClientDependentListComponent.vue').default);

Vue.component('client-create-loan-account', require('./components/ClientCreateLoanAccountComponent.vue').default);
Vue.component('client-edit-loan-account', require('./components/ClientEditLoanAccountComponent.vue').default);
// Vue.component('client-profile', require('./components/ClientProfileComponent.vue').default);

Vue.component('loan-profile', require('./components/LoanAccountDashboardComponent.vue').default);
Vue.component('status', require('./components/AccountStatusComponent.vue').default);
// Vue.component('account-list', require('./components/VueTable.vue').default);
Vue.component('account-list', require('./components/AccountListComponent.vue').default);
// Vue.component('pagination', require('./components/Pagination.vue').default);



//Reports
Vue.component('report-disbursement', require('./components/Reports/DisbursementsComponent.vue').default);
Vue.component('report-repayment', require('./components/Reports/RepaymentsComponent.vue').default);
Vue.component('report-deposit', require('./components/Reports/DepositsComponent.vue').default);
Vue.component('report-client', require('./components/Reports/ClientStatusComponent.vue').default);
Vue.component('report-dst', require('./components/Reports/BulkDSTComponent.vue').default);
Vue.component('user-list', require('./components/UserListComponent.vue').default);
Vue.component('loan-in-arrears', require('./components/Reports/LoanInArrearsComponent.vue').default);
Vue.component('transaction-method', require('./components/TransactionMethodComponent.vue').default);
Vue.component('writeoff-report', require('./components/Reports/WriteOffReportComponent.vue').default);


Vue.component('change-password', require('./components/ChangePasswordModalComponent.vue').default);

// Users
Vue.component('create-user', require('./components/Users/CreateUserComponent.vue').default);
Vue.component('edit-user', require('./components/Users/EditUserComponent.vue').default);
Vue.component('users-list', require('./components/Users/UsersListViewComponent.vue').default);

// Roles and Permissions
Vue.component('create-role', require('./components/Roles_Permissions/CreateRoleComponent.vue').default);
Vue.component('edit-role', require('./components/Roles_Permissions/EditRoleComponent.vue').default);
Vue.component('role-filter', require('./components/Roles_Permissions/RoleFilterComponent.vue').default);
Vue.component('role-list', require('./components/Roles_Permissions/RoleListComponent.vue').default);
Vue.component('permission-list', require('./components/Roles_Permissions/PermissionListComponent.vue').default);
Vue.component('permission-filter', require('./components/Roles_Permissions/PermissionFilterComponent.vue').default);
// Vue.component('create-list', require('./components/Roles/RoleListComponent.vue').default);

// Clusters Components

// Deposit

Vue.component('create-deposit', require('./components/CreateDepositComponent.vue').default);
Vue.component('create-client-deposit', require('./components/CreateClientDepositComponent.vue').default);
Vue.component('edit-deposit', require('./components/EditDepositComponent.vue').default);
Vue.component('deposit-list', require('./components/DepositProductsComponent.vue').default);

Vue.component('cluster-list', require('./components/Clusters/ClusterListComponent.vue').default);


// Holdiays Components

Vue.component('holidays-list', require('./components/HolidaysListComponent.vue').default);

// 
Vue.component('disbursement-modal', require('./components/DisbursementModalComponent.vue').default);
// Vue.component('scorecard-component', require('./components/ScorecardComponent.vue').default);
// Vue.component('scorecard-question', require('./components/ScorecardQuestionComponent.vue').default);
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */
const app = new Vue({
    el: '#app',
});

