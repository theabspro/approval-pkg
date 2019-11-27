app.config(['$routeProvider', function($routeProvider) {

    $routeProvider.
    //CUSTOMER
    when('/approval-pkg/approval-level/list', {
        template: '<approval-level-list></approval-level-list>',
        title: 'Approval Levels',
    }).
    when('/approval-pkg/approval-level/edit', {
        template: '<approval-level-form></approval-level-form>',
        title: 'Edit Approval Levels',
    }).
}]);